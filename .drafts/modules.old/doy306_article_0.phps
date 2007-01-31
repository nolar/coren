<?php defined('CORENINPAGE') or die('Hack!');

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!core::depend('list_0')) return;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class doy306_article_0_exception_duplicate extends list_0_exception_duplicate {}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class doy306_article_0 extends list_0 implements dbaware
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
	return 'article';
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_default_fields ($entity, $parent)
{
	switch ($entity)
	{
		case 'article':
			return array(
			'article'		=> null,

			'child'			=> null, 'child_'	=> null,
			'contest'		=> null, 'contest_'	=> null,
			'place'			=> null,
			'prize'			=> null,

			'title'			=> null,
			'description'		=> null,
			'madestamp'		=> null,

			'child_criteria'	=> array('sname'=>null, 'fname'=>null, 'tname'=>null));
			break;

		default:
			return parent::get_default_fields($entity, $parent);
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function guess_item_fake ($entity, $parent, $submit)
{
	if (
	    (!isset($submit['title'      ]) || ($submit['title'      ] == '')) &&
	    (!isset($submit['description']) || ($submit['description'] == '')) &&
		true) return true;
	return parent::guess_item_fake($entity, $parent, $submit);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_guessed_itemid ($entity, $parent, $data)
{
	switch ($entity)
	{
		case 'article':
			$result = core::find_scalar(array($data), array('article', 'id'), null);
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
		case 'article':
			$itemid =        core::find_scalar(array($data), array('article', 'id'), null) != '';
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
		case 'article':
			//!!!
			break;
	}
	if (core::grant($this->grant_view) || core::grant($this->grant_edit))
	{
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

protected function get_itemaccess ($entity, $itemid, $item)
{
	$result = parent::get_itemaccess($entity, $itemid, $item);
	switch ($entity)
	{
		case 'article':
			//!!!
			break;
	}
	if (core::grant($this->grant_view) || core::grant($this->grant_edit))
	{
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
	$entity = core::find_scalar(array($args, $_GET, $_POST), array('entity'), null);
	$action = core::find_scalar(array($args, $_GET, $_POST), array('action'), null);
	$itemid = core::find_scalar(array($args, $_GET, $_POST), array('id'    ), null);
	$child  = core::find_scalar(array($args, $_GET, $_POST), array('child' ), null);//!!! already done: пересекаетсЯ с полем данных. ввести обЯзательные перфиксы длЯ полей данных!

	$submit = !empty($_POST);//todo: переделать на более достоверный критерий (server[method]==post)

	if (!in_array($entity, array('article'))) $entity = null;
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
		case 'article':
			if (!is_array($filter)) $filter = array();
			$page    = (integer) (isset($filter['page'   ]) ? $filter['page'   ] : $this->default_page   );
			$size    = (integer) (isset($filter['size'   ]) ? $filter['size'   ] : $this->default_size   );
			$skip    = (integer) (isset($filter['skip'   ]) ? $filter['skip'   ] : $this->default_skip   );
			$sorting = (string ) (isset($filter['sorting']) ? $filter['sorting'] : $this->default_sorting);
			$reverse = (string ) (isset($filter['reverse']) ? $filter['reverse'] : $this->default_reverse);

			$count = core::db('select_articles_count', compact('parent', 'itemid'));
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
			$items = core::db('select_articles_data', compact('parent', 'itemid', 'page', 'size', 'skip', 'count', 'offset', 'pagemin', 'pagemax', 'sorting', 'reverse'));

			$ids_child   = array();
			$ids_contest = array();
			foreach ($items as $key => $item)
			{
				if (isset($item['child'  ])) $ids_child  [] = $item['child'  ];
				if (isset($item['contest'])) $ids_contest[] = $item['contest'];
			}
			$data_child   = core::db('select_child_info'  , array('itemids'=>array_unique($ids_child  )));
			$data_contest = core::db('select_contest_info', array('itemids'=>array_unique($ids_contest)));
			foreach ($items as $key => $item)
			{
				if (isset($item['child'  ]) && array_key_exists($item['child'  ], $data_child  )) $items[$key]['child_'  ] = $data_child  [$item['child'  ]]; 
				if (isset($item['contest']) && array_key_exists($item['contest'], $data_contest)) $items[$key]['contest_'] = $data_contest[$item['contest']]; 
			}

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
		case 'article':
			$year   = isset($submit['madestamp_year'  ]) && ($submit['madestamp_year'  ] != '') ? $submit['madestamp_year'  ] : null;
			$month  = isset($submit['madestamp_month' ]) && ($submit['madestamp_month' ] != '') ? $submit['madestamp_month' ] : null;
			$day    = isset($submit['madestamp_day'   ]) && ($submit['madestamp_day'   ] != '') ? $submit['madestamp_day'   ] : null;
			$hour   = isset($submit['madestamp_hour'  ]) && ($submit['madestamp_hour'  ] != '') ? $submit['madestamp_hour'  ] : null;
			$minute = isset($submit['madestamp_minute']) && ($submit['madestamp_minute'] != '') ? $submit['madestamp_minute'] : null;
			$second = isset($submit['madestamp_second']) && ($submit['madestamp_second'] != '') ? $submit['madestamp_second'] : null;
			$isok   = isset($year) || isset($month) || isset($day);
			$result['madestamp'] = $isok ? compact('year', 'month', 'day', 'hour', 'minute', 'second') : null;

			if (isset($submit['child']) && ($submit['child'] != ''))
			{
				$subdata = core::db('select_child_info', array('itemids'=>array($submit['child'])));
				if (array_key_exists($submit['child'], $subdata))
					$result['child_'] = $subdata[$submit['child']]; 
			}

			if (isset($submit['contest']) && ($submit['contest'] != ''))
			{
				$subdata = core::db('select_contest_info', array('itemids'=>array($submit['contest'])));
				if (array_key_exists($submit['contest'], $subdata))
					$result['contest_'] = $subdata[$submit['contest']]; 
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
		case 'article':
			//todo: грубое решение для многошагового поиска в форме. в случае, если ребенок не указан,
			//todo: мы эмулируем ошибку, и форма выведется заново (будь то единичная или списковая).
			//todo: а ошибку мы выдаем в том случае, когда в качестве ребенка указано специаьлное значение
			//todo: ноль (0) или пустая строка (""). реальная разница между этими спец-значениями
			//todo: проявляется лишь в шаблоне (активируются разные radio инпуты).
			//todo: в будущем надо переделать такую многошаговость на более адекватное решение.
			if (($action == 'append') || ($action == 'modify'))
			{
				if ($itemnew['child'] == 0) $result[] = 'need_child_pseudo_error';
			}
			break;
	}
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_action ($entity, $action, $itemid, $itemnew, $itemold, &$newitemid)
{
	switch ($entity)
	{
		case 'article':
			switch ($action)
			{
				case 'append':
					$newitemid = core::db('insert_article', compact('itemnew'));
					break;

				case 'modify':
					core::db('update_article', compact('itemid', 'itemnew'));
					break;

				case 'remove':
					core::db('delete_article', compact('itemid'));
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
		case 'article':
			break;
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_format ($entity, $action, $itemid, $item)
{
	$result = parent::do_format($entity, $action, $itemid, $item);
	switch ($entity)
	{
		case 'article':
//???			if (!in_array($action, array('list', 'massedit'))) .... для скорости можно соптимизировать.
			$result['title'      ] = $this->embed_children($entity, $itemid, $result['title'      ]);
			$result['description'] = $this->embed_children($entity, $itemid, $result['description']);
//???			if (!in_array($action, array('list', 'massedit'))) .... и тут для оптимизации по скорости...
			if (isset($this->format_ubb_module)) $result['title'      ] = core::call($this->format_ubb_module, 'format', array('text'=>$result['title'      ]));
			if (isset($this->format_ubb_module)) $result['description'] = core::call($this->format_ubb_module, 'format', array('text'=>$result['description']));
			break;
	}
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

?>