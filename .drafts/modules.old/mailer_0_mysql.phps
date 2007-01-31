<?php defined('CORENINPAGE') or die('Hack!');

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class mailer_0_mysql extends dbworker
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __construct ($configs)
{
	parent::__construct($configs);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_queue ($args)
{
	$limit = isset($args['limit']) ? $args['limit'] : null;

	$handle = core::handle();
	$table_queue = core::table('queue');

	$sql =
	"
		select `target`, `letter`
		  from {$table_queue}
		 where `status` = 0 or `status` = 2
		 order by `status` asc, `priority` asc
		 limit {$limit}
	";
//???	core::event('query', "Select page of articles from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = array();
	while ($row = mysql_fetch_row($res))
	{
		$result[] = array(
			'target'	=> $row[0],
			'letter'	=> $row[1]);
	}
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_targets ($args)
{
	$ids = isset($args['ids']) ? $args['ids'] : null;

	$handle = core::handle();
	$table_target = core::table('target');

	$idlist = array();
	foreach ($ids as $id) $idlist[] = "'" . mysql_real_escape_string($id, $handle) . "'";
	$idlist = implode(",", $idlist);

	$sql =
	"
		select `target`, `email`
		  from {$table_target}
		 where `target` in ({$idlist})
	";
//???	core::event('query', "Select page of articles from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = array();
	while ($row = mysql_fetch_row($res))
	{
		$result[] = array(
			'target'	=> $row[0],
			'email'		=> $row[1]);
	}
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_letters ($args)
{
	$ids = isset($args['ids']) ? $args['ids'] : null;

	$handle = core::handle();
	$table_letter = core::table('letter');

	$idlist = array();
	foreach ($ids as $id) $idlist[] = "'" . mysql_real_escape_string($id, $handle) . "'";
	$idlist = implode(",", $idlist);

	$sql =
	"
		select `letter`, `subject`, `message`, `headers`
		  from {$table_letter}
		 where `letter` in ({$idlist})
	";
//???	core::event('query', "Select page of articles from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = array();
	while ($row = mysql_fetch_row($res))
	{
		$result[] = array(
			'letter'	=> $row[0],
			'subject'	=> $row[1],
			'message'	=> $row[2],
			'headers'	=> $row[3]);
	}
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function mark_success ($args)
{
	$target  = isset($args['target' ]) ? $args['target' ] : null;
	$letter  = isset($args['letter' ]) ? $args['letter' ] : null;

	$handle = core::handle();
	$table_queue = core::table('queue');

	$target  = "'" . mysql_real_escape_string($target , $handle) . "'";
	$letter  = "'" . mysql_real_escape_string($letter , $handle) . "'";
	$sql =
	"
		update {$table_queue}
		   set `status`	= 1
		     , `stamp`	= now()
		     , `error`	= null
		 where `target`	= {$target}
		   and `letter`	= {$letter}
	";
//???	core::event('query', "Insert article to database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function mark_delayed ($args)
{
	$target  = isset($args['target' ]) ? $args['target' ] : null;
	$letter  = isset($args['letter' ]) ? $args['letter' ] : null;
	$mailmsg = isset($args['mailmsg']) ? $args['mailmsg'] : null;

	$handle = core::handle();
	$table_queue = core::table('queue');

	$target  = "'" . mysql_real_escape_string($target, $handle) . "'";
	$letter  = "'" . mysql_real_escape_string($letter , $handle) . "'";
	$mailmsg = "'" . mysql_real_escape_string($mailmsg, $handle) . "'";
	$sql =
	"
		update {$table_queue}
		   set `status`	= 2
		     , `stamp`	= now()
		     , `error`	= {$mailmsg}
		 where `target`	= {$target}
		   and `letter`	= {$letter }
	";
//???	core::event('query', "Insert article to database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function mark_failure ($args)
{
	$target  = isset($args['target' ]) ? $args['target' ] : null;
	$letter  = isset($args['letter' ]) ? $args['letter' ] : null;
	$error   = isset($args['error'  ]) ? $args['error'  ] : null;

	$handle = core::handle();
	$table_queue = core::table('queue');

	$target  = "'" . mysql_real_escape_string($target , $handle) . "'";
	$letter  = "'" . mysql_real_escape_string($letter , $handle) . "'";
	$error   = "'" . mysql_real_escape_string($error  , $handle) . "'";
	$sql =
	"
		update {$table_queue}
		   set `status`	= 3
		     , `stamp`	= now()
		     , `error`	= {$error}
		 where `target`	= {$target}
		   and `letter`	= {$letter }
	";
//???	core::event('query', "Insert article to database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function insert_target ($args)
{
	$email = isset($args['email']) ? $args['email'] : null;

	$handle = core::handle();
	$table_target = core::table('target');

	$email = "'" . mysql_real_escape_string($email, $handle) . "'";
	$sql =
	"
		insert into {$table_target} (
			 `target`
			,`email`
		) values (
			 default
			,{$email}
		)
	";
//???	core::event('query', "Insert article to database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
	return mysql_insert_id($handle);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function insert_letter ($args)
{
	$subject = isset($args['subject']) ? $args['subject'] : null;
	$message = isset($args['message']) ? $args['message'] : null;
	$headers = isset($args['headers']) ? $args['headers'] : null;

	$handle = core::handle();
	$table_letter = core::table('letter');

	$subject = "'" . mysql_real_escape_string($subject, $handle) . "'";
	$message = "'" . mysql_real_escape_string($message, $handle) . "'";
	$headers = "'" . mysql_real_escape_string($headers, $handle) . "'";
	$sql =
	"
		insert into {$table_letter} (
			 `letter`
			,`subject`
			,`message`
			,`headers`
		) values (
			 default
			,{$subject}
			,{$message}
			,{$headers}
		)
	";
//???	core::event('query', "Insert article to database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
	return mysql_insert_id($handle);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function insert_queue ($args)
{
	$target   = isset($args['target'  ]) ? $args['target'  ] : null;
	$letter   = isset($args['letter'  ]) ? $args['letter'  ] : null;
	$priority = isset($args['priority']) ? $args['priority'] : null;

	$handle = core::handle();
	$table_queue = core::table('queue');

	$target   = "'" . mysql_real_escape_string($target, $handle) . "'";
	$letter   = "'" . mysql_real_escape_string($letter, $handle) . "'";
	$priority = (integer) $priority;
	$sql =
	"
		insert into {$table_queue} (
			 `target`
			,`letter`
			,`priority`
		) values (
			 {$target  }
			,{$letter  }
			,{$priority}
		)
	";
//???	core::event('query', "Select page of articles from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

?>