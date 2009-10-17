<?php

/**
 * @package Oops
 * @subpackage Event_Dispatcher
 */

/**
 * Event dispatcher class
 */
class Oops_Event_Dispatcher {
	protected $_name;
	protected $_ro = array();
	protected $_nestedDispatchers = array();

	protected function __construct($name = '') {
		if(strlen($name)) $this->_name = $name;
	}

	/**
	 * Returns a notification dispatcher singleton
	 *
	 * @param string $name Name of the notification dispatcher. Default notification dispatcher is named __default.
	 * @return Oops_Event_Dispatcher
	 */
	public static function getInstance($name = '__default') {
		static $dispatchers = array();
		$name = strtolower($name);
		if(!isset($dispatchers[$name])) {
			$dispatchers[$name] = new Oops_Event_Dispatcher($name);
			$cfg = self::_getConfig($name);
			foreach($cfg as $event => $observersString) {
				$observers = explode(',', $observersString);
				foreach($observers as $callback) {
					if(strpos($callback, '::')) $callback = explode('::', $callback);
					if(is_callable($callback, true)) {
						$dispatchers[$name]->addObserver($callback, $event);
					}
				}
			}
		
		}
		return $dispatchers[$name];
	}

	/**
	 * Reads config from ini file ./application/config/events.ini
	 *  returns section according to passed name
	 *  
	 * @param string $name Dispatcher name
	 */
	protected static function _getConfig($name) {
		static $config = null;
		if(!isset($config)) {
			if(file_exists('./application/config/events.ini')) {
				$config = new Oops_Config_Ini('./application/config/events.ini');
			} else {
				$config = new Oops_Config();
			}
		}
		return $config->$name;
	}

	/**
	 * Registers observer callback for the event
	 *
	 * Return false if the callback is already registered for the given event
	 *
	 * @param mixed		A PHP Callback
	 * @param string	Event name
	 * @return bool		True if the observer has been registered, false otherwise
	 */
	public function addObserver($callback, $event) {
		if(!is_string($event)) {
			// @todo Consider throwing exception here
			return false;
		}
		
		$event = strtolower($event);
		
		if(!($reg = $this->_identifyCallback($callback))) {
			return false;
		}
		
		if(!isset($this->_ro[$event])) {
			$this->_ro[$event] = array($reg => $callback);
		} else {
			if(isset($this->_ro[$event][$reg])) return false;
			$this->_ro[$event][$reg] = $callback;
		}
		return true;
	}

	public function removeObserver($callback, $event) {
		$event = strtolower($event);
		if(!isset($this->_ro[$event])) return false;
		if(!($reg = $this->_identifyCallback($callback))) return false;
		if(!isset($this->_ro[$event][$reg])) return false;
		unset($this->_ro[$event][$reg]);
		return true;
	}

	protected function _identifyCallback($callback) {
		static $objectsCounter = 0;
		//Let's identify the callback
		if(is_array($callback)) {
			if(is_object($callback[0])) {
				// @todo make another objects numeration
				if(!isset($callback[0]->observerId)) {
					$callback[0]->observerId = $objectsCounter++;
				}
				$objectId = $callback[0]->observerId;
				
				$reg = get_class($callback[0]) . '{' . $objectId . '}' . '::' . strtolower($callback[1]);
			} else {
				$reg = strtolower($callback[0]) . '::' . strtolower($callback[1]);
			}
		} elseif(is_string($callback) && function_exists($callback)) {
			$reg = $callback;
		} else {
			// @todo Consider throwing exception on illegal callback here
			return false;
		}
		return $reg;
	}

	/**
	 * Creates notification object and notifies registered observers
	 * @param string   Event name
	 * @param mixed    Event information of any kind
	 * @return object  The notification object
	 */
	public function post($event, $info = array()) {
		require_once ("Oops/Event/Notification.php");
		$notification = new Oops_Event_Notification($event, $info);
		return $this->postNotification($notification);
	
	}

	/**
	 * Notifies registered observers and nested dispatchers (if implemented)
	 * @param object   The notification object
	 * @return object  The notification object //!!!! not necessary
	 */
	public function postNotification($notification) {
		$event = strtolower($notification->getEvent());
		if(!isset($this->_ro[$event])) return $notification;
		foreach($this->_ro[$event] as $callback) {
			if($notification->isCancelled()) return $notification;
			if(is_array($callback) && !is_object($callback[0])) {
				require_once ("Oops/Loader.php");
				Oops_Loader::load($callback[0]);
			}
			call_user_func_array($callback, array($notification));
		}
		/* Here to call nested dispatchers and pending observers */
		foreach($this->_nestedDispatchers as $nestedDispatcher) {
			$notification = $nestedDispatcher->postNotification($notification);
		}
		
		return $notification;
	}

	public function addNestedDispatcher($dispatcher) {
		if(!is_object($dispatcher)) {
			/**
			 * consider throwing exception here
			 */
			return false;
		}
		$dispatcherName = $dispatcher->getName();
		if(!isset($this->_nestedDispatchers[$dispatcherName])) {
			$this->_nestedDispatchers[$dispatcherName] = $dispatcher;
			return true;
		}
		return false;
	}

	/**
	 * Removes nested dispatcher
	 * @param unknown_type $dispatcher
	 */
	public function removeNestedDispatcher($dispatcher) {
		$dispatcherName = (string) $dispatcher;
		if(isset($this->_nestedDispatchers[$dispatcherName])) {
			unset($this->_nestedDispatchers[$dispatcher]);
			return true;
		}
		return false;
	}

	/**
	 * Returns dispatcher name
	 * @return string
	 */
	public function getName() {
		return $this->_name;
	}

	public function __toString() {
		return $this->_name;
	}

}