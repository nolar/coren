<?php defined('CORENINPAGE') or die('Hack!');
####################################################################################################
####################################################################################################
####################################################################################################
#
abstract class requisites_request_0 extends coren_module
{
#
####################################################################################################
####################################################################################################
####################################################################################################
#
protected $order = array('G', 'P', 'F', 'C', 'S', 'E');
protected $parent;
#
protected $magic;
#
####################################################################################################
#
public function __construct ($configs)
{
	parent::__construct($configs);

	if (isset($configs['order' ])) $this->order  = str_split($configs['order']);
	if (isset($configs['parent'])) $this->parent = $configs['parent'];

	$this->magic = get_magic_quotes_gpc();
}
#
####################################################################################################
####################################################################################################
####################################################################################################
#
public function retrieve ($data)
{
	$requisites = isset($data['requisites']) ? $data['requisites'] : null;

	$result = array();
	$parent = $this->magic ? addslashes($this->parent) : $this->parent;

	if (is_array($requisites))
	foreach ($requisites as $requisite)
	{
		$result[$requisite] = null;
		$field = $this->field_of_requisite($requisite);
		if (is_null($field)) continue;
		$field = $this->magic ? addslashes($field) : $field;
		foreach ($this->order as $source)
		switch ($source)
		{
			case 'g': case 'G': if (!is_null($result[$requisite] = $this->_get_($_GET   , $parent, $field))) break 2; break;
			case 'p': case 'P': if (!is_null($result[$requisite] = $this->_get_($_POST  , $parent, $field))) break 2; break;
			case 'c': case 'C': if (!is_null($result[$requisite] = $this->_get_($_COOKIE, $parent, $field))) break 2; break;
			case 's': case 'S': if (!is_null($result[$requisite] = $this->_get_($_SERVER, $parent, $field))) break 2; break;
			case 'e': case 'E': if (!is_null($result[$requisite] = $this->_get_($_ENV   , $parent, $field))) break 2; break;
			case 'f': case 'F':
				//todo:!!! to_doc(from reorganized $_FILES according to $this->parent)
				break;
		}
	}

	return $result;
}
#
####################################################################################################
#
private function _get_ ($array, $parent, $field)
{
	if ($parent != '')
		$array = array_key_exists($parent, $array) ? $array[$parent] : null;

	if (isset($array[$field]))
		return $array[$field];
	else
		return null;
}
#
####################################################################################################
####################################################################################################
####################################################################################################
#
abstract protected function field_of_requisite ($requisite);
#
####################################################################################################
####################################################################################################
####################################################################################################
}
?>