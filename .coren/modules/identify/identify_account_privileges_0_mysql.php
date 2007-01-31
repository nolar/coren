<?php defined('CORENINPAGE') or die('Hack!');
//!!! крайне плохо то, что на один движок завязан join из трех таблиц (assignment & membership).
//!!! это надо как-то решить. разделить по разным методам и разным движкам?
####################################################################################################
####################################################################################################
####################################################################################################
#
class identify_account_privileges_0_mysql extends coren_broker
{
#
####################################################################################################
####################################################################################################
####################################################################################################
#
function select_privileges ($data)
{
	$identifier = $data['identifier'];

	$db = coren::db('handle');
	$table_privilege  = 'auth_privilege'         ;
	$table_membership = 'auth_account_membership';
	$table_assignment = 'auth_assignment'        ;

	$identifier = "'" . mysql_real_escape_string($identifier, $db) . "'";
	$sql =
	"
		select {$table_privilege}.`codename`
		  from {$table_privilege} join {$table_assignment} using (`privilege`) join {$table_membership} using (`rolegroup`)
		 where {$table_membership}.`account` = {$identifier}
	";
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));

	$result = array();
	while ($row = mysql_fetch_row($res))
		$result[] = $row[0];
	mysql_free_result($res);
	return $result;
}
#
####################################################################################################
####################################################################################################
####################################################################################################
}
?>