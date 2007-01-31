<?php defined('CORENINPAGE') or die('Hack!');
//todo: 1. сделать tag неяобзательным для дочерних. если тега нет - просто не делать включения и формат по нему.
//todo: 2. сделать определение, а изменилось ли чего, и требуется ли exec_action()? чтобы не гонять по базе неменяющие операции.

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class list_0_exception_duplicate extends exception {}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class list_0 extends module
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

private $dataroot;
private $children;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __construct ($configs)
{
	parent::__construct($configs);

	$this->dataroot = isset($configs['dataroot']) ? $configs['dataroot'] : null;
	$this->children = array();

	$children = core::find_scalar(array($configs), array('children'), null);
	$children = core::explode_scalar($children);
	foreach ($children as $childcode)
	{
		$childparts = explode('_', $childcode, 2);
		if (count($childparts) < 2) continue;

		$childentity = $childparts[0];
		$childname   = $childparts[1];

		if (($childentity == '') || ($childname == '')) continue;

		$childmodule  = core::find_scalar(array($configs), array('child_' . $childcode . '_module' ), null);
		$childfield   = core::find_scalar(array($configs), array('child_' . $childcode . '_field'  ), null);
		$childtag     = core::find_scalar(array($configs), array('child_' . $childcode . '_tag'    ), null);

		if (($childmodule == '') || ($childfield == '') || ($childtag == '')) continue;

		$childavoid   = core::find_scalar(array($configs), array('child_' . $childcode . '_avoid'  ), null);
		$childformats = core::find_scalar(array($configs), array('child_' . $childcode . '_formats'), null);
		$childavoid   = core::explode_scalar($childavoid  );
		$childformats = core::explode_scalar($childformats);

		if (!isset($this->children[$childentity])) $this->children[$childentity] = array();
		$this->children[$childentity][$childname] = array(
			'module'	=> $childmodule,
			'field'		=> $childfield,
			'tag'		=> $childtag,
			'avoid'		=> $childavoid,
			'formats'	=> $childformats);
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function main ($args)
{
	$directive = $this->do_parse_request($args);
	if (!is_array($directive) || empty($directive))
		throw new exception("Request is not parsed. Probably module is not fully implemented.");

	$entity = isset($directive['entity']) ? $directive['entity'] : null;
	$action = isset($directive['action']) ? $directive['action'] : null;
	$submit = isset($directive['submit']) ? $directive['submit'] : null;
	$filter = isset($directive['filter']) ? $directive['filter'] : null;
	$parent = isset($directive['parent']) ? $directive['parent'] : null;
	$itemid = isset($directive['itemid']) ? $directive['itemid'] : null;
	$child  = isset($directive['child' ]) ? $directive['child' ] : null;

	if (is_null($entity)) $entity = $this->get_default_entity();

	core::expire(0);//!!! только в отладочных целяъ. позже сделать систему устареваиня более умной!

	$atomic = $this->is_action_atomic($entity, $action);
	if (is_null($atomic))
	{
		echo $this->main_do_spec($args, $entity, $action, $submit, $filter, $parent, $itemid, $child);
	} else
	if ($atomic === true)
	{
		echo $this->main_do_item($args, $entity, $action, $submit, $filter, $parent, $itemid, $child);
	} else
	if ($atomic === false)
	{
		echo $this->main_do_list($args, $entity, $action, $submit, $filter, $parent);
	} else
	{
		/* unknown action type determined. what to do? nothing, i think. or to throw? */
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function main_do_spec ($args, $entity, $action, $submitted, $filter, $parent, $itemid, $child)
{
	if (is_null($entity)) $entity = $this->get_default_entity();

	// Make default action to be a showing of the form without any errors.
	$doform = true;
	$submit = null;
	$errors = null;
	$newitemid = null;

	// Check if action was submitted (POST'ed), but not initially requested (GET'ed).
	if ($submitted && is_null($child))
	{
		try
		{
			$subargs = array();
			$subargs['entity'] = $entity;
			$subargs['action'] = $action;
			$subargs['filter'] = $filter;
 			$errors = $this->exec_spec($subargs);

			if ($this->have_error_inside($errors))
			{
				core::rollback();
			} else
			{
				core::commit();
				$doform = false;

				$subargs = array();
				$subargs['entity'] = $entity;
				$this->apply_spec($subargs);
			}
		}
		catch (exception $exception)
		{
			try { core::rollback(); }
			catch (exception $exception_rollback) {}
			throw $exception;
		}
	}

	// Show form of action (possibly prefilled and with errors) or success report.
	// NB: $doform can be 'false' only if action was submitted AND executed without errors.
	if ($doform)
	{
		return $this->show_spec(compact('entity', 'action', 'filter', 'errors'));
	} else
	{
		return $this->do_show_spec_done($entity, $action, $filter);
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function main_do_item ($args, $entity, $action, $submitted, $filter, $parent, $itemid, $child)
{
	if (is_null($entity)) $entity = $this->get_default_entity();

	// Make default action to be a showing of the form without any errors.
	$doform = true;
	$submit = null;
	$errors = null;
	$newitemid = null;

	// Check if action was submitted (POST'ed), but not initially requested (GET'ed).
	if ($submitted && is_null($child))
	{
		try
		{
			$subargs = array();
			$subargs['entity'] = $entity;
			$subargs['files' ] = $this->build_initial_files();
			$subargs['post'  ] = $this->build_initial_post ();
			$subargs['get'   ] = $this->build_initial_get  ();
			$submit = $this->build_item_submit($subargs, true);

			$subargs = array();
			$subargs['entity'] = $entity;
			$subargs['action'] = $action;
			$subargs['filter'] = $filter;
			$subargs['parent'] = $parent;
			$subargs['itemid'] = $itemid;
			$subargs['submit'] = &$submit;
 			$errors = $this->exec_item($subargs, $newitemid);

			if ($this->have_error_inside($errors))
			{
				core::rollback();
			} else
			{
				core::commit();
				$doform = false;

				$subargs = array();
				$subargs['entity'] = $entity;
				$this->apply_item($subargs);
			}
		}
		catch (exception $exception)
		{
			try { core::rollback(); }
			catch (exception $exception_rollback) {}
			throw $exception;
		}
	}

	// Show form of action (possibly prefilled and with errors) or success report.
	// NB: $doform can be 'false' only if action was submitted AND executed without errors.
	if ($doform)
	{
		return $this->show_item(compact('entity', 'action', 'filter', 'parent', 'itemid', 'child', 'submit', 'errors'));
	} else
	{
		return $this->do_show_item_done($entity, $action, $filter, $parent, isset($newitemid) ? $newitemid : $itemid);
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function main_do_list ($args, $entity, $action, $submitted, $filter, $parent)
{
	if (is_null($entity)) $entity = $this->get_default_entity();

	// Make default action to be a showing of the form without any errors.
	$doform = true;
	$submit = null;
	$errors = null;

	// Check if action was submitted (POST'ed), but not initially requested (GET'ed).
	if ($submitted)
	{
		try
		{
			$subargs = array();
			$subargs['entity'] = $entity;
			$subargs['files' ] = $this->build_initial_files();
			$subargs['post'  ] = $this->build_initial_post ();
			$subargs['get'   ] = $this->build_initial_get  ();
			$submit = $this->build_list_submit($subargs, true);

			$subargs = array();
			$subargs['entity'] = $entity;
			$subargs['action'] = $action;
			$subargs['filter'] = $filter;
			$subargs['parent'] = $parent;
			$subargs['submit'] = &$submit;
 			$errors = $this->exec_list($subargs);

			if ($this->have_error_inside($errors))
			{
				core::rollback();
			} else
			{
				core::commit();
				$doform = false;

				$subargs = array();
				$subargs['entity'] = $entity;
				$this->apply_list($subargs);
			}
		}
		catch (exception $exception)
		{
			try { core::rollback(); }
			catch (exception $exception_rollback) {}
			throw $exception;
		}
	}

	// Show form of action (possibly prefilled and with errors) or success report.
	// NB: $doform can be 'false' only if action was submitted AND executed without errors.
	if ($doform)
	{
		return $this->show_list(compact('entity', 'action', 'filter', 'parent', 'submit', 'errors'));
	} else
	{
		return $this->do_show_list_done($entity, $action, $filter, $parent);
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function exec_spec ($args)
{
//???	$parentaction = isset($args['parentaction']) ? $args['parentaction'] : null;
	$parentfailed = isset($args['parentfailed']) ? $args['parentfailed'] : null;
	$entity       = isset($args['entity'      ]) ? $args['entity'      ] : null;
	$action       = isset($args['action'      ]) ? $args['action'      ] : null;
	$filter       = isset($args['filter'      ]) ? $args['filter'      ] : null;

	if (is_null($entity)) $entity = $this->get_default_entity();

	$errors = array(null=>array());

//???	if (is_null($action) && !is_null($parentaction)) $action = $this->guess_action_item($entity, $parentaction, $parent, $submit);
	if (is_null($action)) return null;

	$overaccess = $this->overaccess($entity);
	if (!in_array($action, $overaccess))
	{
		$errors[null][] = 'overaccess';
		return $errors;
	}

	if (!$this->have_error_inside($errors) && !$parentfailed)
		try
		{
			$errorsaction = $this->do_special($entity, $action, $filter);
			$errors[null] = array_merge($errors[null], is_array($errorsaction) ? $errorsaction : array());
		}
		catch (list_0_exception_duplicate $exception)
		{
			$errors[null][] = 'dupe';
		}

/*???
	if (isset($this->children[$entity]) && !empty($this->children[$entity]))
	{
		$childargs = array();
		$childargs['parentaction'] = $action;
		$childargs['parentfailed'] = $parentfailed || $this->have_error_inside($errors);
		$childargs['entity'      ] = null;
		$childargs['action'      ] = null;
		$childargs['filter'      ] = null;
		$childargs['parent'      ] = isset($itemid) ? $itemid : false;
		foreach ($this->children[$entity] as $childname => $childinfo)
		{
			if (isset($submit[$childinfo['field']]))
				$childargs['submit'] = &$submit[$childinfo['field']];
			$errors[$childinfo['field']] = core::call($childinfo['module'], 'exec_list', $childargs);
		}
	}
???*/
	if (!$this->have_error_inside($errors) && !$parentfailed)
	{
		core::event('exec_spec_success', 'spec executed successfuly', array(
			'entity'=>$entity,
			'action'=>$action,
			'filter'=>$filter));
	}

	return $errors;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function exec_item ($args, &$newitemid = null)
{
	$parentaction = isset($args['parentaction']) ? $args['parentaction'] : null;
	$parentfailed = isset($args['parentfailed']) ? $args['parentfailed'] : null;
	$entity       = isset($args['entity'      ]) ? $args['entity'      ] : null;
	$action       = isset($args['action'      ]) ? $args['action'      ] : null;
	$filter       = isset($args['filter'      ]) ? $args['filter'      ] : null;
	$parent       = isset($args['parent'      ]) ? $args['parent'      ] : null;
	$itemid       = isset($args['itemid'      ]) ? $args['itemid'      ] : null;
	if (isset($args['submit'])) $submit = &$args['submit']; else $submit = null; if (!is_array($submit)) $submit = array();

	if (is_null($entity)) $entity = $this->get_default_entity();

	$errors = array(null=>array());

	if (is_null($action) && !is_null($parentaction)) $action = $this->guess_action_item($entity, $parentaction, $parent, $submit);
	if (is_null($action)) return null;

	$overaccess = $this->overaccess($entity);
	if (!in_array($action, $overaccess))
	{
		$errors[null][] = 'overaccess';
		return $errors;
	}

	if ($this->is_action_orphan($entity, $action)) $itemid = false; else
	if (is_null($itemid)) $itemid = $this->guess_itemid($entity, $parent, $submit);

	$meta = null;
	$items = (is_null($itemid) || ($itemid === false) || (($parent === false) && !$this->is_action_forced($entity, $action))) ? array() : $this->do_read_list($entity, $filter, $parent, $itemid, $meta);
	if (($itemid !== false) && empty($items))
	{
		$errors[null]['fake'] = true;
		return $errors;
	}

	$itemold = empty($items) ? null : array_shift($items);

	$itemaccess = $this->itemaccess($entity, $itemid, $itemold);
	if (!in_array($action, $itemaccess))
	{
		$errors[null][] = 'itemaccess';
		return $errors;
	}

	$newsubmit = $this->do_handle($entity, $action, $itemid, $itemold, $submit);
	if (isset($newsubmit) && is_array($newsubmit))
		foreach ($newsubmit as $newsubmitfield => $newsubmitvalue)
			$submit[$newsubmitfield] = $newsubmitvalue;

	$itemnew = $this->merge_items($entity, $itemold, $parent, $submit);
	$errorsverify = $this->do_verify($entity, $action, $itemid, $itemnew, $itemold);
	$errors[null] = array_merge($errors[null], is_array($errorsverify) ? $errorsverify : array());
	if (!$this->have_error_inside($errors) && !$parentfailed)
		try
		{
			$errorsaction = $this->do_action($entity, $action, $itemid, $itemnew, $itemold, $newitemid);
			$errors[null] = array_merge($errors[null], is_array($errorsaction) ? $errorsaction : array());
		}
		catch (list_0_exception_duplicate $exception)
		{
			$errors[null][] = 'dupe';
		}

	//todo: сделать оповещение дочерних о том, что сменился parent с itemid на newitemid. а тоьлко после оповещения заменить.
	if (isset($newitemid)) { $itemid = $newitemid; if (array_key_exists('newitemid', $args)) $args['newitemid'] = $newitemid; }

	//todo: это временная мера против возникновения ошибок в admin_accounts?request, когда оно помещает запрос в список
	//todo: активаций, и не создает реального элемента в базе. в таком случае ничего не делать с дочерними модулями,
	//todo: так как они будут ругаться на фейковый ид родителя. предполагается, что если do_action() вернул null
	//todo: в качество нового идентификатора элемента, даже для orphan действий, то это означает что он сам обработал
	//todo: все данные, и никакой дальнейшей обработки не требуется.
	if ($itemid === false) return $errors;

	if (isset($this->children[$entity]) && !empty($this->children[$entity]))
	{
		$childargs = array();
		$childargs['parentaction'] = $action;
		$childargs['parentfailed'] = $parentfailed || $this->have_error_inside($errors);
		$childargs['entity'      ] = null;
		$childargs['action'      ] = null;
		$childargs['filter'      ] = null;
		$childargs['parent'      ] = isset($itemid) ? $itemid : false;
		foreach ($this->children[$entity] as $childname => $childinfo)
		{
			if (isset($submit[$childinfo['field']]))
				$childargs['submit'] = &$submit[$childinfo['field']];
			$errors[$childinfo['field']] = core::call($childinfo['module'], 'exec_list', $childargs);
		}
	}

	if (!$this->have_error_inside($errors) && !$parentfailed)
	{
		core::event('exec_item_success', 'item executed successfuly', array(
			'entity'=>$entity,
			'action'=>$action,
			'filter'=>$filter,
			'parent'=>$parent,
			'itemid'=>$itemid,
			'itemold'=>$itemold,
			'itemnew'=>$itemnew,
			'itemaccess'=>$itemaccess,
			'overaccess'=>$overaccess));
	}

	return $errors;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function exec_list ($args)
{
	$parentaction = isset($args['parentaction']) ? $args['parentaction'] : null;
	$entity       = isset($args['entity'      ]) ? $args['entity'      ] : null;
	$action       = isset($args['action'      ]) ? $args['action'      ] : null;
	$parent       = isset($args['parent'      ]) ? $args['parent'      ] : null;
	if (isset($args['submit'])) $submit = &$args['submit']; else $submit = null; if (!is_array($submit)) $submit = array();

	if (is_null($entity)) $entity = $this->get_default_entity();

	$errors = array(null=>array());

	if (($action == '') && ($parentaction != '')) $action = $this->guess_action_list($entity, $parentaction, $parent, $submit);
	if (($action == '')) return $errors;

	$overaccess = $this->overaccess($entity);
	if (!in_array($action, $overaccess))
	{
		$errors[null][] = 'overaccess';
		return $errors;
	}

	$itemargs = $args;
	foreach ($submit as $index => $item)
	{
		$itemargs['parentaction'] = $action;
		$itemargs['action'      ] = null;
		$itemargs['itemid'      ] = null;
		if (isset($submit[$index])) 
			$itemargs['submit'] = &$submit[$index];

		$temp = $this->exec_item($itemargs);
		if (!is_null($temp))
			$errors[$index] = $temp;
		else
			unset($submit[$index]);
	}

	return $errors;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function show_spec ($args)
{
//???	$parentaction = isset($args['parentaction']) ? $args['parentaction'] : null;
	$entity       = isset($args['entity'      ]) ? $args['entity'      ] : null;
	$action       = isset($args['action'      ]) ? $args['action'      ] : null;
	$filter       = isset($args['filter'      ]) ? $args['filter'      ] : null;
	$errors       = isset($args['errors'      ]) ? $args['errors'      ] : null; $errors = $this->merge_errors(null, $errors);

	if (is_null($entity)) $entity = $this->get_default_entity();

//???	if (($action == '') && ($parentaction != '')) $action = $this->guess_action_item($entity, $parentaction, $parent, $submit);
	if (($action == '')) return null;

	$overaccess = $this->overaccess($entity);
	if (in_array('overaccess', $errors[null]) || !in_array($action, $overaccess))
		return $this->do_show_spec_overaccess($entity, $action, $overaccess, $filter);

/*???
	if (!is_null($child))
	{
		if (isset($this->children[$entity]) && isset($this->children[$entity][$child]))
		{
			$childargs = array();
			$childargs['parent'] = $itemid;
			return core::call($this->children[$entity][$child]['module'], 'main', $childargs);
		} else
		throw new exception("Unknown child '{$child}' requested for entity '{$entity}'.");
	}

	$children = array();
	if (isset($this->children[$entity]) && !empty($this->children[$entity]))
	{
		$childargs = array();
		$childargs['parentaction'] = $action;
		$childargs['entity'      ] = null;
		$childargs['action'      ] = null;
		$childargs['filter'      ] = null;
		$childargs['parent'      ] = isset($itemid) ? $itemid : false;

		foreach ($this->children[$entity] as $childname => $childinfo)
		{
			if (in_array($action, $childinfo['avoid'])) continue;//??? rename to avoidaction?

			if (isset($submit[$childinfo['field']])) 
				$childargs['submit'] = &$submit[$childinfo['field']];
			if (isset($errors[$childinfo['field']])) 
				$childargs['errors'] =  $errors[$childinfo['field']];
			$children[$childname] = core::call($childinfo['module'], 'show_list', $childargs);
		}
	}
???*/

	return $this->do_show_spec_form($entity, $action, $overaccess, $filter, $errors[null]);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function show_item ($args)
{
	$parentaction = isset($args['parentaction']) ? $args['parentaction'] : null;
	$entity       = isset($args['entity'      ]) ? $args['entity'      ] : null;
	$action       = isset($args['action'      ]) ? $args['action'      ] : null;
	$filter       = isset($args['filter'      ]) ? $args['filter'      ] : null;
	$parent       = isset($args['parent'      ]) ? $args['parent'      ] : null;
	$itemid       = isset($args['itemid'      ]) ? $args['itemid'      ] : null;
	$child        = isset($args['child'       ]) ? $args['child'       ] : null;
	if (isset($args['submit'])) $submit = &$args['submit']; else $submit = null; if (!is_array($submit)) $submit = array();
	$errors       = isset($args['errors'      ]) ? $args['errors'      ] : null; $errors = $this->merge_errors(null, $errors);

	if (is_null($entity)) $entity = $this->get_default_entity();

	if (($action == '') && ($parentaction != '')) $action = $this->guess_action_item($entity, $parentaction, $parent, $submit);
	if (($action == '')) return null;

	if ($this->is_action_orphan($entity, $action)) $itemid = false; else
	if (is_null($itemid)) $itemid = $this->guess_itemid($entity, $parent, $submit);

	$overaccess = $this->overaccess($entity);
	if (in_array('overaccess', $errors[null]) || !in_array($action, $overaccess))
		return $this->do_show_item_overaccess($entity, $action, $overaccess, $filter, $parent, $itemid);

	$meta = null;
	$items = (is_null($itemid) || ($itemid === false) || ($parent === false)) ? array() : $this->do_read_list($entity, $filter, $parent, $itemid, $meta);
	if (in_array('fake', $errors[null]) || (($itemid !== false) && empty($items)))
		return $this->do_show_item_absent($entity, $action, $overaccess, $filter, $parent, $itemid);

	$itemold = empty($items) ? null : array_shift($items);
	$itemold = $this->merge_items($entity, $itemold, $parent, null);

	$itemaccess = $this->itemaccess($entity, $itemid, $itemold);
	if (in_array('itemaccess', $errors[null]) || !in_array($action, $itemaccess))
		return $this->do_show_item_itemaccess($entity, $action, $overaccess, $itemaccess, $filter, $parent, $itemid, $itemold);

	if (!is_null($child))
	{
		if (isset($this->children[$entity]) && isset($this->children[$entity][$child]))
		{
			$childargs = array();
			$childargs['parent'] = $itemid;
			return core::call($this->children[$entity][$child]['module'], 'main', $childargs);
		} else
		throw new exception("Unknown child '{$child}' requested for entity '{$entity}'.");
	}

	$itemnew = $this->merge_items($entity, $itemold, $parent, $submit);

	//!!!todo: group this to single method $this->format($entity, $itemid, $item);
	$itemoldf = $itemold;
	$itemnewf = $itemnew;
	$itemoldf_ = $this->do_format($entity, $action, $itemid, $itemold);
	$itemnewf_ = $this->do_format($entity, $action, $itemid, $itemnew);
	if (is_array($itemoldf_))
		foreach ($itemoldf_ as $field => $value)
			if (array_key_exists($field, $itemoldf))
				$itemoldf[$field] = $value;
	if (is_array($itemnewf_))
		foreach ($itemnewf_ as $field => $value)
			if (array_key_exists($field, $itemnewf))
				$itemnewf[$field] = $value;

	$children = array();
	if (isset($this->children[$entity]) && !empty($this->children[$entity]))
	{
		$childargs = array();
		$childargs['parentaction'] = $action;
		$childargs['entity'      ] = null;
		$childargs['action'      ] = null;
		$childargs['filter'      ] = null;
		$childargs['parent'      ] = isset($itemid) ? $itemid : false;

		foreach ($this->children[$entity] as $childname => $childinfo)
		{
			if (in_array($action, $childinfo['avoid'])) continue;//??? rename to avoidaction?

			if (isset($submit[$childinfo['field']])) 
				$childargs['submit'] = &$submit[$childinfo['field']];
			if (isset($errors[$childinfo['field']])) 
				$childargs['errors'] =  $errors[$childinfo['field']];
			$children[$childname] = core::call($childinfo['module'], 'show_list', $childargs);
		}
	}

	return $this->do_show_item_form($entity, $action, $overaccess, $itemaccess, $filter, $parent, $itemid, $itemold, $itemnew, $itemoldf, $itemnewf, $errors[null], $children);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function show_list ($args)
{
	$parentaction = isset($args['parentaction']) ? $args['parentaction'] : null;
	$entity       = isset($args['entity'      ]) ? $args['entity'      ] : null;
	$action       = isset($args['action'      ]) ? $args['action'      ] : null;
	$filter       = isset($args['filter'      ]) ? $args['filter'      ] : null;
	$parent       = isset($args['parent'      ]) ? $args['parent'      ] : null;
	if (isset($args['submit'])) $submit = &$args['submit']; else $submit = null; if (!is_array($submit)) $submit = array();
	$errors       = isset($args['errors'      ]) ? $args['errors'      ] : null; $errors = $this->merge_errors(null, $errors);

	if (is_null($entity)) $entity = $this->get_default_entity();

	if (($action == '') && ($parentaction != '')) $action = $this->guess_action_list($entity, $parentaction, $parent, $submit);
	if (($action == '')) return null;

	$overaccess = $this->overaccess($entity);
	if (in_array('overaccess', $errors[null]) || !in_array($action, $overaccess))
		return $this->do_show_list_overaccess($entity, $action, $overaccess, $filter, $parent);

	$meta = null;
	$items = ($parent === false) && !$this->is_action_forced($entity, $action) ? array() : $this->do_read_list($entity, $filter, $parent, null, $meta);
	$structs = $this->merge_lists($entity, $items, $parent, $submit);

	if (!is_array($meta)) $meta = array();
	$meta['pagecount'] = count($structs);
	$meta['fullcount'] = (isset($meta['count']) ? $meta['count'] : $meta['pagecount']) + (count($structs) - count($items));

	if (empty($structs))
		return $this->do_show_list_empty($entity, $action, $overaccess, $filter, $parent, $meta);

	//todo: resorting here. optionally. only if needed by module.

	//todo: здесь сделать prefetch данных в дочерних элементах, передавая им каждому полный itemids в роли parents.

	$lines = array();
	$pageindex = 1;
	foreach ($structs as $struct)
	{
		$listindex = isset($struct['id']) ? $meta['offset'] + $pageindex : null;
		$itemid   = isset($struct['id']) ? $struct['id'] : false;
		$itemold  = $struct['old'];
		$itemnew  = $struct['new'];
		$itemoldf = $struct['old'];
		$itemnewf = $struct['new'];
		$itemerrors = $this->merge_errors(null, isset($struct['index']) && isset($errors[$struct['index']]) ? $errors[$struct['index']] : null);
		$itemerrors = $itemerrors[null];
		$itemaccess = $this->itemaccess($entity, $itemid, $itemold);

		//!!! regroup to #this->format(....);
		$itemoldf_ = $this->do_format($entity, $action, $itemid, $itemold);
		$itemnewf_ = $this->do_format($entity, $action, $itemid, $itemnew);
		if (is_array($itemoldf_))
			foreach ($itemoldf_ as $field => $value)
				if (array_key_exists($field, $itemoldf))
					$itemoldf[$field] = $value;
		if (is_array($itemnewf_))
			foreach ($itemnewf_ as $field => $value)
				if (array_key_exists($field, $itemnewf))
					$itemnewf[$field] = $value;

		$children = array();
		if (isset($this->children[$entity]) && !empty($this->children[$entity]))
		{
			$childargs = array();
			$childargs['parentaction'] = $action;
			$childargs['entity'      ] = null;
			$childargs['action'      ] = null;
			$childargs['filter'      ] = null;
			$childargs['parent'      ] = $itemid;
			foreach ($this->children[$entity] as $childname => $childinfo)
			{
				if (in_array($action, $childinfo['avoid'])) continue;//??? rename to avoidaction?

				if (isset($struct['index']) && isset($submit[$struct['index']]) && is_array($submit[$struct['index']]) && isset($submit[$struct['index']][$childinfo['field']]))
					$childargs['submit'] = &$submit[$struct['index']][$childinfo['field']];
				if (isset($struct['index']) && isset($errors[$struct['index']]) && is_array($errors[$struct['index']]) && isset($errors[$struct['index']][$childinfo['field']]))
					$childargs['errors'] =  $errors[$struct['index']][$childinfo['field']];
				$children[$childname] = core::call($childinfo['module'], 'show_list', $childargs);
			}
		}
		$line = $this->do_show_list_line($entity, $action, $overaccess, $itemaccess, $filter, $parent, $meta, 
				$pageindex, $listindex, $itemid, $itemold, $itemnew, $itemoldf, $itemnewf, $itemerrors, $children);
		if ($line == '') continue;
		else $lines[] = $line;
		$pageindex++;
	}
	$glue = $this->do_show_list_glue($entity, $action, $overaccess, $filter, $parent, $meta);
	$glued = implode($glue, $lines);
	$view = $this->do_show_list_list($entity, $action, $overaccess, $filter, $parent, $meta, $lines, $glued, $errors[null]);
	return $view == '' ? $glued : $view;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Вспомогательный рекурсивный метод для реструктуризации FILES. Используется в методе ниже.
private function build_initial_files_recurse (&$result, $value, $metaindex)
{
	if (is_array($value))
	{
		foreach ($value as $key => $val)
		{
			if (!array_key_exists($key, $result) || !is_array($result[$key]))
				$result[$key] = array();
			$this->build_initial_files_recurse($result[$key], $val, $metaindex);
		}
	} else
	{
		$result[$metaindex] = $value;
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Получение данных из FILES. Перегруппировываем значения так, чтобы мета-поля (имя файла, размер и
// т.п.) были самыми последними ключами в иерархии ключей. Т.о., структура FILES становится строго
// подобна структуре POST и GET.
private function build_initial_files ()
{
	$root = $_FILES;
	$result = array();
	if (is_array($root)) foreach ($root as $key1 => $sub)
	{
		$result[$key1] = array();
		if (is_array($sub)) foreach ($sub as $key2 => $value)
			$this->build_initial_files_recurse($result[$key1], $value, $key2);
	}
	if (isset($this->dataroot))
		$result = isset($result[$this->dataroot]) ? $result[$this->dataroot] : null;
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Получение данных из POST.
private function build_initial_post ()
{
	$root = $_POST;
	if (isset($this->dataroot))
		$root = isset($root[$this->dataroot]) ? $root[$this->dataroot] : null;
	return $root;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Получение данных из GET.
private function build_initial_get ()
{
	$root = $_GET;
	if (isset($this->dataroot))
		$root = isset($root[$this->dataroot]) ? $root[$this->dataroot] : null;
	return $root;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Публичный метод, который, собственно, и вызывается рекурсивно из build'ов других модулей
// более высокого уровня вложенности. Все прочие build_методы - лишь вспомогательные.
public function build_item_submit ($args)
{
	$entity = isset($args['entity']) ? $args['entity'] : null;
	if (is_null($entity)) $entity = $this->get_default_entity();

	$files = isset($args['files']) && is_array($args['files']) ? $args['files'] : array();
	$post  = isset($args['post' ]) && is_array($args['post' ]) ? $args['post' ] : array();
	$get   = isset($args['get'  ]) && is_array($args['get'  ]) ? $args['get'  ] : array();

	$result = $this->do_build($entity, $files, $post, $get);
	if (!is_array($result)) $result = array();

	if (isset($this->children[$entity]) && !empty($this->children[$entity]))
	{
		$childargs = array();
		foreach ($this->children[$entity] as $childname => $childinfo)
		{
			$childargs['files'] = isset($files[$childinfo['field']]) ? $files[$childinfo['field']] : null;
			$childargs['post' ] = isset($post [$childinfo['field']]) ? $post [$childinfo['field']] : null;
			$childargs['get'  ] = isset($get  [$childinfo['field']]) ? $get  [$childinfo['field']] : null;
			$result[$childinfo['field']] = core::call($childinfo['module'], 'build_list_submit', $childargs);
		}
	}
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function build_list_submit ($args)
{
	$entity = isset($args['entity']) ? $args['entity'] : null;
	if (is_null($entity)) $entity = $this->get_default_entity();

	$files = isset($args['files']) && is_array($args['files']) ? $args['files'] : array();
	$post  = isset($args['post' ]) && is_array($args['post' ]) ? $args['post' ] : array();
	$get   = isset($args['get'  ]) && is_array($args['get'  ]) ? $args['get'  ] : array();

	$result = array();
	$childargs = array();
	$childargs['entity'] = $entity;
	foreach ($this->merge_indexes(array($files, $post, $get)) as $index)
	{
		$childargs['files'] = isset($files[$index]) ? $files[$index] : null;
		$childargs['post' ] = isset($post [$index]) ? $post [$index] : null;
		$childargs['get'  ] = isset($get  [$index]) ? $get  [$index] : null;
		$result[$index] = $this->build_item_submit($childargs);
	}
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Публичный метод, который, собственно, и вызывается рекурсивно из apply'ов других модулей
// более высокого уровня вложенности. Все прочие apply_методы - лишь вспомогательные.
private function apply_all ($args)
{
	$entity = isset($args['entity']) ? $args['entity'] : null;
	if (is_null($entity)) $entity = $this->get_default_entity();

	$this->do_apply($entity);

	if (isset($this->children[$entity]))
	{
		$childargs = array();
		foreach ($this->children[$entity] as $childname => $childinfo)
		{
			core::call($childinfo['module'], 'apply_list', $childargs);
		}
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function apply_list ($args)
{
	$this->apply_all($args);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function apply_item ($args)
{
	$this->apply_all($args);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function apply_spec ($args)
{
	$this->apply_all($args);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

private function have_error_inside ($errors)
{
	if (!is_array($errors)) return false;
	$result = false;
	foreach ($errors as $key => $val)
		if ($key == '')
			$result = $result || (is_array($val) && !empty($val));
		else
			$result = $result || ($this->have_error_inside($val));
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

private function merge_errors ($original, $additions)
{
	$result = $original;
	if (!is_array($result)) $result = array();
	if (!isset($result[null]) || !is_array($result[null])) $result[null] = array();

	if (is_array($additions))
		foreach ($additions as $key => $val)
			if ($key == '')
				$result[$key] = array_merge($result[$key], is_array($val) ? $val : array($val));
			else
				$result[$key] = $this->merge_errors(isset($result[$key]) ? $result[$key] : null, $val);
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

private function merge_indexes ($arrays)
{
	$result = array();
	if (is_array($arrays))
		foreach ($arrays as $array)
			if (is_array($array))
				foreach ($array as $index => $data)
					if (!in_array($index, $result))
						$result[] = $index;
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

private function merge_items ($entity, $original, $parent, $override)
{
	$default = $this->get_default_fields($entity, $parent);
	if (!is_array($original)) $original = array();
	if (!is_array($override)) $override = array();
	if (!is_array($default )) $default  = array();

	$result = array();
	foreach ($default as $field => $__no_matter__)
		$result[$field] =
			(array_key_exists($field, $override) ? $override[$field] :
			(array_key_exists($field, $original) ? $original[$field] :
			(array_key_exists($field, $default ) ? $default [$field] :
			null)));
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

private function merge_lists ($entity, $originals, $parent, $overrides)
{
	if (!is_array($originals)) $originals = array();
	if (!is_array($overrides)) $overrides = array();

	//...
	$originalids = array();
	foreach ($originals as $index => $item) if ($index != '')
		$originalids[$index] = $this->guess_itemid($entity, $parent, $item);
	$overrideids = array();
	foreach ($overrides as $index => $item) if ($index != '')
		$overrideids[$index] = $this->guess_itemid($entity, $parent, $item);

	//...
	$result = array();
	$resultrow = array();
	foreach ($originals as $originalindex => $item) if ($originalindex != '')
	{
		$overrideindex = is_null($originalids[$originalindex]) ? false : array_search($originalids[$originalindex], $overrideids);
		if ($overrideindex === false) $overrideindex = null;
		$resultrow['id'   ] = $originalids[$originalindex];
		$resultrow['old'  ] = $this->merge_items($entity, $item, $parent, null);
		$resultrow['new'  ] = $this->merge_items($entity, $resultrow['old'], $parent, isset($overrideindex) ? $overrides[$overrideindex] : null);
		$resultrow['index'] = $overrideindex;
		$result[] = $resultrow;
	}
	foreach ($overrides as $overrideindex => $item) if ($overrideindex != '')
	if (is_null($overrideids[$overrideindex]) || !in_array($overrideids[$overrideindex], $originalids))
	{
		$resultrow['id'   ] = null/*NB: it must be null for new elements, but not $overrideids[$overrideindex]*/;
		$resultrow['old'  ] = null;
		$resultrow['new'  ] = $this->merge_items($entity, $resultrow['old'], $parent, $item);
		$resultrow['index'] = $overrideindex;
		$result[] = $resultrow;
	}
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

private $format_value__module;
private $format_value__args;

private function format_value__callback ($matches)//??? rename somehow?
{
	$this->format_value__args['parameter'] = $matches[1];
	return core::call($this->format_value__module, 'embed', $this->format_value__args);
}


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function embed ($args)
{
	$entity = isset($args['entity'   ]) ? $args['entity'   ] : null;
	$itemid = isset($args['parameter']) ? $args['parameter'] : null;//??? itemid? но в случае картинок у нас не itemid, а order. а могут быть и другие поля. как быть?
	$parent = isset($args['parent'   ]) ? $args['parent'   ] : null;

	$subargs = array();
	$subargs['entity'] = $entity;
	$subargs['action'] = 'embed';
	$subargs['parent'] = $parent;
	$subargs['itemid'] = $itemid;
	return $this->show_item($subargs);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function embed_children ($entity, $itemid, $value)
{
	$result = $value;
	if (isset($this->children[$entity]) && !empty($this->children[$entity]))
	{
		$this->format_value__args = array();
		$this->format_value__args['parent'] = isset($itemid) ? $itemid : false;
		foreach ($this->children[$entity] as $childname => $childinfo)
		{
//??? avoidformat?			if (in_array($action, $childinfo['avoid'])) continue;

			$this->format_value__module = $childinfo['module'];
			$result = preg_replace_callback("|\\{ {$childinfo['tag']} =? ([^\\}]*?) \\}|xi", array(&$this, 'format_value__callback'), $result);
		}
	}
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_args_spec_overaccess ($entity, $action, $overaccess, $filter) { return array(); }
protected function do_show_spec_overaccess ($entity, $action, $overaccess, $filter)
{
	$args = $this->get_args_spec_overaccess($entity, $action, $overaccess, $filter);
	if (!is_array($args)) $args = array();
	$args['overaccess'] = $overaccess;
	$args['entity'    ] = $entity;
	$args['action'    ] = $action;
	$args['filter'    ] = $filter;
	return core::template($entity . '_' . $action . '_' . 'overaccess', $args);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_args_spec_form ($entity, $action, $overaccess, $filter, $errors) { return array(); }
protected function do_show_spec_form ($entity, $action, $overaccess, $filter, $errors)
{
	$args = $this->get_args_spec_form($entity, $action, $overaccess, $filter, $errors);
	if (!is_array($args)) $args = array();
	$args['overaccess'] = $overaccess;
	$args['entity'    ] = $entity;
	$args['action'    ] = $action;
	$args['filter'    ] = $filter;
	$args['errors'    ] = $errors;
	return core::template($entity . '_' . $action . '_' . 'form', $args);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_args_spec_done ($entity, $action, $filter) { return array(); }
protected function do_show_spec_done ($entity, $action, $filter)
{
	$args = $this->get_args_spec_done($entity, $action, $filter);
	if (!is_array($args)) $args = array();
	$args['entity'] = $entity;
	$args['action'] = $action;
	$args['filter'] = $filter;
	return core::template($entity . '_' . $action . '_' . 'done', $args);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_args_item_overaccess ($entity, $action, $overaccess, $filter, $parent, $itemid) { return array(); }
protected function do_show_item_overaccess ($entity, $action, $overaccess, $filter, $parent, $itemid)
{
	$args = $this->get_args_item_overaccess($entity, $action, $overaccess, $filter, $parent, $itemid);
	if (!is_array($args)) $args = array();
	$args['overaccess'] = $overaccess;
	$args['entity'    ] = $entity;
	$args['action'    ] = $action;
	$args['filter'    ] = $filter;
	$args['parent'    ] = $parent;
	$args['itemid'    ] = $itemid;
	return core::template($entity . '_' . $action . '_' . 'overaccess', $args);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_args_item_itemaccess ($entity, $action, $overaccess, $itemaccess, $filter, $parent, $itemid, $itemold) { return array(); }
protected function do_show_item_itemaccess ($entity, $action, $overaccess, $itemaccess, $filter, $parent, $itemid, $itemold)
{
	$args = $this->get_args_item_itemaccess($entity, $action, $overaccess, $itemaccess, $filter, $parent, $itemid, $itemold);
	if (!is_array($args)) $args = array();
	$args['overaccess'] = $overaccess;
	$args['itemaccess'] = $itemaccess;
	$args['entity'    ] = $entity;
	$args['action'    ] = $action;
	$args['filter'    ] = $filter;
	$args['parent'    ] = $parent;
	$args['itemid'    ] = $itemid;
	$args['old'       ] = $itemold;
	return core::template($entity . '_' . $action . '_' . 'itemaccess', $args);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_args_item_absent ($entity, $action, $overaccess, $filter, $parent, $itemid) { return array(); }
protected function do_show_item_absent ($entity, $action, $overaccess, $filter, $parent, $itemid)
{
	$args = $this->get_args_item_absent($entity, $action, $overaccess, $filter, $parent, $itemid);
	if (!is_array($args)) $args = array();
	$args['overaccess'] = $overaccess;
	$args['entity'    ] = $entity;
	$args['action'    ] = $action;
	$args['filter'    ] = $filter;
	$args['parent'    ] = $parent;
	$args['itemid'    ] = $itemid;
	return core::template($entity . '_' . $action . '_' . 'absent', $args);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_args_item_form ($entity, $action, $overaccess, $itemaccess, $filter, $parent, $itemid, $itemold, $itemnew, $itemoldf, $itemnewf, $errors, $children) { return array(); }
protected function do_show_item_form ($entity, $action, $overaccess, $itemaccess, $filter, $parent, $itemid, $itemold, $itemnew, $itemoldf, $itemnewf, $errors, $children)
{
	$args = $this->get_args_item_form($entity, $action, $overaccess, $itemaccess, $filter, $parent, $itemid, $itemold, $itemnew, $itemoldf, $itemnewf, $errors, $children);
	if (!is_array($args)) $args = array();
	$args['overaccess'] = $overaccess;
	$args['itemaccess'] = $itemaccess;
	$args['entity'    ] = $entity;
	$args['action'    ] = $action;
	$args['filter'    ] = $filter;
	$args['parent'    ] = $parent;
	$args['itemid'    ] = $itemid;
	$args['old'       ] = $itemold;
	$args['new'       ] = $itemnew;
	$args['oldf'      ] = $itemoldf;
	$args['newf'      ] = $itemnewf;
	$args['errors'    ] = $errors;
	$args['children'  ] = $children;
	return core::template($entity . '_' . $action . '_' . 'form', $args);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_args_item_done ($entity, $action, $filter, $parent, $itemid) { return array(); }
protected function do_show_item_done ($entity, $action, $filter, $parent, $itemid)
{
	$args = $this->get_args_item_done($entity, $action, $filter, $parent, $itemid);
	if (!is_array($args)) $args = array();
	$args['entity'] = $entity;
	$args['action'] = $action;
	$args['filter'] = $filter;
	$args['parent'] = $parent;
	$args['itemid'] = $itemid;
	return core::template($entity . '_' . $action . '_' . 'done', $args);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_args_list_overaccess ($entity, $action, $overaccess, $filter, $parent) { return array(); }
protected function do_show_list_overaccess ($entity, $action, $overaccess, $filter, $parent)
{
	$args = $this->get_args_list_overaccess($entity, $action, $overaccess, $filter, $parent);
	if (!is_array($args)) $args = array();
	$args['overaccess'] = $overaccess;
	$args['entity'    ] = $entity;
	$args['action'    ] = $action;
	$args['filter'    ] = $filter;
	$args['parent'    ] = $parent;
	return core::template($entity . '_' . $action . '_' . 'overaccess', $args);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_args_list_empty ($entity, $action, $overaccess, $filter, $parent, $meta) { return array(); }
protected function do_show_list_empty ($entity, $action, $overaccess, $filter, $parent, $meta)
{
	$args = $this->get_args_list_empty($entity, $action, $overaccess, $filter, $parent, $meta);
	if (!is_array($args)) $args = array();
	$args['overaccess'] = $overaccess;
	$args['entity'    ] = $entity;
	$args['action'    ] = $action;
	$args['filter'    ] = $filter;
	$args['parent'    ] = $parent;
	$args['meta'      ] = $meta;
	return core::template($entity . '_' . $action . '_' . 'empty', $args);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_args_list_line ($entity, $action, $overaccess, $itemaccess, $filter, $parent, $meta, $pageindex, $listindex, $itemid, $itemold, $itemnew, $itemoldf, $itemnewf, $errors, $children) { return array(); }
protected function do_show_list_line ($entity, $action, $overaccess, $itemaccess, $filter, $parent, $meta, $pageindex, $listindex, $itemid, $itemold, $itemnew, $itemoldf, $itemnewf, $errors, $children)
{
	$args = $this->get_args_list_line($entity, $action, $overaccess, $itemaccess, $filter, $parent, $meta, $pageindex, $listindex, $itemid, $itemold, $itemnew, $itemoldf, $itemnewf, $errors, $children);
	if (!is_array($args)) $args = array();
	$args['overaccess'] = $overaccess;
	$args['itemaccess'] = $itemaccess;
	$args['entity'    ] = $entity;
	$args['action'    ] = $action;
	$args['filter'    ] = $filter;
	$args['parent'    ] = $parent;
	$args['meta'      ] = $meta;
	$args['meta'      ]['pageindex'] = $pageindex;
	$args['meta'      ]['listindex'] = $listindex;
	$args['itemid'    ] = $itemid;
	$args['old'       ] = $itemold;
	$args['new'       ] = $itemnew;
	$args['oldf'      ] = $itemoldf;
	$args['newf'      ] = $itemnewf;
	$args['errors'    ] = $errors;
	$args['children'  ] = $children;
	return core::template($entity . '_' . $action . '_' . 'line', $args);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_args_list_glue ($entity, $action, $overaccess, $filter, $parent, $meta) { return array(); }
protected function do_show_list_glue ($entity, $action, $overaccess, $filter, $parent, $meta)
{
	$args = $this->get_args_list_glue($entity, $action, $overaccess, $filter, $parent, $meta);
	if (!is_array($args)) $args = array();
	$args['overaccess'] = $overaccess;
	$args['entity'    ] = $entity;
	$args['action'    ] = $action;
	$args['filter'    ] = $filter;
	$args['parent'    ] = $parent;
	$args['meta'      ] = $meta;
	return core::template($entity . '_' . $action . '_' . 'glue', $args);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_args_list_list ($entity, $action, $overaccess, $filter, $parent, $meta, $lines, $glued, $errors) { return array(); }
protected function do_show_list_list ($entity, $action, $overaccess, $filter, $parent, $meta, $lines, $glued, $errors)
{
	$args = $this->get_args_list_list($entity, $action, $overaccess, $filter, $parent, $meta, $lines, $glued, $errors);
	if (!is_array($args)) $args = array();
	$args['overaccess'] = $overaccess;
	$args['entity'    ] = $entity;
	$args['action'    ] = $action;
	$args['filter'    ] = $filter;
	$args['parent'    ] = $parent;
	$args['meta'      ] = $meta;
	$args['lines'     ] = $lines;
	$args['glued'     ] = $glued;
	$args['errors'    ] = $errors;
	return core::template($entity . '_' . $action . '_' . 'list', $args);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_args_list_done ($entity, $action, $filter, $parent) { return array(); }
protected function do_show_list_done ($entity, $action, $filter, $parent)
{
	$args = $this->get_args_list_done($entity, $action, $filter, $parent);
	if (!is_array($args)) $args = array();
	$args['entity'] = $entity;
	$args['action'] = $action;
	$args['filter'] = $filter;
	$args['parent'] = $parent;
	return core::template($entity . '_' . $action . '_' . 'done', $args);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function overaccess ($entity)
{
	$result = $this->get_overaccess($entity);
	if (!is_array($result)) $result = array();
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function itemaccess ($entity, $itemid, $item)
{
	$result = $this->get_itemaccess($entity, $itemid, $item);
	if (!is_array($result)) $result = array();
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function guess_itemid ($entity, $parent, $submit)
{
	return $this->get_guessed_itemid($entity, $parent, $submit);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function guess_item_fake ($entity, $parent, $submit)
{
	if (is_array($submit))
		foreach ($submit as $field => $value)
			if (!is_array($value))/* ignore arrays while checking for fakeness */
				if ($value != '')
					return false;
	return true;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function guess_action_item ($entity, $parentaction, $parent, $submit)
{
	if (in_array($parentaction, array('item', 'list'    ))) return 'item';
	if (in_array($parentaction, array('remove'          ))) return 'remove';
	if (in_array($parentaction, array('append', 'modify'))) return $this->get_guessed_action($entity, $parent, $submit);
	if (in_array($parentaction, array('massedit'        ))) return $this->get_guessed_action($entity, $parent, $submit);
	return null;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function guess_action_list ($entity, $parentaction, $parent, $submit)
{
	if (in_array($parentaction, array('item', 'list'    ))) return 'list';
	if (in_array($parentaction, array('remove'          ))) return 'remove';
	if (in_array($parentaction, array('append', 'modify'))) return 'massedit';
	if (in_array($parentaction, array('massedit'        ))) return null;//NB: impossible! list can no be a child of another list. only of an item.
	return null;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function is_action_atomic ($entity, $action)
{
	if (in_array($action, array('item', 'append', 'modify', 'remove'))) return true;
	if (in_array($action, array('list', 'massedit'))) return false;
	return null;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function is_action_orphan ($entity, $action)
{
	return in_array($action, array('append'));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function is_action_forced ($entity, $action)
{
	return false;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_default_entity	()								{ return null;		}
protected function get_default_fields	($entity, $parent)						{ return array();	}
protected function get_guessed_itemid	($entity, $parent, $submit)					{ return null;		}
protected function get_guessed_action	($entity, $parent, $submit)					{ return null;		}
protected function get_overaccess	($entity)							{ return array();	}
protected function get_itemaccess	($entity, $itemid, $item)					{ return array();	}
protected function do_parse_request	($args)								{ return null;		}
protected function do_build		($entity, $files, $post, $get)					{ return array();	}
protected function do_read_list		($entity, $filter, $parent, $itemid, &$meta)			{ return array();	}
protected function do_handle		($entity, $action, $itemid, $itemold, $submit)			{ return array();	}
protected function do_verify		($entity, $action, $itemid, $itemnew, $itemold)			{ return array();	}
protected function do_action		($entity, $action, $itemid, $itemnew, $itemold, &$newitemid)	{ return array();	}
protected function do_apply		($entity)							{ return null;		}
protected function do_embed		($entity, $parameter, $parent)					{ return null;		}
protected function do_format		($entity, $action, $itemid, $item)				{ return $item;		}
protected function do_special		($entity, $action, $filter)					{ return array();	}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

?>