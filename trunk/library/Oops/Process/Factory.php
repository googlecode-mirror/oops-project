<?php

class Oops_Process_Factory {
	private static $_storage;
	
	private static $_processes = array();

	/**
	 * Instantiate new process of a given class. Throws exception if class is not valid
	 * 
	 * @param string $processClass
	 * @return Oops_Process
	 * @throws Oops_Process_Exception
	 */
	static public function newProcess($processClass, $inputValues) {
		Oops_Loader::load($processClass);
		$reflectionClass = new ReflectionClass($processClass);
		if(!($reflectionClass->isSubclassOf('Oops_Process_Abstract'))) {
			require_once ("Oops/Process/Exception.php");
			throw new Oops_Process_Exception("Invalid process class $processClass");
		}
		
		/**
		 * 
		 * @var Oops_Process_Abstract $process
		 */
		$process = $reflectionClass->newInstance();
		$process->init($inputValues);
		if(!$process->pid) {
			require_once ("Oops/Process/Exception.php");
			throw new Oops_Process_Exception("Process init error, no pid for instance of $processClass", OOPS_PROCESS_EXCEPTION_NO_PID);
		}
		self::$_processes[$process->pid] = $process;
		return $process;
	}

	/**
	 * 
	 * @param string $pid
	 * @return Oops_Process_Abstract
	 * @throws Oops_Process_Exception
	 */
	static public function getProcess($pid) {
		if(!is_object(self::$_processes[$pid])) {
			$storage = self::getStorage();
			$processClass = $storage->getClass($pid);
			$reflectionClass = new ReflectionClass($processClass);
			self::$_processes[$pid] = $reflectionClass->newInstance($pid);
		}
		return self::$_processes[$pid];
	}

	/**
	 * Instantiate process storage object, singleton pattern implemented.
	 * 
	 * @return Oops_Process_Storage
	 */
	static public function getStorage() {
		if(!is_object(self::$_storage)) {
			// @todo Use config
			require_once ("Oops/Process/Storage.php");
			self::$_storage = new Oops_Process_Storage();
		}
		return self::$_storage;
	}
	
	/**
	 * Generates new process id
	 * 
	 * @return string
	 */
	static public function generatePid() {
		// @todo implement this function
		trigger_error("Function is not implemented yet");
		return md5(mt_rand(0,100000) . mt_rand(0,100000));
	}
}
