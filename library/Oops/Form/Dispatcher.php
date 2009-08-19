<?php

require_once 'Oops/Event/Dispatcher.php';

class Oops_Form_Dispatcher extends Oops_Event_Dispatcher {
	
	public function __construct($name) {
		$dispatcher = Oops_Event_Dispatcher::getInstance($name);
		$this->addNestedDispatcher($dispatcher);		
	}

	/**
	 * (non-PHPdoc)
	 * @see Oops/Event/Oops_Event_Dispatcher#post($event, $info)
	 * 
	 * @return Oops_Form_Notification
	 */
	function post($event, $info = array()) {
		$notification = new Oops_Form_Notification($event, $info);
		return $this->postNotification($notification);
	}

}