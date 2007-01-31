<?php defined('CORENINPAGE') or die('Hack!');
//todo: 1. управление группами (membership) сразу из аккаунта. как сделать? дочерним модулем?
//todo: 2. управление дополнительными полями. e-mail, fio... и вообще сюда надо пиклеить profile (а ля modify).
//todo: 3. в форме изменения или профайла сделать псевдо-пароль. то есть такой пароль из звездочек (количество конфигурируемо),
//todo:    присылание которого в POST означало бы что пароль не изменился. фактически при этом происходит частичный запрет
//todo:    на использование паролей из звездочек.

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!core::depend('list_0')) return;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class admin_accounts_0_exception_duplicate extends list_0_exception_duplicate {}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class admin_accounts_0 extends list_0 implements dbaware
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected $default_page;
protected $default_size;
protected $default_skip;
protected $default_mask;
protected $default_sorting;
protected $default_reverse;

protected $grant_view;
protected $grant_edit;
protected $grant_profile;
protected $grant_email  ;
protected $grant_request;
protected $grant_confirm;

protected $activator;
protected $activator_email;

protected $pseudo_password;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function __construct ($configs)
{
	parent::__construct($configs);

	$this->default_page = core::find_scalar(array($configs), array('default_page'), null);
	$this->default_size = core::find_scalar(array($configs), array('default_size'), null);
	$this->default_skip = core::find_scalar(array($configs), array('default_skip'), null);

	$this->grant_view = core::find_scalar(array($configs), array('grant_view'), null);
	$this->grant_edit = core::find_scalar(array($configs), array('grant_edit'), null);
	$this->grant_profile = core::find_scalar(array($configs), array('grant_profile'), null);
	$this->grant_email   = core::find_scalar(array($configs), array('grant_email'  ), null);
	$this->grant_request = core::find_scalar(array($configs), array('grant_request'), null);
	$this->grant_confirm = core::find_scalar(array($configs), array('grant_confirm'), null);
	if (!isset($this->grant_view)) throw new exception("Misconfig: grant_view.");
	if (!isset($this->grant_edit)) throw new exception("Misconfig: grant_edit.");
	if (!isset($this->grant_profile)) throw new exception("Misconfig: grant_profile.");
	if (!isset($this->grant_email  )) throw new exception("Misconfig: grant_email."  );
	if (!isset($this->grant_request)) throw new exception("Misconfig: grant_request.");
	if (!isset($this->grant_confirm)) throw new exception("Misconfig: grant_confirm.");

	$this->activator = core::find_scalar(array($configs), array('activator'), null);
	$this->activator_email = core::find_scalar(array($configs), array('activator_email'), null);
	if (is_null($this->activator)) throw new exception("msconfig: activator");
	if (is_null($this->activator_email)) throw new exception("msconfig: activator_email");

	$this->pseudo_password = '**********';
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_default_entity ()
{
	return 'account';
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function get_default_fields ($entity, $parent)
{
	switch ($entity)
	{
		case 'account':
			return array(
			'account'		=> null,
			'created'		=> null,
			'entered'		=> null,
			'touched'		=> null,
			'disabled'		=> null,
			'reason'		=> null,
			'comment'		=> null,
			'email'			=> null,
			'agreement'		=> null,
			'logname'		=> null,
			'password'		=> null,
			'password1'		=> null,
			'password2'		=> null,
			'.fname'		=> null,
			'.sname'		=> null,
			'.tname'		=> null,
			'.email_messages'	=> null,
			'subscriptions'		=> null/*!!!*/);
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
		case 'account':
			$result = core::find_scalar(array($data), array('account', 'id'), null);
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
		case 'account':
			$itemid =        core::find_scalar(array($data), array('account', 'id'), null) != '';
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
		case 'account':
			//!!!
			break;
	}
	$result[] = 'profile';//mean: everyone can access profile, if he/she is its owner (checked in itemaccess()).
	if (core::grant($this->grant_email  ) || core::grant($this->grant_edit))
	{
		$result[] = 'email';
	}
	if (core::grant($this->grant_request) || core::grant($this->grant_edit))
	{
		$result[] = 'request';
	}
	if (core::grant($this->grant_confirm) || core::grant($this->grant_edit))
	{
		$result[] = 'confirm';
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
		case 'account':
			//!!!
			break;
	}
	if ((core::grant($this->grant_profile) && (core::until(null, 'account') == $item['account'])) || core::grant($this->grant_edit)) //mean: current user always can edit his/her profile.
	{
		$result[] = 'profile';
	}
	if (core::grant($this->grant_email  ) || core::grant($this->grant_edit))
	{
		$result[] = 'email';
	}
	if (core::grant($this->grant_request) || core::grant($this->grant_edit))
	{
		$result[] = 'request';
	}
	if (core::grant($this->grant_confirm) || core::grant($this->grant_edit))
	{
		$result[] = 'confirm';//??? no need in itemaccess. special actions are only in overaccess
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
	if (in_array($action, array('profile'))) return true;
	if (in_array($action, array('email'  ))) return null;
	if (in_array($action, array('request'))) return true;
	if (in_array($action, array('confirm'))) return null;
	return parent::is_action_atomic($entity, $action);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function is_action_orphan ($entity, $action)
{
	if (in_array($action, array('request'))) return true;
	return parent::is_action_orphan($entity, $action);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_parse_request ($args)
{
	$entity = null;
	$action = core::find_scalar(array($args, $_GET, $_POST), array('action'), null);
	$itemid = core::find_scalar(array($args, $_GET, $_POST), array('id'    ), null);
	$child  = core::find_scalar(array($args, $_GET, $_POST), array('child' ), null);
	$submit = core::find_scalar(array($_SERVER), array('REQUEST_METHOD'), null) == 'POST';

	if ($action == 'signin'  ) $action = 'request';// alias
	if ($action == 'register') $action = 'request';// alias
	if ($action == 'activate') $action = 'confirm';// alias

	if (!in_array($entity, array('account'))) $entity = null;
	if (!in_array($action, array('append', 'modify', 'remove', 'list', 'item', 'profile', 'email', 'request', 'confirm'))) $action = null;
	if (is_null($action)) $action = isset($itemid) ? 'item' : 'list';

	if (($action == 'profile') && is_null($itemid))
		$itemid = core::until(null, 'account');

	$filter = array();
	$filter['page'] = core::find_scalar(array($args, $_POST, $_GET), array('page'), null);
	$filter['size'] = core::find_scalar(array($args, $_POST, $_GET), array('size'), null);
	$filter['skip'] = core::find_scalar(array($args, $_POST, $_GET), array('skip'), null);
	$filter['sorting'] = core::find_scalar(array($args, $_POST, $_GET), array('sorting'), null);
	$filter['reverse'] = core::find_scalar(array($args, $_POST, $_GET), array('reverse'), null);
	$filter['mask'] = core::find_scalar(array($args, $_POST, $_GET), array('mask'), null);
	$filter['code'] = core::find_scalar(array($args, $_POST, $_GET), array('code'), null);

	if (($filter['code'] != '') && ($action == 'email'))
	{
		$action = 'email';
		$submit = true;
	}

	if (($filter['code'] != '') && ($action != 'email'))
	{
		$action = 'confirm';
		$submit = true;
	}

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
		case 'account':
			if (!is_array($filter)) $filter = array();
			$page = (integer) (isset($filter['page']) ? $filter['page'] : $this->default_page);
			$size = (integer) (isset($filter['size']) ? $filter['size'] : $this->default_size);
			$skip = (integer) (isset($filter['skip']) ? $filter['skip'] : $this->default_skip);
			$sorting = (string ) (isset($filter['sorting']) ? $filter['sorting'] : $this->default_sorting);
			$reverse = (string ) (isset($filter['reverse']) ? $filter['reverse'] : $this->default_reverse);
			$mask = (string ) (isset($filter['mask']) ? $filter['mask'] : $this->default_mask);

			$count = core::db('select_account_count', compact('parent', 'itemid', 'mask'));
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
			$items = core::db('select_account_data', compact('parent', 'itemid', 'mask', 'page', 'size', 'skip', 'count', 'offset', 'pagemin', 'pagemax', 'sorting', 'reverse'));

			foreach ($items as $index => $item)
			{
//				$items[$index]['password1'] = $items[$index]['password2'] = $items[$index]['password'];
				$items[$index]['password1'] = $items[$index]['password2'] = $this->pseudo_password;
				$items[$index]['information'] = array();
			}

			$ids = array(); foreach ($items as $item) $ids[] = $item['account'];
			$data = core::db('select_information', array('ids'=>array_unique($ids)));
			foreach ($data as $index => $value)
				if (array_key_exists($value['account'], $items))
					$items[$value['account']]['.' . $value['value']] = $value['content'];

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
		case 'account':
			if (in_array($action, array('profile')))
			{
				$result['logname'] = $itemold['logname'];//mean: user not allowed to change logname in profile.
			}

			$result['.email_messages'] = (bool) isset($submit['.email_messages']) ? $submit['.email_messages'] : null;//because it is checkbox, and if not checked - it is not sent in POST.

//???			$result['password1'] = isset($submit['password1']) ? $submit['password1'] : (isset($itemold['password']) ? $itemold['password' ] : null);
//???			$result['password2'] = isset($submit['password2']) ? $submit['password2'] : (isset($result['password1']) ? $result['password1' ] : null);
//???			$result['password' ] = isset($submit['password' ]) ? $submit['password' ] : (isset($result['password1']) ? $result['password1' ] : null);

			if ((isset($submit['password1']) && ($submit['password1'] === $this->pseudo_password))
			 && (isset($submit['password2']) && ($submit['password2'] === $this->pseudo_password)) )
				$result['password1'] = $result['password2'] = $itemold['password'];
			else
				$result['password'] = isset($submit['password1']) ? $submit['password1'] : (isset($submit['password']) ? $submit['password'] : null);
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
		case 'account':
			if (in_array($action, array('profile')))
			{
				if ($itemnew['email'] == ''                              ) { $result[] = 'email'; $result[] = 'email_empty';		} else
				if ($error = $this->check_email($itemnew['email'])       ) { $result[] = 'email'; $result[] = 'email_' . $error;	} else
				{}
				if ($itemnew['password1'] != $itemnew['password2']       ) { $result[] = 'password'; $result[] = 'password_mismatch';	} else
				if ($itemnew['password1'] == ''                          ) { $result[] = 'password'; $result[] = 'password_empty';	} else
				if ($error = $this->check_password($itemnew['password1'])) { $result[] = 'password'; $result[] = 'password_' . $error;	} else
				{}
			}
			if (in_array($action, array('request')))
			{
				if (!in_array($itemnew['agreement'], array('yes', '1'))  ) { $result[] = 'agreement'; }

				if ($itemnew['email'] == ''                              ) { $result[] = 'email'; $result[] = 'email_empty';		} else
				if ($error = $this->check_email($itemnew['email'])       ) { $result[] = 'email'; $result[] = 'email_' . $error;	} else
				{}
				if ($itemnew['logname'] == ''                            ) { $result[] = 'logname'; $result[] = 'logname_empty';	} else
				if ($this->exists_account($itemnew, $itemold)            ) { $result[] = 'logname'; $result[] = 'logname_exists';	} else
				if ($error = $this->check_logname($itemnew['logname'])   ) { $result[] = 'logname'; $result[] = 'logname_' . $error;	} else
				{}
				if ($itemnew['password1'] != $itemnew['password2']       ) { $result[] = 'password'; $result[] = 'password_mismatch';	} else
				if ($itemnew['password1'] == ''                          ) { $result[] = 'password'; $result[] = 'password_empty';	} else
				if ($error = $this->check_password($itemnew['password1'])) { $result[] = 'password'; $result[] = 'password_' . $error;	} else
				{}
			}
			if (in_array($action, array('append', 'modify')))
			{
				if ($itemnew['email'] == ''                              ) { $result[] = 'email'; $result[] = 'email_empty';		} else
				if ($error = $this->check_email($itemnew['email'])       ) { $result[] = 'email'; $result[] = 'email_' . $error;	} else
				{}
				if ($itemnew['logname'] == ''                            ) { $result[] = 'logname'; $result[] = 'logname_empty';	} else
				if ($this->exists_account($itemnew, $itemold)            ) { $result[] = 'logname'; $result[] = 'logname_exists';	} else
				if ($error = $this->check_logname($itemnew['logname'])   ) { $result[] = 'logname'; $result[] = 'logname_' . $error;	} else
				{}
				if ($itemnew['password1'] != $itemnew['password2']       ) { $result[] = 'password'; $result[] = 'password_mismatch';	} else
				if ($itemnew['password1'] == ''                          ) { $result[] = 'password'; $result[] = 'password_empty';	} else
				if ($error = $this->check_password($itemnew['password1'])) { $result[] = 'password'; $result[] = 'password_' . $error;	} else
				{}
			}
			break;
	}
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_action ($entity, $action, $itemid, $itemnew, $itemold, &$newitemid)
{
	switch ($entity)
	{
		case 'account':
			switch ($action)
			{
				case 'profile':
					if ($itemnew['email'] != $itemold['email'])
					{
						$mail = $itemnew['email'];
						$data = array('account'=>$itemnew['account'], 'email'=>$mail);
						core::call($this->activator_email, 'request', compact('mail', 'data'));
						$itemnew['email'] = $itemold['email'];//mean: do not actually change email until confirmed.
					}

					core::db('update_account', compact('itemid', 'itemnew'));

					$account = $itemnew['account'];
					$information = array();
					foreach ($itemnew as $key => $val)
						if ($key{0} == '.')
							$information[substr($key, 1)] = $val;
					core::db('update_information', compact('account', 'information'));

					break;

				case 'request':
					$mail = $itemnew['email'];
					$data = $itemnew;
					core::call($this->activator, 'request', compact('mail', 'data'));
					break;

				case 'append':
					$newitemid = core::db('insert_account', compact('itemnew'));

					$account = $newitemid;
					$information = array();
					foreach ($itemnew as $key => $val)
						if ($key{0} == '.')
							$information[substr($key, 1)] = $val;
					core::db('update_information', compact('account', 'information'));

					break;

				case 'modify':
					core::db('update_account', compact('itemid', 'itemnew'));

					$account = $itemnew['account'];
					$information = array();
					foreach ($itemnew as $key => $val)
						if ($key{0} == '.')
							$information[substr($key, 1)] = $val;
					core::db('update_information', compact('account', 'information'));

					break;

				case 'remove':
					core::db('delete_account', compact('itemid'));

					$account = $itemold['account'];
					core::db('delete_information', compact('account'));

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
		case 'account':
			break;
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_format ($entity, $action, $itemid, $item)
{
	$result = parent::do_format($entity, $action, $itemid, $item);
	switch ($entity)
	{
		case 'account':
			//???
			break;
	}
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function do_special ($entity, $action, $filter)
{
	$result = parent::do_special($entity, $action, $filter);
	switch ($entity)
	{
		case 'account':
			switch ($action)
			{
				case 'confirm':
					if (!is_array($filter)) $filter = array();
					$code = isset($filter['code']) ? $filter['code'] : null;

					$data = core::call($this->activator, 'confirm', compact('code'));
					if (!is_array($data))// actually: !is_null(). but we assume it is array always.
					{
						$result[] = 'wrong_code';
					} else
					{
						$itemnew = $data;
						$account = core::db('create_account', array('itemnew'=>$data));

						$information = array();
						foreach ($itemnew as $key => $val)
							if ($key{0} == '.')
								$information[substr($key, 1)] = $val;
						core::db('update_information', compact('account', 'information'));

						//!!! весьма такое специфичное действие в специфичном модуле, но лишь бы работало.
						$subargs = array();
						$subargs['entity'] = null;
						$subargs['action'] = null;
						$subargs['parent'] = $account;
						$subargs['submit'] = isset($data['subscriptions']) ? $data['subscriptions'] : null;
						$subargs['parentaction'] = 'append';
						core::call('admin_accountsubscriptions', 'exec_list', $subargs);
					}
					break;

				case 'email':
					if (!is_array($filter)) $filter = array();
					$code = isset($filter['code']) ? $filter['code'] : null;

					$data = core::call($this->activator_email, 'confirm', compact('code'));
					if (!is_array($data))// actually: !is_null(). but we assume it is array always.
					{
						$result[] = 'wrong_code';
					} else
					{
						$account = $data['account'];
						$email   = $data['email'  ];
						core::db('update_email', array('account'=>$account, 'email'=>$email));
					}
					break;

				default:
					return parent::do_special($entity, $action, $filter);
			}
			break;
	}
	return $result;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function check_email ($value)
{
	$length = strlen($value);
	$atpos = strpos($value, "@");
	if ($atpos === false) return 'no_at';// no @ in email
	if ($atpos == 0) return 'no_user';// box is empty
	if ($atpos == ($length-1)) return 'no_domain';// domain is empty
	if (strpos($value, "@", $atpos+1) !== false) return 'multi_at';// multiple @s
	//???todo: неразрешенные символы (составить список разрешенных)
	return null;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function check_logname ($value)
{
	$length = strlen($value);
	//???todo: неразрешенные символы (составить список разрешенных)
	return null;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function check_password ($value)
{
	$length = strlen($value);
//...	if ($length < 3) return 'small';
	//todo: не должен содержать национальных символов, так как они зависят от кодировки. разрегить только символы ASCII<128.
	//todo: хотя еще нужно проверить, будет ли это вызывать сбои. то есть будут ли по линуксом задания русскобуквенного пароля
	//todo: приводить к несовпадению его с тем же, но набранным в винде (при одинаковых meta...charset).
	return null;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

protected function exists_account ($itemnew, $itemold)
{
	if ($itemnew['logname'] != $itemold['logname'])
	{
		return core::db('exists_account', $itemnew);
	} else
	{
		return false;
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

?>