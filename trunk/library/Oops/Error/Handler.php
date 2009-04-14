<?
/**
* @package Oops
* @subpackage Error
*/

if(!defined('OOPS_Loaded')) die("OOPS not loaded");

require_once("Oops/Object.php");

/**
* Class for handling user errors
*
* Usage:
* <code><?
*   require_once("Oops/Error/Handler");
*   $eh = new Oops_Error_Hander();
*
*   // ... your code ...
*
*   trigger_error("Some error occured", E_USER_ERROR);
*   trigger_error("Warning here", E_USER_WARNING);
*   trigger_error("Sending a notice", E_USER_NOTICE);
*
*   //... your code ...
*
*   if($eh->isClear()) {
*      //there were no errors/warnings/notices in your code
*   }
*
*   if($eh->isError()) {
*      //there were user errors
*      Oops_Debug::Dump($eh->getErrors(),"Errors occured"));
*   }
*
*   if($eg->isWarning()) {
*      //there were warnings
*      Oops_Debug::Dump($eh->getWarnings(),"Warnings occured"));
*   }
*
*   if($eg->isNotice()) {
*      //there were notices
*      Oops_Debug::Dump($eh->getNotices(),"Notices sent"));
*   }
*
*   //when using PHP4 call destructor manually
*   $eh->destruct();
*   unset($eh);
* ?></code>
*/
class Oops_Error_Handler extends Oops_Object {

	/**
	* Catched errors stack
	*/
	var $_errors = array();

	/**
	* Catched warnings stack
	*/
	var $_warnings = array();

	/**
	* Catched notices stack
	*/
	var $_notices = array();

	/**
	* Total state, TRUE if no errors were handled
	*/
	var $_clear = true;

	/**
	* Constructor, sets the constructed object as error handler
	*/
	function __construct() {
		set_error_handler(array($this,'handle'));
	}

	/**
	* Destructor for PHP4 (call it manually)
	*/
	function destruct() {
		restore_error_handler();
	}

	function __destruct() {
		$this->destruct();
	}

	/**
	* Error handling function
	*/
	function handle($errno, $errstr) {
		switch($errno) {

			case E_USER_ERROR:
				$this->_errors[] = $errstr;
				break;

			case E_USER_WARNING:
				$this->_warnings[] = $errstr;
				break;

			case E_USER_NOTICE:
				$this->_notices[] = $errstr;
				break;

			default:
				return false;
		}
		$this->_clear = false;
		return true;
	}

	function isError() {
		return count($this->_errors) ? true : false;
	}

	function isWarning() {
		return count($this->_warnings) ? true : false;
	}

	function isNotice() {
		return count($this->_notices) ? true : false;
	}

	function getErrors() {
		return $this->_errors;
	}

	function getWarnings() {
		return $this->_warnings;
	}

	function getNotices() {
		return $this->_notices;
	}

	function isClear() {
		return $this->_clear;
	}

}