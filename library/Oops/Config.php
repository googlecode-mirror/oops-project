<?php
/**
* @package Oops
* @subpackage Config
* @author Dmitry Ivanov rockmagic@yandex.ru
* @license GNUv3
*/

/**
* Config class
*
*/
class Oops_Config {
	protected $_data = array();
	public $used = false;
	protected $_cache = array();
	
	private static $instances = array();
	
	function __construct($data = array(), $keyDelimiter = '.') {
		if(!is_array($data)) {
			trigger_error("Config/InvalidConfigData", E_USER_WARNING);
			return;
		}
		foreach($data as $key => $value) {
			$key = strtolower($key);
			if(strpos($key,$keyDelimiter) !== false) {
				list($key,$subKey) = explode($keyDelimiter, $key, 2);
				$value = array($subKey => $value);
			}
			if(is_array($value)) {
				if(isset($this->_data[$key])) {
					if(is_object($this->_data[$key])) {
						$this->_data[$key]->mergeConfig(new Oops_Config($value));
					} else {
						trigger_error("Config/ConflictingInitValues/$key", E_USER_WARNING);
					}
				} else {
					$this->_data[$key] = new Oops_Config($value);
				}
			}
			elseif(!isset($this->_data[$key])) $this->_data[$key] = $value;
			else trigger_error("Config/ConflictingInitValues/$key", E_USER_WARNING);
		}
	}

	function getKeys() {
		return array_keys($this->_data);
	}

	function get($key) {
		if(!strlen((string)$key)) {
			trigger_error("Config/InvalidConfigKey/$key", E_USER_WARNING);
			return null;
		}
		$key = strtolower($key);
		if(!isset($this->_data[$key])) {
//			trigger_error("Config/ConfigKeyNotFound/$key", E_USER_NOTICE);
			// @todo return new Oops_Config, make __toString function returning empty string
			$this->_data[$key] = new Oops_Config_Fake();
		}
		return $this->_data[$key];
	}

	function __get($var) {
		return $this->get($var);
	}

	function __set($var, $value) {
		return false;
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
	
	public function isValidConfig() {
		return true;
	}
}