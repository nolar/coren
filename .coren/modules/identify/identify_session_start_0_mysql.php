<?php defined('CORENINPAGE') or die('Hack!');
####################################################################################################
####################################################################################################
####################################################################################################
#
class identify_session_start_0_mysql extends coren_broker
{
#
####################################################################################################
####################################################################################################
####################################################################################################
#
function session_start ($data)
{
	$identifier  = $data['identifier'];
	$account  = $data['account'];
	$remote   = $data['remote' ];
	$secure   = $data['secure' ];
	$period   = $data['period' ];

	$db = coren::db('handle');
	$table_session = 'auth_session';

	$identifier = "'" . mysql_real_escape_string($identifier, $db) . "'";
	$account    = "'" . mysql_real_escape_string($account   , $db) . "'";
	$remote     = "'" . mysql_real_escape_string($remote    , $db) . "'";
	$secure     = $secure ? 1 : 0;
	$period     = (integer) $period;
	$sql =
	"
		insert into {$table_session} (
			 `session`
			,`account`
			,`remote`
			,`secure`
			,`period`
			,`status`
			,`started`
			,`touched`
			,`closed` 
		) values (
			 {$identifier}
			,{$account}
			,{$remote}
			,{$secure}
			,{$period}
			,default
			,now()
			,now()
			,null
		)
	";
	$res = @mysql_query($sql, $db);
	if ($res === false) if (mysql_errno($db) === 1062) throw new identify_session_start_0_exception_duplicate(mysql_error($db), mysql_errno($db));
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));
}
#
####################################################################################################
####################################################################################################
####################################################################################################
}
?>