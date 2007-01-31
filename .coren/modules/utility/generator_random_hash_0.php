<?php defined('CORENINPAGE') or die('Hack!');
####################################################################################################
####################################################################################################
####################################################################################################
#
if (!core::depend('generator_random_0')) return;
#
####################################################################################################
#
class generator_random_hash_0 extends generator_random_0
{
#
####################################################################################################
####################################################################################################
####################################################################################################
#
protected $algorithm;
#
####################################################################################################
#
function __construct ($configs)
{
	parent::__construct($configs);
	if (isset($configs['algorithm'])) $this->algorithm = $configs['algorithm'];
}
#
####################################################################################################
####################################################################################################
####################################################################################################
#
public function make_string ($data)
{
	return hash($this->algo, parent::make_string($data));
}
#
####################################################################################################
####################################################################################################
####################################################################################################
}
?>