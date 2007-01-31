<?php defined('CORENINPAGE') or die('Hack!');

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!core::depend('list_0')) return;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class forum_0_exception_duplicate extends list_0_exception_duplicate {}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class forum_0 extends list_0 implements dbaware
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected $grant_view;
protected $grant_post;
protected $grant_edit;

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

	$this->grant_view = isset($configs['grant_view']) ? $configs['grant_view'] : null;
	$this->grant_post = isset($configs['grant_post']) ? $configs['grant_post'] : null;
	$this->grant_edit = isset($configs['grant_edit']) ? $configs['grant_edit'] : null;
	if (!isset($this->grant_view)) throw new exception("Misconfig: grant_view.");
	if (!isset($this->grant_post)) throw new exception("Misconfig: grant_post.");
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
	return 'message';
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_default_fields ($entity, $parent)
{
	switch ($entity)
	{
		case 'forum':
			return array(
			'forum'			=> null,
			'grant'			=> null,

			'name'			=> null,
			'comment'		=> null);

			break;

		case 'topic':
			return array(
			'topic'			=> null,
			'forum'			=> null, 'forum_' => null,

			'creator'		=> null, 'creator_' => null,
			'creator_addr'		=> null,
			'created'		=> null,

			'name'			=> null,
			'comment'		=> null,

			'last_stamp'		=> null,
			'mess_count'		=> null,
			'first_mess'		=> null,

			'.text'			=> null);

			break;

		case 'message':
			return array(
			'message'		=> null,
			'topic'			=> null, 'topic_' => null,
			'forum'			=> null, 'forum_' => null,

			'creator'		=> null, 'creator_' => null,
			'creator_addr'		=> null,
			'created'		=> null,

			'text'			=> null);

			break;

		default:
			return parent::get_default_fields($entity, $parent);
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function guess_item_fake ($entity, $parent, $submit)
{
	switch ($entity)
	{
		case 'forum':
			if (
			    (!isset($submit['name'   ]) || ($submit['name'   ] == '')) &&
			    (!isset($submit['comment']) || ($submit['comment'] == '')) &&
			    (!isset($submit['grant'  ]) || ($submit['grant'  ] == '')) &&
				true) return true;
			break;

		case 'topic':
			if (
			    (!isset($submit['name'   ]) || ($submit['name'   ] == '')) &&
			    (!isset($submit['comment']) || ($submit['comment'] == '')) &&
			    (!isset($submit['.text'  ]) || ($submit['.text'  ] == '')) &&
				true) return true;
			break;

		case 'message':
			if (
			    (!isset($submit['text']) || ($submit['text'] == '')) &&
				true) return true;
			break;
	}
	return parent::guess_item_fake($entity, $parent, $submit);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_guessed_itemid ($entity, $parent, $data)
{
	switch ($entity)
	{
		case 'forum':
			$result = core::find_scalar(array($data), array('forum', 'f'), null);
			break;

		case 'topic':
			$result = core::find_scalar(array($data), array('topic', 't'), null);
			break;

		case 'message':
			$result = core::find_scalar(array($data), array('message', 'm'), null);
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
		case 'forum':
			$itemid =        core::find_scalar(array($data), array('forum', 'f'), null) != '';
			$delete = (bool) core::find_scalar(array($data), array('delete'), null);
			$fake = $this->guess_item_fake($entity, $parent, $data);
			if (!$itemid && !$delete) $result = $fake ? null : 'append'; else
			if (!$itemid &&  $delete) $result =         null           ; else
			if ( $itemid && !$delete) $result =                'modify'; else
			if ( $itemid &&  $delete) $result =                'remove'; else
			$result = parent::get_guessed_action($entity, $parent, $data);
			break;

		case 'topic':
			$itemid =        core::find_scalar(array($data), array('topic', 't'), null) != '';
			$delete = (bool) core::find_scalar(array($data), array('delete'), null);
			$fake = $this->guess_item_fake($entity, $parent, $data);
			if (!$itemid && !$delete) $result = $fake ? null : 'append'; else
			if (!$itemid &&  $delete) $result =         null           ; else
			if ( $itemid && !$delete) $result =                'modify'; else
			if ( $itemid &&  $delete) $result =                'remove'; else
			$result = parent::get_guessed_action($entity, $parent, $data);
			break;

		case 'message':
			$itemid =        core::find_scalar(array($data), array('message', 'm'), null) != '';
			$delete = (bool) core::find_scalar(array($data), array('delete' ), null);
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
		case 'forum':
			if (core::grant($this->grant_view) || core::grant($this->grant_post) || core::grant($this->grant_edit))
			{
				$result[] = 'list';
				$result[] = 'item';
			}
			if (core::grant($this->grant_post) || core::grant($this->grant_edit))
			{
				$result[] = 'lookup';
			}
			if (core::grant($this->grant_edit))
			{
				$result[] = 'append';
				$result[] = 'modify';
				$result[] = 'remove';
			}
			break;

		case 'topic':
			if (core::grant($this->grant_view) || core::grant($this->grant_post) || core::grant($this->grant_edit))
			{
				$result[] = 'list';
				$result[] = 'item';
			}
			if (core::grant($this->grant_post) || core::grant($this->grant_edit))
			{
				$result[] = 'append';
			}
			if (core::grant($this->grant_edit))
			{
				$result[] = 'modify';
				$result[] = 'remove';
			}
			break;

		case 'message':
			if (core::grant($this->grant_view) || core::grant($this->grant_post) || core::grant($this->grant_edit))
			{
				$result[] = 'list';
//???				$result[] = 'item';
			}
			if (core::grant($this->grant_post) || core::grant($this->grant_edit))
			{
				$result[] = 'append';
			}
			if (core::grant($this->grant_edit))
			{
				$result[] = 'modify';
				$result[] = 'remove';
			}
			break;
	}
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_itemaccess ($entity, $itemid, $item)
{
	$result = parent::get_itemaccess($entity, $itemid, $item);
	switch ($entity)
	{
		case 'forum':
			if (core::grant($this->grant_view) || core::grant($this->grant_post) || core::grant($this->grant_edit))
			{
				$result[] = 'list';
				$result[] = 'item';
			}
			if (core::grant($this->grant_post) || core::grant($this->grant_edit))
			{
				$result[] = 'lookup';
			}
			if (core::grant($this->grant_edit))
			{
				$result[] = 'append';
				$result[] = 'modify';
				$result[] = 'remove';
			}
			break;

		case 'topic':
			if (core::grant($this->grant_view) || core::grant($this->grant_post) || core::grant($this->grant_edit))
			{
				$result[] = 'list';
				$result[] = 'item';
			}
			if (core::grant($this->grant_post) || core::grant($this->grant_edit))
			{
				$result[] = 'append';
			}
			if (core::grant($this->grant_edit))
			{
				$result[] = 'modify';
				$result[] = 'remove';
			}
			break;

		case 'message':
			if (core::grant($this->grant_view) || core::grant($this->grant_post) || core::grant($this->grant_edit))
			{
				$result[] = 'list';
//???				$result[] = 'item';
			}
			if (core::grant($this->grant_post) || core::grant($this->grant_edit))
			{
				$result[] = 'append';
			}
			if (core::grant($this->grant_edit))
			{
				$result[] = 'modify';
				$result[] = 'remove';
			}
			break;
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
	$child  = core::find_scalar(array($args, $_GET, $_POST), array('child' ), null);

	if (($entity == 'forum') && is_null($itemid)) $itemid = core::find_scalar(array($args, $_POST, $_GET), array('f'), null);
	if (($entity == 'topic') && is_null($itemid)) $itemid = core::find_scalar(array($args, $_POST, $_GET), array('t'), null);
	if (($entity == 'message') && is_null($itemid)) $itemid = core::find_scalar(array($args, $_POST, $_GET), array('m'), null);

	$submit = !empty($_POST);//todo: переделать на более достоверный критерий (server[method]==post)

	if (!in_array($entity, array('forum', 'topic', 'message'))) $entity = null;
	if (!in_array($action, array('append', 'modify', 'remove', 'list', 'item'))) $action = null;
	if (is_null($action)) $action = isset($itemid) ? 'item' : 'list';

	//!!!todo: четко отследить тут запрет на ряд действий: message.item, message.list, topic.list.
	//!!!todo: эти действия через parse_request не проходят, а вызываются тоьлко напрямую через show_list,
	//!!!todo: и потому доступны в access'ах.

	$filter = array();
	$filter['page'   ] = core::find_scalar(array($args, $_POST, $_GET), array('page'   ), null);
	$filter['size'   ] = core::find_scalar(array($args, $_POST, $_GET), array('size'   ), null);
	$filter['skip'   ] = core::find_scalar(array($args, $_POST, $_GET), array('skip'   ), null);
	$filter['sorting'] = core::find_scalar(array($args, $_POST, $_GET), array('sorting'), null);
	$filter['reverse'] = core::find_scalar(array($args, $_POST, $_GET), array('reverse'), null);
	$filter['forum'  ] = core::find_scalar(array($args, $_POST, $_GET), array('f'      ), null);
	$filter['topic'  ] = core::find_scalar(array($args, $_POST, $_GET), array('t'      ), null);

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
		case 'forum':
			if (!is_array($filter)) $filter = array();
			$page    = (integer) (isset($filter['page'   ]) ? $filter['page'   ] : $this->default_page   );
			$size    = (integer) (isset($filter['size'   ]) ? $filter['size'   ] : $this->default_size   );
			$skip    = (integer) (isset($filter['skip'   ]) ? $filter['skip'   ] : $this->default_skip   );
			$sorting = (string ) (isset($filter['sorting']) ? $filter['sorting'] : $this->default_sorting);
			$reverse = (string ) (isset($filter['reverse']) ? $filter['reverse'] : $this->default_reverse);

			if (core::grant($this->grant_edit))
			{
				$grants     = null;
			} else
			{
				$grants     = core::grants();
			}

			$count = core::db('select_forums_count', compact('parent', 'itemid', 'grants'));
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
			$items = core::db('select_forums_data', compact('parent', 'itemid', 'grants', 'page', 'size', 'skip', 'count', 'offset', 'pagemin', 'pagemax', 'sorting', 'reverse'));

			return $items;

		case 'topic':
			if (!is_array($filter)) $filter = array();
			$page    = (integer) (isset($filter['page'   ]) ? $filter['page'   ] : $this->default_page   );
			$size    = (integer) (isset($filter['size'   ]) ? $filter['size'   ] : $this->default_size   );
			$skip    = (integer) (isset($filter['skip'   ]) ? $filter['skip'   ] : $this->default_skip   );
			$sorting = (string ) (isset($filter['sorting']) ? $filter['sorting'] : $this->default_sorting);
			$reverse = (string ) (isset($filter['reverse']) ? $filter['reverse'] : $this->default_reverse);
			$forum   = (string ) (isset($filter['forum'  ]) ? $filter['forum'  ] : null);

			//!!!todo: ВАЖНО!!! проверить что этот $forum id нам разрешен на чтение; если нет - эмулировать отсутствие топика.

			$count = core::db('select_topics_count', compact('parent', 'itemid', 'forum'));
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
			$items = core::db('select_topics_data', compact('parent', 'itemid', 'forum', 'page', 'size', 'skip', 'count', 'offset', 'pagemin', 'pagemax', 'sorting', 'reverse'));

			$ids_forum = array();
			foreach ($items as $key => $item)
			{
				if (isset($item['forum'])) $ids_forum[] = $item['forum'];
			}
			$data_forum = core::db('select_forum_info', array('itemids'=>array_unique($ids_forum)));
			foreach ($items as $key => $item)
			{
				if (isset($item['forum']) && array_key_exists($item['forum'], $data_forum)) $items[$key]['forum_'] = $data_forum[$item['forum']]; 
			}

			$ids_accounts = array();
			foreach ($items as $key => $item)
			{
				if (isset($item['creator'])) $ids_accounts[] = $item['creator'];
			}
			$data_accounts = core::call('account_information', 'accounts_information', array('ids'=>array_unique($ids_accounts)));
			foreach ($items as $key => $item)
			{
				if (isset($item['creator']) && array_key_exists($item['creator'], $data_accounts)) $items[$key]['creator_'] = $data_accounts[$item['creator']];
			}

			return $items;

		case 'message':
			if (!is_array($filter)) $filter = array();
			$page    = (integer) (isset($filter['page'   ]) ? $filter['page'   ] : $this->default_page   );
			$size    = (integer) (isset($filter['size'   ]) ? $filter['size'   ] : $this->default_size   );
			$skip    = (integer) (isset($filter['skip'   ]) ? $filter['skip'   ] : $this->default_skip   );
			$sorting = (string ) (isset($filter['sorting']) ? $filter['sorting'] : $this->default_sorting);
			$reverse = (string ) (isset($filter['reverse']) ? $filter['reverse'] : $this->default_reverse);
			$topic   = (string ) (isset($filter['topic'  ]) ? $filter['topic'  ] : null);

			//!!!todo: ВАЖНО!!! проверить что этот $topic id нам разрешен на чтение (в т.ч. по форуму); если нет - эмулировать отсутствие мессаги.

			$count = core::db('select_messages_count', compact('parent', 'itemid', 'topic'));
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
			$items = core::db('select_messages_data', compact('parent', 'itemid', 'topic', 'page', 'size', 'skip', 'count', 'offset', 'pagemin', 'pagemax', 'sorting', 'reverse'));

			$ids_topic = array();
			foreach ($items as $key => $item)
			{
				if (isset($item['topic'])) $ids_topic[] = $item['topic'];
			}
			$data_topic = core::db('select_topic_info', array('itemids'=>array_unique($ids_topic)));
			foreach ($items as $key => $item)
			{
				if (isset($item['topic']) && array_key_exists($item['topic'], $data_topic))
				{
					$items[$key]['topic_'] = $data_topic[$item['topic']]; 
					$items[$key]['forum' ] = $data_topic[$item['topic']]['forum'];
				}
			}

			$ids_forum = array();
			foreach ($items as $key => $item)
			{
				if (isset($item['forum'])) $ids_forum[] = $item['forum'];
			}
			$data_forum = core::db('select_forum_info', array('itemids'=>array_unique($ids_forum)));
			foreach ($items as $key => $item)
			{
				if (isset($item['forum']) && array_key_exists($item['forum'], $data_forum)) $items[$key]['forum_'] = $data_forum[$item['forum']]; 
			}

			$ids_accounts = array();
			foreach ($items as $key => $item)
			{
				if (isset($item['creator'])) $ids_accounts[] = $item['creator'];
			}
			$data_accounts = core::call('account_information', 'accounts_information', array('ids'=>array_unique($ids_accounts)));
			foreach ($items as $key => $item)
			{
				if (isset($item['creator']) && array_key_exists($item['creator'], $data_accounts)) $items[$key]['creator_'] = $data_accounts[$item['creator']];
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
		case 'forum':
			break;

		case 'topic':
			if (in_array($action, array('append')))
			{
				$result['creator'] = core::until(null, 'account');

				$ids = array();
				if ($result['creator'] != '') $ids[] = $result['creator'];
				$subdata = core::call('account_information', 'accounts_information', compact('ids'));

				$result['creator_'] = is_array($subdata) && array_key_exists($result['creator'], $subdata) ? $subdata[$result['creator']] : null;
				$result['creator_addr'] = $_SERVER['REMOTE_ADDR'];

				$result['forum'] = core::find_scalar(array($_POST, $_GET, $submit), array('f', 'forum'), null);
			}

			if (isset($submit['forum']) && ($submit['forum'] != ''))
			{
				$subdata = core::db('select_forum_info', array('itemids'=>array($submit['forum'])));
				if (array_key_exists($submit['forum'], $subdata))
					$result['forum_'] = $subdata[$submit['forum']]; 
			}
			break;

		case 'message':
			if (in_array($action, array('append')))
			{
				$result['creator'] = core::until(null, 'account');

				$ids = array();
				if ($result['creator'] != '') $ids[] = $result['creator'];
				$subdata = core::call('account_information', 'accounts_information', compact('ids'));

				$result['creator_'] = is_array($subdata) && array_key_exists($result['creator'], $subdata) ? $subdata[$result['creator']] : null;
				$result['creator_addr'] = $_SERVER['REMOTE_ADDR'];

				$result['topic'] = core::find_scalar(array($_POST, $_GET, $submit), array('t', 'topic'), null);
			}

			if (isset($submit['topic']) && ($submit['topic'] != ''))
			{
				$subdata = core::db('select_topic_info', array('itemids'=>array($submit['topic'])));
				if (array_key_exists($submit['topic'], $subdata))
				{
					$result['topic_'] = $subdata[$submit['topic']]; 
					$result['forum' ] = $subdata[$submit['topic']]['forum']; 
					$submit['forum' ] = $result['forum'];
				}
			}

			if (isset($submit['forum']) && ($submit['forum'] != ''))
			{
				$subdata = core::db('select_forum_info', array('itemids'=>array($submit['forum'])));
				if (array_key_exists($submit['forum'], $subdata))
					$result['forum_'] = $subdata[$submit['forum']]; 
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
		case 'forum':
			//!!!
			break;

		case 'topic':
			//!!!
			if (is_null($itemnew['forum']))
				$result[] = 'forum';
			break;

		case 'message':
			//!!!
			//todo: check if we can revoke item from current parent, and if we can inject it into new parent (by grants?)
			if (is_null($itemnew['topic']))
				$result[] = 'topic';
			break;
	}
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_action ($entity, $action, $itemid, $itemnew, $itemold, &$newitemid)
{
	switch ($entity)
	{
		case 'forum':
			switch ($action)
			{
				case 'append':
					$newitemid = core::db('insert_forum', compact('itemnew'));
					break;

				case 'modify':
					core::db('update_forum', compact('itemid', 'itemnew'));
					break;

				case 'remove':
					core::db('delete_forum', compact('itemid'));
					break;

				default:
					return parent::do_action($entity, $action, $itemid, $itemnew, $itemold, $newitemid);
			}
			break;

		case 'topic':
			switch ($action)
			{
				case 'append':
					$newitemid = core::db('insert_topic', compact('itemnew'));

					$mess = $this->get_default_fields('message', null);
					$mess['topic'] = $newitemid; $mess['topic_'] = $itemnew;
					$mess['forum'] = $itemnew['forum']; $mess['forum_'] = $itemnew['forum_'];
					$mess['creator'] = $itemnew['creator'];
					$mess['creator_addr'] = $itemnew['creator_addr'];
					$mess['created'] = $itemnew['created'];
					$mess['text'] = $itemnew['.text'];
					$messid = core::db('insert_message', array('itemnew'=>$mess));

					core::db('recalculate_topic', array('id'=>$newitemid));

					break;

				case 'modify':
					core::db('update_topic', compact('itemid', 'itemnew'));

					if ($itemnew['.text'] != '')
					{
						$mess = $this->get_default_fields('message', null);
						$mess['topic'] = $itemid;
						$mess['text' ] = $itemnew['.text'];
						core::db('update_message', array('itemid'=>$itemold['first_mess'], 'itemnew'=>$mess));
					}

					break;

				case 'remove':
					core::db('delete_topic', compact('itemid'));
					break;

				default:
					return parent::do_action($entity, $action, $itemid, $itemnew, $itemold, $newitemid);
			}
			break;

		case 'message':
			switch ($action)
			{
				case 'append':
					$newitemid = core::db('insert_message', compact('itemnew'));

					if (isset($itemnew['topic'])) core::db('recalculate_topic', array('id'=>$itemnew['topic']));
					
					break;

				case 'modify':
					core::db('update_message', compact('itemid', 'itemnew'));

					if (isset($itemold['topic'])) core::db('recalculate_topic', array('id'=>$itemold['topic']));
					if (isset($itemnew['topic'])) core::db('recalculate_topic', array('id'=>$itemnew['topic']));

					break;

				case 'remove':
					core::db('delete_message', compact('itemid'));

					if (isset($itemold['topic'])) core::db('recalculate_topic', array('id'=>$itemold['topic']));

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
		case 'forum':
			break;

		case 'topic':
			break;

		case 'message':
			break;
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_format ($entity, $action, $itemid, $item)
{
	$result = parent::do_format($entity, $action, $itemid, $item);
	switch ($entity)
	{
		case 'forum':
			//???
			break;

		case 'topic':
			//???
			break;

		case 'message':
			$result['text'] = htmlspecialchars($result['text']);
//???			if (!in_array($action, array('list', 'massedit'))) .... для скорости можно соптимизировать.
//			if (!in_array($action, array('list', 'massedit')))
			$result['text'] = $this->embed_children($entity, $itemid, $result['text']);
//???			if (!in_array($action, array('list', 'massedit'))) .... и тут для оптимизации по скорости...
//			if (!in_array($action, array('list', 'massedit')))
			if (isset($this->format_ubb_module)) $result['text'] = core::call($this->format_ubb_module, 'format', array('text'=>$result['text']));
			break;
	}
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

?>