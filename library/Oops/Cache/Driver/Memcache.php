<?php

class Oops_Cache_Driver_Memcache extends Oops_Cache_Driver_Abstract {
	
	/**
	 * 
	 * @var Memcache
	 */
	private $_m;
	
	private $_compress = 0;

	/**
	 *
	 * @param Oops_Config $config
	 */
	public function __construct($config) {
		$config = new Oops_Config(array('host' => 'localhost', 'port' => 11211, 'timeout' => 1));
		$this->_m = new Memcache();
		$this->_m->connect($config->host, $config->port, $config->timeout);
		if($config->compress) $this->_compress = MEMCACHE_COMPRESSED;
		
	}

	public function get($key) {
		return $this->_m->get($key);
	}

	public function set($key, $value, $ttl = null) {
		return $this->_m->set($key, $value, $this->_compress ,$ttl);
	}

	public function drop($key) {
		return $this->_m->delete($key);
	}
}
