<?php defined('CORENINPAGE') or die('Hack!');

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!core::depend('list_0')) return;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class picts_0_exception_duplicate extends list_0_exception_duplicate {}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class picts_0 extends list_0 implements dbaware
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

protected $imager_module;
protected $image_storage_module;
protected $thumb_storage_module;
protected $thumb_mode;

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

	$this->imager_module        = isset($configs['imager_module'       ]) ? $configs['imager_module'       ] : null;
	$this->image_storage_module = isset($configs['image_storage_module']) ? $configs['image_storage_module'] : null;
	$this->thumb_storage_module = isset($configs['thumb_storage_module']) ? $configs['thumb_storage_module'] : null;
	if (is_null($this->imager_module       )) throw new exception("Misconfig: imager_module."       );
	if (is_null($this->image_storage_module)) throw new exception("Misconfig: image_storage_module.");
	if (is_null($this->thumb_storage_module)) throw new exception("Misconfig: thumb_storage_module.");

	$this->thumb_mode = isset($configs['thumb_mode']) ? $configs['thumb_mode'] : null;
	$this->thumb_mode = (integer) $this->thumb_mode;
	if (($this->thumb_mode < 1) || ($this->thumb_mode > 3)) throw new exception("Misconfig: unknown thumb_mode.");

	$this->format_ubb_module = core::find_scalar(array($configs), array('format_ubb_module'), null);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_default_entity ()
{
	return 'picture';
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_default_fields ($entity, $parent)
{
	switch ($entity)
	{
		case 'picture':
			return array(
			'picture'		=> null,
			'parent'		=> $parent,
			'order'			=> null,
			'caption'		=> null,
			'comment'		=> null,
			'align'			=> null,
			'embed'			=> null,
			'image_storage'		=> null,
			'image_mime'		=> null,
			'image_name'		=> null,
			'image_size'		=> null,
			'image_xsize'		=> null,
			'image_ysize'		=> null,
			'image_action'		=> null,
			'image_attach'		=> null,
			'thumb_mode'		=> null,
			'thumb_storage'		=> null,
			'thumb_mime'		=> null,
			'thumb_name'		=> null,
			'thumb_size'		=> null,
			'thumb_xsize'		=> null,
			'thumb_ysize'		=> null,
			'thumb_action'		=> null,
			'thumb_attach'		=> null);
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
	    (!isset($submit['image_storage']) || ($submit['image_storage'] == '')) &&//??? проверять только на непустость, или еще спросить у хранилища про наличие такого ид?
	    (!isset($submit['thumb_storage']) || ($submit['thumb_storage'] == '')) &&//??? проверять только на непустость, или еще спросить у хранилища про наличие такого ид?
	    //??? довольно-таки спорная проверка. а что если выбрано action=оставить без картинки, но файл подгружен. нужно ли добавлять такую ПУСТУЮ запись?
	    (!isset($submit['image_attach' ]) || !is_array($submit['image_attach']) || !isset($submit['image_attach']['tmp_name']) || !is_uploaded_file($submit['image_attach']['tmp_name'])) &&
	    (!isset($submit['thumb_attach' ]) || !is_array($submit['thumb_attach']) || !isset($submit['thumb_attach']['tmp_name']) || !is_uploaded_file($submit['thumb_attach']['tmp_name'])) &&
		true) return true;
	return parent::guess_item_fake($entity, $parent, $submit);
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

protected function get_guessed_action ($entity, $parent, $data)
{
	switch ($entity)
	{
		case 'picture':
			$itemid =        core::find_scalar(array($data), array('picture', 'id'), null) != '';
			$delete = (bool) core::find_scalar(array($data), array('delete'       ), null);
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
		case 'picture':
			//!!!
			break;
	}
	if (core::grant($this->grant_view) || core::grant($this->grant_edit))
	{
		$result[] = 'list';
		$result[] = 'item';
		$result[] = 'thumb';
		$result[] = 'image';
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
		case 'picture':
			//!!!
			break;
	}
	if (core::grant($this->grant_view) || core::grant($this->grant_edit))
	{
		$result[] = 'list';
		$result[] = 'item';
		$result[] = 'thumb';
		$result[] = 'image';
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
	if (in_array($action, array('thumb', 'image'))) return true;
	return parent::is_action_atomic($entity, $action);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_parse_request ($args)
{//returns: array(entity, action, submit, filter, parent, itemid, child);
	$entity = null;
	$parent = core::find_scalar(array($args               ), array('parent'         ), null);
	$action = core::find_scalar(array($args, $_GET, $_POST), array('pictaction'     ), null);
	$itemid = core::find_scalar(array($args, $_GET, $_POST), array('pictid'         ), null);

	//!!! todo: сделать все префиксы конфигурируемым (PICTchild, PICTid, PICTaction...)
	if (isset($_GET['pictchild'])) $child = $_GET['pictchild'];
	else $child = null;

	$submit = !empty($_POST);//todo: переделать на более достоверный критерий (server[method]==post)

	if (!in_array($entity, array('picture'))) $entity = null;
	if (!in_array($action, array('append', 'modify', 'remove', 'list', 'item', 'thumb', 'image'))) $action = null;
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
		case 'picture':
			if (!is_array($filter)) $filter = array();
			$page    = (integer) (isset($filter['page'   ]) ? $filter['page'   ] : $this->default_page   );
			$size    = (integer) (isset($filter['size'   ]) ? $filter['size'   ] : $this->default_size   );
			$skip    = (integer) (isset($filter['skip'   ]) ? $filter['skip'   ] : $this->default_skip   );
			$sorting = (string ) (isset($filter['sorting']) ? $filter['sorting'] : $this->default_sorting);
			$reverse = (string ) (isset($filter['reverse']) ? $filter['reverse'] : $this->default_reverse);

			$count = core::db('select_pictures_count', compact('parent', 'itemid'));
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
			$items = core::db('select_pictures_data', compact('parent', 'itemid', 'page', 'size', 'skip', 'count', 'offset', 'pagemin', 'pagemax', 'sorting', 'reverse'));
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
		case 'picture':
			switch ($image_action = isset($submit['image_action']) ? $submit['image_action'] : null)
			{
				case 0: /* = leave everything untouched */
					break;

				case 1:/* = delete image */
					$result['image_storage'] = null;
					$result['image_mime'   ] = null;
					$result['image_name'   ] = null;
					$result['image_size'   ] = null;
					$result['image_xsize'  ] = null;
					$result['image_ysize'  ] = null;
					break;

				case 2:/* = use just uploaded file (if it is), or leave untouched (if nothing uploaded) */
					if (!isset($submit['image_attach']) || !is_array($submit['image_attach']) || !isset($submit['image_attach']['tmp_name']) || !is_uploaded_file($submit['image_attach']['tmp_name']))
						break;

					$imagerargs = array();
					$imagerargs['path'      ] = $submit['image_attach']['tmp_name'];
					$imagerargs['force_mime'] = $submit['image_attach']['type'    ];
					$imagerargs['force_name'] = $submit['image_attach']['name'    ];
//					$imagerargs['force_size'] = $submit['image_attach']['size'    ];
					$imagerresult = core::call($this->imager_module, 'info', $imagerargs);
					if ($imagerresult === false) throw new exception("Can not retrieve info about image file.");

					$storageargs = array();
					$storageargs['mime'] = $imagerresult['mime'];
					$storageargs['name'] = $imagerresult['name'];
					$storageargs['size'] = $imagerresult['size'];
					$storageargs['path'] = $imagerresult['path'];
					$storage = core::call($this->image_storage_module, 'upload', $storageargs);

					//todo: delete original file here (i.e. $submit['image_attach']['tmp_name']).

					$result['image_storage'] = $storage;
					$result['image_mime'   ] = $imagerresult['mime' ];
					$result['image_name'   ] = $imagerresult['name' ];
					$result['image_size'   ] = $imagerresult['size' ];
					$result['image_xsize'  ] = $imagerresult['xsize'];
					$result['image_ysize'  ] = $imagerresult['ysize'];
					break;
			}

			$mode = isset($submit ['thumb_mode']) && ($submit ['thumb_mode'] != 0) ? $submit ['thumb_mode'] : (
				isset($itemold['thumb_mode']) && ($itemold['thumb_mode'] != 0) ? $itemold['thumb_mode'] : (
				$this->thumb_mode));
			switch ($mode)
			{
				case 1:/* = keep thumbnail synchronized with image */
					$storage = array_key_exists('image_storage', $result) ? $result['image_storage'] : $itemold['image_storage'];

					if (is_null($storage))
					{
						$result['thumb_storage'] = null;
						$result['thumb_mime'   ] = null;
						$result['thumb_name'   ] = null;
						$result['thumb_size'   ] = null;
						$result['thumb_xsize'  ] = null;
						$result['thumb_ysize'  ] = null;
						break;
					}

					$imagerargs = array();
					$imagerargs['nowx'  ] = array_key_exists('image_xsize', $result) ? $result['image_xsize'] : $itemold['image_xsize'];
					$imagerargs['nowy'  ] = array_key_exists('image_ysize', $result) ? $result['image_ysize'] : $itemold['image_ysize'];
					$imagerargs['limitx'] = 100;//??? но ведь не константами это долэжно быть. может конфиг?
					$imagerargs['limity'] = 100;//??? но ведь не константами это долэжно быть. может конфиг?
					list($x, $y) = core::call($this->imager_module, 'calculate', $imagerargs);

					$storageargs = array();
					$storageargs['id'] = $storage;
					$source_path = core::call($this->image_storage_module, 'path', $storageargs);
					if (is_null($source_path)) throw new exception("Can not retrieve source image for thumbnail generator.");
					$target_path = tempnam(SITEPATH . 'tmp', '');//todo: make it more concrete: what tmp dir to use? system or site-specific?

					$imagerargs = array();
					$imagerargs['source_path'  ] = $source_path;
					$imagerargs['target_path'  ] = $target_path;
					$imagerargs['safe'         ] = false;
					$imagerargs['x'            ] = $x;
					$imagerargs['y'            ] = $y;
					$imagerargs['callback_func'] = array(&$this, '__thumb_callback__');
					$imagerargs['callback_args'] = null;
					$imagerargs['jpeg_quality' ] = 75;
					if (!core::call($this->imager_module, 'make', $imagerargs))
					{
						//??? or should we use some system/core/site-specific image for "generation failed" message?
						$result['thumb_storage'] = null;
						$result['thumb_mime'   ] = null;
						$result['thumb_name'   ] = null;
						$result['thumb_size'   ] = null;
						$result['thumb_xsize'  ] = null;
						$result['thumb_ysize'  ] = null;
						break;
					}

					$imagerargs = array();
					$imagerargs['path'      ] = $target_path;
					$imagerargs['force_name'] = 'thumb_' . basename($source_path);
					$imagerresult = core::call($this->imager_module, 'info', $imagerargs);
					if ($imagerresult === false) throw new exception("Can not retrieve info about thumbnail file.");

					$storageargs = array();
					$storageargs['mime'] = $imagerresult['mime'];
					$storageargs['name'] = $imagerresult['name'];
					$storageargs['size'] = $imagerresult['size'];
					$storageargs['path'] = $imagerresult['path'];
					$storage = core::call($this->thumb_storage_module, 'upload', $storageargs);

					//todo: delete temporary thumbnail file ($target_path).

					$result['thumb_storage'] = $storage;
					$result['thumb_mime'   ] = $imagerresult['mime' ];
					$result['thumb_name'   ] = $imagerresult['name' ];
					$result['thumb_size'   ] = $imagerresult['size' ];
					$result['thumb_xsize'  ] = $imagerresult['xsize'];
					$result['thumb_ysize'  ] = $imagerresult['ysize'];
					break;

				case 2:/* = thumb is always a separate file */
					if (!isset($submit['thumb_attach']) || !is_array($submit['thumb_attach']) || !isset($submit['thumb_attach']['tmp_name']) || !is_uploaded_file($submit['thumb_attach']['tmp_name']))
						break;

					$imagerargs = array();
					$imagerargs['path'      ] = $submit['thumb_attach']['tmp_name'];
					$imagerargs['force_mime'] = $submit['thumb_attach']['type'    ];
					$imagerargs['force_name'] = $submit['thumb_attach']['name'    ];
//					$imagerargs['force_size'] = $submit['thumb_attach']['size'    ];
					$imagerresult = core::call($this->thumbr_module, 'info', $imagerargs);
					if ($imagerresult === false) throw new exception("Can not retrieve info about thumb file.");

					$storageargs = array();
					$storageargs['mime'] = $imagerresult['mime'];
					$storageargs['name'] = $imagerresult['name'];
					$storageargs['size'] = $imagerresult['size'];
					$storageargs['path'] = $imagerresult['path'];
					$storage = core::call($this->thumb_storage_module, 'upload', $storageargs);

					//todo: delete original file here (i.e. $submit['thumb_attach']['tmp_name']).

					$result['thumb_storage'] = $storage;
					$result['thumb_mime'   ] = $imagerresult['mime' ];
					$result['thumb_name'   ] = $imagerresult['name' ];
					$result['thumb_size'   ] = $imagerresult['size' ];
					$result['thumb_xsize'  ] = $imagerresult['xsize'];
					$result['thumb_ysize'  ] = $imagerresult['ysize'];
					break;

				case 3:/* = thumb is file if uploaded with or after image, or it is generated on new image upload */
					//todo: all here. use uploaded thumb if it is uploaded together or after image, or generate new thumb on image upload
					break;

				default:
					// неизвестный режим превьюшки. ничего не делать. чтобы ничего не испортить.
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
					if ($itemnew['thumb_storage'] != '') core::call($this->thumb_storage_module, 'fixate', array('id'=>$itemnew['thumb_storage']));
					break;

				case 'modify':
					core::db('update_picture', compact('itemid', 'itemnew')); 
					if ($itemnew['image_storage'] != $itemold['image_storage'])
					{
						if ($itemnew['image_storage'] != '') core::call($this->image_storage_module, 'fixate', array('id'=>$itemnew['image_storage']));
						if ($itemold['image_storage'] != '') core::call($this->image_storage_module, 'unlink', array('id'=>$itemold['image_storage']));
					}
					if ($itemnew['thumb_storage'] != $itemold['thumb_storage'])
					{
						if ($itemnew['thumb_storage'] != '') core::call($this->thumb_storage_module, 'fixate', array('id'=>$itemnew['thumb_storage']));
						if ($itemold['thumb_storage'] != '') core::call($this->thumb_storage_module, 'unlink', array('id'=>$itemold['thumb_storage']));
					}
					break;

				case 'remove':
					core::db('delete_picture', compact('itemid'));
					if ($itemold['image_storage'] != '') core::call($this->image_storage_module, 'unlink', array('id'=>$itemold['image_storage']));
					if ($itemold['thumb_storage'] != '') core::call($this->thumb_storage_module, 'unlink', array('id'=>$itemold['thumb_storage']));
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
			core::call($this->thumb_storage_module, 'apply');
			break;
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_format ($entity, $action, $itemid, $item)
{
	$result = parent::do_format($entity, $action, $itemid, $item);
	switch ($entity)
	{
		case 'picture':
//???			if (!in_array($action, array('list', 'massedit'))) .... для скорости можно соптимизировать.
			$result['caption'] = $this->embed_children($entity, $itemid, $result['caption']);
			$result['comment'] = $this->embed_children($entity, $itemid, $result['comment']);
//???			if (!in_array($action, array('list', 'massedit'))) .... и тут для оптимизации по скорости...
			if (isset($this->format_ubb_module)) $result['caption'] = core::call($this->format_ubb_module, 'format', array('text'=>$result['caption']));
			if (isset($this->format_ubb_module)) $result['comment'] = core::call($this->format_ubb_module, 'format', array('text'=>$result['comment']));
			break;
	}
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function __thumb_callback__ ($source_image, $source_xsize, $source_ysize, $target_image, $target_xsize, $target_ysize, $callback_args)
{
	return imagecopyresampled($target_image, $source_image, 0, 0, 0, 0, $target_xsize, $target_ysize, $source_xsize, $source_ysize);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_args_item_form ($entity, $action, $overaccess, $itemaccess, $filter, $parent, $itemid, $itemold, $itemnew, $itemoldf, $itemnewf, $errors, $children)
{
	$result = parent::get_args_item_form($entity, $action, $overaccess, $itemaccess, $filter, $parent, $itemid, $itemold, $itemnew, $itemoldf, $itemnewf, $errors, $children);
	$result['image_storage_module'] = $this->image_storage_module;
	$result['thumb_storage_module'] = $this->thumb_storage_module;
	return $result;
}

protected function get_args_list_list ($entity, $action, $overaccess, $filter, $parent, $meta, $lines, $glued, $errors)
{
	$result = parent::get_args_list_list($entity, $action, $overaccess, $filter, $parent, $meta, $lines, $glued, $errors);
	$result['image_storage_module'] = $this->image_storage_module;
	$result['thumb_storage_module'] = $this->thumb_storage_module;
	return $result;
}

protected function get_args_list_line ($entity, $action, $overaccess, $itemaccess, $filter, $parent, $meta, $pageindex, $listindex, $itemid, $itemold, $itemnew, $itemoldf, $itemnewf, $errors, $children)
{
	$result = parent::get_args_list_line($entity, $action, $overaccess, $itemaccess, $filter, $parent, $meta, $pageindex, $listindex, $itemid, $itemold, $itemnew, $itemoldf, $itemnewf, $errors, $children);
	$result['image_storage_module'] = $this->image_storage_module;
	$result['thumb_storage_module'] = $this->thumb_storage_module;
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/*???
protected function do_show_form ($entity, $action, $overaccess, $itemaccess, $filter, $parent, $itemid, $itemold, $itemnew, $itemoldf, $itemnewf, $errors, $children)
{
	if (($entity == 'picture') and ($action == 'image'))
	{
		if (isset($itemold['image_storage']))
		{
			$uri = core::call($this->image_storage_module, 'uri', array('id'=>$itemold['image_storage']));
			if (!is_null($uri))
			{
				header("Location: {$uri}");
				return;//??? template phrase: you was redirected to....
			}
		} else
		{
			//!!! сказать что картинка не была залита, сгенериррована, или вообще не сохранилась.
			//!!!. или вывести картинку дефолтную (призрака заглушку).
		}
	} else
	if (($entity == 'picture') and ($action == 'thumb'))
	{
		if (isset($itemold['thumb_storage']))
		{
			$uri = core::call($this->thumb_storage_module, 'uri', array('id'=>$itemold['thumb_storage']));
			if (!is_null($uri))
			{
				header("Location: {$uri}");
				return;//??? template phrase: you was redirected to....
			}
		} else
		{
			//!!! сказать что картинка не была залита, сгенериррована, или вообще не сохранилась.
			//!!!. или вывести картинку дефолтную (призрака заглушку).
		}
	} else
	return parent::do_show_form($entity, $action, $overaccess, $itemaccess, $filter, $parent, $itemid, $itemold, $itemnew, $itemoldf, $itemnewf, $errors, $children);
}*/

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

?>