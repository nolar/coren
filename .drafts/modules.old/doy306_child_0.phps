<?php defined('CORENINPAGE') or die('Hack!');
//todo: в будущем переделать picture_info() на использование конфигурируемого модуля типа imager_0.php

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!core::depend('list_0')) return;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class doy306_child_0_exception_duplicate extends list_0_exception_duplicate {}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class doy306_child_0 extends list_0 implements dbaware
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected $grant_view;
protected $grant_edit;

protected $default_page;
protected $default_size;
protected $default_skip;
protected $default_sorting;
protected $default_reverse;

protected $format_ubb_module;
protected $photo_storage_module;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __construct ($configs)
{
	parent::__construct($configs);

	$this->grant_view = isset($configs['grant_view']) ? $configs['grant_view'] : null;
	$this->grant_edit = isset($configs['grant_edit']) ? $configs['grant_edit'] : null;
	if (!isset($this->grant_view)) throw new exception("Misconfig: grant_view.");
	if (!isset($this->grant_edit)) throw new exception("Misconfig: grant_edit.");

	$this->default_page    = core::find_scalar(array($configs), array('default_page'   ), null);
	$this->default_size    = core::find_scalar(array($configs), array('default_size'   ), null);
	$this->default_skip    = core::find_scalar(array($configs), array('default_skip'   ), null);
	$this->default_sorting = core::find_scalar(array($configs), array('default_sorting'), null);
	$this->default_reverse = core::find_scalar(array($configs), array('default_reverse'), null);

	$this->format_ubb_module    = core::find_scalar(array($configs), array('format_ubb_module'   ), null);
	$this->photo_storage_module = core::find_scalar(array($configs), array('photo_storage_module'), null);
	if (is_null($this->photo_storage_module)) throw new exception("Misconfig: photo_storage_module.");
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_default_entity ()
{
	return 'child';
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_default_fields ($entity, $parent)
{
	switch ($entity)
	{
		case 'child':
			return array(
			'child'			=> null,

			'fname'			=> null,
			'sname'			=> null,
			'tname'			=> null,
			'sex'			=> null,

			'comment'		=> null,
			'birthday'		=> null,
			'parents'		=> null,

			'photo_storage'		=> null,
			'photo_mime'		=> null,
			'photo_name'		=> null,
			'photo_size'		=> null,
			'photo_xsize'		=> null,
			'photo_ysize'		=> null,
			'photo_action'		=> null,
			'photo_attach'		=> null);
			break;
		default:
			return parent::get_default_fields($entity, $parent);
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function guess_item_fake ($entity, $parent, $submit)
{
	if (
	    (!isset($submit['fname'        ]) || ($submit['fname'        ] == '')) &&
	    (!isset($submit['sname'        ]) || ($submit['sname'        ] == '')) &&
	    (!isset($submit['tname'        ]) || ($submit['tname'        ] == '')) &&
	    (!isset($submit['sex'          ]) || ($submit['sex'          ] == '')) &&
	    (!isset($submit['comment'      ]) || ($submit['comment'      ] == '')) &&
	    (!isset($submit['birthday'     ]) || ($submit['birthday'     ] == '')) &&
	    (!isset($submit['parents'      ]) || ($submit['parents'      ] == '')) &&
	    (!isset($submit['photo_action' ]) || ($submit['photo_action' ] == '')) &&
	    (!isset($submit['photo_storage']) || ($submit['photo_storage'] == '')) &&
		true) return true;
	return parent::guess_item_fake($entity, $parent, $submit);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_guessed_itemid ($entity, $parent, $data)
{
	switch ($entity)
	{
		case 'child':
			$result = core::find_scalar(array($data), array('child', 'id'), null);
			break;
		default:
			$result = parent::get_guessed_itemid($entity, $parent, $data);
	}
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_guessed_action ($entity, $parent, $data)
{
	switch ($entity)
	{
		case 'child':
			$itemid =        core::find_scalar(array($data), array('child', 'id'), null) != '';
			$delete = (bool) core::find_scalar(array($data), array('delete'     ), null);
			$fake = $this->guess_item_fake($entity, $parent, $data);
			if (!$itemid && !$delete) $result = $fake ? null : 'append'; else
			if (!$itemid &&  $delete) $result =         null           ; else
			if ( $itemid && !$delete) $result =                'modify'; else
			if ( $itemid &&  $delete) $result =                'remove'; else
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
		$result[] = 'lookup';
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
		$result[] = 'lookup';
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
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_parse_request ($args)
{//returns: array(entity, action, submit, filter, parent, itemid, child);
	$entity = core::find_scalar(array($args, $_GET, $_POST), array('entity'), null);
	$action = core::find_scalar(array($args, $_GET, $_POST), array('action'), null);
	$itemid = core::find_scalar(array($args, $_GET, $_POST), array('id'    ), null);
	$child  = core::find_scalar(array($args, $_GET, $_POST), array('child' ), null);

	$submit = !empty($_POST);//todo: переделать на более достоверный критерий (server[method]==post)

	if (!in_array($entity, array('child'))) $entity = null;
	if (!in_array($action, array('append', 'modify', 'remove', 'list', 'item'))) $action = null;
	if (is_null($action)) $action = isset($itemid) ? 'item' : 'list';

	$filter = array();
	$filter['page'   ] = core::find_scalar(array($args, $_POST, $_GET), array('page'   ), null);
	$filter['size'   ] = core::find_scalar(array($args, $_POST, $_GET), array('size'   ), null);
	$filter['skip'   ] = core::find_scalar(array($args, $_POST, $_GET), array('skip'   ), null);
	$filter['sorting'] = core::find_scalar(array($args, $_POST, $_GET), array('sorting'), null);
	$filter['reverse'] = core::find_scalar(array($args, $_POST, $_GET), array('reverse'), null);

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
		case 'child':
			if (!is_array($filter)) $filter = array();
			$page    = (integer) (isset($filter['page'   ]) ? $filter['page'   ] : $this->default_page   );
			$size    = (integer) (isset($filter['size'   ]) ? $filter['size'   ] : $this->default_size   );
			$skip    = (integer) (isset($filter['skip'   ]) ? $filter['skip'   ] : $this->default_skip   );
			$sorting = (string ) (isset($filter['sorting']) ? $filter['sorting'] : $this->default_sorting);
			$reverse = (string ) (isset($filter['reverse']) ? $filter['reverse'] : $this->default_reverse);

			$count = core::db('select_child_count', compact('parent', 'itemid'));
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
			$meta = compact('count', 'page', 'size', 'skip', 'offset', 'pagemin', 'pagemax', 'sorting', 'reverse');
			$items = core::db('select_child_data', compact('parent', 'itemid', 'page', 'size', 'skip', 'count', 'offset', 'pagemin', 'pagemax', 'sorting', 'reverse'));
			return $items;

		default:
			return parent::do_read_list($entity, $filter, $parent, $itemid, &$meta);
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_handle ($entity, $action, $itemid, $itemold, $submit)
{
	$result = parent::do_handle($entity, $action, $itemid, $itemold, $submit);
	switch ($entity)
	{
		case 'child':
			switch ($photo_action = isset($submit['photo_action']) ? $submit['photo_action'] : null)
			{
				case 1:
					$result['photo_storage'] = null;
					$result['photo_mime'   ] = null;
					$result['photo_name'   ] = null;
					$result['photo_size'   ] = null;
					$result['photo_xsize'  ] = null;
					$result['photo_ysize'  ] = null;
					break;
				case 2:
					$photo_path = $photo_mime = $photo_name = $photo_size = $photo_xsize = $photo_ysize = null;
					if (!$this->__picture_info(isset($submit['photo_attach']) ? $submit['photo_attach'] : null, $photo_path, $photo_mime, $photo_name, $photo_size, $photo_xsize, $photo_ysize))
						break;

					$fileargs = array();
					$fileargs['mime'] = $photo_mime;
					$fileargs['name'] = $photo_name;
					$fileargs['size'] = $photo_size;
					$fileargs['path'] = $photo_path;
					$photo_storage = core::call($this->photo_storage_module, 'upload', $fileargs);

					$result['photo_storage'] = $photo_storage;
					$result['photo_mime'   ] = $photo_mime;
					$result['photo_name'   ] = $photo_name;
					$result['photo_size'   ] = $photo_size;
					$result['photo_xsize'  ] = $photo_xsize;
					$result['photo_ysize'  ] = $photo_ysize;
					break;
			}

			$year   = isset($submit['birthday_year'  ]) && ($submit['birthday_year'  ] != '') ? $submit['birthday_year'  ] : null;
			$month  = isset($submit['birthday_month' ]) && ($submit['birthday_month' ] != '') ? $submit['birthday_month' ] : null;
			$day    = isset($submit['birthday_day'   ]) && ($submit['birthday_day'   ] != '') ? $submit['birthday_day'   ] : null;
			$hour   = isset($submit['birthday_hour'  ]) && ($submit['birthday_hour'  ] != '') ? $submit['birthday_hour'  ] : null;
			$minute = isset($submit['birthday_minute']) && ($submit['birthday_minute'] != '') ? $submit['birthday_minute'] : null;
			$second = isset($submit['birthday_second']) && ($submit['birthday_second'] != '') ? $submit['birthday_second'] : null;
			$isok   = isset($year) || isset($month) || isset($day);
			$result['birthday'] = $isok ? compact('year', 'month', 'day', 'hour', 'minute', 'second') : null;

			break;
	}
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_verify ($entity, $action, $itemid, $itemnew, $itemold)
{
	$result = parent::do_verify($entity, $action, $itemid, $itemnew, $itemold);
	switch ($entity)
	{
		case 'child':
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
		case 'child':
			switch ($action)
			{
				case 'append':
					$newitemid = core::db('insert_child', compact('itemnew'));
					if ($itemnew['photo_storage'] != '') core::call($this->photo_storage_module, 'fixate', array('id'=>$itemnew['photo_storage']));
					break;

				case 'modify':
					core::db('update_child', compact('itemid', 'itemnew')); 
					if ($itemnew['photo_storage'] != $itemold['photo_storage'])
					{
						if ($itemnew['photo_storage'] != '') core::call($this->photo_storage_module, 'fixate', array('id'=>$itemnew['photo_storage']));
						if ($itemold['photo_storage'] != '') core::call($this->photo_storage_module, 'unlink', array('id'=>$itemold['photo_storage']));
					}
					break;

				case 'remove':
					core::db('delete_child', compact('itemid'));
					if ($itemold['photo_storage'] != '') core::call($this->photo_storage_module, 'unlink', array('id'=>$itemold['photo_storage']));
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
		case 'child':
			core::call($this->photo_storage_module, 'apply');
			break;
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_format ($entity, $action, $itemid, $item)
{
	$result = parent::do_format($entity, $action, $itemid, $item);
	switch ($entity)
	{
		case 'child':
//???			if (!in_array($action, array('list', 'massedit'))) .... для скорости можно соптимизировать.
			$result['comment'] = $this->embed_children($entity, $itemid, $result['comment']);
//???			if (!in_array($action, array('list', 'massedit'))) .... и тут для оптимизации по скорости...
			if (isset($this->format_ubb_module)) $result['comment'] = core::call($this->format_ubb_module, 'format', array('text'=>$result['comment']));
			break;
	}
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_args_item_form ($entity, $action, $overaccess, $itemaccess, $filter, $parent, $itemid, $itemold, $itemnew, $itemoldf, $itemnewf, $errors, $children)
{
	$result = parent::get_args_item_form($entity, $action, $overaccess, $itemaccess, $filter, $parent, $itemid, $itemold, $itemnew, $itemoldf, $itemnewf, $errors, $children);
	$result['photo_storage_module'] = $this->photo_storage_module;
	return $result;
}

protected function get_args_list_list ($entity, $action, $overaccess, $filter, $parent, $meta, $lines, $glued, $errors)
{
	$result = parent::get_args_list_list($entity, $action, $overaccess, $filter, $parent, $meta, $lines, $glued, $errors);
	$result['photo_storage_module'] = $this->photo_storage_module;
	return $result;
}

protected function get_args_list_line ($entity, $action, $overaccess, $itemaccess, $filter, $parent, $meta, $pageindex, $listindex, $itemid, $itemold, $itemnew, $itemoldf, $itemnewf, $errors, $children)
{
	$result = parent::get_args_list_line($entity, $action, $overaccess, $itemaccess, $filter, $parent, $meta, $pageindex, $listindex, $itemid, $itemold, $itemnew, $itemoldf, $itemnewf, $errors, $children);
	$result['photo_storage_module'] = $this->photo_storage_module;
	return $result;
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
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

?>