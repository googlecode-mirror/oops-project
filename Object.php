<?
/**
* @package Oops
*/

if(!defined('OOPS_Loaded')) die("OOPS not found");

/**
*
* Class for PHP 4/5 constructors compability
*   Any class should inherit Oops_Object and can't contain function named as class. 
*
*   __constructor method is used instead of common PHP4 constructor.
*
*   In order to pass variables by reference to the constructor, define PHP4-style constructor like the following:
*   <code><?
*     class Oops_SomeClass extends Oops_Object {
*        function Oops_SomeClass (&$var) {
*           $this->__construct($var);
*        }
*        function __construct(&$var) {
*           //constructor code
*        }
*     }
*   ?></code>
*
* @author Dmitry Ivanov
* @package Oops
*
*/
class Oops_Object {
	/**
	* @access protected
	*/
	var $_gotErrors=false;


	function Oops_Object() {
		$args = func_get_args();
		call_user_func_array(array(&$this, '__construct'), $args);
	}
	function __construct() {}
}
?>