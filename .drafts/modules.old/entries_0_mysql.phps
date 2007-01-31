<?php defined('CORENINPAGE') or die('Hack!');
//todo: 1. add additional data to events in some methods. for ex. itemid and so on...

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class entries_0_mysql extends dbworker
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function db_work_begin ()
{
	//
	$db = core::handle();

	// query
	$sql = "start transaction";

	// event
	core::event('query', "Start transaction.", array('query'=>$sql));

	// execute
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function db_work_commit ()
{
	//
	$db = core::handle();

	// query
	$sql = "commit";

	// event
	core::event('query', "Commit transaction.", array('query'=>$sql));

	// execute
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function db_work_rollback ()
{
	//
	$db = core::handle();

	// query
	$sql = "rollback";

	// event
	core::event('query', "Rollback transaction.", array('query'=>$sql));

	// execute
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function db_pseudo_item ()
{
	return array(
			'item'			=> null,
			'grant'			=> null,
			'headline'		=> null,
			'announce'		=> null,
			'fulltext'		=> null,
			'actualfrom'		=> null,
			'actualfrom_year'	=> null,
			'actualfrom_month'	=> null,
			'actualfrom_day'	=> null,
			'actualfrom_hour'	=> null,
			'actualfrom_minute'	=> null,
			'actualfrom_second'	=> null,
			'actualtill'		=> null,
			'actualtill_year'	=> null,
			'actualtill_month'	=> null,
			'actualtill_day'	=> null,
			'actualtill_hour'	=> null,
			'actualtill_minute'	=> null,
			'actualtill_second'	=> null,
			'published'		=> null,
			'expander'		=> null);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function db_select_items_count ($args)
{
	$filters = @$args['filters'];

	// handle
	$db = core::handle();

	// table
	$table = core::table('item');

	//...
	$filters = $this->db__filterswhere($filters);
	if ($filters === '') $filters = "1=1";

	// query
	$sql =
	"
		select count(*)
		  from {$table}
		 where {$filters}
	";

	// event
	core::event('query', "Select number of items in database.", array('query'=>$sql));

	// execute
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));

	// fetch
	$result = null;
	while ($row = mysql_fetch_row($res))
		$result = $row[0];
	mysql_free_result($res);

	// return
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function db_select_items_page ($args)
{
	$filters = @$args['filters'];
	$sorting = @$args['sorting'];
	$reverse = @$args['reverse'];
	$page    = @$args['page'   ];
	$size    = @$args['size'   ];
	$offset  = @$args['offset' ];
	$count   = @$args['count'  ];

	
	
	// handle
	$db = core::handle();

	// table
	$table = core::table('item');

	//...
	$reverse = ($reverse ? 'desc' : 'asc');
	$sorting =
//!!!		($sorting == 'disabled' ? 'disabled' :
//!!!		($sorting == 'created'  ? 'created'  :
//!!!		($sorting == 'touched'  ? 'touched'  :
//!!!		($sorting == 'logname'  ? 'logname'  :
		('item');
//!!!		))));

	//...
	$filters = $this->db__filterswhere($filters);
	if ($filters === '') $filters = "1=1";

	// query
	$sql =
	"
		select `item`, `grant`, `headline`, `announce`, `fulltext`, `actualfrom`, `actualtill`, `published`, `expander`
		  from {$table}
		 where {$filters}
		 order by `{$sorting}` {$reverse}, `item` asc
		 limit {$size} offset {$offset}
	";

	// event
	core::event('query', "Select page of items from database.", array('query'=>$sql));

	// execute
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));

	// fetch
	$result = array();
	while ($row = mysql_fetch_row($res))
	{
		$actualfrom = split('[- :]', $row[5]); if (count($actualfrom) < 6) $actualfrom = array_pad($actualfrom, 6, null);
		$actualtill = split('[- :]', $row[6]); if (count($actualtill) < 6) $actualtill = array_pad($actualtill, 6, null);
		$result[$row[0]] = array(
			'item'			=> $row[0],
			'grant'			=> $row[1],
			'headline'		=> $row[2],
			'announce'		=> $row[3],
			'fulltext'		=> $row[4],
			'actualfrom'		=> !is_null($row[5]),
			'actualfrom_year'	=> is_null($row[5]) ? null : $actualfrom[0],
			'actualfrom_month'	=> is_null($row[5]) ? null : $actualfrom[1],
			'actualfrom_day'	=> is_null($row[5]) ? null : $actualfrom[2],
			'actualfrom_hour'	=> is_null($row[5]) ? null : $actualfrom[3],
			'actualfrom_minute'	=> is_null($row[5]) ? null : $actualfrom[4],
			'actualfrom_second'	=> is_null($row[5]) ? null : $actualfrom[5],
			'actualtill'		=> !is_null($row[6]),
			'actualtill_year'	=> is_null($row[6]) ? null : $actualtill[0],
			'actualtill_month'	=> is_null($row[6]) ? null : $actualtill[1],
			'actualtill_day'	=> is_null($row[6]) ? null : $actualtill[2],
			'actualtill_hour'	=> is_null($row[6]) ? null : $actualtill[3],
			'actualtill_minute'	=> is_null($row[6]) ? null : $actualtill[4],
			'actualtill_second'	=> is_null($row[6]) ? null : $actualtill[5],
			'published'		=> $row[7],
			'expander'		=> $row[8]);
	}
	mysql_free_result($res);

	// return
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function db_select_items_byids ($filters, $itemids)
{
	// check if empty
	if (empty($itemids)) return array();

	// handle
	$db = core::handle();

	// table
	$table = core::table('item');

	//...
	$filters = $this->db__filterswhere($filters);
	if ($filters === '') $filters = "1=1";

	// values
	$sqlvalues = array();
	foreach ($itemids as $itemid)
	{
		$sqlvalues[] = "'" . mysql_real_escape_string($itemid, $db) . "'";
	}
	$sqlvalues = implode(',', $sqlvalues);

	// query
	$sql =
	"
		select `item`, `grant`, `headline`, `announce`, `fulltext`, `actualfrom`, `actualtill`, `published`, `expander`
		  from {$table}
		 where {$filters} and `item` in ({$sqlvalues})
	";

	// event
	core::event('query', "Select items from database by ids.", array('query'=>$sql));

	// execute
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));

	// fetch
	$result = array();
	while ($row = mysql_fetch_row($res))
	{
		$actualfrom = split('[- :]', $row[5]); if (count($actualfrom) < 6) $actualfrom = array_pad($actualfrom, 6, null);
		$actualtill = split('[- :]', $row[6]); if (count($actualtill) < 6) $actualtill = array_pad($actualtill, 6, null);
		$result[$row[0]] = array(
			'item'			=> $row[0],
			'grant'			=> $row[1],
			'headline'		=> $row[2],
			'announce'		=> $row[3],
			'fulltext'		=> $row[4],
			'actualfrom'		=> !is_null($row[5]),
			'actualfrom_year'	=> is_null($row[5]) ? null : $actualfrom[0],
			'actualfrom_month'	=> is_null($row[5]) ? null : $actualfrom[1],
			'actualfrom_day'	=> is_null($row[5]) ? null : $actualfrom[2],
			'actualfrom_hour'	=> is_null($row[5]) ? null : $actualfrom[3],
			'actualfrom_minute'	=> is_null($row[5]) ? null : $actualfrom[4],
			'actualfrom_second'	=> is_null($row[5]) ? null : $actualfrom[5],
			'actualtill'		=> !is_null($row[6]),
			'actualtill_year'	=> is_null($row[6]) ? null : $actualtill[0],
			'actualtill_month'	=> is_null($row[6]) ? null : $actualtill[1],
			'actualtill_day'	=> is_null($row[6]) ? null : $actualtill[2],
			'actualtill_hour'	=> is_null($row[6]) ? null : $actualtill[3],
			'actualtill_minute'	=> is_null($row[6]) ? null : $actualtill[4],
			'actualtill_second'	=> is_null($row[6]) ? null : $actualtill[5],
			'published'		=> $row[7],
			'expander'		=> $row[8]);
	}
	mysql_free_result($res);

	// return
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function db_insert_item ($data)
{
	// handle
	$db = core::handle();

	// table
	$table = core::table('item');

	// format
	$actualfrom = (bool) $data['actualfrom'] ? sprintf("'%04d-%02d-%02d %02d:%02d:%02d'", (integer) $data['actualfrom_year'], (integer) $data['actualfrom_month'], (integer) $data['actualfrom_day'], (integer) $data['actualfrom_hour'], (integer) $data['actualfrom_minute'], (integer) $data['actualfrom_second']) : "null";
	$actualtill = (bool) $data['actualtill'] ? sprintf("'%04d-%02d-%02d %02d:%02d:%02d'", (integer) $data['actualtill_year'], (integer) $data['actualtill_month'], (integer) $data['actualtill_day'], (integer) $data['actualtill_hour'], (integer) $data['actualtill_minute'], (integer) $data['actualtill_second']) : "null";
	$published  = (bool) $data['published' ] ? "'1'" : "null";

	// escape
	foreach ($data as $key => &$val) $val = is_null($val) || ($val == '') ? "null" : "'" . mysql_real_escape_string($val, $db) . "'";

	// query
	$sql =
	"
		insert into {$table} (
			 `item`
			,`grant`
			,`headline`
			,`announce`
			,`fulltext`
			,`actualfrom`
			,`actualtill`
			,`published`
			,`expander`
		) values (
			 default
			,{$data['grant'   ]}
			,{$data['headline']}
			,{$data['announce']}
			,{$data['fulltext']}
			,{$actualfrom}
			,{$actualtill}
			,{$published}
			,{$data['expander']}
		)
	";

	// event
	core::event('query', "Insert item to database.", array('query'=>$sql));

	// execute
	$res = @mysql_query($sql, $db);
	if ($res === false) if (mysql_errno($db) === 1062) throw new entries_0_exception_duplicate(mysql_error($db), mysql_errno($db));
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));

	// return
	return mysql_insert_id($db);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function db_update_item ($itemid, $data)
{
	// handle
	$db = core::handle();

	// tables
	$table = core::table('item');

	// format
	$actualfrom = (bool) $data['actualfrom'] ? sprintf("'%04d-%02d-%02d %02d:%02d:%02d'", (integer) $data['actualfrom_year'], (integer) $data['actualfrom_month'], (integer) $data['actualfrom_day'], (integer) $data['actualfrom_hour'], (integer) $data['actualfrom_minute'], (integer) $data['actualfrom_second']) : "null";
	$actualtill = (bool) $data['actualtill'] ? sprintf("'%04d-%02d-%02d %02d:%02d:%02d'", (integer) $data['actualtill_year'], (integer) $data['actualtill_month'], (integer) $data['actualtill_day'], (integer) $data['actualtill_hour'], (integer) $data['actualtill_minute'], (integer) $data['actualtill_second']) : "null";
	$published  = (bool) $data['published' ] ? "'1'" : "null";

	// escape
	$itemid = "'" . mysql_real_escape_string($itemid, $db) . "'";
	foreach ($data as $key => &$val) $val = is_null($val) || ($val == '') ? "null" : "'" . mysql_real_escape_string($val, $db) . "'";

	// query
	$sql =
	"
		update {$table} set
			 `grant`      = {$data['grant'   ]}
			,`headline`   = {$data['headline']}
			,`announce`   = {$data['announce']}
			,`fulltext`   = {$data['fulltext']}
			,`actualfrom` = {$actualfrom}
			,`actualtill` = {$actualtill}
			,`published`  = {$published}
			,`expander`   = {$data['expander']}
		where `item` = {$itemid}
	";

	// event
	core::event('query', "Update item in database.", array('query'=>$sql));

	// execute
	$res = @mysql_query($sql, $db);
	if ($res === false) if (mysql_errno($db) === 1062) throw new entries_0_exception_duplicate(mysql_error($db), mysql_errno($db));
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function db_delete_item ($itemid)
{
	// handle
	$db = core::handle();

	// tables
	$table = core::table('item');

	// escape
	$itemid = "'" . mysql_real_escape_string($itemid, $db) . "'";

	// query
	$sql =
	"
		delete from {$table}
		 where `item` = {$itemid}
	";

	// event
	core::event('query', "Delete item from database.", array('query'=>$sql));

	// execute
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function db_pseudo_itemlink ()
{
	return array(
			'link'		=> null,
			'order'		=> null,
			'text'		=> null,
			'url'		=> null);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function db_select_itemlinks ($itemids)
{
	// check if empty
	if (empty($itemids)) return array();

	// handle
	$db = core::handle();

	// table
	$table = core::table('link');

	// values
	$sqlvalues = array();
	foreach ($itemids as $itemid)
	{
		$sqlvalues[] = "'" . mysql_real_escape_string($itemid, $db) . "'";
	}
	$sqlvalues = implode(',', $sqlvalues);

	// query
	$sql =
	"
		select `item`, `link`, `order`, `text`, `url`
		  from {$table}
		 where `item` in ({$sqlvalues})
		 order by `item`, `order`, `link`
	";

	// event
	core::event('query', "Select items' links from database.", array('query'=>$sql));

	// execute
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));

	// fetch
	$result = array();
	while ($row = mysql_fetch_row($res))
	{
		if (!array_key_exists($row[0], $result)) $result[$row[0]] = array();
		$result[$row[0]][$row[1]] = array(
			'link'	=> $row[1],
			'order'	=> $row[2],
			'text'	=> $row[3],
			'url'	=> $row[4]);
	}
	mysql_free_result($res);

	// return
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function db_update_itemlinks ($itemid, $total, $delete, $update, $insert)
{
	//
	$db = core::handle();

	// tables
	$table = core::table('link');

	// escape
	$itemid = "'" . mysql_real_escape_string($itemid, $db) . "'";

	//...
	if (!empty($delete))
	{
		// values
		$sqlvalues = array();
		foreach ($delete as $linkid => $link)
		{
			$sqlvalues[] = "'" . mysql_real_escape_string($linkid, $db) . "'";
		}
		$sqlvalues = implode(',', $sqlvalues);

		// query
		$sql =
		"
			delete from {$table}
			 where `link` in ({$sqlvalues}) and (`item` = {$itemid} or `item` is null)
		";

		// event
		core::event('query', "Delete some item's links from database.", array('query'=>$sql));

		// execute
		$res = @mysql_query($sql, $db);
		if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));
	}

	//...
	if (!empty($update))
	{
		// values
		$sqlvalues = array();
		foreach ($update as $linkid => $link)
		{
			//...
			$linkid = "'" . mysql_real_escape_string($linkid, $db) . "'";

			//...
			foreach ($link as $key => &$val) $val = is_null($val) || ($val == '') ? "null" : "'" . mysql_real_escape_string($val, $db) . "'";

			// query
			$sql =
			"
			update {$table} set
				 `item`		= {$itemid}
				,`order`	= {$link['order']}
				,`text`		= {$link['text' ]}
				,`url`		= {$link['url'  ]}
				,`stamp`    = null
			where `link` = {$linkid} and (`item` = {$itemid} or `item` is null)
			";

			// event
			core::event('query', "Update some item's link in database.", array('query'=>$sql));

			// execute
			$res = @mysql_query($sql, $db);
			if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));
		}
	}

	//...
	if (!empty($insert))
	{
		// values
		$sqlvalues = array();
		foreach ($insert as $link)
		{
			//...
			foreach ($link as $key => &$val) $val = is_null($val) || ($val == '') ? "null" : "'" . mysql_real_escape_string($val, $db) . "'";

			//...
			$sqlvalues[] = "({$itemid}
				,{$link['order']}
				,{$link['text' ]}
				,{$link['url'  ]}
				)";
		}
		$sqlvalues = implode(',', $sqlvalues);

		// query
		$sql =
		"
			insert into {$table} (
				 `item`
				,`order`
				,`text`
				,`url`
			) values {$sqlvalues}
		";

		// event
		core::event('query', "Insert some item's links to database.", array('query'=>$sql));

		// execute
		$res = @mysql_query($sql, $db);
		if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function db_delete_itemlinks ($itemid)
{
	// handle
	$db = core::handle();

	// tables
	$table = core::table('link');

	// escape
	$itemid = "'" . mysql_real_escape_string($itemid, $db) . "'";

	// query
	$sql =
	"
		delete from {$table}
		 where `item` = {$itemid}
	";

	// event
	core::event('query', "Delete all item's links from database.", array('query'=>$sql));

	// execute
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function db_pseudo_itemfile ()
{
	return array(
			'file'		=> null,
			'order'		=> null,
			'text'		=> null,
			'filename'	=> null,
			'filesize'	=> null,
			'mimetype'	=> null);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function db_select_itemfiles ($itemids)
{
	// check if empty
	if (empty($itemids)) return array();

	// handle
	$db = core::handle();

	// table
	$table = core::table('file');

	// values
	$sqlvalues = array();
	foreach ($itemids as $itemid)
	{
		$sqlvalues[] = "'" . mysql_real_escape_string($itemid, $db) . "'";
	}
	$sqlvalues = implode(',', $sqlvalues);

	// query
	$sql =
	"
		select `item`, `file`, `order`, `text`, `filename`, length(`data`), `mimetype`
		  from {$table}
		 where `item` in ({$sqlvalues})
		 order by `item`, `order`, `file`
	";

	// event
	core::event('query', "Select items' files from database.", array('query'=>$sql));

	// execute
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));

	// fetch
	$result = array();
	while ($row = mysql_fetch_row($res))
	{
		if (!array_key_exists($row[0], $result)) $result[$row[0]] = array();
		$result[$row[0]][$row[1]] = array(
			'file'		=> $row[1],
			'order'		=> $row[2],
			'text'		=> $row[3],
			'filename'	=> $row[4],
			'filesize'	=> $row[5],
			'mimetype'	=> $row[6]);
	}
	mysql_free_result($res);

	// return
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function db_update_itemfiles ($itemid, $total, $delete, $update, $insert)
{
	//
	$db = core::handle();

	// tables
	$table = core::table('file');

	// escape
	$itemid = "'" . mysql_real_escape_string($itemid, $db) . "'";

	//...
	if (!empty($delete))
	{
		// values
		$sqlvalues = array();
		foreach ($delete as $fileid => $file)
		{
			$sqlvalues[] = "'" . mysql_real_escape_string($fileid, $db) . "'";
		}
		$sqlvalues = implode(',', $sqlvalues);

		// query
		$sql =
		"
			delete from {$table}
			 where `file` in ({$sqlvalues}) and (`item` = {$itemid} or `item` is null)
		";

		// event
		core::event('query', "Delete some item's files from database.", array('query'=>$sql));

		// execute
		$res = @mysql_query($sql, $db);
		if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));
	}

	//...
	if (!empty($update))
	{
		// values
		$sqlvalues = array();
		foreach ($update as $fileid => $file)
		{
			//...
			$fileid = "'" . mysql_real_escape_string($fileid, $db) . "'";

			//...
			foreach ($file as $key => &$val) $val = is_null($val) || ($val == '') ? "null" : "'" . mysql_real_escape_string($val, $db) . "'";

			// query
			$sql =
			"
			update {$table} set
				 `item`     = {$itemid}
				,`order`    = {$file['order'   ]}
				,`text`     = {$file['text'    ]}
				,`filename` = {$file['filename']}
				,`mimetype` = {$file['mimetype']}
				,`stamp`    = null
			where `file` = {$fileid} and (`item` = {$itemid} or `item` is null)
			";

			// event
			core::event('query', "Update some item's file in database.", array('query'=>$sql));

			// execute
			$res = @mysql_query($sql, $db);
			if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));
		}
	}

	//...
	if (!empty($insert))
	{
		// values
		$sqlvalues = array();
		foreach ($insert as $file)
		{
			//...
			foreach ($file as $key => &$val) $val = is_null($val) || ($val == '') ? "null" : "'" . mysql_real_escape_string($val, $db) . "'";

			//...
			$sqlvalues[] = "({$itemid}
				,{$file['order'   ]}
				,{$file['text'    ]}
				,{$file['filename']}
				,{$file['mimetype']}
				)";
		}
		$sqlvalues = implode(',', $sqlvalues);

		// query
		$sql =
		"
		insert into {$table} (
			 `item`
			,`order`
			,`text`
			,`filename`
			,`mimetype`
		) values {$sqlvalues}
		";

		// event
		core::event('query', "Insert some item's files to database.", array('query'=>$sql));

		// execute
		$res = @mysql_query($sql, $db);
		if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function db_delete_itemfiles ($itemid)
{
	// handle
	$db = core::handle();

	// tables
	$table = core::table('file');

	// escape
	$itemid = "'" . mysql_real_escape_string($itemid, $db) . "'";

	// query
	$sql =
	"
		delete from {$table}
		 where `item` = {$itemid}
	";

	// event
	core::event('query', "Delete all item's files from database.", array('query'=>$sql));

	// execute
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function db_pseudo_itempict ()
{
	return array(
			'pict'		=> null,
			'order'		=> null,
			'text'		=> null,
			'filename'	=> null,
			'filesize'	=> null,
			'mimetype'	=> null,
			'xsize'		=> null,
			'ysize'		=> null,
			'align'		=> null,
			'embed'		=> null);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function db_select_itempicts ($itemids)
{
	// check if empty
	if (empty($itemids)) return array();

	// handle
	$db = core::handle();

	// table
	$table = core::table('pict');

	// values
	$sqlvalues = array();
	foreach ($itemids as $itemid)
	{
		$sqlvalues[] = "'" . mysql_real_escape_string($itemid, $db) . "'";
	}
	$sqlvalues = implode(',', $sqlvalues);

	// query
	$sql =
	"
		select `item`, `pict`, `order`, `text`, `filename`, length(`data`), `mimetype`, `xsize`, `ysize`, `align`, `embed`
		  from {$table}
		 where `item` in ({$sqlvalues})
		 order by `item`, `order`, `pict`
	";

	// event
	core::event('query', "Select items' picts from database.", array('query'=>$sql));

	// execute
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));

	// fetch
	$result = array();
	while ($row = mysql_fetch_row($res))
	{
		if (!array_key_exists($row[0], $result)) $result[$row[0]] = array();
		$result[$row[0]][$row[1]] = array(
			'pict'		=> $row[1],
			'order'		=> $row[2],
			'text'		=> $row[3],
			'filename'	=> $row[4],
			'filesize'	=> $row[5],
			'mimetype'	=> $row[6],
			'xsize'		=> $row[7],
			'ysize'		=> $row[8],
			'align'		=> $row[9],
			'embed'		=> $row[10]);
	}
	mysql_free_result($res);

	// return
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function db_update_itempicts ($itemid, $total, $delete, $update, $insert)
{
	//
	$db = core::handle();

	// tables
	$table = core::table('pict');

	// escape
	$itemid = "'" . mysql_real_escape_string($itemid, $db) . "'";

	//...
	if (!empty($delete))
	{
		// values
		$sqlvalues = array();
		foreach ($delete as $pictid => $pict)
		{
			$sqlvalues[] = "'" . mysql_real_escape_string($pictid, $db) . "'";
		}
		$sqlvalues = implode(',', $sqlvalues);

		// query
		$sql =
		"
			delete from {$table}
			 where `pict` in ({$sqlvalues}) and (`item` = {$itemid} or `item` is null)
		";

		// event
		core::event('query', "Delete some item's picts from database.", array('query'=>$sql));

		// execute
		$res = @mysql_query($sql, $db);
		if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));
	}

	//...
	if (!empty($update))
	{
		// values
		$sqlvalues = array();
		foreach ($update as $pictid => $pict)
		{
			//...
			$pictid = "'" . mysql_real_escape_string($pictid, $db) . "'";

			//...
			foreach ($pict as $key => &$val) $val = is_null($val) || ($val == '') ? "null" : "'" . mysql_real_escape_string($val, $db) . "'";

			// query
			$sql =
			"
			update {$table} set
				 `item`     = {$itemid}
				,`order`    = {$pict['order'   ]}
				,`text`     = {$pict['text'    ]}
				,`filename` = {$pict['filename']}
				,`mimetype` = {$pict['mimetype']}
				,`xsize`    = {$pict['xsize'   ]}
				,`ysize`    = {$pict['ysize'   ]}
				,`align`    = {$pict['align'   ]}
				,`embed`    = {$pict['embed'   ]}
				,`stamp`    = null
			where `pict` = {$pictid} and (`item` = {$itemid} or `item` is null)
			";

			// event
			core::event('query', "Update some item's pict in database.", array('query'=>$sql));

			// execute
			$res = @mysql_query($sql, $db);
			if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));
		}
	}

	//...
	if (!empty($insert))
	{
		// values
		$sqlvalues = array();
		foreach ($insert as $pict)
		{
			//...
			foreach ($pict as $key => &$val) $val = is_null($val) || ($val == '') ? "null" : "'" . mysql_real_escape_string($val, $db) . "'";

			//...
			$sqlvalues[] = "({$itemid}
				,{$pict['order'   ]}
				,{$pict['text'    ]}
				,{$pict['filename']}
				,{$pict['mimetype']}
				,{$pict['xsize'   ]}
				,{$pict['ysize'   ]}
				,{$pict['align'   ]}
				,{$pict['embed'   ]}
				)";
		}
		$sqlvalues = implode(',', $sqlvalues);

		// query
		$sql =
		"
		insert into {$table} (
			 `item`
			,`order`
			,`text`
			,`filename`
			,`mimetype`
			,`xsize`
			,`ysize`
			,`align`
			,`embed`
		) values {$sqlvalues}
		";

		// event
		core::event('query', "Insert some item's picts to database.", array('query'=>$sql));

		// execute
		$res = @mysql_query($sql, $db);
		if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function db_delete_itempicts ($itemid)
{
	// handle
	$db = core::handle();

	// tables
	$table = core::table('pict');

	// escape
	$itemid = "'" . mysql_real_escape_string($itemid, $db) . "'";

	// query
	$sql =
	"
		delete from {$table}
		 where `item` = {$itemid}
	";

	// event
	core::event('query', "Delete all item's picts from database.", array('query'=>$sql));

	// execute
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function db_select_itemlink_info ($itemid, $linkid)
{
	// handle
	$db = core::handle();

	// table
	$table = core::table('link');

	// escape
	$itemid = "'" . mysql_real_escape_string($itemid, $db) . "'";
	$linkid = "'" . mysql_real_escape_string($linkid, $db) . "'";

	// query
	$sql =
	"
		select `url`
		  from {$table}
		 where `link` = {$linkid} and (`item` = {$itemid} or `item` is null)
		 limit 1
	";

	// event
	core::event('query', "Select info on item's link from database.", array('query'=>$sql));

	// execute
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));

	// fetch
	$result = null;
	while ($row = mysql_fetch_row($res))
		$result = array(
			'url'		=> $row[0]);
	mysql_free_result($res);

	// return
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function db_select_itemfile_info ($itemid, $fileid)
{
	// handle
	$db = core::handle();

	// table
	$table = core::table('file');

	// escape
	$itemid = "'" . mysql_real_escape_string($itemid, $db) . "'";
	$fileid = "'" . mysql_real_escape_string($fileid, $db) . "'";

	// query
	$sql =
	"
		select `filename`, length(`data`), `mimetype`
		  from {$table}
		 where `file` = {$fileid} and (`item` = {$itemid} or `item` is null)
		 limit 1
	";

	// event
	core::event('query', "Select info on item's file from database.", array('query'=>$sql));

	// execute
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));

	// fetch
	$result = null;
	while ($row = mysql_fetch_row($res))
		$result = array(
			'filename'	=> $row[0],
			'filesize'	=> $row[1],
			'mimetype'	=> $row[2]);
	mysql_free_result($res);

	// return
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function db_select_itemfile_data ($itemid, $fileid, $offset, $segment)
{
	// handle
	$db = core::handle();

	// table
	$table = core::table('file');

	// escape
	$itemid = "'" . mysql_real_escape_string($itemid, $db) . "'";
	$fileid = "'" . mysql_real_escape_string($fileid, $db) . "'";
	$offset  = 1 + (integer) $offset ;
	$segment =     (integer) $segment;

	// query
	$sql =
	"
		select substring(`data`, {$offset}, {$segment})
		  from {$table}
		 where `file` = {$fileid} and (`item` = {$itemid} or `item` is null)
		 limit 1
	";

	// event
	core::event('query', "Select data of item's file from database.", array('query'=>$sql));

	// execute
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));

	// fetch
	$result = null;
	while ($row = mysql_fetch_row($res))
		$result = $row[0];
	mysql_free_result($res);

	// return
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function db_upload_itemfile_data ($itemid, $fileid, $data)
{
	// handle
	$db = core::handle();

	// tables
	$table = core::table('file');

	// escape
	$itemid = "'" . mysql_real_escape_string($itemid, $db) . "'";
	$fileid = "'" . mysql_real_escape_string($fileid, $db) . "'";
	$data   = "'" . mysql_real_escape_string($data  , $db) . "'";

	// query
	$sql =
	"
		update {$table} set
			`data`  = concat(`data`, {$data})
		where `file` = {$fileid} and (`item` = {$itemid} or `item` is null)
	";

	// event
	core::event('query', "Upload data of item's file to database.", array('query'=>substr_replace($sql, '...', strpos($sql, "`data`, '")+9, strlen($data))));

	// execute
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function db_update_itemfile_data ($itemid, $fileid)
{
	// handle
	$db = core::handle();

	// tables
	$table = core::table('file');

	// escape
	$itemid = "'" . mysql_real_escape_string($itemid, $db) . "'";
	$fileid = "'" . mysql_real_escape_string($fileid, $db) . "'";

	// query
	$sql =
	"
		update {$table} set
			 `stamp` = now()
			,`data`  = ''
		where `file` = {$fileid} and (`item` = {$itemid} or `item` is null)
	";

	// event
	core::event('query', "Reset data of item's file in database.", array('query'=>$sql));

	// execute
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function db_insert_itemfile_data ()
{
	// handle
	$db = core::handle();

	// tables
	$table = core::table('file');

	// query
	$sql =
	"
		insert into {$table} (`file`, `stamp`, `data`)
		values (default, now(), '')
	";

	// event
	core::event('query', "Add empty data for item's file into database.", array('query'=>$sql));

	// execute
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));

	//...
	return mysql_insert_id($db);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function db_select_itempict_info ($itemid, $pictid)
{
	// handle
	$db = core::handle();

	// table
	$table = core::table('pict');

	// escape
	$itemid = "'" . mysql_real_escape_string($itemid, $db) . "'";
	$pictid = "'" . mysql_real_escape_string($pictid, $db) . "'";

	// query
	$sql =
	"
		select `filename`, length(`data`), `mimetype`
		  from {$table}
		 where `pict` = {$pictid} and (`item` = {$itemid} or `item` is null)
		 limit 1
	";

	// event
	core::event('query', "Select info on item's pict from database.", array('query'=>$sql));

	// execute
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));

	// fetch
	$result = null;
	while ($row = mysql_fetch_row($res))
		$result = array(
			'filename'	=> $row[0],
			'filesize'	=> $row[1],
			'mimetype'	=> $row[2]);
	mysql_free_result($res);

	// return
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function db_select_itempict_data ($itemid, $pictid, $offset, $segment)
{
	// handle
	$db = core::handle();

	// table
	$table = core::table('pict');

	// escape
	$itemid = "'" . mysql_real_escape_string($itemid, $db) . "'";
	$pictid = "'" . mysql_real_escape_string($pictid, $db) . "'";
	$offset  = 1 + (integer) $offset ;
	$segment =     (integer) $segment;

	// query
	$sql =
	"
		select substring(`data`, {$offset}, {$segment})
		  from {$table}
		 where `pict` = {$pictid} and (`item` = {$itemid} or `item` is null)
		 limit 1
	";

	// event
	core::event('query', "Select data of item's pict from database.", array('query'=>$sql));

	// execute
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));

	// fetch
	$result = null;
	while ($row = mysql_fetch_row($res))
		$result = $row[0];
	mysql_free_result($res);

	// return
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function db_upload_itempict_data ($itemid, $pictid, $data)
{
	// handle
	$db = core::handle();

	// tables
	$table = core::table('pict');

	// escape
	$itemid = "'" . mysql_real_escape_string($itemid, $db) . "'";
	$pictid = "'" . mysql_real_escape_string($pictid, $db) . "'";
	$data   = "'" . mysql_real_escape_string($data  , $db) . "'";

	// query
	$sql =
	"
		update {$table} set
			`data`  = concat(`data`, {$data})
		 where `pict` = {$pictid} and (`item` = {$itemid} or `item` is null)
	";

	// event
	core::event('query', "Upload data of item's pict to database.", array('query'=>substr_replace($sql, '...', strpos($sql, "`data`, '")+9, strlen($data))));

	// execute
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function db_update_itempict_data ($itemid, $pictid)
{
	// handle
	$db = core::handle();

	// tables
	$table = core::table('pict');

	// escape
	$itemid = "'" . mysql_real_escape_string($itemid, $db) . "'";
	$pictid = "'" . mysql_real_escape_string($pictid, $db) . "'";

	// query
	$sql =
	"
		update {$table} set
			 `stamp` = now()
			,`data`  = ''
		 where `pict` = {$pictid} and (`item` = {$itemid} or `item` is null)
	";

	// event
	core::event('query', "Reset data of item's pict in database.", array('query'=>$sql));

	// execute
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function db_insert_itempict_data ()
{
	// handle
	$db = core::handle();

	// tables
	$table = core::table('pict');

	// query
	$sql =
	"
		insert into {$table} (`pict`, `stamp`, `data`)
		values (default, now(), '')
	";

	// event
	core::event('query', "Add empty data for item's pict into database.", array('query'=>$sql));

	// execute
	$res = @mysql_query($sql, $db);
	if ($res === false) throw new exception(mysql_error($db), mysql_errno($db));

	//...
	return mysql_insert_id($db);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function db__filterswhere ($filters)
{
	//...
	$result = array();

	//...
	if (isset($filter['grants']) && is_array($filter['grants']))
	{
		//...
		$sqlvalues = array();
		foreach ($filter['grants'] as $grant)
		{
			$sqlvalues[] = "'" . mysql_real_escape_string($grant, $db) . "'";
		}
		$sqlvalues = implode(',', $sqlvalues);

		//...
		$result[] = $sqlvalues === '' ?
			"(`grant` is null)" :
			"(`grant` is null or `grant` in ({$sqlvalues}))"; 
	}

	//...
	if (isset($filters['published']) && (bool) $filters['published'])
	{
		$result[] = "(`published` is not null and `published` <> 0)";
	}

	//...
	if (isset($filters['actualfrom']) && (bool) $filters['actualfrom'])
	{
		$result[] = "(`actualfrom` is null or `actualfrom` <= now())";
	}

	//...
	if (isset($filters['actualtill']) && (bool) $filters['actualtill'])
	{
		$result[] = "(`actualtill` is null or `actualtill` >= now())";
	}

	//...
	$result = implode(" and ", $result);
	if ($result !== '') $result = "(" . $result . ")";

	//...
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

?>