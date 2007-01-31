<?php defined('CORENINPAGE') or die('Hack!');
#
# This is a default script for unhandled exceptions.
# It can be used as a sample and copied anywhere else.
# You probably want to redefine this exception handler
# on a per-site or per-directory basis. Just make there
# a file 'fatal' or '.fatal' with appropriate extension
# (which one is used on your site: php, phps or other).
#
header("HTTP/1.0 503 Unhandled exception");
printf("Unhandled exception of class '%s' at line %d of file '%s':\n%s\n",
	get_class($exception),
	$exception->getLine(),
	$exception->getFile(),
	$exception->getMessage());
#
?>