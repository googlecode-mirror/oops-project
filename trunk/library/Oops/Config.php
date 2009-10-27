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
class Oops_Config implements Countable, Iterator {
	protected $_data = array();
	public $used = false;
	protected $_cache = array();
	protected $_arrayPosition = 0;
	protected $_keyDelimiter;
	
	/**
	 * Whether in-memory modifications to configuration data are allowed
	 * 
	 * @var boolean 
	 */
	protected $_allowModifications;
	
	private static $instances = array();

	function __construct($data = array(), $keyDelimiter = '.', $allowModifications = false) {
		if(!is_array($data)) {
			trigger_error("Config/InvalidConfigData", E_USER_WARNING);
			return;
		}
		
		$this->_keyDelimiter = $keyDelimiter;
		$this->_allowModifications = $allowModifications;
		
		foreach($data as $key => $value) {
			$key = strtolower($key);
			if(strpos($key, $keyDelimiter) !== false) {
				list($key, $subKey) = explode($keyDelimiter, $key, 2);
				$value = array($subKey => $value);
			}
			if(is_array($value)) {
				if(isset($this->_data[$key])) {
					if(is_object($this->_data[$key])) {
						$this->_data[$key]->mergeConfig(new Oops_Config($value, $this->_keyDelimiter, $this->_allowModifications));
					} else {
						trigger_error("Config/ConflictingInitValues/$key", E_USER_WARNING);
					}
				} else {
					$this->_data[$key] = new Oops_Config($value, $this->_keyDelimiter, $this->_allowModifications);
				}
			} elseif(!isset($this->_data[$key]))
				$this->_data[$key] = $value;
			else
				trigger_error("Config/ConflictingInitValues/$key", E_USER_WARNING);
		}
	}

	function getKeys() {
		return array_keys($this->_data);
	}

	function get($key) {
		if(!strlen((string) $key)) {
			trigger_error("Config/InvalidConfigKey/$key", E_USER_WARNING);
			return null;
		}
		$key = strtolower($key);
		if(!isset($this->_data[$key])) {
			require_once 'Oops/Config/Fake.php';
			$this->_data[$key] = new Oops_Config_Fake();
		}
		return $this->_data[$key];
	}

	/**
	 * Getter
	 * @ignore
	 */
	function __get($var) {
		return $this->get($var);
	}

	/**
	 * Setter
	 * @ignore
	 */
	function __set($var, $value) {
		if(!$this->_allowModifications) return false;
		if(is_array($value)) {
			$value = new Oops_Config($value, $this->_keyDelimiter, $this->_allowModifications);
		}
		
		if(isset($this->_data[$var]) && $this->_data[$var] instanceof Oops_Config_Fake) {
			$fakeData = $this->_data[$var]->getData();
			if(count($fakeData)) {
				$this->_data[$var] = new Oops_Config($fakeData, $this->_keyDelimiter, true);
			} else {
				unset($this->_data[$var]);
			}
		}
		
		if(isset($this->_data[$var]) && $this->_data[$var] instanceof Oops_Config && $value instanceof Oops_Config) {
			$this->_data[$var]->mergeConfig($value);
		} else {
			$this->_data[$var] = $value;
		}
		return $value;
	}

	/**
	 * Merges another config values to this one, replacing existing keys with new values
	 * 
	 * @param Oops_Config $config
	 */
	public function mergeConfig($config) {
		if(!is_object($config)) return;
		foreach($config->_data as $key => $value) {
			if(!isset($this->_data[$key])) {
				$this->_data[$key] = $config->_data[$key];
			} elseif(is_object($this->_data[$key]) && is_object($value)) {
				$this->_data[$key]->mergeConfig($config->_data[$key]);
			} else {
				$this->_data[$key] = $config->_data[$key];
			}
		}
	}

	public function isValidConfig() {
		return true;
	}

	public function count() {
		return count($this->_data);
	}

	/**
	 * Iterator interface method
	 */
	public function current() {
		return current($this->_data);
	}

	public function key() {
		return key($this->_data);
	}

	public function next() {
		++$this->_arrayPosition;
		next($this->_data);
	}

	public function rewind() {
		$this->_arrayPosition = 0;
		reset($this->_data);
	}

	public function valid() {
		if($this->_arrayPosition < count($this->_data) && $this->_arrayPosition >= 0) return true;
		return false;
	}

	public function __toArray() {
		$array = $this->_data;
		foreach($array as $k => $v) {
			if($v instanceof Oops_Config) $array[$k] = $v->__toArray();
		}
		return $array;
	}
}