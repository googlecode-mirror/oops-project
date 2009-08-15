<?php

require_once 'Oops/Pattern/Identifiable/Singleton/Interface.php';

class Oops_Process_Helper implements Oops_Pattern_Identifiable_Singleton_Interface {

	public function getId() {
		return $this->_processClass;
	}

	public static function getInstance($class) {
		static $_instances = array();
		if(!isset($_instances[$class]) || !is_object($_instances[$class])) {
			$_instances[$class] = new self($class);
		}
		return $_instances[$class];
	}

	private function __construct($class) {
		require_once 'Oops/Loader.php';
		if(Oops_Loader::find($class)) {
			$reflectionClass = new ReflectionClass($class);
			if(!$reflectionClass->isSubclassOf('Oops_Process_Abstract')) {
				require_once 'Oops/Process/Exception.php';
				throw new Oops_Process_Exception("Requested class not found", OOPS_PROCESS_EXCEPTION_INVALID_CLASS);
			}
			$this->_class = $class;
			$this->_reflection = $reflectionClass;
			$classVars = $reflectionClass->getDefaultProperties();
			$this->_info = array(
				'states' => $classVars['_states'], 
				'variables' => $classVars['_variables'], 
				'transitions' => $classVars['_transition'] );
		} else {
			require_once 'Oops/Process/Exception.php';
			throw new Oops_Process_Exception("Requested class $class not found", OOPS_PROCESS_EXCEPTION_INVALID_CLASS);
		}
	}

	public function __get($name) {
		switch($name) {
			case 'class':
				return $this->_class;
			default:
				if(isset($this->_info[$name])) return $this->_info[$name];
		}
		return null;
	}
}