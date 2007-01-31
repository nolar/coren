<?php defined('CORENINPAGE') or die('Hack!');
####################################################################################################
####################################################################################################
####################################################################################################
#
####################################################################################################
####################################################################################################
####################################################################################################
#
class cache_memory_0 extends coren_module
{
#
####################################################################################################
####################################################################################################
####################################################################################################
#
protected $cache = array();
#
####################################################################################################
#
public function __construct ($configs)
{
	parent::__construct($configs);
}
#
####################################################################################################
####################################################################################################
####################################################################################################
#
public function get ($data)
{
	$id = isset($data['identifier']) && (is_string($data['identifier']) || is_integer($data['identifier'])) ? $data['identifier'] : null;
	if ($id == '') throw new exception("Bad identifier for cache item. Must be non-empty string or integer.");

	if (array_key_exists($id, $this->cache))
	{
		$result = $this->cache[$id];
	} else
	{
		$result = null;
	}
	return $result;
}
#
####################################################################################################
#
public function set ($data)
{
	$temp = isset($data['data']) ? $data['data'] : null;
	if (is_null($temp)) return $this->reset($data);

	$id = isset($data['identifier']) && (is_string($data['identifier']) || is_integer($data['identifier'])) ? $data['identifier'] : null;
	if ($id == '') throw new exception("Bad identifier for cache item. Must be non-empty string or integer.");

	$this->cache[$id] = $temp;
}
#
####################################################################################################
#
public function reset ($data)
{
	$id = isset($data['identifier']) && (is_string($data['identifier']) || is_integer($data['identifier'])) ? $data['identifier'] : null;
	if ($id == '') throw new exception("Bad identifier for cache item. Must be non-empty string or integer.");

	if (array_key_exists($id, $this->cache))
	{
		unset($this->cache[$id]);
	}
}
#
####################################################################################################
#
public function check ($data)
{
	$id = isset($data['identifier']) && (is_string($data['identifier']) || is_integer($data['identifier'])) ? $data['identifier'] : null;
	if ($id == '') throw new exception("Bad identifier for cache item. Must be non-empty string or integer.");

	return (bool) array_key_exists($id, $this->cache);
}
#
####################################################################################################
#
public function clear ($data)
{
	$this->cache = array();
}
#
####################################################################################################
####################################################################################################
####################################################################################################
}
?>