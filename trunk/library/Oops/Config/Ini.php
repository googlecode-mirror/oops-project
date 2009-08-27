<?php
/**
* @package Oops
* @subpackage Config
* @author Dmitry Ivanov rockmagic@yandex.ru
* @license GNUv3
*/

require_once("Oops/Config.php");
/**
* Configuration based on .ini files
*
*/
class Oops_Config_Ini extends Oops_Config {
	protected $_parseError = false;

	/**
	* @param string Ini file name
	*/
	function __construct($filename) {
		set_error_handler(array($this,"_parseIniErrorHandler"));
		$data = parse_ini_file($filename,true);
		restore_error_handler();
		if($this->_parseError) {
			trigger_error("Config/InvalidIniFile/$filename", E_USER_WARNING);
			return;
		}
		parent::__construct($data);
	}

	function _parseIniErrorHandler($errno,$errstr) {
		$this->_parseError = true;
		return true;
	}
}
