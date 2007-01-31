<?php defined('CORENINPAGE') or die('Hack!');
####################################################################################################
####################################################################################################
####################################################################################################
#
#...
#
####################################################################################################
####################################################################################################
####################################################################################################
#
abstract class cache_directory_0 extends coren_module
{
#
####################################################################################################
####################################################################################################
####################################################################################################
#
protected $lock_relax;
protected $lock_count;
protected $lock_sleep;
protected $chunk_size;
#
protected $dir_path    ;
protected $dir_absolute;
protected $dir_required;
protected $dir_automake;
protected $dir_umask   ;
protected $dir         ;
#
####################################################################################################
#
public function __construct ($configs)
{
	parent::__construct($configs);

	if (!coren::depend('_path_normalizer_0'))
		throw new exception("Tool '_path_normalizer_0' missed.");

	if(isset($configs['lock_relax'])) $this->lock_relax = $configs['lock_relax'];
	if(isset($configs['lock_count'])) $this->lock_count = $configs['lock_count'];
	if(isset($configs['lock_sleep'])) $this->lock_sleep = $configs['lock_sleep'];

	if(isset($configs['chunk_size'])) $this->chunk_size = $configs['chunk_size'];
	$this->chink_size = (integer) $this->chunk_size;
	if ($this->chunk_size <= 0) $this->chunk_size = 1024;

	if(isset($configs['dir_path'    ])) $this->dir_path     = $configs['dir_path'    ];
	if(isset($configs['dir_absolute'])) $this->dir_absolute = $configs['dir_absolute'];
	if(isset($configs['dir_required'])) $this->dir_required = $configs['dir_required'];
	if(isset($configs['dir_automake'])) $this->dir_automake = $configs['dir_automake'];
	if(isset($configs['dir_umask'   ])) $this->dir_umask    = $configs['dir_umask'   ];
	$this->dir = _path_normalizer_0::normalize_dir($this->dir_path, $this->dir_absolute ? null : SITEPATH);
	if ($this->dir_automake && !file_exists($this->dir))
	{
		$old_umask = umask(octdec($this->dir_umask));
		mkdir($this->dir, 0777, true);
		umask($old_umask);
	}
	if ($this->dir_required && !is_dir($this->dir))
	{
		throw new exception("Required directory '{$this->dir}' does not exist.");
	}
}
#
####################################################################################################
####################################################################################################
####################################################################################################
#
public function exists ($data)
{
	$identifier = isset($data['identifier']) ? $data['identifier'] : null;
	$path = $this->dir . $this->_filename_($identifier);
	return (bool) file_exists($path);
}
#
####################################################################################################
#
public function clear ($data)
{
	$handle = opendir($this->dir);
	if ($handle !== false)
	{
		while (($filename = readdir($handle)) !== false)
			if (($filename != '.') && ($filename != '..'))
				$this->_erase_($this->dir . $filename);
		closedir($handle);
	}
}
#
####################################################################################################
####################################################################################################
####################################################################################################
#
public function get_list ($data)
{
	$list = isset($data['list']) && is_array($data['list']) ? $data['list'] : array();
	$result = array();
	foreach ($list as $identifier => $default)
	{
		$path = $this->dir . $this->_filename_($identifier);
		$encoded = $this->_load_($path);
		$decoded = is_null($encoded) ? null : $this->_decode_($encoded);
		if (is_null($decoded))
			$result[$identifier] = $default;
		else
			$result[$identifier] = $decoded;
	}
	return $result;
}
#
####################################################################################################
#
public function set_list ($data)
{
	$list = isset($data['list']) && is_array($data['list']) ? $data['list'] : array();
	foreach ($list as $identifier => $decoded)
	{
		$path = $this->dir . $this->_filename_($identifier);
		$encoded = is_null($decoded) ? null : $this->_encode_($decoded);
		if (is_null($encoded))
			$this->_erase_($path);
		else
			$this->_save_($path, $encoded);
	}
}
#
####################################################################################################
#
public function clr_list ($data)
{
	$list = isset($data['list']) && is_array($data['list']) ? $data['list'] : array();
	foreach ($list as $identifier => $not_used_variable)
	{
		$path = $this->dir . $this->_filename_($identifier);
		$this->_erase_($path);
	}
}
#
####################################################################################################
#
public function get_item ($data)
{
	$identifier = isset($data['identifier']) ? $data['identifier'] : null;
	$default    = isset($data['default'   ]) ? $data['default'   ] : null;
	if (!is_scalar($identifier)) throw new exception("Bad identifier for cache item. Must be scalar.");
	$temp = $this->get_list(array('list'=>array($identifier => $default)));
	return isset($temp[$identifier]) ? $temp[$identifier] : null;
}
#
####################################################################################################
#
public function set_item ($data)
{
	$identifier = isset($data['identifier']) ? $data['identifier'] : null;
	$value      = isset($data['value'     ]) ? $data['value'     ] : null;
	if (!is_scalar($identifier)) throw new exception("Bad identifier for cache item. Must be scalar.");
	$this->set_list(array('list'=>array($identifier => $value)));
}
#
####################################################################################################
#
public function clr_item ($data)
{
	$identifier = isset($data['identifier']) ? $data['identifier'] : null;
	if (!is_scalar($identifier)) throw new exception("Bad identifier for cache item. Must be scalar.");
	$this->clr_list(array('list'=>array($identifier => null)));
}
#
####################################################################################################
####################################################################################################
####################################################################################################
#
abstract protected function _encode_ ($decoded);
abstract protected function _decode_ ($encoded);
#
####################################################################################################
#
final protected function _filename_ ($identifier)
{
	$result = $identifier;
	$result = strtr($identifier, array("@"=>"@@", "/"=>"@F", "\\"=>"@B"));
	if ($result == '') $result = '@';
	return $result;
}
#
####################################################################################################
#
protected function _load_ ($path)
{
	if (!is_file($path)) return null;

	$handle = @fopen($path, 'rb');
	$error = isset($php_errormsg) ? $php_errormsg : null;
	if ($handle === false)
		if (isset($error))
			throw new exception("Can not open cache file '{$path}' for reading: {$error}");
		else	throw new exception("Can not open cache file '{$path}' for reading.");

	$ignore = ignore_user_abort(true);
	$locked = @flock($handle, LOCK_SH);//todo: make it in a while(), with LOCK_NB & usleep()s, and, mainly, timeouting of lock waits.
	$error = isset($php_errormsg) ? $php_errormsg : null;
	if (!$locked && !$this->lock_relax)
	{
		fclose($handle);
		ignore_user_abort($ignore);
		if (isset($error))
			throw new exception("Can not lock cache file '{$path}' for reading: {$error}");
		else	throw new exception("Can not lock cache file '{$path}' for reading.");
	}

	$result = '';
	while (!feof($handle)) { $result .= fread($handle, $this->chunk_size); }
	fclose($handle);//NB: also unlocks file, if it was locked.
	ignore_user_abort($ignore);
	return $result;
}
#
####################################################################################################
#
protected function _save_ ($path, $value)
{
	$handle = @fopen($path, 'ab');
	$error = isset($php_errormsg) ? $php_errormsg : null;
	if ($handle === false)
		if (isset($error))
			throw new exception("Can not open cache file '{$path}' for writing: {$error}");
		else	throw new exception("Can not open cache file '{$path}' for writing.");

	$ignore = ignore_user_abort(true);
	$locked = @flock($handle, LOCK_EX);//todo: make it in a while(), with LOCK_NB & usleep()s, and, mainly, timeouting of lock waits.
	$error = isset($php_errormsg) ? $php_errormsg : null;
	if (!$locked && !$this->lock_relax)
	{
		fclose($handle);
		ignore_user_abort($ignore);
		if (isset($error))
			throw new exception("Can not lock cache file '{$path}' for writing: {$error}");
		else	throw new exception("Can not lock cache file '{$path}' for writing.");
	}

	ftruncate($handle, 0);
	while (strlen($value)) { fwrite($handle, $value, $this->chunk_size); $value = substr($value, $this->chunk_size); }
	fclose($handle);//NB: also unlocks file, if it was locked.
	ignore_user_abort($ignore);
}
#
####################################################################################################
#
protected function _erase_ ($path)
{
	//NB: no locking implemented, since unlink()ing will not corrupt data consistency.
	if (is_file($path))
		unlink($path);
}
#
####################################################################################################
####################################################################################################
####################################################################################################
}
?>