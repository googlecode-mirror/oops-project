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
	private $_stack = array();

	/**
	* Get current Server object
	*
	* @access public
	* @uses Oops_Server_Stack::getInstance()
	* @static
	* @return Oops_Server Current server instance
	*/
	public static function &last() {
		$stack =& Oops_Server_Stack::getInstance();
		return $stack->_stack[count($stack->_stack)-1];
	}

	/**
	* Pushes new server into stack, new server object become current
	*
	* @access public
	* @uses Oops_Server_Stack::getInstance()
	* @static
	* @param Oops_Server Current server instance
	*/
	public static function push(&$server) {
		$stack =& Oops_Server_Stack::getInstance();
		$stack->_stack[] =& $server;
		return count($stack->_stack);
	}

	/**
	* Pops a server object from stack
	*
	* @access public
	* @uses Oops_Server_Stack::getInstance()
	* @static
	* @return Oops_Server Current server instance
	*/
	public static function &pop() {
		$stack =& Oops_Server_Stack::getInstance();
		$last =& $stack->_stack[count($stack->_stack)-1];
		$last = null;
		array_pop($stack->_stack);
		return $stack->_stack[count($stack->_stack)-1];
	}

	/**
	* Get stack size
	*
	* @return int Number of servers in stack
	*/
	public static function size() {
		$stack =& Oops_Server_Stack::getInstance();
		return count($stack->_stack);
	}

	/**
	* Singleton pattern implementation
	* Should be deprecated when migrating to PHP5. Class constant should be used instead.
	*
	* @access private
	*/
	public static function &getInstance() {
		static $instance;
		if(!isset($instance)) $instance = new Oops_Server_Stack();
		return $instance;
	}

	private function __construct() {}
}