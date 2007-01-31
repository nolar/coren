<?php defined('CORENINPAGE') or die('Hack!');
//todo: 1. add additional data to core::event()s in some methods. for ex. itemid and so on...

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!core::depend('doy306_contest_0')) return; // for exceptions

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class doy306_contest_0_mysql extends dbworker
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_contests_count ($args)
{
	$parent  = isset($args['parent' ]) ? $args['parent' ] : null;
	$itemid  = isset($args['itemid' ]) ? $args['itemid' ] : null;

	$published  = isset($args['published' ]) ? $args['published' ] : null;
	$actualfrom = isset($args['actualfrom']) ? $args['actualfrom'] : null;
	$actualtill = isset($args['actualtill']) ? $args['actualtill'] : null;

	$handle = core::handle();
	$table_contest = core::table('contest');

	$filterclause = $this->__filterclause_contest($handle, $parent, $itemid, $published, $actualfrom, $actualtill);
	$sql =
	"
		select count(*)
		  from {$table_contest}
		 where {$filterclause}
	";
//???	core::event('query', "Select number of contests in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = null;
	while ($row = mysql_fetch_row($res))
		$result = $row[0];
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_contests_data ($args)
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

	$published  = isset($args['published' ]) ? $args['published' ] : null;
	$actualfrom = isset($args['actualfrom']) ? $args['actualfrom'] : null;
	$actualtill = isset($args['actualtill']) ? $args['actualtill'] : null;

	$handle = core::handle();
	$table_contest = core::table('contest');

	$sortings_asc  = array('contest', 'title', 'preamble', 'task', 'results');
	$sortings_desc = array('stamp', 'actualfrom', 'actualtill');
	if (in_array($sorting, $sortings_asc )) { if (is_null($reverse)) $reverse = false; } else
	if (in_array($sorting, $sortings_desc)) { if (is_null($reverse)) $reverse = true ; } else
						{ $sorting = 'contest' ; $reverse = false; }
	$reverse = ($reverse ? 'desc' : 'asc');
	$filterclause = $this->__filterclause_contest($handle, $parent, $itemid, $published, $actualfrom, $actualtill);
	$sql =
	"
		select `contest`, `title`, `preamble`, `task`, `results`, `stamp`, `actualfrom`, `actualtill`, `published`
		  from {$table_contest}
		 where {$filterclause}
		 order by `{$sorting}` {$reverse}, `contest` asc
		 limit {$size} offset {$offset}
	";
//???	core::event('query', "Select page of contests from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = array();
	while ($row = mysql_fetch_row($res))
	{
		$result[$row[0]] = array(
			'contest'		=> $row[0],
			'title'			=> $row[1],
			'preamble'		=> $row[2],
			'task'			=> $row[3],
			'results'		=> $row[4],
			'stamp'			=> $this->__splitdate($row[5]),
			'actualfrom'		=> $this->__splitdate($row[6]),
			'actualtill'		=> $this->__splitdate($row[7]),
			'published'		=> $row[8]);
	}
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function insert_contest ($args)
{//args: itemnew; returns itemid
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null; if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_contest = core::table('contest');

	$stamp      = is_null($itemnew['stamp'     ]) ? " now() " : sprintf("'%04d-%02d-%02d %02d:%02d:%02d'", (integer) $itemnew['stamp'     ]['year'], (integer) $itemnew['stamp'     ]['month'], (integer) $itemnew['stamp'     ]['day'], (integer) $itemnew['stamp'     ]['hour'], (integer) $itemnew['stamp'     ]['minute'], (integer) $itemnew['stamp'     ]['second']);
	$actualfrom = is_null($itemnew['actualfrom']) ? "default" : sprintf("'%04d-%02d-%02d %02d:%02d:%02d'", (integer) $itemnew['actualfrom']['year'], (integer) $itemnew['actualfrom']['month'], (integer) $itemnew['actualfrom']['day'], (integer) $itemnew['actualfrom']['hour'], (integer) $itemnew['actualfrom']['minute'], (integer) $itemnew['actualfrom']['second']);
	$actualtill = is_null($itemnew['actualtill']) ? "default" : sprintf("'%04d-%02d-%02d %02d:%02d:%02d'", (integer) $itemnew['actualtill']['year'], (integer) $itemnew['actualtill']['month'], (integer) $itemnew['actualtill']['day'], (integer) $itemnew['actualtill']['hour'], (integer) $itemnew['actualtill']['minute'], (integer) $itemnew['actualtill']['second']);
	$published  = $itemnew['published'] ? "1" : "0";
	foreach ($itemnew as $key => $val) $itemnew[$key] = !is_scalar($val) || ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$sql =
	"
		insert into {$table_contest} (
			 `contest`
			,`title`
			,`preamble`
			,`task`
			,`results`
			,`stamp`
			,`actualfrom`
			,`actualtill`
			,`published`
		) values (
			 default
			,{$itemnew['title']}
			,{$itemnew['preamble']}
			,{$itemnew['task']}
			,{$itemnew['results']}
			,{$stamp}
			,{$actualfrom}
			,{$actualtill}
			,{$published}
		)
	";
//???	core::event('query', "Insert contest to database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new doy306_contest_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
	return mysql_insert_id($handle);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function update_contest ($args)
{//args: itemid, itemnew
	$itemid  = isset($args['itemid' ]) ? $args['itemid' ] : null;
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null;
	if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_contest = core::table('contest');

	$stamp      = is_null($itemnew['stamp'     ]) ? " now() " : sprintf("'%04d-%02d-%02d %02d:%02d:%02d'", (integer) $itemnew['stamp'     ]['year'], (integer) $itemnew['stamp'     ]['month'], (integer) $itemnew['stamp'     ]['day'], (integer) $itemnew['stamp'     ]['hour'], (integer) $itemnew['stamp'     ]['minute'], (integer) $itemnew['stamp'     ]['second']);
	$actualfrom = is_null($itemnew['actualfrom']) ? "default" : sprintf("'%04d-%02d-%02d %02d:%02d:%02d'", (integer) $itemnew['actualfrom']['year'], (integer) $itemnew['actualfrom']['month'], (integer) $itemnew['actualfrom']['day'], (integer) $itemnew['actualfrom']['hour'], (integer) $itemnew['actualfrom']['minute'], (integer) $itemnew['actualfrom']['second']);
	$actualtill = is_null($itemnew['actualtill']) ? "default" : sprintf("'%04d-%02d-%02d %02d:%02d:%02d'", (integer) $itemnew['actualtill']['year'], (integer) $itemnew['actualtill']['month'], (integer) $itemnew['actualtill']['day'], (integer) $itemnew['actualtill']['hour'], (integer) $itemnew['actualtill']['minute'], (integer) $itemnew['actualtill']['second']);
	$published  = $itemnew['published'] ? "1" : "0";
	foreach ($itemnew as $key => $val) $itemnew[$key] = !is_scalar($val) || ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	$sql =
	"
		update {$table_contest} set
			 `title`   = {$itemnew['title']}
			,`preamble`   = {$itemnew['preamble']}
			,`task`   = {$itemnew['task']}
			,`results`   = {$itemnew['results']}
			,`stamp` = {$stamp}
			,`actualfrom` = {$actualfrom}
			,`actualtill` = {$actualtill}
			,`published`  = {$published}
		where `contest` = {$itemid}
	";
//???	core::event('query', "Update contest in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new doy306_contest_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function delete_contest ($args)
{//args: itemid
	$itemid = isset($args['itemid']) ? $args['itemid'] : null;

	$handle = core::handle();
	$table_contest = core::table('contest');

	$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	$sql =
	"
		delete from {$table_contest}
		 where `contest` = {$itemid}
	";
//???	core::event('query', "Delete contest from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __filterclause_contest ($handle, $parent, $itemid, $published, $actualfrom, $actualtill)
{
	$result = array();

//???	// parent is ognored in contests
//???	if (isset($parent)
//???	{
//???		$parent = "'" . mysql_real_escape_string($parent, $handle) . "'";
//???		$result[] = "(`parent` = {$parent})";
//???	}

	if (isset($itemid))
	{
		$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
		$result[] = "(`contest` = {$itemid})";
	}

	if ($published)
		$result[] = "(`published` is not null and `published` <> 0)";

	if ($actualfrom)
		$result[] = "(`actualfrom` is null or `actualfrom` <= now())";

	if ($actualtill)
		$result[] = "(`actualtill` is null or `actualtill` >= now())";

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