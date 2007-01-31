<?php defined('CORENINPAGE') or die('Hack!');
####################################################################################################
####################################################################################################
####################################################################################################
#
####################################################################################################
####################################################################################################
####################################################################################################
#
class event_unconditional_0 extends coren_module
{
#
####################################################################################################
####################################################################################################
####################################################################################################
#
protected $stop_name;
protected $stop_slot;
protected $event;
#
####################################################################################################
#
public function __construct ($configs)
{
	parent::__construct($configs);

	if (isset($configs['stop_name'])) $this->stop_name = $configs['stop_name'];
	if (isset($configs['stop_slot'])) $this->stop_slot = $configs['stop_slot'];
	if (isset($configs['event'    ])) $this->event     = $configs['event'    ];
}
#
####################################################################################################
####################################################################################################
####################################################################################################
#
public function check ($data)
{
	if (!is_null($this->stop_slot) && coren::slot_used($this->stop_slot)) return;
	if (!is_null($this->stop_name) && coren::name_used($this->stop_name)) return;
	$data = null;//??? make it configurable?
	return coren::event($this->event, $data);
}
#
####################################################################################################
####################################################################################################
####################################################################################################
}
?>