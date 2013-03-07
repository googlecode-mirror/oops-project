<?php

class Oops_Process_Controller extends Oops_Controller {
	
	private $_processClass;
	private $_processClassHelper;
	private $_processId;
	private $_processObject;
	
	public function __construct() {
		parent::__construct();
		$this->init();
	}
	
	function init() {
		try {
		foreach($this->_server->controller_params as $i => $value) {
			switch ($i) {
				case 0:
					$this->_processClass = $value;
					require_once 'Oops/Process/Helper.php';
					$this->_processClassHelper = Oops_Process_Helper::getInstance($this->_processClass);
					break;
				case 1:
					$this->_processId = $value;
					require_once 'Oops/Process/Factory.php';
					$this->_processObject = Oops_Process_Factory::getProcess($this->_processId);
					if(strtolower(get_class($this->_processObject)) != $this->_processClass) {
						require_once 'Oops/Process/Exception.php';
						throw new Oops_Process_Exception("Process with id {$this->_processId} can't be invoked under {$this->_processClass} controller", OOPS_PROCESS_EXCEPTION_INVALID_CLASS);
					}
					break;
				default:
					/**
					 * More than 2 params, do something special?
					 */
			}
		}
		} catch(Oops_Process_Exception $e) {
			/**
			 * Init error - invalid class, invalid process id, invalid process data
			 * TODO workout th exception
			 */
		}
	}
	
	public function Run() {
		return call_user_func(array($this, $this->_server->action . 'Action'));
	}
	
	public function __call($methodName) {
		/**
		 * Since we should define every 'working' method, let's just report an error  
		 */
		trigger_error("Process/Controller/UndefinedAction $methodName");
	}
	
	public function indexAction() {
		if(isset($this->_processObject)) {
			return $this->_indexProcessAction();
		} else {
			return $this->_indexClassAction();
		}
	}
	
	public function startAction() {
		if(isset($this->_processObject)) {
			trigger_error("Process/Controller/CantStartExistingProcess");
			return;
		}
	}
	
	public function updateAction() {
		if(!isset($this->_processObject)) {
			trigger_error("Process/Controller/CantUpdateUndefinedProcess");
			return;
		}
		/**
		 * Here we should do something with the process
		 */
		// @todo Make process update procedure
	}
}