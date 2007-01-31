<?php defined('CORENINPAGE') or die('Hack!');
//todo: 1. сделать так, чтобы методы select_*() не переопределялись, а достаточно было переопрежедения какого-нибудь одного метода.
//todo: 2. сделать чтобы поиск fname/sname/tname LIKE ... не начинался с маски "%"; например, сделав отдельные индексы. а то ищет полным перебором.


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!core::depend('doy306_child_0_mysql')) return; // for parent

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class doy306_child_search_0_mysql extends doy306_child_0_mysql
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_child_count ($args)
{
	$parent = isset($args['parent']) ? $args['parent'] : null;
	$itemid = isset($args['itemid']) ? $args['itemid'] : null;
	$fname  = isset($args['fname' ]) ? $args['fname' ] : null;
	$sname  = isset($args['sname' ]) ? $args['sname' ] : null;
	$tname  = isset($args['tname' ]) ? $args['tname' ] : null;

	$handle = core::handle();
	$table_child = core::table('child');

	$filterclause = $this->__filterclause2($handle, $parent, $itemid, $fname, $sname, $tname);
	if ($filterclause == '') $filterclause = "1=1";
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
	//!!!todo: сортировку по запросу!
	$parent = isset($args['parent']) ? $args['parent'] : null;
	$itemid = isset($args['itemid']) ? $args['itemid'] : null;
	$fname  = isset($args['fname' ]) ? $args['fname' ] : null;
	$sname  = isset($args['sname' ]) ? $args['sname' ] : null;
	$tname  = isset($args['tname' ]) ? $args['tname' ] : null;
	$page   = isset($args['page'  ]) ? $args['page'  ] : null; $page   = (integer) $page  ;
	$size   = isset($args['size'  ]) ? $args['size'  ] : null; $size   = (integer) $size  ;
	$skip   = isset($args['skip'  ]) ? $args['skip'  ] : null; $skip   = (integer) $skip  ;
	$count  = isset($args['count' ]) ? $args['count' ] : null; $count  = (integer) $count ;
	$offset = isset($args['offset']) ? $args['offset'] : null; $offset = (integer) $offset;

	$handle = core::handle();
	$table_child = core::table('child');

	$filterclause = $this->__filterclause2($handle, $parent, $itemid, $fname, $sname, $tname);
	if ($filterclause == '') $filterclause = "1=1";
	$sql =
	"
		select `child`, `fname`, `sname`, `tname`, `sex`, `comment`, `birthday`,
				`photo_storage`, `photo_mime`, `photo_name`, `photo_size`, `photo_xsize`, `photo_ysize`
		  from {$table_child}
		 where {$filterclause}
		 order by `sname` asc, `fname` asc, `child` asc
		 limit {$size} offset {$offset}
	";
//???	core::event('query', "Select page of child from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = array();
	while ($row = mysql_fetch_row($res))
	{
		$birthday = split('[- :]', $row[6]); if (count($birthday) < 6) $birthday   = array_pad($birthday  , 6, null);
		$result[$row[0]] = array(
			'child'			=> $row[ 0],
			'fname'			=> $row[ 1],
			'sname'			=> $row[ 2],
			'tname'			=> $row[ 3],
			'sex'			=> $row[ 4],
			'comment'		=> $row[ 5],
			'birthdayset'		=> !is_null($row[6]),
			'birthday_year'		=> $birthday[0],
			'birthday_month'	=> $birthday[1],
			'birthday_day'		=> $birthday[2],
			'birthday_hour'		=> $birthday[3],
			'birthday_minute'	=> $birthday[4],
			'birthday_second'	=> $birthday[5],
			'photo_storage'		=> $row[ 7],
			'photo_mime'		=> $row[ 8],
			'photo_name'		=> $row[ 9],
			'photo_size'		=> $row[10],
			'photo_xsize'		=> $row[11],
			'photo_ysize'		=> $row[12]);
	}
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __filterclause2 ($handle, $parent, $itemid, $fname, $sname, $tname)
{
	$result = array();

	if ($fname != '')
	{
		$fname = "'%" . mysql_real_escape_string($fname, $handle) . "%'";
		$result[] = "(`fname` like {$fname})";
	}

	if ($sname != '')
	{
		$sname = "'%" . mysql_real_escape_string($sname, $handle) . "%'";
		$result[] = "(`sname` like {$sname})";
	}

	if ($tname != '')
	{
		$tname = "'%" . mysql_real_escape_string($tname, $handle) . "%'";
		$result[] = "(`tname` like {$tname})";
	}

	if (isset($itemid))
	{
		$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
		$result[] = "(`child` = {$itemid})";
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