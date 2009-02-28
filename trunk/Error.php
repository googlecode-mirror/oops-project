<?
/**
* @package Oops
*/

if(!defined('OOPS_Loaded')) die("OOPS not found");

/**
* Error handling class
*/
class Oops_Error {

	/**
	* @ignore
	*/
	var $_errors=array();

	/**
	* Use Oops_Error::Raise('Error/Description/Or/ID') to log error.
	*
	* @static
	* @param string Error string, f.e. 'Error/SomeClassOrPackage/WhatWasWrong' or 'Warning/Module/WhatsUp'
	* @return boolean False on successful error logging, and true if no error or warningstring was passed
	*/
	function Raise($str,$data=null) {
		if(!strlen($str)) return true;
		$err =& Oops_Error::getInstance();
		$err->_errors[]=array("message"=>$str,"data"=>$data/*,"trace"=>debug_backtrace()*/);
		if(isset($this)) $this->_gotErrors=true;
		return false;
	}

	/**
	* Use Oops_Error::Get() to get errors log as array
	*
	* @static
	* @return array Logged errors as array of strings
	*/
	function Get() {
		$err =& Oops_Error::getInstance();
		return $err->_errors;
	}

	/**
	* Use Oops_Error::Clear() to flush the errors log
	*
	* @static
	*/
	function Clear() {
		$err =& Oops_Error::getInstance();
		$err->_errors=array();
	}

	function Debug() {
		$err =& Oops_Error::getInstance();
		debugPrint($err->_errors);
	}

	/**
	* @ignore
	* @access private
	*/
	function &getInstance() {
		static $o;
		if(!isset($o)) {
			$o = new Oops_Error();
			if(isDebug()) register_shutdown_function(array("Oops_Error","Debug"));
		}
		return $o;
	}
	
}
?>