<?php defined('CORENINPAGE') or die('Hack!');
//??? 1. имеет ли он какие-либо конфиги? например, default_limitx, default_limity, default_scale, default_jpeg_quality, default_safe...
//??? 2. использовать ли ceil(), или же лучше перевести на round()? floor()?

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class imager_0 extends module
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __construct ($configs)
{
	parent::__construct($configs);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function calculate ($args)
{
	$nowx   = (integer) core::find_scalar(array($args), array('nowx'  ), null);
	$nowy   = (integer) core::find_scalar(array($args), array('nowy'  ), null);
	$newx   = (integer) core::find_scalar(array($args), array('newx'  ), null);
	$newy   = (integer) core::find_scalar(array($args), array('newy'  ), null);
	$scale  = (float  ) core::find_scalar(array($args), array('scale' ), null);
	$limitx = (integer) core::find_scalar(array($args), array('limitx'), 1   );
	$limity = (integer) core::find_scalar(array($args), array('limity'), 1   );

	if ($nowx   <= 0) throw new exception("Bad now x.");
	if ($nowy   <= 0) throw new exception("Bad now y.");
	if ($newx   <  0) throw new exception("Bad new x.");
	if ($newy   <  0) throw new exception("Bad new y.");
	if ($scale  <  0) throw new exception("Bad scale.");
	if ($limitx <= 0) throw new exception("Bad limit x.");
	if ($limity <= 0) throw new exception("Bad limit y.");

	$nowr = 1.0 * $nowx / $nowy;

	if (($newx == 0) && ($newy == 0))
	{
		if ($scale > 0.0)
		{
			$newx = ceil($nowx * $scale);
			$newy = ceil($nowy * $scale);
		} else
		{
			if ($nowr < 1.0)
			{
				$newy = ceil(min($nowy, $limity, $limitx / $nowr));
				$newx = ceil($newy * $nowr);
			} else
			{
				$newx = ceil(min($nowx, $limitx, $limity * $nowr));
				$newy = ceil($newx / $nowr);
			}
		}
	} else
	if ($newx == 0)
	{
		$newx = ceil($nowr * $newy);
	} else
	if ($newy == 0)
	{
		$newy = ceil($nowr * $newx);
	}

	return array($newx, $newy);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function info ($args)
{
	$path       = core::find_scalar(array($args), array('path'      ), null);
	$prefix     = core::find_scalar(array($args), array('prefix'    ), null);
	$force_mime = core::find_scalar(array($args), array('force_mime'), null);
	$force_name = core::find_scalar(array($args), array('force_name'), null);
	$force_size = core::find_scalar(array($args), array('force_size'), null);

	if ($path == ''      ) return false;
	if (!is_file($path)  ) return false;

	$mime = ($force_mime != '') ? $force_mime : null;
	$name = ($force_name != '') ? $force_name : basename($path);
	$size = ($force_size != '') ? $force_size : filesize($path);

	$info = getimagesize($path);
	if ($info !== false)
	{
		$xsize = $info[0];
		$ysize = $info[1];
		if ($mime == '') $mime = image_type_to_mime_type($info[2]);
	} else
	{
		$xsize = null;
		$ysize = null;
	}

	return array(
		$prefix . 'path'  => $path,
		$prefix . 'mime'  => $mime,
		$prefix . 'name'  => $name,
		$prefix . 'size'  => $size,
		$prefix . 'xsize' => $xsize,
		$prefix . 'ysize' => $ysize);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function make ($args)
{
	$source_path   = core::find_scalar(array($args), array('source_path'  ), null);
	$target_path   = core::find_scalar(array($args), array('target_path'  ), null);
	$safe          = core::find_scalar(array($args), array('safe'         ), null);
	$x             = core::find_scalar(array($args), array('x'            ), null);
	$y             = core::find_scalar(array($args), array('y'            ), null);
	$callback_func = core::find_value (array($args), array('callback_func'), null, true);
	$callback_args = core::find_value (array($args), array('callback_args'), null, true);
	$jpeg_quality  = core::find_scalar(array($args), array('jpeg_quality' ), 75);

	$info = getimagesize($source_path);
	if ($info === false)
	{
		$result = $safe ? copy($source_path, $target_path) : false;
		return $result;
	}

	$x = (integer) $x;
	$y = (integer) $y;
	$source_xsize = $info[0];
	$source_ysize = $info[1];
	$source_type  = $info[2];
	$target_xsize = $x > 0 ? $x : $source_xsize;
	$target_ysize = $y > 0 ? $y : $source_ysize;
	$target_type  = $source_type;

	switch ($source_type)
	{
		case IMAGETYPE_GIF : $source_image = imagecreatefromgif ($source_path); break;
		case IMAGETYPE_JPEG: $source_image = imagecreatefromjpeg($source_path); break;
		case IMAGETYPE_PNG : $source_image = imagecreatefrompng ($source_path); break;
		case IMAGETYPE_WBMP: $source_image = imagecreatefromwbmp($source_path); break;
		case IMAGETYPE_XBM : $source_image = imagecreatefromxbm ($source_path); break;
		default            : $source_image = false;
	}

	if ($source_image === false)
	{
		$result = $safe ? copy($source_path, $target_path) : false;
		return $result;
	}

	$target_image = imagecreatetruecolor($target_xsize, $target_ysize);

	if ($target_image === false)
	{
		imagedestroy($source_image);
		$result = $safe ? copy($source_path, $target_path) : false;
		return $result;
	}

	$result = is_callable($callback_func) ? call_user_func($callback_func, $source_image, $source_xsize, $source_ysize, $target_image, $target_xsize, $target_ysize, $callback_args) : false;

	if ($result === false)
	{
		imagedestroy($source_image);
		imagedestroy($target_image);
		$result = $safe ? copy($source_path, $target_path) : false;
		return $result;
	}

	switch ($target_type)
	{
		case IMAGETYPE_GIF : $result = imagegif ($target_image, $target_path); break;
		case IMAGETYPE_JPEG: $result = imagejpeg($target_image, $target_path, $jpeg_quality); break;
		case IMAGETYPE_PNG : $result = imagepng ($target_image, $target_path); break;
		case IMAGETYPE_WBMP: $result = imagewbmp($target_image, $target_path); break;
		case IMAGETYPE_XBM : $result = imagexbm ($target_image, $target_path); break;
		default            : $result = false;
	}

	imagedestroy($source_image);
	imagedestroy($target_image);

	if ($result === false)
	{
		$result = $safe ? copy($source_path, $target_path) : false;
		return $result;
	}

	return true;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

?>