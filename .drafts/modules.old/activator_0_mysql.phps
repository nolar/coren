<?php defined('CORENINPAGE') or die('Hack!');

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!core::depend('activator_0')) return; // for exceptions

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class activator_0_mysql extends dbworker
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function find_activation ($args)
{
	$code = isset($args['code']) ? $args['code'] : null;
	
	$handle = core::handle();
	$table  = core::table('activation');

	$code = "'" . mysql_real_escape_string($code, $handle) . "'";
	$sql =
	"
		select `mail`, `data`
		  from {$table}
		 where `code` = {$code} and `request_stamp` is not null and `confirm_stamp` is null
		 order by `request_stamp` desc
		 limit 1
	";
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	$result = null;
	while ($row = mysql_fetch_row($res))
	{
		$result = array('mail'=>$row[0], 'data'=>$row[1]);
	}
	mysql_free_result($res);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function make_activation ($args)
{
	$code = isset($args['code']) ? $args['code'] : null;
	$data = isset($args['data']) ? $args['data'] : null;
	$mail = isset($args['mail']) ? $args['mail'] : null;
	$addr = isset($args['addr']) ? $args['addr'] : null;

	$handle = core::handle();
	$table  = core::table('activation');

	$code = "'" . mysql_real_escape_string($code, $handle) . "'";
	$data = "'" . mysql_real_escape_string($data, $handle) . "'";
	$mail = "'" . mysql_real_escape_string($mail, $handle) . "'";
	$addr = "'" . mysql_real_escape_string($addr, $handle) . "'";
	$sql =
	"
		insert into {$table} (
			 `code`
			,`data`
			,`mail`
			,`request_addr`
			,`request_stamp`
			,`confirm_addr`
			,`confirm_stamp`
		) values (
			 {$code}
			,{$data}
			,{$mail}
			,{$addr}
			,now()
			,default
			,default
		)
	";
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new activator_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function mark_activation ($args)
{
	$code = isset($args['code']) ? $args['code'] : null;
	$addr = isset($args['addr']) ? $args['addr'] : null;

	$handle = core::handle();
	$table  = core::table('activation');

	$code = "'" . mysql_real_escape_string($code, $handle) . "'";
	$addr = "'" . mysql_real_escape_string($addr, $handle) . "'";
	$sql =
	"
		update {$table} set
			 `confirm_addr`  = {$addr}
			,`confirm_stamp` = now()
		where `code` = {$code} and `request_stamp` is not null and `confirm_stamp` is null
	";
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) === 1062) throw new activator_0_exception_duplicate(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

?>