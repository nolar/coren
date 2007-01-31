<?php defined('CORENINPAGE') or die('Hack!');
//todo: 1. add additional data to core::event()s in some methods. for ex. itemid and so on...

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!core::depend('doy306_employee_0')) return; // for exceptions

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class doy306_employee_0_mysql extends dbworker
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_employee_count ($args)
{
	$parent  = isset($args['parent' ]) ? $args['parent' ] : null;
	$itemid  = isset($args['itemid' ]) ? $args['itemid' ] : null;

	$handle = core::handle();
	$table_employee = core::table('employee');

	$filterclause = $this->__filterclause_employee($handle, $parent, $itemid);
	$sql =
	"
		select count(*)
		  from {$table_employee}
		 where {$filterclause}
	";
//???	core::event('query', "Select number of employee in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = null;
	while ($row = mysql_fetch_row($res))
		$result = $row[0];
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_employee_data ($args)
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
	$table_employee = core::table('employee');

	$sortings_asc  = array('employee', 'fname', 'sname', 'tname', 'sex', 'position', 'qualify', 'comment');
	$sortings_desc = array('birthday');
	if (in_array($sorting, $sortings_asc )) { if (is_null($reverse)) $reverse = false; } else
	if (in_array($sorting, $sortings_desc)) { if (is_null($reverse)) $reverse = true ; } else
						{ $sorting = 'employee'; $reverse = false; }
	$reverse = ($reverse ? 'desc' : 'asc');
	$filterclause = $this->__filterclause_employee($handle, $parent, $itemid);
	$sql =
	"
		select `employee`, `fname`, `sname`, `tname`, `sex`, `birthday`, `department`, `position`, `qualify`, `comment`,
				`photo_storage`, `photo_mime`, `photo_name`, `photo_size`, `photo_xsize`, `photo_ysize`
		  from {$table_employee}
		 where {$filterclause}
		 order by `{$sorting}` {$reverse}, `sname` asc, `fname` asc, `tname` asc, `employee` asc
		 limit {$size} offset {$offset}
	";
//???	core::event('query', "Select page of employee from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = array();
	while ($row = mysql_fetch_row($res))
	{
		$result[$row[0]] = array(
			'employee'		=> $row[ 0],
			'fname'			=> $row[ 1],
			'sname'			=> $row[ 2],
			'tname'			=> $row[ 3],
			'sex'			=> $row[ 4],
			'birthday'		=> $this->__splitdate($row[5]),
			'department'		=> $row[ 6],
			'position'		=> $row[ 7],
			'qualify'		=> $row[ 8],
			'comment'		=> $row[ 9],
			'photo_storage'		=> $row[10],
			'photo_mime'		=> $row[11],
			'photo_name'		=> $row[12],
			'photo_size'		=> $row[13],
			'photo_xsize'		=> $row[14],
			'photo_ysize'		=> $row[15]);
	}
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function insert_employee ($args)
{//args: itemnew; returns itemid
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null;
	if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_employee = core::table('employee');

	$sex      = ($itemnew['sex'] == 1 ? 1 : ($itemnew['sex'] == 2 ? 2 : (0)));
	$birthday = is_null($itemnew['birthday']) ? "default" : sprintf("'%04d-%02d-%02d %02d:%02d:%02d'", (integer) $itemnew['birthday']['year'], (integer) $itemnew['birthday']['month'], (integer) $itemnew['birthday']['day'], (integer) $itemnew['birthday']['hour'], (integer) $itemnew['birthday']['minute'], (integer) $itemnew['birthday']['second']);
	foreach ($itemnew as $key => $val) $itemnew[$key] = !is_scalar($val) || ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$sql =
	"
		insert into {$table_employee} (
			 `employee`

			,`fname`
			,`sname`
			,`tname`
			,`sex`

			,`comment`
			,`birthday`
			,`department`
			,`position`
			,`qualify`

			,`photo_storage`
			,`photo_mime`
			,`photo_name`
			,`photo_size`
			,`photo_xsize`
			,`photo_ysize`
		) values (
			 default

			,{$itemnew['fname'        ]}
			,{$itemnew['sname'        ]}
			,{$itemnew['tname'        ]}
			,{$sex                     }

			,{$itemnew['comment'      ]}
			,{$birthday                }
			,{$itemnew['department'   ]}
			,{$itemnew['position'     ]}
			,{$itemnew['qualify'      ]}

			,{$itemnew['photo_storage']}
			,{$itemnew['photo_mime'   ]}
			,{$itemnew['photo_name'   ]}
			,{$itemnew['photo_size'   ]}
			,{$itemnew['photo_xsize'  ]}
			,{$itemnew['photo_ysize'  ]}
		)
	";
//???	core::event('query', "Insert employee to database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new doy306_employee_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
	return mysql_insert_id($handle);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function update_employee ($args)
{//args: itemid, itemnew
	$itemid  = isset($args['itemid' ]) ? $args['itemid' ] : null;
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null;
	if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_employee = core::table('employee');

	$sex      = ($itemnew['sex'] == 1 ? 1 : ($itemnew['sex'] == 2 ? 2 : (0)));
	$birthday = is_null($itemnew['birthday']) ? "default" : sprintf("'%04d-%02d-%02d %02d:%02d:%02d'", (integer) $itemnew['birthday']['year'], (integer) $itemnew['birthday']['month'], (integer) $itemnew['birthday']['day'], (integer) $itemnew['birthday']['hour'], (integer) $itemnew['birthday']['minute'], (integer) $itemnew['birthday']['second']);
	foreach ($itemnew as $key => $val) $itemnew[$key] = !is_scalar($val) || ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$itemid   = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	$sql =
	"
		update {$table_employee} set
			 `fname`		= {$itemnew['fname'        ]}
			,`sname`		= {$itemnew['sname'        ]}
			,`tname`		= {$itemnew['tname'        ]}
			,`sex`			= {$sex                     }

			,`comment`		= {$itemnew['comment'      ]}
			,`birthday`		= {$birthday                }
			,`department`		= {$itemnew['department'   ]}
			,`position`		= {$itemnew['position'     ]}
			,`qualify`		= {$itemnew['qualify'      ]}

			,`photo_storage`	= {$itemnew['photo_storage']}
			,`photo_mime`		= {$itemnew['photo_mime'   ]}
			,`photo_name`		= {$itemnew['photo_name'   ]}
			,`photo_size`		= {$itemnew['photo_size'   ]}
			,`photo_xsize`		= {$itemnew['photo_xsize'  ]}
			,`photo_ysize`		= {$itemnew['photo_ysize'  ]}
		where `employee` = {$itemid}
	";
//???	core::event('query', "Update employee in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new doy306_employee_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function delete_employee ($args)
{//args: itemid
	$itemid = isset($args['itemid']) ? $args['itemid'] : null;

	$handle = core::handle();
	$table_employee = core::table('employee');

	$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	$sql =
	"
		delete from {$table_employee}
		 where `employee` = {$itemid}
	";
//???	core::event('query', "Delete employee from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_department_count ($args)
{
	$parent  = isset($args['parent' ]) ? $args['parent' ] : null;
	$itemid  = isset($args['itemid' ]) ? $args['itemid' ] : null;

	$handle = core::handle();
	$table_department = core::table('department');

	$filterclause = $this->__filterclause_department($handle, $parent, $itemid);
	$sql =
	"
		select count(*)
		  from {$table_department}
		 where {$filterclause}
	";
//???	core::event('query', "Select number of department in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = null;
	while ($row = mysql_fetch_row($res))
		$result = $row[0];
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_department_data ($args)
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
	$table_department = core::table('department');

	$sortings_asc  = array('department', 'name', 'comment');
	$sortings_desc = array();
	if (in_array($sorting, $sortings_asc )) { if (is_null($reverse)) $reverse = false; } else
	if (in_array($sorting, $sortings_desc)) { if (is_null($reverse)) $reverse = true ; } else
						{ $sorting = 'name'; $reverse = false; }
	$reverse = ($reverse ? 'desc' : 'asc');
	$filterclause = $this->__filterclause_department($handle, $parent, $itemid);
	$sql =
	"
		select `department`, `name`, `comment`
		  from {$table_department}
		 where {$filterclause}
		 order by `{$sorting}` {$reverse}, `department` asc
		 limit {$size} offset {$offset}
	";
//???	core::event('query', "Select page of department from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = array();
	while ($row = mysql_fetch_row($res))
	{
		$result[$row[0]] = array(
			'department'		=> $row[ 0],
			'name'			=> $row[ 1],
			'comment'		=> $row[ 2]);
	}
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function insert_department ($args)
{//args: itemnew; returns itemid
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null;
	if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_department = core::table('department');

	foreach ($itemnew as $key => $val) $itemnew[$key] = !is_scalar($val) || ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$sql =
	"
		insert into {$table_department} (
			 `department`
			,`name`
			,`comment`
		) values (
			 default
			,{$itemnew['name'      ]}
			,{$itemnew['comment'   ]}
		)
	";
//???	core::event('query', "Insert department to database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new doy306_department_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
	return mysql_insert_id($handle);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function update_department ($args)
{//args: itemid, itemnew
	$itemid  = isset($args['itemid' ]) ? $args['itemid' ] : null;
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null;
	if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_department = core::table('department');

	$itemid   = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	foreach ($itemnew as $key => $val) $itemnew[$key] = !is_scalar($val) || ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$sql =
	"
		update {$table_department} set
			 `name`		= {$itemnew['name']}
			,`comment`	= {$itemnew['comment']}
		where `department` = {$itemid}
	";
//???	core::event('query', "Update department in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new doy306_department_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function delete_department ($args)
{//args: itemid
	$itemid = isset($args['itemid']) ? $args['itemid'] : null;

	$handle = core::handle();
	$table_department = core::table('department');

	$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	$sql =
	"
		delete from {$table_department}
		 where `department` = {$itemid}
	";
//???	core::event('query', "Delete department from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __filterclause_employee ($handle, $parent, $itemid)
{
	$result = array();

	if (isset($itemid))
	{
		$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
		$result[] = "(`employee` = {$itemid})";
	}

	$result = implode(" and ", $result);
	$result = $result != '' ? "(" . $result . ")" : "true";
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __filterclause_department ($handle, $parent, $itemid)
{
	$result = array();

	if (isset($itemid))
	{
		$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
		$result[] = "(`department` = {$itemid})";
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

public function select_department_info ($args)
{
	$itemids = isset($args['itemids']) ? $args['itemids'] : null;
	if (!is_array($itemids)) $itemids = array();

	$handle = core::handle();
	$table_department = core::table('department');

	foreach ($itemids as $key => $val) $itemids[$key] = !is_scalar($val) || ($val == '') ? "null" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$itemids = empty($itemids) ? "null" : implode(",", $itemids);
	$sql =
	"
		select `department`, `name`, `comment`
		  from {$table_department}
		 where `department` in ({$itemids})
	";
//???	core::event('query', "Select page of contests from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = array();
	while ($row = mysql_fetch_row($res))
	{
		$result[$row[0]] = array(
			'department'		=> $row[ 0],
			'name'			=> $row[ 1],
			'comment'		=> $row[ 2]);
	}
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

?>