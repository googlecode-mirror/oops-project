<?php
abstract class Oops_Session_Abstract {

	public function __construct($config) {
		if(!is_object($config)) {
			require_once("Oops/Config.php");
			$config = new Oops_Config();
		}

		if(@$config->name) session_name($config->name);

		if(@$config->cache_limiter) {
			session_cache_limiter($config->cache_limiter);
		} else {
			session_cache_limiter('nocache');
		}

	}

	/**
	 * Sets current class as session save handler
	 * 
	 * @return bool True on success
	 */
	protected function setHandler() {
		//First let's test if this class implements Oops_Session_Interface
		$reflection = new ReflectionClass(get_class($this));
		if(!$reflection->implementsInterface('Oops_Session_Interface')) {
			/** @todo throw exception or error here */
			return false;
		}
		
		return session_set_save_handler(
			array(&$this, '_open'),
			array(&$this, '_close'),
			array(&$this, '_read'),
			array(&$this, '_write'),
			array(&$this, '_destroy'),
			array(&$this, '_gc')
		);
	}


	public function _open($path, $name) {
		return TRUE;
	}
	public function _close() {
		return TRUE;
	}

}