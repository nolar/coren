<?php defined('CORENINPAGE') or die('Hack!');
//todo: 1. add additional data to core::event()s in some methods. for ex. itemid and so on...

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!core::depend('articles_0')) return; // for exceptions

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class articles_0_mysql extends dbworker
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_articles_count ($args)
{
	$parent     = isset($args['parent'    ]) ? $args['parent'    ] : null;
	$itemid     = isset($args['itemid'    ]) ? $args['itemid'    ] : null;
	$categories = isset($args['categories']) ? $args['categories'] : null;
	$grants     = isset($args['grants'    ]) ? $args['grants'    ] : null;
	$published  = isset($args['published' ]) ? $args['published' ] : null;
	$actualfrom = isset($args['actualfrom']) ? $args['actualfrom'] : null;
	$actualtill = isset($args['actualtill']) ? $args['actualtill'] : null;

	$handle = core::handle();
	$table_article = core::table('article');

	$filterclause = $this->__filterclause_article($handle, $parent, $itemid, $categories, $grants, $published, $actualfrom, $actualtill);
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
	$categories = isset($args['categories']) ? $args['categories'] : null;
	$grants     = isset($args['grants'    ]) ? $args['grants'    ] : null;
	$published  = isset($args['published' ]) ? $args['published' ] : null;
	$actualfrom = isset($args['actualfrom']) ? $args['actualfrom'] : null;
	$actualtill = isset($args['actualtill']) ? $args['actualtill'] : null;

	$handle = core::handle();
	$table_article = core::table('article');

	$sortings_asc  = array('article', 'category', 'headline', 'announce', 'fulltext', 'expander', 'published');
	$sortings_desc = array('stamp', 'actualfrom', 'actualtill');
	if (in_array($sorting, $sortings_asc )) { if (is_null($reverse)) $reverse = false; } else
	if (in_array($sorting, $sortings_desc)) { if (is_null($reverse)) $reverse = true ; } else
						{ $sorting = 'article' ; $reverse = false; }
	$reverse = ($reverse ? 'desc' : 'asc');
	$filterclause = $this->__filterclause_article($handle, $parent, $itemid, $categories, $grants, $published, $actualfrom, $actualtill);
	$sql =
	"
		select `article`, `category`, `headline`, `announce`, `fulltext`, `expander`, `stamp`, `actualfrom`, `actualtill`, `published`
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
			'category'		=> $row[1],
			'headline'		=> $row[2],
			'announce'		=> $row[3],
			'fulltext'		=> $row[4],
			'expander'		=> $row[5],
			'stamp'			=> $this->__splitdate($row[6]),
			'actualfrom'		=> $this->__splitdate($row[7]),
			'actualtill'		=> $this->__splitdate($row[8]),
			'published'		=> $row[9]);
	}
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function insert_article ($args)
{//args: itemnew; returns itemid
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null; if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_article = core::table('article');


	$stamp      = is_null($itemnew['stamp'      ]) ? " now() " : sprintf("'%04d-%02d-%02d %02d:%02d:%02d'", (integer) $itemnew['stamp'      ]['year'], (integer) $itemnew['stamp'      ]['month'], (integer) $itemnew['stamp'      ]['day'], (integer) $itemnew['stamp'      ]['hour'], (integer) $itemnew['stamp'      ]['minute'], (integer) $itemnew['stamp'      ]['second']);
	$actualfrom = is_null($itemnew['actualfrom' ]) ? "default" : sprintf("'%04d-%02d-%02d %02d:%02d:%02d'", (integer) $itemnew['actualfrom' ]['year'], (integer) $itemnew['actualfrom' ]['month'], (integer) $itemnew['actualfrom' ]['day'], (integer) $itemnew['actualfrom' ]['hour'], (integer) $itemnew['actualfrom' ]['minute'], (integer) $itemnew['actualfrom' ]['second']);
	$actualtill = is_null($itemnew['actualtill' ]) ? "default" : sprintf("'%04d-%02d-%02d %02d:%02d:%02d'", (integer) $itemnew['actualtill' ]['year'], (integer) $itemnew['actualtill' ]['month'], (integer) $itemnew['actualtill' ]['day'], (integer) $itemnew['actualtill' ]['hour'], (integer) $itemnew['actualtill' ]['minute'], (integer) $itemnew['actualtill' ]['second']);
	$published  = $itemnew['published'] ? "1" : "0";
	foreach ($itemnew as $key => $val) $itemnew[$key] = !is_scalar($val) || ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$sql =
	"
		insert into {$table_article} (
			 `article`
			,`category`
			,`headline`
			,`announce`
			,`fulltext`
			,`expander`
			,`stamp`
			,`actualfrom`
			,`actualtill`
			,`published`
		) values (
			 default
			,{$itemnew['category']}
			,{$itemnew['headline']}
			,{$itemnew['announce']}
			,{$itemnew['fulltext']}
			,{$itemnew['expander']}
			,{$stamp}
			,{$actualfrom}
			,{$actualtill}
			,{$published}
		)
	";
//???	core::event('query', "Insert article to database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new articles_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
	return mysql_insert_id($handle);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function update_article ($args)
{//args: itemid, itemnew
	$itemid  = isset($args['itemid']) ? $args['itemid'] : null;
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null;
	if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_article = core::table('article');

	$stamp      = is_null($itemnew['stamp'      ]) ? " now() " : sprintf("'%04d-%02d-%02d %02d:%02d:%02d'", (integer) $itemnew['stamp'      ]['year'], (integer) $itemnew['stamp'      ]['month'], (integer) $itemnew['stamp'      ]['day'], (integer) $itemnew['stamp'      ]['hour'], (integer) $itemnew['stamp'      ]['minute'], (integer) $itemnew['stamp'      ]['second']);
	$actualfrom = is_null($itemnew['actualfrom' ]) ? "default" : sprintf("'%04d-%02d-%02d %02d:%02d:%02d'", (integer) $itemnew['actualfrom' ]['year'], (integer) $itemnew['actualfrom' ]['month'], (integer) $itemnew['actualfrom' ]['day'], (integer) $itemnew['actualfrom' ]['hour'], (integer) $itemnew['actualfrom' ]['minute'], (integer) $itemnew['actualfrom' ]['second']);
	$actualtill = is_null($itemnew['actualtill' ]) ? "default" : sprintf("'%04d-%02d-%02d %02d:%02d:%02d'", (integer) $itemnew['actualtill' ]['year'], (integer) $itemnew['actualtill' ]['month'], (integer) $itemnew['actualtill' ]['day'], (integer) $itemnew['actualtill' ]['hour'], (integer) $itemnew['actualtill' ]['minute'], (integer) $itemnew['actualtill' ]['second']);
	$published  = $itemnew['published'] ? "1" : "0";
	foreach ($itemnew as $key => $val) $itemnew[$key] = !is_scalar($val) || ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	$sql =
	"
		update {$table_article} set
			 `category`   = {$itemnew['category']}
			,`headline`   = {$itemnew['headline']}
			,`announce`   = {$itemnew['announce']}
			,`fulltext`   = {$itemnew['fulltext']}
			,`expander`   = {$itemnew['expander']}
			,`stamp`      = {$stamp}
			,`actualfrom` = {$actualfrom}
			,`actualtill` = {$actualtill}
			,`published`  = {$published}
		where `article` = {$itemid}
	";
//???	core::event('query', "Update article in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new articles_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function delete_article ($args)
{//args: itemid
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

public function select_categories_count ($args)
{
	$parent     = isset($args['parent'    ]) ? $args['parent'    ] : null;
	$itemid     = isset($args['itemid'    ]) ? $args['itemid'    ] : null;
	$grants     = isset($args['grants'    ]) ? $args['grants'    ] : null;

	$handle = core::handle();
	$table_category = core::table('category');

	$filterclause = $this->__filterclause_categories($handle, $parent, $itemid, $grants);
	$sql =
	"
		select count(*)
		  from {$table_category}
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

public function select_categories_data ($args)
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
	$grants     = isset($args['grants'    ]) ? $args['grants'    ] : null;

	$handle = core::handle();
	$table_category = core::table('category');

	$sortings_asc  = array('category', 'grant', 'name', 'comment');
	$sortings_desc = array();
	if (in_array($sorting, $sortings_asc )) { if (is_null($reverse)) $reverse = false; } else
	if (in_array($sorting, $sortings_desc)) { if (is_null($reverse)) $reverse = true ; } else
						{ $sorting = 'name'    ; $reverse = false; }
	$reverse = ($reverse ? 'desc' : 'asc');
	$filterclause = $this->__filterclause_categories($handle, $parent, $itemid, $grants);
	$sql =
	"
		select `category`, `grant`, `name`, `comment`
		  from {$table_category}
		 where {$filterclause}
		 order by `{$sorting}` {$reverse}, `category` asc
		 limit {$size} offset {$offset}
	";
//???	core::event('query', "Select page of articles from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = array();
	while ($row = mysql_fetch_row($res))
	{
		$result[$row[0]] = array(
			'category'		=> $row[0],
			'grant'			=> $row[1],
			'name'			=> $row[2],
			'comment'		=> $row[3]);
	}
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function insert_category ($args)
{//args: itemnew; returns itemid
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null; if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_category = core::table('category');

	foreach ($itemnew as $key => $val) $itemnew[$key] = !is_scalar($val) || ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$sql =
	"
		insert into {$table_category} (
			 `category`
			,`grant`
			,`name`
			,`comment`
		) values (
			 default
			,{$itemnew['grant'  ]}
			,{$itemnew['name'   ]}
			,{$itemnew['comment']}
		)
	";
//???	core::event('query', "Insert article to database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new articles_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
	return mysql_insert_id($handle);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function update_category ($args)
{//args: itemid, itemnew
	$itemid  = isset($args['itemid']) ? $args['itemid'] : null;
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null;
	if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_category = core::table('category');

	$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	foreach ($itemnew as $key => $val) $itemnew[$key] = !is_scalar($val) || ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$sql =
	"
		update {$table_category} set
			 `grant`	= {$itemnew['grant'  ]}
			,`name`		= {$itemnew['name'   ]}
			,`comment`	= {$itemnew['comment']}
		where `category` = {$itemid}
	";
//???	core::event('query', "Update article in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new articles_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function delete_category ($args)
{//args: itemid
	$itemid = isset($args['itemid']) ? $args['itemid'] : null;

	$handle = core::handle();
	$table_category = core::table('category');

	$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	$sql =
	"
		delete from {$table_category}
		 where `category` = {$itemid}
	";
//???	core::event('query', "Delete article from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_category_info ($args)
{
	$itemids = isset($args['itemids']) ? $args['itemids'] : null;
	if (!is_array($itemids)) $itemids = array();

	$handle = core::handle();
	$table_category = core::table('category');

	foreach ($itemids as $key => $val) $itemids[$key] = !is_scalar($val) || ($val == '') ? "null" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$itemids = empty($itemids) ? "null" : implode(",", $itemids);
	$sql =
	"
		select `category`, `grant`, `name`, `comment`
		  from {$table_category}
		 where `category` in ({$itemids})
	";
//???	core::event('query', "Select page of categorys from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = array();
	while ($row = mysql_fetch_row($res))
	{
		$result[$row[0]] = array(
			'category'		=> $row[0],
			'grant'			=> $row[1],
			'name'			=> $row[2],
			'comment'		=> $row[3]);
	}
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_categories_allowed ($args)
{//args: grants; returns: array of accessible category ids
	$grants = isset($args['grants']) ? $args['grants'] : null;

	$handle = core::handle();
	$table_category = core::table('category');

	$filterclause = $this->__filterclause_grants($handle, $grants);
	$sql =
	"
		select `category`
		  from {$table_category}
		 where {$filterclause}
	";
//???	core::event('query', "Select page of articles from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = array();
	while ($row = mysql_fetch_row($res))
		$result[] = $row[0];
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __filterclause_grants ($handle, $grants)
{
	$result = array();

	if (isset($grants))
	{
		$sqlvalues = array();
		if (is_array($grants))
			foreach ($grants as $i)
				$sqlvalues[] = "'" . mysql_real_escape_string($i, $handle) . "'";
		$sqlvalues = implode(',', $sqlvalues);
		$result[] = ($sqlvalues == '') ?
			"(`grant` is null)" :
			"(`grant` is null or `grant` in ({$sqlvalues}))";
	}

	$result = implode(" and ", $result);
	$result = $result != '' ? "(" . $result . ")" : "true";
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __filterclause_article ($handle, $parent, $itemid, $categories, $grants, $published, $actualfrom, $actualtill)
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

	if (isset($categories))
	{
		$sqlvalues = array();
		if (is_array($categories))
			foreach ($categories as $i)
				$sqlvalues[] = "'" . mysql_real_escape_string($i, $handle) . "'";
		$sqlvalues = implode(',', $sqlvalues);
		$result[] = ($sqlvalues == '') ?
			"(`category` is null)" :
			"(`category` is null or `category` in ({$sqlvalues}))";
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

function __filterclause_categories ($handle, $parent, $itemid, $grants)
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
		$result[] = "(`category` = {$itemid})";
	}

	if (isset($grants))
	{
		$sqlvalues = array();
		if (is_array($grants))
			foreach ($grants as $i)
				$sqlvalues[] = "'" . mysql_real_escape_string($i, $handle) . "'";
		$sqlvalues = implode(',', $sqlvalues);
		$result[] = ($sqlvalues == '') ?
			"(`grant` is null)" :
			"(`grant` is null or `grant` in ({$sqlvalues}))";
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