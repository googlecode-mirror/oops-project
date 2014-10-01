<?php

class Oops_Cache_Driver_Apc {

	/**
	 *
	 * @param Oops_Config $config        	
	 */
	public function __construct($config = NULL) {
	}

	public function get($key) {
		return apc_fetch($key);
	}

	public function set($key, $value, $ttl = null) {
		return apc_store($key, $value, $ttl);
	}

	public function drop($key) {
		return apc_delete($key);
	}
}
