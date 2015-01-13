<?php

class Oops_Cache_Factory {
	
	static public function getDriver($config) {
		$class = 'Oops_Cache_Driver_' . $config->class;
		return new $class($config);
	}
	
	static public function getMapstore($config) {
		$class = 'Oops_Cache_Mapstore_' . $config->class;
		return new $class($config);
	}
}