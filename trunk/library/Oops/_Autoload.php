<?
/**
* This file is being loaded if register_autoload option is set in config
*
* @package Oops
*/

require_once("Oops/Loader.php");

/**
* Autoloading function
*/
function __autoload($class) {
	if(Oops_Loader::find($class)) return $class;
	return false;
}