<?php defined('CORENINPAGE') or die('Hack!');


//todo: 3. либо из запроса добывать дефолтный account_id (но все равно нужен его логин для формы).
//todo: 4. mailer и оповещения по мылу.

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!core::depend('list_0')) return;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class messages_0_exception_duplicate extends list_0_exception_duplicate {}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class messages_0 extends list_0 implements dbaware
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected $grant_post;
protected $grant_read;

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

	$this->grant_post = isset($configs['grant_post']) ? $configs['grant_post'] : null;
	$this->grant_read = isset($configs['grant_read']) ? $configs['grant_read'] : null;
	if (!isset($this->grant_post)) throw new exception("Misconfig: grant_post.");
	if (!isset($this->grant_read)) throw new exception("Misconfig: grant_read.");

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
		case 'message':
			return array(
			'message'		=> null,
			'source'		=> null, 'source_' => null,
			'target'		=> null, 'target_' => null, '.target_logname' => null,
			'ipaddr'		=> null,
			'stamp_post'		=> null,
			'stamp_read'		=> null,
			'subject'		=> null,
			'content'		=> null);
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
		case 'message':
			if (
			    (!isset($submit['subject']) || ($submit['subject'] == '')) &&
			    (!isset($submit['content']) || ($submit['content'] == '')) &&
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
		case 'message':
			$result = core::find_scalar(array($data), array('message', 'id'), null);
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
		case 'message':
			$itemid =        core::find_scalar(array($data), array('message', 'id'), null) != '';
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
		case 'message':
			//!!!
			break;
	}
	if (core::grant($this->grant_read))
	{
		$result[] = 'list';
		$result[] = 'item';
	}
	if (core::grant($this->grant_post))
	{
		$result[] = 'append';
	}
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_itemaccess ($entity, $itemid, $item)
{
	$result = parent::get_itemaccess($entity, $itemid, $item);
	switch ($entity)
	{
		case 'message':
			//!!!
			break;
	}
	if (core::grant($this->grant_read))
	{
		$result[] = 'list';
		$result[] = 'item';
	}
	if (core::grant($this->grant_post))
	{
		$result[] = 'append';
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

	$submit = !empty($_POST);//todo: переделать на более достоверный критерий (server[method]==post)

	if ($action == 'post') $action = 'append';

	if (!in_array($entity, array('message'))) $entity = null;
	if (!in_array($action, array('append', 'modify', 'remove', 'list', 'item'))) $action = null;
	if (is_null($action)) $action = isset($itemid) ? 'item' : 'list';

	$filter = array();
	$filter['page'   ] = core::find_scalar(array($args, $_POST, $_GET), array('page'   ), null);
	$filter['size'   ] = core::find_scalar(array($args, $_POST, $_GET), array('size'   ), null);
	$filter['skip'   ] = core::find_scalar(array($args, $_POST, $_GET), array('skip'   ), null);
	$filter['sorting'] = core::find_scalar(array($args, $_POST, $_GET), array('sorting'), null);
	$filter['reverse'] = core::find_scalar(array($args, $_POST, $_GET), array('reverse'), null);
	$filter['target' ] = core::find_scalar(array($args, $_POST, $_GET), array('target' ), null);

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
		case 'message':
			if (!is_array($filter)) $filter = array();
			$page    = (integer) (isset($filter['page'   ]) ? $filter['page'   ] : $this->default_page   );
			$size    = (integer) (isset($filter['size'   ]) ? $filter['size'   ] : $this->default_size   );
			$skip    = (integer) (isset($filter['skip'   ]) ? $filter['skip'   ] : $this->default_skip   );
			$sorting = (string ) (isset($filter['sorting']) ? $filter['sorting'] : $this->default_sorting);
			$reverse = (string ) (isset($filter['reverse']) ? $filter['reverse'] : $this->default_reverse);

			$account = core::until(null, 'account');
			if (is_null($account)) return array();

			$count = core::db('select_messages_count', compact('parent', 'itemid', 'account'));
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
			$items = core::db('select_messages_data', compact('parent', 'itemid', 'account', 'count', 'page', 'size', 'skip', 'offset', 'pagemin', 'pagemax', 'sorting', 'reverse'));

			$ids_accounts = array();
			foreach ($items as $key => $item)
			{
				if (isset($item['source'])) $ids_accounts[] = $item['source'];
				if (isset($item['target'])) $ids_accounts[] = $item['target'];
			}
			$data_accounts = core::call('account_information', 'accounts_information', array('ids'=>array_unique($ids_accounts)));
			foreach ($items as $key => $item)
			{
				if (isset($item['source']) && array_key_exists($item['source'], $data_accounts)) $items[$key]['source_'] = $data_accounts[$item['source']];
				if (isset($item['target']) && array_key_exists($item['target'], $data_accounts)) $items[$key]['target_'] = $data_accounts[$item['target']];
			}

			//todo: типа так мы помечаем что сообщение было прочитано. на самом деле надо придумать чего поумнее.
			//todo: action не проверяем, так как он не передается (а зря), но нам и не важно. у нас есть только
			//todo: три действия: list, item, append. все остальные, которые читают список (modify/remove), в этом
			//todo: модуле не используются. так что спокойно определяем что action=item по наличию одного itemid.
			if (isset($itemid) && ($itemid != ''))
			{
				core::db('readup_message', compact('itemid'));
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
		case 'message':
			if (in_array($action, array('append')))
			{
				$result['source'] = core::until(null, 'account');
				if (isset($submit['.target_logname']))
					$result['target'] = core::call('account_information', 'resolve_logname', array('logname'=>$submit['.target_logname']));
				else
					$result['target'] = null;

				$ids = array();
				if ($result['source'] != '') $ids[] = $result['source'];
				if ($result['target'] != '') $ids[] = $result['target'];

				$subdata = core::call('account_information', 'accounts_information', compact('ids'));

				$result['source_'] = is_array($subdata) && array_key_exists($result['source'], $subdata) ? $subdata[$result['source']] : null;
				$result['target_'] = is_array($subdata) && array_key_exists($result['target'], $subdata) ? $subdata[$result['target']] : null;

				$result['ipaddr'] = $_SERVER['REMOTE_ADDR'];
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
		case 'message':
			if (is_null($itemnew['target']))
				$result[] = 'target';
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
		case 'message':
			switch ($action)
			{
				case 'append':
					$newitemid = core::db('insert_message', compact('itemnew'));
					break;

//???				case 'modify':
//???					core::db('update_message', compact('itemid', 'itemnew'));
//???					break;

//???				case 'remove':
//???					core::db('delete_message', compact('itemid'));
//???					break;

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
		case 'message':
//???			if (!in_array($action, array('list', 'massedit'))) .... длЯ скорости можно соптимизировать.
			$result['content'] = $this->embed_children($entity, $itemid, $result['content']);
//???			if (!in_array($action, array('list', 'massedit'))) .... и тут длЯ оптимизации по скорости...
			if (isset($this->format_ubb_module)) $result['content'] = core::call($this->format_ubb_module, 'format', array('text'=>$result['content']));
			break;
	}
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

?>