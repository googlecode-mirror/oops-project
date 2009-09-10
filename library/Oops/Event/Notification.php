<?php

/**
 * @package Oops
 * @subpackage Event_Dispatcher
 */

/**
 * 
 * @author Dmitry Ivanov <rockmagic@yandex.ru>
 * 
 * @property-read string $event Event string ID
 * @property-read mixed $info Event details
 * @property-read array $errors Notification errors (if was cancelled with errors)
 * @property-read array $attached Data attached by observers
 * @property-read int $_state;
 *
 */
class Oops_Event_Notification {
	
	const STATE_DEFAULT = 0;
	const STATE_CANCELLED = 1;
	
	protected $_event;
	protected $_info;
	protected $_errors = array();
	protected $_attached = array();
	protected $_state = self::STATE_DEFAULT;

	/**
	 * 
	 * @param string $event Event string ID (ex. onAfterSomethingHappened)
	 * @param mixed $info Event details
	 */
	function __construct($event, $info = null) {
		$this->_event = $event;
		$this->_info = $info;
	}
	
	/**
	 * Getter
	 * 
	 * @ignore
	 */
	function __get($name) {
		switch($name) {
			case 'event':
				return $this->_event;
			case 'info':
				return $this->_info;
			case 'errors':
				return $this->getErrors();
			case 'attached':
				return $this->_attached;
			case 'state':
				return $this->_state;
			
		}
	}

	function getState() {
		return $this->_state;
	}

	function getInfo() {
		return $this->_info;
	}

	function getEvent() {
		return $this->_event;
	}

	/**
	 * 
	 * @return bool True if notification was cancelled
	 */
	function isCancelled() {
		return ($this->_state === self::STATE_CANCELLED ? true : false);
	}

	/**
	 * Cancels the notification and stores passed error
	 * 
	 * @param string $Error
	 */
	public function Cancel($Error = null) {
		$this->_state = self::STATE_CANCELLED;
		if(!is_null($Error)) {
			if(is_array($Error))
				$this->_errors = array_merge($this->_errors, $Error);
			else
				$this->_errors[] = $Error;
		
		}
	}

	/**
	 * Returns all errors stored after cancel calls
	 * 
	 * @return array Error strings
	 */
	function getErrors() {
		if($this->isCancelled()) return $this->_errors;
		return null;
	}

	/**
	 * Attaches mixed data to notification
	 * 
	 * @param string $key Data key (name)
	 * @param mixed $value Data to attach
	 */
	function attachData($key, $value) {
		$this->_attached[$key] = $value;
	}

	/**
	 * Detaches any attached data
	 * 
	 * @param string $key Data key
	 */
	function detachData($key) {
		unset($this->_attached[$key]);
	}

	function getAttachedData($mergeTo = null) {
		if(is_array($mergeTo)) {
			return array_merge($mergeTo, $this->_attached);
		}
		return $this->_attached;
	}
}