<?php

require_once("Oops/Object.php");

/**
* Store generated key strings for client and check if suggested value is a previously generated key string
*
* @todo Use factory template
*/
class Oops_Kcaptcha_Storage extends Oops_Object {

	/**
	* @param object Config object
	*/
	function __construct($config =  null) {
		if(!is_object($config)) {
			require_once("Oops/Kcaptcha/Config.php");
			$config = new Oops_Kcaptcha_Config();
		}
		$this->config = $config;
	}

	function Store($string) {
		$k = $this->config->storage_key;

		@session_start();

		if(!is_array($_SESSION[$k])) $_SESSION[$k] = array();
		if(!in_array($string, $_SESSION[$k])) $_SESSION[$k][]=$string;
	}

	function Check($passed, $keep = false) {
		@session_start();
		$k = $this->config->storage_key;

		if(is_array($_SESSION[$k]) && (($position = array_search($passed, $_SESSION[$k])) !== false)) {
			if(!$keep) {
				array_splice($_SESSION[$k], $position, 1);
			}
			return true;
		}
		return false;
	}
}