<?php

interface Oops_Form_Observer_Interface {

	/**
	 * This function should attach form structure to the notification object
	 * Function is being called before showing the form
	 * 
	 * @param Oops_Form_Notification $notification
	 * @return void
	 */
	public function onBeforeFormShow(Oops_Form_Notification &$notification);

	/**
	 * This function is being called after form submission and should check the submitted values.
	 * If error occured notification must be cancelled ($notification->Cancel());
	 *  
	 * @param Oops_Form_Notification $notification
	 * @return void
	 */
	public function onBeforeFormSave(Oops_Form_Notification &$notification);

	/**
	 * This function is being called after all "onBeforeFormSave" observers run
	 * Submitted data to be stored (applied) in this function.
	 *  
	 * @param Oops_Form_Notification $notification
	 * @return void
	 */
	public function onAfterFormSave(Oops_Form_Notification &$notification);
}