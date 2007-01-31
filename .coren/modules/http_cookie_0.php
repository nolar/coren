<?php defined('CORENINPAGE') or die('Hack!');
####################################################################################################
####################################################################################################
####################################################################################################
#
####################################################################################################
####################################################################################################
####################################################################################################
#
class http_cookie_0 extends coren_module
{
#
####################################################################################################
####################################################################################################
####################################################################################################
#
protected $autolock;
protected $cookie_name;
protected $cookie_domain = '';
protected $cookie_path   = '/';
protected $cookie_secure = false;
protected $cookie_expire = null;
#
####################################################################################################
#
protected $wasset;
protected $value ;
protected $expire;
#
####################################################################################################
#
function __construct ($configs)
{
	parent::__construct($configs);

	if (isset($configs['autolock'     ])) $this->autolock      = $configs['autolock'     ];
	if (isset($configs['cookie_name'  ])) $this->cookie_name   = $configs['cookie_name'  ];
	if (isset($configs['cookie_domain'])) $this->cookie_domain = $configs['cookie_domain'];
	if (isset($configs['cookie_path'  ])) $this->cookie_path   = $configs['cookie_path'  ];
	if (isset($configs['cookie_secure'])) $this->cookie_secure = $configs['cookie_secure'];
	if (isset($configs['cookie_expire'])) $this->cookie_expire = $configs['cookie_expire'];
}
#
####################################################################################################
####################################################################################################
####################################################################################################
#
public function rst ($data)
{
	if (!$this->wasset || !$this->autolock)
	{
		if (!$this->wasset) coren::handler('send', coren::event_for_stage_epiwork);
		$this->wasset = true;
		$this->value  = '';
		$this->expire = null;
	}
}
#
####################################################################################################
#
public function set ($data)
{
	if (!$this->wasset || !$this->autolock)
	{
		if (!$this->wasset) coren::handler('send', coren::event_for_stage_epiwork);
		$this->wasset = true;
		$this->value  = isset($data['value' ]) ? $data['value' ] : null;
		$this->expire = isset($data['expire']) ? $data['expire'] : null;
	}
}
#
####################################################################################################
#
public function send ($data)
{
	if ($this->wasset && !is_null($this->value))
	{
		//
//!!!		if (headers_sent()) throw?

		$value  = (string ) $this->value;
		$expire = (integer) (isset($this->cookie_expire) ? $this->cookie_expire : (isset($this->expire) ? $this->expire : 0));
		$expire = $expire > 0 ? time() + $expire : 0;
		setcookie($this->cookie_name, $value, $expire, $this->cookie_path, $this->cookie_domain, $this->cookie_secure);
	}
}
#
####################################################################################################
####################################################################################################
####################################################################################################
}
?>