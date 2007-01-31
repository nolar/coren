<?php defined('CORENINPAGE') or die('Hack!');
//todo: 1. add additional data to core::event()s in some methods. for ex. itemid and so on...

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!core::depend('admin_grantgroups_0')) return; // for exceptions

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class admin_grantgroups_0_mysql extends dbworker
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
	$reverse = null;//todo via $filter in module
	$sorting = null;//todo via $filter in module

	$handle = core::handle();
	$table_group = core::table('group');

	$reverse = ($reverse ? 'asc' : 'desc');
	$sorting = ('codename');
	$filterclause = $this->__filterclause($handle, $parent, $itemid);
	if ($filterclause == '') $filterclause = "1=1";
	$sql =
	"
		select `group`, `codename`, `comment`
		  from {$table_group}
		 where {$filterclause}
		 order by `{$sorting}` {$reverse}, `group` asc
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

public function select_assignment ($args)
{
	$parent     = isset($args['parent'    ]) ? $args['parent'    ] : null;

	$handle = core::handle();
	$table_assignment = core::table('assignment');

	$parent = "'" . mysql_real_escape_string($parent, $handle) . "'";
	$sql =
	"
		select `group`
		  from {$table_assignment}
		 where `grant` = {$parent}
	";
//???	core::event('query', "Select page of groups from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = array();
	while ($row = mysql_fetch_row($res))
		$result[] = $row[0];
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function assign_group ($args)
{
	$grant = isset($args['grant']) ? $args['grant'] : null;
	$group = isset($args['group']) ? $args['group'] : null;

	$handle = core::handle();
	$table_assignment = core::table('assignment');

	$grant = "'" . mysql_real_escape_string($grant, $handle) . "'";
	$group = "'" . mysql_real_escape_string($group, $handle) . "'";
	$sql =
	"
		replace into {$table_assignment} (
			 `grant`
			,`group`
		) values (
			 {$grant}
			,{$group}
		)
	";
//???	core::event('query', "Insert group to database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
	return mysql_insert_id($handle);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function revoke_group ($args)
{//args: itemid
	$grant = isset($args['grant']) ? $args['grant'] : null;
	$group = isset($args['group']) ? $args['group'] : null;

	$handle = core::handle();
	$table_assignment = core::table('assignment');

	$grant = "'" . mysql_real_escape_string($grant, $handle) . "'";
	$group = "'" . mysql_real_escape_string($group, $handle) . "'";
	$sql =
	"
		delete from {$table_assignment}
		 where `grant` = {$grant} and `group` = {$group}
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

//???	// parent is ognored in accounts
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