<?php defined('CORENINPAGE') or die('Hack!');
//todo: 1. add additional data to core::event()s in some methods. for ex. itemid and so on...

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!core::depend('admin_groupgrants_0')) return; // for exceptions

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class admin_groupgrants_0_mysql extends dbworker
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_grants_count ($args)
{
	$parent     = isset($args['parent'    ]) ? $args['parent'    ] : null;
	$itemid     = isset($args['itemid'    ]) ? $args['itemid'    ] : null;

	$handle = core::handle();
	$table_grant = core::table('grant');

	$filterclause = $this->__filterclause($handle, $parent, $itemid);
	if ($filterclause == '') $filterclause = "1=1";
	$sql =
	"
		select count(*)
		  from {$table_grant}
		 where {$filterclause}
	";
//???	core::event('query', "Select number of grants in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = null;
	while ($row = mysql_fetch_row($res))
		$result = $row[0];
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_grants_data ($args)
{
	$parent     = isset($args['parent'    ]) ? $args['parent'    ] : null;
	$itemid     = isset($args['itemid'    ]) ? $args['itemid'    ] : null;
	$reverse = null;//todo via $filter in module
	$sorting = null;//todo via $filter in module

	$handle = core::handle();
	$table_grant = core::table('grant');

	$reverse = ($reverse ? 'asc' : 'desc');
	$sorting = ('codename');
	$filterclause = $this->__filterclause($handle, $parent, $itemid);
	if ($filterclause == '') $filterclause = "1=1";
	$sql =
	"
		select `grant`, `codename`, `comment`
		  from {$table_grant}
		 where {$filterclause}
		 order by `{$sorting}` {$reverse}, `grant` asc
	";
//???	core::event('query', "Select page of grants from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = array();
	while ($row = mysql_fetch_row($res))
	{
		$result[$row[0]] = array(
			'grant'			=> $row[0],
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
		select `grant`
		  from {$table_assignment}
		 where `group` = {$parent}
	";
//???	core::event('query', "Select page of grants from database.", array('query'=>$sql));
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

function assign_grant ($args)
{
	$group = isset($args['group']) ? $args['group'] : null;
	$grant = isset($args['grant']) ? $args['grant'] : null;

	$handle = core::handle();
	$table_assignment = core::table('assignment');

	$group = "'" . mysql_real_escape_string($group, $handle) . "'";
	$grant = "'" . mysql_real_escape_string($grant, $handle) . "'";
	$sql =
	"
		replace into {$table_assignment} (
			 `group`
			,`grant`
		) values (
			 {$group}
			,{$grant}
		)
	";
//???	core::event('query', "Insert grant to database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
	return mysql_insert_id($handle);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function revoke_grant ($args)
{//args: itemid
	$group = isset($args['group']) ? $args['group'] : null;
	$grant = isset($args['grant']) ? $args['grant'] : null;

	$handle = core::handle();
	$table_assignment = core::table('assignment');

	$group = "'" . mysql_real_escape_string($group, $handle) . "'";
	$grant = "'" . mysql_real_escape_string($grant, $handle) . "'";
	$sql =
	"
		delete from {$table_assignment}
		 where `group` = {$group} and `grant` = {$grant}
	";
//???	core::event('query', "Delete grant from database.", array('query'=>$sql));
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
		$result[] = "(`grant` = {$itemid})";
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