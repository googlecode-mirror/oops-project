<?php
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
		ob_start();
		Oops_Debug::Dump($this->_in);
		return ob_get_clean();
	}
}
