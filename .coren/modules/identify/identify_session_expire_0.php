<?php defined('CORENINPAGE') or die('Hack!');
####################################################################################################
####################################################################################################
####################################################################################################
#
####################################################################################################
####################################################################################################
####################################################################################################
#
class identify_session_expire_0 extends coren_module
{
#
####################################################################################################
####################################################################################################
####################################################################################################
#
function __construct ($configs)
{
	parent::__construct($configs);
}
#
####################################################################################################
####################################################################################################
####################################################################################################
#
public function expire ($data)
{
	coren::db('expire_sessions', null);
}
#
####################################################################################################
####################################################################################################
####################################################################################################
}
?>