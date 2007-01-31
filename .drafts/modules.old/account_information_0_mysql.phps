<?php defined('CORENINPAGE') or die('Hack!');

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class account_information_0_mysql extends dbworker
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function select_accounts_data ($args)
{
	$ids = isset($args['ids']) ? $args['ids'] : null;
	if (!is_array($ids)) $ids = array();

	$handle = core::handle();
	$table_account = core::table('account');

	$filterclause = array();
	foreach ($ids as $id) $filterclause[] = "'" . mysql_real_escape_string($id, $handle) . "'";
	$filterclause = empty($filterclause) ? "false" : "`account` in (" . implode(",", $filterclause) . ")";

	$sql =
	"
		select `account`, `created`, `entered`, `touched`, `disabled`, `reason`, `comment`, `email`, `agreement`, `logname`, `password`
		  from {$table_account}
		 where {$filterclause}
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

function select_accounts_info ($args)
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

function resolve_logname ($args)
{
	$logname = isset($args['logname']) ? $args['logname'] : null;

	$handle = core::handle();
	$table_account = core::table('account');

	$logname = "'" . mysql_real_escape_string($logname, $handle) . "'";
	$sql =
	"
		select `account`
		  from {$table_account}
		 where `logname` = {$logname}
	";
//???	core::event('query', "Select page of accounts from database.", array('query'=>$sql));
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = null;
	while ($row = mysql_fetch_row($res))
		$result = $row[0];
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
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