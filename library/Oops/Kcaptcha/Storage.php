<?php

/**
* Store generated key strings for client and check if suggested value is a previously generated key string
*
*/
class Oops_Kcaptcha_Storage {
	
	/**
	 * 
	 * @var Oops_Kcaptcha_Config
	 */
	public $config;

	/**
	* @param Oops_Kcaptcha_Config Config object
	*/
	public function __construct($config =  null) {
		// @todo use Oops_Config
		if(!is_object($config)) {
			$config = new Oops_Kcaptcha_Config();
		}
		$this->config = $config;
	}

	public function Store($string) {
		$k = $this->config->storage_key;
		$maxKeys = property_exists($this->config, 'storage_maxkeys') ? intval($this->config->storage_maxkeys) : 0;
		
		Oops_Session::init();

		if(!isset($_SESSION[$k]) || !is_array($_SESSION[$k])) $_SESSION[$k] = array();
		$_SESSION[$k][]=$string;

		if($maxKeys > 0) $_SESSION[$k] = array_slice($_SESSION[$k], -1 * $maxKeys);
	}

	public function Check($passed, $keep = false) {
		Oops_Session::init();
		$k = $this->config->storage_key;
		
		if(empty($_SESSION[$k])) return false;

		if(($position = array_search($passed, $_SESSION[$k])) !== false) {
			if(!$keep) {
				array_splice($_SESSION[$k], $position, 1);
			}
			return true;
		}
		return false;
	}
}