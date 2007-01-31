<?php defined('CORENINPAGE') or die('Hack!');
//todo: 1. add additional data to core::event()s in some methods. for ex. itemid and so on...

//!!!todo: заменить поля *_year/month/day/... на составные массивы

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!core::depend('admin_accounts_0')) return; // for exceptions

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class admin_accounts_0_mysql extends dbworker
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_account_count ($args)
{
	$parent     = isset($args['parent'    ]) ? $args['parent'    ] : null;
	$itemid     = isset($args['itemid'    ]) ? $args['itemid'    ] : null;
	$mask       = isset($args['mask'      ]) ? $args['mask'      ] : null;

	$handle = core::handle();
	$table_account = core::table('account');

	$filterclause = $this->__filterclause($handle, $parent, $itemid, $mask);
	$sql =
	"
		select count(*)
		  from {$table_account}
		 where {$filterclause}
	";
//???	core::event('query', "Select number of accounts in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = null;
	while ($row = mysql_fetch_row($res))
		$result = $row[0];
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_account_data ($args)
{
	$parent     = isset($args['parent'    ]) ? $args['parent'    ] : null;
	$itemid     = isset($args['itemid'    ]) ? $args['itemid'    ] : null;
	$mask       = isset($args['mask'      ]) ? $args['mask'      ] : null;
	$page   = isset($args['page'  ]) ? $args['page'  ] : null; $page   = (integer) $page  ;
	$size   = isset($args['size'  ]) ? $args['size'  ] : null; $size   = (integer) $size  ;
	$skip   = isset($args['skip'  ]) ? $args['skip'  ] : null; $skip   = (integer) $skip  ;
	$count  = isset($args['count' ]) ? $args['count' ] : null; $count  = (integer) $count ;
	$offset = isset($args['offset']) ? $args['offset'] : null; $offset = (integer) $offset;
	$sorting = isset($args['sorting']) ? $args['sorting'] : null;
	$reverse = isset($args['reverse']) ? $args['reverse'] : null;

	$handle = core::handle();
	$table_account = core::table('account');

	$sortings_asc  = array('account', 'disabled', 'reason', 'comment', 'email', 'agreement', 'logname');
	$sortings_desc = array('created', 'entered', 'touched');
	if (in_array($sorting, $sortings_asc )) { if (is_null($reverse)) $reverse = false; } else
	if (in_array($sorting, $sortings_desc)) { if (is_null($reverse)) $reverse = true ; } else
						{ $sorting = 'logname'; $reverse = false; }
	$reverse = ($reverse ? 'desc' : 'asc');
	$filterclause = $this->__filterclause($handle, $parent, $itemid, $mask);
	$sql =
	"
		select `account`, `created`, `entered`, `touched`, `disabled`, `reason`, `comment`, `email`, `agreement`, `logname`, `password`
		  from {$table_account}
		 where {$filterclause}
		 order by `{$sorting}` {$reverse}, `account` asc
		 limit {$size} offset {$offset}
	";
//???	core::event('query', "Select page of accounts from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = array();
	while ($row = mysql_fetch_row($res))
	{
		$result[$row[0]] = array(
			'account'	=> $row[ 0],
			'created'	=> $this->__splitdate($row[1]),
			'entered'	=> $this->__splitdate($row[2]),
			'touched'	=> $this->__splitdate($row[3]),
			'disabled'	=> $row[ 4],
			'reason'	=> $row[ 5],
			'comment'	=> $row[ 6],
			'email'		=> $row[ 7],
			'agreement'	=> $row[ 8],
			'logname'	=> $row[ 9],
			'password'	=> $row[10]);
	}
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function exists_account ($args)
{//args: itemnew; returns itemid
	$logname = isset($args['logname']) ? $args['logname'] : null;

	$handle = core::handle();
	$table_account = core::table('account');

	$logname = "'" . mysql_real_escape_string($logname, $handle) . "'";
	$sql =
	"
		select count(*)
		  from {$table_account}
		 where `logname` = {$logname}
	";
//???	core::event('query', "Select number of accounts in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = null;
	while ($row = mysql_fetch_row($res))
		$result = ($row[0] > 0);
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function create_account ($args)
{//args: itemnew; returns itemid
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null; if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_account = core::table('account');

	foreach ($itemnew as $key => $val) $itemnew[$key] = !is_scalar($val) || ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$sql =
	"
		insert into {$table_account} (
			 `account`
			,`created`
			,`entered`
			,`touched`
			,`disabled`
			,`reason`
			,`comment`
			,`email`
			,`agreement`
			,`logname`
			,`password`
		) values (
			 default
			,now()
			,default
			,default
			,0
			,default
			,default
			,{$itemnew['email']}
			,{$itemnew['agreement']}
			,{$itemnew['logname']}
			,{$itemnew['password']}
		)
	";
//???	core::event('query', "Insert account to database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new admin_accounts_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
	return mysql_insert_id($handle);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function update_email ($args)
{
	$account = isset($args['account']) ? $args['account'] : null;
	$email   = isset($args['email'  ]) ? $args['email'  ] : null;

	$handle = core::handle();
	$table_account = core::table('account');

	$account = "'" . mysql_real_escape_string($account, $handle) . "'";
	$email   = "'" . mysql_real_escape_string($email  , $handle) . "'";
	$sql =
	"
		update {$table_account} set
			`email` = {$email}
		where `account` = {$account}
	";
//???	core::event('query', "Update account in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new admin_accounts_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function insert_account ($args)
{//args: itemnew; returns itemid
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null; if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_account = core::table('account');

	$disabled  = $itemnew['disabled'] ? "1" : "0";
	foreach ($itemnew as $key => $val) $itemnew[$key] = !is_scalar($val) || ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$sql =
	"
		insert into {$table_account} (
			 `account`
			,`created`
			,`entered`
			,`touched`
			,`disabled`
			,`reason`
			,`comment`
			,`email`
			,`agreement`
			,`logname`
			,`password`
		) values (
			 default
			,now()
			,default
			,default
			,{$disabled}
			,{$itemnew['reason']}
			,{$itemnew['comment']}
			,{$itemnew['email']}
			,{$itemnew['agreement']}
			,{$itemnew['logname']}
			,{$itemnew['password']}
		)
	";
//???	core::event('query', "Insert account to database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new admin_accounts_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
	return mysql_insert_id($handle);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function update_account ($args)
{//args: itemid, itemnew
	$itemid  = isset($args['itemid']) ? $args['itemid'] : null;
	$itemnew = isset($args['itemnew']) ? $args['itemnew'] : null;
	if (!is_array($itemnew)) $itemnew = array();

	$handle = core::handle();
	$table_account = core::table('account');

	$disabled  = $itemnew['disabled'] ? "1" : "0";
	$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	foreach ($itemnew as $key => $val) $itemnew[$key] = !is_scalar($val) || ($val == '') ? "default" : "'" . mysql_real_escape_string($val, $handle) . "'";
	$sql =
	"
		update {$table_account} set
			 `disabled`	= {$disabled}
			,`reason`	= {$itemnew['reason']}
			,`comment`	= {$itemnew['comment']}
			,`email`	= {$itemnew['email']}
			,`agreement`	= {$itemnew['agreement']}
			,`logname`	= {$itemnew['logname']}
			,`password`	= {$itemnew['password']}
		where `account` = {$itemid}
	";
//???	core::event('query', "Update account in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new admin_accounts_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function delete_account ($args)
{//args: itemid
	$itemid = isset($args['itemid']) ? $args['itemid'] : null;

	$handle = core::handle();
	$table_account = core::table('account');

	$itemid = "'" . mysql_real_escape_string($itemid, $handle) . "'";
	$sql =
	"
		delete from {$table_account}
		 where `account` = {$itemid}
	";
//???	core::event('query', "Delete account from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function select_information ($args)
{
	$ids = isset($args['ids']) ? $args['ids'] : null;
	if (!is_array($ids)) $ids = array();

	$handle = core::handle();
	$table_information = core::table('account_information');

	$filterclause = array();
	foreach ($ids as $id) $filterclause[] = "'" . mysql_real_escape_string($id, $handle) . "'";
	$filterclause = empty($filterclause) ? "false" : "`account` in (" . implode(",", $filterclause) . ")";

	$sql =
	"
		select `account`, `value`, `content`
		  from {$table_information}
		 where {$filterclause}
	";
//???	core::event('query', "Select page of accounts from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = array();
	while ($row = mysql_fetch_row($res))
	{
		$result[] = array(
			'account'	=> $row[ 0],
			'value'		=> $row[ 1],
			'content'	=> $row[ 2]);
	}
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function update_information ($args)
{
	$account     = isset($args['account'    ]) ? $args['account'    ] : null;
	$information = isset($args['information']) ? $args['information'] : null;
	if (!is_array($information)) $information = array();
	if (empty($information)) return;

	$handle = core::handle();
	$table_information = core::table('account_information');

	$account = "'" . mysql_real_escape_string($account, $handle) . "'";
	$values = array();
	foreach ($information as $key => $val)
	{
		$key = "'" . mysql_real_escape_string($key, $handle) . "'";
		$val = "'" . mysql_real_escape_string($val, $handle) . "'";
		$values[] = "({$account},{$key},{$val})";
	}
	$values = implode(",", $values);

	$sql =
	"
		replace into {$table_information} (
			 `account`
			,`value`
			,`content`
		) values {$values}
	";
//???	core::event('query', "Update account in database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new admin_accounts_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function delete_information ($args)
{
	$account     = isset($args['account'    ]) ? $args['account'    ] : null;

	$handle = core::handle();
	$table_information = core::table('account_information');
	
	$account = "'" . mysql_real_escape_string($account, $handle) . "'";
	$sql =
	"
		delete from {$table_information}
		 where `account` = {$account}
	";
//???	core::event('query', "Delete account from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __filterclause ($handle, $parent, $itemid, $mask)
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
		$result[] = "(`account` = {$itemid})";
	}

	if (isset($mask))
	{
		$mask = "'%" . mysql_real_escape_string($mask, $handle) . "%'";
		$result[] = "(`logname` like {$mask})";
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