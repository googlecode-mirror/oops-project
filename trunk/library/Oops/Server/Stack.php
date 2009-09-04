<?php
/**
* @package Oops
* @subpackage Server
*/

require_once("Oops/Server.php");

/**
* Application servers stack
*/
class Oops_Server_Stack {
	/**
	 * Server stack instance for Singleton pattern
	 * @var Oops_Server_Stack
	 */
	static private $instance;
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
	public static function last() {
		$stack = Oops_Server_Stack::getInstance();
		return count($stack->_stack) ? $stack->_stack[count($stack->_stack)-1] : null;
	}

	/**
	* Pushes new server into stack, new server object become current
	*
	* @access public
	* @uses Oops_Server_Stack::getInstance()
	* @static
	* @param Oops_Server Current server instance
	*/
	public static function push($server) {
		$stack = Oops_Server_Stack::getInstance();
		$stack->_stack[] = $server;
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
	public static function pop() {
		$stack = Oops_Server_Stack::getInstance();
		$last = $stack->_stack[count($stack->_stack)-1];
		$last = null;
		array_pop($stack->_stack);
		return count($stack->_stack) ? $stack->_stack[count($stack->_stack)-1] : null;
	}

	/**
	* Get stack size
	*
	* @return int Number of servers in stack
	*/
	public static function size() {
		$stack = Oops_Server_Stack::getInstance();
		return count($stack->_stack);
	}

	/**
	* Singleton pattern implementation
	* Should be deprecated when migrating to PHP5. Class constant should be used instead.
	*
	* @access private
	*/
	public static function getInstance() {
		if(!is_object(self::$instance)) self::$instance = new self();
		return self::$instance;
	}

	private function __construct() {}
}