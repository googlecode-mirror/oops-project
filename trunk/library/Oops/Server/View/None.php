<?php
/**
* @package Oops
* @subpackage Server
*/

require_once 'Oops/Server/View.php';
/**
* Server HTML output View
*/
class Oops_Server_View_None extends Oops_Server_View {
	function getContentType() {
		$ret = 'text/plain';
		$cfg = Oops_Server::getConfig();
		if(strlen($charset = $cfg->oops->charset)) $ret .= "; charset=$charset";
		return $ret;
	}
}
