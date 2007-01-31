<?php defined('CORENINPAGE') or die('Hack!');
//todo: 1. add additional data to core::event()s in some methods. for ex. itemid and so on...

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!core::depend('subscriptions_0')) return; // for exceptions

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class subscriptions_0_mysql extends dbworker
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_subscriptions_count ($args)
{
	$parent     = isset($args['parent'    ]) ? $args['parent'    ] : null;
	$itemid     = isset($args['itemid'    ]) ? $args['itemid'    ] : null;

	$handle = core::handle();
	$table_subscription = core::table('subscription');

	$filterclause = $this->__filterclause_subscription($handle, $parent, $itemid);
	$sql =
	"
		select count(*)
		  from {$table_subscription}
		 where {$filterclause}
	";
//???	core::event('query', "Select number of subscriptions in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = null;
	while ($row = mysql_fetch_row($res))
		$result = $row[0];
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_subscriptions_data ($args)
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
	$table_subscription = core::table('subscription');

	$sortings_asc  = array('subscription', 'name', 'comment');
	$sortings_desc = array();
	if (in_array($sorting, $sortings_asc )) { if (is_null($reverse)) $reverse = false; } else
	if (in_array($sorting, $sortings_desc)) { if (is_null($reverse)) $reverse = true ; } else
						{ $sorting = 'subscription' ; $reverse = false; }
	$reverse = ($reverse ? 'desc' : 'asc');
	$filterclause = $this->__filterclause_subscription($handle, $parent, $itemid);
	$sql =
	"
		select `subscription`, `name`, `comment`
		  from {$table_subscription}
		 where {$filterclause}
		 order by `{$sorting}` {$reverse}, `subscription` asc
		 limit {$size} offset {$offset}
	";
//???	core::event('query', "Select page of subscriptions from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = array();
	while ($row = mysql_fetch_row($res))
	{
		$result[$row[0]] = array(
			'subscription'		=> $row[0],
			'name'			=> $row[1],
			'comment'		=> $row[2]);
	}
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function insert_subscription ($args)
{//args: itemnew; returns itemid
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null; if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_subscription = core::table('subscription');

	foreach ($itemnew as $key => $val) $itemnew[$key] = !is_scalar($val) || ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$sql =
	"
		insert into {$table_subscription} (
			 `subscription`
			,`name`
			,`comment`
		) values (
			 default
			,{$itemnew['name'   ]}
			,{$itemnew['comment']}
		)
	";
//???	core::event('query', "Insert subscription to database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new subscriptions_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
	return mysql_insert_id($handle);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function update_subscription ($args)
{//args: itemid, itemnew
	$itemid  = isset($args['itemid']) ? $args['itemid'] : null;
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null;
	if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_subscription = core::table('subscription');

	foreach ($itemnew as $key => $val) $itemnew[$key] = !is_scalar($val) || ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	$sql =
	"
		update {$table_subscription} set
			 `name`      = {$itemnew['name'   ]}
			,`comment`   = {$itemnew['comment']}
		where `subscription` = {$itemid}
	";
//???	core::event('query', "Update subscription in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new subscriptions_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function delete_subscription ($args)
{//args: itemid
	$itemid = isset($args['itemid']) ? $args['itemid'] : null;

	$handle = core::handle();
	$table_subscription = core::table('subscription');

	$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	$sql =
	"
		delete from {$table_subscription}
		 where `subscription` = {$itemid}
	";
//???	core::event('query', "Delete subscription from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_abonentship ($args)
{
	$itemid  = isset($args['itemid' ]) ? $args['itemid' ] : null;

	$handle = core::handle();
	$table_abonentship = core::table('abonentship');

	$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	$sql =
	"
		select `account`
		  from {$table_abonentship}
		 where `subscription` = {$itemid}
	";
//???	core::event('query', "Select page of subscriptions from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = array();
	while ($row = mysql_fetch_row($res))
		$result[] = $row[0];
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __filterclause_subscription ($handle, $parent, $itemid)
{
	$result = array();

//???	// parent is ognored in subscriptions
//???	if (isset($parent)
//???	{
//???		$parent = "'" . mysql_real_escape_string($parent, $handle) . "'";
//???		$result[] = "(`parent` = {$parent})";
//???	}

	if (isset($itemid))
	{
		$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
		$result[] = "(`subscription` = {$itemid})";
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