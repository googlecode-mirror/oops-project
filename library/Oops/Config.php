<?
/**
* @package Oops
* @subpackage Config
* @author Dmitry Ivanov rockmagic@yandex.ru
* @license GNUv3
*/

if(!defined('OOPS_Loaded')) die("OOPS not loaded");

require_once("Oops/Object.php");

/**
* Config class
*
*/
class Oops_Config extends Oops_Object {
	protected $_data = array();
	public $used = false;

	function __construct($data = array()) {
		if(!is_array($data)) {
			trigger_error("Config/InvalidConfigData", E_USER_WARNING);
			return;
		}
		foreach($data as $key=>$value) {
			$key = strtolower($key);
			if(is_array($value)) $this->_data[$key] = new Oops_Config($value);
			else $this->_data[$key] = $value;
		}
	}

	function get($key) {
		if(!strlen((string)$key)) {
debugPrint($key,"invalid",true);
			trigger_error("Config/InvalidConfigKey/$key", E_USER_WARNING);
			return null;
		}
		$key = strtolower($key);
		if(!isset($this->_data[$key])) {
			trigger_error("Config/ConfigKeyNotFound/$key", E_USER_NOTICE);
			return null;
		}
		return $this->_data[$key];
	}

	function __get($var) {
		return $this->get($key);
	}

	function mergeConfig(&$config) {
		if(!is_object($config)) return;
		foreach($config->_data as $key=>$value) {
			if(!isset($this->_data[$key])) {
				$this->_data[$key] = $config->_data[$key];
			}
			elseif(is_object($this->_data[$key]) && is_object($value)) {
				$this->_data[$key]->mergeConfig($config->_data[$key]);
			}
			else {
				$this->_data[$key] = $config->_data[$key];
			}
		}
	}
}