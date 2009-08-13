<?
/**
 * Default state of the notification
 */
@define('EVENT_NOTIFICATION_STATE_DEFAULT', 0);

/**
 * Notification has been cancelled
 */
@define('EVENT_NOTIFICATION_STATE_CANCELLED', 1);

class Oops_Event_Notification {
	var $_event;
	var $_info;
	var $_errors = array();
	var $_attached = array();
	
	var $_state = EVENT_NOTIFICATION_STATE_DEFAULT;

	function Oops_Event_Notification($event, &$info) {
		Oops_Event_Notification::__construct($event, $info);
	}

	function __construct($event, &$info) {
		$this->_event = $event;
		$this->_info = & $info;
	}

	function getState() {
		return $this->_state;
	}

	function &getInfo() {
		return $this->_info;
	}

	function getEvent() {
		return $this->_event;
	}

	function isCancelled() {
		return ($this->_state === EVENT_NOTIFICATION_STATE_CANCELLED ? true : false);
	}

	function Cancel($Error = null) {
		$this->_state = EVENT_NOTIFICATION_STATE_CANCELLED;
		if(!is_null($Error)) {
			if(is_array($Error))
				$this->_errors = array_merge($this->_errors, $Error);
			else
				$this->_errors[] = $Error;
		
		}
	}

	function GetErrors() {
		if($this->isCancelled()) return $this->_errors;
		return null;
	}

	function attachData($key, $value) {
		$this->_attached[$key] = $value;
	}

	function detachData($key) {
		unset($this->_attached[$key]);
	}

	function getAttachedData($mergeTo = null) {
		if(!is_array($mergeTo)) {
			return array_merge($mergeTo, $this->_attached);
		}
		return $this->_attached;
	}
}