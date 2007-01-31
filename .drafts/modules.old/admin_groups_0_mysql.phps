<?php defined('CORENINPAGE') or die('Hack!');
//todo: 1. add additional data to core::event()s in some methods. for ex. itemid and so on...

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!core::depend('admin_groups_0')) return; // for exceptions

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class admin_groups_0_mysql extends dbworker
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_groups_count ($args)
{
	$parent     = isset($args['parent'    ]) ? $args['parent'    ] : null;
	$itemid     = isset($args['itemid'    ]) ? $args['itemid'    ] : null;

	$handle = core::handle();
	$table_group = core::table('group');

	$filterclause = $this->__filterclause($handle, $parent, $itemid);
	if ($filterclause == '') $filterclause = "1=1";
	$sql =
	"
		select count(*)
		  from {$table_group}
		 where {$filterclause}
	";
//???	core::event('query', "Select number of groups in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = null;
	while ($row = mysql_fetch_row($res))
		$result = $row[0];
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_groups_data ($args)
{
	$parent     = isset($args['parent'    ]) ? $args['parent'    ] : null;
	$itemid     = isset($args['itemid'    ]) ? $args['itemid'    ] : null;
	$page   = isset($args['page'  ]) ? $args['page'  ] : null; $page   = (integer) $page  ;
	$size   = isset($args['size'  ]) ? $args['size'  ] : null; $size   = (integer) $size  ;
	$skip   = isset($args['skip'  ]) ? $args['skip'  ] : null; $skip   = (integer) $skip  ;
	$count  = isset($args['count' ]) ? $args['count' ] : null; $count  = (integer) $count ;
	$offset = isset($args['offset']) ? $args['offset'] : null; $offset = (integer) $offset;
	$reverse = null;//todo via $filter in module
	$sorting = null;//todo via $filter in module

	$handle = core::handle();
	$table_group = core::table('group');

	$reverse = ($reverse ? 'desc' : 'asc');
	$sorting =
//!!!		($sorting == 'disabled' ? 'disabled' :
//!!!		($sorting == 'created'  ? 'created'  :
//!!!		($sorting == 'touched'  ? 'touched'  :
//!!!		($sorting == 'logname'  ? 'logname'  :
		('codename');
//!!!		))));
	$filterclause = $this->__filterclause($handle, $parent, $itemid);
	if ($filterclause == '') $filterclause = "1=1";
	$sql =
	"
		select `group`, `codename`, `comment`
		  from {$table_group}
		 where {$filterclause}
		 order by `{$sorting}` {$reverse}, `group` asc
		 limit {$size} offset {$offset}
	";
//???	core::event('query', "Select page of groups from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = array();
	while ($row = mysql_fetch_row($res))
	{
		$result[$row[0]] = array(
			'group'			=> $row[0],
			'codename'		=> $row[1],
			'comment'		=> $row[2]);
	}
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function insert_group ($args)
{//args: itemnew; returns itemid
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null; if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_group = core::table('group');

	foreach ($itemnew as $key => $val) $itemnew[$key] = ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$sql =
	"
		insert into {$table_group} (
			 `group`
			,`codename`
			,`comment`
		) values (
			 default
			,{$itemnew['codename']}
			,{$itemnew['comment']}
		)
	";
//???	core::event('query', "Insert group to database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new admin_groups_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
	return mysql_insert_id($handle);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function update_group ($args)
{//args: itemid, itemnew
	$itemid  = isset($args['itemid']) ? $args['itemid'] : null;
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null;
	if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_group = core::table('group');

	$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	foreach ($itemnew as $key => $val) $itemnew[$key] = ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$sql =
	"
		update {$table_group} set
			 `codename`	= {$itemnew['codename']}
			,`comment`	= {$itemnew['comment']}
		where `group` = {$itemid}
	";
//???	core::event('query', "Update group in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new admin_groups_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function delete_group ($args)
{//args: itemid
	$itemid = isset($args['itemid']) ? $args['itemid'] : null;

	$handle = core::handle();
	$table_group = core::table('group');

	$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	$sql =
	"
		delete from {$table_group}
		 where `group` = {$itemid}
	";
//???	core::event('query', "Delete group from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __filterclause ($handle, $parent, $itemid)
{
	$result = array();

//???	// parent is ognored in groups
//???	if (isset($parent)
//???	{
//???		$parent = "'" . mysql_real_escape_string($parent, $handle) . "'";
//???		$result[] = "(`parent` = {$parent})";
//???	}

	if (isset($itemid))
	{
		$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
		$result[] = "(`group` = {$itemid})";
	}

	$result = implode(" and ", $result);
	if ($result != '') $result = "(" . $result . ")";
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

?>