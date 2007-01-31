<?php defined('CORENINPAGE') or die('Hack!');

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class event_notes2forum_0_mysql extends dbworker
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function find_topic_by_article ($args)
{
	$article = isset($args['article']) ? $args['article'] : null;

	$handle = core::handle();
	$table_sync = core::table('sync');

	$article = "'" . mysql_real_escape_string($article, $handle) . "'";
	$sql =
	"
		select `topic`
		  from {$table_sync}
		 where `article` = {$article}
		 limit 1
	";
//???	core::event('query', "Select number of files in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = null;
	while ($row = mysql_fetch_row($res))
		$result = $row[0];
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function find_message_by_note ($args)
{
	$note = isset($args['note']) ? $args['note'] : null;

	$handle = core::handle();
	$table_sync = core::table('syncnotes');

	$note = "'" . mysql_real_escape_string($note, $handle) . "'";
	$sql =
	"
		select `message`
		  from {$table_sync}
		 where `note` = {$note}
		 limit 1
	";
//???	core::event('query', "Select number of files in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = null;
	while ($row = mysql_fetch_row($res))
		$result = $row[0];
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function sync_message_note ($args)
{
	$message = isset($args['message']) ? $args['message'] : null;
	$note    = isset($args['note'   ]) ? $args['note'   ] : null;

	$handle = core::handle();
	$table_sync = core::table('syncnotes');

	$message = "'" . mysql_real_escape_string($message, $handle) . "'";
	$note    = "'" . mysql_real_escape_string($note   , $handle) . "'";
	$sql =
	"
		replace into {$table_sync} (`message`, `note`)
		values ({$message}, {$note})
	";
//???	core::event('query', "Select number of files in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function desync_message_note ($args)
{
	$message = isset($args['message']) ? $args['message'] : null;
	$note    = isset($args['note'   ]) ? $args['note'   ] : null;

	$handle = core::handle();
	$table_sync = core::table('syncnotes');

	$message = "'" . mysql_real_escape_string($message, $handle) . "'";
	$note    = "'" . mysql_real_escape_string($note   , $handle) . "'";
	$sql =
	"
		delete from {$table_sync}
		where `message` = {$message} and `note` = {$note}
	";
//???	core::event('query', "Select number of files in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

?>