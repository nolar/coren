<?php defined('CORENINPAGE') or die('Hack!');

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!core::depend('list_0')) return;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class gallery_0_exception_duplicate extends list_0_exception_duplicate {}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class gallery_0 extends list_0 implements dbaware
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected $nicon_mode;
protected $picon_mode;
protected $image_storage_module;
protected $nicon_storage_module;
protected $picon_storage_module;
protected $default_page = 1;
protected $default_size = 15;
protected $default_skip = 0;
protected $grant_view;
protected $grant_edit;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __construct ($configs)
{
	parent::__construct($configs);

	$this->image_storage_module = core::find_scalar(array($configs), array('image_storage_module'), null);
	$this->nicon_storage_module = core::find_scalar(array($configs), array('nicon_storage_module'), null);
	$this->picon_storage_module = core::find_scalar(array($configs), array('picon_storage_module'), null);
	if (is_null($this->image_storage_module)) throw new exception("Misconfig: image_storage_module.");
	if (is_null($this->nicon_storage_module)) throw new exception("Misconfig: nicon_storage_module.");
	if (is_null($this->picon_storage_module)) throw new exception("Misconfig: picon_storage_module.");

	$this->nicon_mode = (integer) core::find_scalar(array($configs), array('nicon_mode'), null);
	$this->picon_mode = (integer) core::find_scalar(array($configs), array('picon_mode'), null);
	if (($this->nicon_mode < 1) || ($this->nicon_mode > 3)) throw new exception("Misconfig: unknown nicon_mode.");
	if (($this->picon_mode < 1) || ($this->picon_mode > 3)) throw new exception("Misconfig: unknown picon_mode.");

	$this->grant_view = isset($configs['grant_view']) ? $configs['grant_view'] : null;
	$this->grant_edit = isset($configs['grant_edit']) ? $configs['grant_edit'] : null;
	if (!isset($this->grant_view)) throw new exception("Misconfig: grant_view.");
	if (!isset($this->grant_edit)) throw new exception("Misconfig: grant_edit.");
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_default_entity ()
{
	return 'picture';
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_default_errors ($entity)
{
	$result = parent::get_default_errors($entity);
	switch ($entity)
	{
		case 'picture':
			//!!!
			break;
	}
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_default_fields ($entity, $parent)
{
	switch ($entity)
	{
		case 'picture':
			return array(
			'picture'		=> null,

			'stampset'		=> null,
			'stamp_year'		=> null,
			'stamp_month'		=> null,
			'stamp_day'		=> null,
			'stamp_hour'		=> null,
			'stamp_minute'		=> null,
			'stamp_second'		=> null,
			'caption'		=> null,
			'comment'		=> null,
			'category'		=> null,

			'filetype'		=> null,
			'filename'		=> null,
			'filesize'		=> null,
			'thumb'			=> null,
			'align'			=> null,
			'embed'			=> null,

			'image_storage'		=> null,
			'image_mime'		=> null,
			'image_name'		=> null,
			'image_size'		=> null,
			'image_xsize'		=> null,
			'image_ysize'		=> null,

			'nicon_mode'		=> null,
			'nicon_storage'		=> null,
			'nicon_mime'		=> null,
			'nicon_name'		=> null,
			'nicon_size'		=> null,
			'nicon_xsize'		=> null,
			'nicon_ysize'		=> null,

			'picon_mode'		=> null,
			'picon_storage'		=> null,
			'picon_mime'		=> null,
			'picon_name'		=> null,
			'picon_size'		=> null,
			'picon_xsize'		=> null,
			'picon_ysize'		=> null);
			break;
		default:
			return parent::get_default_fields($entity, $parent);
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_guessed_itemid ($entity, $parent, $data)
{
	switch ($entity)
	{
		case 'picture':
			$result = core::find_scalar(array($data), array('picture', 'id'), null);
			break;
		default:
			$result = parent::get_guessed_itemid($entity, $parent, $data);
	}
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function guess_item_fake ($entity, $parent, $submit)
{
	if ((!isset($submit['caption'      ]) || ($submit['caption'      ] == '')) &&
//???	    (!isset($submit['comment'      ]) || ($submit['comment'      ] == '')) &&
	    (!isset($submit['image_storage']) || ($submit['image_storage'] == '')) &&
	    (!isset($submit['nicon_storage']) || ($submit['nicon_storage'] == '')) &&
	    (!isset($submit['picon_storage']) || ($submit['picon_storage'] == ''))
	   ) return true;
	return parent::guess_item_fake($entity, $parent, $submit);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_guessed_action ($entity, $parent, $data)
{
	switch ($entity)
	{
		case 'picture':
			$itemid =        core::find_scalar(array($data), array('picture', 'id'), null) != '';
			$delete = (bool) core::find_scalar(array($data), array('delete'       ), null);
			if (!$itemid && !$delete) $result = 'append'; else
			if (!$itemid &&  $delete) $result =  null   ; else
			if ( $itemid && !$delete) $result = 'modify'; else
			if ( $itemid &&  $delete) $result = 'remove'; else
			$result = parent::get_guessed_action($entity, $parent, $data);
			break;
		default:
			$result = parent::get_guessed_action($entity, $parent, $data);
	}
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_overaccess ($entity)
{
	$result = parent::get_overaccess($entity);
	if (core::grant($this->grant_view) || core::grant($this->grant_edit))
	{
		$result[] = 'list';
		$result[] = 'item';
		$result[] = 'image';
		$result[] = 'nicon';
		$result[] = 'picon';
	}
	if (core::grant($this->grant_edit))
	{
		$result[] = 'append';
		$result[] = 'modify';
		$result[] = 'remove';
		$result[] = 'massedit';
	}
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_itemaccess ($entity, $itemid, $item)
{
	$result = parent::get_overaccess($entity);
	if (core::grant($this->grant_view) || core::grant($this->grant_edit))
	{
		$result[] = 'list';
		$result[] = 'item';
		$result[] = 'image';
		$result[] = 'nicon';
		$result[] = 'picon';
	}
	if (core::grant($this->grant_edit))
	{
		$result[] = 'append';
		$result[] = 'modify';
		$result[] = 'remove';
		$result[] = 'massedit';
	}
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function is_action_atomic ($entity, $action)
{
	if (in_array($action, array('image', 'nicon', 'picon'))) return true;
	return parent::is_action_atomic($entity, $action);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_parse_request ($args)
{//returns: array(entity, action, submit, filter, parent, itemid, child);
	$entity = null;
	$action = core::find_scalar(array($args, $_GET, $_POST), array('action'     ), null);
	$itemid = core::find_scalar(array($args, $_GET, $_POST), array('id'         ), null);

	if (isset($_GET['child'])) $child = $_GET['child'];
	else $child = null;

	$submit = !empty($_POST);//todo: переделать на более достоверный критерий (server[method]==post)

	if (!in_array($action, array('append', 'modify', 'remove', 'list', 'item', 'image', 'nicon', 'picon'))) $action = null;
	if (is_null($action)) $action = isset($itemid) ? 'item' : 'list';

	$filter = array();
	$filter['page'] = core::find_scalar(array($args, $_GET), array('page'), $this->default_page);
	$filter['size'] = core::find_scalar(array($args, $_GET), array('size'), $this->default_size);
	$filter['skip'] = core::find_scalar(array($args, $_GET), array('skip'), $this->default_skip);
	$filter['category'] = core::find_scalar(array($args, $_GET), array('category'), $this->default_skip);

	return compact('entity', 'action', 'submit', 'filter', 'parent', 'itemid', 'child');
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_build ($entity, $files, $post, $get)
{
	$result = parent::do_build($entity, $files, $post, $get);
	foreach ($get   as $field => $value) $result[$field] = $value;
	foreach ($post  as $field => $value) $result[$field] = $value;
	foreach ($files as $field => $value) $result[$field] = $value;
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_read_list ($entity, $filter, $parent, $itemid, &$meta)
{
	switch ($entity)
	{
		case 'picture':
			if (!is_array($filter)) $filter = array();
			$page = isset($filter['page']) ? $filter['page'] : null;
			$size = isset($filter['size']) ? $filter['size'] : null;
			$skip = isset($filter['skip']) ? $filter['skip'] : null;
			$category = isset($filter['category']) ? $filter['category'] : null;

			$count = core::db('select_pictures_count', compact('parent', 'itemid', 'category'));
			if ($skip > $count) $skip = $count;
			if ($size > 0)
			{
				$pagemin = 1; $pagemax = max(1, floor(($count - $skip) / $size) + (($count - $skip) % $size ? 1 : 0));
				if ($page > $pagemax) $page = $pagemax;
				if ($page < $pagemin) $page = $pagemin;
				$offset = ($page - 1) * $size + $skip;
			} else
			{
				$pagemin = 1; $pagemax = 1;
				$page = 1;
				$size = $count - $skip;
				$offset = $skip;
			}
			$meta = compact('count', 'page', 'size', 'skip', 'offset', 'pagemin', 'pagemax');
			$items = core::db('select_pictures_data', compact('parent', 'itemid', 'category', 'page', 'size', 'skip', 'count', 'offset'));
			return $items;
		default:
			return parent::do_read_list($entity, $filter, $parent, $itemid, &$meta);
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_handle ($entity, $action, $itemid, $itemold, $submit)
{
	$result = parent::do_handle($entity, $action, $itemid, $itemold, $submit);
	do {
		$image_path  = null;
		$image_mime  = null;
		$image_name  = null;
		$image_size  = null;
		$image_xsize = null;
		$image_ysize = null;
		if (!$this->__picture_info(isset($submit['image']) ? $submit['image'] : null,
			$image_path, $image_mime, $image_name, $image_size, $image_xsize, $image_ysize))
				break;

		$fileargs = array();
		$fileargs['mime'] = $image_mime;
		$fileargs['name'] = $image_name;
		$fileargs['size'] = $image_size;
		$fileargs['path'] = $image_path;
		$image_storage = core::call($this->image_storage_module, 'upload', $fileargs);

		$result['image_storage'] = $image_storage;
		$result['image_mime'   ] = $image_mime;
		$result['image_name'   ] = $image_name;
		$result['image_size'   ] = $image_size;
		$result['image_xsize'  ] = $image_xsize;
		$result['image_ysize'  ] = $image_ysize;
	} while (false);

	$mode = isset($submit ['nicon_mode']) && ($submit ['nicon_mode'] != 0) ? $submit ['nicon_mode'] : (
		isset($itemold['nicon_mode']) && ($itemold['nicon_mode'] != 0) ? $itemold['nicon_mode'] : (
		$this->nicon_mode));
	switch ($mode)
	{
		case 1:/* = thumb is force to autogenerate */
			if (isset($image_path))
			do {
			$nicon_path  = null;
			$nicon_mime  = null;
			$nicon_name  = null;
			$nicon_size  = null;
			$nicon_xsize = null;
			$nicon_ysize = null;
			if (!$this->__picture_nicon($image_path, $image_name, 100, 100, 1.0, /* config or submit want size? !!!*/
				$nicon_path, $nicon_mime, $nicon_name, $nicon_size, $nicon_xsize, $nicon_ysize))
					break;

			$fileargs = array();
			$fileargs['mime'] = $nicon_mime;
			$fileargs['name'] = $nicon_name;
			$fileargs['size'] = $nicon_size;
			$fileargs['path'] = $nicon_path;
			$nicon_storage = core::call($this->nicon_storage_module, 'upload', $fileargs);

			$result['nicon_storage'] = $nicon_storage;
			$result['nicon_mime'   ] = $nicon_mime;
			$result['nicon_name'   ] = $nicon_name;
			$result['nicon_size'   ] = $nicon_size;
			$result['nicon_xsize'  ] = $nicon_xsize;
			$result['nicon_ysize'  ] = $nicon_ysize;
			} while (false);
			break;
		case 2:/* = thumb is separate file */
			do {
			$nicon_path  = null;
			$nicon_mime  = null;
			$nicon_name  = null;
			$nicon_size  = null;
			$nicon_xsize = null;
			$nicon_ysize = null;
			if (!$this->__picture_info(isset($submit['nicon']) ? $submit['nicon'] : null,
				$nicon_path, $nicon_mime, $nicon_name, $nicon_size, $nicon_xsize, $nicon_ysize))
					break;

			$fileargs = array();
			$fileargs['mime'] = $nicon_mime;
			$fileargs['name'] = $nicon_name;
			$fileargs['size'] = $nicon_size;
			$fileargs['path'] = $nicon_path;
			$nicon_storage = core::call($this->nicon_storage_module, 'upload', $fileargs);

			$result['nicon_storage'] = $nicon_storage;
			$result['nicon_mime'   ] = $nicon_mime;
			$result['nicon_name'   ] = $nicon_name;
			$result['nicon_size'   ] = $nicon_size;
			$result['nicon_xsize'  ] = $nicon_xsize;
			$result['nicon_ysize'  ] = $nicon_ysize;
			} while (false);
			break;
		case 3:/* = thumb is file if uploaded with or after image, or it is generated on new image upload */
			//todo: all here. use uploaded thumb if it is uploaded together or after image, or generate new thumb on image upload
			break;
		default:
			// неизвестный режим превьюшки. ничего не делать. чтобы ничего не испортить.
	}

	$mode = isset($submit ['picon_mode']) && ($submit ['picon_mode'] != 0) ? $submit ['picon_mode'] : (
		isset($itemold['picon_mode']) && ($itemold['picon_mode'] != 0) ? $itemold['picon_mode'] : (
		$this->picon_mode));
	switch ($mode)
	{
		case 1:/* = thumb is force to autogenerate */
			if (isset($nicon_path))
			do {
			$picon_path  = null;
			$picon_mime  = null;
			$picon_name  = null;
			$picon_size  = null;
			$picon_xsize = null;
			$picon_ysize = null;
			if (!$this->__picture_picon($nicon_path, $nicon_name, 33, /* shading parameters? !!!*/
				$picon_path, $picon_mime, $picon_name, $picon_size, $picon_xsize, $picon_ysize))
					break;

			$fileargs = array();
			$fileargs['mime'] = $picon_mime;
			$fileargs['name'] = $picon_name;
			$fileargs['size'] = $picon_size;
			$fileargs['path'] = $picon_path;
			$picon_storage = core::call($this->picon_storage_module, 'upload', $fileargs);

			$result['picon_storage'] = $picon_storage;
			$result['picon_mime'   ] = $picon_mime;
			$result['picon_name'   ] = $picon_name;
			$result['picon_size'   ] = $picon_size;
			$result['picon_xsize'  ] = $picon_xsize;
			$result['picon_ysize'  ] = $picon_ysize;
			} while (false);
			break;
		case 2:/* = thumb is separate file */
			do {
			$picon_path  = null;
			$picon_mime  = null;
			$picon_name  = null;
			$picon_size  = null;
			$picon_xsize = null;
			$picon_ysize = null;
			if (!$this->__picture_info(isset($submit['picon']) ? $submit['picon'] : null,
				$picon_path, $picon_mime, $picon_name, $picon_size, $picon_xsize, $picon_ysize))
					break;

			$fileargs = array();
			$fileargs['mime'] = $picon_mime;
			$fileargs['name'] = $picon_name;
			$fileargs['size'] = $picon_size;
			$fileargs['path'] = $picon_path;
			$picon_storage = core::call($this->picon_storage_module, 'upload', $fileargs);

			$result['picon_storage'] = $picon_storage;
			$result['picon_mime'   ] = $picon_mime;
			$result['picon_name'   ] = $picon_name;
			$result['picon_size'   ] = $picon_size;
			$result['picon_xsize'  ] = $picon_xsize;
			$result['picon_ysize'  ] = $picon_ysize;
			} while (false);
			break;
		case 3:/* = thumb is file if uploaded with or after image, or it is generated on new image upload */
			//todo: all here. use uploaded thumb if it is uploaded together or after image, or generate new thumb on image upload
			break;
		default:
			// неизвестный режим превьюшки. ничего не делать. чтобы ничего не испортить.
	}

	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_verify ($entity, $action, $itemid, $itemnew, $itemold)
{
	$result = parent::do_verify($entity, $action, $itemid, $itemnew, $itemold);
	switch ($entity)
	{
		case 'picture':
			//todo: check if we can revoke item from current parent, and if we can inject it into new parent (by grants?)
			break;
	}
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_action ($entity, $action, $itemid, $itemnew, $itemold, &$newitemid)
{
	switch ($entity)
	{
		case 'picture':
			switch ($action)
			{
				case 'append':
					$newitemid = core::db('insert_picture', compact('itemnew'));
					if ($itemnew['image_storage'] != '') core::call($this->image_storage_module, 'fixate', array('id'=>$itemnew['image_storage']));
					if ($itemnew['nicon_storage'] != '') core::call($this->nicon_storage_module, 'fixate', array('id'=>$itemnew['nicon_storage']));
					if ($itemnew['picon_storage'] != '') core::call($this->picon_storage_module, 'fixate', array('id'=>$itemnew['picon_storage']));
					break;

				case 'modify':
					core::db('update_picture', compact('itemid', 'itemnew')); 
					if ($itemnew['image_storage'] != $itemold['image_storage'])
					{
						if ($itemnew['image_storage'] != '') core::call($this->image_storage_module, 'fixate', array('id'=>$itemnew['image_storage']));
						if ($itemold['image_storage'] != '') core::call($this->image_storage_module, 'unlink', array('id'=>$itemold['image_storage']));
					}
					if ($itemnew['nicon_storage'] != $itemold['nicon_storage'])
					{
						if ($itemnew['nicon_storage'] != '') core::call($this->nicon_storage_module, 'fixate', array('id'=>$itemnew['nicon_storage']));
						if ($itemold['nicon_storage'] != '') core::call($this->nicon_storage_module, 'unlink', array('id'=>$itemold['nicon_storage']));
					}
					if ($itemnew['picon_storage'] != $itemold['picon_storage'])
					{
						if ($itemnew['picon_storage'] != '') core::call($this->picon_storage_module, 'fixate', array('id'=>$itemnew['picon_storage']));
						if ($itemold['picon_storage'] != '') core::call($this->picon_storage_module, 'unlink', array('id'=>$itemold['picon_storage']));
					}
					break;

				case 'remove':
					core::db('delete_picture', compact('itemid'));
					if ($itemold['image_storage'] != '') core::call($this->image_storage_module, 'unlink', array('id'=>$itemold['image_storage']));
					if ($itemold['nicon_storage'] != '') core::call($this->nicon_storage_module, 'unlink', array('id'=>$itemold['nicon_storage']));
					if ($itemold['picon_storage'] != '') core::call($this->picon_storage_module, 'unlink', array('id'=>$itemold['picon_storage']));
					break;

				default:
					return parent::do_action($entity, $action, $itemid, $itemnew, $itemold, $newitemid);
			}
			break;

		default:
			return parent::do_action($entity, $action, $itemid, $itemnew, $itemold, $newitemid);
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_apply ($entity)
{
	parent::do_apply($entity);
	switch ($entity)
	{
		case 'picture':
			core::call($this->image_storage_module, 'apply');
			core::call($this->nicon_storage_module, 'apply');
			core::call($this->picon_storage_module, 'apply');
			break;
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_show_form ($entity, $action, $overaccess, $itemaccess, $filter, $parent, $itemid, $itemold, $itemnew, $itemoldf, $itemnewf, $children)
{
	if (($entity == 'picture') and ($action == 'image'))
	{
		//!!! todo here
		if (isset($itemold['image_storage']))
		{
			$fileargs = array();
			$fileargs['id'] = $itemold['image_storage'];
			$uri = core::call($this->image_storage_module, 'fromroot', $fileargs);
			if ($uri != '')
			{
				header("Location: {$uri}");
				return;//?? phrase: you was redirected to....
			}
		}
		//!!! сказать что картинка не была залита, сгенериррована, или вообще не сохранилась.
		//!!!. или вывести картинку дефолтную (призрака заглушку).

		//!!! А на самом деле это не наша забота редиректить. Это должен модуль хранилища сделать или passthrough, или редирект.

		core::shutup();
	} else
	if (($entity == 'picture') and ($action == 'nicon'))
	{
		//!!! todo here
		if (isset($itemold['nicon_storage']))
		{
			$fileargs = array();
			$fileargs['id'] = $itemold['nicon_storage'];
			$uri = core::call($this->nicon_storage_module, 'fromroot', $fileargs);
			if ($uri != '')
			{
				header("Location: {$uri}");
				return;//?? phrase: you was redirected to....
			}
		}
		//!!! сказать что картинка не была залита, сгенериррована, или вообще не сохранилась.
		//!!!. или вывести картинку дефолтную (призрака заглушку).

		core::shutup();
	} else
	if (($entity == 'picture') and ($action == 'picon'))
	{
		//!!! todo here
		if (isset($itemold['picon_storage']))
		{
			$fileargs = array();
			$fileargs['id'] = $itemold['picon_storage'];
			$uri = core::call($this->picon_storage_module, 'fromroot', $fileargs);
			if ($uri != '')
			{
				header("Location: {$uri}");
				return;//?? phrase: you was redirected to....
			}
		}
		//!!! сказать что картинка не была залита, сгенериррована, или вообще не сохранилась.
		//!!!. или вывести картинку дефолтную (призрака заглушку).

		core::shutup();
	} else
	return parent::do_show_form($entity, $action, $overaccess, $itemaccess, $filter, $parent, $itemid, $itemold, $itemnew, $itemoldf, $itemnewf, $children);;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function __picture_info ($file, &$filepath, &$filetype, &$filename, &$filesize, &$xsize, &$ysize)
{
	if (!is_array($file)) return false;
	$path = isset($file['tmp_name']) ? $file['tmp_name'] : null;
	if ($path == '') return false;
	if (!is_file($path)) return false;

	$filepath = $path;
	$filetype = isset($file['type']) ? $file['type'] : null;
	$filename = isset($file['name']) ? $file['name'] : null;
	$filesize = isset($file['size']) ? $file['size'] : null;

	if ($filename == '') $filename = basename($filepath);
	if ($filesize == '') $filesize = filesize($filepath);

	$info = getimagesize($path);
	if ($info !== false)
	{
		$xsize = $info[0];
		$ysize = $info[1];
		if ($filetype == '') $filetype = image_type_to_mime_type($info[2]);
	} else
	{
		$xsize = 0;
		$ysize = 0;
	}

	return true;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function __picture_nicon ($orig_path, $name, $wantx, $wanty, $wantr, &$filepath, &$filetype, &$filename, &$filesize, &$xsize, &$ysize)
{
	$info = getimagesize($orig_path);
	if ($info !== false)
	{
		$path = $orig_path . ".nicon";

		$orig_xsize  = $info[0];
		$orig_ysize  = $info[1];
		$orig_format = $info[2];

		if (!isset($wantx) && !isset($wanty))
		{
			$ratiox = isset($wantr) && ($wantr > 0.0) && ($wantr < 1.0) ? $wantr : 1.0;
			$ratioy = isset($wantr) && ($wantr > 0.0) && ($wantr < 1.0) ? $wantr : 1.0;
		} else
		if (!isset($wantx))
		{
			$ratioy = 1.0 * $wanty / $orig_ysize;
			$ratiox = $ratioy;
		} else
		if (!isset($wanty))
		{
			$ratiox = 1.0 * $wantx / $orig_xsize;
			$ratioy = $ratiox;
		} else
		{
			$ratiox = 1.0 * $wantx / $orig_xsize;
			$ratioy = 1.0 * $wanty / $orig_ysize;
		}

		$wantx = round($orig_xsize * $ratiox);
		$wanty = round($orig_ysize * $ratioy);


		switch ($orig_format)
		{
			case IMAGETYPE_GIF : $source = imagecreatefromgif ($orig_path); break;
			case IMAGETYPE_JPEG: $source = imagecreatefromjpeg($orig_path); break;
			case IMAGETYPE_PNG : $source = imagecreatefrompng ($orig_path); break;
			default: return false;
		}
		$target = imagecreatetruecolor($wantx, $wanty);
		imagecopyresampled($target, $source, 0, 0, 0, 0, $wantx, $wanty, $orig_xsize, $orig_ysize);

		switch ($orig_format)
		{
			case IMAGETYPE_GIF : imagegif ($target, $path); break;
			case IMAGETYPE_JPEG: imagejpeg($target, $path); break;
			case IMAGETYPE_PNG : imagepng ($target, $path); break;
			default: return false;
		}
		imagedestroy($source);
		imagedestroy($target);

		return $this->__picture_info(array('tmp_name' => $path, 'name' => $name),
			$filepath, $filetype, $filename, $filesize, $xsize, $ysize);
	} else
	{
		return false;
	}

	return true;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function __picture_picon ($orig_path, $name, $power, &$filepath, &$filetype, &$filename, &$filesize, &$xsize, &$ysize)
{
	$info = getimagesize($orig_path);
	if ($info !== false)
	{
		$path = $orig_path . ".picon";

		$orig_xsize  = $info[0];
		$orig_ysize  = $info[1];
		$orig_format = $info[2];

		switch ($orig_format)
		{
			case IMAGETYPE_GIF : $source = imagecreatefromgif ($orig_path); break;
			case IMAGETYPE_JPEG: $source = imagecreatefromjpeg($orig_path); break;
			case IMAGETYPE_PNG : $source = imagecreatefrompng ($orig_path); break;
			default: return false;
		}

		$target = imagecreatetruecolor($orig_xsize, $orig_ysize);
		imagecopy($target, $source, 0, 0, 0, 0, $orig_xsize, $orig_ysize);
		$color = imagecolorallocatealpha($target, 255,255,255, round(127*(1.0*$power/100)));
		imagefilledrectangle($target, 0, 0, $orig_xsize, $orig_ysize, $color);

		switch ($orig_format)
		{
			case IMAGETYPE_GIF : imagegif ($target, $path); break;
			case IMAGETYPE_JPEG: imagejpeg($target, $path); break;
			case IMAGETYPE_PNG : imagepng ($target, $path); break;
			default: return false;
		}
		imagedestroy($source);
		imagedestroy($target);

		return $this->__picture_info(array('tmp_name' => $path, 'name' => $name),
			$filepath, $filetype, $filename, $filesize, $xsize, $ysize);
	} else
	{
		return false;
	}

	return true;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

?>