<?php defined('CORENINPAGE') or die('Hack!');
####################################################################################################
####################################################################################################
####################################################################################################
#
class account_information_0_mysql extends coren_broker
{
#
####################################################################################################
####################################################################################################
####################################################################################################
#
function select_information ($data)
{
	$identifiers = $data['identifiers'];
	if (empty($identifiers)) return array();

	$db = coren::db('handle');
	$table_account = 'auth_account';

	$sql_identifiers = array();
	foreach ($identifiers as $identifier)
		$sql_identifiers[] = "'" . mysql_real_escape_string($identifier, $db) . "'";
	$sql_identifiers = implode(",", $sql_identifiers);
	$sql =
	"
		select `account`, `created`, `entered`, `touched`, `disabled`, `reason`, `comment`, `logname`, `email`, `agreement`
		  from {$table_account}
		 where `account` in ({$sql_identifiers})
	";
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));

	$result = array();
	while ($row = mysql_fetch_row($res))
		$result[$row[0]] = array(
			'account'	=> $row[0],
			'created'	=> coren::db('split_date', array('value'=>$row[1])),
			'entered'	=> coren::db('split_date', array('value'=>$row[2])),
			'touched'	=> coren::db('split_date', array('value'=>$row[3])),
			'disabled'	=> $row[4],
			'reason'	=> $row[5],
			'comment'	=> $row[6],
			'logname'	=> $row[7],
			'email'		=> $row[8],
			'agreement'	=> $row[9]);
	mysql_free_result($res);
	return $result;
}
#
####################################################################################################
####################################################################################################
####################################################################################################
}
?>