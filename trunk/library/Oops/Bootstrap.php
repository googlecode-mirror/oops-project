<?php

if(!defined("DS")) define("DS", DIRECTORY_SEPARATOR);

$libPath = dirname(dirname(__FILE__));
if(!in_array($libPath, explode(PATH_SEPARATOR, get_include_path()))) {
	set_include_path($libPath . PATH_SEPARATOR . get_include_path());
}

/**
 * Some system checks
 */
@set_magic_quotes_runtime(0);
@ini_set('zend.ze1_compatibility_mode', '0');

$tz = defined('TIMEZONE') ? constant('TIMEZONE') : 'Europe/Moscow';
date_default_timezone_set($tz);

require_once 'Oops/Loader.php';
spl_autoload_register(array('Oops_Loader', 'load'));

//require_once 'Oops/Server/Stream/Wrapper.php';
stream_wrapper_register('oops', 'Oops_Server_Stream_Wrapper');


