<?php defined('CORENINPAGE') or die('Hack!');
//todo: 1. add additional data to core::event()s in some methods. for ex. itemid and so on...

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!core::depend('picts_0')) return; // for exceptions

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class picts_0_mysql extends dbworker
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_pictures_count ($args)
{
	$parent = isset($args['parent']) ? $args['parent'] : null;
	$itemid = isset($args['itemid']) ? $args['itemid'] : null;

	$handle = core::handle();
	$table_picture = core::table('picture');

	$filterclause = $this->__filterclause($handle, $parent, $itemid);
	$sql =
	"
		select count(*)
		  from {$table_picture}
		 where {$filterclause}
	";
//???	core::event('query', "Select number of pictures in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = null;
	while ($row = mysql_fetch_row($res))
		$result = $row[0];
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_pictures_data ($args)
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
	$table_picture = core::table('picture');

	$sortings_asc  = array('picture', 'parent', 'order', 'caption', 'comment', 'align', 'embed'/*todo: and other fields*/);
	$sortings_desc = array();
	if (in_array($sorting, $sortings_asc )) { if (is_null($reverse)) $reverse = false; } else
	if (in_array($sorting, $sortings_desc)) { if (is_null($reverse)) $reverse = true ; } else
						{ $sorting = 'parent'  ; $reverse = false; }
	$reverse = ($reverse ? 'desc' : 'asc');
	$filterclause = $this->__filterclause($handle, $parent, $itemid);
	$sql =
	"
		select `picture`, `parent`, `order`, `caption`, `comment`, `align`, `embed`,
					`image_storage`, `image_mime`, `image_name`, `image_size`, `image_xsize`, `image_ysize`,
			`thumb_mode`,	`thumb_storage`, `thumb_mime`, `thumb_name`, `thumb_size`, `thumb_xsize`, `thumb_ysize`
		  from {$table_picture}
		 where {$filterclause}
		 order by `{$sorting}` {$reverse}, `parent` asc, `order` asc, `picture` asc
		 limit {$size} offset {$offset}
	";
//???	core::event('query', "Select page of pictures from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = array();
	while ($row = mysql_fetch_row($res))
	{
		$result[$row[0]] = array(
			'picture'		=> $row[ 0],
			'parent'		=> $row[ 1],
			'order'			=> $row[ 2],
			'caption'		=> $row[ 3],
			'comment'		=> $row[ 4],
			'align'			=> $row[ 5],
			'embed'			=> $row[ 6],
			'image_storage'		=> $row[ 7],
			'image_mime'		=> $row[ 8],
			'image_name'		=> $row[ 9],
			'image_size'		=> $row[10],
			'image_xsize'		=> $row[11],
			'image_ysize'		=> $row[12],
			'thumb_mode'		=> $row[13],
			'thumb_storage'		=> $row[14],
			'thumb_mime'		=> $row[15],
			'thumb_name'		=> $row[16],
			'thumb_size'		=> $row[17],
			'thumb_xsize'		=> $row[18],
			'thumb_ysize'		=> $row[19]);
	}
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function insert_picture ($args)
{//args: itemnew; returns itemid
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null;
	if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_picture = core::table('picture');

	foreach ($itemnew as $key => $val) $itemnew[$key] = !is_scalar($val) || ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$sql =
	"
		insert into {$table_picture} (
			 `picture`
			,`parent`
			,`order`
			,`caption`
			,`comment`
			,`align`
			,`embed`

			,`image_storage`
			,`image_mime`
			,`image_name`
			,`image_size`
			,`image_xsize`
			,`image_ysize`

			,`thumb_mode`
			,`thumb_storage`
			,`thumb_mime`
			,`thumb_name`
			,`thumb_size`
			,`thumb_xsize`
			,`thumb_ysize`
		) values (
			 default
			,{$itemnew['parent'       ]}
			,{$itemnew['order'        ]}
			,{$itemnew['caption'      ]}
			,{$itemnew['comment'      ]}
			,{$itemnew['align'        ]}
			,{$itemnew['embed'        ]}

			,{$itemnew['image_storage']}
			,{$itemnew['image_mime'   ]}
			,{$itemnew['image_name'   ]}
			,{$itemnew['image_size'   ]}
			,{$itemnew['image_xsize'  ]}
			,{$itemnew['image_ysize'  ]}

			,{$itemnew['thumb_mode'   ]}
			,{$itemnew['thumb_storage']}
			,{$itemnew['thumb_mime'   ]}
			,{$itemnew['thumb_name'   ]}
			,{$itemnew['thumb_size'   ]}
			,{$itemnew['thumb_xsize'  ]}
			,{$itemnew['thumb_ysize'  ]}
		)
	";
//???	core::event('query', "Insert picture to database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new gallery_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
	return mysql_insert_id($handle);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function update_picture ($args)
{//args: itemid, itemnew
	$itemid  = isset($args['itemid' ]) ? $args['itemid' ] : null;
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null;
	if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_picture = core::table('picture');

	$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	foreach ($itemnew as $key => $val) $itemnew[$key] = !is_scalar($val) || ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$sql =
	"
		update {$table_picture} set
			 `parent`	= {$itemnew['parent' ]}
			,`order`	= {$itemnew['order'  ]}
			,`caption`	= {$itemnew['caption']}
			,`comment`	= {$itemnew['comment']}
			,`align`	= {$itemnew['align'  ]}
			,`embed`	= {$itemnew['embed'  ]}

			,`image_storage`= {$itemnew['image_storage']}
			,`image_mime`	= {$itemnew['image_mime'   ]}
			,`image_name`	= {$itemnew['image_name'   ]}
			,`image_size`	= {$itemnew['image_size'   ]}
			,`image_xsize`	= {$itemnew['image_xsize'  ]}
			,`image_ysize`	= {$itemnew['image_ysize'  ]}

			,`thumb_mode`	= {$itemnew['thumb_mode'   ]}
			,`thumb_storage`= {$itemnew['thumb_storage']}
			,`thumb_mime`	= {$itemnew['thumb_mime'   ]}
			,`thumb_name`	= {$itemnew['thumb_name'   ]}
			,`thumb_size`	= {$itemnew['thumb_size'   ]}
			,`thumb_xsize`	= {$itemnew['thumb_xsize'  ]}
			,`thumb_ysize`	= {$itemnew['thumb_ysize'  ]}
		where `picture` = {$itemid}
	";
//???	core::event('query', "Update picture in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new gallery_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function delete_picture ($args)
{//args: itemid
	$itemid = isset($args['itemid']) ? $args['itemid'] : null;

	$handle = core::handle();
	$table_picture = core::table('picture');

	$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	$sql =
	"
		delete from {$table_picture}
		 where `picture` = {$itemid}
	";
//???	core::event('query', "Delete picture from database.", array('query'=>$sql));
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
		$result[] = "(`picture` = {$itemid})";
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