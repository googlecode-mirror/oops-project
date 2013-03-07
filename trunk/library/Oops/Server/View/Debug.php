<?php
/**
* @package Oops
* @subpackage Server
*/

require_once 'Oops/Server/View.php';
/**
* Server view HTML output
*/
class Oops_Server_View_Debug extends Oops_Server_View {
	function getContentType() {
		// @todo Consider moving this into Oops_Server
		$ret = 'text/html';
		$cfg = Oops_Server::getConfig();
		if(strlen($charset = $cfg->oops->charset)) $ret .= "; charset=$charset";
		return $ret;
	}

	function Out() {
		ob_start();
		Oops_Debug::Dump($this->_in);
		return ob_get_clean();
	}
}
