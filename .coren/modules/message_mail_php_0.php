<?php defined('CORENINPAGE') or die('Hack!');
####################################################################################################
####################################################################################################
####################################################################################################
#
####################################################################################################
####################################################################################################
####################################################################################################
#
class message_mail_php_0 extends coren_module
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
protected $name;
protected $slot;
#
protected $privilege_for_send;
#
protected $event_for_not_authorized = 'message.mail:not.authorized';
protected $event_for_send_success   = 'message.mail:successed';
#
protected $headers;
#
####################################################################################################
#
function __construct ($configs)
{
	parent::__construct($configs);

	if (isset($configs['silent'])) $this->silent = $configs['silent'];
	if (isset($configs['hidden'])) $this->hidden = $configs['hidden'];
	if (isset($configs['prefix'])) $this->prefix = $configs['prefix'];
	if (isset($configs['name'  ])) $this->name   = $configs['name'  ];
	if (isset($configs['slot'  ])) $this->slot   = $configs['slot'  ];

	if (isset($configs['privilege_for_send'])) $this->privilege_for_send = $configs['privilege_for_send'];

	if (isset($configs['event_for_send_success'])) $this->event_for_send_success = $configs['event_for_send_success'];

	if (isset($configs['headers'])) $this->headers = $configs['headers'];
}
#
####################################################################################################
####################################################################################################
####################################################################################################
#
public function send ($data)
{
	if (!coren::have($this->privilege_for_send))
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
			$ele = _dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'send', null);
			$ele->appendChild(_dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'not-authorized', null));
		}
//???		do nto throw here. see file ...inject... for reasoning.
//???		throw new exception("Not authorized to flush queue of messages.");
		return;
	}

	$recipient = isset($data['recipient']) ? $data['recipient'] : ''; if (is_scalar($recipient)) $recipient = (string ) $recipient; else throw new exception("Bad recipient for message mail (must be string).");
	$subject   = isset($data['subject'  ]) ? $data['subject'  ] : ''; if (is_scalar($subject  )) $subject   = (string ) $subject  ; else throw new exception("Bad subject for message mail (must be string).");
	$message   = isset($data['message'  ]) ? $data['message'  ] : ''; if (is_scalar($message  )) $message   = (string ) $message  ; else throw new exception("Bad message for message mail (must be string).");
	$headers   = (string) $this->headers;

	$recipient = trim(preg_replace("/(\\r|\\n)+/"   , " "      , $recipient));//NB: strip all newlines at all.
	$subject   = trim(preg_replace("/(\\r|\\n)+/"   , " "      , $subject  ));//NB: strip all newlines at all.
	$message   = trim(preg_replace("/(\\r(\\n?))/"  , "\n"     , $message  ));//NB: force   LF (  \n).
	$headers   = trim(preg_replace("/([^\\r]|^)\\n/", "\\1\r\n", $headers  ));//NB: force CRLF (\r\n).

	$result = @mail($recipient, $subject, $message, $headers);
	$errormsg = isset($php_errormsg) ? $php_errormsg : "Error in mail(); \$php_errormsg isn't set.";
	if (!$result) throw new exception($errormsg);

	if (!$this->silent)
	{
		coren::event($this->event_for_send_success);
	}
	if (!$this->hidden)
	{
		if (!coren::depend('_dom_builder_0'))
			throw new exception("Tool '_dom_builder_0' missed.");

		$doc = coren::xml();
		$ele = _dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'send', null);
		$ele->appendChild(_dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'recipient', $recipient));
		$ele->appendChild(_dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'subject'  , $subject  ));
		$ele->appendChild(_dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'message'  , $message  ));
		coren::slot(coren::name($doc->documentElement->appendChild($ele), $this->name), $this->slot);
	}
}
#
####################################################################################################
####################################################################################################
####################################################################################################
}
?>