<?php defined('CORENINPAGE') or die('Hack!');
//todo: 1. сделать иерархию активаторов: базовый ничего не умеет, activator_serialized - через serialize() хранит данные,
//todo:    другие потомки хранят как угодно, в том числе дополнительной субтаблицей, или xml-encoded, или другими методами.

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class activator_0_exception_duplicate extends exception {}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class activator_0 extends module implements dbaware
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected $trycount;
protected $generator;
protected $mailer;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __construct ($configs)
{
	parent::__construct($configs);

	$this->trycount = core::find_scalar(array($configs), array('trycount'), null);
	$this->trycount = max(1, (integer) $this->trycount);

	$this->generator = core::find_scalar(array($configs), array('generator'), null);
	$this->mailer    = core::find_scalar(array($configs), array('mailer'   ), null);
	if (!isset($this->generator)) throw new exception('misconfig: generator');
	if (!isset($this->mailer   )) throw new exception('misconfig: mailer'   );
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function request ($args)
{
	$mail = isset($args['mail']) ? $args['mail'] : null;
	$data = isset($args['data']) ? $args['data'] : null;

	$addr = $_SERVER['REMOTE_ADDR'];
	$data = serialize($data);

	$code = null;
	for ($i = 1; ($i <= $this->trycount) && is_null($code); $i++)
	{
		$code = core::call($this->generator, 'generate');
		try
		{
			core::db('make_activation', compact('mail', 'code', 'data', 'addr'));
			$code = $code;
		}
		catch (activator_0_exception_duplicate $exception)
		{
			if ($i >= $this->trycount) throw $exception;
		}
	}

	if ($mail != '')
	{
		$priority = -1;//NB: positive value mean immediate sending.
		$subject  = core::template('request_subject', compact('mail', 'code', 'data', 'addr'));
		$message  = core::template('request_message', compact('mail', 'code', 'data', 'addr'));
		$headers  = core::template('request_headers', compact('mail', 'code', 'data', 'addr'));
		$to       = $mail;
		core::call($this->mailer, 'inject', compact('priority', 'subject', 'message', 'headers', 'to'));
	}

	return $code;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function confirm ($args)
{
	$code = isset($args['code']) ? $args['code'] : null;

	$addr = $_SERVER['REMOTE_ADDR'];
	if (!is_scalar($code)) $code = null;

	$temp = core::db('find_activation', compact('code'));
	if (is_array($temp))
	{
		core::db('mark_activation', compact('code', 'addr'));

		$mail = isset($temp['mail']) ? $temp['mail'] : null;
		$data = isset($temp['data']) ? $temp['data'] : null;

		$temp = @unserialize($data);
		if ($temp !== false) $data = $temp;//!!! а может возвращать null? чего вдруг возвращать неправильно распакованные данные в сыром виде-то?
	} else
	{
		$mail = null;
		$data = null;
	}

	if ($mail != '')
	{
		$priority = -1;//NB: positive value mean immediate sending.
		$subject  = core::template('confirm_subject', compact('mail', 'code', 'data', 'addr'));
		$message  = core::template('confirm_message', compact('mail', 'code', 'data', 'addr'));
		$headers  = core::template('confirm_headers', compact('mail', 'code', 'data', 'addr'));
		$to       = $mail;
		core::call($this->mailer, 'inject', compact('priority', 'subject', 'message', 'headers', 'to'));
	}

	return $data;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

?>