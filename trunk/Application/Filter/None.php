<?
/**
* @package Oops
* @subpackage Application
*/

require_once("Oops/Application/Filter.php");
/**
* Application HTML output filter
*/
class Oops_Application_Filter_None extends Oops_Application_Filter {
	function getContentType() {
		return "text/plain";
	}
}
?>