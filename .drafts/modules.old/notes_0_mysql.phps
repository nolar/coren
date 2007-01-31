<?php defined('CORENINPAGE') or die('Hack!');
//todo: 1. add additional data to core::event()s in some methods. for ex. itemid and so on...

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!core::depend('notes_0')) return; // for exceptions

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class notes_0_mysql extends dbworker
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_notes_count ($args)
{
	$parent = isset($args['parent']) ? $args['parent'] : null;
	$itemid = isset($args['itemid']) ? $args['itemid'] : null;

	$handle = core::handle();
	$table_note = core::table('note');

	$filterclause = $this->__filterclause($handle, $parent, $itemid);
	$sql =
	"
		select count(*)
		  from {$table_note}
		 where {$filterclause}
	";
//???	core::event('query', "Select number of notes in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = null;
	while ($row = mysql_fetch_row($res))
		$result = $row[0];
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_notes_data ($args)
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

	$handle = core::handle();
	$table_note = core::table('note');

	$sortings_asc  = array('note', 'parent', 'author', 'email', 'text', 'reply');
	$sortings_desc = array('posted', 'replied');
	if (in_array($sorting, $sortings_asc )) { if (is_null($reverse)) $reverse = false; } else
	if (in_array($sorting, $sortings_desc)) { if (is_null($reverse)) $reverse = true ; } else
						{ $sorting = 'note'    ; $reverse = false; }
	$reverse = ($reverse ? 'desc' : 'asc');
	$filterclause = $this->__filterclause($handle, $parent, $itemid);
	$sql =
	"
		select `note`, `parent`, `author`, `email`, `text`, `reply`, `posted`, `replied`
		  from {$table_note}
		 where {$filterclause}
		 order by `{$sorting}` {$reverse}, `note` asc
		 limit {$size} offset {$offset}
	";
//???	core::event('query', "Select page of notes from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = array();
	while ($row = mysql_fetch_row($res))
	{
		$result[$row[0]] = array(
			'note'		=> $row[ 0],
			'parent'	=> $row[ 1],
			'author'	=> $row[ 2],
			'email'		=> $row[ 3],
			'text'		=> $row[ 4],
			'reply'		=> $row[ 5],
			'posted'	=> $this->__splitdate($row[6]),
			'replied'	=> $this->__splitdate($row[7]));
	}
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function post_note ($args)
{//args: itemnew; returns itemid
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null;
	if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_note = core::table('note');

	foreach ($itemnew as $key => $val) $itemnew[$key] = !is_scalar($val) || ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$sql =
	"
		insert into {$table_note} (
			 `note`
			,`parent`
			,`author`
			,`email`
			,`text`
			,`reply`
			,`posted`
			,`replied`
		) values (
			 default
			,{$itemnew['parent']}
			,{$itemnew['author']}
			,{$itemnew['email' ]}
			,{$itemnew['text'  ]}
			,default
			,now()
			,null
		)
	";
//???	core::event('query', "Insert note to database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new gallery_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
	return mysql_insert_id($handle);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function reply_note ($args)
{//args: itemid, itemnew
	$itemid  = isset($args['itemid' ]) ? $args['itemid' ] : null;
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null;
	if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_note = core::table('note');

	$posted  = is_null($itemnew['posted']) ? "default" : sprintf("'%04d-%02d-%02d %02d:%02d:%02d'", (integer) $itemnew['posted']['year'], (integer) $itemnew['posted']['month'], (integer) $itemnew['posted']['day'], (integer) $itemnew['posted']['hour'], (integer) $itemnew['posted']['minute'], (integer) $itemnew['posted']['second']);
	$replied = is_null($itemnew['replied']) ? "default" : sprintf("'%04d-%02d-%02d %02d:%02d:%02d'", (integer) $itemnew['replied']['year'], (integer) $itemnew['replied']['month'], (integer) $itemnew['replied']['day'], (integer) $itemnew['replied']['hour'], (integer) $itemnew['replied']['minute'], (integer) $itemnew['replied']['second']);
	foreach ($itemnew as $key => $val) $itemnew[$key] = !is_scalar($val) || ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	$sql =
	"
		update {$table_note} set
			 `reply`        = {$itemnew['reply'     ]}
			,`replied`      = now()
		where `note` = {$itemid}
	";
//???	core::event('query', "Update note in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new gallery_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function insert_note ($args)
{//args: itemnew; returns itemid
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null;
	if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_note = core::table('note');

	$posted  = is_null($itemnew['posted']) ? "default" : sprintf("'%04d-%02d-%02d %02d:%02d:%02d'", (integer) $itemnew['posted']['year'], (integer) $itemnew['posted']['month'], (integer) $itemnew['posted']['day'], (integer) $itemnew['posted']['hour'], (integer) $itemnew['posted']['minute'], (integer) $itemnew['posted']['second']);
	$replied = is_null($itemnew['replied']) ? "default" : sprintf("'%04d-%02d-%02d %02d:%02d:%02d'", (integer) $itemnew['replied']['year'], (integer) $itemnew['replied']['month'], (integer) $itemnew['replied']['day'], (integer) $itemnew['replied']['hour'], (integer) $itemnew['replied']['minute'], (integer) $itemnew['replied']['second']);
	foreach ($itemnew as $key => $val) $itemnew[$key] = !is_scalar($val) || ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$sql =
	"
		insert into {$table_note} (
			 `note`
			,`parent`
			,`author`
			,`email`
			,`text`
			,`reply`
			,`posted`
			,`replied`
		) values (
			 default
			,{$itemnew['parent']}
			,{$itemnew['author' ]}
			,{$itemnew['email'   ]}
			,{$itemnew['text'  ]}
			,{$itemnew['reply'  ]}
			,{$posted}
			,{$replied}
		)
	";
//???	core::event('query', "Insert note to database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new gallery_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
	return mysql_insert_id($handle);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function update_note ($args)
{//args: itemid, itemnew
	$itemid  = isset($args['itemid' ]) ? $args['itemid' ] : null;
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null;
	if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_note = core::table('note');

	$posted  = is_null($itemnew['posted']) ? "default" : sprintf("'%04d-%02d-%02d %02d:%02d:%02d'", (integer) $itemnew['posted']['year'], (integer) $itemnew['posted']['month'], (integer) $itemnew['posted']['day'], (integer) $itemnew['posted']['hour'], (integer) $itemnew['posted']['minute'], (integer) $itemnew['posted']['second']);
	$replied = is_null($itemnew['replied']) ? "default" : sprintf("'%04d-%02d-%02d %02d:%02d:%02d'", (integer) $itemnew['replied']['year'], (integer) $itemnew['replied']['month'], (integer) $itemnew['replied']['day'], (integer) $itemnew['replied']['hour'], (integer) $itemnew['replied']['minute'], (integer) $itemnew['replied']['second']);
	foreach ($itemnew as $key => $val) $itemnew[$key] = !is_scalar($val) || ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	$sql =
	"
		update {$table_note} set
			 `parent`        = {$itemnew['parent'      ]}
			,`author`         = {$itemnew['author'      ]}
			,`email`       = {$itemnew['email'      ]}
			,`text`       = {$itemnew['text'      ]}
			,`reply`      = {$itemnew['reply'     ]}
			,`posted`      = {$posted}
			,`replied`      = {$replied}
		where `note` = {$itemid}
	";
//???	core::event('query', "Update note in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new gallery_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function delete_note ($args)
{//args: itemid
	$itemid = isset($args['itemid']) ? $args['itemid'] : null;

	$handle = core::handle();
	$table_note = core::table('note');

	$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	$sql =
	"
		delete from {$table_note}
		 where `note` = {$itemid}
	";
//???	core::event('query', "Delete note from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __filterclause ($handle, $parent, $itemid)
{
	$result = array();

	if (isset($parent))
	{
		$parent = "'" . mysql_real_escape_string($parent, $handle) . "'";
		$result[] = "(`parent` = {$parent})";
	}

	if (isset($itemid))
	{
		$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
		$result[] = "(`note` = {$itemid})";
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