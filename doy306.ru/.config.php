<?php defined('CORENINPAGE') or die('Hack!');

// Behavior.
#$event_errors = true;//???
#$print_errors = 'html';
#$print_rstats = 'text';

$rts_enable = true;
$rts_prefix = "\n<plaintext>\n";


coren::set_xsl_format('test1');

//	$dependency_search_root = '';
	$dependency_search_path = 'tools/;modules/;modules/event/;modules/identify/;modules/utility/;modules/database/';
	$dependency_search_dirs = 'tools/;modules/;modules/event/;modules/identify/;modules/utility/;modules/database/';
	$response_comes_first	= true;

	// Database.
//	$default_database = '__default_db__';
	$default_database_implementer = 'database_mysql_0';
	$default_database_configs = array(
		'host'		=> 'localhost',
		'port'		=>  null,
		'user'		=> 'coren-doy306',
		'pass'		=> 'iria',
		'base'		=> 'coren-doy306',
		'charset'	=> 'utf8',
		'tableprefix'	=> 'coren_',
		'persistent'	=> 1,
		'---!!!'	=> '!!!');

/*
	$coren_database = '__coren__';
	$coren_database_implementer = 'database_mysql_0';
	$coren_database_configs = array(
		'host'		=> 'localhost',
		'port'		=>  null,
		'user'		=> 'coren-doy306',
		'pass'		=> 'iria',
		'base'		=> 'coren-doy306',
		'charset'	=> 'utf8',
		'tableprefix'	=> 'coren_',
		'persistent'	=> 1,
		'---!!!'	=> '!!!');
*/

#
# Trick to make resulting document be recognized as UTF-8 even if < ? xml > PI is not first.
#
//echo "\xEF\xBB\xBF";

?>