<?php defined('CORENINPAGE') or die('Hack!');
####################################################################################################
####################################################################################################
####################################################################################################
#
####################################################################################################
####################################################################################################
####################################################################################################
#
class identify_session_start_0_exception_duplicate extends exception {}
#
class identify_session_start_0 extends coren_module
{
#
####################################################################################################
####################################################################################################
####################################################################################################
#
const namespace = 'http://coren.numeri.net/namespaces/identify/';
protected $silent;
protected $hidden;
protected $prefix;
#
protected $event_for_session_start_context	= 'identify.session.start?context'	;
protected $event_for_session_start_credentials	= 'identify.session.start?credentials'	;
protected $event_for_session_start_acknowledge	= 'identify.session.start?acknowledge'	;
protected $event_for_session_start_identifier	= 'identify.session.start?identifier'	;
protected $event_for_session_start_failed	= 'identify.session.start!failed'	;
protected $event_for_session_start_successed	= 'identify.session.start!successed'	;
protected  $name_for_session_start_failed   ;
protected  $name_for_session_start_successed;
protected  $slot_for_session_start_failed   ;
protected  $slot_for_session_start_successed;
#
protected $trycount;
protected $period_normal;
protected $period_long;
#
####################################################################################################
#
function __construct ($configs)
{
	parent::__construct($configs);

	if (isset($configs['silent'])) $this->silent = $configs['silent'];
	if (isset($configs['hidden'])) $this->hidden = $configs['hidden'];
	if (isset($configs['prefix'])) $this->prefix = $configs['prefix'];

	if (isset($configs['event_for_session_start_context'    ])) $this->event_for_session_start_context     = $configs['event_for_session_start_context'    ];
	if (isset($configs['event_for_session_start_credentials'])) $this->event_for_session_start_credentials = $configs['event_for_session_start_credentials'];
	if (isset($configs['event_for_session_start_acknowledge'])) $this->event_for_session_start_acknowledge = $configs['event_for_session_start_acknowledge'];
	if (isset($configs['event_for_session_start_identifier' ])) $this->event_for_session_start_identifier  = $configs['event_for_session_start_identifier' ];
	if (isset($configs['event_for_session_start_failed'     ])) $this->event_for_session_start_failed      = $configs['event_for_session_start_failed'     ];
	if (isset($configs['event_for_session_start_successed'  ])) $this->event_for_session_start_successed   = $configs['event_for_session_start_successed'  ];
	if (isset($configs[ 'name_for_session_start_failed'     ])) $this-> name_for_session_start_failed      = $configs[ 'name_for_session_start_failed'     ];
	if (isset($configs[ 'name_for_session_start_successed'  ])) $this-> name_for_session_start_successed   = $configs[ 'name_for_session_start_successed'  ];
	if (isset($configs[ 'slot_for_session_start_failed'     ])) $this-> slot_for_session_start_failed      = $configs[ 'slot_for_session_start_failed'     ];
	if (isset($configs[ 'slot_for_session_start_successed'  ])) $this-> slot_for_session_start_successed   = $configs[ 'slot_for_session_start_successed'  ];

	if(isset($configs['trycount'     ])) $this->trycount      = $configs['trycount'     ];
	if(isset($configs['period_normal'])) $this->period_normal = $configs['period_normal'];
	if(isset($configs['period_long'  ])) $this->period_long   = $configs['period_long'  ];
}
#
####################################################################################################
####################################################################################################
####################################################################################################
#
public function start ($data)
{
	$requisites = array('origin');
	$requisites = coren::event($this->event_for_session_start_context, compact('requisites'));
	$origin = isset($requisites['origin']) ? $requisites['origin'] : null;

	$requisites = array('logname', 'password', 'remember');
	$requisites = coren::event($this->event_for_session_start_credentials, compact('requisites'));
	$logname  = isset($requisites['logname' ]) ? $requisites['logname' ] : null;
	$password = isset($requisites['password']) ? $requisites['password'] : null;
	$remember = isset($requisites['remember']) ? $requisites['remember'] : null;
	$credentials = compact('logname', 'password');

	if (!is_null($logname) && !is_null($password))
	{
		$acknowledge = coren::event($this->event_for_session_start_acknowledge, $credentials);
		$code =
			(is_null($acknowledge)    ? 'wrong'    :
			($acknowledge['disabled'] ? 'disabled' :
			(null)));
	} else
	{
		$acknowledge = null;
		$code = 'nothing';
	}

	if (is_null($code))
	{
		$trycount = max($this->trycount, 1);
		$account  = $acknowledge['account'];
		$remote = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
		$secure = 0;//!!!todo later: configurable default secure mode; selectable loginfo secure mode; account info with secure field.
		$period = $remember ? 0 : 60*60;//!!!todo later: configurable default period; from account settings; from credentials
		for ($i = 1; $i <= $trycount; $i++)
		{
			$identifier = coren::event($this->event_for_session_start_identifier, null);
			if (is_null($identifier)) throw new exception("Noone have generated identifier for new session.");
			try
			{
				coren::db('session_start', compact('identifier', 'account', 'remote', 'secure', 'period'));
				break;
			}
			catch (identify_session_start_0_exception_duplicate $exception)
			{
				if ($i >= $trycount) throw $exception;
			}
		}

		if (!$this->silent)
		{
			coren::event($this->event_for_session_start_successed, compact('origin', 'identifier', 'credentials', 'acknowledge', 'remote', 'secure', 'period'));
		}
		if (!$this->hidden)
		{
			if (!coren::depend('_dom_builder_0'))
				throw new exception("Tool '_dom_builder_0' missed.");

			$doc = coren::xml();
			$ele = _dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'session-start-success', null);
			$ele->appendChild(_dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'identifier' , $identifier ));
			$ele->appendChild(_dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'origin'     , $origin     ));
			$ele->appendChild(_dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'credentials', $credentials));
			$ele->appendChild(_dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'acknowledge', $acknowledge));
			$ele->appendChild(_dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'remote'     , $remote     ));
			$ele->appendChild(_dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'secure'     , $secure     ));
			$ele->appendChild(_dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'period'     , $period     ));
			coren::slot(coren::name($doc->documentElement->appendChild($ele),
				$this->name_for_session_start_successed),
				$this->slot_for_session_start_successed);
		}
	} else
	{
		if (!$this->silent)
		{
			coren::event($this->event_for_session_start_failed, compact('identifier', 'origin', 'credentials', 'acknowledge', 'code'));
		}
		if (!$this->hidden)
		{
			if (!coren::depend('_dom_builder_0'))
				throw new exception("Tool '_dom_builder_0' missed.");

			$doc = coren::xml();
			$ele = _dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'session-start-failure', null);
			$ele->appendChild(_dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'code'       , $code       ));
			$ele->appendChild(_dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'origin'     , $origin     ));
			$ele->appendChild(_dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'credentials', $credentials));
			$ele->appendChild(_dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'acknowledge', $acknowledge));
			coren::slot(coren::name($doc->documentElement->appendChild($ele),
				$this->name_for_session_start_failed),
				$this->slot_for_session_start_failed);
		}
	}
}
#
####################################################################################################
####################################################################################################
####################################################################################################
}
?>