<?php defined('CORENINPAGE') or die('Hack!');
//todo: 1. ���������� �������� (membership) ����� �� ��������. ��� �������? �������� �������?
//todo: 2. ���������� ��������������� ������. e-mail, fio...

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!core::depend('list_0')) return;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class admin_groups_0_exception_duplicate extends list_0_exception_duplicate {}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class admin_groups_0 extends list_0 implements dbaware
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected $grant_edit;
protected $grant_view;

protected $default_page;
protected $default_size;
protected $default_skip;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __construct ($configs)
{
	parent::__construct($configs);

	$this->grant_view = core::find_scalar(array($configs), array('grant_view'), null);
	$this->grant_edit = core::find_scalar(array($configs), array('grant_edit'), null);
	if (!isset($this->grant_view)) throw new exception("Misconfig: grant_view.");
	if (!isset($this->grant_edit)) throw new exception("Misconfig: grant_edit.");

	$this->default_page = core::find_scalar(array($configs), array('default_page'), null);
	$this->default_size = core::find_scalar(array($configs), array('default_size'), null);
	$this->default_skip = core::find_scalar(array($configs), array('default_skip'), null);
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
			'group'		=> null,
			'codename'	=> null,
			'comment'	=> null);
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
			$itemid =        core::find_scalar(array($data), array('group', 'id'), null) != '';
			$delete = (bool) core::find_scalar(array($data), array('delete'       ), null);
			if (!$itemid && !$delete) $result = 'append'; else
			if (!$itemid &&  $delete) $result =  null   ; else
			if ( $itemid && !$delete) $result = 'modify'; else
			if ( $itemid &&  $delete) $result = 'remove'; else
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
		case 'group':
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
	$entity = null;
	$action = core::find_scalar(array($args, $_GET, $_POST), array('action'), null);
	$itemid = core::find_scalar(array($args, $_GET, $_POST), array('id'    ), null);

//	no need in children for group (?)
//	if (isset($_GET['child'])) $child = $_GET['child'];
//	else $child = null;

	$submit = !empty($_POST);//todo: ���������� �� ����� ����������� �������� (server[method]==post)

	if (!in_array($entity, array('group'))) $entity = null;
	if (!in_array($action, array('append', 'modify', 'remove', 'list', 'item'))) $action = null;
	if (is_null($action)) $action = isset($itemid) ? 'item' : 'list';

	$filter = array();
	$filter['page'] = core::find_scalar(array($args, $_GET), array('page'), $this->default_page);
	$filter['size'] = core::find_scalar(array($args, $_GET), array('size'), $this->default_size);
	$filter['skip'] = core::find_scalar(array($args, $_GET), array('skip'), $this->default_skip);

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
		case 'group':
			if (!is_array($filter)) $filter = array();
			$page    = isset($filter['page'   ]) ? $filter['page'   ] : null;
			$size    = isset($filter['size'   ]) ? $filter['size'   ] : null;
			$skip    = isset($filter['skip'   ]) ? $filter['skip'   ] : null;

			$count = core::db('select_groups_count', compact('parent', 'itemid'));
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
			$meta = compact('count', 'page', 'size', 'skip', 'offset', 'pagemin', 'pagemax');
			$items = core::db('select_groups_data', compact('parent', 'itemid', 'page', 'size', 'skip', 'count', 'offset'));
			return $items;

		default:
			return parent::do_read_list($entity, $filter, $parent, $itemid, &$meta);
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_handle ($entity, $action, $itemid, $itemold, $submit)
{
	$result = parent::do_handle($entity, $action, $itemid, $itemold, $submit);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_verify ($entity, $action, $itemid, $itemnew, $itemold)
{
	$result = parent::do_verify($entity, $action, $itemid, $itemnew, $itemold);
	switch ($entity)
	{
		case 'group':
			// Verify that codename specified.
			if (!strlen($itemnew['codename']))
				$result[] = 'codename_empty';

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
				case 'append':
					$newitemid = core::db('insert_group', compact('itemnew'));
					break;

				case 'modify':
					core::db('update_group', compact('itemid', 'itemnew'));
					break;

				case 'remove':
					core::db('delete_group', compact('itemid'));
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