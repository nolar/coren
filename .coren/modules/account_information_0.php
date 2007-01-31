<?php defined('CORENINPAGE') or die('Hack!');
####################################################################################################
####################################################################################################
####################################################################################################
#
####################################################################################################
####################################################################################################
####################################################################################################
#
class account_information_0 extends coren_module
{
#
####################################################################################################
####################################################################################################
####################################################################################################
#
protected $event_for_cache_get = 'account.cache.get';
protected $event_for_cache_set = 'account.cache.set';
#
####################################################################################################
#
function __construct ($configs)
{
	parent::__construct($configs);

	if (isset($configs['event_for_cache_get'])) $this->event_for_cache_get = $configs['event_for_cache_get'];
	if (isset($configs['event_for_cache_set'])) $this->event_for_cache_set = $configs['event_for_cache_set'];
}
#
####################################################################################################
####################################################################################################
####################################################################################################
#
public function information ($data)
{
	$identifiers = isset($data['identifiers']) ? $data['identifiers'] : null;
	$identifier  = isset($data['identifier' ]) ? $data['identifier' ] : null;
	if ($singlemode = !is_null($identifier)) $identifiers = array(null => $identifier);
	if (!is_array($identifiers)) throw new exception("List of account identifiers must be an array.");
	$result = array();


	if (!is_null($this->event_for_cache_get))
	foreach ($identifiers as $index => $identifier)
	{
		if (!is_null($temp = coren::event($this->event_for_cache_get, compact('identifier'))))
		{
			$result[$index] = $temp;
			unset($identifiers[$index]);
		}
	}

	$information = empty($identifiers) ? array() : coren::db('select_information', compact('identifiers'));

	foreach ($identifiers as $index => $identifier)
	{
		if (array_key_exists($identifier, $information))
		{
			$result[$index] = $information[$identifier];
			if (!is_null($this->event_for_cache_set))
				coren::event($this->event_for_cache_set, array('identifier'=>$identifier, 'value'=>$information[$identifier]));
		} else
		{
			$result[$index] = null;
		}
	}

	return $singlemode ? $result[null] : $result;
}
#
####################################################################################################
####################################################################################################
####################################################################################################
}
?>