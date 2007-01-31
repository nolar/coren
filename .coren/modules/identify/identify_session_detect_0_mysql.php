<?php defined('CORENINPAGE') or die('Hack!');
####################################################################################################
####################################################################################################
####################################################################################################
#
class identify_session_detect_0_mysql extends coren_broker
{
#
####################################################################################################
####################################################################################################
####################################################################################################
#
function select_information ($data)
{
	$identifier = $data['identifier'];

	$db = coren::db('handle');
	$table_session = 'auth_session';

	$identifier = "'" . mysql_real_escape_string($identifier, $db) . "'";
	$sql =
	"
		select `session`, `account`, `remote`, `secure`, `period`, `status`, `started`, `touched`, `closed`
		  from {$table_session}
		 where `session` = {$identifier}
		 order by `started` desc
		 limit 1
	";
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));

	$result = null;
	while ($row = mysql_fetch_row($res))
		$result = array(
			'session'	=> $row[0],
			'account'	=> $row[1],
			'remote'	=> $row[2],
			'secure'	=> $row[3],
			'period'	=> $row[4],
			'status'	=> $row[5],
			'started'	=> coren::db('split_date', array('value'=>$row[6])),
			'touched'	=> coren::db('split_date', array('value'=>$row[7])),
			'closed'	=> coren::db('split_date', array('value'=>$row[8])));
	mysql_free_result($res);
	return $result;
}
#
####################################################################################################
####################################################################################################
####################################################################################################
}
?>