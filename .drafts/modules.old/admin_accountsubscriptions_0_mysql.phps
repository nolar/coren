<?php defined('CORENINPAGE') or die('Hack!');
//todo: 1. add additional data to core::event()s in some methods. for ex. itemid and so on...

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!core::depend('admin_accountsubscriptions_0')) return; // for exceptions

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class admin_accountsubscriptions_0_mysql extends dbworker
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

	$filterclause = $this->__filterclause($handle, $parent, $itemid);
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
	$parent     = isset($args['parent'    ]) ? $args['parent'    ] : null;
	$itemid     = isset($args['itemid'    ]) ? $args['itemid'    ] : null;
	$reverse = null;//todo via $filter in module
	$sorting = null;//todo via $filter in module

	$handle = core::handle();
	$table_subscription = core::table('subscription');

	$reverse = ($reverse ? 'asc' : 'desc');
	$sorting = ('name');
	$filterclause = $this->__filterclause($handle, $parent, $itemid);
	$sql =
	"
		select `subscription`, `name`, `comment`
		  from {$table_subscription}
		 where {$filterclause}
		 order by `{$sorting}` {$reverse}, `subscription` asc
	";
//???	core::event('query', "Select page of subscriptions from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = array();
	while ($row = mysql_fetch_row($res))
	{
		$result[$row[0]] = array(
			'subscription'	=> $row[0],
			'name'		=> $row[1],
			'comment'	=> $row[2]);
	}
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_assignment ($args)
{
	$parent     = isset($args['parent'    ]) ? $args['parent'    ] : null;

	$handle = core::handle();
	$table_assignment = core::table('abonentship');

	$parent = "'" . mysql_real_escape_string($parent, $handle) . "'";
	$sql =
	"
		select `subscription`
		  from {$table_assignment}
		 where `account` = {$parent}
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

function assign_subscription ($args)
{
	$account = isset($args['account']) ? $args['account'] : null;
	$subscription = isset($args['subscription']) ? $args['subscription'] : null;

	$handle = core::handle();
	$table_assignment = core::table('abonentship');

	$account = "'" . mysql_real_escape_string($account, $handle) . "'";
	$subscription = "'" . mysql_real_escape_string($subscription, $handle) . "'";
	$sql =
	"
		replace into {$table_assignment} (
			 `account`
			,`subscription`
		) values (
			 {$account}
			,{$subscription}
		)
	";
//???	core::event('query', "Insert subscription to database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
	return mysql_insert_id($handle);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function revoke_subscription ($args)
{//args: itemid
	$account = isset($args['account']) ? $args['account'] : null;
	$subscription = isset($args['subscription']) ? $args['subscription'] : null;

	$handle = core::handle();
	$table_assignment = core::table('abonentship');

	$account = "'" . mysql_real_escape_string($account, $handle) . "'";
	$subscription = "'" . mysql_real_escape_string($subscription, $handle) . "'";
	$sql =
	"
		delete from {$table_assignment}
		 where `account` = {$account} and `subscription` = {$subscription}
	";
//???	core::event('query', "Delete subscription from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __filterclause ($handle, $parent, $itemid)
{
	$result = array();

//???	// parent is ognored in accounts
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
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

?>