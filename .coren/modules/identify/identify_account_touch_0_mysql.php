<?php defined('CORENINPAGE') or die('Hack!');
####################################################################################################
####################################################################################################
####################################################################################################
#
class identify_account_touch_0_mysql extends coren_broker
{
#
####################################################################################################
####################################################################################################
####################################################################################################
#
function touch_account ($data)
{
	$identifier = $data['identifier'];

	$db = coren::db('handle');
	$table_account = 'auth_account';

	$identifier = "'" . mysql_real_escape_string($identifier, $db) . "'";
	$sql =
	"
		update {$table_account}
		   set `touched` = now()
		 where `account` = {$identifier}
	";
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));
}
#
####################################################################################################
####################################################################################################
####################################################################################################
}
?>