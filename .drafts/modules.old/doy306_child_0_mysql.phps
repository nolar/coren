<?php defined('CORENINPAGE') or die('Hack!');
//todo: 1. add additional data to core::event()s in some methods. for ex. itemid and so on...

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!core::depend('doy306_child_0')) return; // for exceptions

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class doy306_child_0_mysql extends dbworker
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_child_count ($args)
{
	$parent  = isset($args['parent' ]) ? $args['parent' ] : null;
	$itemid  = isset($args['itemid' ]) ? $args['itemid' ] : null;

	$handle = core::handle();
	$table_child = core::table('child');

	$filterclause = $this->__filterclause_child($handle, $parent, $itemid);
	$sql =
	"
		select count(*)
		  from {$table_child}
		 where {$filterclause}
	";
//???	core::event('query', "Select number of child in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = null;
	while ($row = mysql_fetch_row($res))
		$result = $row[0];
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_child_data ($args)
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
	$table_child = core::table('child');

	$sortings_asc  = array('child', 'fname', 'sname', 'tname', 'sex', 'comment');
	$sortings_desc = array('birthday');
	if (in_array($sorting, $sortings_asc )) { if (is_null($reverse)) $reverse = false; } else
	if (in_array($sorting, $sortings_desc)) { if (is_null($reverse)) $reverse = true ; } else
						{ $sorting = 'child'   ; $reverse = false; }
	$reverse = ($reverse ? 'desc' : 'asc');
	$filterclause = $this->__filterclause_child($handle, $parent, $itemid);
	$sql =
	"
		select `child`, `fname`, `sname`, `tname`, `sex`, `comment`, `birthday`,
				`photo_storage`, `photo_mime`, `photo_name`, `photo_size`, `photo_xsize`, `photo_ysize`
				,`parents`
		  from {$table_child}
		 where {$filterclause}
		 order by `{$sorting}` {$reverse}, `sname` asc, `fname` asc, `tname` asc, `child` asc
		 limit {$size} offset {$offset}
	";
//???	core::event('query', "Select page of child from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = array();
	while ($row = mysql_fetch_row($res))
	{
		$result[$row[0]] = array(
			'child'			=> $row[ 0],
			'fname'			=> $row[ 1],
			'sname'			=> $row[ 2],
			'tname'			=> $row[ 3],
			'sex'			=> $row[ 4],
			'comment'		=> $row[ 5],
			'birthday'		=> $this->__splitdate($row[6]),
			'photo_storage'		=> $row[ 7],
			'photo_mime'		=> $row[ 8],
			'photo_name'		=> $row[ 9],
			'photo_size'		=> $row[10],
			'photo_xsize'		=> $row[11],
			'photo_ysize'		=> $row[12],
			'parents'		=> $row[13]);
	}
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function insert_child ($args)
{//args: itemnew; returns itemid
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null;
	if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_child = core::table('child');

	$sex = ($itemnew['sex'] == 1 ? 1 : ($itemnew['sex'] == 2 ? 2 : (0)));
	$birthday = is_null($itemnew['birthday']) ? "default" : sprintf("'%04d-%02d-%02d %02d:%02d:%02d'", (integer) $itemnew['birthday']['year'], (integer) $itemnew['birthday']['month'], (integer) $itemnew['birthday']['day'], (integer) $itemnew['birthday']['hour'], (integer) $itemnew['birthday']['minute'], (integer) $itemnew['birthday']['second']);
	foreach ($itemnew as $key => $val) $itemnew[$key] = !is_scalar($val) || ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$sql =
	"
		insert into {$table_child} (
			 `child`

			,`fname`
			,`sname`
			,`tname`
			,`sex`
			,`comment`
			,`birthday`

			,`photo_storage`
			,`photo_mime`
			,`photo_name`
			,`photo_size`
			,`photo_xsize`
			,`photo_ysize`

			,`parents`
		) values (
			 default

			,{$itemnew['fname'  ]}
			,{$itemnew['sname'  ]}
			,{$itemnew['tname'  ]}
			,{$sex               }
			,{$itemnew['comment']}
			,{$birthday          }

			,{$itemnew['photo_storage']}
			,{$itemnew['photo_mime'   ]}
			,{$itemnew['photo_name'   ]}
			,{$itemnew['photo_size'   ]}
			,{$itemnew['photo_xsize'  ]}
			,{$itemnew['photo_ysize'  ]}

			,{$itemnew['parents']}
		)
	";
//???	core::event('query', "Insert child to database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new doy306_child_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
	return mysql_insert_id($handle);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function update_child ($args)
{//args: itemid, itemnew
	$itemid  = isset($args['itemid' ]) ? $args['itemid' ] : null;
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null;
	if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_child = core::table('child');

	$sex = ($itemnew['sex'] == 1 ? 1 : ($itemnew['sex'] == 2 ? 2 : (0)));
	$birthday = is_null($itemnew['birthday']) ? "default" : sprintf("'%04d-%02d-%02d %02d:%02d:%02d'", (integer) $itemnew['birthday']['year'], (integer) $itemnew['birthday']['month'], (integer) $itemnew['birthday']['day'], (integer) $itemnew['birthday']['hour'], (integer) $itemnew['birthday']['minute'], (integer) $itemnew['birthday']['second']);
	foreach ($itemnew as $key => $val) $itemnew[$key] = !is_scalar($val) || ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	$sql =
	"
		update {$table_child} set
			 `fname`		= {$itemnew['fname'  ]}
			,`sname`		= {$itemnew['sname'  ]}
			,`tname`		= {$itemnew['tname'  ]}
			,`sex`			= {$sex               }
			,`comment`		= {$itemnew['comment']}
			,`birthday`		= {$birthday          }

			,`photo_storage`	= {$itemnew['photo_storage']}
			,`photo_mime`		= {$itemnew['photo_mime'   ]}
			,`photo_name`		= {$itemnew['photo_name'   ]}
			,`photo_size`		= {$itemnew['photo_size'   ]}
			,`photo_xsize`		= {$itemnew['photo_xsize'  ]}
			,`photo_ysize`		= {$itemnew['photo_ysize'  ]}

			,`parents`		= {$itemnew['parents'  ]}
		where `child` = {$itemid}
	";
//???	core::event('query', "Update child in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new doy306_child_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function delete_child ($args)
{//args: itemid
	$itemid = isset($args['itemid']) ? $args['itemid'] : null;

	$handle = core::handle();
	$table_child = core::table('child');

	$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	$sql =
	"
		delete from {$table_child}
		 where `child` = {$itemid}
	";
//???	core::event('query', "Delete child from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __filterclause_child ($handle, $parent, $itemid)
{
	$result = array();

	if (isset($itemid))
	{
		$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
		$result[] = "(`child` = {$itemid})";
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