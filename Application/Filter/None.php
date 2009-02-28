<?
/**
* @package Oops
*/

__autoload("Oops_Application_Filter");
/**
* Application HTML output filter
*/
class Oops_Application_Filter_None extends Oops_Application_Filter {
	function getContentType() {
		return "text/plain";
	}
}
?>