<?php

/**
 * 
 * Default cache config
 *
 */
class Oops_Cache_Defconf extends Oops_Config {

	public function __construct() {
		$d = array('manager' => 'cascade', 'driver' => 'apc', 'mapstore' => 'mongodb');
		parent::__construct($d);
	}
}