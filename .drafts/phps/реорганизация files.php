<?php
####################################################################################################
#################################  R E Q U E S T    P A R S I N G  #################################
####################################################################################################
/*
//!!!!! этим делом должно заниматься не ядро, а специальные модули. !!!!
#
# HTTP requests are assumed to be of some special structure. PHP supports structural fields in
# GET/POST/FILES/COOKIE superglobal arrays. Coren use this ability to structurize input fields by
# module. I.e. each module has its own subsection in GPFC arrays. To split this structured fields
# from other data fields, all per-module structures are prefixed. Technically this is done by
# specifing some specially-named field, that must contain hash with module names as keys and value
# as per-module datas. Name of such a special field is stored in this configurable variable.
#
protected static $request_field_prefix = '@';
#
# When coren starts, it parses HTTP request by splitting all GPFC data by module codename. All
# parsed structures are stored in this fields. Fields are hashes, with keys being module codenames
# and values being modules' datas. In addition, all slashes are stripped in this datas, and FILES
# are reorganized to be more easy-to-use (see below).
#
protected static $request_data_get    = null;
protected static $request_data_post   = null;
protected static $request_data_files  = null;
protected static $request_data_cookie = null;
#
####################################################################################################
#
# Quotes name of the field, so it may be put into HTTP response (into form or into url), and by this
# name datas will be passed directly to the module, who requested quoting. Fieldname can be in the
# form 'X' or 'X[Y]' or '[X][Y]'; anyway, it will be converted to 'prefix[MODULENAME][X][Y]'.
# If you will request empty field names, this method will return full prefix for celler's HTTP
# fields.
#
# Returns converted and quoted name of the field.
#
public static function field ($field)
{
	self::$rstats_count_of_corenapi['field']++; #rstats#

	if (empty(self::$stack))
		throw new coren_exception_out_of_stack("Method coren::field() must be called from within module.");

	if (!is_string($field))
		throw new coren_exception("Name of the field must be string in coren::field().");

	$module = self::$stack[count(self::$stack)-1]['module'];

	$pos = str_pos($field, '[');
	if ($field == '')
	{
		return self::$request_field_prefix . '[' . $module . ']';
	} else
	if ($pos === false)
	{
		return self::$request_field_prefix . '[' . $module . ']' . '[' . $field . ']';
	} else
	if ($pos === 0)
	{
		return self::$request_field_prefix . '[' . $module . ']' . $field;
	} else
	{
		$s1 = substr($field, 0, $pos);
		$s2 = substr($field, $pos);
		return self::$request_field_prefix . '[' . $module . ']' . '[' . $s1 . ']' . $s2;
	}
}
#
####################################################################################################
#
# Returns module's dequoted GET data.
#
public static function request_get ()
{
	self::$rstats_count_of_corenapi['request_get']++; #rstats#

	if (empty(self::$stack))
		throw new coren_exception_out_of_stack("Method request_get() must be called from within module.");

	$module = self::$stack[count(self::$stack)-1]['module'];

	return array_key_exists($module, self::$request_data_get) ? self::$request_data_get[$module] : array();
}
#
####################################################################################################
#
# Returns module's dequoted POST data.
#
public static function request_post ()
{
	self::$rstats_count_of_corenapi['request_post']++; #rstats#

	if (empty(self::$stack))
		throw new coren_exception_out_of_stack("Method request_post() must be called from within module.");

	$module = self::$stack[count(self::$stack)-1]['module'];

	return array_key_exists($module, self::$request_data_post) ? self::$request_data_post[$module] : array();
}
#
####################################################################################################
#
# Returns module's reorganized FILES data.
#
public static function request_files ()
{
	self::$rstats_count_of_corenapi['request_files']++; #rstats#

	if (empty(self::$stack))
		throw new coren_exception_out_of_stack("Method request_files() must be called from within module.");

	$module = self::$stack[count(self::$stack)-1]['module'];

	return array_key_exists($module, self::$request_data_files) ? self::$request_data_files[$module] : array();
}
#
####################################################################################################
#
# Returns module's dequoted COOKIE data.
#
public static function request_cookie ()
{
	self::$rstats_count_of_corenapi['request_cookie']++; #rstats#

	if (empty(self::$stack))
		throw new coren_exception_out_of_stack("Method request_cookie() must be called from within module.");

	$module = self::$stack[count(self::$stack)-1]['module'];

	return array_key_exists($module, self::$request_data_cookie) ? self::$request_data_cookie[$module] : array();
}
#
####################################################################################################
#
#...
#
private static function __stage__parse_request__ ()
{
	$get    = $_GET   ;
	$post   = $_POST  ;
	$files  = $_FILES ;
	$cookie = $_COOKIE;

	if (get_magic_quotes_gpc())
	{
		$get    = self::__stage__parse_request__stripslashes($get   );
		$post   = self::__stage__parse_request__stripslashes($post  );
		$cookie = self::__stage__parse_request__stripslashes($cookie);
	}

	$files = self::__stage__parse_request__files_toplevel($files);

	self::$request_data_get    = self::__stage__parse_request__struct($get   );
	self::$request_data_post   = self::__stage__parse_request__struct($post  );
	self::$request_data_files  = self::__stage__parse_request__struct($files );
	self::$request_data_cookie = self::__stage__parse_request__struct($cookie);
}
#
#...
#
private static function __stage__parse_request__struct ($array)
{
	$result = array();
	$prefix = self::$request_field_prefix;
	if (is_array($array) && array_key_exists($prefix, $array) && is_array($array[$prefix]))
		foreach ($array[$prefix] as $module => $data)
			if (is_array($data))
				$result[$module] = $data;
	return $result;
}
#
#...
#
private static function __stage__parse_request__stripslashes ($value)
{
	if (is_array($value))
	{
		$result = array();
		foreach ($value as $key => $val)
			$result[stripslashes($key)] = self::__stage__parse_request__stripslashes($val);
	} else
	{
		$result = stripslashes($value);
	}
	return $result;
}
#
#...
#
private static function __stage__parse_request__files_toplevel ($value)
{
	if (is_array($value))
	{
		$result = array();
		foreach ($value as $key1 => $tmp)
		{
			if (is_array($tmp))
			{
				$result[$key1] = array();
				foreach ($tmp as $key2 => $sub)
					self::__stage__parse_request__files_recurse($result[$key1], $sub, $key2);
			} else
			{
				$result[$key1] = $tmp;
			}
		}
	} else
	{
		$result = $value;
	}
	return $result;
}
#
#...
#
private static function __stage__parse_request__files_recurse (&$result, $value, $metaindex)
{
	if (is_array($value))
	{
		foreach ($value as $key => $val)
		{
			if (!array_key_exists($key, $result) || !is_array($result[$key]))
				$result[$key] = array();
			$this->build_initial_files_recurse($result[$key], $val, $metaindex);
		}
	} else
	{
		$result[$metaindex] = $value;
	}
}
*/
#
