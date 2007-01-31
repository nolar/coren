<?php defined('CORENINPAGE') or die('Hack!');

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!core::depend('list_0')) return;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class subscriptions_0_exception_duplicate extends list_0_exception_duplicate {}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class subscriptions_0 extends list_0 implements dbaware
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected $grant_edit;
protected $grant_view;
protected $grant_send;

protected $default_page;
protected $default_size;
protected $default_skip;
protected $default_sorting;
protected $default_reverse;

protected $mailer;

protected $format_ubb_module;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __construct ($configs)
{
	parent::__construct($configs);

	$this->grant_view = isset($configs['grant_view']) ? $configs['grant_view'] : null;
	$this->grant_edit = isset($configs['grant_edit']) ? $configs['grant_edit'] : null;
	$this->grant_send = isset($configs['grant_send']) ? $configs['grant_send'] : null;
	if (!isset($this->grant_view)) throw new exception("Misconfig: grant_view.");
	if (!isset($this->grant_edit)) throw new exception("Misconfig: grant_edit.");
	if (!isset($this->grant_send)) throw new exception("Misconfig: grant_send.");

	$this->default_page    = core::find_scalar(array($configs), array('default_page'   ), null);
	$this->default_size    = core::find_scalar(array($configs), array('default_size'   ), null);
	$this->default_skip    = core::find_scalar(array($configs), array('default_skip'   ), null);
	$this->default_sorting = core::find_scalar(array($configs), array('default_sorting'), null);
	$this->default_reverse = core::find_scalar(array($configs), array('default_reverse'), null);

	$this->mailer = core::find_scalar(array($configs), array('mailer'), null);
	if (!isset($this->mailer)) throw new exception('misconfig: mailer');

	$this->format_ubb_module = core::find_scalar(array($configs), array('format_ubb_module'), null);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_default_entity ()
{
	return 'subscription';
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_default_fields ($entity, $parent)
{
	switch ($entity)
	{
		case 'subscription':
			return array(
			'subscription'		=> null,
			'name'			=> null,
			'comment'		=> null,
			'.subject'		=> null,
			'.message'		=> null,
			'.headers'		=> null);
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
		case 'subscription':
			if (
			    (!isset($submit['name'   ]) || ($submit['name'   ] == '')) &&
			    (!isset($submit['comment']) || ($submit['comment'] == '')) &&
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
		case 'subscription':
			$result = core::find_scalar(array($data), array('subscription', 'id'), null);
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
		case 'subscription':
			$itemid =        core::find_scalar(array($data), array('subscription', 'id'), null) != '';
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
		case 'subscription':
			//!!!
			break;
	}
	$result[] = 'sendnow';
	if (core::grant($this->grant_send) || core::grant($this->grant_edit))
	{
		$result[] = 'send';
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
		case 'subscription':
			//!!!
			break;
	}
	$result[] = 'sendnow';
	if (core::grant($this->grant_send) || core::grant($this->grant_edit))
	{
		$result[] = 'send';
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

protected function is_action_atomic ($entity, $action)
{
	if (in_array($action, array('send', 'sendnow'))) return true;
	return parent::is_action_atomic($entity, $action);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function is_action_orphan ($entity, $action)
{
	return parent::is_action_orphan($entity, $action);
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

	if (!in_array($entity, array('subscription'))) $entity = null;
	if (!in_array($action, array('append', 'modify', 'remove', 'list', 'item', 'send'))) $action = null;//NB: deny 'sendnow' here!
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
		case 'subscription':
			if (!is_array($filter)) $filter = array();
			$page    = (integer) (isset($filter['page'   ]) ? $filter['page'   ] : $this->default_page   );
			$size    = (integer) (isset($filter['size'   ]) ? $filter['size'   ] : $this->default_size   );
			$skip    = (integer) (isset($filter['skip'   ]) ? $filter['skip'   ] : $this->default_skip   );
			$sorting = (string ) (isset($filter['sorting']) ? $filter['sorting'] : $this->default_sorting);
			$reverse = (string ) (isset($filter['reverse']) ? $filter['reverse'] : $this->default_reverse);

			$count = core::db('select_subscriptions_count', compact('parent', 'itemid'));
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
			$items = core::db('select_subscriptions_data', compact('parent', 'itemid', 'page', 'size', 'skip', 'count', 'offset', 'pagemin', 'pagemax', 'sorting', 'reverse'));

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
		case 'subscription':
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
		case 'subscription':
			//!!!
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
		case 'subscription':
			switch ($action)
			{
				case 'send':
				case 'sendnow':
					$abonentship = core::db('select_abonentship', array('itemid'=>$itemid));
					$information = core::call('account_information', 'accounts_information', array('ids'=>array_unique($abonentship)));
					$to = array(); foreach ($information as $val) $to[] = $val['email'];

					$subject = $itemnew['.subject'];
					$message = $itemnew['.message'];
					$headers = $itemnew['.headers'];
					$priority = 0;
					core::call($this->mailer, 'inject', compact('priority', 'to', 'subject', 'message', 'headers'));

					break;

				case 'append':
					$newitemid = core::db('insert_subscription', compact('itemnew'));
					break;

				case 'modify':
					core::db('update_subscription', compact('itemid', 'itemnew'));
					break;

				case 'remove':
					core::db('delete_subscription', compact('itemid'));
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
		case 'subscription':
			break;
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_format ($entity, $action, $itemid, $item)
{
	$result = parent::do_format($entity, $action, $itemid, $item);
	switch ($entity)
	{
		case 'subscription':
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
}

?>