<?php defined('CORENINPAGE') or die('Hack!');
####################################################################################################
####################################################################################################
####################################################################################################
#
####################################################################################################
####################################################################################################
####################################################################################################
#
abstract class event_if_0 extends coren_module
{
#
####################################################################################################
####################################################################################################
####################################################################################################
#
protected $stop_name;
protected $stop_slot;
protected $event_true;
protected $event_false;
#
####################################################################################################
#
public function __construct ($configs)
{
	parent::__construct($configs);

	if (isset($configs['stop_name'  ])) $this->stop_name   = $configs['stop_name'  ];
	if (isset($configs['stop_slot'  ])) $this->stop_slot   = $configs['stop_slot'  ];
	if (isset($configs['event_true' ])) $this->event_true  = $configs['event_true' ];
	if (isset($configs['event_false'])) $this->event_false = $configs['event_false'];
}
#
####################################################################################################
####################################################################################################
####################################################################################################
#
abstract protected function evaluate (&$event_data);
#
####################################################################################################
#
public function check ($data)
{
	if (!is_null($this->stop_slot) && coren::slot_used($this->stop_slot)) return;
	if (!is_null($this->stop_name) && coren::name_used($this->stop_name)) return;
	$event_data = null;
	return coren::event($this->evaluate($data) ? $this->event_true : $this->event_false, $event_data);
}
#
####################################################################################################
####################################################################################################
####################################################################################################
}
?>