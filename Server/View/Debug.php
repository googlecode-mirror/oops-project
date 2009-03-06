<?
/**
* @package Oops
* @subpackage Server
*/

require_once("Oops/Server/View.php");
/**
* Server view HTML output
*/
class Oops_Server_View_Debug extends Oops_Server_View {
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