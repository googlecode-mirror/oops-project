<?php

require_once ("Oops/Storage/Interface.php");
require_once ("Oops/Sql.php");

// @todo refactor to Oops_Process_Storage_Database, or use it, or extend Oops_Storage_Database
// @todo use stdClass or Oops_Process_Storage_Element instead of array in _composeData and _decomposeData methods
// @todo use some database abstraction layer for select/insert/replace/delete operations 
/**
 * 
 * @author Dmitry Ivanov
 * Process data retrieval and storing
 * 
 */
class Oops_Process_Storage implements Oops_Storage_Interface {
	private $_tableProcesses = 'processes';
	private $_tableProcessData = 'processData';
	
	private $_cached = array();
	

	public function __construct() {
		//@todo Init database settings
	}

	/**
	 * @param string $pid Process ID
	 * @return array Process Data
	 */
	public function get($pid) {
		// @todo cache results using _cacheGet _cacheSet
		if(isset($this->_cached[$pid])) return $this->_cached[$pid];
		 
		if(preg_match('/[^a-zA-Z0-9_]/', $pid)) {
			// @todo Throw exception here
			return false;
		}
		$r = Oops_Sql::Query("SELECT class, currentState FROM {$this->_tableProcesses} WHERE pid = '$pid'");
		switch(mysql_num_rows($r)) {
			case 0:
				return false;
			case 1:
				$ret = mysql_fetch_assoc($r);
				$ret['variables'] = array();
				
				$r = Oops_Sql::Query("SELECT name, class, id, serialized FROM {$this->_tableProcessData} WHERE pid = '$pid'");
				while((list($name, $class, $id, $serialized) = mysql_fetch_row($r)) !== false) {
					try {
						$ret['variables'][$name] = $this->_decomposeData($class, $id, $serialized);
					} catch(Exception $e) {
						/**
						 * Something was wrong, throw exception?
						 */
						// @todo Make it clear with exceptions
						throw $e;
					}
				}
				
				$this->_cached[$pid] = $ret;
				return $ret;
			default:
				// @todo throw database error exception
				return false;
		}
	}

	/**
	 * 
	 * @param Oops_Process_Abstract $process
	 * @return bool True on success
	 * @throws Oops_Process_Exception
	 */
	public function set($pid, $data) {
		if(!strlen($pid)) {
			require_once("Oops/Process/Exception.php");
			throw new Oops_Process_Exception("Can not store process without id", OOPS_PROCESS_EXCEPTION_NO_PID);
		}
		if(!isset($data['class'])) {
			require_once("Oops/Process/Exception.php");
			throw new Oops_Process_Exception("Proceess class not defined", OOPS_PROCESS_EXCEPTION_NO_CLASS);
		}
		
		$this->_cached[$pid] = $data;
		
		/**
		 * First store process class and state
		 */
		$class = Oops_Sql::Escape($data['class']);
		$currentState = Oops_Sql::Escape($data['currentState']);
		
		Oops_Sql::Query("REPLACE INTO {$this->_tableProcesses} (pid, class, currentState) VALUES ('$pid', '$class', '$currentState')");
		
		foreach($data['variables'] as $name => $value) {
			list($class, $id, $serialized) = $this->_composeData($value);
			Oops_Sql::EscapeIt($class);
			Oops_Sql::EscapeIt($id);
			Oops_Sql::EscapeIt($serialized);
			$valuesArray[] = "('$pid', '$name', '$class', '$id', '$serialized')";
		}
		$values = join(', ', $valuesArray);
		$keys = '(pid, name, class, id, serialized)';
		Oops_Sql::Query("REPLACE INTO {$this->_tableProcessData} $keys values $values");
		
	}

	/**
	 * Compose daa for storage
	 * 
	 * @param mixed $data
	 * @return array ($class, $id, $serialized)
	 */
	protected function _composeData($data) {
		
		if(is_object($data)) {
			// @todo Check for class interfaces to decide how to recover object id
			if(method_exists($data, 'getId')) {
				$id = $data->getId();
				$class = get_class($data);
				return array($class, $id, '');
			}
		}
		
		return array('','',serialize($data));
	}

	/**
	 * Decompose stored data to PHP object or value
	 * 
	 * @param $class
	 * @param $id
	 * @param $serialized
	 * @return mixed
	 */
	protected function _decomposeData($class, $id, $serialized) {
		if(strlen($serialized)) {
			// @todo Use Oops_Error_Handler, throw exception on unserialize failure
			require_once("Oops/Error/Handler.php");
			$eH = new Oops_Error_Handler();
			
			$result = unserialize($serialized);
			
			restore_error_handler();
			if(!$eH->isClear()) {
				throw new Exception("Decomposition Error");
			}
			
		} elseif(strlen($class) && Oops_Loader::find($class)) {
			// @todo Check for $class interfaces, use $class::getInstance or Factory if any
			$result = new $class($id);
		} else {
			throw new Exception("Decomposition Error");
		}
		return $result;
	}

	/**
	 * 
	 * @param string $pid
	 * @return string Stored process class
	 */
	protected function getClass($pid) {
		//@todo define getClass function
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Oops/Storage/Oops_Storage_Interface#add($id, $value)
	 */
	public function add($pid, $data) {
		throw new Exception(__CLASS__. "::" . __FUNCTION__ ." not implemented yet");
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Oops/Storage/Oops_Storage_Interface#replace($id, $value)
	 */
	public function replace($pid, $data) {
		throw new Exception(__CLASS__. "::" . __FUNCTION__ ." not implemented yet");
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Oops/Storage/Oops_Storage_Interface#delete($id)
	 */
	public function delete($pid) {
		throw new Exception(__CLASS__. "::" . __FUNCTION__ ." not implemented yet");
	}
}