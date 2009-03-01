<?
/**
* @package Oops
*/

require_once("Oops/Application/Filter.php");
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