<?php defined('CORENINPAGE') or die('Hack!');
//todo: 1. управление группами (membership) сразу из аккаунта. как сделать? дочерним модулем?
//todo: 2. управление дополнительными полями. e-mail, fio...

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!core::depend('list_0')) return;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class admin_accountsubscriptions_0_exception_duplicate extends list_0_exception_duplicate {}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class admin_accountsubscriptions_0 extends list_0 implements dbaware
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
			'account'	=> $parent,
			'subscription'	=> null,
			'name'		=> null,
			'comment'	=> null,
			'assigned'	=> null);
			break;

		default:
			return parent::get_default_fields($entity, $parent);
	}
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
			$itemid = core::find_scalar(array($data), array('subscription', 'id'), null) != '';
			if ( $itemid) $result = 'modify'; else
			if (!$itemid) $result =  null   ; else
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
		$result[] = 'modify';
		$result[] = 'list';
		$result[] = 'massedit';
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
		$result[] = 'modify';
		$result[] = 'list';
		$result[] = 'massedit';
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function is_action_forced ($entity, $action)
{
	if (in_array($action, array('massedit'))) return true;
	return parent::is_action_forced($entity, $action);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function guess_action_list ($entity, $parentaction, $parent, $submit)
{
	if (in_array($parentaction, array('profile'))) return parent::guess_action_list($entity, 'modify', $parent, $submit);
	if (in_array($parentaction, array('request'))) return parent::guess_action_list($entity, 'append', $parent, $submit);
	return parent::guess_action_list($entity, $parentaction, $parent, $submit);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_parse_request ($args)
{
/*	$entity = null;
	$action = core::find_scalar(array($args, $_GET, $_POST), array('action'), null);
	$itemid = core::find_scalar(array($args, $_GET, $_POST), array('id'    ), null);

//	no need in children for subscription (?)
//	if (isset($_GET['child'])) $child = $_GET['child'];
//	else $child = null;

	$submit = !empty($_POST);//todo: переделать на более достоверный критерий (server[method]==post)

	if (!in_array($entity, array('subscription'))) $entity = null;
	if (!in_array($action, array('append', 'modify', 'remove', 'list', 'item'))) $action = null;
	if (is_null($action)) $action = isset($itemid) ? 'item' : 'list';

	$filter = array();

	return compact('entity', 'action', 'submit', 'filter', 'parent', 'itemid', 'child');
*/
	return null;//NB: this module is not for main content. it is only for child-attaches.
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

			$count = core::db('select_subscriptions_count', compact('parent', 'itemid'));
				$pagemin = 1; $pagemax = 1;
				$page = 1;
				$size = $count;
				$offset = 0;
			$meta = compact('count', 'page', 'size', 'skip', 'offset', 'pagemin', 'pagemax');
			$items = core::db('select_subscriptions_data', compact('parent', 'itemid'));

			$assignment = core::db('select_assignment', compact('parent'));
			foreach ($items as $key => $item)
				$items[$key]['assigned'] = (bool) (is_array($assignment) && in_array($item['subscription'], $assignment));

			return $items;

		default:
			return parent::do_read_list($entity, $filter, $parent, $itemid, &$meta);
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_handle ($entity, $action, $itemid, $itemold, $submit)
{
	$result = parent::do_handle($entity, $action, $itemid, $itemold, $submit);
	if (isset($submit['assigned']) && $submit['assigned'])
		$result['assigned'] = true;
	else
		$result['assigned'] = false;
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_verify ($entity, $action, $itemid, $itemnew, $itemold)
{
	$result = parent::do_verify($entity, $action, $itemid, $itemnew, $itemold);
	switch ($entity)
	{
		case 'subscription':
			//???
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
				case 'modify':
					$account = $itemnew['account'];
					$subscription = $itemid;
					if ($itemnew['assigned'] != $itemold['assigned'])
						if ($itemnew['assigned'])
							core::db('assign_subscription', compact('account', 'subscription'));
						else
							core::db('revoke_subscription', compact('account', 'subscription'));
					break;

				case 'remove':
					//??? при удалении подписки, мы должны удалить все его присвоения.
					//??? вопрос тоьлко: должны ли мы их удалять в accounts или в accountsubscriptions?
					//??? ведь accountsubscriptions может и не вызватсья, если ни одно поле в форме не
					//??? содержит ссылки на дочерний элемент (assignment).
					//??? тем более здесь выполняется для каждого элемента.
					//??? так что лучше, наверрное, удалять один запросов и в admin_accounts_0.
//???					$account = $itemnew['account'];
//???					core::db('revoke_all_subscriptions', compact('account'));
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
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

?>