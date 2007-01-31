<?php
//!!! сделать поумнее, чтобы ссылка была все-таки относительной. через include_path?
//!!! и переименовать этот файл в core_alias.php (или не надо?)
define('COREMANUAL', true);
require(dirname(__FILE__) . "/../.core.0.0.0/core.php");
core::work('stop_modules', null);
?>