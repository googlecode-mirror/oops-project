<?php

// @todo Request document for transition (almost complete - tickets)
// @todo Check for code duplication in init and setState methods.


require_once("Oops/Pattern/Identifiable/Factored/Interface.php");

/**
 * 
 * @author Dmitry Ivanov
 * 
 * @property-read string  $pid Process Id
 * @property-read string $currentState Process current state
 * @property-read alias $state Alias for currentState
 * 
 */
abstract class Oops_Process_Abstract implements Oops_Pattern_Identifiable_Factored_Interface {
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
	 * Any kind of additional operational process data to be places on input and not stored as process variables
	 * @var unknown_type
	 */
	protected $_extra = array();

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
	 * Should be defined as protected function action_$StateA_$StateB
	 * Can return Oops_Process_Ticket_Interface object to be stored as corresponding document
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
			default:
				if(in_array($name, $this->_variables)) return $this->{'_'.$name};
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
				require_once ("Oops/Process/Exception.php");
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
		$stateDecisionMakerFunction = array(
			$this, 
			'_dM_' . $this->_currentState );
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
				$transitionDecisionMakerFunction = array(
					$this, 
					'_dM_' . $this->_currentState . '_' . $state );
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
		
		if(!$this->isValidState($newState)) {
			require_once ("Oops/Process/Exception.php");
			throw new Oops_Process_Exception("Invalid state $newState", OOPS_PROCESS_EXCEPTION_INVALID_STATE);
		}
		
		/**
		 * Do the transition
		 * @var callback
		 */
		$actionFunction = array(
			$this, 
			'_action_' . $this->_currentState . '_' . $newState );
		$ticket = call_user_func($actionFunction);
		
		if(!is_object($ticket)) {
			$ticket = new Oops_Process_Ticket_Default();
		} elseif(!($ticket instanceof Oops_Process_Ticket_Interface)) {
			/**
			 * @quiz Do we need exception here?
			 */
			throw new Oops_Process_Exception("Invalid ticket");
		}
		
		/** 
		 * Finally set the new state 
		 */
		$this->_currentState = $newState;
		$this->_store($ticket);
	}

	/**
	 * Process initialization, used by factory for newly created processes
	 * 
	 * @param array $inputValues - named input values
	 * @throws Oops_Process_Exception
	 */
	public final function init($inputValues) {
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
			if($key == 'currentState' || key == 'pid') {
				require_once ("Oops/Process/Exception.php");
				throw new Oops_Process_Exception("Invalid input value", OOPS_PROCESS_EXCEPTION_INVALID_INPUT);
			}
			if(in_array($key, $this->_variables)) {
				$this->{'_' . $key} = $value;
			} else {
				$this->_extra[$key] = $value;
			}
		}
		
		/**
		 * Run custom init action (transition from nowhere to the start position) 
		 */
		$ticket = $this->_actionStart($this->_getStartState());
		if(!is_object($ticket)) $ticket = new Oops_Process_Ticket_Default();
		
		/**
		 * And only now, if everything ok (no exceptions), let's make a pid for this process
		 */
		$this->_pid = Oops_Process_Factory::generatePid();
		$this->_store();
	}

	/**
	 * Init internal values on process creation
	 * 
	 * @param string Start state id 
	 * @return Oops_Process_Ticket_Interface
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
				require_once ("Oops/Process/Exception.php");
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
				require_once ("Oops/Process/Exception.php");
				throw new Oops_Process_Exception("Constructing " . get_class($this) . " for a $class process", OOPS_PROCESS_EXCEPTION_INVALID_CLASS);
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
				if(array_key_exists($name, $this->_variables)) {
					$this->{'_' . $name} = $value;
				} else {
					require_once ("Oops/Process/Exception.php");
					throw new Oops_Process_Exception("Invalid variable name", OOPS_PROCESS_EXCEPTION_INVALID_INPUT);
				}
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
	protected final function _store($ticket) {
		$data = array(
			'class' => get_class($this), 
			'currentState' => $this->_currentState, 
			'ticket' => $ticket, 
			'variables' => array() );
		foreach($this->_variables as $name => $access) {
			$data['variables'][$name] = $this->{'_' . $name};
		}
		
		require_once ("Oops/Process/Factory.php");
		
		/**
		 * 
		 * @var Oops_Process_Storage $storage
		 */
		$storage =& Oops_Process_Factory::getStorage();
		return $storage->set($this->_pid, $data);
	}

	/**
	 * Returns start position of this process class.
	 * Method is being inside init, after calling assigning input variables and before calling _setState (that calls _actionStart)
	 * @redefine-check
	 * 
	 * @return string Start position id
	 */
	protected function _getStartState() {
		return $this->_states[0];
	}
	
	public static final function getFactoryCallback() {
		return array('Oops_Process_Factory', 'getProcess');
	}
	
	public final function getId() {
		return $this->_pid;
	}

}