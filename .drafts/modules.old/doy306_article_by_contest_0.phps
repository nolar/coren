<?php defined('CORENINPAGE') or die('Hack!');

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!core::depend('doy306_article_0')) return;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class doy306_article_by_contest_0_exception_duplicate extends doy306_article_0_exception_duplicate {}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class doy306_article_by_contest_0 extends doy306_article_0 implements dbaware
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

protected function get_default_fields ($entity, $parent)
{
	switch ($entity)
	{
		case 'article':
			$result = parent::get_default_fields($entity, $parent);
			$result['contest'] = $parent;
			return $result;

		default:
			return parent::get_default_fields($entity, $parent);
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_read_list ($entity, $filter, $parent, $itemid, &$meta)
{
	switch ($entity)
	{
		case 'article':
			//nb: отличается от того же родительского метода тем, что:
			//nb: 1. читает весь список без разбивки по страницам.
			//nb: 2. фильтрует по родителю.
			//nb: 3. не сортирует. а может все-таки сортировать???
			//nb: 4. не читает дочерние данные, которые уже есть в родителе.
			//nb: поэтому расширение метода невозможно, и он полностью переопределяется, но похож на родительский.

			$count   = core::db('select_articles_count', compact('parent', 'itemid'));
			$skip    = $offset  = 0;
			$size    = $count - $skip;
			$page    = $pagemin = $pagemax = 1;
			$sorting = $reverse = null;
			$meta = compact('count', 'page', 'size', 'skip', 'offset', 'pagemin', 'pagemax', 'sorting', 'reverse');
			$items = core::db('select_articles_data', compact('parent', 'itemid', 'page', 'size', 'skip', 'count', 'offset', 'pagemin', 'pagemax', 'sorting', 'reverse'));

			$ids_child   = array();
			foreach ($items as $key => $item)
			{
				if (isset($item['child'  ])) $ids_child  [] = $item['child'  ];
			}
			$data_child   = core::db('select_child_info'  , array('itemids'=>array_unique($ids_child  )));
			foreach ($items as $key => $item)
			{
				if (isset($item['child'  ]) && array_key_exists($item['child'  ], $data_child  )) $items[$key]['child_'  ] = $data_child  [$item['child'  ]]; 
			}

			return $items;

		default:
			return parent::do_read_list($entity, $filter, $parent, $itemid, &$meta);
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

?>