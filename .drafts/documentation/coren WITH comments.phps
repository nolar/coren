<?php defined('CORENINPAGE') or die('Hack!');
####################################################################################################
#################################  U T I L I T Y    C L A S S E S  #################################
####################################################################################################
#
# Classes declared here are usually used in the whole site, build with coren:
# * coren_object	- indirect base class for both modules' implementers and database brokers;
# * coren_module	- base class for all modules' implementers;
# * coren_broker	- base class for all modules' database brokers;
# * coren_tool		- base class for all static tools;
# * coren_exception	- base class for all exception, thrown from coren main class.
#
# These classes define only very basic functionality in them (like constructor in coren_object),
# or no functionality at all. All these classes are declared as abstract to prevent their direct
# use. Instead, only children of these abstract classes would be used. And all children classes must
# obey these inheritance conventions:
# * if the class defines basic functionality, and will be used as a main implementer of a module,
#   then it must inherit from coren_module;
# * if the class defines database access methods (read and/or write), and will be used only to
#   access data from main implementer (via coren::db()), then it must inherit from coren_broker;
# * if the class defines only static methods, and will be used as dinamycally loaded tool in other
#   classes (modules, database brokers or tools), then it must inherit from coren_tool.
#
# Note, that these are two different hierarchies: coren_object and coren_tool. The reason for such
# separation is that tools are almost always static, and they are almost never instantiated. This
# differs from coren's objects, which are useful only when instantiated, and only when they obey
# some technique of coren's modular approach. One of this technic is that constructor of coren's
# module (either main implementer or database broker) must accept single parameter, which is hash
# of config values for this module. Other is that all methods, callable from coren, must accept
# single parameter, which is a hash of data values. And so on. So, coren_object defines this basic
# functionalities (stubs) for module classes, while coren_tool is for static classes of tools.
#
# Exceptions are a special case, and form third hierarchy. Unlike other utility classes, this
# hierarchy of exceptions is fully implemented, starting from its base class coren_exception, and
# along all hierarchy's branche and leaf classes. No one of these classes is abstract; so anyone
# can be thrown anywhere by itself, without its inherited classes (even base class coren_exception).
#
# But note, that coren main class throws only children of coren_exception, and not coren_exception
# itself. Note also, that site's modules, database brokers, tools are not forced to use only
# coren_exception or any of its derivatives, and can throw absolutely any exception they want (that
# is including PHP built-in "exception" class, coren_exception, or any other class from their own
# hierarchy of exceptions -- anything suitable to throw operator).
#
# Class coren_exceptions differs from PHP "exception" class in that coren_exception knows in what
# stage of work and in what module and method of coren's call stack it was thrown. Coren's stack
# is slightly different from PHP's one, so this can become useful information. But PHP call stack
# is also available, since coren_exception is child of PHP "exception" class.
#
# There are four basic branches in hierarchy of coren's exceptions, all marked with their infix:
# * _abort_	- exceptions to silently stop some level of work without error messages;
# * _no_	- exceptions to signal that some required thing is missed and work can not be done;
# * _bad_	- exceptions to signal about bad type or structure of parameter to coren's method;
# * _config_	- exceptions to signal about bad configuration of coren (mean initial config files).
# Suffix of exception's class name specifies what is actually missed, bad or misconfigured.
#
# Exceptions are not unique for a situation, so one single exception can be thrown from different
# places and in different situations; exact description of what is wrong and why it is wrong can be
# retrieved from exception's message.
#
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
class coren_exception_bad_xsl_format			extends coren_exception_bad_		{}
class coren_exception_bad_xsl_parameter			extends coren_exception_bad_		{}
class coren_exception_bad_xsl_value			extends coren_exception_bad_		{}
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
# Public constants are used mainly in coren main class itself, but sometime they can be used in
# coren's modules or other PHP code. Values of these constants can change across different versions
# of coren, but can not change not on a per-site or a per-request basis (they are constants after
# all, not a variables).
#
# Short description of contants:
#
# version_*		- major, minor and patch version of coren, as numbers;
#
# xml_version		- version of resulting xml document;
# xml_encoding		- encoding of resulting xml document;
# xsl_version		- version of xsl stylesheet;
# xsl_ns_prefix		- tag prefix for xsl stylesheet;
# xsl_ns_uri		- namespace for xsl stylesheet;
# own_ns_prefix		- tag prefix for coren's elements and attributes;
# own_ns_uri		- namespace for coren's elements and attributes;
#
# event_for_stage_*	- name of event, which causes execution of specific stage of work;
# event_for_fatal	- name of event for notifying about fatal dying (by unhandled exception);
#
# It should be noted, that xml_version and xml_encoding will be used in all coren's created DOM
# documents, and in fact only affect < ? xml ... ? > process instruction when printing document
# (or converting it to string). As PHP manual says, DOM always uses UTF-8 internally. But it is
# highly recommended for modules and other code to create DOM documents with encoding got from this
# constant, since somewhen it could change to UCS2 or something like that. Anyway, no matter what
# encoding is used in DOM documents, output encoding of resulting document can always be set in
# coren's configuration files - it will be used instead of this constant default.
#
# Like xml_version and xml_encoding, xsl_version is used when building XSL stylesheet to specify
# its version, and can affect how XSLT processor applies this stylesheet to XML document.
#
# Where elements and attributes can be produced by coren, they all must have a namespace, and must
# be prefixed (though technically they can be namespaced without prefix, it is a coren authors'
# choice to prefix all namespaced elements and attributes). For now, there are only two origins of
# namespaced elements and attributes: own coren's elements/attributes, and XSL elements/attributes.
# Modules also must create namespaced element for these kinds of data (mainly XSL), and it is very
# prefered to use this constants for proper values of namespace prefixes and/or URIs (at least
# because this prefixes are associated with proper URIs in top-level elements of both XMLs and XSLs,
# produced by coren).
#
# Other set of constants is names of events, that coren triggers itself. These are events for stages
# (most out-of-coren stages are triggered as events, and modules have to catch them by these names
# to do some work), and single event for notifying about fatal error of script (in fact - unhandled
# exception), which prevents futher execution. Modules can use this "fatal" event to free some
# critical resources or disconnect database connections, just like they usually do in stage "free".
# This is to discretion of modules to decide, should they react to fatal error event, or should not.
#
####################################################################################################
#
const version_major		= 0;
const version_minor		= 0;
const version_patch		= 0;
#
####################################################################################################
#
const xml_version		= '1.0';
const xml_encoding		= 'utf-8';
const xsl_version		= '1.0';
const xsl_ns_prefix		= 'xsl';
const xsl_ns_uri		= 'http://www.w3.org/1999/XSL/Transform';
const own_ns_prefix		= 'coren';
const own_ns_uri		= 'http://coren.numeri.net/namespaces/coren/';
#
####################################################################################################
#
const event_for_stage_init	= 'coren!stage(init)'	;
const event_for_stage_prework	= 'coren!stage(prework)';
const event_for_stage_content	= 'coren!stage(content)';
const event_for_stage_epiwork	= 'coren!stage(epiwork)';
const event_for_stage_free	= 'coren!stage(free)'	;
const event_for_fatal		= 'coren:fatal'		;
#
####################################################################################################
#############################  R U N - T I M E    S T A T I S T I C S  #############################
####################################################################################################
#
# Every string in coren's source code, which relates to gathering and handling run-time statistics
# (RTS), is (and must be) marked with special comment (see code below for examples). When script is
# compiled with statistic gathering being disabled, compiler will omit (simply cut out) all lines
# with this special mark to slightly improve performance by not doing unneccesary code. This is
# useful for final releases of sites/products, where no debug information must be dumped to user.
#
# Here is a short description of RTS fields and methods of coren main class:
#
# $rts_load_stamp	- Microtime when previous coren::__load__() has started.
# $rts_call_stamp	- Microtime when previous coren::__call__() has started.
# $rts_startup_stamp	- Microtime when coren work has started (actually, when RTS was reset).
# $rts_total_runtime	- Number of second while coren was working from start till dumping of RTS.
#
# $rts_count_of_loads	- Number of files actually loaded by coren::depend().
# $rts_count_of_coren	- Number of calls to specific API method of coren main class.
# $rts_count_of_module	- Number of times any method of specific module was called.
# $rts_count_of_method	- Number of times specific method of specific module was called.
#
# $rts_time_for_loads	- Total time spent for loading & parsing files by coren::depend().
# $rts_time_for_stage	- Time spent for specific stage of coren's work.
# $rts_time_for_module	- Time spent in any method of specific module.
# $rts_time_for_method	- Time spent in specific method of specific module.
#
# __rts__reset__()	- Resets some fields for gathering RTS.
# __rts__print__()	- Prints gathered RTS to script output (usually to user/visitor).
#
# Note that modules call other modules, methods call other methods intensively via coren API calls.
# But $rts_time_for_module and $rts_time_for_method are the times spent in specific modules and
# methods only; they do not include time spent in nested calls of other modules and methods.
# For example, if we call method A, which in turn calls method B, and if we measure time spent in
# such a call to A, we will get time spent both in method A and method B. This is not what we want.
# Instead, we measure total time spent in call to A, and also measure time spent in call to B, and
# then substract time of B from measured time of A. So we get "clean" time spent in method A; this
# is what we want, and this time is accumulated in $rts_time_for_module/$rts_time_for_method fields.
#
# The same is true for loading of files via coren API calls: only time spent for loading and parsing
# of files is accurately measured, excluding unnecessary overflows caused by recursive dependencies.
#
# Coren API calls are calls to methods of coren main class; but only to those methods, whose names
# do not begin with underscore. Actually, these are almost all public static methods. Just because
# methods, whose names start with underscore are in fact either private or protected, and must not
# be exposed.
#
# When resetting RTS counter, only those counters are initialized in coren::__rts__reset__(), which
# can not be initialized by simple assignment in field declaration. Usually these are dynamic or
# calculated arrays or some functions/formulas. For now it is a list of coren API methods (those one
# whose name do not begin with underscore), and time-stamp of script startup.
#
# When printing RTS, all modules are printed in alphabetical order, with their times and count of
# calls, even if they never have been called or instantiated. Unlike modules, only those methods
# are printed, that were called at least once; and methods are printed in the order in which they
# were first called.
#
# Be warned when printing RTS, that user/visitor might not accept such an additional datas, and may
# even fail validation of document or even raise an error. So printing is done only and only when
# it is configured to do so. And this must be enabled only when you test script timings; do not
# enable printing of RTS in release sites/products.
#
####################################################################################################
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
# Returns nothing.
#
/*rts*/protected static function __rts__reset__ ()
/*rts*/{
/*rts*/	self::$rts_startup_stamp = microtime(true);
/*rts*/
/*rts*/	foreach (get_class_methods(get_class()) as $method)
/*rts*/		if ($method{0} != '_')
/*rts*/			self::$rts_count_of_coren[$method] = 0;
/*rts*/}
#
####################################################################################################
#
# Returns nothing.
#
/*rts*/protected static function __rts__print__ ()
/*rts*/{
/*rts*/	self::$rts_total_runtime = microtime(true) - self::$rts_startup_stamp;
/*rts*/	if (!self::$rts_enable) return;
/*rts*/	printf(self::$rts_prefix);
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
/*rts*/	printf("\n");
/*rts*/
/*rts*/	printf("Per stages:\n");
/*rts*/	$at = 0;
/*rts*/	$maxlength = (integer) max(array_merge(array(0), array_map('strlen', self::$stages))) + 2;
/*rts*/	foreach (self::$stages as $stage) if ($stage != '')
/*rts*/	{
/*rts*/		$s = "'" . $stage . "'";
/*rts*/		$t = self::$rts_time_for_stage[$stage];
/*rts*/		$at += $t;
/*rts*/		printf("Time in stage %-{$maxlength}s: %f sec (%2.0f%%).\n",
/*rts*/			$s, $t, 100.0 * $t / self::$rts_total_runtime);
/*rts*/	}
/*rts*/	printf("Time in stages%-{$maxlength}s: %f sec (%2.0f%%).\n",
/*rts*/		'', $at, 100.0 * $at / self::$rts_total_runtime);
/*rts*/	printf("\n");
/*rts*/
/*rts*/	printf("Per modules:\n");
/*rts*/	$at = 0; $ac = 0;
/*rts*/	$maxlength = (integer) max(array_merge(array(0), array_map('strlen', self::$modules))) + 2;
/*rts*/	foreach (self::$modules as $module)
/*rts*/	{
/*rts*/		$s = "'" . $module . "'";
/*rts*/		$t = self::$rts_time_for_module[$module];
/*rts*/		$c = self::$rts_count_of_module[$module];
/*rts*/		$at += $t; $ac += $c;
/*rts*/		printf("Time in module %-{$maxlength}s: %f sec (%2.0f%%); called %3d times.\n",
/*rts*/			$s, $t, 100.0 * $t / self::$rts_total_runtime, $c);
/*rts*/	}
/*rts*/	printf("Time in modules%-{$maxlength}s: %f sec (%2.0f%%); called %3d times.\n",
/*rts*/		'', $at, 100.0 * $at / self::$rts_total_runtime, $ac);
/*rts*/	printf("\n");
/*rts*/
/*rts*/	printf("Per methods:\n");
/*rts*/	$view = array(); $at = 0; $ac = 0; $maxlength = 0;
/*rts*/	foreach (self::$rts_time_for_method          as $module => $unused_variable_1)
/*rts*/	foreach (self::$rts_time_for_method[$module] as $method => $unused_variable_2)
/*rts*/	{
/*rts*/		$s = "'" . $module . "'" . "::" . $method;
/*rts*/		$t = self::$rts_time_for_method[$module][$method];
/*rts*/		$c = self::$rts_count_of_method[$module][$method];
/*rts*/		$view[] = array($s, $t, $c);
/*rts*/		$at += $t; $ac += $c; $maxlength = max($maxlength, strlen($s));
/*rts*/	}
/*rts*/	foreach ($view as $line)
/*rts*/	{
/*rts*/		list($s, $t, $c) = $line;
/*rts*/		printf("Time in method %-{$maxlength}s: %f sec (%2.0f%%); called %3d times.\n",
/*rts*/			$s, $t, 100.0 * $t / self::$rts_total_runtime, $c);
/*rts*/	}
/*rts*/	printf("Time in methods%-{$maxlength}s: %f sec (%2.0f%%); called %3d times.\n",
/*rts*/		'', $at, 100.0 * $at / self::$rts_total_runtime, $ac);
/*rts*/	printf("\n");
/*rts*/
/*rts*/	printf("Per coren api calls:\n");
/*rts*/	$ac = 0;
/*rts*/	$maxlength = (integer) max(array_merge(array(0), array_map('strlen', array_keys(self::$rts_count_of_coren)))) + 2;
/*rts*/	foreach (self::$rts_count_of_coren as $method => $count)
/*rts*/	{
/*rts*/		$s = "'" . $method . "'";
/*rts*/		$c = self::$rts_count_of_coren[$method];
/*rts*/		$ac += $c;
/*rts*/		printf("Coren method %-{$maxlength}s called %3d times.\n",
/*rts*/			$s, $c);
/*rts*/	}
/*rts*/	printf("Coren methods%-{$maxlength}s called %3d times.\n",
/*rts*/		'', $ac);
/*rts*/	printf("\n");
/*rts*/
/*rts*/	printf(self::$rts_suffix);
/*rts*/}
#
####################################################################################################
####################################  F I L E    L O A D I N G  ####################################
####################################################################################################
#
# Here is a set of fields and methods for loading specific file in isolated context. Isolation of
# context mean that no variable names are implicitly predefined, nor from caller context, nor from
# method arguments, and no variable from loaded file will be accidentally exported to caller or to
# global context (unless it is specially set via PHP superglobal variables, such as $GLOBALS).
#
# But isolation of context does not mean that context will be absolutely clean. Caller can pass
# an array of variables, which will be extracted into isolated context. Variables will be extracted
# by reference, so this is a way to get some values out of method; of course, called file must be
# coded so that result will be placed into proper variables. If some variable from array can not be
# extracted because of invalid/numeric key, it will prefixed with single underscore (for eaxmaple,
# numeric keys 0, 1, 2 will become variables $_0, $_1, $_2, ans so on).
#
# Isolation is achieved by executing include/require statement in single method, which even have
# no parameters (otherwise they will make context not fully isolated). Path to the file, which will
# be loaded, is stored in special static field of coren main class. These methods and this field
# are all private to class, so even ansectors can not use them, and even in this class it is highly
# not recommended to use them. Instead, use wrapper method.
#
# Wrapper method is what everyone should call to make an isolated load. It receives single
# argument with path to file, which should be loaded, then stores it in special static field
# mentioned above, and then executes special static method for actual isolated loading.
#
# Wrapper method also collects RTS about all isolated loadings: time used for reading of file and
# parsing of code (in summ, since they can not be measured individually), and number of load
# attempts, whether they were successful or were not. If some exception is thrown while loading
# a file, it does not break collecting of RTS and does not let it to loose some data. Everything
# is calculated strictly and accurately.
#
# The only essential difference from direct include/require statements is that after this method
# returns, PHP will not be able to trace error message (via $php_errormsg), even if tracing is
# enabled. This is because right after include/require statement there are a lot of other operations
# and statements executed; more on that, $php_errormsg is automatically set in the scope where error
# happen, and our loading is isolated (i.e. context is no exported, and $php_errormsg is part of
# context). Therefore, it is assumed, that caller does not depend on error tracing for isolated
# loading, but checks errors in some other way.
#
# While methods described above are some kind of "low-level" file including, there is a "high-level"
# routine to not just load a file, but to form its name, find it and only then load it. This is
# called a dependency, and the file it loads is built from the name of required class, suffixed by
# coren's file extension (determined in "prepare" stage). If coren was configured to use searchdirs,
# then file will be searched first; if it will not be found, then nothing will be loaded at all,
# just as if file did not exist. If coren was not configured to use searchdirs, then this method
# will rely on PHP's option to search files in its own include path.
#
# Since dependency is not just a file, but a name of class to load (usually module implementer, or
# database broker, or tool), dependency loading will cache successes and even failures to load a
# dependency. So latter attempts to load same dependency will be as fast, as searching for cached
# result. This is done to prevent unnecessary file re-searches, which are very resource intensive.
#
# Dependency is considered sucessfuly loaded if before(!) or after loading a file in either way
# (internal or PHP search), there is the class with requested name. Note that if class initially
# exists, searching and loading is not performed, but result is returned immediately.
#
# For security reasons, all methods of loading files must be used only with hard-coded names (of
# file or of classes). Passed arguments are not checked to be a valid language identifiers or safe
# filenames; but this arguments are used as part of path to file. So it is somehow possible to
# inject specially constructed paths, which lead to wrong files. So, saying it again in other words,
# never call this method with dynamically constructed name of class (i.e. received from users by
# any possible way: http request data, database, mail and so on). Never!
#
####################################################################################################
#
private static $depend_cache = array();
#
private static         $__load__context__ ;
private static         $__load__filepath__;
private static function __load__include__ () { extract(self::$__load__context__, EXTR_PREFIX_INVALID | EXTR_REFS, ''); return include(self::$__load__filepath__); }
private static function __load__require__ () { extract(self::$__load__context__, EXTR_PREFIX_INVALID | EXTR_REFS, ''); return require(self::$__load__filepath__); }
#
####################################################################################################
#
# Returns exactly what include/require statement will return, whatever it is.
#
protected static function __load__ ($filepath, $context)
{
/*rts*/	$rts_prevtimer = microtime(true) - self::$rts_load_stamp;
/*rts*/	self::$rts_load_stamp = microtime(true);
/*rts*/	try
/*rts*/	{
		self::$__load__context__  = is_array($context) ? $context : array();
		self::$__load__filepath__ = $filepath;
		$result = self::__load__include__();
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
# Returns exactly what include/require statement will return, whatever it is.
#
public static function load ($filename, $context = null)
{
/*rts*/	self::$rts_count_of_coren['load']++;

/*err*/	if (is_scalar($filename)) $filename = trim($filename); else
/*err*/		throw new coren_exception_bad_filename("Can not load a file because its name is of bad type (must be scalar).");
/*err*/	if ($filename == '')
/*err*/		throw new coren_exception_bad_filename("Can not load a file because its name is empty string (must be non-empty).");

/*err*/	if (is_null ($context)) {} else
/*err*/	if (is_array($context)) {} else
/*err*/		throw new coren_exception_bad_context("Can not load a file because context is of bad type (must be null or array).");

	return self::__load__($filename, $context);
}
#
####################################################################################################
#
# Returns TRUE if requested class either was loaded, or did exist before the call.
# Returns FALSE if even after attempt to load the file, requested class still does not exist.
#
public static function depend ($dependency)
{
/*rts*/	self::$rts_count_of_coren['depend']++;

/*err*/	if (is_scalar($dependency)) $dependency = trim($dependency); else
/*err*/		throw new coren_exception_bad_dependency("Can not check or load a dependency because its name is of bad type (must be scalar).");
/*err*/	if ($dependency == '')
/*err*/		throw new coren_exception_bad_dependency("Can not check or load a dependency because its name is empty string (must be non-empty).");

	if (isset(self::$depend_cache[$dependency]))
		return self::$depend_cache[$dependency];

	$filename = $dependency . EXTENSION;
	if (empty(self::$dependency_search_dirs))
	{
		self::__load__($filename, null);
	} else
	{
		$filepath = false;
		foreach (self::$dependency_search_dirs as $dir)
			if (file_exists($filepath = self::$dependency_search_root . $dir . $filename))
				{ self::__load__($filepath, null); break; }
	}

	return self::$depend_cache[$dependency] = class_exists($dependency);
}
#
####################################################################################################
###############################  M O D U L E    M A N A G E M E N T  ###############################
####################################################################################################
#
# As coren sites are modular, i.e. built with set of modules, here is a way to manage this modular
# structure. Control does not mean full control and ability to change structure of final product
# on-the-fly, but only an ability to add new modules and their configs, and to create new dynamic
# modules at run-time. Almost all methods here are internal; only create_module() is public.
#
# What is significant in this section, is set of fields with declaration of modular structure:
#
# $modules	- module names; used only for checking if module is already defined;
# $implementers - implementer classes for modules (not files, but classes: without extensions);
# $databases	- references (by name) to modules for handling modules' database connection;
# $configs	- two-level array of config values.
#
# All these fields are arrays, with keys being module names; in $configs values are also an arrays
# with second-level keys being names of configs. When module is added with __add_module__(), all
# these fields are modified to contain added module (field $configs contains empty array in value).
# When config is added with __add_config__(), only field $configs is modified to contain that config
# value.
#
# After module was added, it can not be nor removed, nor changed (overrided). Same is true for
# config values. So these fields are some kind of "add'n'lock" lists. If someone will try to add
# module or config with same name, it will not be added, and FALSE will be returned as a result
# (note that no exception will be thrown -- this is not a fatal situation here; though caller of
# __add_*__() method can throw an exception if it think that inability to add a module/config is
# fatal for him).
#
# Note, that if module is added with empty database (i.e. name of module, which handles database
# connection for that module), then default database is automatically substituted here, at the stage
# of adding, but not at the stage of calling such a module.
#
# Also note, that only those config values are added, which are not empty. Configs with empty or
# null values are silently ignored (exception is not thrown, but FALSE is returned as a result).
#
# If config is added for still unexisting module, it is remembered, and TRUE is returned from adding
# method. When module with that name will be added, it will not override accumulated values, but
# will leave them untouched. So there is no difference what to add first: modules or configs. Though
# this is only actual for coren stage "fetch" and for nothing more.
#
# Method create_module() is intended to dynamically create modules on-the-fly. It is assumed that
# for caller there is no difference with what name module will be created, so this name is very
# dynamic and can change from request to request (too many condition can affect what exact name
# the created module will have). Technically, name of created module is constructed from configured
# template for such a names: it is a printf-like string, where first %-token will be replaced with
# sequentally incremented number. For example, first module will got value "1", second - "2", and
# so on. If for some reason this configured template does not contain %-token (determined by
# violating an assertion that formatted string must differ from template), then module is not
# created, and NULL is returned in result.
#
# Creation of modules is protected against name collisions. So, for example, if you have configured
# template name "M%s", and have manually added modules "M1", "M3", "M4" into database, and then
# tried to call create_module() for first time, it will detect collision and will skip index "1"
# for occupied module "M1", and will create module "M2" instead; of course, "M2" will be returned
# as result of method. When called for second time, it will create and return "M5", because names
# "M3" and "M4" are occupied.
#
####################################################################################################
#
protected static $modules      = array();
protected static $implementers = array();
protected static $databases    = array();
protected static $configs      = array();
#
private static $create_module_index = 1;
#
####################################################################################################
#
# Returns TRUE if module was successfuly added.
# Returns FALSE if module was not added for some reason.
#
protected static function __add_module__ ($module, $implementer, $database)
{
/*err*/	if (is_scalar($module)) $module = trim($module); else
/*err*/		throw new coren_exception_bad_module("Can not add module because its own name is of bad type (must be scalar).");
/*err*/	if ($module == '')
/*err*/		throw new coren_exception_bad_module("Can not add module because its own name is empty string (must be non-empty).");

/*err*/	if (is_scalar($implementer)) $implementer = trim($implementer); else
/*err*/		throw new coren_exception_bad_implementer("Can not add module because name of its implementer is of bad type (must be scalar).");
/*err*/	if ($implementer == '')
/*err*/		throw new coren_exception_bad_implementer("Can not add module because name of its implementer is empty string (must be non-empty).");

	if (is_null  ($database)) $database = self::$default_database;/*NB: no else */
/*err*/	if (is_scalar($database)) $database = trim($database); else
/*err*/		throw new coren_exception_bad_database("Can not add module because name of its database module is of bad type (must be scalar or null).");
/*err*/	if ($database == '')
/*err*/		throw new coren_exception_bad_database("Can not add module because name of its database module is empty string (must be non-empty).");

	if (isset(self::$modules[$module])) return false;

	if (!isset(self::$configs[$module]))
	self::$configs     [$module] = array();
	self::$modules     [$module] = $module;
	self::$implementers[$module] = $implementer;
	self::$databases   [$module] = $database;

/*rts*/	self::$rts_time_for_module[$module] = 0.0;
/*rts*/	self::$rts_count_of_module[$module] = 0;
/*rts*/	self::$rts_time_for_method[$module] = array();
/*rts*/	self::$rts_count_of_method[$module] = array();

	return true;
}
#
####################################################################################################
#
# Returns TRUE if config was successfuly added.
# Returns FALSE if config was not added for some reason.
#
protected static function __add_config__ ($module, $config, $value)
{
/*err*/	if (is_scalar($module)) $module = trim($module); else
/*err*/		throw new coren_exception_bad_module("Can not add config because name of its module is of bad type (must be scalar).");
/*err*/	if ($module == '')
/*err*/		throw new coren_exception_bad_module("Can not add config because name of its module is empty string (must be non-empty).");

/*err*/	if (is_scalar($config)) $config = trim($config); else
/*err*/		throw new coren_exception_bad_config("Can not add config because its own name is of bad type (must be scalar).");
/*err*/	if ($config == '')
/*err*/		throw new coren_exception_bad_config("Can not add config because its own name is empty string (must be non-empty).");

/*err*/	if (is_null  ($value)) $value = ''; else
/*err*/	if (is_scalar($value)) $value = trim($value); else
/*err*/		throw new coren_exception_bad_value("Can not add config because its value is of bad type (must be scalar or null).");

	if (isset(self::$configs[$module][$config])) return false;
	if ($value == '') return false;

	if (!isset(self::$configs[$module]))
	self::$configs[$module] = array();
	self::$configs[$module][$config] = $value;

	return true;
}
#
####################################################################################################
#
# Returns name of created module (string), if it was successfuly created.
# Returns NULL if it is not possible to generate module's name or to create a module.
#
public static function create_module ($implementer, $database, $configs)
{
/*rts*/	self::$rts_count_of_coren['create_module']++;

	$prev = self::$created_module_name;
	do
	{
		$module = sprintf(self::$created_module_name, self::$create_module_index++);
		if ($module == $prev) return null;//NB: this is to prevent non-formattable (not unique) module names.
		else $prev = $module;
	}
	while (self::__add_module__($module, $implementer, $database) === false);

	if (is_array($configs))
		foreach ($configs as $config => $value)
			self::__add_config__($module, $config, $value);

	return $module;
}
#
####################################################################################################
##############################  M O D U L E    O P E R A B I L I T Y  ##############################
####################################################################################################
#
# Modules call modules intensivily, coren itself also calls modules to do some work. So calling
# methods of modules is the most important thing in this class. To understand how calling works,
# you should first understand base architecture of coren modular structure.
#
# There are a lot of implementer classes, which can be dynamically loaded with coren::depend(). All
# this classes inherites from coren_module, which in turn inherits from coren_object. When someone
# calls for some method of specific module, this module is first instantiated (together with loading
# implementer class). After an instance is created, coren looks for a requested method in this
# instance, and if it is callable, calls it. What this method will return -- that is returned as
# the result of __call__(). This is how a call for module's method works.
#
# Other case is when module calls its own database broker to perform some operation on data. It does
# this via coren::db() method, which call for __call__() with appropriate $database parameter set.
# This parameter specifies name of module to use as a handler of database connection. Not only this
# method is remembered in coren call stack for futher use, but also provides suffix for a database
# broker class. After this suffix received, coren walk through whole hierarchy of main implementer
# classes, searching for class of database broker, whose name is constructed as name of main class
# concatenated with suffix, which we just got from database handler module. Once such a class is
# found for database broker, it is instantiated, and method of this instance is called as usually.
#
# Gathered database suffixes, created instances of both main modules and their database brokers, --
# all this is cached for later reuse. But error conditions (impossibility to construct an instance)
# are never remembered; every new call makes a new try to construct an instance. So you can try to
# instantiate it again a little later, when error reason will be possibly eliminated.
#
# If you will read code of __instance_of_broker__(), you will see, that cache of broker instances
# is two-level, where first-level key is name of target module itself, and second-level key is name
# of database handler module. This structure gives us an ability to turn some single module into
# different database handlers very dynamically and simultaneously. But for now, this ability is not
# utilized, and will not be utilized in nearest feature. Though somewhen in future it can and will
# be reutilized to support multi-database functionality of single module. So there is no reason to
# remove it now.
#
# Coren call stack is an array of records about what module, what method and with what database
# handler module it was called. When some module&method pair is requested, record about it is pushed
# into call stack. And only after this real PHP methods are called. It is important to know, that
# constructor is also called after pushing record to stack. So if some exception occurs while
# instantiating module, it occurs in context of that erroneous module&method, and not in context of
# caller of this module&method pair. Anyway, once method has returned, or an exception was thrown,
# information about that module&method pair is popped from call stack.
#
# So that is how coren stack differs from PHP stack: while PHP stack contains all PHP-level calls
# to every PHP-level class and method, coren-level stack contains only calls to modules&methods of
# the coren-level architecture. These two stack are not just alternative stacks, but two absolutely
# different stacks with absolutely different information in them.
#
# If al of the above is clear, then here is short description of fields and methods:
#
# $stack			- coren-level stack (described above);
# $instances_of_*		- cache of instances of modules (keys are names of modules);
# $class_suffixes		- cache of broker suffixes (keys are names of database modules);
#
# __instance_of_module__()	- method for retrieving (creating and caching) instance of module;
# __instance_of_broker__()	- method for retrieving (creating and caching) instance of broker;
# __call__()			- method for actual calling of method within module.
#
# As it is seen here, all fields and all methods in this section are internal, and not available
# to modules or any other external routines. This is because modules must instead call wrapper
# methods for what they actually want to do (coren::db(), coren::event(), ...).
#
####################################################################################################
#
protected static $stack = array();
protected static $instances_of_modules = array();
protected static $instances_of_brokers = array();
protected static $class_suffixes = array();
#
####################################################################################################
#
# Returns an instance of module's implementer class.
# Throws if it is impossible to create an instance.
#
protected static function __instance_of_module__ ($module)
{
	if (isset(self::$instances_of_modules[$module]))
		return self::$instances_of_modules[$module];

/*err*/	if (!isset(self::$modules[$module]))
/*err*/		throw new coren_exception_no_module_description("Can not instantiate module '{$module}' itself because this module does not exist.");

	$implementer = self::$implementers[$module];
	$depend_ok = self::depend($implementer);
/*err*/	if (!$depend_ok)
/*err*/		throw new coren_exception_no_module_implementer("Can not instantiate module '{$module}' itself because its implementer '{$implementer}' was not found.");

	//NB: Constructor's exceptions are handled in __call__(), not here.
	$instance = new $implementer(self::$configs[$module]);

/*err*/	//NB: This is impossible though, but we have to check.
/*err*/	if (is_null($instance))
/*err*/		throw new coren_exception_no_module_instance("Can not instantiate module '{$module}' itself because its implementer '{$implementer}' has constructed null instance somewhy.");

	self::$instances_of_modules[$module] = $instance;
	self::$instances_of_brokers[$module] = array();
	return $instance;
}
#
####################################################################################################
#
# Returns an instance of module's database broker class.
# Throws if it is impossible to create an instance.
#
protected static function __instance_of_broker__ ($module, $database, $instance_of_module)
{
	if (isset(self::$instances_of_brokers[$module][$database]))
		return self::$instances_of_brokers[$module][$database];

/*err*/	if (!isset(self::$modules[$module]))
/*err*/		throw new coren_exception_no_broker_description("Can not instantiate broker for module '{$module}' because this module does not exist.");

/*err*/	if (!isset(self::$modules[$database]))
/*err*/		throw new coren_exception_no_broker_database("Can not instantiate broker for module '{$module}' because this module refers to database module '{$database}', which in turn does not exist.");

	$class_suffix = isset(self::$class_suffixes[$database])
		? (self::$class_suffixes[$database])
		: (self::$class_suffixes[$database] = self::__call__(null, $database, 'suffix', null));

/*err*/	if (is_scalar($class_suffix)) $class_suffix = trim($class_suffix); else
/*err*/		throw new coren_exception_no_broker_suffix("Can not instantiate broker for module '{$module}' because database module '{$database}' has returned class suffix of wrong type.");
/*err*/	if ($class_suffix == '')
/*err*/		throw new coren_exception_no_broker_suffix("Can not instantiate broker for module '{$module}' because database module '{$database}' has returned empty class suffix.");

	$classes = class_parents($instance_of_module);
	$miclass = get_class($instance_of_module);
	if (!is_array($classes)) $classes = array();
	array_unshift($classes, $miclass);

	$implementer = null;
	foreach ($classes as $class)
		if (self::depend($class . '_' . $class_suffix))
		{
			$implementer = $class . '_' . $class_suffix;
			break;
		}

/*err*/	if (is_null($implementer))
/*err*/		throw new coren_exception_no_broker_implementer("Can not instantiate broker for module '{$module}' because its implementer '{$miclass}_{$class_suffix}' was not found, and no ancestor of '{$miclass}' suffixed by '{$class_suffix}' was found too.");

	//NB: Constructor's exceptions are handled in __call__(), not here.
	$instance = new $implementer(self::$configs[$module]);

/*err*/	//NB: This is impossible though, but we have to check.
/*err*/	if (is_null($instance))
/*err*/		throw new coren_exception_no_broker_instance("Can not instantiate broker for module '{$module}' because its implementer '{$implementer}' has constructed null instance somewhy.");

	self::$instances_of_brokers[$module][$database] = $instance;
	return $instance;
}
#
####################################################################################################
#
# Returns exactly what called method of called module had return.
# Throws exceptions if can not instantiate module or call method.
# Re-throws exceptions, which were thrown from somewhere within.
#
protected static function __call__ ($database, $module, $method, $data)
{
/*rts*/	$rts_prevtimer = microtime(true) - self::$rts_call_stamp;
/*rts*/	self::$rts_call_stamp = microtime(true);

	array_push(self::$stack, compact('database', 'module', 'method'));
	try
	{
		$instance = self::__instance_of_module__($module);
		if (!is_null($database))
		$instance = self::__instance_of_broker__($module, $database, $instance);

/*err*/		if (!is_callable(array($instance, $method)))
/*err*/			throw is_null($database)
/*err*/				? new coren_exception_no_module_method("Can not call unexistent method '{$method}' of module '{$module}'.")
/*err*/				: new coren_exception_no_broker_method("Can not call unexistent method '{$method}' of broker '{$module}'.");

		if (!is_array($data)) $data = is_null($data) ? array() : array('@'=>$data);
		$result = call_user_func(array($instance, $method), $data);
	}
	catch (exception $exception) {}
	array_pop(self::$stack);

/*rts*/	$rts_leave = microtime(true);
/*rts*/	if (isset(self::$modules[$module]))
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
# Calls a method of database broker for current module. Current module is determinded as the last
# one in coren's stack. If stack is empty, then coren's own database broker is used, because this
# is a call from coren main class itself (from some of its stages). This technique is used to fetch
# coren's structure (modules, configs, handlers, parameters) from coren's database handler module,
# whose fetching methods are in its database broker class rather than in itself. It is a special
# convention to prevent unneccessary loading of code for fetching for those database modules, who
# will only handle connection and will never fetch coren's structure.
#
####################################################################################################
#
# Returns exactly what called method of database broker will return, whatever it is.
#
public static function db ($method, $data = null)
{
/*rts*/	self::$rts_count_of_coren['db']++;

/*err*/	if (is_scalar($method)) $method = trim($method); else
/*err*/		throw new coren_exception_bad_method("Can not call database operation because its name is of bad type (must be scalar).");
/*err*/	if ($method == '')
/*err*/		throw new coren_exception_bad_method("Can not call database operation because its name is empty string (must be non-empty).");

	if (empty(self::$stack))
	{
		$module   = self::$coren_database;
		$database = self::$coren_database;
	} else
	if (is_null(self::$stack[count(self::$stack)-1]['database']))
	{
		$module   = self::$stack[count(self::$stack)-1]['module'];
		$database = self::$databases[$module];
	} else
	{
		$module   = self::$stack[count(self::$stack)-1]['database'];
		$database = null;
	}

	return self::__call__($database, $module, $method, $data);
}
#
####################################################################################################
##################################  E V E N T    H A N D L I N G  ##################################
####################################################################################################
#!!!!!!!!!!!!!!!!!
# Internal storage of event handlers, and also an indexes for quick selection of event handler.
# First field stores sequentally indexed list of records. Each record is an information about what
# module and method should be called upon receiving of some event; it has keys [module] & [method].
# Indexes are hashes, whose keys are some string criteria, and values are numerical indexes of
# corresponding event handlers (as they are in the first field).
#
# Whenever some event occurs, it selects all indexes for each criteria matched (i.e. implementer,
# module and method names, and identifier of an event), then intersects them, and that result of
# intersection is a full list of indexes of event handlers for current event. They are called
# sequentally one-by-one in the order, in which they were registered. Such an algorithm.
# 
protected static $handlers = array();
#
####################################################################################################
#
# Registers handler for events with specified criteria. First argument specifies method name of
# caller module, which will be called to handle event.
#
# But not for all events it will be passed to handler. Only those one, which comes from specific
# implementer class (either module or broker or engine or whatever), specific module, specific
# method, and have a specific identifier. If some criteria is NULL or empty string, then this
# criteria is ignored, and all events are passed to handler (of course, if they do match other
# criterias).
#
# For example, setting all criteria to NULL will make coren to pass every event to this handler;
# setting all criteria to non-empty string will match events of strict origin and type;
# setting method to NULL will match events from eny method from specific module of specific class
# and of specific identifier; and so on - any combination of nulls/non-nulls allowed.
#
# Returns nothing (always NULL).
#
protected static function __add_handler__ ($module, $method, $event, $map)
{
/*err*/	if (is_scalar($module)) $module = trim($module); else
/*err*/		throw new coren_exception_bad_module("Can not add handler because name of callback module is of bad type (must be scalar).");
/*err*/	if ($module == '')
/*err*/		throw new coren_exception_bad_module("Can not add handler because name of callback module is empty string (must be non-empty).");

/*err*/	if (is_scalar($method)) $method = trim($method); else
/*err*/		throw new coren_exception_bad_method("Can not add handler because name of callback method is of bad type (must be scalar).");
/*err*/	if ($method == '')
/*err*/		throw new coren_exception_bad_method("Can not add handler because name of callback method is empty string (must be non-empty).");

/*err*/	if (is_scalar($event)) $event = trim($event); else
/*err*/		throw new coren_exception_bad_event("Can not add handler because name of its event is of bad type (must be scalar).");
/*err*/	if ($event == '')
/*err*/		throw new coren_exception_bad_event("Can not add handler because name of its event is empty string (must be non-empty).");

	if (is_null($map))
	{
		$map = array();
	} else
	if (is_array($map))
	{
		$map_old = $map; $map = array();
		foreach ($map_old as $dstkey => $srckey)
		{
/*err*/			if (is_scalar($dstkey)) $dstkey = trim($dstkey); else
/*err*/			if (is_null  ($dstkey)) $dstkey = ''; else
/*err*/				throw new coren_exception_bad_dstkey("Can not add handler because dstkey in its map is of bad type (must be scalar or null).");

/*err*/			if (is_scalar($srckey)) $srckey = trim($srckey); else
/*err*/			if (is_null  ($srckey)) $srckey = ''; else
/*err*/				throw new coren_exception_bad_srckey("Can not add handler because srckey in its map is of bad type (must be scalar or null).");

			$map[$dstkey] = $srckey;
		}
	} else
	if (is_scalar($map))
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
/*err*/	} else
/*err*/ {
/*err*/		throw new coren_excetion_bad_map("Can not add handler because its map is of bad type (must be either null, or properly formatted string, or properly structured array).");
	}

	if (!isset(self::$handlers[$event])) self::$handlers[$event] = array();
	self::$handlers[$event][] = compact('module', 'method', 'map');
}
#
####################################################################################################
#
#...
#
public static function handler ($method, $event, $map = null)
{
/*rts*/	self::$rts_count_of_coren['handler']++;

	if (empty(self::$stack)) return;
	$module = self::$stack[count(self::$stack)-1]['module'];
	return self::__add_handler__($module, $method, $event, $map);
}
#
####################################################################################################
#
# Triggers an event in coren. This event will be passed to registered event handlers, for which
# it match their criteria (see coren::handler()). Module and method names and
# implementer of module are determined automatically. Identifier of an event must be specified
# by caller (those who triggered the event). Data argument will be passed to event handler and can
# contain anything you want (and what event handler assumes is there). Text argument is descriptive
# message of the event and have informational purpose only (for logging, for example).
#
# Returns nothing (always NULL).
#
public static function event ($event, $data = null)
{
/*rts*/	self::$rts_count_of_coren['event']++;

/*err*/	if (is_scalar($event)) $event = trim($event); else
/*err*/		throw new coren_exception_bad_event("Can not trigger event because its name is of bad type (must be scalar).");
/*err*/	if ($event == '')
/*err*/		throw new coren_exception_bad_event("Can not trigger event because its name os empty string (must be non-empty).");

	if (!isset(self::$handlers[$event]))
		return null;

	if (empty(self::$stack))
	{
		$module = '';
		$method = '';
		$implementer = '';
	} else
	{
		$module = self::$stack[count(self::$stack)-1]['module'];
		$method = self::$stack[count(self::$stack)-1]['method'];
		$implementer = self::$implementers[$module];
	}

	if (!is_array($data)) $data = is_null($data) ? array() : array('@'=>$data);
	$data[null] = compact('implementer', 'module', 'method', 'event');

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
			$result = self::__call__(null, $callback['module'], $callback['method'], $data);
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
# Internal storage of privileges. Keys are names of privileges (always strings), values are boolean flags
# indicating if privilege is allocated to current visitor or if isn't. I.e., if values is TRUE, then
# this privilege is allocated; if value is FALSE, then this privilege is just known to coren, but is not
# allocated to visitor.
#
# Initially, there is no any privilege predefined.
#
protected static $privileges = array();
#
####################################################################################################
#
# Informs coren about allocated privileges for the visitor. This privileges are used in checking what
# visitor can do and what can not do (see coren::have()).
#
# All privileges specified in argument are marked as TRUE in their allocation state. If some privilege was
# not in the list of known privileges before, it will be placed there as if coren::add_privileges_available()
# was called before.
#
# Returns nothing (always NULL).
#
public static function grant ($privileges)
{
/*rts*/	self::$rts_count_of_coren['grant']++;

/*err*/	if (!is_array($privileges))
/*err*/		throw new coren_exception_bad_privileges("Can not grant privileges because their list is of bad type (must be array).");

	foreach ($privileges as $privilege)
	{
		if (is_null  ($privilege)) continue;//NB: no else.
/*err*/		if (is_scalar($privilege)) $privilege = trim($privilege); else
/*err*/			throw new coren_exception_bad_privilege("Can not grant privilege because its name is of bad type (must be scalar).");
/*err*/		if ($privilege == '')
/*err*/			throw new coren_exception_bad_privilege("Can not grant privilege because its name is empty string (must be non-empty).");

		self::$privileges[$privilege] = true;//NB: any non-null value; only existence of keys is actually used.
	}
}
#
####################################################################################################
#
# Checks if some single privilege is allocated to current visitor, i.e. if this privilege was previously
# added by coren::grant(). privilege, which you check, must be string. If it is not
# string, it will be typecasted to string, and then checked. This is bad behavior though; who
# knows what typecast misteries will be added to language in future.
#
# Returns TRUE if privilege is allocated.
# Returns FALSE if privilege is not allocated.
#
public static function have ($privilege)
{
/*rts*/	self::$rts_count_of_coren['have']++;

	if (is_null  ($privilege)) return true;//NB: no else.
/*err*/	if (is_scalar($privilege)) $privilege = trim($privilege); else
/*err*/		throw new coren_exception_bad_privilege("Can not check for privilege because its name is of bad type (must be scalar).");
/*err*/	if ($privilege == '')
/*err*/		throw new coren_exception_bad_privilege("Can not check for privilege because its name is empty string (must be non-empty).");

	return (bool) isset(self::$privileges[$privilege]);
}
#
####################################################################################################
###############################  I N T E R N A L    S T A T E ( S )  ###############################
####################################################################################################
#
#...
#
#//??? рассылать как сообщение? а ля expire() & caches & redirector.
#//??? с другой стороны, это состояние использует само ядро.
protected static $shutup = false; // Boolean. True if real content is flushed, and nothing should be printed more.
#
####################################################################################################
#
public static function shutup ($force = null)
{
/*rts*/	self::$rts_count_of_coren['shutup']++;

	//!!! вероятно, надо очистить все запомненные контенты, и запретить их принимать вновь.
	//!!! или просто забить на них, а по флажку shutup'нутости не выводить ничего в конце.
//???	self::flush($force);
	self::$shutup = true;
}
#
####################################################################################################
#
public static function current_stage ()
{
/*rts*/	self::$rts_count_of_coren['current_stage']++;
	return self::$current_stage;
}
#
####################################################################################################
#
public static function current_module ()
{
/*rts*/	self::$rts_count_of_coren['current_module']++;
	return empty(self::$stack) ? null : self::$stack[count(self::$stack)-1]['module'];
}
#
####################################################################################################
#
public static function current_method ()
{
/*rts*/	self::$rts_count_of_coren['current_method']++;
	return empty(self::$stack) ? null : self::$stack[count(self::$stack)-1]['method'];
}
#
####################################################################################################
#
public static function version ()
{
/*rts*/	self::$rts_count_of_coren['version']++;
	return sprintf("%d.%d.%d", self::version_major, self::version_minor, self::version_patch);
}
#
####################################################################################################
#####################################  X M L    C O N T E N T  #####################################
####################################################################################################
#
#...
#
protected static $xml_document = null;
protected static $xml_xpath    = null;
#
####################################################################################################
#
public static function xml ()
{
/*rts*/	self::$rts_count_of_coren['xml']++;
	return self::$xml_document;
}
#
####################################################################################################
#
#
#
public static function name ($node, $name)
{
/*rts*/	self::$rts_count_of_coren['name']++;

/*err*/	if (is_null  ($name)) $name = ''; else
/*err*/	if (is_scalar($name)) $name = trim($name); else
/*err*/		throw new coren_exception_bad_xml_name("Can not mark xml node by name because this name is of bad type (must be scalar or null).");

/*err*/	if (!($node instanceof DOMNode))
/*err*/		throw new coren_exception_bad_xml_node("Can not mark xml node by name because this node is of bad type (must be child of DOMNode).");

	//!!! node может оказаться фрагментом. в таком случае пометить все дочерние elements. но событие кинуть только единожды.
	if ($name == '') $node->removeAttributeNS(self::own_ns_uri, 'name');
	else $node->setAttributeNS(self::own_ns_uri, self::own_ns_prefix . ':' . 'name', $name);
	return $node;
}
#
####################################################################################################
#
#
#
public static function slot ($node, $slot)
{
/*rts*/	self::$rts_count_of_coren['slot']++;

/*err*/	if (is_null  ($slot)) $slot = ''; else
/*err*/	if (is_scalar($slot)) $slot = trim($slot); else
/*err*/		throw new coren_exception_bad_xml_slot("Can not mark xml node by slot because this slot is of bad type (must be scalar or null).");

/*err*/	if (!($node instanceof DOMNode))
/*err*/		throw new coren_exception_bad_xml_node("Can not mark xml node by slot because this node is of bad type (must be child of DOMNode).");

	//!!! node может оказаться фрагментом. в таком случае пометить все дочерние elements. но событие кинуть только единожды.
	if ($slot == '') $node->removeAttributeNS(self::own_ns_uri, 'slot');
	else $node->setAttributeNS(self::own_ns_uri, self::own_ns_prefix . ':' . 'slot', $slot);
	return $node;
}
#
####################################################################################################
#
public static function names ()
{
/*rts*/	self::$rts_count_of_coren['names']++;

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
public static function slots ()
{
/*rts*/	self::$rts_count_of_coren['slots']++;

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
public static function name_used ($name)
{
/*rts*/	self::$rts_count_of_coren['name_used']++;

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
public static function slot_used ($slot)
{
/*rts*/	self::$rts_count_of_coren['slot_used']++;

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
public static function name_nodes ($name)
{
/*rts*/	self::$rts_count_of_coren['name_nodes']++;

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
public static function slot_nodes ($slot)
{
/*rts*/	self::$rts_count_of_coren['slot_nodes']++;

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
#####################################  X S L    C O N T E N T  #####################################
####################################################################################################
#
#...
#
protected static $xsl_document = null;
protected static $xsl_xpath    = null;
#
protected static $xsl_format     = null;
protected static $xsl_parameters = array();
#
####################################################################################################
#
public static function xsl ()
{
/*rts*/	self::$rts_count_of_coren['xsl']++;
	return self::$xsl_document;
}
#
####################################################################################################
#
public static function get_xsl_format ()
{
/*rts*/	self::$rts_count_of_coren['get_xsl_format']++;

	return self::$xsl_format;
}
#
####################################################################################################
#
#...
#
public static function set_xsl_format ($format)
{
/*rts*/	self::$rts_count_of_coren['set_xsl_format']++;

/*err*/	if (is_scalar($format)) $format = trim($format); else
/*err*/		throw new coren_exception_bad_xsl_format("Can not set xsl target format because its name is of bad type (must be scalar).");
/*err*/	if ($format == '')
/*err*/		throw new coren_exception_bad_xsl_format("Can not set xsl target format because its name is empty string (must be non-empty).");

	self::$xsl_format = $format;
}
#
####################################################################################################
#
#...
#
public static function get_xsl_parameter ($parameter)
{
/*rts*/	self::$rts_count_of_coren['get_xsl_parameter']++;

/*err*/	if (is_scalar($parameter)) $parameter = trim($parameter); else
/*err*/		throw new coren_exception_bad_xsl_parameter("Can not get xsl parameter because its name is of bad type (must be scalar).");
/*err*/	if ($parameter == '')
/*err*/		throw new coren_exception_bad_xsl_parameter("Can not get xsl parameter because its name is empty string (must be non-empty).");

	return isset(self::$xsl_parameters[$parameter]) ? self::$xsl_parameters[$parameter] : null;
}
####################################################################################################
#
#...
#
public static function set_xsl_parameter ($parameter, $value)
{
/*rts*/	self::$rts_count_of_coren['set_xsl_parameter']++;

/*err*/	if (is_scalar($parameter)) $parameter = trim($parameter); else
/*err*/		throw new coren_exception_bad_xsl_parameter("Can not set xsl parameter because its name is of bad type (must be scalar).");
/*err*/	if ($parameter == '')
/*err*/		throw new coren_exception_bad_xsl_parameter("Can not set xsl parameter because its name is empty string (must be non-empty).");

/*err*/	if (is_null  ($value)) $value = ''; else
/*err*/	if (is_scalar($value)) $value = trim($value); else
/*err*/		throw new coren_exception_bad_xsl_value("Can not set xsl parameter because its value is of bad type (must be scalar or null).");

	if ($value == '')
	{
		if (isset(self::$xsl_parameters[$parameter]))
		    unset(self::$xsl_parameters[$parameter]);
	} else
	{
		self::$xsl_parameters[$parameter] = $value;
	}
}
#
####################################################################################################
###########################  S T A G E    I M P L E M E N T A T I O N S  ###########################
####################################################################################################
#
#...
#
# Инициализирует все константы и переменные,а также конфиги ядра (самого себя то есть).
#
# Модель с базами такая:
# для обоих (дефолтной и ядерной) можно задать название модуля. для дефолтной базы это просто декоративная
# настройка. для ядерной базы это либо декоративная настройка (если отличается от дефолттной), либо указание
# использовать дефолтную базу.
# Для дефолтной базы можно задать класс, который ее обрабатывает, и конфиги. Если класс не задан, то
# дефолтная база не вносится в список модулей, и считается что она придет из списка модулей при
# фетчинге ядерной базы.
# С ядерной базой все сложнее. Если она совпадает по имени модуля с дефолтной, то дефолтная база (модуль)
# должна существовать (то есть быть задана классом и конфигами чуть выше); иначе exception. Если же ядерная
# база не совпадает с дефолтной по имени модуля, то она должна быть явно задана своим собственным классом и
# конфигами; иначе еще другой, схожий exception.
#
protected static $rts_enable		;/*rts*/
protected static $rts_prefix		;/*rts*/
protected static $rts_suffix		;/*rts*/
protected static $default_database	;
protected static $coren_database	;
protected static $created_module_name	;//todo: name it better and nicer
protected static $response_comes_first	;
protected static $dependency_search_root;
protected static $dependency_search_dirs;
#
####################################################################################################
#
private static function __stage__prepare_self__ ()
{
	if (!defined('ABSOLUTE'   )) define('ABSOLUTE'   , (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_ADDR'] . ($_SERVER['SERVER_PORT'] != (isset($_SERVER['HTTPS']) ? 443 : 80) ? ":" . $_SERVER['SERVER_PORT'] : "")) . "/");
	if (!defined('SITEPATH'   )) define('SITEPATH'   , rtrim(str_replace("\\", "/", $_SERVER['DOCUMENT_ROOT']), "/") . "/");
	if (!defined('COREFILE'   )) define('COREFILE'   , str_replace("\\", "/", __FILE__));
	if (!defined('COREPATH'   )) define('COREPATH'   , substr(COREFILE, 0, -strlen(basename(COREFILE))));
	if (!defined('SELFFILE'   )) define('SELFFILE'   , str_replace("\\", "/", $_SERVER['SCRIPT_FILENAME']));
	if (!defined('SELFPATH'   )) define('SELFPATH'   , substr(SELFFILE, 0, -strlen(basename(SELFFILE))));
	if (!defined('DEPTH'      )) define('DEPTH'      , substr_count(SELFPATH, "/") - substr_count(SITEPATH, "/"));
	if (!defined('EXTENSION'  )) define('EXTENSION'  , '.' . pathinfo(COREFILE, PATHINFO_EXTENSION));

	//NB: isolated_load непригоден, так как нам как раз нужно то, что определяется внутри, и нужно чтобы оно попало в наш локальный контекст.
	foreach (array_unique(array(
		COREPATH . '.config' . EXTENSION,
		COREPATH .  'config' . EXTENSION,
		SITEPATH . '.config' . EXTENSION,
		SITEPATH .  'config' . EXTENSION,
		SELFPATH . '.config' . EXTENSION,
		SELFPATH .  'config' . EXTENSION))
		as $file) if (file_exists($file)) { include($file); }

/*rts*/		if (!isset   ($rts_enable)) $rts_enable = false; /*NB: no else */
/*rts*//*err*/	if (is_bool  ($rts_enable)) {/* do nothing */} else
/*rts*//*err*/	if (is_scalar($rts_enable)) $rts_enable = (bool) trim($rts_enable); else
/*rts*//*err*/		throw new coren_exception_config_rts_enable("Can not parse coren's config because 'rts_enable' is of bad type (must be scalar or null or not set at all).");

/*rts*/		if (!isset   ($rts_prefix)) $rts_prefix = ''; /*NB: no else */
/*rts*//*err*/	if (is_scalar($rts_prefix)) $rts_prefix = trim($rts_prefix); else
/*rts*//*err*/		throw new coren_exception_config_rts_prefix("Can not parse coren's config because 'rts_prefix' is of bad type (must be scalar or null or not set at all).");

/*rts*/		if (!isset   ($rts_suffix)) $rts_suffix = ''; /*NB: no else */
/*rts*//*err*/	if (is_scalar($rts_suffix)) $rts_suffix = trim($rts_suffix); else
/*rts*//*err*/		throw new coren_exception_config_rts_suffix("Can not parse coren's config because 'rts_suffix' is of bad type (must be scalar or null or not set at all).");

		if (!isset   ($default_database)) $default_database = '__default_database__'; /*NB: no else */
/*err*/		if (is_scalar($default_database)) $default_database = trim($default_database); else
/*err*/			throw new coren_exception_config_default_database("Can not parse coren's config because 'default_database' is of bad type (must be scalar or null or not set at all).");
/*err*/		if ($default_database == '')
/*err*/			throw new coren_exception_config_default_database("Can not parse coren's config because 'default_database' is empty string (must be non-empty or null or not set at all).");

		if (!isset   ($coren_database)) $coren_database = $default_database; /*NB: no else */
/*err*/		if (is_scalar($coren_database)) $coren_database = trim($coren_database); else
/*err*/			throw new coren_exception_config_coren_database("Can not parse coren's config because 'coren_database' is of bad type (must be scalar or null or not set at all).");
/*err*/		if ($coren_database == '')
/*err*/			throw new coren_exception_config_coren_database("Can not parse coren's config because 'coren_database' is empty string (must be non-empty or null or not set at all).");

		if (!isset   ($created_module_name)) $created_module_name = '__auto_created_module__%s__'; /*NB: no else */
/*err*/		if (is_scalar($created_module_name)) $created_module_name = trim($created_module_name); else
/*err*/			throw new coren_exception_config_created_module_name("Can not parse coren's config because 'created_module_name' is of bad type (must be scalar or null or not set at all).");
/*err*/		if ($created_module_name == '')
/*err*/			throw new coren_exception_config_created_module_name("Can not parse coren's config because 'created_module_name' is empty string (must be non-empty or null or not set at all).");

		if (!isset   ($response_comes_first)) $response_comes_first = false; /*NB: no else */
/*err*/		if (is_bool  ($response_comes_first)) {/* do nothing */} else
/*err*/		if (is_scalar($response_comes_first)) $response_comes_first = (bool) trim($response_comes_first); else
/*err*/			throw new coren_exception_config_response_comes_first("Can not parse coren's config because 'response_comes_first' is of bad type (must be scalar or null or not set at all).");

		if (!isset   ($dependency_search_root)) $dependency_search_root = COREPATH; /*NB: no else */
/*err*/		if (is_scalar($dependency_search_root)) $dependency_search_root = trim($dependency_search_root); else
/*err*/			throw new coren_exception_config_dependency_search_root("Can not parse coren's config because 'dependency_search_root' is of bad type (must be scalar or null or not set at all).");

		if (!isset   ($dependency_search_dirs)) $dependency_search_dirs = ''; /*NB: no else */
/*err*/		if (is_scalar($dependency_search_dirs)) $dependency_search_dirs = trim($dependency_search_dirs); else
/*err*/			throw new coren_exception_config_dependency_search_dirs("Can not parse coren's config because 'dependency_search_dirs' is of bad type (must be scalar or null or not set at all).");

		$dependency_search_dirs = explode(PATH_SEPARATOR, $dependency_search_dirs);
/*err*/		$dependency_search_dirs = array_map('trim', $dependency_search_dirs);
/*err*/		$dependency_search_dirs = array_filter($dependency_search_dirs, 'strlen');

/*rts*/	self::$rts_enable		= $rts_enable			;
/*rts*/	self::$rts_prefix		= $rts_prefix			;
/*rts*/	self::$rts_suffix		= $rts_suffix			;
	self::$default_database		= $default_database		;
	self::$coren_database		= $coren_database		;
	self::$created_module_name	= $created_module_name		;
	self::$response_comes_first	= $response_comes_first		;
	self::$dependency_search_root	= $dependency_search_root	;
	self::$dependency_search_dirs	= $dependency_search_dirs	;

	if (isset($default_database_implementer))
	{
		self::__add_module__($default_database, $default_database_implementer, $default_database);
		if (isset($default_database_configs) && is_array($default_database_configs))
			foreach ($default_database_configs as $config => $value)
				self::__add_config__($default_database, $config, $value);
	}

	if ($coren_database != $default_database)
	if (isset($coren_database_implementer))
	{
		self::__add_module__($coren_database, $coren_database_implementer, $coren_database);
		if (isset($coren_database_configs) && is_array($coren_database_configs))
			foreach ($coren_database_configs as $config => $value)
				self::__add_config__($coren_database, $config, $value);
/*err*/	} else
/*err*/	{
/*err*/		throw new coren_exception_config_have_no_database("Can not fetch coren's data because coren's database module was specified, but its implementor was not. So we have nothing to do.");
/*err*/	} else
/*err*/	if (!isset(self::$modules[$default_database]))
/*err*/	{
/*err*/		throw new coren_exception_config_have_no_database("Can not fetch coren's data because coren's database module aliased to default database module, but default database module is absent. So we have nothing to do.");
	}

	self::$xml_document = new DOMDocument(self::xml_version, self::xml_encoding);
	$xml_documentelement =
		self::$xml_document->createElementNS(self::own_ns_uri, self::own_ns_prefix . ':' . 'data');
		$xml_documentelement->setAttributeNS(self::own_ns_uri, self::own_ns_prefix . ':' . 'version'      , self::version()    );
		$xml_documentelement->setAttributeNS(self::own_ns_uri, self::own_ns_prefix . ':' . 'version_major', self::version_major);
		$xml_documentelement->setAttributeNS(self::own_ns_uri, self::own_ns_prefix . ':' . 'version_minor', self::version_minor);
		$xml_documentelement->setAttributeNS(self::own_ns_uri, self::own_ns_prefix . ':' . 'version_patch', self::version_patch);
	self::$xml_document->appendChild($xml_documentelement);

	self::$xml_xpath = new DOMXPath(self::$xml_document);
	self::$xml_xpath->registerNamespace(self::own_ns_prefix, self::own_ns_uri);

	self::$xsl_document = new DOMDocument(self::xml_version, self::xml_encoding);
	$xsl_documentelement =
		self::$xsl_document->createElementNS(self::xsl_ns_uri, self::xsl_ns_prefix . ':' . 'stylesheet');
		$xsl_documentelement->setAttributeNS(self::xsl_ns_uri, self::xsl_ns_prefix . ':' . 'version'      , self::xsl_version  );
		$xsl_documentelement->setAttributeNS(self::own_ns_uri, self::own_ns_prefix . ':' . 'version'      , self::version()    );
		$xsl_documentelement->setAttributeNS(self::own_ns_uri, self::own_ns_prefix . ':' . 'version_major', self::version_major);
		$xsl_documentelement->setAttributeNS(self::own_ns_uri, self::own_ns_prefix . ':' . 'version_minor', self::version_minor);
		$xsl_documentelement->setAttributeNS(self::own_ns_uri, self::own_ns_prefix . ':' . 'version_patch', self::version_patch);
	self::$xsl_document->appendChild($xsl_documentelement);

	self::$xsl_xpath = new DOMXPath(self::$xsl_document);
	self::$xsl_xpath->registerNamespace(self::own_ns_prefix, self::own_ns_uri);

	//todo: add to xmlroot: coren configs, constants, and so on... ??? или нафиг там конфиги, если они должны влиять только на rnutime? а другие данные пусть добавляют специаьлные модули-добавляторы.
}
#
####################################################################################################
#
# Stage of main work. Connects to coren's own database; nothing more.
# Stage of main work. Fetches information about modules and configs from coren's database.
# Then adds this information into own internal structures. If coren's database was not connected
# before, then nothing is fetched and nothing is appended. So the coren will be empty and will do
# nothing.
#
# Here is no division of configs into per-site, per-package or per-module. This division is on
# responsibility of database engine and the physical structure it uses to store data. Regardless
# of this database engine must return list of configs ordered so that more prioritive config comes
# first. This is because in __add_config__() once config was added for module, it can not be
# overwritten. Also it is on responsibility of database engine to apply value of some multi-module
# configs (per-site, per-package, etc) to every module to which this config is applycable. It can
# be achieved either by sql-joins, or by server-side sql-procedures, or internally in engine.
#
private static function __stage__fetch_modules__ ()
{
	$handle = self::__call__(null, self::$coren_database, 'handle', null);
	if (!is_null($handle))
	{
		$modules = self::db('modules');
		foreach ($modules as $module)
			self::__add_module__($module['module'], $module['implementer'], $module['database']);

		$configs = self::db('configs');
		foreach ($configs as $config)
			self::__add_config__($config['module'], $config['config'], $config['value']);

		$handlers = self::db('handlers');
		foreach ($handlers as $handler)
			self::__add_handler__($handler['module'], $handler['method'], $handler['event'], $handler['map']);

		$parameters = self::db('parameters');
		foreach ($parameters as $parameter)
			self::set_xsl_parameter($parameter['parameter'], $parameter['value']);
	}
}
#
####################################################################################################
#
#...
#
private static function __stage__send_result__ ()
{
//???	self::$xml_document->xinclude();
//???	self::$xsl_document->xinclude();
	self::$xml_document->normalize();
	self::$xsl_document->normalize();

	if (self::$xsl_format == '')
	{
		$result = self::$xml_document;
	} else
	{
		$processor = new XSLTProcessor();
		$processor->importStylesheet(self::$xsl_document);
		foreach (self::$xsl_parameters as $parameter => $value)
			$processor->setParameter(''/* how do this looks like in xml? ???*/, $parameter, $value);
		$result = $processor->transformToDoc(self::$xml_document);
	}

	$result = $result->saveXML();

	//NB: trick to make < ?xml > PI to go first at the page, even if ph errors,warnings,notices appeared.
	//NB: otherwise browser autodetects wrong encoding (opera at least).
	//NB: other way is to print \xEF\xBB\xBF at start of __work__(), but we can not be sure that
	//NB: result will be in UTF8. It can be anything.
	//NB: But this trick behaves bad if level of buffer differs from that was at start.
	if (self::$response_comes_first)
	{
		$buffer = ob_get_contents(); if (ob_get_level() > 0) ob_clean();
		if ($result !== false) print $result;
		if ($buffer !== false) print $buffer;
	} else
	{
		if ($result !== false) print $result;
	}
}
#
####################################################################################################
#
# Stage of main work. Initializes all resident modules, that were previously instantiated.
# Initialization is achieved by calling specially named method '_coren_initialize_' of every
# resident module. Note, that some modules initially marked as resident, may stop being such
# if they could not been instantiated, and so they will not be initialized.
#
# Stage of main work. Shuts down all resident modules, that were previously instantiated and
# initialized. Shutting down is achieved by calling specially named method '_coren_shutdown_'
# of every resident module. Note, that some modules initially marked as resident, may stop
# being such if they could not been instantiated, and so they will not be nor initialized,
# not shut down.
#
private static function __stage__init__    () { self::event(self::event_for_stage_init   ); }
private static function __stage__prework__ () { self::event(self::event_for_stage_prework); }
private static function __stage__content__ () { self::event(self::event_for_stage_content); }
private static function __stage__epiwork__ () { self::event(self::event_for_stage_epiwork); }
private static function __stage__free__    () { self::event(self::event_for_stage_free   ); }
#
####################################################################################################
###############################  M A I N   &   S T A G E    W O R K  ###############################
####################################################################################################
#
#...
#
protected static $stages = array();
protected static $current_stage = null;
#
####################################################################################################
#
private static function __work__do_stage ($stagename)
{
/*rts*/	$stamp = microtime(true);
/*rts*/	self::$rts_time_for_stage[$stagename] = 0.0;
	self::$stages[] = self::$current_stage = $stagename;
	$method = '__stage__' . $stagename . '__';
	try
	{
		self::$method();
	}
	catch (coren_exception_abort_stage $exception) { $exception = null; }
	catch (exception $exception) {}
	self::$current_stage = null;
/*rts*/	self::$rts_time_for_stage[$stagename] += microtime(true) - $stamp;
	if (isset($exception)) throw $exception;
}
#
####################################################################################################
#
private static function __work__do_fatal ($exception)
{
	try { self::event(self::event_for_fatal, compact('exception')); }
	catch (exception $dummy_exception) { unset($dummy_exception); }

	foreach (array_unique(array(
		SELFPATH . '.fatal' . EXTENSION,
		SELFPATH .  'fatal' . EXTENSION,
		SITEPATH . '.fatal' . EXTENSION,
		SITEPATH .  'fatal' . EXTENSION,
		COREPATH . '.fatal' . EXTENSION,
		COREPATH .  'fatal' . EXTENSION))
		as $file) if (file_exists($file)) { include($file); break; }
}
#
####################################################################################################
#
#...
#
public static function __work__ ()
{
/*rts*/	self::__rts__reset__();
	try
	{
		self::__work__do_stage('prepare_self' );
		self::__work__do_stage('fetch_modules');
		self::__work__do_stage('init'         );
		self::__work__do_stage('prework'      );
		self::__work__do_stage('content'      );
		self::__work__do_stage('epiwork'      );
		self::__work__do_stage('free'         );
		self::__work__do_stage('send_result'  );
	}
	catch (coren_exception_abort_work $exception)
	{
		/* do nothing here: just silently exit from coren */
	}
	catch (exception $exception)
	{
		if (self::$response_comes_first)
		{
			$buffer = ob_get_contents();
			if (ob_get_level() > 0) ob_clean();
			self::__work__do_fatal($exception);
			if ($buffer !== false) print $buffer;
		} else
		{
			self::__work__do_fatal($exception);
		}
	}
/*rts*/	self::__rts__print__();
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
#...
#
return defined('COREMANUAL') && COREMANUAL ? null : coren::__work__(); // NB: this is not an exit() from the script, but a valid return from require(core.php).
#
####################################################################################################
####################################################################################################
####################################################################################################
?>