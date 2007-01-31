<?php defined('CORENINPAGE') or die('Hack!');
//todo: 1. add additional data to core::event()s in some methods. for ex. itemid and so on...

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!core::depend('admin_grants_0')) return; // for exceptions

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class admin_grants_0_mysql extends dbworker
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
	$page   = isset($args['page'  ]) ? $args['page'  ] : null; $page   = (integer) $page  ;
	$size   = isset($args['size'  ]) ? $args['size'  ] : null; $size   = (integer) $size  ;
	$skip   = isset($args['skip'  ]) ? $args['skip'  ] : null; $skip   = (integer) $skip  ;
	$count  = isset($args['count' ]) ? $args['count' ] : null; $count  = (integer) $count ;
	$offset = isset($args['offset']) ? $args['offset'] : null; $offset = (integer) $offset;
	$reverse = null;//todo via $filter in module
	$sorting = null;//todo via $filter in module

	$handle = core::handle();
	$table_grant = core::table('grant');

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
		select `grant`, `codename`, `comment`
		  from {$table_grant}
		 where {$filterclause}
		 order by `{$sorting}` {$reverse}, `grant` asc
		 limit {$size} offset {$offset}
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
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function insert_grant ($args)
{//args: itemnew; returns itemid
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null; if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_grant = core::table('grant');

	foreach ($itemnew as $key => $val) $itemnew[$key] = ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$sql =
	"
		insert into {$table_grant} (
			 `grant`
			,`codename`
			,`comment`
		) values (
			 default
			,{$itemnew['codename']}
			,{$itemnew['comment']}
		)
	";
//???	core::event('query', "Insert grant to database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new admin_grants_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
	return mysql_insert_id($handle);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function update_grant ($args)
{//args: itemid, itemnew
	$itemid  = isset($args['itemid']) ? $args['itemid'] : null;
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null;
	if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_grant = core::table('grant');

	$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	foreach ($itemnew as $key => $val) $itemnew[$key] = ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$sql =
	"
		update {$table_grant} set
			 `codename`	= {$itemnew['codename']}
			,`comment`	= {$itemnew['comment']}
		where `grant` = {$itemid}
	";
//???	core::event('query', "Update grant in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new admin_grants_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function delete_grant ($args)
{//args: itemid
	$itemid = isset($args['itemid']) ? $args['itemid'] : null;

	$handle = core::handle();
	$table_grant = core::table('grant');

	$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	$sql =
	"
		delete from {$table_grant}
		 where `grant` = {$itemid}
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

//???	// parent is ognored in grants
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