<?php defined('CORENINPAGE') or die('Hack!');

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!core::depend('list_0')) return;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class notes_0_exception_duplicate extends list_0_exception_duplicate {}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class notes_0 extends list_0 implements dbaware
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected $grant_view;
protected $grant_edit;
protected $grant_post;
protected $grant_reply;

protected $default_page;
protected $default_size;
protected $default_skip;
protected $default_sorting;
protected $default_reverse;

protected $format_ubb_module;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __construct ($configs)
{
	parent::__construct($configs);

	$this->grant_view = isset($configs['grant_view']) ? $configs['grant_view'] : null;
	$this->grant_edit = isset($configs['grant_edit']) ? $configs['grant_edit'] : null;
	$this->grant_post = isset($configs['grant_post']) ? $configs['grant_post'] : null;
	$this->grant_reply = isset($configs['grant_reply']) ? $configs['grant_reply'] : null;
	if (!isset($this->grant_view)) throw new exception("Misconfig: grant_view.");
	if (!isset($this->grant_edit)) throw new exception("Misconfig: grant_edit.");
	if (!isset($this->grant_post)) throw new exception("Misconfig: grant_post.");
	if (!isset($this->grant_reply)) throw new exception("Misconfig: grant_reply.");

	$this->default_page    = core::find_scalar(array($configs), array('default_page'   ), null);
	$this->default_size    = core::find_scalar(array($configs), array('default_size'   ), null);
	$this->default_skip    = core::find_scalar(array($configs), array('default_skip'   ), null);
	$this->default_sorting = core::find_scalar(array($configs), array('default_sorting'), null);
	$this->default_reverse = core::find_scalar(array($configs), array('default_reverse'), null);

	$this->format_ubb_module = core::find_scalar(array($configs), array('format_ubb_module'), null);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_default_entity ()
{
	return 'note';
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_default_fields ($entity, $parent)
{
	switch ($entity)
	{
		case 'note':
			return array(
			'note'			=> null,
			'parent'		=> $parent,
			'author'		=> null,
			'email'			=> null,
			'text'			=> null,
			'reply'			=> null,
			'posted'		=> null,
			'replied'		=> null);
			break;
		default:
			return parent::get_default_fields($entity, $parent);
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function guess_item_fake ($entity, $parent, $submit)
{
	if (
	    (!isset($submit['text']) || ($submit['text'] == '')) &&
		true) return true;
	return parent::guess_item_fake($entity, $parent, $submit);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_guessed_itemid ($entity, $parent, $data)
{
	switch ($entity)
	{
		case 'note':
			$result = core::find_scalar(array($data), array('note'), null);
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
		case 'note':
			$itemid =        core::find_scalar(array($data), array('note'), null) != '';
			$delete = (bool) core::find_scalar(array($data), array('delete'    ), null);
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
		case 'note':
			//!!!
			break;
	}
	if (core::grant($this->grant_post) || core::grant($this->grant_edit))
	{
		$result[] = 'post';
	}
	if (core::grant($this->grant_reply) || core::grant($this->grant_edit))
	{
		$result[] = 'reply';
	}
	if (core::grant($this->grant_view) || core::grant($this->grant_edit))
	{
		$result[] = 'list';
		$result[] = 'item';
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
		case 'note':
			//!!!
			break;
	}
	if (core::grant($this->grant_post) || core::grant($this->grant_edit))
	{
		$result[] = 'post';
	}
	if (core::grant($this->grant_reply) || core::grant($this->grant_edit))
	{
		$result[] = 'reply';
	}
	if (core::grant($this->grant_view) || core::grant($this->grant_edit))
	{
		$result[] = 'list';
		$result[] = 'item';
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
	if (in_array($action, array('post', 'reply'))) return true;
	return parent::is_action_atomic($entity, $action);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function is_action_orphan ($entity, $action)
{
	if (in_array($action, array('post'))) return true;
	return parent::is_action_orphan($entity, $action);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_parse_request ($args)
{
	$entity = null;
	$parent = core::find_scalar(array($args               ), array('parent'         ), null);
	$action = core::find_scalar(array($args, $_POST, $_GET), array('noteaction'     ), null);
	$itemid = core::find_scalar(array($args, $_POST, $_GET), array('noteid'         ), null);

	//!!! todo: сделать все префиксы конфигурируемым (notechild, noteid, noteaction...)
	if (isset($_GET['notechild'])) $child = $_GET['notechild'];
	else $child = null;

	$submit = !empty($_POST);//todo: переделать на более достоверный критерий (server[method]==post)

	if (!in_array($entity, array('note'))) $entity = null;
	if (!in_array($action, array('append', 'modify', 'remove', 'list', 'item', 'post', 'reply'))) $action = null;
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
		case 'note':
			if (!is_array($filter)) $filter = array();
			$page    = (integer) (isset($filter['page'   ]) ? $filter['page'   ] : $this->default_page   );
			$size    = (integer) (isset($filter['size'   ]) ? $filter['size'   ] : $this->default_size   );
			$skip    = (integer) (isset($filter['skip'   ]) ? $filter['skip'   ] : $this->default_skip   );
			$sorting = (string ) (isset($filter['sorting']) ? $filter['sorting'] : $this->default_sorting);
			$reverse = (string ) (isset($filter['reverse']) ? $filter['reverse'] : $this->default_reverse);

			$count = core::db('select_notes_count', compact('parent', 'itemid'));
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
			$items = core::db('select_notes_data', compact('parent', 'itemid', 'page', 'size', 'skip', 'count', 'offset', 'pagemin', 'pagemax', 'sorting', 'reverse'));
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
		case 'note':
			$year   = isset($submit['posted_year'  ]) && ($submit['posted_year'  ] != '') ? $submit['posted_year'  ] : null;
			$month  = isset($submit['posted_month' ]) && ($submit['posted_month' ] != '') ? $submit['posted_month' ] : null;
			$day    = isset($submit['posted_day'   ]) && ($submit['posted_day'   ] != '') ? $submit['posted_day'   ] : null;
			$hour   = isset($submit['posted_hour'  ]) && ($submit['posted_hour'  ] != '') ? $submit['posted_hour'  ] : null;
			$minute = isset($submit['posted_minute']) && ($submit['posted_minute'] != '') ? $submit['posted_minute'] : null;
			$second = isset($submit['posted_second']) && ($submit['posted_second'] != '') ? $submit['posted_second'] : null;
			$isok   = isset($year) || isset($month) || isset($day);
			$result['posted'] = $isok ? compact('year', 'month', 'day', 'hour', 'minute', 'second') : null;

			$year   = isset($submit['replied_year'  ]) && ($submit['replied_year'  ] != '') ? $submit['replied_year'  ] : null;
			$month  = isset($submit['replied_month' ]) && ($submit['replied_month' ] != '') ? $submit['replied_month' ] : null;
			$day    = isset($submit['replied_day'   ]) && ($submit['replied_day'   ] != '') ? $submit['replied_day'   ] : null;
			$hour   = isset($submit['replied_hour'  ]) && ($submit['replied_hour'  ] != '') ? $submit['replied_hour'  ] : null;
			$minute = isset($submit['replied_minute']) && ($submit['replied_minute'] != '') ? $submit['replied_minute'] : null;
			$second = isset($submit['replied_second']) && ($submit['replied_second'] != '') ? $submit['replied_second'] : null;
			$isok   = isset($year) || isset($month) || isset($day);
			$result['replied'] = $isok ? compact('year', 'month', 'day', 'hour', 'minute', 'second') : null;

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
		case 'note':
			if (($action == 'post') && ($itemnew['reply'] != ''))
				$result[] = 'can_not_reply';
			break;
	}
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_action ($entity, $action, $itemid, $itemnew, $itemold, &$newitemid)
{
	switch ($entity)
	{
		case 'note':
			switch ($action)
			{
				case 'post':
					$newitemid = core::db('post_note', compact('itemnew'));
					break;

				case 'reply':
					core::db('reply_note', compact('itemid', 'itemnew'));
					break;

				case 'append':
					$newitemid = core::db('insert_note', compact('itemnew'));
					break;

				case 'modify':
					core::db('update_note', compact('itemid', 'itemnew')); 
					break;

				case 'remove':
					core::db('delete_note', compact('itemid'));
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
		case 'note':
			break;
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_format ($entity, $action, $itemid, $item)
{
	$result = parent::do_format($entity, $action, $itemid, $item);
	switch ($entity)
	{
		case 'note':
			$result['text' ] = htmlspecialchars($result['text' ]);
			$result['reply'] = htmlspecialchars($result['reply']);
//???			if (!in_array($action, array('list', 'massedit'))) .... для скорости можно соптимизировать.
			$result['text' ] = $this->embed_children($entity, $itemid, $result['text' ]);
			$result['reply'] = $this->embed_children($entity, $itemid, $result['reply']);
//???			if (!in_array($action, array('list', 'massedit'))) .... и тут для оптимизации по скорости...
			if (isset($this->format_ubb_module)) $result['text' ] = core::call($this->format_ubb_module, 'format', array('text'=>$result['text' ]));
			if (isset($this->format_ubb_module)) $result['reply'] = core::call($this->format_ubb_module, 'format', array('text'=>$result['reply']));
			break;
	}
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

?>