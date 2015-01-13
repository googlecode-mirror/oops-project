<?php

/**
 * 
 * Default cache config
 *
 */
class Oops_Cache_Defconf extends Oops_Config {

	public function __construct() {
		switch(true) {
			case function_exists('apc_get'):
				$driver = 'apc';
				break;
			case class_exists('Memcache'):
				$driver = 'memcache';
				break;
			default:
				throw new Oops_Exception("No cache drivers available");
		}
		
		switch(true) {
			case extension_loaded('mongo'):
				$mapstore = 'mongodb';
				break;
			case function_exists('mysql_connect'):
				$mapstore = 'mysql';
				break;
			default:
				throw new Oops_Exception("No db drivers available");
		}
		
		$d = array(
			'manager' => 'cascade', 
			'driver' => array('class' => $driver), 
			'mapstore' => array('class' => $mapstore));
		
		parent::__construct($d);
	}
}