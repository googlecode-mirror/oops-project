<?
/**
* @package Oops
* @subpackage Server
*/

if(!defined("OOPS_Loaded")) die("OOPS not loaded");

require_once("Oops/Server.php");

/**
* Application servers stack
*/
class Oops_Server_Stack {
	/**
	* Servers stack array
	*
	* @access private
	*/
	var $_stack = array();

	/**
	* Get current Server object
	*
	* @access public
	* @uses Oops_Server_Stack::getInstance()
	* @static
	* @return Oops_Server Current server instance
	*/
	function &last() {
		$stack =& Oops_Server_Stack::getInstance();
		return end($stack->_stack);
	}

	/**
	* Pushes new server into stack, new server object become current
	*
	* @access public
	* @uses Oops_Server_Stack::getInstance()
	* @static
	* @param Oops_Server Current server instance
	*/
	function push(&$server) {
		$stack =& Oops_Server_Stack::getInstance();
		return array_push($stack->_stack,$server);
	}

	/**
	* Pops a server object from stack
	*
	* @access public
	* @uses Oops_Server_Stack::getInstance()
	* @static
	* @return Oops_Server Current server instance
	*/
	function &pop() {
		$stack =& Oops_Server_Stack::getInstance();
		return array_pop($stack->_stack);
	}

	/**
	* Get stack size
	*
	* @return int Number of servers in stack
	*/
	function size() {
		$stack =& Oops_Server_Stack::getInstance();
		return count($stack->_stack);
	}

	/**
	* Singleton pattern implementation
	* Should be deprecated when migrating to PHP5. Class constant should be used instead.
	*
	* @access private
	*/
	function &getInstance() {
		static $instance;
		if(!isset($instance)) $instance = new Oops_Server_Stack();
		return $instance;
	}
}