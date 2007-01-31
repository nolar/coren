<?php defined('CORENINPAGE') or die('Hack!');

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!core::depend('list_0')) return;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class files_0_exception_duplicate extends list_0_exception_duplicate {}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class files_0 extends list_0 implements dbaware
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

protected $file_storage_module;
protected $format_ubb_module;

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

	$this->format_ubb_module = core::find_scalar(array($configs), array('format_ubb_module'), null);
	$this->file_storage_module = isset($configs['file_storage_module']) ? $configs['file_storage_module'] : null;
	if (is_null($this->file_storage_module)) throw new exception("Misconfig: file_storage_module.");
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_default_entity ()
{
	return 'file';
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_default_fields ($entity, $parent)
{
	switch ($entity)
	{
		case 'file':
			return array(
			'file'			=> null,
			'parent'		=> $parent,
			'order'			=> null,
			'caption'		=> null,
			'comment'		=> null,
			'file_storage'		=> null,
			'file_mime'		=> null,
			'file_name'		=> null,
			'file_size'		=> null,
			'file_action'		=> null,
			'file_attach'		=> null);
			break;
		default:
			return parent::get_default_fields($entity, $parent);
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function guess_item_fake ($entity, $parent, $submit)
{
	if (
	    (!isset($submit['order'  ]) || ($submit['order'  ] == '')) &&
	    (!isset($submit['caption']) || ($submit['caption'] == '')) &&
	    (!isset($submit['comment']) || ($submit['comment'] == '')) &&
	    (!isset($submit['file_storage']) || ($submit['file_storage'] == '')) &&//??? провер€ть только на непустость, или еще спросить у хранилища про наличие такого ид?
	    //??? довольно-таки спорна€ проверка. а что если выбрано action=оставить без картинки, но файл подгружен. нужно ли добавл€ть такую ѕ”—“”ё запись?
	    (!isset($submit['file_attach' ]) || !is_array($submit['file_attach']) || !isset($submit['file_attach']['tmp_name']) || !is_uploaded_file($submit['file_attach']['tmp_name'])) &&
		true) return true;
	return parent::guess_item_fake($entity, $parent, $submit);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_guessed_itemid ($entity, $parent, $data)
{
	switch ($entity)
	{
		case 'file':
			$result = core::find_scalar(array($data), array('file'), null);
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
		case 'file':
			$itemid =        core::find_scalar(array($data), array('file'    ), null) != '';
			$delete = (bool) core::find_scalar(array($data), array('delete'  ), null);
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
	switch ($entity)
	{
		case 'file':
			//!!!
			break;
	}
	if (core::grant($this->grant_view) || core::grant($this->grant_edit))
	{
		$result[] = 'list';
		$result[] = 'item';
		$result[] = 'file';
		$result[] = 'embed';
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
	switch ($entity)
	{
		case 'file':
			//!!!
			break;
	}
	if (core::grant($this->grant_view) || core::grant($this->grant_edit))
	{
		$result[] = 'list';
		$result[] = 'item';
		$result[] = 'file';
		$result[] = 'embed';
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
	if (in_array($action, array('file'))) return true;
	return parent::is_action_atomic($entity, $action);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_parse_request ($args)
{//returns: array(entity, action, submit, filter, parent, itemid, child);
	$entity = null;
	$parent = core::find_scalar(array($args               ), array('parent'         ), null);
	$action = core::find_scalar(array($args, $_GET, $_POST), array('fileaction'     ), null);
	$itemid = core::find_scalar(array($args, $_GET, $_POST), array('fileid'         ), null);

	//!!! todo: сделать все префиксы конфигурируемым (FILEchild, FILEid, FILEaction...)
	if (isset($_GET['filechild'])) $child = $_GET['filechild'];
	else $child = null;

	$submit = !empty($_POST);//todo: переделать на более достоверный критерий (server[method]==post)

	if (!in_array($entity, array('file'))) $entity = null;
	if (!in_array($action, array('append', 'modify', 'remove', 'list', 'item', 'file'))) $action = null;
	if (is_null($action)) $action = isset($itemid) ? 'item' : 'list';

	$filter = array();
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
		case 'file':
			if (!is_array($filter)) $filter = array();
			$page    = (integer) (isset($filter['page'   ]) ? $filter['page'   ] : $this->default_page   );
			$size    = (integer) (isset($filter['size'   ]) ? $filter['size'   ] : $this->default_size   );
			$skip    = (integer) (isset($filter['skip'   ]) ? $filter['skip'   ] : $this->default_skip   );
			$sorting = (string ) (isset($filter['sorting']) ? $filter['sorting'] : $this->default_sorting);
			$reverse = (string ) (isset($filter['reverse']) ? $filter['reverse'] : $this->default_reverse);

			$count = core::db('select_files_count', compact('parent', 'itemid'));
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
			$items = core::db('select_files_data', compact('parent', 'itemid', 'page', 'size', 'skip', 'count', 'offset', 'pagemin', 'pagemax', 'sorting', 'reverse'));
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
		case 'file':
			switch ($file_action = isset($submit['file_action']) ? $submit['file_action'] : 2)
			{
				case 0: /* = leave everything untouched */
					break;

				case 1:/* = delete file */
					$result['file_storage'] = null;
					$result['file_mime'   ] = null;
					$result['file_name'   ] = null;
					$result['file_size'   ] = null;
					break;

				case 2:/* = use just uploaded file (if it is), or leave untouched (if nothing uploaded) */
					if (!isset($submit['file_attach']) || !is_array($submit['file_attach']) || !isset($submit['file_attach']['tmp_name']) || !is_uploaded_file($submit['file_attach']['tmp_name']))
						break;

					$storageargs = array();
					$storageargs['mime'] = $submit['file_attach']['type'    ];
					$storageargs['name'] = $submit['file_attach']['name'    ];
					$storageargs['size'] = $submit['file_attach']['size'    ];
					$storageargs['path'] = $submit['file_attach']['tmp_name'];
					$storage = core::call($this->file_storage_module, 'upload', $storageargs);

					//todo: delete original file here (i.e. $submit['file_attach']['tmp_name']).

					$result['file_storage'] = $storage;
					$result['file_mime'   ] = $submit['file_attach']['type'];
					$result['file_name'   ] = $submit['file_attach']['name'];
					$result['file_size'   ] = $submit['file_attach']['size'];

					break;
			}
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
		case 'file':
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
		case 'file':
			switch ($action)
			{
				case 'append':
					$newitemid = core::db('insert_file', compact('itemnew'));
					if ($itemnew['file_storage'] != '') core::call($this->file_storage_module, 'fixate', array('id'=>$itemnew['file_storage']));
					break;

				case 'modify':
					core::db('update_file', compact('itemid', 'itemnew')); 
					if ($itemnew['file_storage'] != $itemold['file_storage'])
					{
						if ($itemnew['file_storage'] != '') core::call($this->file_storage_module, 'fixate', array('id'=>$itemnew['file_storage']));
						if ($itemold['file_storage'] != '') core::call($this->file_storage_module, 'unlink', array('id'=>$itemold['file_storage']));
					}
					break;

				case 'remove':
					core::db('delete_file', compact('itemid'));
					if ($itemold['file_storage'] != '') core::call($this->file_storage_module, 'unlink', array('id'=>$itemold['file_storage']));
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
		case 'file':
			core::call($this->file_storage_module, 'apply');
			break;
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_format ($entity, $action, $itemid, $item)
{
	$result = parent::do_format($entity, $action, $itemid, $item);
	switch ($entity)
	{
		case 'file':
//???			if (!in_array($action, array('list', 'massedit'))) .... дл€ скорости можно соптимизировать.
			$result['caption'] = $this->embed_children($entity, $itemid, $result['caption']);
			$result['comment'] = $this->embed_children($entity, $itemid, $result['comment']);
//???			if (!in_array($action, array('list', 'massedit'))) .... и тут дл€ оптимизации по скорости...
			if (isset($this->format_ubb_module)) $result['caption'] = core::call($this->format_ubb_module, 'format', array('text'=>$result['caption']));
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
	$result['file_storage_module'] = $this->file_storage_module;
	return $result;
}

protected function get_args_list_list ($entity, $action, $overaccess, $filter, $parent, $meta, $lines, $glued, $errors)
{
	$result = parent::get_args_list_list($entity, $action, $overaccess, $filter, $parent, $meta, $lines, $glued, $errors);
	$result['file_storage_module'] = $this->file_storage_module;
	return $result;
}

protected function get_args_list_line ($entity, $action, $overaccess, $itemaccess, $filter, $parent, $meta, $pageindex, $listindex, $itemid, $itemold, $itemnew, $itemoldf, $itemnewf, $errors, $children)
{
	$result = parent::get_args_list_line($entity, $action, $overaccess, $itemaccess, $filter, $parent, $meta, $pageindex, $listindex, $itemid, $itemold, $itemnew, $itemoldf, $itemnewf, $errors, $children);
	$result['file_storage_module'] = $this->file_storage_module;
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*
protected function do_show_form ($entity, $action, $overaccess, $itemaccess, $filter, $parent, $itemid, $itemold, $itemnew, $itemoldf, $itemnewf, $errors, $children)
{
	if (($entity == 'file') and ($action == 'file'))
	{
		if (isset($itemold['file_storage']))
		{
			$uri = core::call($this->file_storage_module, 'uri', array('id'=>$itemold['file_storage']));
			if (!is_null($uri))
			{
				header("Location: {$uri}");
				return;//??? template phrase: you was redirected to....
			}
		} else
		{
			//!!! сказать что файл не была залит, сгенериррован, или вообще не сохранился.
			//!!!. или вывести картинку дефолтную (призрака заглушку).
		}
	} else
	return parent::do_show_form($entity, $action, $overaccess, $itemaccess, $filter, $parent, $itemid, $itemold, $itemnew, $itemoldf, $itemnewf, $errors, $children);;
}
*/
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

?>