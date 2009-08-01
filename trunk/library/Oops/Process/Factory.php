<?php

class Oops_Process_Factory {
	static private $_storage;
	
	/**
	 * Instantiate new process of a given class. Throws exception if class is not valid
	 * 
	 * @param string $processClass
	 * @return Oops_Process
	 * @throws Oops_Process_Exception
	 */
	static public function &newProcess($processClass, array $inputValues) {
		
	}
	/**
	 * 
	 * @param string $pid
	 * @return Oops_Process
	 * @throws Oops_Process_Exception
	 */
	static public function &getProcess($pid) {
		
	}
	
	/**
	 * Instantiate process storage object, singleton pattern implemented.
	 * 
	 * @return Oops_Project_Storage
	 */
	static public function &getStorage() {
		if(!is_object(self::$_storage)) {
			/** @todo Use config */
			require_once("Oops/Process/Storage.php");
			self::$_storage =& new Oops_Process_Storage();
		}
		return self::$_storage;
	}
}
