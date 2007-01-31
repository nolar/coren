<?php defined('CORENINPAGE') or die('Hack!');
//todo: 1. add additional data to core::event()s in some methods. for ex. itemid and so on...

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!core::depend('files_0')) return; // for exceptions

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class files_0_mysql extends dbworker
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_files_count ($args)
{
	$parent = isset($args['parent']) ? $args['parent'] : null;
	$itemid = isset($args['itemid']) ? $args['itemid'] : null;

	$handle = core::handle();
	$table_file = core::table('file');

	$filterclause = $this->__filterclause($handle, $parent, $itemid);
	$sql =
	"
		select count(*)
		  from {$table_file}
		 where {$filterclause}
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

public function select_files_data ($args)
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
	$table_file = core::table('file');

	$sortings_asc  = array('file', 'parent', 'order', 'caption', 'comment'/*todo: and other fields*/);
	$sortings_desc = array();
	if (in_array($sorting, $sortings_asc )) { if (is_null($reverse)) $reverse = false; } else
	if (in_array($sorting, $sortings_desc)) { if (is_null($reverse)) $reverse = true ; } else
						{ $sorting = 'parent'  ; $reverse = false; }
	$reverse = ($reverse ? 'desc' : 'asc');
	$filterclause = $this->__filterclause($handle, $parent, $itemid);
	$sql =
	"
		select `file`, `parent`, `order`, `caption`, `comment`,
					`file_storage`, `file_mime`, `file_name`, `file_size`
		  from {$table_file}
		 where {$filterclause}
		 order by `{$sorting}` {$reverse}, `parent` asc, `order` asc, `file` asc
		 limit {$size} offset {$offset}
	";
//???	core::event('query', "Select page of files from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = array();
	while ($row = mysql_fetch_row($res))
	{
		$result[$row[0]] = array(
			'file'			=> $row[ 0],
			'parent'		=> $row[ 1],
			'order'			=> $row[ 2],
			'caption'		=> $row[ 3],
			'comment'		=> $row[ 4],
			'file_storage'		=> $row[ 5],
			'file_mime'		=> $row[ 6],
			'file_name'		=> $row[ 7],
			'file_size'		=> $row[ 8]);
	}
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function insert_file ($args)
{//args: itemnew; returns itemid
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null;
	if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_file = core::table('file');

	foreach ($itemnew as $key => $val) $itemnew[$key] = !is_scalar($val) || ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$sql =
	"
		insert into {$table_file} (
			 `file`
			,`parent`
			,`order`
			,`caption`
			,`comment`

			,`file_storage`
			,`file_mime`
			,`file_name`
			,`file_size`
		) values (
			 default
			,{$itemnew['parent'       ]}
			,{$itemnew['order'        ]}
			,{$itemnew['caption'      ]}
			,{$itemnew['comment'      ]}

			,{$itemnew['file_storage']}
			,{$itemnew['file_mime'   ]}
			,{$itemnew['file_name'   ]}
			,{$itemnew['file_size'   ]}
		)
	";
//???	core::event('query', "Insert file to database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new gallery_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
	return mysql_insert_id($handle);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function update_file ($args)
{//args: itemid, itemnew
	$itemid  = isset($args['itemid' ]) ? $args['itemid' ] : null;
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null;
	if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_file = core::table('file');

	foreach ($itemnew as $key => $val) $itemnew[$key] = !is_scalar($val) || ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	$sql =
	"
		update {$table_file} set
			 `parent`        = {$itemnew['parent'      ]}
			,`order`         = {$itemnew['order'      ]}
			,`caption`       = {$itemnew['caption'      ]}
			,`comment`       = {$itemnew['comment'      ]}

			,`file_storage` = {$itemnew['file_storage']}
			,`file_mime`    = {$itemnew['file_mime'   ]}
			,`file_name`    = {$itemnew['file_name'   ]}
			,`file_size`    = {$itemnew['file_size'   ]}
		where `file` = {$itemid}
	";
//???	core::event('query', "Update file in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new gallery_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function delete_file ($args)
{//args: itemid
	$itemid = isset($args['itemid']) ? $args['itemid'] : null;

	$handle = core::handle();
	$table_file = core::table('file');

	$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	$sql =
	"
		delete from {$table_file}
		 where `file` = {$itemid}
	";
//???	core::event('query', "Delete file from database.", array('query'=>$sql));
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
		$result[] = "(`file` = {$itemid})";
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