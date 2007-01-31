<?php defined('CORENINPAGE') or die('Hack!');
####################################################################################################
#################################  U T I L I T Y    C L A S S E S  #################################
####################################################################################################
#
abstract class coren_object
{
	public function __construct ($configs) {}
}
#
abstract class coren_module extends coren_object {}
abstract class coren_broker extends coren_object {}
#
####################################################################################################
#
abstract class coren_tool
{
}
#
####################################################################################################
#
class coren_exception extends exception
{
	protected $stage ;
	protected $module;
	protected $method;
	public function __construct ($message = null, $code = 0)
	{
		parent::__construct($message, $code);
		$this->stage  = coren::current_stage ();
		$this->module = coren::current_module();
		$this->method = coren::current_method();
	}
	public function getStage  () { return $this->stage ; }
	public function getModule () { return $this->module; }
	public function getMethod () { return $this->method; }
}
#
class coren_exception_stack				extends coren_exception			{}
#
class coren_exception_abort_				extends coren_exception			{}
class coren_exception_abort_event			extends coren_exception_abort_		{}
class coren_exception_abort_stage			extends coren_exception_abort_		{}
class coren_exception_abort_work			extends coren_exception_abort_		{}
#
class coren_exception_no_				extends coren_exception			{}
class coren_exception_no_module_			extends coren_exception_no_		{}
class coren_exception_no_module_description		extends coren_exception_no_module_	{}
class coren_exception_no_module_implementer		extends coren_exception_no_module_	{}
class coren_exception_no_module_instance		extends coren_exception_no_module_	{}
class coren_exception_no_module_method			extends coren_exception_no_module_	{}
class coren_exception_no_broker_			extends coren_exception_no_		{}
class coren_exception_no_broker_description		extends coren_exception_no_broker_	{}
class coren_exception_no_broker_implementer		extends coren_exception_no_broker_	{}
class coren_exception_no_broker_instance		extends coren_exception_no_broker_	{}
class coren_exception_no_broker_method			extends coren_exception_no_broker_	{}
class coren_exception_no_broker_database		extends coren_exception_no_broker_	{}
class coren_exception_no_broker_suffix			extends coren_exception_no_broker_	{}
#
class coren_exception_bad_				extends coren_exception			{}
class coren_exception_bad_filename			extends coren_exception_bad_		{}
class coren_exception_bad_context			extends coren_exception_bad_		{}
class coren_exception_bad_dependency			extends coren_exception_bad_		{}
class coren_exception_bad_implementer			extends coren_exception_bad_		{}
class coren_exception_bad_database			extends coren_exception_bad_		{}
class coren_exception_bad_module			extends coren_exception_bad_		{}
class coren_exception_bad_method			extends coren_exception_bad_		{}
class coren_exception_bad_config			extends coren_exception_bad_		{}
class coren_exception_bad_value				extends coren_exception_bad_		{}
class coren_exception_bad_event				extends coren_exception_bad_		{}
class coren_exception_bad_map				extends coren_exception_bad_		{}
class coren_exception_bad_dstkey			extends coren_exception_bad_		{}
class coren_exception_bad_srckey			extends coren_exception_bad_		{}
class coren_exception_bad_xml_node			extends coren_exception_bad_		{}
class coren_exception_bad_xml_name			extends coren_exception_bad_		{}
class coren_exception_bad_xml_slot			extends coren_exception_bad_		{}
class coren_exception_bad_xslt_format			extends coren_exception_bad_		{}
class coren_exception_bad_xslt_parameter		extends coren_exception_bad_		{}
class coren_exception_bad_xslt_value			extends coren_exception_bad_		{}
class coren_exception_bad_privileges			extends coren_exception_bad_		{}
class coren_exception_bad_privilege			extends coren_exception_bad_		{}
#
class coren_exception_config_				extends coren_exception			{}
class coren_exception_config_rts_enable			extends coren_exception_config_		{}
class coren_exception_config_rts_prefix			extends coren_exception_config_		{}
class coren_exception_config_rts_suffix			extends coren_exception_config_		{}
class coren_exception_config_default_database		extends coren_exception_config_		{}
class coren_exception_config_coren_database		extends coren_exception_config_		{}
class coren_exception_config_created_module_name	extends coren_exception_config_		{}
class coren_exception_config_response_comes_first	extends coren_exception_config_		{}
class coren_exception_config_dependency_search_root	extends coren_exception_config_		{}
class coren_exception_config_dependency_search_dirs	extends coren_exception_config_		{}
class coren_exception_config_have_no_database		extends coren_exception_config_		{}
#
####################################################################################################
############################  B E G I N    O F    M A I N    C L A S S  ############################
####################################################################################################
#
class coren
{
#
####################################################################################################
#################################  C L A S S    C O N S T A N T S  #################################
####################################################################################################
#
const version_major		= 0;
const version_minor		= 0;
const version_patch		= 0;
#
####################################################################################################
#
const conf_xml_encoding		= 'utf-8';
const data_xml_encoding		= 'utf-8';
const xslt_xml_encoding		= 'utf-8';
#
const conf_xml_version		= '1.0';
const data_xml_version		= '1.0';
const xslt_xml_version		= '1.0';
#
const xslt_xsl_version		= '1.0';
#
const data_xml_ns_prefix	= 'coren';
const xslt_xml_ns_prefix	= 'xsl';
#
const conf_xml_ns_uri		= 'http://coren.numeri.net/namespaces/coren/configuration/';
const data_xml_ns_uri		= 'http://coren.numeri.net/namespaces/coren/data-document/';
const xslt_xml_ns_uri		= 'http://www.w3.org/1999/XSL/Transform';
#
####################################################################################################
#
const event_for_stage_init	= 'coren!stage(init)'	;
const event_for_stage_prework	= 'coren!stage(prework)';
const event_for_stage_content	= 'coren!stage(content)';
const event_for_stage_epiwork	= 'coren!stage(epiwork)';
const event_for_stage_free	= 'coren!stage(free)'	;
#
const event_for_fatal		= 'coren:fatal'		;
#
####################################################################################################
#
const default_data_key		= '@';
#
####################################################################################################
#############################  R U N - T I M E    S T A T I S T I C S  #############################
####################################################################################################
#
/*rts*/protected static $rts_verbosity		= null;
/*rts*/protected static $rts_prefix		= null;
/*rts*/protected static $rts_suffix		= null;
#
/*rts*/protected static $rts_load_stamp		= null;
/*rts*/protected static $rts_call_stamp		= null;
/*rts*/protected static $rts_startup_stamp	= null;
/*rts*/protected static $rts_total_runtime	= null;
#
/*rts*/protected static $rts_count_of_loads	= 0;
/*rts*/protected static $rts_count_of_coren	= array();
/*rts*/protected static $rts_count_of_module	= array();
/*rts*/protected static $rts_count_of_method	= array();
#
/*rts*/protected static $rts_time_for_loads	= 0.0;
/*rts*/protected static $rts_time_for_stage	= array();
/*rts*/protected static $rts_time_for_module	= array();
/*rts*/protected static $rts_time_for_method	= array();
#
####################################################################################################
#
/*rts*/protected static function rts_reset ()
/*rts*/{
/*rts*/	self::$rts_startup_stamp = microtime(true);
/*rts*/
/*rts*/	//NB: here is a hack: created function is out-of-class, so it can see only public methods.
/*rts*/	$function = create_function('$class', 'return get_class_methods($class);');
/*rts*/	$methods = $function(get_class());
/*rts*/	foreach ($methods as $method)
/*rts*/		if ($method{0} != '_')
/*rts*/			self::$rts_count_of_coren[$method] = 0;
/*rts*/}
#
####################################################################################################
#
/*rts*/protected static function rts_print ()
/*rts*/{
/*rts*/	self::$rts_total_runtime = microtime(true) - self::$rts_startup_stamp;
/*rts*/	if (!self::$rts_verbosity) return;
/*rts*/	print(self::$rts_prefix);
/*rts*/
/*rts*/	{
/*rts*/		printf("Total running time  : %f sec (100%%)\n",
/*rts*/			self::$rts_total_runtime);
/*rts*/	}
/*rts*/	{
/*rts*/		$t = self::$rts_time_for_loads;
/*rts*/		$c = self::$rts_count_of_loads;
/*rts*/		printf("Loading of php-files: %f sec (%2.0f%%); %3d files loaded.\n",
/*rts*/			$t, 100.0 * $t / self::$rts_total_runtime, $c);
/*rts*/	}
/*rts*/	print("\n");
/*rts*/
/*rts*/	print("Per stages:\n");
/*rts*/	$all_count = $all_time = $maxlength = 0; $list = array();
/*rts*/	foreach (self::$rts_time_for_stage as $stage => $unused_variable)
/*rts*/	{
/*rts*/		$title = "'{$stage}'";
/*rts*/		$all_count += $count = 1;
/*rts*/		$all_time  += $time  = self::$rts_time_for_stage[$stage];
/*rts*/		$maxlength = max($maxlength, strlen($title));
/*rts*/		$list[$title] = array($title, $count, $time);
/*rts*/	}
/*rts*/	foreach ($list as $item)
/*rts*/	{
/*rts*/		list($title, $count, $time) = $item;
/*rts*/		printf("Time in stage %-{$maxlength}s: %f sec (%2.0f%%).\n",
/*rts*/			$title, $time, 100.0 * $time / self::$rts_total_runtime);
/*rts*/	}
/*rts*/	{
/*rts*/		printf("Time in stages%-{$maxlength}s: %f sec (%2.0f%%).\n",
/*rts*/			'', $all_time, 100.0 * $all_time / self::$rts_total_runtime);
/*rts*/	}
/*rts*/	print("\n");
/*rts*/
/*rts*/	print("Per modules:\n");
/*rts*/	$all_count = $all_time = $maxlength = 0; $list = array();
/*rts*/	foreach (self::$rts_time_for_module as $module => $unused_variable)
/*rts*/	{
/*rts*/		$title = "'{$module}'";
/*rts*/		$all_count += $count = self::$rts_count_of_module[$module];
/*rts*/		$all_time  += $time  = self::$rts_time_for_module[$module];
/*rts*/		$maxlength = max($maxlength, strlen($title));
/*rts*/		$list[$title] = array($title, $count, $time);
/*rts*/	}
/*rts*/	ksort($list, SORT_STRING);
/*rts*/	foreach ($list as $item)
/*rts*/	{
/*rts*/		list($title, $count, $time) = $item;
/*rts*/		printf("Time in module %-{$maxlength}s: %f sec (%2.0f%%); called %3d times.\n",
/*rts*/			$title, $time, 100.0 * $time / self::$rts_total_runtime, $count);
/*rts*/	}
/*rts*/	{
/*rts*/		printf("Time in modules%-{$maxlength}s: %f sec (%2.0f%%); called %3d times.\n",
/*rts*/			'', $all_time, 100.0 * $all_time / self::$rts_total_runtime, $all_count);
/*rts*/	}
/*rts*/	print("\n");
/*rts*/
/*rts*/	print("Per methods:\n");
/*rts*/	$all_count = $all_time = $maxlength = 0; $list = array();
/*rts*/	foreach (self::$rts_time_for_method          as $module => $unused_variable_1)
/*rts*/	foreach (self::$rts_time_for_method[$module] as $method => $unused_variable_2)
/*rts*/	{
/*rts*/		$title = "'{$module}::{$method}'";
/*rts*/		$all_count += $count = self::$rts_count_of_method[$module][$method];
/*rts*/		$all_time  += $time  = self::$rts_time_for_method[$module][$method];
/*rts*/		$maxlength = max($maxlength, strlen($title));
/*rts*/		$list[$title] = array($title, $count, $time);
/*rts*/	}
/*rts*/	ksort($list, SORT_STRING);
/*rts*/	foreach ($list as $item)
/*rts*/	{
/*rts*/		list($title, $count, $time) = $item;
/*rts*/		printf("Time in method %-{$maxlength}s: %f sec (%2.0f%%); called %3d times.\n",
/*rts*/			$title, $time, 100.0 * $time / self::$rts_total_runtime, $count);
/*rts*/	}
/*rts*/	{
/*rts*/		printf("Time in methods%-{$maxlength}s: %f sec (%2.0f%%); called %3d times.\n",
/*rts*/			'', $all_time, 100.0 * $all_time / self::$rts_total_runtime, $all_count);
/*rts*/	}
/*rts*/	print("\n");
/*rts*/
/*rts*/	print("Per coren calls:\n");
/*rts*/	$all_count = $all_time = $maxlength = 0; $list = array();
/*rts*/	foreach (self::$rts_count_of_coren as $method => $unused_variable)
/*rts*/	{
/*rts*/		$title = "'{$method}'";
/*rts*/		$all_count += $count = self::$rts_count_of_coren[$method];
/*rts*/		$all_time  += $time  = 0;
/*rts*/		$maxlength = max($maxlength, strlen($title));
/*rts*/		$list[$title] = array($title, $count, $time);
/*rts*/	}
/*rts*/	ksort($list, SORT_STRING);
/*rts*/	foreach ($list as $item)
/*rts*/	{
/*rts*/		list($title, $count, $time) = $item;
/*rts*/		printf("Coren method %-{$maxlength}s called %3d times.\n",
/*rts*/			$title, $count);
/*rts*/	}
/*rts*/	{
/*rts*/		printf("Coren methods%-{$maxlength}s called %3d times.\n",
/*rts*/			'', $all_count);
/*rts*/	}
/*rts*/	print("\n");
/*rts*/
/*rts*/	print(self::$rts_suffix);
/*rts*/}
#
####################################################################################################
################################  P H P - F I L E    L O A D I N G  ################################
####################################################################################################
#
protected static $load_class_root	= null;
protected static $load_class_path	= array();
protected static $load_class_file	= array();
#
protected static $load_class_cache	= array();
#
####################################################################################################
#
private static         $load__context ;
private static         $load__filepath;
private static function load__include () { extract(self::$load__context, EXTR_PREFIX_INVALID | EXTR_REFS, ''); return include(self::$load__filepath); }
private static function load__require () { extract(self::$load__context, EXTR_PREFIX_INVALID | EXTR_REFS, ''); return require(self::$load__filepath); }
#
protected static function load__wrapper ($filepath, $context, $optional)
{
/*rts*/	$rts_prevtimer = microtime(true) - self::$rts_load_stamp;
/*rts*/	self::$rts_load_stamp = microtime(true);
/*rts*/	try
/*rts*/	{
		self::$load__context  = $context;
		self::$load__filepath = $filepath;
		$result = $optional ? self::load__include() : self::load__require() ;
/*rts*/	}
/*rts*/	catch (exception $exception) {}
/*rts*/	$rts_leave = microtime(true);
/*rts*/	$rts_timer = $rts_leave - self::$rts_load_stamp;
/*rts*/	self::$rts_count_of_loads ++;
/*rts*/	self::$rts_time_for_loads += $rts_timer;
/*rts*/	self::$rts_call_stamp     += $rts_timer;
/*rts*/	self::$rts_load_stamp = microtime(true) - $rts_prevtimer;
/*rts*/	if (isset($exception)) throw $exception;
	return $result;
}
#
####################################################################################################
#
public static function load_file ($filename, $context = null, $optional = null)
{
/*rts*/	self::$rts_count_of_coren[__FUNCTION__]++;

	if (is_null($context)) $context = array();

/*xvc*/	if (!is_string($filename))
/*xvc*/		throw new coren_exception_bad_filename("Can not load a file because its name is of bad type (must be string).");

/*xvc*/	if (!is_array($context))
/*xvc*/		throw new coren_exception_bad_context("Can not load a file because context is of bad type (must be null or array).");

	return self::load__wrapper($filename, $context, $optional);
}
#
####################################################################################################
#
public static function load_class ($classname)
{
/*rts*/	self::$rts_count_of_coren[__FUNCTION__]++;

/*xvc*/	if (!is_string($classname))
/*xvc*/		throw new coren_exception_bad_classname("Can not check or load a class because its name is of bad type (must be string).");

	if (isset(self::$load_class_cache[$classname]))
		return self::$load_class_cache[$classname];

	if (isset(self::$load_class_file[$classname]))
	{
		$root = is_null(self::$load_class_root) ? dirname(__FILE__) : self::$load_class_root;
		$file = self::$load_class_file[$classname];
		if (file_exists($filename = $root . DIRECTORY_SEPARATOR . $file))
			self::load__wrapper($filename, array(), true);
	} else
	if ((bool) self::$load_class_path)
	{
		$root = is_null(self::$load_class_root) ? dirname(__FILE__) : self::$load_class_root;
		$base = $classname . COREN_EXTENSION;
		foreach (self::$load_class_path as $path)
			if (file_exists($filename = $root . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $base))
				{ self::load__wrapper($filename, array(), true); break; }
	} else
	{
		$filename = $classname . COREN_EXTENSION;
		self::load__wrapper($filename, array(), true);
	}

	return self::$load_class_cache[$classname] = class_exists($classname);
}
#
####################################################################################################
##################  C O M M O N    S T R U C T U R E S    A N D    M E T H O D S  ##################
####################################################################################################
#
protected static $module_names		= array();
protected static $module_classes	= array();
protected static $module_databases	= array();
protected static $module_data		= array();
#
protected static $stage			= null;
protected static $stack			= array();
#
####################################################################################################
#
public static function current_stage ()
{
/*rts*/	self::$rts_count_of_coren[__FUNCTION__]++;
	return self::$stage;
}
#
####################################################################################################
#
public static function current_module ()
{
/*rts*/	self::$rts_count_of_coren[__FUNCTION__]++;
	return empty(self::$stack) ? null : self::$stack[count(self::$stack)-1]['module'];
}
#
####################################################################################################
#
public static function current_method ()
{
/*rts*/	self::$rts_count_of_coren[__FUNCTION__]++;
	return empty(self::$stack) ? null : self::$stack[count(self::$stack)-1]['method'];
}
#
####################################################################################################
#
public static function version ()
{
/*rts*/	self::$rts_count_of_coren[__FUNCTION__]++;
	return self::version_major . '.' . self::version_minor . '.' . self::version_patch;
}
#
####################################################################################################
###############################  M O D U L E    M A N A G E M E N T  ###############################
####################################################################################################
#
protected static $default_database	= null;
protected static $template_of_name	= null;
#
####################################################################################################
#
protected static function add_module ($name, $class, $database, $data)
{
	if (is_null($database)) $database = self::$default_database;

/*xvc*/	if (!is_string($name))
/*xvc*/		throw new coren_exception_bad_module("Can not add module because its own name is of bad type (must be string).");
/*xvc*/	if ($name == '')
/*xvc*/		throw new coren_exception_bad_module("Can not add module because its own name is empty string (must be non-empty).");

/*xvc*/	if (!is_string($class))
/*xvc*/		throw new coren_exception_bad_class("Can not add module because name of its class is of bad type (must be string).");
/*xvc*/	if ($class == '')
/*xvc*/		throw new coren_exception_bad_class("Can not add module because name of its class is empty string (must be non-empty).");
/*xvc*/	if (preg_match('/(^[^_a-zA-Z])|([^_a-zA-Z0-9])/', $class))
/*xvc*/		throw new coren_exception_bad_class("Can not add module because name of its class is not valid identifier (must be valid).");

/*xvc*/	if (!is_string($database))
/*xvc*/		throw new coren_exception_bad_database("Can not add module because name of its database is of bad type (must be string or null).");
/*xvc*/	if ($database == '')
/*xvc*/		throw new coren_exception_bad_database("Can not add module because name of its database is empty string (must be non-empty).");

	if (isset(self::$module_names[$name])) return false;

	self::$module_names    [$name] = $name;
	self::$module_classes  [$name] = $class;
	self::$module_databases[$name] = $database;
	self::$module_data     [$name] = array();//$data!!!!!!!!!!!

/*rts*/	self::$rts_time_for_module[$name] = 0.0;
/*rts*/	self::$rts_count_of_module[$name] = 0;
/*rts*/	self::$rts_time_for_method[$name] = array();
/*rts*/	self::$rts_count_of_method[$name] = array();

	return true;
}
#
####################################################################################################
#
public static function create_module ($class, $database, $data)
{
/*rts*/	self::$rts_count_of_coren[__FUNCTION__]++;

	if (is_null($database)) $database = self::$default_database;

/*xvc*/	if (!is_string($class))
/*xvc*/		throw new coren_exception_bad_class("Can not create module because name of its class is of bad type (must be string).");
/*xvc*/	if ($class == '')
/*xvc*/		throw new coren_exception_bad_class("Can not create module because name of its class is empty string (must be non-empty).");
/*xvc*/	if (preg_match('/(^[^_a-zA-Z])|([^_a-zA-Z0-9])/', $class))
/*xvc*/		throw new coren_exception_bad_class("Can not create module because name of its class is not valid identifier (must be valid).");

/*xvc*/	if (!is_string($database))
/*xvc*/		throw new coren_exception_bad_database("Can not create module because name of its database is of bad type (must be string or null).");
/*xvc*/	if ($database == '')
/*xvc*/		throw new coren_exception_bad_database("Can not create module because name of its database is empty string (must be non-empty).");

	static $index = 1;
	$prev = self::$template_of_name;
	do
	{
		$name = sprintf(self::$template_of_name, $index++);
		//NB: this is to prevent module names, which do not contain placeholders (like %s or %d).
		if ($name == $prev) return null;
		else $prev = $name;
	}
	while (self::add_module($name, $class, $database, $data) === false);

	return $name;
}
#
####################################################################################################
##############################  M O D U L E    O P E R A B I L I T Y  ##############################
####################################################################################################
#
protected static $instances_of_modules	= array();
protected static $instances_of_brokers	= array();
protected static $class_suffixes	= array();
#
####################################################################################################
#
protected static function instantiate_module ($module)
{
	if (isset(self::$instances_of_modules[$module]))
		return self::$instances_of_modules[$module];

	$ok = isset(self::$module_names[$module]) || self::conf_fetch_module($module);
/*xsc*/	if (!$ok)
/*xsc*/		throw new coren_exception_no_module_name("Can not instantiate module '{$module}' because this module does not exist.");

	$ok = self::load_class($class = self::$module_classes[$module]);
/*xsc*/	if (!$ok)
/*xsc*/		throw new coren_exception_no_module_class("Can not instantiate module '{$module}' because its class '{$class}' was not found nor loaded.");

	//NB: Constructor's exceptions are handled in call(), not here.
	$instance = new $class(self::$module_data[$module]);

/*xvc*/	//NB: This is impossible though, but we have to check.
/*xvc*/	if (is_null($instance))
/*xvc*/		throw new coren_exception_no_module_instance("Can not instantiate module '{$module}' because its class '{$class}' has constructed null instance somewhy.");

	self::$instances_of_modules[$module] = $instance;
	self::$instances_of_brokers[$module] = array();
	return $instance;
}
#
####################################################################################################
#
protected static function instantiate_broker ($module, $database, $instance_of_module)
{
	if (isset(self::$instances_of_brokers[$module][$database]))
		return self::$instances_of_brokers[$module][$database];

	$suffix = isset(self::$suffixes[$database])
		? (self::$suffixes[$database])
		: (self::$suffixes[$database] = self::call(null, $database, 'suffix', null));

/*xvc*/	if (!is_string($suffix))
/*xvc*/		throw new coren_exception_no_broker_suffix("Can not instantiate broker '{$module}' because database module '{$database}' has returned class suffix of wrong type (must be string).");
/*xvc*/	if ($suffix == '')
/*xvc*/		throw new coren_exception_no_broker_suffix("Can not instantiate broker '{$module}' because database module '{$database}' has returned empty class suffix (must be non-empty).");
/*xvc*/	if (preg_match('/[^_a-zA-Z0-9]/', $suffix))
/*xvc*/		throw new coren_exception_no_broker_suffix("Can not instantiate broker '{$module}' because database module '{$database}' has returned invalid class suffix '{$suffix}'.");

	$classes = class_parents($instance_of_module);
	$miclass = get_class($instance_of_module);
	if (!is_array($classes)) $classes = array();
	array_unshift($classes, $miclass);

	$class = null;
	foreach ($classes as $candidate)
		if (self::load_class($candidate . '_' . $suffix))
		{
			$class = $candidate . '_' . $suffix;
			break;
		}

/*xsc*/	if (is_null($class))
/*xsc*/		throw new coren_exception_no_broker_class("Can not instantiate broker '{$module}' because its class '{$miclass}_{$suffix}' was not found, and no ancestor of '{$miclass}' suffixed with '{$suffix}' were found too.");

	//NB: Constructor's exceptions are handled in call(), not here.
	$instance = new $class(self::$module_data[$module]);

/*xvc*/	//NB: This is impossible though, but we have to check.
/*xvc*/	if (is_null($instance))
/*xvc*/		throw new coren_exception_no_broker_instance("Can not instantiate broker '{$module}' because its class '{$class}' has constructed null instance somewhy.");

	self::$instances_of_brokers[$module][$database] = $instance;
	return $instance;
}
#
####################################################################################################
#
protected static function call ($database, $module, $method, $data)
{
/*rts*/	$rts_prevtimer = microtime(true) - self::$rts_call_stamp;
/*rts*/	self::$rts_call_stamp = microtime(true);

	array_push(self::$stack, compact('database', 'module', 'method'));
	try
	{
		$instance = self::instantiate_module($module);
		if (!is_null($database))
		$instance = self::instantiate_broker($module, $database, $instance);

/*xsc*/		if (!is_callable(array($instance, $method)))
/*xsc*/			throw is_null($database)
/*xsc*/				? new coren_exception_no_module_method("Can not call method '{$method}' of module '{$module}' because this method does not exist.")
/*xsc*/				: new coren_exception_no_broker_method("Can not call method '{$method}' of broker '{$module}' because this method does not exist.");

		if (!is_array($data)) $data = is_null($data) ? array() : array(self::default_data_key => $data);
		$result = call_user_func(array($instance, $method), $data);
	}
	catch (exception $exception) {}
	array_pop(self::$stack);

/*rts*/	$rts_leave = microtime(true);
/*rts*/	if (isset(self::$module_names[$module]))
/*rts*/	{
/*rts*/		$rts_timer = $rts_leave - self::$rts_call_stamp;
/*rts*/		self::$rts_time_for_module[$module] += $rts_timer;
/*rts*/		self::$rts_count_of_module[$module] ++;
/*rts*/		if (!isset(self::$rts_time_for_method[$module][$method]))
/*rts*/		{
/*rts*/			self::$rts_time_for_method[$module][$method] = 0.0;
/*rts*/			self::$rts_count_of_method[$module][$method] = 0;
/*rts*/		}
/*rts*/		self::$rts_time_for_method[$module][$method] += $rts_timer;
/*rts*/		self::$rts_count_of_method[$module][$method] ++;
/*rts*/	}
/*rts*/	self::$rts_call_stamp = microtime(true) - $rts_prevtimer;

	if (isset($exception)) throw $exception;
	return $result;
}
#
####################################################################################################
###########################  D A T A B A S E    C O N N E C T I V I T Y  ###########################
####################################################################################################
#
public static function db ($method, $data = null)
{
/*rts*/	self::$rts_count_of_coren[__FUNCTION__]++;

/*x?c*/	if (empty(self::$stack))
/*x?c*/		throw new coren_exception_stack("API method " . __FUNCTION__ . "() can be called only from within other modules.");

/*xvc*/	if (!is_string($method))
/*xvc*/		throw new coren_exception_bad_method("Can not call database method because its name is of bad type (must be string).");

	if (is_null(self::$stack[count(self::$stack)-1]['database']))
	{
		$module   = self::$stack[count(self::$stack)-1]['module'];
		$database = self::$module_databases[$module];
	} else
	{
		$module   = self::$stack[count(self::$stack)-1]['database'];
		$database = null;
	}

	return self::call($database, $module, $method, $data);
}
#
####################################################################################################
##################################  E V E N T    H A N D L I N G  ##################################
####################################################################################################
# 
protected static $handlers = array();
#
####################################################################################################
#
protected static function add_handler ($module, $method, $event, $map)
{
/*xvc*/	if (!is_string($module))
/*xvc*/		throw new coren_exception_bad_module("Can not add handler because name of callback module is of bad type (must be string).");

/*xvc*/	if (!is_string($method))
/*xvc*/		throw new coren_exception_bad_method("Can not add handler because name of callback method is of bad type (must be string).");

/*xvc*/	if (!is_string($event))
/*xvc*/		throw new coren_exception_bad_event("Can not add handler because name of its event is of bad type (must be string).");

	if (is_null($map))
	{
		$map = array();
	} else
	if (is_array($map))
	{
		$map_old = $map; $map = array();
		foreach ($map_old as $dstkey => $srckey)
		{
			if (is_null($dstkey)) $dstkey = '';
/*xvc*/			if (!is_string($dstkey))
/*xvc*/				throw new coren_exception_bad_dstkey("Can not add handler because dstkey in its map is of bad type (must be string or null).");

			if (is_null($srckey)) $srckey = '';
/*xvc*/			if (!is_string($srckey))
/*xvc*/				throw new coren_exception_bad_srckey("Can not add handler because srckey in its map is of bad type (must be string or null).");

			$map[$dstkey] = $srckey;
		}
	} else
	if (is_string($map))
	{
		$map_parts = explode(' ', $map); $map = array();
		foreach ($map_parts as $map_part)
		{
			$map_part = explode('=', $map_part, 2);
			$dstkey = isset($map_part[0]) ? trim($map_part[0]) : null;
			$srckey = isset($map_part[1]) ? trim($map_part[1]) : null;
			if (!is_null($srckey) && !is_null($dstkey))
				$map[$dstkey] = $srckey;
		}
/*xvc*/	} else
/*xvc*/	{
/*xvc*/		throw new coren_excetion_bad_map("Can not add handler because its map is of bad type (must be either null, or properly formatted string, or properly structured array).");
	}

	if (!isset(self::$handlers[$event])) self::$handlers[$event] = array();
	self::$handlers[$event][] = compact('module', 'method', 'map');
}
#
####################################################################################################
#
public static function handler ($method, $event, $map = null)
{
/*rts*/	self::$rts_count_of_coren[__FUNCTION__]++;

/*x?c*/	if (empty(self::$stack))
/*x?c*/		throw new coren_exception_stack("API method " . __FUNCTION__ . "() can be called only from within other modules.");

	$module = self::$stack[count(self::$stack)-1]['module'];
	return self::add_handler($module, $method, $event, $map);
}
#
####################################################################################################
#
public static function event ($event, $data = null)
{
/*rts*/	self::$rts_count_of_coren[__FUNCTION__]++;

/*xvc*/	if (!is_string($event))
/*xvc*/		throw new coren_exception_bad_event("Can not trigger event because its name is of bad type (must be string).");

	$ok = isset(self::$handlers[$event]) || self::conf_fetch_handler($event);
	if (!$ok) return null;

	if (empty(self::$stack))
	{
		$module = '';
		$method = '';
		$class  = '';
	} else
	{
		$module = self::$stack[count(self::$stack)-1]['module'];
		$method = self::$stack[count(self::$stack)-1]['method'];
		$class  = self::$module_classes[$module];
	}

	if (!is_array($data)) $data = is_null($data) ? array() : array(self::default_data_key => $data);
	$data[null] = compact('class', 'module', 'method', 'event');

	$result = null; $done = array();
	foreach (self::$handlers[$event] as $callback)
	{
		if (!isset($done[$callback['module']])) $done[$callback['module']] = array(); else
		if ( isset($done[$callback['module']][$callback['method']])) continue;
		$done[$callback['module']][$callback['method']] = true/*NB: any non-null value; existence of keys is actually checked. */;

		foreach ($callback['map'] as $dstkey => $srckey)
		{
			if (array_key_exists($srckey, $data))
			{
				$data[$dstkey] = $data[$srckey];
				unset($data[$srckey]);
			} else
			if (array_key_exists($dstkey, $data))
			{
				unset($data[$dstkey]);
			}
		}

		try
		{
			$result = self::call(null, $callback['module'], $callback['method'], $data);
			if (!is_null($result)) break;
		}
		catch (coren_exception_abort_event $exception)
		{
			break;
		}
	}
	return $result;
}
#
####################################################################################################
##############################  P R I V I L E G E    H A N D L I N G  ##############################
####################################################################################################
#
protected static $privileges = array();
#
####################################################################################################
#
public static function grant ($privileges)
{
/*rts*/	self::$rts_count_of_coren[__FUNCTION__]++;

/*xvc*/	if (!is_array($privileges))
/*xvc*/		throw new coren_exception_bad_privileges("Can not grant privileges because their list is of bad type (must be array).");

	foreach ($privileges as $privilege)
	{
//???		if (is_null   ($privilege)) continue;//NB: no else.
/*xvc*/		if (!is_string($privilege))
/*xvc*/			throw new coren_exception_bad_privilege("Can not grant privilege because its name is of bad type (must be string).");

		self::$privileges[$privilege] = true;//NB: any non-null value; only existence of keys is actually used.
	}
}
#
####################################################################################################
#
public static function have ($privilege)
{
/*rts*/	self::$rts_count_of_coren[__FUNCTION__]++;

	if (is_null($privilege)) return true;

/*xvc*/	if (!is_string($privilege))
/*xvc*/		throw new coren_exception_bad_privilege("Can not check for privilege because its name is of bad type (must be string).");

	return (bool) isset(self::$privileges[$privilege]);
}
#
####################################################################################################
###################################  C O N F    D O C U M E N T  ###################################//conf routines???
####################################################################################################
#
protected static $conf_document		= null;
protected static $conf_xpath		= null;
protected static $conf_element		= null;
#
protected static $prefetch_modules	= null;
protected static $prefetch_handlers	= null;
#
####################################################################################################
#
public static function conf ()
{
/*rts*/	self::$rts_count_of_coren[__FUNCTION__]++;
	return self::$conf_document;
}
#
####################################################################################################
#
protected static function conf_initialize ()
{
	if (!defined('COREN_CONFIGURATION_FILE'))
		throw new coren_exception_config_/*!!!*/("Config file is not set.");

	self::$conf_document = new DOMDocument(self::conf_xml_version, self::conf_xml_encoding);
	$old_errors = libxml_use_internal_errors(true);
	$loaded = self::$conf_document->load(COREN_CONFIGURATION_FILE, LIBXML_COMPACT | LIBXML_XINCLUDE);
	libxml_use_internal_errors($old_errors);
	if (!$loaded)
	{
		$error = libxml_get_last_error();
		if (is_null($error))
			throw new coren_exception_configuration_file("Failed to load configuration file with unknown error.");
		else
			throw new coren_exception_configuration_file("Failed to load configuration file: " . $error->message);
	}

	self::$conf_xpath = new DOMXPath(self::$conf_document);
	self::$conf_xpath->registerNamespace("coren", self::conf_xml_ns_uri);

	//!!!todo: 2. make node selectable for script via constants (maybe some special 'id/name' attribute?).
	//!!!todo: 3. make required(!) attribute coren:target, which must have value 'php'?
	$query = "//coren:configuration";//NB: it can be non-root.(???)
	$nodes = self::$conf_xpath->query($query);
/*xcc*/	if ($nodes->length > 1) throw new coren_exception_configuration_element("Only one top-level element allowed, but there is {$nodes->length}.");
	if ($nodes->length < 1) throw new coren_exception_configuration_element("At least one top-level element required, but there are none.");
	self::$conf_element = $nodes->item(0);
}
#
####################################################################################################
#
protected static function conf_apply_values ()
{
	//NB: this is for sutiations, when modules or events are fetched when config file is not ready yet.
	//NB: for example, event to notify about fatal error, but if this error occured in config reading.
	if (is_null(self::$conf_xpath) || is_null(self::$conf_element))
		throw new coren_exception_/*!!!*/("Configuration is not ready for parsing.");

//!!!todo: make it so that extension is either loaded from config, or determined by coren's extension.
//!!!todo: so it is not contstant, and used only internally in coren main class.
//!!!todo: f.e., <load-class-extention='php'/> or <load-class-base='%s.php'/>
	if (!defined('COREN_EXTENSION')) define('COREN_EXTENSION', '.' . pathinfo(__FILE__, PATHINFO_EXTENSION));//!!!!

/*rts*/	$query = "coren:rts-verbosity";
/*rts*/	$nodes = self::$conf_xpath->query($query, self::$conf_element);
/*rts*/	if ($nodes->length > 1) throw new coren_exception_config_/*!!!*/("Only one <rts-verbosity> element allowed.");
/*rts*/	if ($nodes->length > 0) self::$rts_verbosity = trim($nodes->item(0)->nodeValue);
/*rts*/
/*rts*/	$query = "coren:rts-prefix";
/*rts*/	$nodes = self::$conf_xpath->query($query, self::$conf_element);
/*rts*/	if ($nodes->length > 1) throw new coren_exception_config_/*!!!*/("Only one <rts-prefix> element allowed.");
/*rts*/	if ($nodes->length > 0) self::$rts_prefix = $nodes->item(0)->nodeValue;
/*rts*/
/*rts*/	$query = "coren:rts-suffix";
/*rts*/	$nodes = self::$conf_xpath->query($query, self::$conf_element);
/*rts*/	if ($nodes->length > 1) throw new coren_exception_config_/*!!!*/("Only one <rts-suffix> element allowed.");
/*rts*/	if ($nodes->length > 0) self::$rts_suffix = $nodes->item(0)->nodeValue;

	$query = "coren:load-class-root";
	$nodes = self::$conf_xpath->query($query, self::$conf_element);
/*xcc*/	if ($nodes->length > 1) throw new coren_exception_config_/*!!!*/("Too many root dependecies. Only one must be.");
	if ($nodes->length > 0) self::$load_class_root = trim($nodes->item(0)->nodeValue);

	$query = "coren:load-class-path";
	$nodes = self::$conf_xpath->query($query, self::$conf_element);
	foreach ($nodes as $node)
	{
		//!!!todo: check if it has children (it must not; throw if it has them).
		//!!!todo: trim, then skip empty(?)
		self::$load_class_path[] = trim($node->nodeValue);
	}

	$query = "coren:load-class-file";
	$nodes = self::$conf_xpath->query($query, self::$conf_element);
	foreach ($nodes as $node)
	{
		$class = $node->getAttributeNS(self::conf_xml_ns_uri, 'class');
		//!!!todo: check that class is not empty (i.e. is set); throw otherwise.
		//!!!todo: check if it has children (it must not; throw if it has them).
		//!!!todo: trim, then skip empty(?)
		self::$load_class_file[$class] = trim($node->nodeValue);
	}

	$query = "coren:default-database";
	$nodes = self::$conf_xpath->query($query, self::$conf_element);
/*err*/	if ($nodes->length > 1) throw new coren_exception_config_/*!!!*/("Only one <database> element allowed.");
	if ($nodes->length > 0) self::$default_database = trim($nodes->item(0)->getAttributeNS(self::conf_xml_ns_uri, 'name'));

	$query = "coren:template-of-name";
	$nodes = self::$conf_xpath->query($query, self::$conf_element);
/*err*/	if ($nodes->length > 1) throw new coren_exception_config_/*!!!*/("Only one <autoname> element allowed.");
	if ($nodes->length > 0) self::$template_of_name = trim($nodes->item(0)->getAttributeNS(self::conf_xml_ns_uri, 'name'));

	$query = "coren:response-at-top";
	$nodes = self::$conf_xpath->query($query, self::$conf_element);
/*err*/	if ($nodes->length > 1) throw new coren_exception_config_/*!!!*/("Only one <response-at-top> element allowed.");
	if ($nodes->length > 0) self::$response_at_top = true;

	$query = "coren:response-prefix";
	$nodes = self::$conf_xpath->query($query, self::$conf_element);
/*err*/	if ($nodes->length > 1) throw new coren_exception_config_/*!!!*/("Only one <response-prefix> element allowed.");
	if ($nodes->length > 0) self::$response_prefix = $nodes->item(0)->nodeValue;;

	$query = "coren:response-suffix";
	$nodes = self::$conf_xpath->query($query, self::$conf_element);
/*err*/	if ($nodes->length > 1) throw new coren_exception_config_/*!!!*/("Only one <response-suffix> element allowed.");
	if ($nodes->length > 0) self::$response_suffix = $nodes->item(0)->nodeValue;;

	//!!!todo: <response-encoding>, <default-xslt-format> ?

	$query = "coren:prefetch-modules";
	$nodes = self::$conf_xpath->query($query, self::$conf_element);
/*err*/	if ($nodes->length > 1) throw new coren_exception_config_/*!!!*/("Only one <prefetch-modules> element allowed.");
	if ($nodes->length > 0) self::$prefetch_modules = true;

	$query = "coren:prefetch-handlers";
	$nodes = self::$conf_xpath->query($query, self::$conf_element);
/*err*/	if ($nodes->length > 1) throw new coren_exception_config_/*!!!*/("Only one <prefetch-handlers> element allowed.");
	if ($nodes->length > 0) self::$prefetch_handlers = true;
}
#
####################################################################################################
#
protected static function conf_assign_parameters ($processor)
{
	$query = "coren:parameter";
	$nodes = self::$conf_xpath->query($query, self::$conf_element);
	foreach ($nodes as $node)
	{
		$name = $node->getAttributeNS(self::conf_xml_ns_uri, 'name');
/*err*/		if ($name == '')
/*err*/			throw new coren_exception_config_/*???*/("??? absent name attribute");
		$value = $node->nodeValue;
		$processor->setParameter(''/* how do this looks like in xml? ???*/, $name, $value);
	}
}
#
####################################################################################################
#
protected static function conf_fetch_modules ()
{
/*err*/	if (is_null(self::$conf_xpath) || is_null(self::$conf_element))
/*err*/		throw new coren_exception_config_/*!!!*/("Configuration is not ready for parsing.");

	$query = self::$prefetch_modules
		? "coren:module[@coren:prefetch!='never' or not(@coren:prefetch)]"
		: "coren:module[@coren:prefetch='always']";
	$nodes = self::$conf_xpath->query($query, self::$conf_element);
	foreach ($nodes as $node) self::conf_add_module_node($node);
}
#
####################################################################################################
#
protected static function conf_fetch_handlers ()
{
/*err*/	if (is_null(self::$conf_xpath) || is_null(self::$conf_element))
/*err*/		throw new coren_exception_config_/*!!!*/("Configuration is not ready for parsing.");

	$query = self::$prefetch_handlers
		? "coren:handler[@coren:prefetch!='never' or not(@coren:prefetch)]"
		: "coren:handler[@coren:prefetch='always']";
	$nodes = self::$conf_xpath->query($query, self::$conf_element);
	foreach ($nodes as $node) self::conf_add_handler_node($node);
}
#
####################################################################################################
#
//!!!! TODO: make an api methods coren::check_module($module) & coren::check_handler($event)
protected static function conf_fetch_module ($module)
{
/*err*/	if (is_null(self::$conf_xpath) || is_null(self::$conf_element))
/*err*/		throw new coren_exception_config_/*!!!*/("Configuration is not ready for parsing.");

	$node = null;
	$apos = strpos($module, "'") !== false;
	$quot = strpos($module, '"') !== false;
	if ($apos && $quot)
	{
		$query = "coren:module[@coren:name]";
		$nodes = self::$conf_xpath->query($query, self::$conf_element);
		foreach ($nodes as $tmp)
			if ($tmp->getAttributeNS(self::$conf_xml_ns_uri, 'name') == $module)
				{ $node = $tmp; break; }
	} else
	{
		$quoted = ($apos ? '"' : "'") . $module . ($apos ? '"' : "'");
		$query = "coren:module[@coren:name={$quoted}]";
		$nodes = self::$conf_xpath->query($query, self::$conf_element);
		if ($nodes->length > 0) $node = $nodes->item(0);
	}

	if ($result = !is_null($node))
		self::conf_add_module_node($node);
	return $result;
}
#
####################################################################################################
#
//!!!! TODO: make an api methods coren::check_module($module) & coren::check_handler($event)
protected static function conf_fetch_handler ($event)
{
/*err*/	if (is_null(self::$conf_xpath) || is_null(self::$conf_element))
/*err*/		throw new coren_exception_config_/*!!!*/("Configuration is not ready for parsing.");

	$node = null;
	$apos = strpos($event, "'") !== false;
	$quot = strpos($event, '"') !== false;
	if ($apos && $quot)
	{
		$query = "coren:handler[@coren:event]";
		$nodes = self::$conf_xpath->query($query, self::$conf_element);
		foreach ($nodes as $tmp)
			if ($tmp->getAttributeNS(self::$conf_xml_ns_uri, 'event') == $event)
				{ $node = $tmp; break; }
	} else 
	{
		$quoted = ($apos ? '"' : "'") . $event . ($apos ? '"' : "'");
		$query = "coren:handler[@coren:event={$quoted}]";
		$nodes = self::$conf_xpath->query($query, self::$conf_element);
		if ($nodes->length > 0) $node = $nodes->item(0);
	}

	if ($result = !is_null($node))
		self::conf_add_handler_node($node);
	return $result;
}
#
####################################################################################################
#
protected static function conf_add_module_node ($node)
{
	$name     = $node->getAttributeNS(self::conf_xml_ns_uri, 'name'    );
	$class    = $node->getAttributeNS(self::conf_xml_ns_uri, 'class'   );
	$database = $node->getAttributeNS(self::conf_xml_ns_uri, 'database');
	return self::add_module($name, $class, $database, $node);
}
#
####################################################################################################
#
protected static function conf_add_handler_node ($node)
{
	$eventi = $node->getAttributeNS(self::conf_xml_ns_uri, 'event' );
	$module = $node->getAttributeNS(self::conf_xml_ns_uri, 'module');
	$method = $node->getAttributeNS(self::conf_xml_ns_uri, 'method');
	$map    = null;//!!!//!!!todo: read mappings
	return self::add_handler($module, $method, $eventi, $map);
}
#
####################################################################################################
###################################  D A T A    D O C U M E N T  ###################################//data routines???
####################################################################################################
#
protected static $data_document = null;
protected static $data_element  = null;
protected static $data_xpath    = null;
#
####################################################################################################
#
public static function data ()
{
/*rts*/	self::$rts_count_of_coren[__FUNCTION__]++;
	return self::$data_document;
}
#
####################################################################################################
#
protected static function data_initialize ()
{
	self::$data_document = new DOMDocument(self::data_xml_version, self::data_xml_encoding);
	self::$data_element =
		self::$data_document->createElementNS(self::data_xml_ns_uri, self::data_xml_ns_prefix . ':' . 'data');
		self::$data_element -> setAttributeNS(self::data_xml_ns_uri, self::data_xml_ns_prefix . ':' . 'version'      , self::version()    );
		self::$data_element -> setAttributeNS(self::data_xml_ns_uri, self::data_xml_ns_prefix . ':' . 'version_major', self::version_major);
		self::$data_element -> setAttributeNS(self::data_xml_ns_uri, self::data_xml_ns_prefix . ':' . 'version_minor', self::version_minor);
		self::$data_element -> setAttributeNS(self::data_xml_ns_uri, self::data_xml_ns_prefix . ':' . 'version_patch', self::version_patch);
	self::$data_document->appendChild(self::$data_element);

	self::$data_xpath = new DOMXPath(self::$data_document);
	self::$data_xpath->registerNamespace("coren", self::data_xml_ns_uri);
}
#
####################################################################################################
#
public static function name ($node, $name)//!!!prefix with data_
{
/*rts*/	self::$rts_count_of_coren[__FUNCTION__]++;

/*err*/	if (is_null  ($name)) $name = ''; else
/*err*/	if (is_scalar($name)) $name = trim($name); else
/*err*/		throw new coren_exception_bad_xml_name("Can not mark xml node by name because this name is of bad type (must be scalar or null).");

/*err*/	if (!($node instanceof DOMNode))
/*err*/		throw new coren_exception_bad_xml_node("Can not mark xml node by name because this node is of bad type (must be child of DOMNode).");

	//!!! node ����� ��������� ����������. � ����� ������ �������� ��� �������� elements. �� ������� ������ ������ ��������.
	if ($name == '') $node->removeAttributeNS(self::own_ns_uri, 'name');
	else $node->setAttributeNS(self::own_ns_uri, self::own_ns_prefix . ':' . 'name', $name);
	return $node;
}
#
####################################################################################################
#
public static function slot ($node, $slot)//!!!prefix with data_
{
/*rts*/	self::$rts_count_of_coren[__FUNCTION__]++;

/*err*/	if (is_null  ($slot)) $slot = ''; else
/*err*/	if (is_scalar($slot)) $slot = trim($slot); else
/*err*/		throw new coren_exception_bad_xml_slot("Can not mark xml node by slot because this slot is of bad type (must be scalar or null).");

/*err*/	if (!($node instanceof DOMNode))
/*err*/		throw new coren_exception_bad_xml_node("Can not mark xml node by slot because this node is of bad type (must be child of DOMNode).");

	//!!! node ����� ��������� ����������. � ����� ������ �������� ��� �������� elements. �� ������� ������ ������ ��������.
	if ($slot == '') $node->removeAttributeNS(self::own_ns_uri, 'slot');
	else $node->setAttributeNS(self::own_ns_uri, self::own_ns_prefix . ':' . 'slot', $slot);
	return $node;
}
#
####################################################################################################
#
public static function names ()//!!!prefix with data_: data_name_list()?
{
/*rts*/	self::$rts_count_of_coren[__FUNCTION__]++;

	$xattr = coren::own_ns_prefix . ':' . 'name';
	$xexpr = "//@{$xattr}";
	$nodes = self::$xml_xpath->query($xexpr);

	$result = array();
	foreach ($nodes as $node) $result[] = $node->nodeValue;
	return array_unique($result);
}
#
####################################################################################################
#
public static function slots ()//!!!prefix with data_
{
/*rts*/	self::$rts_count_of_coren[__FUNCTION__]++;

	$xattr = coren::own_ns_prefix . ':' . 'slot';
	$xexpr = "//@{$xattr}";
	$nodes = self::$xml_xpath->query($xexpr);

	$result = array();
	foreach ($nodes as $node) $result[] = $node->nodeValue;
	return array_unique($result);
}
#
####################################################################################################
#
public static function name_used ($name)//!!!prefix with data_
{
/*rts*/	self::$rts_count_of_coren[__FUNCTION__]++;

/*err*/	if (is_scalar($name)) $name = trim($name); else
/*err*/		throw new coren_exception_bad_xml_name("Can not check if name is used in xml because this name is of bad type (must be scalar).");
/*err*/	if ($name == '')
/*err*/		throw new coren_exception_bad_xml_name("Can not check if name is used in xml because this name is empty string (must be non-empty).");

	$apos = strpos($name, "'") !== false;
	$quot = strpos($name, '"') !== false;
	if ($apos && $quot)
	{
		$result = in_array($name, self::names());
	} else
	{
		$name = ($apos ? '"' : "'") . $name . ($apos ? '"' : "'");
		$xattr = coren::own_ns_prefix . ':' . 'name';
		$xexpr = "//*[@{$xattr}={$name}]";
		$nodes = self::$xml_xpath->query($xexpr);
		$result = $nodes->length > 0;
	}
	return $result;
}
#
####################################################################################################
#
public static function slot_used ($slot)//!!!prefix with data_
{
/*rts*/	self::$rts_count_of_coren[__FUNCTION__]++;

/*err*/	if (is_scalar($slot)) $slot = trim($slot); else
/*err*/		throw new coren_exception_bad_xml_slot("Can not check if slot is used in xml because this slot is of bad type (must be scalar).");
/*err*/	if ($slot == '')
/*err*/		throw new coren_exception_bad_xml_slot("Can not check if slot is used in xml because this slot is empty string (must be non-empty).");

	$apos = strpos($slot, "'") !== false;
	$quot = strpos($slot, '"') !== false;
	if ($apos && $quot)
	{
		$result = in_array($slot, self::slots());
	} else
	{
		$slot = ($apos ? '"' : "'") . $slot . ($apos ? '"' : "'");
		$xattr = coren::own_ns_prefix . ':' . 'slot';
		$xexpr = "//*[@{$xattr}={$slot}]";
		$nodes = self::$xml_xpath->query($xexpr);
		$result = $nodes->length > 0;
	}
	return $result;
}
#
####################################################################################################
#
public static function name_nodes ($name)//!!!prefix with data_
{
/*rts*/	self::$rts_count_of_coren[__FUNCTION__]++;

/*err*/	if (is_scalar($name)) $name = trim($name); else
/*err*/		throw new coren_exception_bad_xml_name("Can not enumerate xml nodes by name because this name is of bad type (must be scalar).");
/*err*/	if ($name == '')
/*err*/		throw new coren_exception_bad_xml_name("Can not enumerate xml nodes by name because this name is empty string (must be non-empty).");

	$result = array();
	$apos = strpos($name, "'") !== false;
	$quot = strpos($name, '"') !== false;
	if ($apos && $quot)
	{
		$xattr = coren::own_ns_prefix . ':' . 'name';
		$xexpr = "//*[@{$xattr}]";
		$nodes = self::$xml_xpath->query($xexpr);
		foreach ($nodes as $node)
			if ($node->getAttributeNS(self::own_ns_uri, "name") == $name)
				$result[] = $node;
	} else
	{
		$name = ($apos ? '"' : "'") . $name . ($apos ? '"' : "'");
		$xattr = coren::own_ns_prefix . ':' . 'name';
		$xexpr = "//*[@{$xattr}={$name}]";
		$nodes = self::$xml_xpath->query($xexpr);
		foreach ($nodes as $node)
			$result[] = $node;
	}
	return $result;
}
#
####################################################################################################
#
public static function slot_nodes ($slot)//!!!prefix with data_
{
/*rts*/	self::$rts_count_of_coren[__FUNCTION__]++;

/*err*/	if (is_scalar($slot)) $slot = trim($slot); else
/*err*/		throw new coren_exception_bad_xml_slot("Can not enumerate xml nodes by slot because this slot is of bad type (must be scalar).");
/*err*/	if ($slot == '')
/*err*/		throw new coren_exception_bad_xml_slot("Can not enumerate xml nodes by slot because this slot is empty string (must be non-empty).");

	$result = array();
	$apos = strpos($slot, "'") !== false;
	$quot = strpos($slot, '"') !== false;
	if ($apos && $quot)
	{
		$xattr = coren::own_ns_prefix . ':' . 'slot';
		$xexpr = "//*[@{$xattr}]";
		$nodes = self::$xml_xpath->query($xexpr);
		foreach ($nodes as $node)
			if ($node->getAttributeNS(self::own_ns_uri, "slot") == $slot)
				$result[] = $node;
	} else
	{
		$slot = ($apos ? '"' : "'") . $slot . ($apos ? '"' : "'");
		$xattr = coren::own_ns_prefix . ':' . 'slot';
		$xexpr = "//*[@{$xattr}={$slot}]";
		$nodes = self::$xml_xpath->query($xexpr);
		foreach ($nodes as $node)
			$result[] = $node;
	}
	return $result;
}
#
####################################################################################################
###################################  X S L T    D O C U M E N T  ###################################//xslt routines???
####################################################################################################
#
protected static $xslt_document	= null;
protected static $xslt_element	= null;
protected static $xslt_format	= null;
#
####################################################################################################
#
public static function xslt ()
{
/*rts*/	self::$rts_count_of_coren[__FUNCTION__]++;
	return self::$xslt_document;
}
#
####################################################################################################
#
protected static function xslt_initialize ()
{
	self::$xslt_document = new DOMDocument(self::xslt_xml_version, self::xslt_xml_encoding);
	self::$xslt_element =
		self::$xslt_document->createElementNS(self::xslt_xml_ns_uri, self::xslt_xml_ns_prefix . ':' . 'stylesheet');
		self::$xslt_element -> setAttributeNS(self::xslt_xml_ns_uri, self::xslt_xml_ns_prefix . ':' . 'version'      , self::xslt_xsl_version);
		self::$xslt_element -> setAttributeNS(self::data_xml_ns_uri, self::data_xml_ns_prefix . ':' . 'version'      , self::version()    );
		self::$xslt_element -> setAttributeNS(self::data_xml_ns_uri, self::data_xml_ns_prefix . ':' . 'version_major', self::version_major);
		self::$xslt_element -> setAttributeNS(self::data_xml_ns_uri, self::data_xml_ns_prefix . ':' . 'version_minor', self::version_minor);
		self::$xslt_element -> setAttributeNS(self::data_xml_ns_uri, self::data_xml_ns_prefix . ':' . 'version_patch', self::version_patch);
	self::$xslt_document->appendChild(self::$xslt_element);
}
#
####################################################################################################
#
public static function xslt_format ($format = null)
{
/*rts*/	self::$rts_count_of_coren[__FUNCTION__]++;

	if (is_null  ($format)) return self::$xslt_format;//NB: no else
/*err*/	if (is_scalar($format)) {} else
/*err*/		throw new coren_exception_bad_xslt_format("Can not set xsl target format because its name is of bad type (must be scalar).");
/*err*/	if ($format == '')
/*err*/		throw new coren_exception_bad_xslt_format("Can not set xsl target format because its name is empty string (must be non-empty).");

	$result = self::$xslt_format;
	self::$xslt_format = $format;
	return $result;
}
#
####################################################################################################
#############################  R E S P O N S E    G E N E R A T I O N  #############################
####################################################################################################
#
protected static $response_at_top	= null;
protected static $response_prefix	= null;
protected static $response_suffix	= null;
protected static $response_document	= null;
#
protected static $shutup = false;
#
####################################################################################################
#
public static function shutup ($force = null)
{
/*rts*/	self::$rts_count_of_coren[__FUNCTION__]++;
//???	self::flush($force);
	self::$shutup = true;
}
#
####################################################################################################
#
protected static function response_transform ()
{
	//!!!!!!!!!!!!!!
//???	self::$data_document->xinclude(); <-- this should never be xniclude()d. recipient must to this.
//???	self::$xslt_document->xinclude(); <-- this can be xinclude()d, probably. 
	self::$data_document->normalize();
	self::$xslt_document->normalize();

	if (self::$xslt_format == '')
	{
		self::$response_document = self::$data_document;
	} else
	{
		$processor = new XSLTProcessor();
		$processor->importStylesheet(self::$xslt_document);
		self::conf_assign_parameters($processor);
		self::$response_document = $processor->transformToDoc(self::$data_document);
		if (self::$response_document === false)
			throw new coren_exception/*!!!*/("Can not apply XSL-transforms.");
	}
}
#
####################################################################################################
#
protected static function response_generate ()
{
	$content = self::$response_document->saveXML();
	if ($content === false)
		throw new coren_exception/*!!!*/("Can not generate resulting XML from its original DOM.");

	$content = self::$response_prefix . $content . self::$response_suffix;

	if (self::$response_at_top)
	{
		//NB: we do not want to destroy current output buffer and to trigger its ob-handler,
		//NB: so we should not use ob_end_clean() or ob_get_clean() here; but ob_clean() is ok.
		$buffer = ob_get_contents();
		if ($buffer !== false) ob_clean();
		print $content;
		if ($buffer !== false) print $buffer;
	} else
	{
		print $content;
	}
}
#
####################################################################################################
###############################  M A I N   &   S T A G E    W O R K  ###############################
####################################################################################################
#
private static function stage_load_configuration	() { self::conf_initialize    (); }
private static function stage_configure_self		() { self::conf_apply_values  (); }
private static function stage_prefetch_modules		() { self::conf_fetch_modules (); }
private static function stage_prefetch_handlers		() { self::conf_fetch_handlers(); }
#
private static function stage_create_containers		() { self::data_initialize(); self::xslt_initialize(); }
#
private static function stage_apply_xslt_to_data	() { self::response_transform(); }
private static function stage_generate_response		() { self::response_generate (); }
#
private static function stage_event_init		() { self::event(self::event_for_stage_init   ); }
private static function stage_event_prework		() { self::event(self::event_for_stage_prework); }
private static function stage_event_content		() { self::event(self::event_for_stage_content); }
private static function stage_event_epiwork		() { self::event(self::event_for_stage_epiwork); }
private static function stage_event_free		() { self::event(self::event_for_stage_free   ); }
#
####################################################################################################
#
private static function work_stage ($stagename)
{
/*rts*/	$stamp = microtime(true);
/*rts*/	self::$rts_time_for_stage[$stagename] = 0.0;
	self::$stage = $stagename;
	try
	{
		$method = "stage_{$stagename}";
		self::$method();
	}
	catch (coren_exception_abort_stage $exception) { $exception = null; }
	catch (exception $exception) {}
	self::$stage = null;
/*rts*/	self::$rts_time_for_stage[$stagename] += microtime(true) - $stamp;
	if (isset($exception)) throw $exception;
}
#
####################################################################################################
#
private static function work_fatal ($exception)
{
	try { self::event(self::event_for_fatal, compact('exception')); }
	catch (exception $dummy_exception) {}

	$header = "HTTP/1.0 503 Unhandled exception";
	$action = defined('COREN_FATAL_ACTION') && (COREN_FATAL_ACTION != '') ? COREN_FATAL_ACTION : 'rethrow';
	switch ($action)
	{
		case 'silent.503':
			header($header);
		case 'silent':
			/* do absolutely nothing here */
			break;

		case 'terse.503':
			header($header);
		case 'terse':
			printf("Unhandled exception.");
			break;

		case 'verbose.503':
			header($header);
		case 'verbose':
			printf("Unhandled exception of class '%s' at line %d of file '%s':\n%s\n",
				get_class($exception),
				$exception->getLine(),
				$exception->getFile(),
				$exception->getMessage());
			break;

		case 'script.503':
			header($header);
		case 'script':
			//todo: load some file, whose name is in some php constant.
			break;

		case 'rethrow.503':
			header($header);
		case 'rethrow':
			throw $exception;
			break;

		default:
			throw new coren_exception/*!!!*/("Fatal error occured, but its handler ('{$action}') is not known and can not be executed.");
	}
}
#
####################################################################################################
#
public static function _work_ ()
{
/*rts*/	self::rts_reset();
	try
	{
		self::work_stage('load_configuration');
		self::work_stage('configure_self'    );
		self::work_stage('prefetch_modules'  );
		self::work_stage('prefetch_handlers' );
		self::work_stage('create_containers' );
		self::work_stage('event_init'        );
		self::work_stage('event_prework'     );
		self::work_stage('event_content'     );
		self::work_stage('event_epiwork'     );
		self::work_stage('event_free'        );
		self::work_stage('apply_xslt_to_data');
		self::work_stage('generate_response' );
	}
	catch (coren_exception_abort_work $exception)
	{
		/* do nothing here: just silently exit from coren */
	}
	catch (exception $exception)
	{
		self::work_fatal($exception);
	}
/*rts*/	self::rts_print();
}
#
####################################################################################################
##############################  E N D    O F    M A I N    C L A S S  ##############################
####################################################################################################
#
}
#
####################################################################################################
####################################################################################################
####################################################################################################
#
//NB: this is not an exit() from the script, but a valid return value for include/require statement.
return defined('COREN_DECLARE_ONLY') && COREN_DECLARE_ONLY ? null : coren::_work_();
#
####################################################################################################
####################################################################################################
####################################################################################################
?>