<?php defined('CORENINPAGE') or die('Hack!');
//todo: 1. add additional data to core::event()s in some methods. for ex. itemid and so on...

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!core::depend('links_0')) return; // for exceptions

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class links_0_mysql extends dbworker
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_links_count ($args)
{
	$parent = isset($args['parent']) ? $args['parent'] : null;
	$itemid = isset($args['itemid']) ? $args['itemid'] : null;

	$handle = core::handle();
	$table_link = core::table('link');

	$filterclause = $this->__filterclause($handle, $parent, $itemid);
	$sql =
	"
		select count(*)
		  from {$table_link}
		 where {$filterclause}
	";
//???	core::event('query', "Select number of links in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = null;
	while ($row = mysql_fetch_row($res))
		$result = $row[0];
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_links_data ($args)
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
	$table_link = core::table('link');

	$sortings_asc  = array('link', 'parent', 'order', 'uri', 'text', 'hint', 'target');
	$sortings_desc = array();
	if (in_array($sorting, $sortings_asc )) { if (is_null($reverse)) $reverse = false; } else
	if (in_array($sorting, $sortings_desc)) { if (is_null($reverse)) $reverse = true ; } else
						{ $sorting = 'parent'  ; $reverse = false; }
	$reverse = ($reverse ? 'desc' : 'asc');
	$filterclause = $this->__filterclause($handle, $parent, $itemid);
	$sql =
	"
		select `link`, `parent`, `order`, `uri`, `text`, `hint`, `target`
		  from {$table_link}
		 where {$filterclause}
		 order by `{$sorting}` {$reverse}, `parent` asc, `order` asc, `link` asc
		 limit {$size} offset {$offset}
	";
//???	core::event('query', "Select page of links from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = array();
	while ($row = mysql_fetch_row($res))
	{
		$result[$row[0]] = array(
			'link'		=> $row[ 0],
			'parent'	=> $row[ 1],
			'order'		=> $row[ 2],
			'uri'		=> $row[ 3],
			'text'		=> $row[ 4],
			'hint'		=> $row[ 5],
			'target'	=> $row[ 6]);
	}
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function insert_link ($args)
{//args: itemnew; returns itemid
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null;
	if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_link = core::table('link');

	foreach ($itemnew as $key => $val) $itemnew[$key] = !is_scalar($val) || ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$sql =
	"
		insert into {$table_link} (
			 `link`
			,`parent`
			,`order`
			,`uri`
			,`text`
			,`hint`
			,`target`
		) values (
			 default
			,{$itemnew['parent']}
			,{$itemnew['order' ]}
			,{$itemnew['uri'   ]}
			,{$itemnew['text'  ]}
			,{$itemnew['hint'  ]}
			,{$itemnew['target']}
		)
	";
//???	core::event('query', "Insert link to database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new gallery_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
	return mysql_insert_id($handle);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function update_link ($args)
{//args: itemid, itemnew
	$itemid  = isset($args['itemid' ]) ? $args['itemid' ] : null;
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null;
	if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_link = core::table('link');

	foreach ($itemnew as $key => $val) $itemnew[$key] = !is_scalar($val) || ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	$sql =
	"
		update {$table_link} set
			 `parent`	= {$itemnew['parent']}
			,`order`	= {$itemnew['order' ]}
			,`uri`		= {$itemnew['uri'   ]}
			,`text`		= {$itemnew['text'  ]}
			,`hint`		= {$itemnew['hint'  ]}
			,`target`	= {$itemnew['target']}
		where `link` = {$itemid}
	";
//???	core::event('query', "Update link in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new gallery_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function delete_link ($args)
{//args: itemid
	$itemid = isset($args['itemid']) ? $args['itemid'] : null;

	$handle = core::handle();
	$table_link = core::table('link');

	$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	$sql =
	"
		delete from {$table_link}
		 where `link` = {$itemid}
	";
//???	core::event('query', "Delete link from database.", array('query'=>$sql));
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
		$result[] = "(`link` = {$itemid})";
	}

	$result = implode(" and ", $result);
	$result = $result != '' ? "(" . $result . ")" : "true";
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

?>