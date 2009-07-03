<?php
abstract class Oops_Session_Abstract {

	public function __construct($config) {
		if(!is_object($config)) $config = new Oops_Config();

		if(@$config->name) session_name($config->name);

		if(@$config->cache_limiter) {
			session_cache_limiter($config->cache_limiter);
		} else {
			session_cache_limiter('private_no_expire');
		}
	}

	protected function setHandler() {
		session_set_save_handler(
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