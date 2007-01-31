<?php defined('CORENINPAGE') or die('Hack!');
//todo: 1. add additional data to core::event()s in some methods. for ex. itemid and so on...

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!core::depend('forum_0')) return; // for exceptions

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class forum_0_mysql extends dbworker
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_messages_count ($args)
{
	$parent     = isset($args['parent'    ]) ? $args['parent'    ] : null;
	$itemid     = isset($args['itemid'    ]) ? $args['itemid'    ] : null;
	$topic      = isset($args['topic'     ]) ? $args['topic'     ] : null;

	$handle = core::handle();
	$table_message = core::table('message');

	$filterclause = $this->__filterclause_message($handle, $parent, $itemid, $topic);
	$sql =
	"
		select count(*)
		  from {$table_message}
		 where {$filterclause}
	";
//???	core::event('query', "Select number of messages in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = null;
	while ($row = mysql_fetch_row($res))
		$result = $row[0];
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_messages_data ($args)
{
	$parent  = isset($args['parent' ]) ? $args['parent' ] : null;
	$itemid  = isset($args['itemid' ]) ? $args['itemid' ] : null;
	$page    = isset($args['page'   ]) ? $args['page'   ] : null; $page   = (integer) $page  ;
	$size    = isset($args['size'   ]) ? $args['size'   ] : null; $size   = (integer) $size  ;
	$skip    = isset($args['skip'   ]) ? $args['skip'   ] : null; $skip   = (integer) $skip  ;
	$count   = isset($args['count'  ]) ? $args['count'  ] : null; $count  = (integer) $count ;
	$offset  = isset($args['offset' ]) ? $args['offset' ] : null; $offset = (integer) $offset;
	$sorting = isset($args['sorting']) ? $args['sorting'] : null;
	$reverse = isset($args['reverse']) ? $args['reverse'] : null;
	$topic   = isset($args['topic'  ]) ? $args['topic'  ] : null;

	$handle = core::handle();
	$table_message = core::table('message');

	$sortings_asc  = array('message', 'topic', 'creator', 'creator_addr', 'text');
	$sortings_desc = array('created');
	if (in_array($sorting, $sortings_asc )) { if (is_null($reverse)) $reverse = false; } else
	if (in_array($sorting, $sortings_desc)) { if (is_null($reverse)) $reverse = true ; } else
						{ $sorting = 'created' ; $reverse = false; }
	$reverse = ($reverse ? 'desc' : 'asc');
	$filterclause = $this->__filterclause_message($handle, $parent, $itemid, $topic);
	$sql =
	"
		select `message`, `topic`, `creator`, `creator_addr`, `created`, `text`
		  from {$table_message}
		 where {$filterclause}
		 order by `{$sorting}` {$reverse}, `message` asc
		 limit {$size} offset {$offset}
	";
//???	core::event('query', "Select page of messages from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = array();
	while ($row = mysql_fetch_row($res))
	{
		$result[$row[0]] = array(
			'message'		=> $row[0],
			'topic'			=> $row[1],
			'creator'		=> $row[2],
			'creator_addr'		=> $row[3],
			'created'		=> $this->__splitdate($row[4]),
			'text'			=> $row[5]);
	}
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function insert_message ($args)
{//args: itemnew; returns itemid
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null; if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_message = core::table('message');

	foreach ($itemnew as $key => $val) $itemnew[$key] = !is_scalar($val) || ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$sql =
	"
		insert into {$table_message} (
			 `message`
			,`topic`
			,`creator`
			,`creator_addr`
			,`created`
			,`text`
		) values (
			 default
			,{$itemnew['topic']}
			,{$itemnew['creator']}
			,{$itemnew['creator_addr']}
			,now()
			,{$itemnew['text']}
		)
	";
//???	core::event('query', "Insert message to database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new forum_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
	return mysql_insert_id($handle);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function update_message ($args)
{//args: itemid, itemnew
	$itemid  = isset($args['itemid']) ? $args['itemid'] : null;
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null;
	if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_message = core::table('message');

	foreach ($itemnew as $key => $val) $itemnew[$key] = !is_scalar($val) || ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	$sql =
	"
		update {$table_message} set
			 `topic`   = {$itemnew['topic']}
			,`text`    = {$itemnew['text' ]}
		where `message` = {$itemid}
	";
//???	core::event('query', "Update message in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new forum_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function delete_message ($args)
{//args: itemid
	$itemid = isset($args['itemid']) ? $args['itemid'] : null;

	$handle = core::handle();
	$table_message = core::table('message');

	$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	$sql =
	"
		delete from {$table_message}
		 where `message` = {$itemid}
	";
//???	core::event('query', "Delete message from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_topics_count ($args)
{
	$parent     = isset($args['parent'    ]) ? $args['parent'    ] : null;
	$itemid     = isset($args['itemid'    ]) ? $args['itemid'    ] : null;
	$forum      = isset($args['forum'     ]) ? $args['forum'     ] : null;

	$handle = core::handle();
	$table_topic = core::table('topic');

	$filterclause = $this->__filterclause_topic($handle, $parent, $itemid, $forum);
	$sql =
	"
		select count(*)
		  from {$table_topic}
		 where {$filterclause}
	";
//???	core::event('query', "Select number of topics in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = null;
	while ($row = mysql_fetch_row($res))
		$result = $row[0];
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_topics_data ($args)
{
	$parent  = isset($args['parent' ]) ? $args['parent' ] : null;
	$itemid  = isset($args['itemid' ]) ? $args['itemid' ] : null;
	$page    = isset($args['page'   ]) ? $args['page'   ] : null; $page   = (integer) $page  ;
	$size    = isset($args['size'   ]) ? $args['size'   ] : null; $size   = (integer) $size  ;
	$skip    = isset($args['skip'   ]) ? $args['skip'   ] : null; $skip   = (integer) $skip  ;
	$count   = isset($args['count'  ]) ? $args['count'  ] : null; $count  = (integer) $count ;
	$offset  = isset($args['offset' ]) ? $args['offset' ] : null; $offset = (integer) $offset;
	$sorting = isset($args['sorting']) ? $args['sorting'] : null;
	$reverse = isset($args['reverse']) ? $args['reverse'] : null;
	$forum   = isset($args['forum'  ]) ? $args['forum'  ] : null;

	$handle = core::handle();
	$table_topic = core::table('topic');

	$sortings_asc  = array('topic', 'forum', 'creator', 'creator_addr', 'created', 'name', 'comment', 'mess_count', 'first_mess');
	$sortings_desc = array('last_stamp');
	if (in_array($sorting, $sortings_asc )) { if (is_null($reverse)) $reverse = false; } else
	if (in_array($sorting, $sortings_desc)) { if (is_null($reverse)) $reverse = true ; } else
						{ $sorting = 'last_stamp' ; $reverse = true ; }
	$reverse = ($reverse ? 'desc' : 'asc');
	$filterclause = $this->__filterclause_topic($handle, $parent, $itemid, $forum);
	$sql =
	"
		select `topic`, `forum`, `creator`, `creator_addr`, `created`, `name`, `comment`, `last_stamp`, `mess_count`, `first_mess`
		  from {$table_topic}
		 where {$filterclause}
		 order by `{$sorting}` {$reverse}, `topic` asc
		 limit {$size} offset {$offset}
	";
//???	core::event('query', "Select page of topics from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = array();
	while ($row = mysql_fetch_row($res))
	{
		$result[$row[0]] = array(
			'topic'			=> $row[0],
			'forum'			=> $row[1],
			'creator'		=> $row[2],
			'creator_addr'		=> $row[3],
			'created'		=> $this->__splitdate($row[4]),
			'name'			=> $row[5],
			'comment'		=> $row[6],
			'last_stamp'		=> $this->__splitdate($row[7]),
			'mess_count'		=> $row[8],
			'first_mess'		=> $row[9]);
	}
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function insert_topic ($args)
{//args: itemnew; returns itemid
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null; if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_topic = core::table('topic');

	foreach ($itemnew as $key => $val) $itemnew[$key] = !is_scalar($val) || ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$sql =
	"
		insert into {$table_topic} (
			 `topic`
			,`forum`
			,`creator`
			,`creator_addr`
			,`created`
			,`name`
			,`comment`
			,`last_stamp`
			,`mess_count`
			,`first_mess`
		) values (
			 default
			,{$itemnew['forum']}
			,{$itemnew['creator']}
			,{$itemnew['creator_addr']}
			,now()
			,{$itemnew['name']}
			,{$itemnew['comment']}
			,default
			,default
			,default
		)
	";
//???	core::event('query', "Insert topic to database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new forum_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
	return mysql_insert_id($handle);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function update_topic ($args)
{//args: itemid, itemnew
	$itemid  = isset($args['itemid']) ? $args['itemid'] : null;
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null;
	if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_topic = core::table('topic');

	foreach ($itemnew as $key => $val) $itemnew[$key] = !is_scalar($val) || ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	$sql =
	"
		update {$table_topic} set
			 `forum`   = {$itemnew['forum'  ]}
			,`name`    = {$itemnew['name'   ]}
			,`comment` = {$itemnew['comment']}
		where `topic` = {$itemid}
	";
//???	core::event('query', "Update topic in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new forum_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function delete_topic ($args)
{//args: itemid
	$itemid = isset($args['itemid']) ? $args['itemid'] : null;

	$handle = core::handle();
	$table_topic = core::table('topic');

	$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	$sql =
	"
		delete from {$table_topic}
		 where `topic` = {$itemid}
	";
//???	core::event('query', "Delete topic from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_forums_count ($args)
{
	$parent     = isset($args['parent'    ]) ? $args['parent'    ] : null;
	$itemid     = isset($args['itemid'    ]) ? $args['itemid'    ] : null;
	$grants     = isset($args['grants'    ]) ? $args['grants'    ] : null;

	$handle = core::handle();
	$table_forum = core::table('forum');

	$filterclause = $this->__filterclause_forums($handle, $parent, $itemid, $grants);
	$sql =
	"
		select count(*)
		  from {$table_forum}
		 where {$filterclause}
	";
//???	core::event('query', "Select number of messages in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = null;
	while ($row = mysql_fetch_row($res))
		$result = $row[0];
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_forums_data ($args)
{
	$parent  = isset($args['parent' ]) ? $args['parent' ] : null;
	$itemid  = isset($args['itemid' ]) ? $args['itemid' ] : null;
	$page    = isset($args['page'   ]) ? $args['page'   ] : null; $page   = (integer) $page  ;
	$size    = isset($args['size'   ]) ? $args['size'   ] : null; $size   = (integer) $size  ;
	$skip    = isset($args['skip'   ]) ? $args['skip'   ] : null; $skip   = (integer) $skip  ;
	$count   = isset($args['count'  ]) ? $args['count'  ] : null; $count  = (integer) $count ;
	$offset  = isset($args['offset' ]) ? $args['offset' ] : null; $offset = (integer) $offset;
	$sorting = isset($args['sorting']) ? $args['sorting'] : null;
	$reverse = isset($args['reverse']) ? $args['reverse'] : null;
	$grants     = isset($args['grants'    ]) ? $args['grants'    ] : null;

	$handle = core::handle();
	$table_forum = core::table('forum');

	$sortings_asc  = array('forum', 'grant', 'name', 'comment');
	$sortings_desc = array();
	if (in_array($sorting, $sortings_asc )) { if (is_null($reverse)) $reverse = false; } else
	if (in_array($sorting, $sortings_desc)) { if (is_null($reverse)) $reverse = true ; } else
						{ $sorting = 'name'    ; $reverse = false; }
	$reverse = ($reverse ? 'desc' : 'asc');
	$filterclause = $this->__filterclause_forums($handle, $parent, $itemid, $grants);
	$sql =
	"
		select `forum`, `grant`, `name`, `comment`
		  from {$table_forum}
		 where {$filterclause}
		 order by `{$sorting}` {$reverse}, `forum` asc
		 limit {$size} offset {$offset}
	";
//???	core::event('query', "Select page of messages from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = array();
	while ($row = mysql_fetch_row($res))
	{
		$result[$row[0]] = array(
			'forum'			=> $row[0],
			'grant'			=> $row[1],
			'name'			=> $row[2],
			'comment'		=> $row[3]);
	}
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function insert_forum ($args)
{//args: itemnew; returns itemid
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null; if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_forum = core::table('forum');

	foreach ($itemnew as $key => $val) $itemnew[$key] = !is_scalar($val) || ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$sql =
	"
		insert into {$table_forum} (
			 `forum`
			,`grant`
			,`name`
			,`comment`
		) values (
			 default
			,{$itemnew['grant'  ]}
			,{$itemnew['name'   ]}
			,{$itemnew['comment']}
		)
	";
//???	core::event('query', "Insert message to database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new forum_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
	return mysql_insert_id($handle);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function update_forum ($args)
{//args: itemid, itemnew
	$itemid  = isset($args['itemid']) ? $args['itemid'] : null;
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null;
	if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_forum = core::table('forum');

	$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	foreach ($itemnew as $key => $val) $itemnew[$key] = !is_scalar($val) || ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$sql =
	"
		update {$table_forum} set
			 `grant`	= {$itemnew['grant'  ]}
			,`name`		= {$itemnew['name'   ]}
			,`comment`	= {$itemnew['comment']}
		where `forum` = {$itemid}
	";
//???	core::event('query', "Update message in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new forum_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function delete_forum ($args)
{//args: itemid
	$itemid = isset($args['itemid']) ? $args['itemid'] : null;

	$handle = core::handle();
	$table_forum = core::table('forum');

	$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	$sql =
	"
		delete from {$table_forum}
		 where `forum` = {$itemid}
	";
//???	core::event('query', "Delete message from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function recalculate_topic ($args)
{
	$id = isset($args['id']) ? $args['id'] : null;

	$handle = core::handle();
	$table_topic = core::table('topic');
	$table_message = core::table('message');

	$sql =
	"
		update {$table_topic} set
			 `last_stamp` = (select max(`created`) from {$table_message} where `topic` = {$id})
			,`mess_count` = (select count(*)       from {$table_message} where `topic` = {$id})
			,`first_mess` = (select `message`      from {$table_message} where `topic` = {$id} order by `message` asc limit 1)
		where `topic` = {$id}
	";
//???	core::event('query', "Delete message from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_forum_info ($args)
{
	$itemids = isset($args['itemids']) ? $args['itemids'] : null;
	if (!is_array($itemids)) $itemids = array();

	$handle = core::handle();
	$table_forum = core::table('forum');

	foreach ($itemids as $key => $val) $itemids[$key] = !is_scalar($val) || ($val == '') ? "null" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$itemids = empty($itemids) ? "null" : implode(",", $itemids);
	$sql =
	"
		select `forum`, `grant`, `name`, `comment`
		  from {$table_forum}
		 where `forum` in ({$itemids})
	";
//???	core::event('query', "Select page of forums from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = array();
	while ($row = mysql_fetch_row($res))
	{
		$result[$row[0]] = array(
			'forum'		=> $row[0],
			'grant'			=> $row[1],
			'name'			=> $row[2],
			'comment'		=> $row[3]);
	}
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_topic_info ($args)
{
	$itemids = isset($args['itemids']) ? $args['itemids'] : null;
	if (!is_array($itemids)) $itemids = array();

	$handle = core::handle();
	$table_topic = core::table('topic');

	foreach ($itemids as $key => $val) $itemids[$key] = !is_scalar($val) || ($val == '') ? "null" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$itemids = empty($itemids) ? "null" : implode(",", $itemids);
	$sql =
	"
		select `topic`, `forum`, `creator`, `creator_addr`, `created`, `name`, `comment`, `last_stamp`, `mess_count`, `first_mess`
		  from {$table_topic}
		 where `topic` in ({$itemids})
	";
//???	core::event('query', "Select page of topics from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = array();
	while ($row = mysql_fetch_row($res))
	{
		$result[$row[0]] = array(
			'topic'			=> $row[0],
			'forum'			=> $row[1],
			'creator'		=> $row[2],
			'creator_addr'		=> $row[3],
			'created'		=> $this->__splitdate($row[4]),
			'name'			=> $row[5],
			'comment'		=> $row[6],
			'last_stamp'		=> $this->__splitdate($row[7]),
			'mess_count'		=> $row[8],
			'first_mess'		=> $row[9]);
	}
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __filterclause_message ($handle, $parent, $itemid, $topic)
{
	$result = array();

//???	// parent is ognored in messages
//???	if (isset($parent)
//???	{
//???		$parent = "'" . mysql_real_escape_string($parent, $handle) . "'";
//???		$result[] = "(`parent` = {$parent})";
//???	}

	if (isset($itemid))
	{
		$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
		$result[] = "(`message` = {$itemid})";
	} else
	{
		$topic = "'" . mysql_real_escape_string($topic, $handle) . "'";
		$result[] = "(`topic` = {$topic})";
	}

	$result = implode(" and ", $result);
	$result = $result != '' ? "(" . $result . ")" : "true";
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __filterclause_topic ($handle, $parent, $itemid, $forum)
{
	$result = array();

//???	// parent is ognored in messages
//???	if (isset($parent)
//???	{
//???		$parent = "'" . mysql_real_escape_string($parent, $handle) . "'";
//???		$result[] = "(`parent` = {$parent})";
//???	}

	if (isset($itemid))
	{
		$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
		$result[] = "(`topic` = {$itemid})";
	} else
	{
		$forum = "'" . mysql_real_escape_string($forum, $handle) . "'";
		$result[] = "(`forum` = {$forum})";
	}

//???	$result[] = "(`mess_count` is null or `mess_count` > 0)";
	$result[] = "(`mess_count` > 0)";

	$result = implode(" and ", $result);
	$result = $result != '' ? "(" . $result . ")" : "true";
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __filterclause_forums ($handle, $parent, $itemid, $grants)
{
	$result = array();

//???	// parent is ognored in messages
//???	if (isset($parent)
//???	{
//???		$parent = "'" . mysql_real_escape_string($parent, $handle) . "'";
//???		$result[] = "(`parent` = {$parent})";
//???	}

	if (isset($itemid))
	{
		$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
		$result[] = "(`forum` = {$itemid})";
	}

	if (isset($grants))
	{
		$sqlvalues = array();
		if (is_array($grants))
			foreach ($grants as $i)
				$sqlvalues[] = "'" . mysql_real_escape_string($i, $handle) . "'";
		$sqlvalues = implode(',', $sqlvalues);
		$result[] = ($sqlvalues == '') ?
			"(`grant` is null)" :
			"(`grant` is null or `grant` in ({$sqlvalues}))";
	}

	$result = implode(" and ", $result);
	$result = $result != '' ? "(" . $result . ")" : "true";
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __splitdate ($value)
{	
	if ($value == '')
	{
		$result = null;
	} else
	{
		$parts = split('[- :]', $value);
		if (count($parts) < 6)
			$parts = array_pad($parts, 6, null);
		$result = array(
			'year'		=> $parts[0],
			'month'		=> $parts[1],
			'day'		=> $parts[2],
			'hour'		=> $parts[3],
			'minute'	=> $parts[4],
			'second'	=> $parts[5]);
	}
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

?>