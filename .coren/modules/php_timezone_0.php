<?php defined('CORENINPAGE') or die('Hack!');
####################################################################################################
####################################################################################################
####################################################################################################
#
class php_timezone_0 extends coren_module
{
#
####################################################################################################
####################################################################################################
####################################################################################################
#
protected $required;
protected $timezone;
#
####################################################################################################
#
function __construct ($configs)
{
	parent::__construct($configs);

	if (isset($configs['required'])) $this->required = $configs['required'];
	if (isset($configs['timezone'])) $this->timezone = $configs['timezone'];
}
#
####################################################################################################
####################################################################################################
####################################################################################################
#
public function set ($data)
{
	if (!is_null($this->timezone))
	{
		if (function_exists('date_default_timezone_set'))
		{
			$result = @date_default_timezone_set($this->timezone);
			if ($this->required && ($result === false))
				throw new exception("Can not set timezone when it is required (timezone '{$this->timezone}' is not valid).");
		} else
		{
			if ($this->required)
				throw new exception("Can not set timezone when it is required (function 'date_default_timezone_set' do not exists; probably because PHP < 5.1.0RC1).");
		}
	}
}
#
####################################################################################################
####################################################################################################
####################################################################################################
}
?>