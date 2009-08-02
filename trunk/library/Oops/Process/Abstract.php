<?php

/**
 * 
 * @author Dmitry Ivanov
 * 
 * @property-read string  $pid Process Id
 * @property-read string $currentState Process current state
 * @property-read alias $state Alias for currentState
 */
abstract class Oops_Process_Abstract {
	/**
	 * Process ID
	 * @var string
	 */
	private $_pid;
	
	/**
	 * Current state pointer
	 * @var string
	 */
	protected $_currentState;
	
	/**
	 * States definition
	 * @var array array('StateA','StateB',...)
	 */
	protected $_states = array();
	
	/**
	 * Process variables definition
	 * @var array array('data1' => 'public', 'data2' => 'private');
	 */
	protected $_variables = array();
	
	
	/**
	 * Transitions priority matrix
	 * stateA => array(stateB, stateC) - in order of precedence
	 * @var array
	 */
	protected $_transitions = array();

	/**
	 * Input variables go here 
	 */
	
	/**
	 * Internal variables go here
	 */
	
	/**
	 * Output variables go here
	 */
	
	/**
	 * State related decision makers go here
	 * Should be defined as (bool) protected function dM_$State()  
	 */
	
	/**
	 * Transition related decision makers go here
	 * Should be defined as (bool) protected function dM_$StateA_$StateB
	 */
	
	/**
	 * Transition actions go here
	 * Should be defined as (void) protected function action_$StateA_$StateB
	 */
	
	/**
	 * 
	 * @param $method
	 * @return unknown_type|bool
	 */
	protected final function __call($method) {
		if(stristr($method, '_dM_') === 0) {
			/**
			 * Decision maker is called and it is not defined
			 */
			return true;
		}
		return false;
	}

	/**
	 * Some actions for sleep and wakeup should be here
	 */
	
	/**
	 * Property getter
	 * 
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name) {
		switch($name) {
			case 'pid':
				return $this->_pid;
			case 'currentState':
			case 'state':
				return $this->_currentState;
		}
	}

	/**
	 * Properties setter
	 * @param string $name
	 * @param mixed $value
	 * @return mixed
	 */
	public function __set($name, $value) {
		switch($name) {
			case 'pid':
			case 'state':
			case 'currentState':
				require_once ("Oops_Process_Exception.php");
				throw new Oops_Process_Exception("Restricted", OOPS_PROCESS_EXCEPTION_RESTRICTED_SETTER);
		}
	}

	public final function tick() {
		/**
		 * Check if it's a valid 
		 */
		if(!isset($this->_pid)) {
			require_once ("Oops/Process/Exception.php");
			throw new Oops_Process_Exception("Can not tick process without pid", OOPS_PROCESS_EXCEPTION_NO_PID);
		}
		
		/**
		 * Ambigous check, this should be checked on setting pid
		 */
		if(!isset($this->_currentState)) {
			require_once ("Oops/Process/Exception.php");
			throw new Oops_Process_Exception("Process state is undefined", OOPS_PROCESS_EXCEPTION_NO_STATE);
		}
		
		/**
		 * @todo Check if there any way for the process, i.e. current state is not final
		 */
		
		/**
		 * Check if process can and should leave it's curent state 
		 */
		$stateDecisionMakerFunction = array($this, '_dM_' . $this->_currentState );
		if(call_user_func($stateDecisionMakerFunction)) {
			/**
			 * Try to leave the state, check transitions DMs
			 */
			
			/**
			 * @var bool Flag value indicating whenever we found available transition or not
			 */
			$foundTheWay = false;
			
			foreach($this->_states[$this->_currentState] as $state) {
				if($state == $this->_currentState) {
					trigger_error("Process/TransitionsDefinitionContainsLoopback", E_USER_NOTICE);
					continue;
				}
				$transitionDecisionMakerFunction = array($this, '_dM_' . $this->_currentState . '_' . $state );
				if(call_user_func($transitionDecisionMakerFunction)) {
					/**
					 * Found the way, initiate transition
					 */
					$foundTheWay = true;
					$this->_setState($state);
					break;
				}
			}
			
			if(!$foundTheWay) {
				/**
				 * We have to leave the current state, but there's no available way to go
				 * This indicates a conflict in decision makers, or missing some 'crash' state.
				 * Developer must eliminate conflicting decision maker.
				 */
				require_once ("Oops/Process/Exception.php");
				throw new Oops_Process_Exception('No way to go from state $state', OOPS_PROCESS_EXCEPTION_NO_WAY);
			}
		
		}
	
	}

	/**
	 * Change process state running a transition action, or init process setting the start state ith corresponding default action
	 * 
	 * @param string $newState
	 * @throws Oops_Process_Exception
	 * @return void
	 */
	protected final function _setState($newState) {
		if(!isset($this->_currentState)) {
			require_once ("Oops/Process/Exception.php");
			throw new Oops_Process_Exception('Process was not started, use Init', OOPS_PROCESS_EXCEPTION_INIT_REQUIRED);
		}
		
		// @todo Check whenever transition is valid (defined in $_states property)
		

		/**
		 * Do the transition
		 * @var callback
		 */
		$actionFunction = array($this, '_action_' . $this->_currentState . '_' . $newState );
		call_user_func($actionFunction);
		
		/** 
		 * Finally set the new state 
		 */
		$this->_currentState = $newState;
	}

	/**
	 * Process initialization, used by factory for newly created processes
	 * 
	 * @param array $inputValues - named input values
	 * @throws Oops_Process_Exception
	 */
	public function init($inputValues) {
		/**
		 * This can't be run if process already initialized
		 */
		if(isset($this->_currentState)) {
			require_once ("Oops/Process/Exception.php");
			throw new Oops_Process_Exception("Process already initialized", OOPS_PROCESS_EXCEPTION_INIT_COMPLETE);
		}
		/**
		 * Place incoming variables to object properties
		 */
		
		foreach($inputValues as $key => $value) {
			/**
			 * @todo Make it's safe
			 */
			if($key == 'currentState' || key == 'pid') {
				require_once ("Oops/Process/Exception.php");
				throw new Oops_Process_Exception("Invalid input value", OOPS_PROCESS_EXCEPTION_INVALID_INPUT);
			}
			$this->{'_' . $key} = $value;
		}
		
		/**
		 * Run custom init action (transition for nowhere to the start position) 
		 */
		$this->_actionStart();
		
		/**
		 * And only now, if everything ok (no exceptions), let's make a pid for this process
		 */
		$this->_pid = Oops_Process_Factory::generatePid();
	}

	/**
	 * Init internal values on process creation
	 * 
	 * @param string Start state id 
	 * @return void
	 */
	abstract protected function _actionStart($startState);

	/**
	 * Process objects control functions
	 * 1. Produce new Process instance, pass input values, save it
	 * 2. Restore existing Process by it's pid
	 * 
	 */
	
	/**
	 * 
	 * @param string $pid
	 */
	public final function __construct(string $pid = null) {
		if(is_null($pid)) {
			/**
			 * This is a newly created process, just wait for input values passed to init method
			 * Or, object could be constructed for definition purposes
			 */
			unset($this->_currentState);
			
		} else {
			/**
			 * This process is being restored, get process data from Storage
			 */
			require_once ("Oops/Process/Factory.php");
			$storage = & Oops_Process_Factory::getStorage();
			$data = $storage->get($pid);
			
			if($data === false) {
				/**
				 * There's no data in storage
				 */
				require_once("Oops/Process/Exception.php");
				throw new Oops_Process_Exception("Process data not found in storage", OOPS_PROCESS_EXCEPTION_NOT_FOUND);
			}
			
			/**
			 * Check for process class
			 * @var unknown_type
			 */
			$class = $data['class'];
			if(strtolower(get_class($this)) != strtolower($class)) {
				/**
				 * There's a wrong class being constructed 
				 */
				require_once("Oops/Process/Exception.php");
				throw new Oops_Process_Exception("Constructing ".get_class($this)." for a $class process", OOPS_PROCESS_EXCEPTION_INVALID_CLASS);
			}
			
			$currentState = $data['currentState'];
			if(!$this->isValidState($currentState)) {
				/**
				 * stored state is invalid
				 */
				require_once ("Oops/Process/Exception.php");
				throw new Oops_Process_Exception("Unable to restore $class process in invalid state $currentState", OOPS_PROCESS_EXCEPTION_INVALID_STATE);
			}
			$this->_currentState = $currentState;
			
			foreach($data['variables'] as $name => $value) {
				// @todo Check if $name is defined as process variable
				$this->{'_' . $name} = $value;
			}
			$this->_trigger_reconstructed();
		}
	}

	/**
	 * Override this function to make any actions after constructing object for existing process
	 */
	protected function _trigger_reconstructed() {
	}

	/**
	 * @uses Oops_Process_Abstract::$_states
	 * @param string $state
	 * @return bool True if $state is a valid state for this process
	 */
	protected final function isValidState($state) {
		if(in_array($state, $this->_states)) return true;
		return false;
	}

	/**
	 * Stores this process
	 * 
	 * @return bool True on success
	 */
	protected final function _store() {
		$data = array('class' => get_class($this), 'currentState' => $this->_currentState, 'variables' => array());
		foreach($this->_variables as $name => $access) {
			$data['variables'][$name] = $this->{'_' . $name};
		}
		
		require_once ("Oops/Process/Factory.php");
		
		/**
		 * 
		 * @var Oops_Process_Storage $storage
		 */
		$storage = & Oops_Process_Factory::getStorage();
		return $storage->set($this->_pid, $data);
	}

}