<?php defined('CORENINPAGE') or die('Hack!');
####################################################################################################
####################################################################################################
####################################################################################################
#
if (!coren::depend('requisites_request_0')) return;
#
class requisites_request_direct_0 extends requisites_request_0
{
#
####################################################################################################
####################################################################################################
####################################################################################################
#
public function __construct ($configs)
{
	parent::__construct($configs);
}
#
####################################################################################################
####################################################################################################
####################################################################################################
#
protected function field_of_requisite ($requisite)
{
	return $requisite;
}
#
####################################################################################################
####################################################################################################
####################################################################################################
}
?>