<?php defined('CORENINPAGE') or die('Hack!');
####################################################################################################
####################################################################################################
####################################################################################################
#
# NB: Use of this module is highly unrecommended under multithreaded environment, because
# setlocale() is per-process function, not per-thread. So when you set locale for this script,
# it also sets locales for all scripts on the whole server. The only possible use of this module
# is resetting locale for categjry LC_ALL to value "C". But this do not guarant that your script
# will run completely wuth this locale - any other script can set its own local and break your "C".
# This do not relate to CGI, of course. There locales are safe enough.
# See PHP manual for details (http://php.net/manual/en/function.setlocale.php).
#
####################################################################################################
####################################################################################################
####################################################################################################
#
class php_locale_0 extends coren_module
{
#
####################################################################################################
####################################################################################################
####################################################################################################
#
protected $required;
protected $category;
protected $locales;
#
####################################################################################################
#
function __construct ($configs)
{
	parent::__construct($configs);

	if (isset($configs['required'])) $this->required = $configs['required'];

	if (isset($configs['category'])) $this->category = $configs['category'];
	$this->category = "LC_" . strtoupper($this->category);
	$this->category = defined($this->category) ? constant($this->category) : null;

	$locale  = isset($configs['locale' ]) ? $configs['locale' ] : null;
	$locales = isset($configs['locales']) ? $configs['locales'] : null;
	$locales = array_filter(split("[[:space:]]+", $locales), "strlen");
	if (!is_null($locale)) array_unshift($locales, $locale);
	$this->locales = $locales;
}
#
####################################################################################################
####################################################################################################
####################################################################################################
#
public function set ($data)
{
	if (!is_null($this->category) && !empty($this->locales))
	{
		if (function_exists('setlocale'))
		{
			$result = @setlocale($this->category, $this->locales);
			if ($this->required && ($result === false))
				throw new exception("Can not set locale when it is required.");
		} else
		{
			if ($this->required)
				throw new exception("Can not set locale when it is required (function 'setlocale' do not exists).");
		}
	}
}
#
####################################################################################################
####################################################################################################
####################################################################################################
}
?>