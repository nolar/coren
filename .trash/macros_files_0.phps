<?php defined('COREINPAGE') or die('Hack!');

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class macros_files_0 extends module
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected $filesdir;

private $itemstates;
private $foundfiles;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __construct ($configs)
{
	parent::__construct($configs);

	$this->filesdir = isset($configs['filesdir']) ? $configs['filesdir'] : null;
	if (!isset($this->filesdir)) throw new exception('misconfig: filesdir');
	else $this->filesdir = core::normalize_path($this->filesdir, SITEPATH);//??? or COREPATH?

	$this->itemstates = array();
	$this->foundfiles = array();
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function core_macros ($args)
{
	$category = isset($args['category']) ? $args['category'] : null;
	$values   = isset($args['values'  ]) ? $args['values'  ] : null;
	$item     = isset($args['item'    ]) ? $args['item'    ] : null;
	$data     = isset($args['data'    ]) ? $args['data'    ] : null;

	if (array_key_exists($category, $this->foundfiles) && array_key_exists($item, $this->foundfiles[$category]))
	{
		$file = $this->foundfiles[$category][$item];
	} else
	{
		if (!is_array($values)) $values = array();

		$file = null;
		foreach ($values as $value)
		{
			$file = $this->find_file($this->filesdir, $category, $value, $item, EXTENSION);
			if (is_null($file)) continue;

			if (!isset($this->itemstates[$category]       )) $this->itemstates[$category]        = array();
			if (!isset($this->itemstates[$category][$item])) $this->itemstates[$category][$item] = array();
		}

		if (!isset($this->foundfiles[$category]       )) $this->foundfiles[$category]        = array();
		if (!isset($this->foundfiles[$category][$item])) $this->foundfiles[$category][$item] = $file;
	}

	if (!is_null($file))
	{
		ob_start();
		try
		{
			$this->__execute__($file, $data, $this->itemstates[$category][$item]);
		}
		catch (exception $exception)
		{
			ob_end_clean();
			throw $exception;
		}
		return ob_get_clean();
	} else
	{
//		echo "MISSED={$item}<br>\n";//!!!
		return null;
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/*
//debug: todo: сделать эту функцию конфигурабельной.
function core_stop ($args)
{
	foreach ($this->foundfiles as $category => $temp)
	foreach ($temp as $item => $file)
	if (is_null($file))
	{
		echo "CATEGORY="; var_dump($category); echo ";";
		echo "ITEM="    ; var_dump($item    ); echo "<br>\n";
	}
}
/**/

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function find_file ($filesdir, $category, $value, $item, $extension)
{
	$filename = $filesdir . '/' . $category . '/' . $value . '/' . $item . $extension;
	return file_exists($filename) ? $filename : null;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

private function __execute__ ($__file__, &$data, &$state)
{
	return require($__file__);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

?>