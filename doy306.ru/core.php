<?php 
define('COREN_CONFIGURATION_FILE', $_SERVER['DOCUMENT_ROOT'] . '/core.xml');
define('COREN_FATAL_ACTION', 'verbose');
require(dirname(__FILE__) . "/../.coren/coren.php");
#require(dirname(__FILE__) . "/../.coren.trunk-/coren.php");
//!!! ������� �������, ����� ������ ���� ���-���� �������������. ����� include_path?
//!!! � ������������� ���� ���� � core_alias.php (��� �� ����?)
?>