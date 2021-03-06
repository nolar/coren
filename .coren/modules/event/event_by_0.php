<?php defined('CORENINPAGE') or die('Hack!');
####################################################################################################
####################################################################################################
####################################################################################################
#
####################################################################################################
####################################################################################################
####################################################################################################
#
abstract class event_by_0 extends coren_module
{
#
####################################################################################################
####################################################################################################
####################################################################################################
#
protected $stop_name;
protected $stop_slot;
protected $event_mask;
#
####################################################################################################
#
public function __construct ($configs)
{
	parent::__construct($configs);

	if (isset($configs['stop_name' ])) $this->stop_name  = $configs['stop_name' ];
	if (isset($configs['stop_slot' ])) $this->stop_slot  = $configs['stop_slot' ];
	if (isset($configs['event_mask'])) $this->event_mask = $configs['event_mask'];
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
	$values = $this->evaluate($event_data);
	if (!is_null($values))//???
	return coren::event(vsprintf($this->event_mask, $values), $event_data);
}
#
####################################################################################################
####################################################################################################
####################################################################################################
}
?>