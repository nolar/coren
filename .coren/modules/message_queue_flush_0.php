<?php defined('CORENINPAGE') or die('Hack!');
####################################################################################################
####################################################################################################
####################################################################################################
#
####################################################################################################
####################################################################################################
####################################################################################################
#
class message_queue_flush_0 extends coren_module
{
#
####################################################################################################
####################################################################################################
####################################################################################################
#
const namespace = 'http://coren.numeri.net/namespaces/message/';
protected $silent;
protected $hidden;
protected $prefix;
#
protected $privilege_for_flush;
#
protected $event_for_send_message   = 'message.queue.flush!send';
protected $event_for_not_authorized = 'message.queue.flush:not.authorized';
protected $event_for_flush_success  = 'message.queue.flush:successed';
protected  $name_for_not_authorized;
protected  $name_for_flush_success ;
protected  $slot_for_not_authorized;
protected  $slot_for_flush_success ;
#
protected $one_by_one;
protected $max_age;
protected $max_try;
protected $limit_per_call;
//todo: limit_per_day
//todo: limit_per_hour
//todo: limit_per_minute
#
####################################################################################################
#
function __construct ($configs)
{
	parent::__construct($configs);

	if (isset($configs['silent'])) $this->silent = $configs['silent'];
	if (isset($configs['hidden'])) $this->hidden = $configs['hidden'];
	if (isset($configs['prefix'])) $this->prefix = $configs['prefix'];

	if (isset($configs['privilege_for_flush'])) $this->privilege_for_flush = $configs['privilege_for_flush'];

	if (isset($configs['event_for_send_message'  ])) $this->event_for_send_message   = $configs['event_for_send_message'  ];
	if (isset($configs['event_for_not_authorized'])) $this->event_for_not_authorized = $configs['event_for_not_authorized'];
	if (isset($configs['event_for_flush_success' ])) $this->event_for_flush_success  = $configs['event_for_flush_success' ];
	if (isset($configs[ 'name_for_not_authorized'])) $this-> name_for_not_authorized = $configs[ 'name_for_not_authorized'];
	if (isset($configs[ 'name_for_flush_success' ])) $this-> name_for_flush_success  = $configs[ 'name_for_flush_success' ];
	if (isset($configs[ 'slot_for_not_authorized'])) $this-> slot_for_not_authorized = $configs[ 'slot_for_not_authorized'];
	if (isset($configs[ 'slot_for_flush_success' ])) $this-> slot_for_flush_success  = $configs[ 'slot_for_flush_success' ];

	if (isset($configs['one_by_one'    ])) $this->one_by_one     = $configs['one_by_one'    ];
	if (isset($configs['max_age'       ])) $this->max_age        = $configs['max_age'       ];
	if (isset($configs['max_try'       ])) $this->max_try        = $configs['max_try'       ];
	if (isset($configs['limit_per_call'])) $this->limit_per_call = $configs['limit_per_call'];
}
#
####################################################################################################
####################################################################################################
####################################################################################################
#
public function flush ($data)
{
	if (!coren::have($this->privilege_for_flush))
	{
		if (!$this->silent)
		{
			coren::event($this->event_for_not_authorized);
		}
		if (!$this->hidden)
		{
			if (!coren::depend('_dom_builder_0'))
				throw new exception("Tool '_dom_builder_0' missed.");

			$doc = coren::xml();
			$ele = _dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'flush', null);
			$ele->appendChild(_dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'not-authorized', null));
			coren::slot(coren::name($doc->documentElement->appendChild($ele), $this->name_for_not_authorized), $this->slot_for_not_authorized);
		}
//???		do nto throw here. see file ...inject... for reasoning.
//???		throw new exception("Not authorized to flush queue of messages.");
		return;
	}

	$limit = max(1, $this->limit_per_call);//min(max(1, $this->limit_per_call)/*, max(1, $this->limit_per_*), ...*/);
	$envelopes = coren::db('select_envelopes', compact('limit'));

	$reports = array();
	if (is_array($envelopes) && !empty($envelopes))
	{
		$identifiers = array();
		foreach ($envelopes as $envelope) $identifiers[] = $envelope['template'];
		$identifiers = array_unique($identifiers);
		$templates = empty($identifiers) ? array() : coren::db('select_templates', compact('identifiers'));

		foreach ($envelopes as $index => $item)
		{
			$envelope    = $item['envelope'   ];
			$template    = $item['template'   ];
			$recipient   = $item['recipient'  ];
			$current_try = $item['current_try'];
			$current_age = $item['current_age'];
			$subject = isset($templates[$template]['subject']) ? $templates[$template]['subject'] : null;
			$message = isset($templates[$template]['message']) ? $templates[$template]['message'] : null;

			try
			{
				$status = +1;
				$error = $errno = null;
				coren::event($this->event_for_send_message, compact('recipient', 'subject', 'message', 'envelope', 'template'));
			}
			catch (exception $exception)
			{
				$status = (isset($this->max_age) && ($current_age >= $this->max_age))
					||(isset($this->max_try) && ($current_try >= $this->max_try))
					? -1 : 0;
				$error  = $exception->getMessage();
				$errno  = $exception->getCode();
			}
			$reports[] = compact('envelope', 'template', 'recipient', 'error', 'errno', 'status');

			if ($this->one_by_one)
			if ($status <= 0)
			{
				coren::db('mark_envelope_failure', compact('envelope', 'error', 'errno', 'status'));
			} else
			{
				coren::db('mark_envelope_success', compact('envelope', 'status'));
			}
		}
	}

	if (!$this->one_by_one)
	{
		$envelopes = array();//NB: this is a list of identifiers of envelopes, that were successfuly sent.
		foreach ($reports as $report)
		{
			$envelope = $report['envelope'];
			$status   = $report['status'  ];
			if ($status <= 0)
			{
				$error = $report['error'];
				$errno = $report['errno'];
				coren::db('mark_envelope_failure', compact('envelope', 'error', 'errno', 'status'));
			} else
			{
				$envelopes[] = $envelope;
			}
		}
		if (!empty($envelopes))
		{
			$status = +1;
			coren::db('mark_envelope_success', compact('envelopes', 'status'));
		}
	}

	if (!$this->silent)
	{
		//!!!??? trigger event about successful or failed send! or maybe it must be dependent
		//!!!??? on one_by_one: event one-by-one, or all at once?
		//coren::event($this->event_for_flush_success, compact(???!!!));
		coren::event($this->event_for_flush_success);
	}
	if (!$this->hidden)
	{
		if (!coren::depend('_dom_builder_0'))
			throw new exception("Tool '_dom_builder_0' missed.");

		$doc = coren::xml();
		$ele = _dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'flush', null);
		$ele->appendChild(_dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'limit'  , $limit));
		$ele->appendChild(_dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'max-age', $this->max_age));
		$ele->appendChild(_dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'max-try', $this->max_try));

		$eler = $ele->appendChild(_dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'reports', null));
		foreach ($reports as $report)
		$eler->appendChild(_dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'report', $report));

		$elet = $ele->appendChild(_dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'templates', null));
		foreach ($templates as $identifier => $template)
		$elet->appendChild(_dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'template', $template))
		   ->setAttributeNodeNS(_dom_builder_0::build_attribute($doc, self::namespace, $this->prefix, 'id', $identifier));

		coren::slot(coren::name($doc->documentElement->appendChild($ele), $this->name_for_flush_success), $this->slot_for_flush_success);
	}
}
#
####################################################################################################
####################################################################################################
####################################################################################################
}
?>