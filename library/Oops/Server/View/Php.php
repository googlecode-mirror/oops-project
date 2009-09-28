<?php
/**
 * @package Oops
 * @subpackage Server
 */

require_once ("Oops/Server/View.php");

/**
 * Server HTML output View
 */
class Oops_Server_View_Php extends Oops_Server_View {

	function getContentType() {
		// @todo Consider moving this into Oops_Server
		$ret = 'text/html';
		$cfg = Oops_Server::getConfig();
		if(strlen($charset = $cfg->oops->charset)) $ret .= "; charset=$charset";
		return $ret;
	}

	/**
	 * HTML filtration. Expects Oops_Controller as an Input. and ext, action and uri_parts as params
	 *
	 * @return string HTML output
	 */
	function Out() {
		$template = $this->_getContentTemplate();
		$content = $template->Out($this->_in);
		
		$filter = $this->_getFilterTemplate();
		if($filter->isValid()) $content = $filter->Out($content);
		
		return $content;
	}

	/**
	 * Instantiates content template for current path, action, view and router controller 
	 * @return Oops_Template
	 */
	function _getContentTemplate() {
		$templateName = $this->_params['controller'] . '/' . $this->_params['ext'];
		
		if(sizeof($this->_params['uri_parts'])) $templateName .= '/' . join('/', $this->_params['uri_parts']);
		$templateName .= "/" . $this->_params['action'] . ".php";
		
		require_once ("Oops/Template.php");
		$template = Oops_Template::getInstance($templateName);
		return $template;
	}

	/**
	 * Instantiates content template for current path, action, view and router controller 
	 * @return Oops_Template
	 */
	function _getFilterTemplate() {
		$templateName = "_filter/" . $this->_params['ext'];
		
		if(sizeof($this->_params['uri_parts'])) $templateName .= '/' . join('/', $this->_params['uri_parts']);
		$templateName .= "/" . $this->_params['action'] . ".php";
		
		require_once ("Oops/Template.php");
		$template = Oops_Template::getInstance($templateName);
		return $template;
	}
}
