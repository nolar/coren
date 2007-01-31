<?php defined('CORENINPAGE') or die('Hack!');
####################################################################################################
####################################################################################################
####################################################################################################
#
####################################################################################################
####################################################################################################
####################################################################################################
#
class identify_session_detect_0 extends coren_module
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
protected $event_for_session_detect_identifier	= 'identify.session.detect?identifier'	;
protected $event_for_session_detect_skipped	= 'identify.session.detect!skipped'	;
protected $event_for_session_detect_failed	= 'identify.session.detect!failed'	;
protected $event_for_session_detect_successed	= 'identify.session.detect!successed'	;
protected  $name_for_session_detect_skipped  ;
protected  $name_for_session_detect_failed   ;
protected  $name_for_session_detect_successed;
protected  $slot_for_session_detect_skipped  ;
protected  $slot_for_session_detect_failed   ;
protected  $slot_for_session_detect_successed;
#
protected $identifier ;
protected $information;
#
####################################################################################################
#
function __construct ($configs)
{
	parent::__construct($configs);

	if (isset($configs['silent'])) $this->silent = $configs['silent'];
	if (isset($configs['hidden'])) $this->hidden = $configs['hidden'];
	if (isset($configs['prefix'])) $this->prefix = $configs['prefix'];

	if (isset($configs['event_for_session_detect_identifier'])) $this->event_for_session_detect_identifier = $configs['event_for_session_detect_identifier'];
	if (isset($configs['event_for_session_detect_skipped'   ])) $this->event_for_session_detect_skipped    = $configs['event_for_session_detect_skipped'   ];
	if (isset($configs['event_for_session_detect_failed'    ])) $this->event_for_session_detect_failed     = $configs['event_for_session_detect_failed'    ];
	if (isset($configs['event_for_session_detect_successed' ])) $this->event_for_session_detect_successed  = $configs['event_for_session_detect_successed' ];
	if (isset($configs[ 'name_for_session_detect_skipped'   ])) $this-> name_for_session_detect_skipped    = $configs[ 'name_for_session_detect_skipped'   ];
	if (isset($configs[ 'name_for_session_detect_failed'    ])) $this-> name_for_session_detect_failed     = $configs[ 'name_for_session_detect_failed'    ];
	if (isset($configs[ 'name_for_session_detect_successed' ])) $this-> name_for_session_detect_successed  = $configs[ 'name_for_session_detect_successed' ];
	if (isset($configs[ 'slot_for_session_detect_skipped'   ])) $this-> slot_for_session_detect_skipped    = $configs[ 'slot_for_session_detect_skipped'   ];
	if (isset($configs[ 'slot_for_session_detect_failed'    ])) $this-> slot_for_session_detect_failed     = $configs[ 'slot_for_session_detect_failed'    ];
	if (isset($configs[ 'slot_for_session_detect_successed' ])) $this-> slot_for_session_detect_successed  = $configs[ 'slot_for_session_detect_successed' ];
}
#
####################################################################################################
####################################################################################################
####################################################################################################
#
public function detect ($data)
{
	$requisites = array('identifier');
	$requisites = coren::event($this->event_for_session_detect_identifier, compact('requisites'));
	$identifier = isset($requisites['identifier']) ? $requisites['identifier'] : null;

	if (is_null($identifier) || !is_scalar($identifier) || ($identifier == ''))
	{
		if (!$this->silent)
		{
			coren::event($this->event_for_session_detect_skipped);
		}
		if (!$this->hidden)
		{
			if (!coren::depend('_dom_builder_0'))
				throw new exception("Tool '_dom_builder_0' missed.");

			$doc = coren::xml();
			$ele = _dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'nothing', null);
			coren::slot(coren::name($doc->documentElement->appendChild($ele),
				$this->name_for_session_detect_skipped),
				$this->slot_for_session_detect_skipped);
		}
		return;
	}

	$information = coren::db('select_information', compact('identifier'));

	$code =
		(is_null($information)            ? 'absent'  :
		(is_null($information['account']) ? 'orphan'  :
		($information['status'] == 1      ? 'expired' :
		($information['status'] == 2      ? 'closed'  :
		($information['status'] == 3      ? 'halted'  :
		($information['status'] != 0      ? 'status'  :
		(null)))))));

	if (is_null($code))
	{
		$this->identifier  = $identifier ;
		$this->information = $information;

		if (!$this->silent)
		{
			coren::event($this->event_for_session_detect_successed, compact('identifier', 'information'));
		}
		if (!$this->hidden)
		{
			if (!coren::depend('_dom_builder_0'))
				throw new exception("Tool '_dom_builder_0' missed.");

			$doc = coren::xml();
			$ele = _dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'session-detect-success', null);
			$ele->appendChild(_dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'identifier' , $identifier ));
			$ele->appendChild(_dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'information', $information));
			coren::slot(coren::name($doc->documentElement->appendChild($ele),
				$this->name_for_session_detect_successed),
				$this->slot_for_session_detect_successed);
		}
		return $information['account'];//NB: this stops futher handling of current event.
	} else
	{
		if (!$this->silent)
		{
			coren::event($this->event_for_session_detect_failed, compact('identifier', 'information', 'code'));
		}
		if (!$this->hidden)
		{
			if (!coren::depend('_dom_builder_0'))
				throw new exception("Tool '_dom_builder_0' missed.");

			$doc = coren::xml();
			$ele = _dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'session-detect-failure', null);
			$ele->appendChild(_dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'code'       , $code       ));
			$ele->appendChild(_dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'identifier' , $identifier ));
			$ele->appendChild(_dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'information', $information));
			coren::slot(coren::name($doc->documentElement->appendChild($ele),
				$this->name_for_session_detect_failed),
				$this->slot_for_session_detect_failed);
		}
	}
}
#
####################################################################################################
####################################################################################################
####################################################################################################
#
public function get_identifier ($data)
{
	return $this->identifier;
}
#
####################################################################################################
#
public function get_information ($data)
{
	return $this->information;
}
#
####################################################################################################
####################################################################################################
####################################################################################################
}
?>