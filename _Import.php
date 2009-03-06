<?
/**
* Library import
*
* @package Oops
* @tutorial Oops/Oops.pkg#import
* 
*/
/**
* This defines the library path, don't change this definition
*/
define("OOPS_PATH",dirname(__FILE__));
define("OOPS_Loaded",true);

$pathParts = explode(DIRECTORY_SEPARATOR,OOPS_PATH);
array_pop($pathParts);
set_include_path(get_include_path().PATH_SEPARATOR.join('/',$pathParts));

require_once("Oops/Server/Stream/Wrapper.php");
stream_wrapper_register("oops","Oops_Server_Stream_Wrapper");


/**
* Funciton for loading OOPS classes. FALSE is returned if error occurs.
*
* @param string class name
* @deprecated Use Oops_Loader::find($class) to check if class could be loaded or require_once for regular loading
*
* <code><?php
*   __autoload("Oops_Some_Class.php"); // OOPS_PATH/Some/Class.php will be loaded.
*   $obj = new Oops_Some_Class(); //Now class is available
* ?></code>
* /
function __autoload($class) {
	return Oops_Loader::find($class);
}

/**
* Just like mysql_query, but connects to mysql server on demand and dies on mysql error
*
* @deprecated Use Oops_Sql::Query instead
*/
function db_query($query,$skiperrors=false) {
	require_once('Oops/Sql.php');
	return Oops_Sql::Query($query,$skiperrors);
}

/**
* Debugging function, a user-friendly print_r
*
* @deprecated Use Oops_Debug::Dump instead
*
* Usage:
* <code><?php
* debugPrint($var); //will output complete backtrace
* debugPrint($thatvar,"That var"); //will output complete backtrace
* debugPrint($var,"Some label",__CLASS__,__FUNCTION__,__FILE__,__LINE__);
* ?></code>
*
*
*
* @param mixed Any variable to output
* @param string Label
* @param boolean show full backtrace or not
* @return void
*/
function debugPrint($value, $name=null, $fullTrace=false) {
	require_once("Oops/Debug.php");
	Oops_Debug::Dump($value, $name, $fullTrace);
}
?>