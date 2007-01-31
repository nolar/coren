<?php defined('CORENINPAGE') or die('Hack!');

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!core::depend('list_0')) return;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class doy306_contest_0_exception_duplicate extends list_0_exception_duplicate {}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class doy306_contest_0 extends list_0 implements dbaware
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected $grant_edit;
protected $grant_view;

protected $default_page;
protected $default_size;
protected $default_skip;
protected $default_sorting;
protected $default_reverse;
protected $default_archive;

protected $format_ubb_module;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __construct ($configs)
{
	parent::__construct($configs);

	$this->grant_view = core::find_scalar(array($configs), array('grant_view'), null);
	$this->grant_edit = core::find_scalar(array($configs), array('grant_edit'), null);
	if (!isset($this->grant_view)) throw new exception("Misconfig: grant_view.");
	if (!isset($this->grant_edit)) throw new exception("Misconfig: grant_edit.");

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
	return 'contest';
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_default_fields ($entity, $parent)
{
	switch ($entity)
	{
		case 'contest':
			return array(
			'contest'		=> null,

			'title'			=> null,
			'preamble'		=> null,
			'task'			=> null,
			'results'		=> null,

			'stamp'			=> null,
			'actualfrom'		=> null,
			'actualtill'		=> null,
			'published'		=> null);
			break;

		default:
			return parent::get_default_fields($entity, $parent);
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function guess_item_fake ($entity, $parent, $submit)
{
	if (
	    (!isset($submit['title'   ]) || ($submit['title'   ] == '')) &&
	    (!isset($submit['preamble']) || ($submit['preamble'] == '')) &&
	    (!isset($submit['task'    ]) || ($submit['task'    ] == '')) &&
	    (!isset($submit['results' ]) || ($submit['results' ] == '')) &&
		true) return true;
	return parent::guess_item_fake($entity, $parent, $submit);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_guessed_itemid ($entity, $parent, $data)
{
	switch ($entity)
	{
		case 'contest':
			$result = core::find_scalar(array($data), array('contest', 'id'), null);
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
		case 'contest':
			$itemid =        core::find_scalar(array($data), array('contest', 'id'), null) != '';
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
		case 'contest':
			//!!!
			break;
	}
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
	$result = parent::get_itemaccess($entity, $itemid, $item);
	switch ($entity)
	{
		case 'contest':
			//!!!
			break;
	}
	if (core::grant($this->grant_view) || core::grant($this->grant_edit))
	{
		$result[] = 'lookup';
		$result[] = 'list';
		$result[] = 'item';
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
{
	$entity = core::find_scalar(array($args, $_GET, $_POST), array('entity'), null);;
	$action = core::find_scalar(array($args, $_GET, $_POST), array('action'), null);
	$itemid = core::find_scalar(array($args, $_GET, $_POST), array('id'    ), null);
	$child  = core::find_scalar(array($args, $_GET, $_POST), array('child' ), null);

	$submit = !empty($_POST);//todo: переделать на более достоверный критерий (server[method]==post)

	if (!in_array($entity, array('contest'))) $entity = null;
	if (!in_array($action, array('append', 'modify', 'remove', 'list', 'item'))) $action = null;
	if (is_null($action)) $action = isset($itemid) ? 'item' : 'list';

	$filter = array();
	$filter['page'   ] = core::find_scalar(array($args, $_POST, $_GET), array('page'   ), null);
	$filter['size'   ] = core::find_scalar(array($args, $_POST, $_GET), array('size'   ), null);
	$filter['skip'   ] = core::find_scalar(array($args, $_POST, $_GET), array('skip'   ), null);
	$filter['sorting'] = core::find_scalar(array($args, $_POST, $_GET), array('sorting'), null);
	$filter['reverse'] = core::find_scalar(array($args, $_POST, $_GET), array('reverse'), null);
	$filter['archive'] = core::find_scalar(array($args, $_POST, $_GET), array('archive'), null);

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
		case 'contest':
			if (!is_array($filter)) $filter = array();
			$page    = (integer) (isset($filter['page'   ]) ? $filter['page'   ] : $this->default_page   );
			$size    = (integer) (isset($filter['size'   ]) ? $filter['size'   ] : $this->default_size   );
			$skip    = (integer) (isset($filter['skip'   ]) ? $filter['skip'   ] : $this->default_skip   );
			$sorting = (string ) (isset($filter['sorting']) ? $filter['sorting'] : $this->default_sorting);
			$reverse = (string ) (isset($filter['reverse']) ? $filter['reverse'] : $this->default_reverse);
			$archive = (string ) (isset($filter['archive']) ? $filter['archive'] : $this->default_archive);

			if (core::grant($this->grant_edit))
			{
				$published  = null;
				$actualfrom = null;
				$actualtill = null;
			} else
			{
				$published  = true;
				$actualfrom = true;
				$actualtill = $archive ? null : true;
			}

			$count = core::db('select_contests_count', compact('parent', 'itemid', 'published', 'actualfrom', 'actualtill'));
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
			$items = core::db('select_contests_data', compact('parent', 'itemid', 'published', 'actualfrom', 'actualtill', 'page', 'size', 'skip', 'count', 'offset', 'pagemin', 'pagemax', 'sorting', 'reverse'));
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
		case 'contest':
			$year   = isset($submit['stamp_year'  ]) && ($submit['stamp_year'  ] != '') ? $submit['stamp_year'  ] : null;
			$month  = isset($submit['stamp_month' ]) && ($submit['stamp_month' ] != '') ? $submit['stamp_month' ] : null;
			$day    = isset($submit['stamp_day'   ]) && ($submit['stamp_day'   ] != '') ? $submit['stamp_day'   ] : null;
			$hour   = isset($submit['stamp_hour'  ]) && ($submit['stamp_hour'  ] != '') ? $submit['stamp_hour'  ] : null;
			$minute = isset($submit['stamp_minute']) && ($submit['stamp_minute'] != '') ? $submit['stamp_minute'] : null;
			$second = isset($submit['stamp_second']) && ($submit['stamp_second'] != '') ? $submit['stamp_second'] : null;
			$isok   = isset($year) || isset($month) || isset($day);
			$result['stamp'] = $isok ? compact('year', 'month', 'day', 'hour', 'minute', 'second') : null;

			$year   = isset($submit['actualfrom_year'  ]) && ($submit['actualfrom_year'  ] != '') ? $submit['actualfrom_year'  ] : null;
			$month  = isset($submit['actualfrom_month' ]) && ($submit['actualfrom_month' ] != '') ? $submit['actualfrom_month' ] : null;
			$day    = isset($submit['actualfrom_day'   ]) && ($submit['actualfrom_day'   ] != '') ? $submit['actualfrom_day'   ] : null;
			$hour   = isset($submit['actualfrom_hour'  ]) && ($submit['actualfrom_hour'  ] != '') ? $submit['actualfrom_hour'  ] : null;
			$minute = isset($submit['actualfrom_minute']) && ($submit['actualfrom_minute'] != '') ? $submit['actualfrom_minute'] : null;
			$second = isset($submit['actualfrom_second']) && ($submit['actualfrom_second'] != '') ? $submit['actualfrom_second'] : null;
			$isok   = isset($submit['actualfrom_set'   ]) && ($submit['actualfrom_set'   ]      );
			$result['actualfrom'] = $isok ? compact('year', 'month', 'day', 'hour', 'minute', 'second') : null;

			$year   = isset($submit['actualtill_year'  ]) && ($submit['actualtill_year'  ] != '') ? $submit['actualtill_year'  ] : null;
			$month  = isset($submit['actualtill_month' ]) && ($submit['actualtill_month' ] != '') ? $submit['actualtill_month' ] : null;
			$day    = isset($submit['actualtill_day'   ]) && ($submit['actualtill_day'   ] != '') ? $submit['actualtill_day'   ] : null;
			$hour   = isset($submit['actualtill_hour'  ]) && ($submit['actualtill_hour'  ] != '') ? $submit['actualtill_hour'  ] : null;
			$minute = isset($submit['actualtill_minute']) && ($submit['actualtill_minute'] != '') ? $submit['actualtill_minute'] : null;
			$second = isset($submit['actualtill_second']) && ($submit['actualtill_second'] != '') ? $submit['actualtill_second'] : null;
			$isok   = isset($submit['actualtill_set'   ]) && ($submit['actualtill_set'   ]      );
			$result['actualtill'] = $isok ? compact('year', 'month', 'day', 'hour', 'minute', 'second') : null;

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
		case 'contest':
			//!!!
			break;
	}
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_action ($entity, $action, $itemid, $itemnew, $itemold, &$newitemid)
{
	switch ($entity)
	{
		case 'contest':
			switch ($action)
			{
				case 'append':
					$newitemid = core::db('insert_contest', compact('itemnew'));
					break;

				case 'modify':
					core::db('update_contest', compact('itemid', 'itemnew'));
					break;

				case 'remove':
					core::db('delete_contest', compact('itemid'));
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
		case 'contest':
			break;
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_format ($entity, $action, $itemid, $item)
{
	$result = parent::do_format($entity, $action, $itemid, $item);
	switch ($entity)
	{
		case 'contest':
//???			if (!in_array($action, array('list', 'massedit'))) .... для скорости можно соптимизировать.
			$result['title'   ] = $this->embed_children($entity, $itemid, $result['title'   ]);
			$result['preamble'] = $this->embed_children($entity, $itemid, $result['preamble']);
			$result['task'    ] = $this->embed_children($entity, $itemid, $result['task'    ]);
			$result['results' ] = $this->embed_children($entity, $itemid, $result['results' ]);
//???			if (!in_array($action, array('list', 'massedit'))) .... и тут для оптимизации по скорости...
			if (isset($this->format_ubb_module)) $result['title'   ] = core::call($this->format_ubb_module, 'format', array('text'=>$result['title'   ]));
			if (isset($this->format_ubb_module)) $result['preamble'] = core::call($this->format_ubb_module, 'format', array('text'=>$result['preamble']));
			if (isset($this->format_ubb_module)) $result['task'    ] = core::call($this->format_ubb_module, 'format', array('text'=>$result['task'    ]));
			if (isset($this->format_ubb_module)) $result['results' ] = core::call($this->format_ubb_module, 'format', array('text'=>$result['results' ]));
			break;
	}
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

?>