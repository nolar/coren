<?php defined('CORENINPAGE') or die('Hack!');
//todo: 1. управление группами (membership) сразу из аккаунта. как сделать? дочерним модулем?
//todo: 2. управление дополнительными полями. e-mail, fio...

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!core::depend('list_0')) return;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class admin_grantgroups_0_exception_duplicate extends list_0_exception_duplicate {}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class admin_grantgroups_0 extends list_0 implements dbaware
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
	return 'group';
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_default_fields ($entity, $parent)
{
	switch ($entity)
	{
		case 'group':
			return array(
			'grant'		=> $parent,
			'group'		=> null,
			'codename'	=> null,
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
		case 'group':
			$result = core::find_scalar(array($data), array('group', 'id'), null);
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
		case 'group':
			$itemid = core::find_scalar(array($data), array('group', 'id'), null) != '';
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
		case 'group':
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
		case 'group':
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
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_parse_request ($args)
{
/*	$entity = null;
	$action = core::find_scalar(array($args, $_GET, $_POST), array('action'), null);
	$itemid = core::find_scalar(array($args, $_GET, $_POST), array('id'    ), null);

//	no need in children for group (?)
//	if (isset($_GET['child'])) $child = $_GET['child'];
//	else $child = null;

	$submit = !empty($_POST);//todo: переделать на более достоверный критерий (server[method]==post)

	if (!in_array($entity, array('group'))) $entity = null;
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
		case 'group':
			$count = core::db('select_groups_count', compact('parent', 'itemid'));
				$pagemin = 1; $pagemax = 1;
				$page = 1;
				$size = $count;
				$offset = 0;
			$meta = compact('count', 'page', 'size', 'skip', 'offset', 'pagemin', 'pagemax');
			$items = core::db('select_groups_data', compact('parent', 'itemid'));

			$assignment = core::db('select_assignment', compact('parent'));
			foreach ($items as $key => $item)
				$items[$key]['assigned'] = is_array($assignment) && in_array($item['group'], $assignment) ? true : 0;
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
		case 'group':
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
		case 'group':
			switch ($action)
			{
				case 'modify':
					$grant = $itemnew['grant'];
					$group = $itemid;
					if ($itemnew['assigned'] != $itemold['assigned'])
						if ($itemnew['assigned'])
							core::db('assign_group', compact('grant', 'group'));
						else
							core::db('revoke_group', compact('grant', 'group'));
					break;

				case 'remove':
					//??? при удалении гранта, мы должны удалить все его присвоения.
					//??? вопрос тоьлко: должны ли мы их удалять в grants или в grantgroups?
					//??? ведь grantgroups может и не вызватсья, если ни одно поле в форме не
					//??? содержит ссылки на дочерний элемент (assignment).
					//??? тем более здесь выполняется для каждого элемента.
					//??? так что лучше, наверрное, удалять один запросов и в admin_grants_0.
//???					$grant = $itemnew['grant'];
//???					core::db('revoke_all_groups', compact('grant'));
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