<?php

interface Oops_Cache_Manager_Interface {

	/**
	 *
	 * @param Oops_Config $config        	
	 */
	public function __construct($config = null);

	/**
	 * Get cached value
	 *
	 * @param string $key        	
	 * @return mixed false or FALSE on failure
	 */
	public function get($key);

	/**
	 * Store cached value
	 *
	 * @param string $key        	
	 * @param mixed $value        	
	 * @param int $ttl
	 *        	TTL in seconds
	 * @return mixed false or FALSE on failure
	 */
	public function set($key, $value, $ttl = null);

	/**
	 * Drop cached value
	 *
	 * @param string $key        	
	 */
	public function drop($key);

	/**
	 * Touch a cache key for cache map
	 *
	 * @param unknown_type $key        	
	 */
	public function touch($key);
	
	/**
	 * Ignore caching for all currently missing keys
	 */
	public function nocache();
}
