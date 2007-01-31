<?php defined('CORENINPAGE') or die('Hack!');

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class recurse_0 extends module
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected $filetype;
protected $fileauto;
protected $filehere;
protected $updown;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __construct ($configs)
{
	parent::__construct($configs);

	$this->filetype = isset($configs['filetype']) ? $configs['filetype'] : null;
	$this->fileauto = isset($configs['autofile']) ? $configs['autofile'] : 'auto'  . (isset($this->filetype) ? '.' . $this->filetype : '');
	$this->filehere = isset($configs['herefile']) ? $configs['herefile'] : 'index' . (isset($this->filetype) ? '.' . $this->filetype : '');
	$this->updown   = isset($configs['updown'  ]) ? $configs['updown'  ] : null;
	if (!isset($this->filetype)) throw new exception('misconfig_type');//!!! make normal text for exception message
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function main ($args)
{
	$fileshere = $this->scan(array('filename'=>$this->filehere, 'limitdepth'=>true));
	$filesauto = $this->scan(array('filename'=>$this->fileauto));

	$files = array();
	foreach ($fileshere as $filename) $files[] = $filename;
	foreach ($filesauto as $filename) $files[] = $filename;
	$files = array_unique($files);
	if ($this->updown) $files = array_reverse($files);

	$result = array();
	foreach ($files as $file)
	{
		$temp = core::template('item', array('file'=>$file));
		if ($temp != '') $result[] = $temp;
	}
	$result = implode(core::template('glue'), $result);
	echo $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function scan ($args)
{
	$forcedir   = isset($args['forcedir'  ]) ? $args['forcedir'  ] : null;
	$filename   = isset($args['filename'  ]) ? $args['filename'  ] : null;
	$limitdepth = isset($args['limitdepth']) ? $args['limitdepth'] : null;
	if (!is_scalar($filename) || ($filename == '')) throw new exception('bad filename');

	$limitdepth = $limitdepth ? 0 : DEPTH;
	$forcedir = $forcedir ? './' : '';

	$result = array();
	for ($level = 0; $level <= $limitdepth; $level++)
	{
		$path = ($level == 0 ? $forcedir : ($level === 1 ? '../' : $path . '../'));
		if (is_file($path . $filename))
			$result[] = $path . $filename;
	}
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

?>