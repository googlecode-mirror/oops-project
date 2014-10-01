<?php

abstract class Oops_Cache_Driver_Abstract {

	/**
	 *
	 * @param Oops_Config $config        	
	 */
	abstract public function __construct($config);

	/**
	 *
	 * @param string $key        	
	 * @param int $ttl        	
	 */
	abstract public function get($key);

	/**
	 *
	 * @param string $key        	
	 * @param mixed $value        	
	 * @param int $ttl        	
	 */
	abstract public function set($key, $value, $ttl = null);

	/**
	 *
	 * @param string $key        	
	 */
	abstract public function drop($key);
} 