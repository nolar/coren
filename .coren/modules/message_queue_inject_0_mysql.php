<?php defined('CORENINPAGE') or die('Hack!');
####################################################################################################
####################################################################################################
####################################################################################################
#
####################################################################################################
####################################################################################################
####################################################################################################
#
class message_queue_inject_0_mysql extends coren_broker
{
#
####################################################################################################
####################################################################################################
####################################################################################################
#
public function insert_template ($data)
{
	$subject = $data['subject'];
	$message = $data['message'];

	$db = coren::db('handle');
	$table_template = 'message_template';

	$subject = "'" . mysql_real_escape_string($subject, $db) . "'";
	$message = "'" . mysql_real_escape_string($message, $db) . "'";
	$sql =
	"
		insert into {$table_template} (
			 `template`
			,`subject`
			,`message`
		) values (
			 default
			,{$subject}
			,{$message}
		)
	";
//???	core::event('query', "Insert article to database.", array('query'=>$sql));
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));
	return mysql_insert_id($db);
}
#
####################################################################################################
#
public function insert_envelopes ($data)
{
	$template   = $data['template'];
	$priority   = $data['priority'];
	$recipients = $data['recipients'];

	$db = coren::db('handle');
	$table_envelope = 'message_envelope';

	$template = "'" . mysql_real_escape_string($template, $db) . "'";
	$priority = (integer) $priority;
	$sql_values = array();
	foreach ($recipients as $recipient)
	{
		$recipient = "'" . mysql_real_escape_string($recipient, $db) . "'";
		$sql_values[] = "({$template},{$recipient},{$priority},0,0,now())";
	}
	$sql_values = implode(",", $sql_values);
	$sql =
	"
		insert into {$table_envelope} (
			 `template`
			,`recipient`
			,`priority`
			,`status`
			,`counter`
			,`injected`
		) values {$sql_values}
	";
//???	core::event('query', "Select page of articles from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));
}
#
####################################################################################################
####################################################################################################
####################################################################################################
}
?>