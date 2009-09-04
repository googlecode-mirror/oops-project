<?php

/**
 * @package Oops
 * @subpackage Template
 * @license GPLv3
 * @author Dmitry Ivanov rockmagic@yandex.ru
 */

/**
 * Class for evaluating templates
 */
class Oops_Template {
	/**
	 * State of the template
	 *
	 * @access private
	 * @var bool
	 */
	private $_valid = true;
	
	private $_tplFile;
	private $_tplName;

	/**
	 * Tells whenever object is a valid template (template file exists)
	 *
	 * @return bool TRUE if valid
	 */
	public function isValid() {
		return $this->_valid;
	}

	/**
	 * @param string template name
	 * @access private
	 */
	private function __construct($tplName) {
		/**
		 * Numbering templates
		 */
		static $num = 0;
		$this->_num = ++$num;
		
		$this->_tplName = $tplName;

		// @todo move helper initialization to static method
		
		require_once 'Oops/Template/Helper.php';
		try {
			/**
			 * Obtain template file name 
			 */
			$this->_tplFile = Oops_Template_Helper::getTemplateFilename($tplName);
		} catch(Exception $e) {
			trigger_error($e->getMessage(), E_USER_ERROR);
		 	$this->_valid = false;
		}
		$this->_request = Oops_Server::getRequest();
		$this->_response = Oops_Server::getResponse();
	}

	/**
	 * @param mixed Template data, will be accessable as $this->Data
	 */
	public function out(&$var) {
		// @todo Eliminate notice when scalar value is passed (can't by passed by reference)
		if(!$this->_valid) return;
		$this->Data = & $var;
		ob_start();
		include ($this->_tplFile);
		return ob_get_clean();
	
	}

	/**
	 * Singleton pattern implementaion
	 *
	 * @static
	 * @param string template name
	 * @return Oops_Template
	 */
	public static function getInstance($tplname) {
		$tplname = strtolower($tplname);
		static $a = array();
		if(!isset($a[$tplname])) $a[$tplname] = new Oops_Template($tplname);
		return $a[$tplname];
	}

	/**
	 * Call another template
	 */
	protected function call($tplname, $data = null) {
		$template = Oops_Template::getInstance($tplname);
		if($template->isValid()) {
			if(is_null($data)) $data = & $this->Data;
			return $template->out($data);
		}
	}

	function store($key, $value = null) {
		static $store = array();
		if(is_null($value))
			return isset($store[$key]) ? $store[$key] : null;
		else
			$store[$key] = $value;
	}

	protected function _setHeader($key, $value) {
		return $this->_response->setHeader($key, $value);
	}

	protected function _pushHeader($key, $value) {
		return $this->_response->pushHeader($key, $value);
	}

	protected function _getHeader($key) {
		return $this->_response->getHeader($key);
	}
}
