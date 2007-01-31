<?php defined('CORENINPAGE') or die('Hack!');

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!core::depend('logger_database_0')) return; // for exceptions

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class logger_database_0_mysql extends dbworker
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected $table;
protected $delayed;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __construct ($configs)
{
	parent::__construct($configs);

	$this->table   = isset($configs['table'  ]) ? $configs['table'  ] : null;
	$this->delayed = isset($configs['delayed']) ? $configs['delayed'] : null;

	if (!isset($this->table)) throw new exception("misconfig_table");
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function create ($args)
{
	$handle = core::handle();
	$table = core::table($this->table);

	$sql =
	"
		create table {$table} (
			 `stamp`	datetime
			,`remote`	varchar(255)
			,`script`	varchar(255)
			,`executor`	varchar(255)
			,`module`	varchar(255)
			,`method`	varchar(255)
			,`identifier`	varchar(255)
			,`message`	longtext
			,index (`stamp`     )
			,index (`remote`    )
			,index (`script`    )
			,index (`executor`  )
			,index (`module`    )
			,index (`method`    )
			,index (`identifier`)
		) engine=myisam
	";
	$res = @mysql_query($sql, $handle);
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function write ($args)
{
	$handle = core::handle();
	$table = core::table($this->table);

	$delayed = $this->delayed ? "delayed" : "";

	$remote     = $_SERVER['REMOTE_ADDR'];
	$script     = SELFFILE;
	$executor   = isset($args['executor'  ]) ? $args['executor'  ] : null;
	$module     = isset($args['module'    ]) ? $args['module'    ] : null;
	$method     = isset($args['method'    ]) ? $args['method'    ] : null;
	$identifier = isset($args['identifier']) ? $args['identifier'] : null;
	$message    = isset($args['message'   ]) ? $args['message'   ] : null;

	$remote     = ($remote     == '') ? "default" : "'" . mysql_real_escape_string($remote    , $handle) . "'";
	$script     = ($script     == '') ? "default" : "'" . mysql_real_escape_string($script    , $handle) . "'";
	$executor   = ($executor   == '') ? "default" : "'" . mysql_real_escape_string($executor  , $handle) . "'";
	$module     = ($module     == '') ? "default" : "'" . mysql_real_escape_string($module    , $handle) . "'";
	$method     = ($method     == '') ? "default" : "'" . mysql_real_escape_string($method    , $handle) . "'";
	$identifier = ($identifier == '') ? "default" : "'" . mysql_real_escape_string($identifier, $handle) . "'";
	$message    = ($message    == '') ? "default" : "'" . mysql_real_escape_string($message   , $handle) . "'";

	$sql =
	"
		insert {$delayed} into {$table} (
			 `stamp`
			,`remote`
			,`script`
			,`executor`
			,`module`
			,`method`
			,`identifier`
			,`message`
		) values (
			 now()
			,{$remote}
			,{$script}
			,{$executor}
			,{$module}
			,{$method}
			,{$identifier}
			,{$message}
		)
	";
	$res = @mysql_query($sql, $handle);
	if ($res === false) if (mysql_errno($handle) == 1146) throw new logger_database_0_table_absent(mysql_error($handle), mysql_errno($handle));
	if ($res === false) throw new exception(mysql_error($handle), mysql_errno($handle));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

?>