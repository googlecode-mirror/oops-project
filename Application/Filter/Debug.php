<?
/**
* @package Oops
*/

__autoload("Oops_Application_Filter");
/**
* Application HTML output filter
*/
class Oops_Application_Filter_Debug extends Oops_Application_Filter {
	function getContentType() {
		return "text/html";
	}

	function Out() {
		$data = $this->_in->getData();
		ob_start();
		debugPrint($data);
		return ob_get_clean();
	}
}
?>