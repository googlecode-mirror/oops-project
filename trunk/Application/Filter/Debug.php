<?
/**
* @package Oops
* @subpackage Application
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
		Oops_Debug::Dump($data);
		return ob_get_clean();
	}
}
?>