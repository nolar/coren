<?php defined('CORENINPAGE') or die('Hack!');
####################################################################################################
####################################################################################################
####################################################################################################
#
####################################################################################################
####################################################################################################
####################################################################################################
#
class message_queue_inject_0 extends coren_module
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
protected $privilege_for_inject;
#
protected $event_for_not_authorized = 'message.queue.inject:not.authorized';
protected $event_for_inject_success = 'message.queue.inject:successed';
protected  $name_for_not_authorized;
protected  $name_for_inject_success;
protected  $slot_for_not_authorized;
protected  $slot_for_inject_success;
#
####################################################################################################
#
function __construct ($configs)
{
	parent::__construct($configs);

	if (isset($configs['silent'])) $this->silent = $configs['silent'];
	if (isset($configs['hidden'])) $this->hidden = $configs['hidden'];
	if (isset($configs['prefix'])) $this->prefix = $configs['prefix'];

	if (isset($configs['privilege_for_inject'])) $this->privilege_for_inject = $configs['privilege_for_inject'];

	if (isset($configs['event_for_not_authorized'])) $this->event_for_not_authorized = $configs['event_for_not_authorized'];
	if (isset($configs['event_for_inject_success'])) $this->event_for_inject_success = $configs['event_for_inject_success'];
	if (isset($configs[ 'name_for_not_authorized'])) $this-> name_for_not_authorized = $configs[ 'name_for_not_authorized'];
	if (isset($configs[ 'name_for_inject_success'])) $this-> name_for_inject_success = $configs[ 'name_for_inject_success'];
	if (isset($configs[ 'slot_for_not_authorized'])) $this-> slot_for_not_authorized = $configs[ 'slot_for_not_authorized'];
	if (isset($configs[ 'slot_for_inject_success'])) $this-> slot_for_inject_success = $configs[ 'slot_for_inject_success'];
}
#
####################################################################################################
####################################################################################################
####################################################################################################
#
public function inject ($data)
{
	if (!coren::have($this->privilege_for_inject))
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
			$ele = _dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'inject', null);
			$ele->appendChild(_dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'not-authorized', null));
			coren::slot(coren::name($doc->documentElement->appendChild($ele), $this->name_for_not_authorized), $this->slot_for_not_authorized);
		}
//???		absence of authrization for some action is an error, but not an exception.
//???		exceptions are for unpredicted situations, and access violation is predicted.
//???		So this is an error, it has an event & data about error, and do nothing more,
//???		but it do not throw an exception, which breaks all workflow of the script
//???		and causes rollbacks in all databases.
//???		throw new exception("Not authorized to inject message into queue of messages.");
		return;
	}

	$priority = isset($data['priority']) ? $data['priority'] :  0; if (is_scalar($priority)) $priority = (integer) $priority; else throw new exception("Bad priority for message queue (must be integer).");
	$subject  = isset($data['subject' ]) ? $data['subject' ] : ''; if (is_scalar($subject )) $subject  = (string ) $subject ; else throw new exception("Bad subject for message queue (must be string).");
	$message  = isset($data['message' ]) ? $data['message' ] : ''; if (is_scalar($message )) $message  = (string ) $message ; else throw new exception("Bad message for message queue (must be string).");

	//??? if both [recipient] and [recipients] are set, should be send to both of them, or to [recipients] only?
	//??? now we use the second way: if [recipients] is set, then [recipient] is ignored.
	$recipient  = isset($data['recipient' ]) ? $data['recipient' ] : null;
	$recipients = isset($data['recipients']) ? $data['recipients'] : array($recipient);
	if (!is_array($recipients)) $recipients = array($recipients);//NB: $recipients will never be null here, so we don't recheck.
	$temp = array();
	foreach ($recipients as $recipient)
		if (is_null  ($recipient)) { continue; } else
		if (is_scalar($recipient)) { $temp[] = (string) $recipient; } else //NB: use recipient even if it is an empty string.
		throw new exception("Bad type of recipient in message queue (must be either null, or string).");
	$recipients = array_unique($temp);

	$template = coren::db('insert_template', compact('subject', 'message'));
	if (!empty($recipients)) coren::db('insert_envelopes', compact('template', 'priority', 'recipients'));

	if (!$this->silent)
	{
		coren::event($this->event_for_inject_success, compact('priority', 'subject', 'message', 'recipients'));
	}
	if (!$this->hidden)
	{
		if (!coren::depend('_dom_builder_0'))
			throw new exception("Tool '_dom_builder_0' missed.");

		$doc = coren::xml();
		$ele = _dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'inject', compact('priority', 'subject', 'message'));
		foreach ($recipients as $recipient)
		$ele->appendChild(_dom_builder_0::build_nodetree($doc, self::namespace, $this->prefix, 'recipient', $recipient));
		coren::slot(coren::name($doc->documentElement->appendChild($ele), $this->name_for_inject_success), $this->slot_for_inject_success);
	}
}
#
####################################################################################################
####################################################################################################
####################################################################################################
}
?>