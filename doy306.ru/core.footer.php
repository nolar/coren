<?php
//!!! ������� �������, ����� ������ ���� ���-���� �������������. ����� include_path?
//!!! � ������������� ���� ���� � core_alias.php (��� �� ����?)
define('COREMANUAL', true);
require(dirname(__FILE__) . "/../.core.0.0.0/core.php");
core::work('stop_modules', null);
?>