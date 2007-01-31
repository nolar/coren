<?php defined('CORENINPAGE') or die('Hack!');
//todo: 1. add additional data to core::event()s in some methods. for ex. itemid and so on...

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!core::depend('doy306_article_0')) return; // for exceptions

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class doy306_article_0_mysql extends dbworker
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_articles_count ($args)
{
	$parent  = isset($args['parent' ]) ? $args['parent' ] : null;
	$itemid  = isset($args['itemid' ]) ? $args['itemid' ] : null;

	$handle = core::handle();
	$table_article = core::table('article');

	$filterclause = $this->__filterclause_article($handle, $parent, $itemid);
	$sql =
	"
		select count(*)
		  from {$table_article}
		 where {$filterclause}
	";
//???	core::event('query', "Select number of articles in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = null;
	while ($row = mysql_fetch_row($res))
		$result = $row[0];
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_articles_data ($args)
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
	$table_article = core::table('article');

	$sortings_asc  = array('article', 'child', 'contest', 'place', 'prize', 'title', 'description');
	$sortings_desc = array('madestamp');
	if (in_array($sorting, $sortings_asc )) { if (is_null($reverse)) $reverse = false; } else
	if (in_array($sorting, $sortings_desc)) { if (is_null($reverse)) $reverse = true ; } else
						{ $sorting = 'article' ; $reverse = false; }
	$reverse = ($reverse ? 'desc' : 'asc');
	$filterclause = $this->__filterclause_article($handle, $parent, $itemid);
	$sql =
	"
		select `article`, `child`, `contest`, `place`, `prize`, `title`, `description`, `madestamp`
		  from {$table_article}
		 where {$filterclause}
		 order by `{$sorting}` {$reverse}, `article` asc
		 limit {$size} offset {$offset}
	";
//???	core::event('query', "Select page of articles from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = array();
	while ($row = mysql_fetch_row($res))
	{
		$result[$row[0]] = array(
			'article'		=> $row[0],
			'child'			=> $row[1],
			'contest'		=> $row[2],
			'place'			=> $row[3],
			'prize'			=> $row[4],
			'title'			=> $row[5],
			'description'		=> $row[6],
			'madestamp'		=> $this->__splitdate($row[7]));
	}
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function insert_article ($args)
{
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null;
	if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_article = core::table('article');

	$madestamp  = is_null($itemnew['madestamp' ]) ? "default" : sprintf("'%04d-%02d-%02d %02d:%02d:%02d'", (integer) $itemnew['madestamp' ]['year'], (integer) $itemnew['madestamp' ]['month'], (integer) $itemnew['madestamp' ]['day'], (integer) $itemnew['madestamp' ]['hour'], (integer) $itemnew['madestamp' ]['minute'], (integer) $itemnew['madestamp' ]['second']);
	foreach ($itemnew as $key => $val) $itemnew[$key] = !is_scalar($val) || ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$sql =
	"
		insert into {$table_article} (
			 `article`
			,`child`
			,`contest`
			,`place`
			,`prize`
			,`title`
			,`description`
			,`madestamp`
		) values (
			 default
			,{$itemnew['child'      ]}
			,{$itemnew['contest'    ]}
			,{$itemnew['place'      ]}
			,{$itemnew['prize'      ]}
			,{$itemnew['title'      ]}
			,{$itemnew['description']}
			,{$madestamp}
		)
	";
//???	core::event('query', "Insert article to database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new doy306_article_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
	return mysql_insert_id($handle);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function update_article ($args)
{
	$itemid  = isset($args['itemid' ]) ? $args['itemid' ] : null;
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null;
	if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_article = core::table('article');

	$madestamp  = is_null($itemnew['madestamp' ]) ? "default" : sprintf("'%04d-%02d-%02d %02d:%02d:%02d'", (integer) $itemnew['madestamp' ]['year'], (integer) $itemnew['madestamp' ]['month'], (integer) $itemnew['madestamp' ]['day'], (integer) $itemnew['madestamp' ]['hour'], (integer) $itemnew['madestamp' ]['minute'], (integer) $itemnew['madestamp' ]['second']);
	foreach ($itemnew as $key => $val) $itemnew[$key] = !is_scalar($val) || ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	$sql =
	"
		update {$table_article} set
			 `child`	= {$itemnew['child'      ]}
			,`contest`	= {$itemnew['contest'    ]}
			,`place`	= {$itemnew['place'      ]}
			,`prize`	= {$itemnew['prize'      ]}
			,`title`	= {$itemnew['title'      ]}
			,`description`	= {$itemnew['description']}
			,`madestamp`	= {$madestamp}
		where `article` = {$itemid}
	";
//???	core::event('query', "Update article in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new doy306_article_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function delete_article ($args)
{
	$itemid = isset($args['itemid']) ? $args['itemid'] : null;

	$handle = core::handle();
	$table_article = core::table('article');

	$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	$sql =
	"
		delete from {$table_article}
		 where `article` = {$itemid}
	";
//???	core::event('query', "Delete article from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __filterclause_article ($handle, $parent, $itemid)
{
	$result = array();

//???	// parent is ognored in articles
//???	if (isset($parent)
//???	{
//???		$parent = "'" . mysql_real_escape_string($parent, $handle) . "'";
//???		$result[] = "(`parent` = {$parent})";
//???	}

	if (isset($itemid))
	{
		$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
		$result[] = "(`article` = {$itemid})";
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

public function select_child_info ($args)
{
	$itemids = isset($args['itemids']) ? $args['itemids'] : null;
	if (!is_array($itemids)) $itemids = array();

	$handle = core::handle();
	$table_child = core::table('child');

	foreach ($itemids as $key => $val) $itemids[$key] = !is_scalar($val) || ($val == '') ? "null" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$itemids = empty($itemids) ? "null" : implode(",", $itemids);
	$sql =
	"
		select `child`, `fname`, `sname`, `tname`, `sex`, `comment`, `birthday`,
				`photo_storage`, `photo_mime`, `photo_name`, `photo_size`, `photo_xsize`, `photo_ysize`
		  from {$table_child}
		 where `child` in ({$itemids})
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
			'photo_ysize'		=> $row[12]);
	}
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_contest_info ($args)
{
	$itemids = isset($args['itemids']) ? $args['itemids'] : null;
	if (!is_array($itemids)) $itemids = array();

	$handle = core::handle();
	$table_contest = core::table('contest');

	foreach ($itemids as $key => $val) $itemids[$key] = !is_scalar($val) || ($val == '') ? "null" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$itemids = empty($itemids) ? "null" : implode(",", $itemids);
	$sql =
	"
		select `contest`, `title`, `preamble`, `task`, `results`, `stamp`, `actualfrom`, `actualtill`, `published`
		  from {$table_contest}
		 where `contest` in ({$itemids})
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
}

?>