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
	var $_data = array();

	function __construct($data) {
		if(!is_array($data)) {
			require_once("Oops/Error.php");
			Oops_Error::Raise("Error/Config/InvalidConfigData",$data);
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
			require_once("Oops/Error.php");
			Oops_Error::Raise("Error/Config/InvalidConfigKey",$key);
			return null;
		}
		$key = strtolower($key);
		if(!isset($this->_data[$key])) {
			require_once("Oops/Error.php");
			Oops_Error::Raise("Notice/Config/ConfigKeyNotFound",$key);
			return null;
		}
		return $this->_data[$key];
	}
}