<?php defined('CORENINPAGE') or die('Hack!');
####################################################################################################
####################################################################################################
####################################################################################################
#
class message_queue_flush_0_mysql extends coren_broker
{
#
####################################################################################################
####################################################################################################
####################################################################################################
#
public function select_envelopes ($data)
{
	$limit = $data['limit'];

	$db = coren::db('handle');
	$table_envelope = 'message_envelope';

	$limit = (integer) $limit;
	$sql =
	"
		select `envelope`, `template`, `recipient`, `priority`, `status`
				, `counter`+1, time_to_sec(timediff(now(),`injected`))
		  from {$table_envelope}
		 where `status` = 0
		 order by `counter` asc, `priority` asc
		 limit {$limit}
	";
//???	core::event('query', "Select page of articles from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));

	$result = array();
	while ($row = mysql_fetch_row($res))
		$result[$row[0]] = array(
			'envelope'	=> $row[0],
			'template'	=> $row[1],
			'recipient'	=> $row[2],
			'priority'	=> $row[3],
			'status'	=> $row[4],
			'current_try'	=> $row[5],
			'current_age'	=> $row[6]);
	mysql_free_result($res);
	return $result;
}
#
####################################################################################################
#
public function select_templates ($data)
{
	$identifiers = $data['identifiers'];

	$db = coren::db('handle');
	$table_template = 'message_template';

	$sql_identifiers = array();
	foreach ($identifiers as $id) $sql_identifiers[] = "'" . mysql_real_escape_string($id, $db) . "'";
	$sql_identifiers = implode(",", $sql_identifiers);
	$sql =
	"
		select `template`, `subject`, `message`
		  from {$table_template}
		 where `template` in ({$sql_identifiers})
	";
//???	core::event('query', "Select page of articles from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));

	$result = array();
	while ($row = mysql_fetch_row($res))
		$result[$row[0]] = array(
			'template'	=> $row[0],
			'subject'	=> $row[1],
			'message'	=> $row[2]);
	mysql_free_result($res);
	return $result;
}
#
####################################################################################################
####################################################################################################
####################################################################################################
#
public function mark_envelope_success ($data)
{
	$envelopes = isset($data['envelopes']) ? $data['envelopes'] : array($data['envelope']);
	$status    = $data['status'];

	$db = coren::db('handle');
	$table_envelope = 'message_envelope';

	$sql_identifiers = array();
	foreach ($envelopes as $id) $sql_identifiers[] = "'" . mysql_real_escape_string($id, $db) . "'";
	$sql_identifiers = implode(",", $sql_identifiers);
	$status = (integer) $status;
	$sql =
	"
		update {$table_envelope}
		   set `status`	    = {$status}
		     , `counter`    = `counter` + 1
		     , `last_stamp` = now()
		     , `last_error` = null
		     , `last_errno` = null
		 where `envelope` in ({$sql_identifiers})
	";
//???	core::event('query', "Insert article to database.", array('query'=>$sql));
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));
}
#
####################################################################################################
#
public function mark_envelope_failure ($data)
{
	$envelope = $data['envelope'];
	$error    = $data['error'   ];
	$errno    = $data['errno'   ];
	$status   = $data['status'  ];

	$db = coren::db('handle');
	$table_envelope = 'message_envelope';

	$envelope = "'" . mysql_real_escape_string($envelope, $db) . "'";
	$error    = "'" . mysql_real_escape_string($error   , $db) . "'";
	$errno    = (integer) $errno;
	$status   = (integer) $status;
	$sql =
	"
		update {$table_envelope}
		   set `status`	    = {$status}
		     , `counter`    = `counter` + 1
		     , `last_stamp` = now()
		     , `last_error` = {$error}
		     , `last_errno` = {$errno}
		 where `envelope` = {$envelope}
	";
//???	core::event('query', "Insert article to database.", array('query'=>$sql));
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));
}
#
####################################################################################################
####################################################################################################
####################################################################################################
}
?>