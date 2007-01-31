<?php defined('CORENINPAGE') or die('Hack!');
####################################################################################################
####################################################################################################
####################################################################################################
#
class account_credentials_0_mysql extends coren_broker
{
#
####################################################################################################
####################################################################################################
####################################################################################################
#
function verify ($data)
{
	$logname        = $data['logname'       ];
	$password_plain = $data['password_plain'];
	$password_md5   = $data['password_md5'  ];

	$db = coren::db('handle');
	$table_account = 'auth_account';

	$sql_logname        = "'" . mysql_real_escape_string($logname       , $db) . "'";
	$sql_password_plain = "'" . mysql_real_escape_string($password_plain, $db) . "'";
	$sql_password_md5   = "'" . mysql_real_escape_string($password_md5  , $db) . "'";

	$whereclauses = array();
	if (!is_null($password_plain)) $whereclauses[] = "(`logname` = {$sql_logname} and `password_plain` = {$sql_password_plain})";
	if (!is_null($password_md5  )) $whereclauses[] = "(`logname` = {$sql_logname} and `password_md5`   = {$sql_password_md5  })";

	if (empty($whereclauses)) return null;
	else $whereclauses = implode(" or ", $whereclauses);

	$sql =
	"
		select `account`, `disabled`, `reason`
		  from {$table_account}
		 where {$whereclauses}
		 order by `created` asc
		 limit 1
	";
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));

	$result = null;
	while ($row = mysql_fetch_row($res))
		$result = array(
			'account'	=> $row[0],
			'disabled'	=> $row[1],
			'reason'	=> $row[2]);
	mysql_free_result($res);
	return $result;
}
#
####################################################################################################
####################################################################################################
####################################################################################################
}
?>