<?php defined('CORENINPAGE') or die('Hack!');
####################################################################################################
####################################################################################################
####################################################################################################
#
if (!core::depend('generator_random_0')) return;
#
####################################################################################################
#
class generator_random_md5_0 extends generator_random_0
{
#
####################################################################################################
####################################################################################################
####################################################################################################
#
public function make_string ($data)
{
	return md5(parent::make_string($data));
}
#
####################################################################################################
####################################################################################################
####################################################################################################
}
?>