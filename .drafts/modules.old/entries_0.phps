<?php defined('CORENINPAGE') or die('Hack!');

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class entries_0_exception_duplicate extends exception {}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class entries_0 extends module
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

static function meta_configs ()
{
	return array('macrofix', 'tablefix',
		'grant_view', 'grant_edit',
		'default_action',
		'default_linkid',
		'default_fileid',
		'default_pictid',
		'default_itemid',
		'default_archive',
		'default_sorting',
		'default_reverse',
		'default_page',
		'default_size',
		'itemlinksignore',
		'itemfilesignore',
		'itempictsignore',
		'segment_download_file',
		'segment_download_pict',
		'segment_upload_file',
		'segment_upload_pict',
		'segment_download',
		'segment_upload',
		'segment',
		'timeout_download_file',
		'timeout_download_pict',
		'timeout_upload_file',
		'timeout_upload_pict',
		'timeout_download',
		'timeout_upload',
		'timeout');
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

static function meta_templates ()
{
	return array(
		'itemlink_deny', 'itemlink_absent', 'itemlink_go',
		'itemfile_deny', 'itemfile_absent',
		'itempict_deny', 'itempict_absent',

		'view_deny', 'view_absent', 'view_item',
		'view_itemlink_empty', 'view_itemlink_row', 'view_itemlink_glue', 'view_itemlink_all',
		'view_itemfile_empty', 'view_itemfile_row', 'view_itemfile_glue', 'view_itemfile_all',
		'view_itempict_empty', 'view_itempict_row', 'view_itempict_glue', 'view_itempict_all',

		'list_deny', 'list_empty', 'list_row', 'list_glue', 'list_all',

		'append_deny', 'append_form', 'append_success',
		'append_itemlink_empty', 'append_itemlink_row', 'append_itemlink_glue', 'append_itemlink_all',
		'append_itemfile_empty', 'append_itemfile_row', 'append_itemfile_glue', 'append_itemfile_all',
		'append_itempict_empty', 'append_itempict_row', 'append_itempict_glue', 'append_itempict_all',

		'modify_deny', 'modify_absent', 'modify_form', 'modify_success',
		'modify_itemlink_empty', 'modify_itemlink_row', 'modify_itemlink_glue', 'modify_itemlink_all',
		'modify_itemfile_empty', 'modify_itemfile_row', 'modify_itemfile_glue', 'modify_itemfile_all',
		'modify_itempict_empty', 'modify_itempict_row', 'modify_itempict_glue', 'modify_itempict_all',
		
		'remove_deny', 'remove_absent', 'remove_form', 'remove_success',
		'remove_itemlink_empty', 'remove_itemlink_row', 'remove_itemlink_glue', 'remove_itemlink_all',
		'remove_itemfile_empty', 'remove_itemfile_row', 'remove_itemfile_glue', 'remove_itemfile_all',
		'remove_itempict_empty', 'remove_itempict_row', 'remove_itempict_glue', 'remove_itempict_all'
		);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

static function meta_functions ()
{
	return array('main');//???
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

private $__format_itemid = null;
private $__format_links;
private $__format_files;
private $__format_picts;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __construct ($configs)
{
	$this->default_action  = isset($configs['default_action' ]) ? $configs['default_action' ] : null;
	$this->default_linkid  = isset($configs['default_linkid' ]) ? $configs['default_linkid' ] : null;
	$this->default_fileid  = isset($configs['default_fileid' ]) ? $configs['default_fileid' ] : null;
	$this->default_pictid  = isset($configs['default_pictid' ]) ? $configs['default_pictid' ] : null;
	$this->default_itemid  = isset($configs['default_itemid' ]) ? $configs['default_itemid' ] : null;
	$this->default_archive = isset($configs['default_archive']) ? $configs['default_archive'] : null;
	$this->default_sorting = isset($configs['default_sorting']) ? $configs['default_sorting'] : null;
	$this->default_reverse = isset($configs['default_reverse']) ? $configs['default_reverse'] : null;
	$this->default_page    = isset($configs['default_page'   ]) ? $configs['default_page'   ] : 1;
	$this->default_size    = isset($configs['default_size'   ]) ? $configs['default_size'   ] : 0;
	$this->grant_view = isset($configs['grant_view']) ? $configs['grant_view'] : '+';
	$this->grant_edit = isset($configs['grant_edit']) ? $configs['grant_edit'] : '-';
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//!!!!! удалить все протофункции!

function db_work_begin () {}
function db_work_commit () {}
function db_work_rollback () {}

function db_pseudo_item () {}
function db_select_items_count ($filters) {}
function db_select_items_page  ($filters, $sorting, $reverse, $page, $size, $offset, $count) {}
function db_select_items_byids ($filters, $itemids) {}
function db_insert_item ($data) {}
function db_update_item ($itemid, $data) {}
function db_delete_item ($itemid) {}

function db_pseudo_itemlink () {}
function db_select_itemlinks ($itemids) {}
function db_update_itemlinks ($itemid, $total, $delete, $update, $insert) {}
function db_delete_itemlinks ($itemid) {}

function db_pseudo_itemfile () {}
function db_select_itemfiles ($itemids) {}
function db_update_itemfiles ($itemid, $total, $delete, $update, $insert) {}
function db_delete_itemfiles ($itemid) {}

function db_pseudo_itempict () {}
function db_select_itempicts ($itemids) {}
function db_update_itempicts ($itemid, $total, $delete, $update, $insert) {}
function db_delete_itempicts ($itemid) {}

function db_select_itemlink_info ($itemid, $linkid) {}

function db_select_itemfile_info ($itemid, $fileid) {}
function db_select_itemfile_data ($itemid, $fileid, $offset, $segment) {}
function db_upload_itemfile_data ($itemid, $fileid, $data) {}
function db_update_itemfile_data ($itemid, $fileid) {}
function db_insert_itemfile_data () {}

function db_select_itempict_info ($itemid, $pictid) {}
function db_select_itempict_data ($itemid, $pictid, $offset, $segment) {}
function db_upload_itempict_data ($itemid, $pictid, $data) {}
function db_update_itempict_data ($itemid, $pictid) {}
function db_insert_itempict_data () {}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function main ($data = null)
{
	//...
	$action = $this->__find_scalar($data, $_GET, $_POST, 'action',  null , $this->default_action);
	$linkid = $this->__find_scalar($data, $_GET, $_POST, 'linkid', 'link', $this->default_linkid);
	$fileid = $this->__find_scalar($data, $_GET, $_POST, 'fileid', 'file', $this->default_fileid);
	$pictid = $this->__find_scalar($data, $_GET, $_POST, 'pictid', 'pict', $this->default_pictid);
	$itemid = $this->__find_scalar($data, $_GET, $_POST, 'itemid', 'item', $this->default_itemid);
	if (!in_array($action, array('append', 'modify', 'remove', 'list', 'view', 'link', 'file', 'pict'))) $action = null;
	if (is_null($action) && !is_null($linkid)) $action = 'link';
	if (is_null($action) && !is_null($fileid)) $action = 'file';
	if (is_null($action) && !is_null($pictid)) $action = 'pict';
	if (is_null($action) && !is_null($itemid)) $action = 'view';
	if (is_null($action)                     ) $action = 'list';

	//...
	switch ($action)
	{
		case 'link'  : echo $this->do_link($data, $itemid, $linkid); break;
		case 'file'  : echo $this->do_file($data, $itemid, $fileid); break;
		case 'pict'  : echo $this->do_pict($data, $itemid, $pictid); break;
		case 'view'  : echo $this->do_view($data, $itemid); break;
		case 'list'  : echo $this->do_list($data); break;
		case 'append': echo $this->do_append($data, $itemid); break;
		case 'modify': echo $this->do_modify($data, $itemid); break;
		case 'remove': echo $this->do_remove($data, $itemid); break;
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function items ($data = null)
{
	return $this->main($data);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function item_list ($data = null)
{
	$data['action'] = 'list';
	return $this->main($data);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function item_view ($data = null)
{
	$data['action'] = 'view';
	return $this->main($data);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function item_append ($data = null)
{
	$data['action'] = 'append';
	return $this->main($data);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function item_modify ($data = null)
{
	$data['action'] = 'modify';
	return $this->main($data);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function item_remove ($data = null)
{
	$data['action'] = 'remove';
	return $this->main($data);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function do_link ($data, $itemid, $linkid)
{
	//...
	$can = array();
	$can['view'] = core::grant($this->grant_view);
	$can['edit'] = core::grant($this->grant_edit);

	//...
	if (!$can['view'] && !$can['edit'])
	{
		$args = array();
		$args['can'] = $can;
		$args['itemid'] = $itemid;
		$args['linkid'] = $linkid;
		return core::template('itemlink_deny', $args);
	}

	//...
	$info = $this->db_select_itemlink_info($itemid, $linkid);

	//...
	if (is_null($info))
	{
		$args = array();
		$args['can'] = $can;
		$args['itemid'] = $itemid;
		$args['linkid'] = $linkid;
		return core::template('itemlink_absent', $args);
	}

	//...
	$args = array();
	$args['can'] = $can;
	$args['itemid'] = $itemid;
	$args['linkid'] = $linkid;
	$args['url'   ] = $info['url'];
	return core::template('itemlink_go', $args);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function do_file ($data, $itemid, $fileid)
{
	//...
	$can = array();
	$can['view'] = core::grant($this->grant_view);
	$can['edit'] = core::grant($this->grant_edit);

	//...
	if (!$can['view'] && !$can['edit'])
	{
		$args = array();
		$args['can'] = $can;
		$args['itemid'] = $itemid;
		$args['fileid'] = $fileid;
		return core::template('itemfile_deny', $args);
	}

	//...
	$info = $this->db_select_itemfile_info($itemid, $fileid);

	//...
	if (is_null($info))
	{
		$args = array();
		$args['can'] = $can;
		$args['itemid'] = $itemid;
		$args['fileid'] = $fileid;
		return core::template('itemfile_absent', $args);
	}

	//...
	$this->__download('file', $itemid, $fileid, $info, 'attachment');
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function do_pict ($data, $itemid, $pictid)
{
	//...
	$can = array();
	$can['view'] = core::grant($this->grant_view);
	$can['edit'] = core::grant($this->grant_edit);

	//...
	if (!$can['view'] && !$can['edit'])
	{
		$args = array();
		$args['can'] = $can;
		$args['itemid'] = $itemid;
		$args['pictid'] = $pictid;
		return core::template('itempict_deny', $args);
	}

	//...
	$info = $this->db_select_itempict_info($itemid, $pictid);

	//...
	if (is_null($info))
	{
		$args = array();
		$args['can'] = $can;
		$args['itemid'] = $itemid;
		$args['pictid'] = $pictid;
		return core::template('itempict_absent', $args);
	}


	//...
	$this->__download('pict', $itemid, $pictid, $info, 'inline');
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function do_view ($data, $itemid)
{
	//...
	$can = array();
	$can['view'] = core::grant($this->grant_view);
	$can['edit'] = core::grant($this->grant_edit);

	//...
	if (!$can['view'] && !$can['edit'])
	{
		$args = array();
		$args['can'] = $can;
		$args['itemid'] = $itemid;
		return core::template('view_deny', $args);
	}

	//...
	$archive = $this->__find_scalar($data, $_GET, $_POST, 'archive', null, $this->default_archive);
	$filters = $this->__create_filters($can['edit'], $archive);

	//...
	$itemids = array($itemid);
	$item = is_null($itemid) ? array() : $this->db_select_items_byids($filters, $itemids);
	$item = array_key_exists($itemid, $item) ? $item[$itemid] : null;

	//...
	if (is_null($item))
	{
		$args = array();
		$args['can'   ] = $can;
		$args['itemid'] = $itemid;
		return core::template('view_absent', $args);
	}

	//...
	$grants = core::grants(true);
	$itemlinks = $this->db_select_itemlinks($itemids);
	$itemfiles = $this->db_select_itemfiles($itemids);
	$itempicts = $this->db_select_itempicts($itemids);

	//...
	$args = array();
	$args['can'          ] = $can;
	$args['grants'       ] = $grants;
	$args['item'         ] = $item;
	$args['itemid'       ] = $itemid;
	$args['itemlinks'    ] = array_key_exists($itemid, $itemlinks) ? $itemlinks[$itemid] : array();
	$args['itemfiles'    ] = array_key_exists($itemid, $itemfiles) ? $itemfiles[$itemid] : array();
	$args['itempicts'    ] = array_key_exists($itemid, $itempicts) ? $itempicts[$itemid] : array();
	$args['itemlinksview'] = $this->_itemlink_list('view_itemlink_', $can, $args['itemlinks'], $itemid);
	$args['itemfilesview'] = $this->_itemfile_list('view_itemfile_', $can, $args['itemfiles'], $itemid);
	$args['itempictsview'] = $this->_itempict_list('view_itempict_', $can, $args['itempicts'], $itemid);
	$args['item']['fulltext_format'] = $this->__format($args['item']['fulltext'], $itemid, $itemlinks, $itemfiles, $itempicts);//!!!
	return core::template('view_item', $args);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function do_list ($data)
{
	//...
	$can = array();
	$can['view'] = core::grant($this->grant_view);
	$can['edit'] = core::grant($this->grant_edit);

	//...
	if (!$can['view'] && !$can['edit'])
	{
		$args = array();
		$args['can'] = $can;
		return core::template('list_deny', $args);
	}

	//...
	$archive = $this->__find_scalar($data, $_GET, $_POST, 'archive', null, $this->default_archive);
	$sorting = $this->__find_scalar($data, $_GET, $_POST, 'sorting', null, $this->default_sorting);
	$reverse = $this->__find_scalar($data, $_GET, $_POST, 'reverse', null, $this->default_reverse);
	$filters = $this->__create_filters($can['edit'], $archive);

	//...
	$page = $this->__find_scalar($data, $_GET, $_POST, 'page', null, null);
	$size = $this->__find_scalar($data, $_GET, $_POST, 'size', null, null);
	if (is_null($page) || ($page === '')) $page = $this->default_page;
	if (is_null($size) || ($size === '')) $size = $this->default_size;
	$page = (integer) $page;
	$size = (integer) $size;

	//...
//!!!	$count = $this->db_select_items_count($filters);
	$count = core::db('db_select_items_count', array('filters'=>$filters));
	if ($size > 0)
	{
		$pagemin = 1; $pagemax = floor($count / $size) + ($count % $size ? 1 : 0);
		if ($page > $pagemax) $page = $pagemax;
		if ($page < $pagemin) $page = $pagemin;
		$offset = ($page - 1) * $size;
	} else
	{
		$pagemin = 1; $pagemax = 1;
		$page = 1;
		$size = $count;
		$offset = 0;
	}

	//...
//!!!	$items = $this->db_select_items_page($filters, $sorting, $reverse, $page, $size, $offset, $count);
	$items = core::db('db_select_items_page', compact('filters', 'sorting', 'reverse', 'page', 'size', 'offset', 'count'));

	//...
	if (empty($items))
	{
		//...
		$args = array();
		$args['can'      ] = $can;
		$args['page'     ] = $page;
		$args['pagemin'  ] = $pagemin;
		$args['pagemax'  ] = $pagemax;
		$args['realcount'] = $count;
		$args['pagecount'] = count($items);
		return core::template('list_empty', $args);
	}

	//...
	$itemids = array_keys($items);

	//...
	$grants = core::grants(true);
	$itemlinks = $this->db_select_itemlinks($itemids); $itemlinks = array();//!!!
	$itemfiles = $this->db_select_itemfiles($itemids); $itemfiles = array();//!!!
	$itempicts = $this->db_select_itempicts($itemids); $itempicts = array();//!!!

	//...
	$rows = array();
	$pageindex = 1;
	$pagecount = count($items);
	foreach ($items as $itemid => $item)
	{
		//...
		$args = array();
		$args['can'          ] = $can;
		$args['page'         ] = $page;
		$args['pagemin'      ] = $pagemin;
		$args['pagemax'      ] = $pagemax;
		$args['realindex'    ] = ($page - 1) * $size + $pageindex;
		$args['realcount'    ] = $count;
		$args['pageindex'    ] = $pageindex;
		$args['pagecount'    ] = $pagecount;
		$args['grants'       ] = $grants;
		$args['item'         ] = $item;
		$args['itemid'       ] = $itemid;
		$args['itemlinks'    ] = array_key_exists($itemid, $itemlinks) ? $itemlinks[$itemid] : array();
		$args['itemfiles'    ] = array_key_exists($itemid, $itemfiles) ? $itemfiles[$itemid] : array();
		$args['itempicts'    ] = array_key_exists($itemid, $itempicts) ? $itempicts[$itemid] : array();
		$args['itemlinksview'] = $this->_itemlink_list('list_row_link_', $can, $args['itemlinks'], $itemid);
		$args['itemfilesview'] = $this->_itemfile_list('list_row_file_', $can, $args['itemfiles'], $itemid);
		$args['itempictsview'] = $this->_itempict_list('list_row_pict_', $can, $args['itempicts'], $itemid);
		$row = core::template('list_row', $args);

		//...
		if ($row == '') break;

		//...
		$rows[] = $row;

		//...
		$pageindex++;
	}

	//...
	$rows = implode(core::template('list_glue'), $rows);

	//...
	$args = array();
	$args['can'      ] = $can;
	$args['page'     ] = $page;
	$args['pagemin'  ] = $pagemin;
	$args['pagemax'  ] = $pagemax;
	$args['realcount'] = $count;
	$args['pagecount'] = $pagecount;
	$args['grants'   ] = $grants;
	$args['items'    ] = $items;
	$args['itemlinks'] = $itemlinks;
	$args['itemfiles'] = $itemfiles;
	$args['itempicts'] = $itempicts;
	$args['rows'     ] = $rows;
	$view = core::template('list_all', $args);

	// Return this final format of rows, or just a rows without format (if format is empty).
	return ($view == '') ? $rows : $view;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function do_append ($data, $itemid)
{
	//...
	$can = array();
	$can['view'] = core::grant($this->grant_view);
	$can['edit'] = core::grant($this->grant_edit);

	//...
	if (!$can['edit'])
	{
		$args = array();
		$args['can'] = $can;
		$args['itemid'] = $itemid;
		return core::template('append_deny', $args);
	}

	//...
	$item = $this->db_pseudo_item();
	$itemid = null; // force it to be null to prevent substituting wrong id in initial request

	//...
	$grants = core::grants(true);
	$itemlinks = array();
	$itemfiles = array();
	$itempicts = array();

	// Emulate uploaded files as if they already were in database.
	if (!$this->__find_scalar($data, $_GET, $_POST, 'itemfilesignore', null, core::config('itemfilesignore')))
	if (isset($_FILES['itemfile']) && is_array($_FILES['itemfile']['tmp_name']))
	foreach ($_FILES['itemfile']['tmp_name'] as $key => $val)
	if (isset($_FILES['itemfile']['tmp_name'][$key]['attachment']) && is_scalar($_FILES['itemfile']['tmp_name'][$key]['attachment']))
	if (is_uploaded_file($_FILES['itemfile']['tmp_name'][$key]['attachment']))
	{
		// Force POST to contain information about uploaded file.
		if (!isset($_POST['itemfile']      ) || !is_array($_POST['itemfile']      )) $_POST['itemfile']       = array();
		if (!isset($_POST['itemfile'][$key]) || !is_array($_POST['itemfile'][$key])) $_POST['itemfile'][$key] = array();

		// Emulate that just uploaded file already exists in database.
		$_POST['itemfile'][$key]['file'] = $this->__upload('file', $itemid, isset($_POST['itemfile'][$key]['file']) ? $_POST['itemfile'][$key]['file'] : null, $_FILES['itemfile']['tmp_name'][$key]['attachment']);

		// Fill other information about file if it does not exist yet.
		if (!isset($_POST['itemfile'][$key]['filename'])) $_POST['itemfile'][$key]['filename'] = $_FILES['itemfile']['name'][$key]['attachment'];
		if (!isset($_POST['itemfile'][$key]['filesize'])) $_POST['itemfile'][$key]['filesize'] = $_FILES['itemfile']['size'][$key]['attachment'];
		if (!isset($_POST['itemfile'][$key]['mimetype'])) $_POST['itemfile'][$key]['mimetype'] = $_FILES['itemfile']['type'][$key]['attachment'];
	}

	// Emulate uploaded picts as if they already were in database.
	if (!$this->__find_scalar($data, $_GET, $_POST, 'itempictsignore', null, core::config('itempictsignore')))
	if (isset($_FILES['itempict']) && is_array($_FILES['itempict']['tmp_name']))
	foreach ($_FILES['itempict']['tmp_name'] as $key => $val)
	if (isset($_FILES['itempict']['tmp_name'][$key]['attachment']) && is_scalar($_FILES['itempict']['tmp_name'][$key]['attachment']))
	if (is_uploaded_file($_FILES['itempict']['tmp_name'][$key]['attachment']))
	{
		// Force POST to contain information about uploaded pict.
		if (!isset($_POST['itempict']      ) || !is_array($_POST['itempict']      )) $_POST['itempict']       = array();
		if (!isset($_POST['itempict'][$key]) || !is_array($_POST['itempict'][$key])) $_POST['itempict'][$key] = array();

		// Emulate that just uploaded pict already exists in database.
		$_POST['itempict'][$key]['pict'] = $this->__upload('pict', $itemid, isset($_POST['itempict'][$key]['pict']) ? $_POST['itempict'][$key]['pict'] : null, $_FILES['itempict']['tmp_name'][$key]['attachment']);

		// Fill other information about pict if it does not exist yet.
		if (!isset($_POST['itempict'][$key]['filename'])) $_POST['itempict'][$key]['filename'] = $_FILES['itempict']['name'][$key]['attachment'];
		if (!isset($_POST['itempict'][$key]['filesize'])) $_POST['itempict'][$key]['filesize'] = $_FILES['itempict']['size'][$key]['attachment'];

		// Fill graphical information about pict if it does not exist yet.
		$uploadinfo = @getimagesize($_FILES['itempict']['tmp_name'][$key]['attachment']);
		if ($uploadinfo === false)
		{
			if (!isset($_POST['itempict'][$key]['xsize'   ])) $_POST['itempict'][$key]['xsize'   ] = null;
			if (!isset($_POST['itempict'][$key]['ysize'   ])) $_POST['itempict'][$key]['ysize'   ] = null;
			if (!isset($_POST['itempict'][$key]['mimetype'])) $_POST['itempict'][$key]['mimetype'] = $_FILES['itempict']['type'][$key]['attachment'];
		} else
		{
			if (!isset($_POST['itempict'][$key]['xsize'   ])) $_POST['itempict'][$key]['xsize'   ] = $uploadinfo[0];
			if (!isset($_POST['itempict'][$key]['ysize'   ])) $_POST['itempict'][$key]['ysize'   ] = $uploadinfo[1];
			if (!isset($_POST['itempict'][$key]['mimetype'])) $_POST['itempict'][$key]['mimetype'] = image_type_to_mime_type($uploadinfo[2]);
		}
	}

	//...
	$itemlinkup = $this->__find_scalar(null, $_GET, $_POST, 'itemlinkup', null, null);
	$itemlinkdn = $this->__find_scalar(null, $_GET, $_POST, 'itemlinkdn', null, null);
	$itemfileup = $this->__find_scalar(null, $_GET, $_POST, 'itemfileup', null, null);
	$itemfiledn = $this->__find_scalar(null, $_GET, $_POST, 'itemfiledn', null, null);
	$itempictup = $this->__find_scalar(null, $_GET, $_POST, 'itempictup', null, null);
	$itempictdn = $this->__find_scalar(null, $_GET, $_POST, 'itempictdn', null, null);
	$submitted = !empty($_POST);

	//...
	$form = true;
	$args = array();
	$args['can'      ] = $can;
	$args['grants'   ] = $grants;
	$args['item'     ] = $item;
	$args['itemid'   ] = $itemid;
	$args['itemlinks'] = array_key_exists($itemid, $itemlinks) ? $itemlinks[$itemid] : array();
	$args['itemfiles'] = array_key_exists($itemid, $itemfiles) ? $itemfiles[$itemid] : array();
	$args['itempicts'] = array_key_exists($itemid, $itempicts) ? $itempicts[$itemid] : array();
	$args['errors'   ] = array(
				'already_exists'	=> false
				);//???

	//...
	if ($submitted)
	{
		//...
		if (isset($_POST['item']) && is_array($_POST['item']))
			foreach ($_POST['item'] as $key => $val)
				if (is_scalar($val))
					$args['item'][$key] = $val;

		//...
		if (!$this->__find_scalar($data, $_GET, $_POST, 'itemlinksignore', null, core::config('itemlinksignore')))
		{
			$args['itemlinks'] = $this->__grabsubs(
				isset($_POST['itemlink']) ? $_POST['itemlink'] : array(),
				array_key_exists($itemid, $itemlinks) ? $itemlinks[$itemid] : array(),
				'link', $this->db_pseudo_itemlink());
		}

		//...
		if (!$this->__find_scalar($data, $_GET, $_POST, 'itemfilesignore', null, core::config('itemfilesignore')))
		{
			$args['itemfiles'] = $this->__grabsubs(
				isset($_POST['itemfile']) ? $_POST['itemfile'] : array(),
				array_key_exists($itemid, $itemfiles) ? $itemfiles[$itemid] : array(),
				'file', $this->db_pseudo_itemfile());
		}

		//...
		if (!$this->__find_scalar($data, $_GET, $_POST, 'itempictsignore', null, core::config('itempictsignore')))
		{
			$args['itempicts'] = $this->__grabsubs(
				isset($_POST['itempict']) ? $_POST['itempict'] : array(),
				array_key_exists($itemid, $itempicts) ? $itempicts[$itemid] : array(),
				'pict', $this->db_pseudo_itempict());
		}
	}

	//...(even when not-post: because moving trough GET possible)
	$this->__reordersubs($args['itemlinks'], $itemlinkup, $itemlinkdn);
	$this->__reordersubs($args['itemfiles'], $itemfileup, $itemfiledn);
	$this->__reordersubs($args['itempicts'], $itempictup, $itempictdn);

	//...
	if ($submitted)
	{
		//!!!todo: verifications

		// Determine if we got no errors in fields.
		$ok = true;
		foreach ($args['errors'] as $error)
			$ok = $ok && !$error;

		// When every fields have correct and acceptable values, do operation.
		if ($ok)
		{
			// Start transaction.
			$this->db_work_begin();

			// Try to execute operation on data. Rollback on exceptions.
			try
			{
				//...
				$itemid = $this->db_insert_item($args['item']);
				$args['itemid'] = $itemid;

				//...
				list($itemlinks_delete, $itemlinks_update, $itemlinks_insert)
					= $this->__splitsubs ($args['itemlinks'], array_key_exists($itemid, $itemlinks) ? $itemlinks[$itemid] : array(), 'link');
				list($itemfiles_delete, $itemfiles_update, $itemfiles_insert)
					= $this->__splitsubs ($args['itemfiles'], array_key_exists($itemid, $itemfiles) ? $itemfiles[$itemid] : array(), 'file');
				list($itempicts_delete, $itempicts_update, $itempicts_insert)
					= $this->__splitsubs ($args['itempicts'], array_key_exists($itemid, $itempicts) ? $itempicts[$itemid] : array(), 'pict');

				//...
				$this->db_update_itemlinks($itemid, $args['itemlinks'],
					$itemlinks_delete, $itemlinks_update, $itemlinks_insert);
				$this->db_update_itemfiles($itemid, $args['itemfiles'],
					$itemfiles_delete, $itemfiles_update, $itemfiles_insert);
				$this->db_update_itempicts($itemid, $args['itempicts'],
					$itempicts_delete, $itempicts_update, $itempicts_insert);

				// Commit transaction.
				$this->db_work_commit();

				// And only after successful commit, switch from form to success report.
				$form = false;
			}
			catch (entries_0_exception_duplicate $exception)
			{
				//...
				try { $this->db_work_rollback(); }
				catch (exception $exception) {}

				//...
				$args['errors']['already_exists'] = true;
			}
			catch (exception $exception)
			{
				//...
				try { $this->db_work_rollback(); }
				catch (exception $exception) {}

				//...
				throw $exception;
			}
		}
	}

	//...
	$args['itemlinksview'] = $this->_itemlink_list('append_itemlink_', $can, $args['itemlinks'], $itemid);
	$args['itemfilesview'] = $this->_itemfile_list('append_itemfile_', $can, $args['itemfiles'], $itemid);
	$args['itempictsview'] = $this->_itempict_list('append_itempict_', $can, $args['itempicts'], $itemid);
	return core::template($form ? 'append_form' : 'append_success', $args);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function do_modify ($data, $itemid)
{
	//...
	$can = array();
	$can['view'] = core::grant($this->grant_view);
	$can['edit'] = core::grant($this->grant_edit);

	//...
	if (!$can['edit'])
	{
		$args = array();
		$args['can'] = $can;
		$args['itemid'] = $itemid;
		return core::template('modify_deny', $args);
	}

	//...
	$archive = isset($data['archive']) && is_scalar($data['archive']) ? $data['archive'] : (isset($_GET['archive']) && is_scalar($_GET['archive']) ? $_GET['archive'] : core::config('default_archive'));
	$filters = $this->__create_filters($can['edit'], $archive);

	//...
	$itemids = array($itemid);
	$item = is_null($itemid) ? array() : $this->db_select_items_byids($filters, $itemids);
	$item = array_key_exists($itemid, $item) ? $item[$itemid] : null;

	//...
	if (is_null($item))
	{
		$args = array();
		$args['can'   ] = $can;
		$args['itemid'] = $itemid;
		return core::template('modify_absent', $args);
	}

	//...
	$grants = core::grants(true);
	$itemlinks = $this->db_select_itemlinks($itemids);
	$itemfiles = $this->db_select_itemfiles($itemids);
	$itempicts = $this->db_select_itempicts($itemids);

	// Emulate uploaded files as if they already were in database.
	if (!$this->__find_scalar($data, $_GET, $_POST, 'itemfilesignore', null, core::config('itemfilesignore')))
	if (isset($_FILES['itemfile']) && is_array($_FILES['itemfile']['tmp_name']))
	foreach ($_FILES['itemfile']['tmp_name'] as $key => $val)
	if (isset($_FILES['itemfile']['tmp_name'][$key]['attachment']) && is_scalar($_FILES['itemfile']['tmp_name'][$key]['attachment']))
	if (is_uploaded_file($_FILES['itemfile']['tmp_name'][$key]['attachment']))
	{
		// Force POST to contain information about uploaded file.
		if (!isset($_POST['itemfile']      ) || !is_array($_POST['itemfile']      )) $_POST['itemfile']       = array();
		if (!isset($_POST['itemfile'][$key]) || !is_array($_POST['itemfile'][$key])) $_POST['itemfile'][$key] = array();

		// Emulate that just uploaded file already exists in database.
		$_POST['itemfile'][$key]['file'] = $this->__upload('file', $itemid, isset($_POST['itemfile'][$key]['file']) ? $_POST['itemfile'][$key]['file'] : null, $_FILES['itemfile']['tmp_name'][$key]['attachment']);

		// Fill other information about file if it does not exist yet.
		if (!isset($_POST['itemfile'][$key]['filename'])) $_POST['itemfile'][$key]['filename'] = $_FILES['itemfile']['name'][$key]['attachment'];
		if (!isset($_POST['itemfile'][$key]['filesize'])) $_POST['itemfile'][$key]['filesize'] = $_FILES['itemfile']['size'][$key]['attachment'];
		if (!isset($_POST['itemfile'][$key]['mimetype'])) $_POST['itemfile'][$key]['mimetype'] = $_FILES['itemfile']['type'][$key]['attachment'];
	}

	// Emulate uploaded picts as if they already were in database.
	if (!$this->__find_scalar($data, $_GET, $_POST, 'itempictsignore', null, core::config('itempictsignore')))
	if (isset($_FILES['itempict']) && is_array($_FILES['itempict']['tmp_name']))
	foreach ($_FILES['itempict']['tmp_name'] as $key => $val)
	if (isset($_FILES['itempict']['tmp_name'][$key]['attachment']) && is_scalar($_FILES['itempict']['tmp_name'][$key]['attachment']))
	if (is_uploaded_file($_FILES['itempict']['tmp_name'][$key]['attachment']))
	{
		// Force POST to contain information about uploaded pict.
		if (!isset($_POST['itempict']      ) || !is_array($_POST['itempict']      )) $_POST['itempict']       = array();
		if (!isset($_POST['itempict'][$key]) || !is_array($_POST['itempict'][$key])) $_POST['itempict'][$key] = array();

		// Emulate that just uploaded pict already exists in database.
		$_POST['itempict'][$key]['pict'] = $this->__upload('pict', $itemid, isset($_POST['itempict'][$key]['pict']) ? $_POST['itempict'][$key]['pict'] : null, $_FILES['itempict']['tmp_name'][$key]['attachment']);

		// Fill other information about pict if it does not exist yet.
		if (!isset($_POST['itempict'][$key]['filename'])) $_POST['itempict'][$key]['filename'] = $_FILES['itempict']['name'][$key]['attachment'];
		if (!isset($_POST['itempict'][$key]['filesize'])) $_POST['itempict'][$key]['filesize'] = $_FILES['itempict']['size'][$key]['attachment'];

		// Fill graphical information about pict if it does not exist yet.
		$uploadinfo = @getimagesize($_FILES['itempict']['tmp_name'][$key]['attachment']);
		if ($uploadinfo === false)
		{
			if (!isset($_POST['itempict'][$key]['xsize'   ])) $_POST['itempict'][$key]['xsize'   ] = null;
			if (!isset($_POST['itempict'][$key]['ysize'   ])) $_POST['itempict'][$key]['ysize'   ] = null;
			if (!isset($_POST['itempict'][$key]['mimetype'])) $_POST['itempict'][$key]['mimetype'] = $_FILES['itempict']['type'][$key]['attachment'];
		} else
		{
			if (!isset($_POST['itempict'][$key]['xsize'   ])) $_POST['itempict'][$key]['xsize'   ] = $uploadinfo[0];
			if (!isset($_POST['itempict'][$key]['ysize'   ])) $_POST['itempict'][$key]['ysize'   ] = $uploadinfo[1];
			if (!isset($_POST['itempict'][$key]['mimetype'])) $_POST['itempict'][$key]['mimetype'] = image_type_to_mime_type($uploadinfo[2]);
		}
	}

	//...
	$itemlinkup = $this->__find_scalar(null, $_GET, $_POST, 'itemlinkup', null, null);
	$itemlinkdn = $this->__find_scalar(null, $_GET, $_POST, 'itemlinkdn', null, null);
	$itemfileup = $this->__find_scalar(null, $_GET, $_POST, 'itemfileup', null, null);
	$itemfiledn = $this->__find_scalar(null, $_GET, $_POST, 'itemfiledn', null, null);
	$itempictup = $this->__find_scalar(null, $_GET, $_POST, 'itempictup', null, null);
	$itempictdn = $this->__find_scalar(null, $_GET, $_POST, 'itempictdn', null, null);
	$submitted = !empty($_POST);

	//...
	$form = true;
	$args = array();
	$args['can'      ] = $can;
	$args['grants'   ] = $grants;
	$args['item'     ] = $item;
	$args['itemid'   ] = $itemid;
	$args['itemlinks'] = array_key_exists($itemid, $itemlinks) ? $itemlinks[$itemid] : array();
	$args['itemfiles'] = array_key_exists($itemid, $itemfiles) ? $itemfiles[$itemid] : array();
	$args['itempicts'] = array_key_exists($itemid, $itempicts) ? $itempicts[$itemid] : array();
	$args['errors'   ] = array(
				'already_exists'	=> false
				);//???

	//...
	if ($submitted)
	{
		//...
		if (isset($_POST['item']) && is_array($_POST['item']))
			foreach ($_POST['item'] as $key => $val)
				if (is_scalar($val))
					$args['item'][$key] = $val;

		//...
		if (!$this->__find_scalar($data, $_GET, $_POST, 'itemlinksignore', null, core::config('itemlinksignore')))
		{
			$args['itemlinks'] = $this->__grabsubs(
				isset($_POST['itemlink']) ? $_POST['itemlink'] : array(),
				array_key_exists($itemid, $itemlinks) ? $itemlinks[$itemid] : array(),
				'link', $this->db_pseudo_itemlink());
		}

		//...
		if (!$this->__find_scalar($data, $_GET, $_POST, 'itemfilesignore', null, core::config('itemfilesignore')))
		{
			$args['itemfiles'] = $this->__grabsubs(
				isset($_POST['itemfile']) ? $_POST['itemfile'] : array(),
				array_key_exists($itemid, $itemfiles) ? $itemfiles[$itemid] : array(),
				'file', $this->db_pseudo_itemfile());
		}

		//...
		if (!$this->__find_scalar($data, $_GET, $_POST, 'itempictsignore', null, core::config('itempictsignore')))
		{
			$args['itempicts'] = $this->__grabsubs(
				isset($_POST['itempict']) ? $_POST['itempict'] : array(),
				array_key_exists($itemid, $itempicts) ? $itempicts[$itemid] : array(),
				'pict', $this->db_pseudo_itempict());
		}
	}

	//...(even when not-post: because moving trough GET possible)
	$this->__reordersubs($args['itemlinks'], $itemlinkup, $itemlinkdn);
	$this->__reordersubs($args['itemfiles'], $itemfileup, $itemfiledn);
	$this->__reordersubs($args['itempicts'], $itempictup, $itempictdn);

	//...
	if ($submitted)
	{
		//!!!todo: verifications

		// Determine if we got no errors in fields.
		$ok = true;
		foreach ($args['errors'] as $error)
			$ok = $ok && !$error;

		// When every fields have correct and acceptable values, do operation.
		if ($ok)
		{
			// Start transaction.
			$this->db_work_begin();

			// Try to execute operation on data. Rollback on exceptions.
			try
			{
				//...
				$this->db_update_item($itemid, $args['item']);

				//...
				list($itemlinks_delete, $itemlinks_update, $itemlinks_insert)
					= $this->__splitsubs ($args['itemlinks'], array_key_exists($itemid, $itemlinks) ? $itemlinks[$itemid] : array(), 'link');
				list($itemfiles_delete, $itemfiles_update, $itemfiles_insert)
					= $this->__splitsubs ($args['itemfiles'], array_key_exists($itemid, $itemfiles) ? $itemfiles[$itemid] : array(), 'file');
				list($itempicts_delete, $itempicts_update, $itempicts_insert)
					= $this->__splitsubs ($args['itempicts'], array_key_exists($itemid, $itempicts) ? $itempicts[$itemid] : array(), 'pict');

				//...
				$this->db_update_itemlinks($itemid, $args['itemlinks'],
					$itemlinks_delete, $itemlinks_update, $itemlinks_insert);
				$this->db_update_itemfiles($itemid, $args['itemfiles'],
					$itemfiles_delete, $itemfiles_update, $itemfiles_insert);
				$this->db_update_itempicts($itemid, $args['itempicts'],
					$itempicts_delete, $itempicts_update, $itempicts_insert);

				// Commit transaction.
				$this->db_work_commit();

				// And only after successful commit, switch from form to success report.
				$form = false;
			}
			catch (entries_0_exception_duplicate $exception)
			{
				//...
				try { $this->db_work_rollback(); }
				catch (exception $exception) {}

				//...
				$args['errors']['already_exists'] = true;
			}
			catch (exception $exception)
			{
				//...
				try { $this->db_work_rollback(); }
				catch (exception $exception) {}

				//...
				throw $exception;
			}
		}
	}

	//...
	$args['itemlinksview'] = $this->_itemlink_list('modify_itemlink_', $can, $args['itemlinks'], $itemid);
	$args['itemfilesview'] = $this->_itemfile_list('modify_itemfile_', $can, $args['itemfiles'], $itemid);
	$args['itempictsview'] = $this->_itempict_list('modify_itempict_', $can, $args['itempicts'], $itemid);
	return core::template($form ? 'modify_form' : 'modify_success', $args);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function do_remove ($data, $itemid)
{
	//...
	$can = array();
	$can['view'] = core::grant($this->grant_view);
	$can['edit'] = core::grant($this->grant_edit);

	//...
	if (!$can['edit'])
	{
		$args = array();
		$args['can'] = $can;
		$args['itemid'] = $itemid;
		return core::template('remove_deny', $args);
	}

	//...
	$archive = isset($data['archive']) && is_scalar($data['archive']) ? $data['archive'] : (isset($_GET['archive']) && is_scalar($_GET['archive']) ? $_GET['archive'] : core::config('default_archive'));
	$filters = $this->__create_filters($can['edit'], $archive);

	//...
	$itemids = array($itemid);
	$item = is_null($itemid) ? array() : $this->db_select_items_byids($filters, $itemids);
	$item = array_key_exists($itemid, $item) ? $item[$itemid] : null;

	//...
	if (is_null($item))
	{
		$args = array();
		$args['can'   ] = $can;
		$args['itemid'] = $itemid;
		return core::template('remove_absent', $args);
	}

	//...
	$grants = core::grants(true);
	$itemlinks = $this->db_select_itemlinks($itemids);
	$itemfiles = $this->db_select_itemfiles($itemids);
	$itempicts = $this->db_select_itempicts($itemids);

	//...
	$submitted = !empty($_POST);

	//...
	$form = true;
	$args = array();
	$args['can'      ] = $can;
	$args['grants'   ] = $grants;
	$args['item'     ] = $item;
	$args['itemid'   ] = $itemid;
	$args['itemlinks'] = array_key_exists($itemid, $itemlinks) ? $itemlinks[$itemid] : array();
	$args['itemfiles'] = array_key_exists($itemid, $itemfiles) ? $itemfiles[$itemid] : array();
	$args['itempicts'] = array_key_exists($itemid, $itempicts) ? $itempicts[$itemid] : array();
	$args['errors'   ] = array(
				);//???

	//...
	if ($submitted)
	{
		//!!!todo: verifications

		// Determine if we got no errors in fields.
		$ok = true;
		foreach ($args['errors'] as $error)
			$ok = $ok && !$error;

		// When every fields have correct and acceptable values, do operation.
		if ($ok)
		{
			// Start transaction.
			$this->db_work_begin();

			// Try to execute operation on data. Rollback on exceptions.
			try
			{
				//...
				$this->db_delete_item($itemid);

				//...
				$this->db_delete_itemlinks($itemid);
				$this->db_delete_itemfiles($itemid);
				$this->db_delete_itempicts($itemid);

				// Commit transaction.
				$this->db_work_commit();

				// And only after successful commit, switch from form to success report.
				$form = false;
			}
			catch (exception $exception)
			{
				//...
				try { $this->db_work_rollback(); }
				catch (exception $exception) {}

				//...
				throw $exception;
			}
		}
	}

	//...
	$args['itemlinksview'] = $this->_itemlink_list('remove_itemlink_', $can, $args['itemlinks'], $itemid);
	$args['itemfilesview'] = $this->_itemfile_list('remove_itemfile_', $can, $args['itemfiles'], $itemid);
	$args['itempictsview'] = $this->_itempict_list('remove_itempict_', $can, $args['itempicts'], $itemid);
	return core::template($form ? 'remove_form' : 'remove_success', $args);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __download ($sub, $itemid, $subid, $info, $disposition)
{
	//...
	$offset = 0;
	$segment = (integer) core::config('segment_download_'.$sub, core::config('segment_download', core::config('segment')));
	$timeout = (double ) core::config('timeout_download_'.$sub, core::config('timeout_download', core::config('timeout')));
	if ($segment <= 0) $segment = 1024*1024;

	//...
	header("Content-type: {$info['mimetype']}");
	header("Content-length: {$info['filesize']}");
	header("Content-disposition: {$disposition}; filename={$info['filename']}");

	//...
	core::clean();

	//...
	do {
		//...
		switch ($sub)
		{
			case 'file': $data = $this->db_select_itemfile_data($itemid, $subid, $offset, $segment); break;
			case 'pict': $data = $this->db_select_itempict_data($itemid, $subid, $offset, $segment); break;
		}

		//...
		echo $data;

		//...
		core::flush(true);

		//...
		$offset += $segment;

		//...
		if (isset($timeout))
			usleep($timeout * 1000000);

	} while (strlen($data) >= $segment);

	//...
	core::shutup(true);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __upload ($sub, $itemid, $subid, $path)
{
	// Start transaction.
	$this->db_work_begin();

	// Try to upload content of a file. Rollback on exceptions.
	try
	{
		//...
		if (!is_null($subid) && is_scalar($subid))
		{
			//...
			switch ($sub)
			{
				case 'file': $this->db_update_itemfile_data($itemid, $subid); break;
				case 'pict': $this->db_update_itempict_data($itemid, $subid); break;
			}
		} else
		{
			//...
			switch ($sub)
			{
				case 'file': $subid = $this->db_insert_itemfile_data(); break;
				case 'pict': $subid = $this->db_insert_itempict_data(); break;
			}
		}

		//...
		if (!is_null($subid)/* && still need chunked-upload after update/insert? ???*/)
		{
			//...
			$segment = (integer) core::config('segment_upload_'.$sub, core::config('segment_upload', core::config('segment')));
			$timeout = (double ) core::config('timeout_upload_'.$sub, core::config('timeout_upload', core::config('timeout')));
			if ($segment <= 0) $segment = 1024*1024;

			//...
			$handle = fopen($path, 'rb');
			if ($handle !== false)
			{
				//...
				while (!feof($handle))
				{
					//...
					$data = fread($handle, $segment);

					//...
					if ($data === false) break;

					//...
					switch ($sub)
					{
						case 'file': $this->db_upload_itemfile_data($itemid, $subid, $data); break;
						case 'pict': $this->db_upload_itempict_data($itemid, $subid, $data); break;
					}

					//...
					if (isset($timeout))
						usleep($timeout * 1000000);
				}

				//...
				fclose($handle);
			}
		}

		// Commit transaction.
		$this->db_work_commit();
	}
	catch (exception $exception)
	{
		//...
		try { $this->db_work_rollback(); }
		catch (exception $exception) {}

		//...
		throw $exception;
	}

	//...
	return $subid;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function _itemlink_list ($macrofix, $can, $itemlinks, $itemid)
{
	//...
	if (empty($itemlinks))
	{
		//...
		$args = array();
		$args['can'] = $can;
		return core::template($macrofix . 'empty', $args);
        }

	//...
	$rows = array();
	$index = 1;
	$count = count($itemlinks);
	foreach ($itemlinks as $itemlinkid => $itemlink)
	{
		//...
		$args = array();
		$args['can'       ] = $can;
		$args['index'     ] = $index;
		$args['count'     ] = $count;
		$args['itemid'    ] = $itemid;
		$args['itemlink'  ] = $itemlink;
		$args['itemlinkid'] = $itemlinkid;
		$row = core::template($macrofix . 'row', $args);

		//...
		if ($row == '') break;

		//...
		$rows[] = $row;

		//...
		$index++;
	}

	//...
	$rows = implode(core::template($macrofix . 'glue'), $rows);

	//...
	$args = array();
	$args['can'      ] = $can;
	$args['count'    ] = $count;
	$args['rows'     ] = $rows;
	$args['itemid'   ] = $itemid;
	$args['itemlinks'] = $itemlinks;
	$row = core::template($macrofix . 'all', $args);

	// Return this final format of rows, or just a rows without format (if format is empty).
	return ($row == '') ? $rows : $row;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function _itemfile_list ($macrofix, $can, $itemfiles, $itemid)
{
	//...
	if (empty($itemfiles))
	{
		//...
		$args = array();
		$args['can'] = $can;
		return core::template($macrofix . 'empty', $args);
        }

	//...
	$rows = array();
	$index = 1;
	$count = count($itemfiles);
	foreach ($itemfiles as $itemfileid => $itemfile)
	{
		//...
		$args = array();
		$args['can'       ] = $can;
		$args['index'     ] = $index;
		$args['count'     ] = $count;
		$args['itemid'    ] = $itemid;
		$args['itemfile'  ] = $itemfile;
		$args['itemfileid'] = $itemfileid;
		$row = core::template($macrofix . 'row', $args);

		//...
		if ($row == '') break;

		//...
		$rows[] = $row;

		//...
		$index++;
	}

	//...
	$rows = implode(core::template($macrofix . 'glue'), $rows);

	//...
	$args = array();
	$args['can'      ] = $can;
	$args['count'    ] = $count;
	$args['rows'     ] = $rows;
	$args['itemid'   ] = $itemid;
	$args['itemfiles'] = $itemfiles;
	$row = core::template($macrofix . 'all', $args);

	// Return this final format of rows, or just a rows without format (if format is empty).
	return ($row == '') ? $rows : $row;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function _itempict_list ($macrofix, $can, $itempicts, $itemid)
{
	//...
	if (empty($itempicts))
	{
		//...
		$args = array();
		$args['can'] = $can;
		return core::template($macrofix . 'empty', $args);
        }

	//...
	$rows = array();
	$index = 1;
	$count = count($itempicts);
	foreach ($itempicts as $itempictid => $itempict)
	{
		//...
		$args = array();
		$args['can'       ] = $can;
		$args['index'     ] = $index;
		$args['count'     ] = $count;
		$args['itemid'    ] = $itemid;
		$args['itempict'  ] = $itempict;
		$args['itempictid'] = $itempictid;
		$row = core::template($macrofix . 'row', $args);

		//...
		if ($row == '') break;

		//...
		$rows[] = $row;

		//...
		$index++;
	}

	//...
	$rows = implode(core::template($macrofix . 'glue'), $rows);

	//...
	$args = array();
	$args['can'      ] = $can;
	$args['count'    ] = $count;
	$args['rows'     ] = $rows;
	$args['itemid'   ] = $itemid;
	$args['itempicts'] = $itempicts;
	$row = core::template($macrofix . 'all', $args);

	// Return this final format of rows, or just a rows without format (if format is empty).
	return ($row == '') ? $rows : $row;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __reordersubs_func ($a, $b)
{
	if (!is_null($a['order']) && !is_null($b['order']))
		if ($a['order'] < $b['order']) return -1; else
		if ($a['order'] > $b['order']) return +1; else
		return strnatcasecmp($a['text'], $b['text']); else
	if (!is_null($a['order'])) return -1; else
	if (!is_null($b['order'])) return +1; else
	return strnatcasecmp($a['text'], $b['text']);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __reordersubs (&$array, $upid, $dnid)
{
	//... (NB: keep assoc-keys in array! required by futher moving of sub)
	uasort($array, array(&$this, '__reordersubs_func'));

	//...
	if (!is_null($upid))
	{
		//...
		$swapkeys = array_keys($array);

		//...
		$swapkey1 = $upid;

		//...
		$swapindex = array_search($swapkey1, $swapkeys);

		//...
		if (($swapindex !== false) && ($swapindex > 0))
		{
			//...
			$swapkey2 = $swapkeys[$swapindex-1];

			//...
			$swaptemp = $array[$swapkey1];
			$array[$swapkey1] = $array[$swapkey2];
			$array[$swapkey2] = $swaptemp;
		}
	} else

	//...
	if (!is_null($dnid))
	{
		//...
		$swapkeys = array_keys($array);

		//...
		$swapkey1 = $dnid;

		//...
		$swapindex = array_search($swapkey1, $swapkeys);

		//...
		if (($swapindex !== false) && ($swapindex < count($swapkeys)-1))
		{
			//...
			$swapkey2 = $swapkeys[$swapindex+1];

			//...
			$swaptemp = $array[$swapkey1];
			$array[$swapkey1] = $array[$swapkey2];
			$array[$swapkey2] = $swaptemp;
		}
	}

	//...
	$order = 0;
	foreach ($array as $key => $val)
		$array[$key]['order'] = ++$order;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __grabsubs ($source, $original, $idname, $pseudo)
{
	//...
	$result = array();

	//...
	if (is_array($source))
	{
		//...
		foreach ($source as $key => $val)
		{
			//...
			if (is_array ($val))
			{
				//...
				$isempty = true; foreach ($val as $valval) $isempty = $isempty && ($valval == '');
				if ($isempty || (isset($val['delete']) && (bool) $val['delete'])) continue;

				//...
				$subid = isset($val[$idname]) ? $val[$idname] : null;

				//...
				$result[$key] = array_key_exists($subid, $original) ? $original[$subid] : $pseudo;

				//...
				foreach ($val as $valkey => $valval)
					$result[$key][$valkey] = $valval;
			}
		}
	}

	//...
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __splitsubs (&$array, $originalarray, $idname)
{
	//...
	$delete = array();
	$update = array();
	$insert = array();

	//...
	$knownids = array();

	//...
	foreach ($array as $key => $sub)
	{
		//...
		if (is_null($sub[$idname]))
		{
			$insert[] = $sub;
			continue;
		}

		//...
		$knownids[] = $sub[$idname];

		//...
		$changed = false;
		foreach($sub as $subkey => $subval) if (!$changed)
		{
			$changed = $changed || (array_key_exists($sub[$idname], $originalarray) && array_key_exists($subkey, $originalarray[$sub[$idname]]) ? $originalarray[$sub[$idname]][$subkey] != $subval : true);
		}

		if ($changed)
		{
			$update[$sub[$idname]] = $sub;
		}
	}

	//...
	foreach ($originalarray as $key => $originalsub)
		if (!in_array($originalsub[$idname], $knownids))
			$delete[$originalsub[$idname]] = $originalsub;

	//...
	return array($delete, $update, $insert);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __create_filters ($emptyfilters, $archive)
{
	//...
	$result = array();
	if ($emptyfilters)
	{
		//...
		$result['grants'    ] = null;
		$result['published' ] = null;
		$result['actualfrom'] = null;
		$result['actualtill'] = null;
	} else
	{
		//...
		$result['grants'    ] = core::grants(false);
		$result['published' ] = true;
		$result['actualfrom'] = true;
		$result['actualtill'] = $archive ? null : true;
	}

	//...
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __find_scalar ($source1, $source2, $source3, $name1, $name2, $default)
{
	//...
	$result = null;

	//...
	if (is_scalar($name1))
	{
		//...
		if (is_null($result) && is_array($source1) && isset($source1[$name1]) && is_scalar($source1[$name1]) && ($source1[$name1] != ''))
			$result = $source1[$name1];

		//...
		if (is_null($result) && is_array($source2) && isset($source2[$name1]) && is_scalar($source2[$name1]) && ($source2[$name1] != ''))
			$result = $source2[$name1];

		//...
		if (is_null($result) && is_array($source3) && isset($source3[$name1]) && is_scalar($source3[$name1]) && ($source3[$name1] != ''))
			$result = $source3[$name1];
	}

	//...
	if (is_scalar($name2))
	{
		//...
		if (is_null($result) && is_array($source1) && isset($source1[$name2]) && is_scalar($source1[$name2]) && ($source1[$name2] != ''))
			$result = $source1[$name2];

		//...
		if (is_null($result) && is_array($source2) && isset($source2[$name2]) && is_scalar($source2[$name2]) && ($source2[$name2] != ''))
			$result = $source2[$name2];

		//...
		if (is_null($result) && is_array($source3) && isset($source3[$name2]) && is_scalar($source3[$name2]) && ($source3[$name2] != ''))
			$result = $source3[$name2];
	}

	//...
	if (is_null($result))
		$result = $default;

	//...
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __format_callback_pict ($matches)
{
	//..
	if (array_key_exists($matches[1], $this->__format_picts))
	{
		//...
		$args = array();
		$args['itemid'    ] = $this->__format_itemid;
		$args['itempict'  ] = $this->__format_picts[$matches[1]];
		$args['itempictid'] = $args['itempict']['pict'];
		return core::template('format_pict', $args);
	}

	//...
	return $matches[0];
}

function __format_callback_tag ($matches)
{
	//..
	if ($matches[1] === 'b')
	{
		return "<b>" . $matches[2] . "</b>"; //!!! template(format_bold)
	}

	//...
	return $matches[0];
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __format ($text, $itemid, $itemlinks, $itemfiles, $itempicts)
{
	if ($this->__format_itemid !== $itemid)
	{
		//...
		$this->__format_itemid = $itemid;

		//...
		$this->__format_links = array();
		if (array_key_exists($itemid, $itemlinks))
			foreach ($itemlinks[$itemid] as $itemlink)
				$this->__format_links[$itemlink['order']] = $itemlink;
		//...
		$this->__format_files = array();
		if (array_key_exists($itemid, $itemfiles))
			foreach ($itemfiles[$itemid] as $itemfile)
				$this->__format_files[$itemfile['order']] = $itemfile;
		//...
		$this->__format_picts = array();
		if (array_key_exists($itemid, $itempicts))
			foreach ($itempicts[$itemid] as $itempict)
				$this->__format_picts[$itempict['order']] = $itempict;
	}

	//...
	$result = $text;

	//...
	$result = str_replace("\n", "<br>\n", $result);//!!! template

	//...
	$result = preg_replace_callback('/\[pict=(.+?)\]/six', array(&$this, '__format_callback_pict'), $result);

	//...
	$result = preg_replace_callback('/\[(b)\] (.*?) \[\/\1\]/six', array(&$this, '__format_callback_tag'), $result);
	$result = preg_replace_callback('/\[(i)\] (.*?) \[\/\1\]/six', array(&$this, '__format_callback_tag'), $result);
	$result = preg_replace_callback('/\[(u)\] (.*?) \[\/\1\]/six', array(&$this, '__format_callback_tag'), $result);

	//...
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

?>