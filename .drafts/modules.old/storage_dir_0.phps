<?php defined('CORENINPAGE') or die('Hack!');
//todo: 1. make filesystem operations more secure and paranoid.
//todo: 2. make errors in filesystem as exceptions. (?)
//todo: 3. make forced mode for directories: autocreate of whole hierarchy. configurable.

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class storage_dir_0 extends module
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected $realdir;
protected $tempdir;
protected $realurl;
protected $tempurl;

protected $umask;

protected $buffer;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function __construct ($configs)
{
	parent::__construct($configs);

	$this->realdir = isset($configs['realdir']) ? $configs['realdir'] : null;
	if (!isset($this->realdir)) throw new exception('misconfig_realdir');
	else $this->realdir = core::normalize_path($this->realdir, SITEPATH);

	$this->tempdir = isset($configs['tempdir']) ? $configs['tempdir'] : null;
	if (!isset($this->tempdir)) throw new exception('misconfig_tempdir');
	else $this->tempdir = core::normalize_path($this->tempdir, SITEPATH);

	$this->realurl = isset($configs['realurl']) ? $configs['realurl'] : null;
	if (!isset($this->realurl))
		if (strncmp($this->realdir, SITEPATH, $length = strlen(SITEPATH)) === 0)
			$this->realurl = substr($this->realdir, $length - 1);
	if (!isset($this->realurl)) throw new exception('misconfig_realurl (or can not determine)');

	$this->tempurl = isset($configs['tempurl']) ? $configs['tempurl'] : null;
	if (!isset($this->tempurl))
		if (strncmp($this->tempdir, SITEPATH, $length = strlen(SITEPATH)) === 0)
			$this->tempurl = substr($this->tempdir, $length - 1);
	if (!isset($this->tempurl)) throw new exception('misconfig_tempurl (or can not determine)');

	$this->umask = isset($configs['umask']) ? $configs['umask'] : null;
	$this->umask = octdec(ltrim($this->umask, ' 0'));
	if ($this->umask == 0) $this->umask = null;

	$this->buffer = array();
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function upload ($args)
{
	$mime = isset($args['mime']) ? $args['mime'] : null;
	$name = isset($args['name']) ? $args['name'] : null;
	$size = isset($args['size']) ? $args['size'] : null;
	$path = isset($args['path']) ? $args['path'] : null;

	if (!isset($path)) return;
	if (!is_file($path)) return;
	if (!is_readable($path)) return;

	//!!!todo: normalize $name to be strict(?) - i.e. only alphanum in name. but keep an extension.
	//!!!todo: или кодировать русские имена в урлах более жестко; так, чтобы они правильно ередавались на сервер?
	//!!!todo: кодировать не получится, так как оно кодирует еще и все слеши в пути, в том числе между id и filename.

	do {
		$id = sprintf("%06d", rand(0, 1000000-1));//!!!todo: make it more unique

		$realdir = $this->realdir . "/" . $id; $realfile = $realdir . "/" . $name;
		$tempdir = $this->tempdir . "/" . $id; $tempfile = $tempdir . "/" . $name;
		$exists = file_exists($realfile) || file_exists($tempfile);
	} while ($exists/*!!!todo: || configurable maxtrycount is out */);

	if ($exists)
		throw new exception("Can not generate unique identifier for storing file.");

	if (!is_null($this->umask)) $oldmask = umask($this->mask);
	if (@mkdir($tempdir) === false)
	{
		if (!is_null($this->umask)) umask($oldmask);
		throw new exception("Can not make dir '{$tempdir}' for temporary place" . (isset($php_errormsg) ? ": " . $php_errormsg : "") . ".");
	}
	//todo: пытаться делать жесткий линк, и тоьлко если он не удался - делать копию. link($path, $tempfile)
	if (@copy($path, $tempfile) === false)
	{
		if (!is_null($this->umask)) umask($oldmask);
		throw new exception("Can not copy file '{$path}' to its temporary place '{$tempfile}'" . (isset($php_errormsg) ? ": " . $php_errormsg : "") . ".");
	}
	if (!is_null($this->umask)) umask($oldmask);

	return $id . "/" . $name;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function path ($args)
{
	$id = isset($args['id']) ? $args['id'] : null;
	if (!isset($id)) return;

	$realfile = $this->realdir . "/" . $id;
	$tempfile = $this->tempdir . "/" . $id;

	if (file_exists($realfile))
		return $realfile;
	if (file_exists($tempfile))
		return $tempfile;
	return null;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function uri ($args)
{
	$id = isset($args['id']) ? $args['id'] : null;
	if (!isset($id)) return;

	$realfile = $this->realdir . "/" . $id;
	$tempfile = $this->tempdir . "/" . $id;

	if (file_exists($realfile))
		return $this->realurl . "/" . $id;
	if (file_exists($tempfile))
		return $this->tempurl . "/" . $id;
	return null;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function fixate ($args)
{
	$id = isset($args['id']) ? $args['id'] : null;
	if (!isset($id)) return;
	if (!isset($this->buffer[$id])) $this->buffer[$id] = null;
	$this->buffer[$id] = true;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function unlink ($args)
{
	$id = isset($args['id']) ? $args['id'] : null;
	if (!isset($id)) return;
	if (!isset($this->buffer[$id])) $this->buffer[$id] = null;
	$this->buffer[$id] = $this->buffer[$id] ? $this->buffer[$id] : false;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function apply ($args)
{
	foreach ($this->buffer as $id => $op)
	{
		$realfile = $this->realdir . "/" . $id;
		$tempfile = $this->tempdir . "/" . $id;
		$realdir = dirname($realfile);
		$tempdir = dirname($tempfile);

		if ($op === true)
		{
			if (file_exists($tempdir)) rename($tempdir, $realdir);
		} else
		if ($op === false)
		{
			if (file_exists($realfile)) unlink($realfile);
			if (file_exists($tempfile)) unlink($tempfile);
			if (file_exists($realdir)) rmdir ($realdir);
			if (file_exists($tempdir)) rmdir ($tempdir);
		} else
		{
			/* do nothing because here is nothing to do */
		}
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

?>