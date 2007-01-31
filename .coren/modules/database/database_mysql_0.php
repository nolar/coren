<?php defined('CORENINPAGE') or die('Hack!');
####################################################################################################
####################################################################################################
####################################################################################################
#
# Configs and their default values:
#
# host			- (localhost)
# port			- (3306)
# user			- (?)
# pass			- (?)
# base			- (-)
# charset		- (-)
# persistent		- (false)
# table_module		- (coren_object        )
# table_package		- (coren_package       )
# table_config_site	- (coren_config_site   )
# table_config_package	- (coren_config_package)
# table_config_module	- (coren_config_module )
#
#
#
#
#
#
####################################################################################################
####################################################################################################
####################################################################################################
#
class database_mysql_0 extends coren_module
{
#
####################################################################################################
####################################################################################################
####################################################################################################
#
protected $tablefix;
protected $host;
protected $port;
protected $user;
protected $pass;
protected $base;
protected $charset;
protected $persistent;
#
protected $handle;
#
####################################################################################################
#
function __construct ($configs)
{
	parent::__construct($configs);

	if (isset($configs['tablefix'  ])) $this->tablefix   = $configs['tablefix'  ];
	if (isset($configs['host'      ])) $this->host       = $configs['host'      ];
	if (isset($configs['port'      ])) $this->port       = $configs['port'      ];
	if (isset($configs['user'      ])) $this->user       = $configs['user'      ];
	if (isset($configs['pass'      ])) $this->pass       = $configs['pass'      ];
	if (isset($configs['base'      ])) $this->base       = $configs['base'      ];
	if (isset($configs['charset'   ])) $this->charset    = $configs['charset'   ];
	if (isset($configs['persistent'])) $this->persistent = $configs['persistent'];
}
#
####################################################################################################
####################################################################################################
####################################################################################################
#
function handle ($data)
{
	if (!is_null($this->handle)) return $this->handle;

	// Connect. Throw on error.
	$handle = $this->persistent
		? @mysql_pconnect($this->host . (isset($this->port) ? ':'.$this->port : ''), $this->user, $this->pass)
		: @mysql_connect ($this->host . (isset($this->port) ? ':'.$this->port : ''), $this->user, $this->pass, true);
	if ($handle === false) throw new exception(mysql_error(), mysql_errno());//NB: without argument.

	// Select active database.
	if (isset($this->base))
	{
		$res = @mysql_select_db($this->base, $handle);
		if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
	}

	// Select codepage for client connection.
	if (isset($this->charset))
	{
		$res = @mysql_query("set names `" . str_replace('`', '``', $this->charset) . "`", $handle);
		if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
	}

//	// Start a new transaction.
//	$res = @mysql_query('start transaction with consistent snapshot', $handle);
//	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

	coren::handler('disconnect', coren::event_for_stage_free);

	return $this->handle = $handle;
}
#
####################################################################################################
#
function disconnect ($data)
{
	$handle = $this->handle;
	if (isset($handle))
	{
//		$res = @mysql_query('commit', $handle);
//		if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

		$res = @mysql_close($handle);
		if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
	}
}
#
####################################################################################################
#
function commit ($data)
{
	//!!!
	$handle = isset($data['handle']) ? $data['handle'] : null;
	if (isset($handle))
	{
		$res = @mysql_query('commit', $handle);
		if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
	}
}
#
####################################################################################################
#
function rollback ($data)
{
	//!!!
	$handle = isset($data['handle']) ? $data['handle'] : null;
	if (isset($handle))
	{
		$res = @mysql_query('rollback', $handle);
		if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));

		$res = @mysql_query('start transaction with consistent snapshot', $handle);
		if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
	}
}
#
####################################################################################################
#
function suffix ($data)
{
	return 'mysql';
}
#
####################################################################################################
####################################################################################################
####################################################################################################
#
/*
function quote_table ($data)//?????????
{
	$table = isset($data['table']) ? $data['table'] : null;

	if (isset($table) && is_scalar($table))
	{
		return '`' . str_replace('`', '``', $table) . '`';
	} else throw new exception("Bad table name: either null or not scalar.");
}
#
####################################################################################################
#
function quote_string ($data)//??????
{
	$string = isset($data['string']) ? $data['string'] : null;

	if (isset($string) && is_scalar($string))
	{
		return "'" . mysql_real_escape_string($string, $this->handle) . "'";
	} else throw new exception("Bad string: either null or not scalar.");
}
#
*/
####################################################################################################
#
function split_date ($data)
{
	$value = isset($data['value']) ? $data['value'] : null;
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
#
####################################################################################################
####################################################################################################
####################################################################################################
}
?>