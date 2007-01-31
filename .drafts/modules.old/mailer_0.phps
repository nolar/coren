<?php defined('CORENINPAGE') or die('Hack!');
//todo: 1. more default headers: x-mailer, reply-to, organization, mime type, encodings/charsets...
//todo: 2. more limits: per_hour, per_minute, per_day...
//todo: 3. организовать ротацию сообщений в очереди, если их сейчас не удалось отправить (то есть снижать приоритет на каждой попытке).
//todo: 4. реализовать лимиты времени (timeout), пока сообщение будет пытаться отправиться, а после истечения лимита ставить его в refused и больше не пытаться.

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class mailer_0 extends module implements dbaware
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected $grant;

protected $limit_per_call;

protected $default_from_addr;
protected $default_from_name;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __construct ($configs)
{
	parent::__construct($configs);

	$this->grant = core::find_scalar(array($configs), array('grant'), null);
	if (is_null($this->grant)) throw new exception("misconfig: grant");

	$this->limit_per_call = core::find_scalar(array($configs), array('limit_per_call'), null);

	$this->default_from_addr = core::find_scalar(array($configs), array('default_from_addr'), null);
	$this->default_from_name = core::find_scalar(array($configs), array('default_from_name'), null);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function normalize_fields ($subject, $message, $headers)
{
	if (is_array($subject)) $subject = implode("\r\n", $subject);
	if (is_array($message)) $message = implode("\r\n", $message);
	if (is_array($headers)) $headers = implode("\r\n", $headers);

	$subject = trim(preg_replace("/(\\r|\\n)+/"   , " "      , $subject));
	$message = trim(preg_replace("/([^\\r]|^)\\n/", "\\1\r\n", $message));
	$headers = trim(preg_replace("/([^\\r]|^)\\n/", "\\1\r\n", $headers));

	if (!is_null($this->default_from_addr) && !preg_match("/\\r\\nFrom:\\s/", $headers))
		if (!is_null($this->default_from_name))
			$headers .= "\r\nFrom: {$this->default_from_name} <{$this->default_from_addr}>";
		else
			$headers .= "\r\nFrom: {$this->default_from_addr}";

	return array($subject, $message, $headers);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function send_and_mark ($target, $letter, $email, $subject, $message, $headers)
{
	$errcode = $mailmsg = null;

	if ($email == '')
	{
		$errcode = "target/email";
	} else
	if ($subject == '')
	{
		$errcode = "letter/subject";
	} else
	if ($message == '')
	{
		$errcode = "letter/message";
	} else
	{
		$mailmsg = null;
		$errcode = (bool) $this->mail($email, $subject, $message, $headers, $mailmsg);
	}

	if ($errcode === true ) core::db('mark_success', compact('target', 'letter'           )); else
	if ($errcode === false) core::db('mark_delayed', compact('target', 'letter', 'mailmsg')); else
	if (!is_null($errcode)) core::db('mark_failure', compact('target', 'letter', 'errcode'));
	
	return array($errcode, $mailmsg);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function inject ($args)
{
//???	делать ли проверку гранта (права доступа) на инджект? ведь инджект обычно делается не по запросу юзера, а внутримодульно.
//???	или сделать проверку, но почти всегда проставлять грант инджекта как "+" ("всегда можно")?
//???	if (!core::grant($this->grant_inject))
//???	{
//???		return;
//???	}

	$priority = isset($args['priority']) ? $args['priority'] : null;
	$subject  = isset($args['subject' ]) ? $args['subject' ] : null;
	$message  = isset($args['message' ]) ? $args['message' ] : null; 
	$headers  = isset($args['headers' ]) ? $args['headers' ] : null;
	$to       = isset($args['to'      ]) ? $args['to'      ] : null;

	list($subject, $message, $headers) = $this->normalize_fields($subject, $message, $headers);
	if (($subject == '') && ($message == '')) return;
	if (!is_array($to)) $to = ($to == '') ? array() : array($to);

	$letter = core::db('insert_letter', compact('subject', 'message', 'headers'));
	foreach ($to as $email)
	{
		$target = core::db('insert_target', compact('email'));
		core::db('insert_queue', compact('target', 'letter', 'priority'));
		if ($priority > 0) $this->send_and_mark($target, $letter, $email, $subject, $message, $headers);
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function queue ($args)
{
	if (!core::grant($this->grant))
	{
		echo core::template('denied');
		return;
	}
	
	$limit = max(1, $this->limit_per_call);//min(max(1, $this->limit_per_call)/*, max(1, $this->limit_per_*), ...*/);
	$queue = core::db('select_queue', compact('limit'));
	$count = count($queue);

	if (!is_array($queue) || empty($queue))
	{
		echo core::template('empty', compact('limit', 'count'));
		return;
	}

	$ids = array(); foreach ($queue as $item) $ids[] = $item['target']; $targets = empty($ids) ? array() : core::db('select_targets', compact('ids'));
	$ids = array(); foreach ($queue as $item) $ids[] = $item['letter']; $letters = empty($ids) ? array() : core::db('select_letters', compact('ids'));

	$targetids = array(); foreach ($targets as $key => $val) $targetids[$key] = $targets[$key]['target'];
	$letterids = array(); foreach ($letters as $key => $val) $letterids[$key] = $letters[$key]['letter'];

	foreach ($letters as $index => $letter)
	{
		$subject = $letters[$index]['subject'];
		$message = $letters[$index]['message'];
		$headers = $letters[$index]['headers'];
		list($subject, $message, $headers) = $this->normalize_fields($subject, $message, $headers);
		$letters[$index]['subject'] = $subject;
		$letters[$index]['message'] = $message;
		$letters[$index]['headers'] = $headers;
	}

	$reports = array();
	$success = $refused = $delayed = 0;

	foreach ($queue as $index => $item)
	{
		$target = $item['target']; $p = array_search($target, $targetids); $targetinfo = ($p !== false) ? $targets[$p] : null;
		$letter = $item['letter']; $p = array_search($letter, $letterids); $letterinfo = ($p !== false) ? $letters[$p] : null;

		$email   = is_array($targetinfo) ? $targetinfo['email'  ] : null;
		$subject = is_array($letterinfo) ? $letterinfo['subject'] : null;
		$message = is_array($letterinfo) ? $letterinfo['message'] : null;
		$headers = is_array($letterinfo) ? $letterinfo['headers'] : null;

		list($errcode, $mailmsg) = $this->send_and_mark($target, $letter, $email, $subject, $message, $headers);

		if ($errcode === true)
		{
			$success++;
			$reports[] = core::template('report_success', compact('target', 'letter', 'email', 'subject', 'message', 'headers'));
		} else
		if ($errcode === false)
		{
			$delayed++;
			$reports[] = core::template('report_delayed', compact('target', 'letter', 'email', 'subject', 'message', 'headers', 'mailmsg'));
		} else
		if (!is_null($errcode))
		{
			$refused++;
			$reports[] = core::template('report_refused', compact('target', 'letter', 'email', 'subject', 'message', 'headers', 'errcode'));
		}
	}

	$reports = implode(core::template('report_glue'), $reports);
	echo core::template('report', compact('reports', 'limit', 'count', 'success', 'delayed', 'refused'));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function main ($args)
{
	return $this->queue($args);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function mail ($email, $subject, $message, $headers, &$errormsg)
{
	throw new exception("Mailer class is abstract; its method mail() is not implemented.");
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

?>