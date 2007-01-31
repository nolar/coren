<?php defined('CORENINPAGE') or die('Hack!');
####################################################################################################
####################################################################################################
####################################################################################################
#
class database_mysql_0_mysql extends coren_broker
{
#
####################################################################################################
####################################################################################################
####################################################################################################
#
protected $tablefix;
#
####################################################################################################
#
function __construct ($configs)
{
	parent::__construct($configs);
	if (isset($configs['tablefix'])) $this->tablefix = $configs['tablefix'];
}
#
####################################################################################################
####################################################################################################
####################################################################################################
#
public function modules ($data)
{
	$db = coren::db('handle');
	$table_module = '`coren_module`';

	$sql =
	"
		select `module`, `implementer`, `database`
		  from {$table_module}
		 where (`disabled` is null or `disabled` = 0)
	";
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));

	$result = array();
	while ($row = mysql_fetch_row($res))
		$result[] = array(
			'module'	=> $row[0],
			'implementer'	=> $row[1],
			'database'	=> $row[2]);
	mysql_free_result($res);
	return $result;
}
#
####################################################################################################
#
public function configs ($data)
{
	$db = coren::db('handle');
	$table_config = '`coren_config`';

	$sql =
	"
		select `module`, `config`, `value`
		  from {$table_config}
	";
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));

	$result = array();
	while ($row = mysql_fetch_row($res))
		$result[] = array(
			'module'	=> $row[0],
			'config'	=> $row[1],
			'value'		=> $row[2]);
	mysql_free_result($res);
	return $result;
}
#
####################################################################################################
#
public function handlers ($data)
{
	$db = coren::db('handle');
	$table_handler = '`coren_handler`';

	$sql =
	"
		select `module`, `method`, `event`, `map`
		  from {$table_handler}
		 order by `order` asc
	";
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));

	$result = array();
	while ($row = mysql_fetch_row($res))
		$result[] = array(
			'module'	=> $row[0],
			'method'	=> $row[1],
			'event'		=> $row[2],
			'map'		=> $row[3]);
	mysql_free_result($res);
	return $result;
}
#
####################################################################################################
#
public function parameters ($data)
{
	$db = coren::db('handle');
	$table_parameter = '`coren_parameter`';

	$sql =
	"
		select `parameter`, `value`
		  from {$table_parameter}
	";
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));

	$result = array();
	while ($row = mysql_fetch_row($res))
		$result[] = array(
			'parameter'	=> $row[0],
			'value'		=> $row[1]);
	mysql_free_result($res);
	return $result;
}
#
####################################################################################################
####################################################################################################
####################################################################################################
}
?>