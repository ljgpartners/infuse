<?php
define('MUNEE_CACHE', $_SERVER['DOCUMENT_ROOT']."/packages/bpez/infuse/munee_cache");
define('WEBROOT', $_SERVER['DOCUMENT_ROOT']);

// Include the composer autoload file
require '../../../../vendor/autoload.php';
// Echo out the response
echo \Munee\Dispatcher::run(new \Munee\Request());