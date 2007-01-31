<?php defined('CORENINPAGE') or die('Hack!');
####################################################################################################
####################################################################################################
####################################################################################################
#
####################################################################################################
####################################################################################################
####################################################################################################
#
class identify_session_close_0 extends coren_module
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
protected $event_for_session_close_context	= 'identify.session.close?context'	;
protected $event_for_session_close_identifier	= 'identify.session.close?identifier'	;
protected $event_for_session_close_failed	= 'identify.session.close!failed'	;
protected $event_for_session_close_successed	= 'identify.session.close!successed'	;
protected  $name_for_session_close_failed   ;
protected  $name_for_session_close_successed;
protected  $slot_for_session_close_failed   ;
protected  $slot_for_session_close_successed;
#
####################################################################################################
#
function __construct ($configs)
{
	parent::__construct($configs);

	if (isset($configs['silent'])) $this->silent = $configs['silent'];
	if (isset($configs['hidden'])) $this->hidden = $configs['hidden'];
	if (isset($configs['prefix'])) $this->prefix = $configs['prefix'];

	if (isset($configs['event_for_session_close_context'    ])) $this->event_for_session_close_context     = $configs['event_for_session_close_context'    ];
	if (isset($configs['event_for_session_close_identifier' ])) $this->event_for_session_close_identifier  = $configs['event_for_session_close_identifier' ];
	if (isset($configs['event_for_session_close_failed'     ])) $this->event_for_session_close_failed      = $configs['event_for_session_close_failed'     ];
	if (isset($configs['event_for_session_close_successed'  ])) $this->event_for_session_close_successed   = $configs['event_for_session_close_successed'  ];
	if (isset($configs[ 'name_for_session_close_failed'     ])) $this-> name_for_session_close_failed      = $configs[ 'name_for_session_close_failed'     ];
	if (isset($configs[ 'name_for_session_close_successed'  ])) $this-> name_for_session_close_successed   = $configs[ 'name_for_session_close_successed'  ];
	if (isset($configs[ 'slot_for_session_close_failed'     ])) $this-> slot_for_session_close_failed      = $configs[ 'slot_for_session_close_failed'     ];
	if (isset($configs[ 'slot_for_session_close_successed'  ])) $this-> slot_for_session_close_successed   = $configs[ 'slot_for_session_close_successed'  ];
}
#
####################################################################################################
####################################################################################################
####################################################################################################
#
public function close ($data)
{
	$requisites = array('origin');
	$requisites = coren::event($this->event_for_session_close_context, compact('requisites'));
	$origin = isset($requisites['origin']) ? $requisites['origin'] : null;

	$identifier = coren::event($this->event_for_session_close_identifier, null);

	$code =
		(is_null($identifier) ? 'already' :
		(null));

	if (is_null($code))
	{
		coren::db('close_session', compact('identifier'));

		if (!$this->silent)
		{
			coren::event($this->event_for_session_close_successed, compact('origin', 'identifier'));
		}
		if (!$this->hidden)
		{
			if (!coren::depend('_dom_builder_0'))
				throw new exception("Tool '_dom_builder_0' missed.");

			$doc = coren::xml();
			$ele = _dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'session-close-success', null);
			$ele->appendChild(_dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'identifier' , $identifier ));
			$ele->appendChild(_dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'origin'     , $origin     ));
			coren::slot(coren::name($doc->documentElement->appendChild($ele),
				$this->name_for_session_close_successed),
				$this->slot_for_session_close_successed);
		}
	} else
	{
		if (!$this->silent)
		{
			coren::event($this->event_for_session_close_failed, compact('identifier', 'origin', 'code'));
		}
		if (!$this->hidden)
		{
			if (!coren::depend('_dom_builder_0'))
				throw new exception("Tool '_dom_builder_0' missed.");

			$doc = coren::xml();
			$ele = _dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'session-close-failure', null);
			$ele->appendChild(_dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'code'       , $code       ));
			$ele->appendChild(_dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'identifier' , $identifier ));
			$ele->appendChild(_dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'origin'     , $origin     ));
			coren::slot(coren::name($doc->documentElement->appendChild($ele),
				$this->name_for_session_close_failed),
				$this->slot_for_session_close_failed);
		}
	}
}
#
####################################################################################################
####################################################################################################
####################################################################################################
}
?>