<?php

class Oops_Form_Dispatcher extends Oops_Event_Dispatcher {
	
	public function __construct($name) {
		// @todo Solve the problem with Event_Registrator. Now this will not invoke Event_Dispatcher::getInstance. So a nested dispatcher should be used.
		parent::__construct($name);
	}

	/**
	 * (non-PHPdoc)
	 * @see Oops/Event/Oops_Event_Dispatcher#post($event, $info)
	 * 
	 * @return Oops_Form_Notification
	 */
	function &post($event, $info = array()) {
		$notification = new Oops_Form_Notification($event, $info);
		return $this->postNotification($notification);
	}

}