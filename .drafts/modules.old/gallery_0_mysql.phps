<?php defined('CORENINPAGE') or die('Hack!');
//todo: 1. add additional data to core::event()s in some methods. for ex. itemid and so on...

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!core::depend('gallery_0')) return; // for exceptions

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class gallery_0_mysql extends dbworker
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_pictures_count ($args)
{
	$parent = isset($args['parent']) ? $args['parent'] : null;
	$itemid = isset($args['itemid']) ? $args['itemid'] : null;
	$category = isset($args['category']) ? $args['category'] : null;

	$handle = core::handle();
	$table_picture = core::table('picture');

	$filterclause = $this->__filterclause($handle, $parent, $itemid, $category);
	if ($filterclause == '') $filterclause = "1=1";
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
	$parent = isset($args['parent']) ? $args['parent'] : null;
	$itemid = isset($args['itemid']) ? $args['itemid'] : null;
	$category = isset($args['category']) ? $args['category'] : null;
	$page   = isset($args['page'  ]) ? $args['page'  ] : null; $page   = (integer) $page  ;
	$size   = isset($args['size'  ]) ? $args['size'  ] : null; $size   = (integer) $size  ;
	$skip   = isset($args['skip'  ]) ? $args['skip'  ] : null; $skip   = (integer) $skip  ;
	$count  = isset($args['count' ]) ? $args['count' ] : null; $count  = (integer) $count ;
	$offset = isset($args['offset']) ? $args['offset'] : null; $offset = (integer) $offset;

	$handle = core::handle();
	$table_picture = core::table('picture');

	$filterclause = $this->__filterclause($handle, $parent, $itemid, $category);
	if ($filterclause == '') $filterclause = "1=1";
	$sql =
	"
		select `picture`, `stamp`, `caption`, `comment`, `category`,
					`image_storage`, `image_mime`, `image_name`, `image_size`, `image_xsize`, `image_ysize`,
			`nicon_mode`,	`nicon_storage`, `nicon_mime`, `nicon_name`, `nicon_size`, `nicon_xsize`, `nicon_ysize`,
			`picon_mode`,	`picon_storage`, `picon_mime`, `picon_name`, `picon_size`, `picon_xsize`, `picon_ysize`
		  from {$table_picture}
		 where {$filterclause}
		 order by `stamp` desc, `picture` asc
		 limit {$size} offset {$offset}
	";
//???	core::event('query', "Select page of pictures from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = array();
	while ($row = mysql_fetch_row($res))
	{
		$stamp      = split('[- :]', $row[1]); if (count($stamp     ) < 6) $stamp      = array_pad($stamp     , 6, null);
		$result[$row[0]] = array(
			'picture'		=> $row[ 0],
			'stampset'		=> !is_null($row[1]),
			'stamp_year'		=> $stamp[0],
			'stamp_month'		=> $stamp[1],
			'stamp_day'		=> $stamp[2],
			'stamp_hour'		=> $stamp[3],
			'stamp_minute'		=> $stamp[4],
			'stamp_second'		=> $stamp[5],
			'caption'		=> $row[ 2],
			'comment'		=> $row[ 3],
			'category'		=> $row[ 4],
			'image_storage'		=> $row[ 5],
			'image_mime'		=> $row[ 6],
			'image_name'		=> $row[ 7],
			'image_size'		=> $row[ 8],
			'image_xsize'		=> $row[ 9],
			'image_ysize'		=> $row[10],
			'nicon_mode'		=> $row[11],
			'nicon_storage'		=> $row[12],
			'nicon_mime'		=> $row[13],
			'nicon_name'		=> $row[14],
			'nicon_size'		=> $row[15],
			'nicon_xsize'		=> $row[16],
			'nicon_ysize'		=> $row[17],
			'picon_mode'		=> $row[18],
			'picon_storage'		=> $row[19],
			'picon_mime'		=> $row[20],
			'picon_name'		=> $row[21],
			'picon_size'		=> $row[22],
			'picon_xsize'		=> $row[23],
			'picon_ysize'		=> $row[24]);
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

	$stamp = $itemnew['stampset'] ? sprintf("'%04d-%02d-%02d %02d:%02d:%02d'", (integer) $itemnew['stamp_year'], (integer) $itemnew['stamp_month'], (integer) $itemnew['stamp_day'], (integer) $itemnew['stamp_hour'], (integer) $itemnew['stamp_minute'], (integer) $itemnew['stamp_second']) : "now()";
	foreach ($itemnew as $key => $val) $itemnew[$key] = ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$sql =
	"
		insert into {$table_picture} (
			 `picture`
			,`stamp`
			,`caption`
			,`comment`
			,`category`

			,`image_storage`
			,`image_mime`
			,`image_name`
			,`image_size`
			,`image_xsize`
			,`image_ysize`

			,`nicon_mode`
			,`nicon_storage`
			,`nicon_mime`
			,`nicon_name`
			,`nicon_size`
			,`nicon_xsize`
			,`nicon_ysize`

			,`picon_mode`
			,`picon_storage`
			,`picon_mime`
			,`picon_name`
			,`picon_size`
			,`picon_xsize`
			,`picon_ysize`
		) values (
			 default
			,{$stamp        }
			,{$itemnew['caption'      ]}
			,{$itemnew['comment'      ]}
			,{$itemnew['category'     ]}

			,{$itemnew['image_storage']}
			,{$itemnew['image_mime'   ]}
			,{$itemnew['image_name'   ]}
			,{$itemnew['image_size'   ]}
			,{$itemnew['image_xsize'  ]}
			,{$itemnew['image_ysize'  ]}

			,{$itemnew['nicon_mode'   ]}
			,{$itemnew['nicon_storage']}
			,{$itemnew['nicon_mime'   ]}
			,{$itemnew['nicon_name'   ]}
			,{$itemnew['nicon_size'   ]}
			,{$itemnew['nicon_xsize'  ]}
			,{$itemnew['nicon_ysize'  ]}

			,{$itemnew['picon_mode'   ]}
			,{$itemnew['picon_storage']}
			,{$itemnew['picon_mime'   ]}
			,{$itemnew['picon_name'   ]}
			,{$itemnew['picon_size'   ]}
			,{$itemnew['picon_xsize'  ]}
			,{$itemnew['picon_ysize'  ]}
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
	$stamp = $itemnew['stampset'] ? sprintf("'%04d-%02d-%02d %02d:%02d:%02d'", (integer) $itemnew['stamp_year'], (integer) $itemnew['stamp_month'], (integer) $itemnew['stamp_day'], (integer) $itemnew['stamp_hour'], (integer) $itemnew['stamp_minute'], (integer) $itemnew['stamp_second']) : "now()";
	foreach ($itemnew as $key => $val) $itemnew[$key] = ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$sql =
	"
		update {$table_picture} set
			 `stamp`        = {$stamp}
			,`caption`       = {$itemnew['caption'      ]}
			,`comment`       = {$itemnew['comment'      ]}
			,`category`      = {$itemnew['category'     ]}

			,`image_storage` = {$itemnew['image_storage']}
			,`image_mime`    = {$itemnew['image_mime'   ]}
			,`image_name`    = {$itemnew['image_name'   ]}
			,`image_size`    = {$itemnew['image_size'   ]}
			,`image_xsize`   = {$itemnew['image_xsize'  ]}
			,`image_ysize`   = {$itemnew['image_ysize'  ]}

			,`nicon_mode`    = {$itemnew['nicon_mode'   ]}
			,`nicon_storage` = {$itemnew['nicon_storage']}
			,`nicon_mime`    = {$itemnew['nicon_mime'   ]}
			,`nicon_name`    = {$itemnew['nicon_name'   ]}
			,`nicon_size`    = {$itemnew['nicon_size'   ]}
			,`nicon_xsize`   = {$itemnew['nicon_xsize'  ]}
			,`nicon_ysize`   = {$itemnew['nicon_ysize'  ]}

			,`picon_mode`    = {$itemnew['picon_mode'   ]}
			,`picon_storage` = {$itemnew['picon_storage']}
			,`picon_mime`    = {$itemnew['picon_mime'   ]}
			,`picon_name`    = {$itemnew['picon_name'   ]}
			,`picon_size`    = {$itemnew['picon_size'   ]}
			,`picon_xsize`   = {$itemnew['picon_xsize'  ]}
			,`picon_ysize`   = {$itemnew['picon_ysize'  ]}
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

function __filterclause ($handle, $parent, $itemid, $category)
{
	$result = array();

	if (isset($category))
	{
		$category = "'" . mysql_real_escape_string($category, $handle) . "'";
		$result[] = "(`category` is null or `category` = {$category})";
	}

	if (isset($itemid))
	{
		$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
		$result[] = "(`picture` = {$itemid})";
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