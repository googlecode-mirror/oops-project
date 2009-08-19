<?php

require_once 'Oops/Form/Observer/Interface.php';

class Oops_Form_Kcaptcha_Observer implements Oops_Form_Observer_Interface {
	private $_requestKey = 'captcha';

	public function __construct($requestKey = null) {
		if(is_null($requestKey)) return;
		$requestKey = strval($requestKey);
		if(strlen($requestKey)) $this->_requestKey = strval($requestKey);
	}

	public function onBeforeFormShow(Oops_Form_Notification $notification) {
		/**
		 * Attach captcha field
		 */
		$notification->attachData('captcha', array(
			'field' => $this->_requestKey, 
			'required' => true));
	}

	public function onBeforeFormSave(Oops_Form_Notification $notification) {
		$request = $notification->getInfo();
		$passedValue = strval($request[$this->_requestKey]);
		if(strlen($passedValue)) {
			require_once 'Oops/Kcaptcha/Storage.php';
			$captchaStorage = new Oops_Kcaptcha_Storage();
			/**
			 * Here we could pass second argument to captcha checker in irder to keep value in storage
			 * If so we could skip showing captcha on other form form errors
			 */
			if($captchaStorage->Check($this->_requestKey)) return;
		}
		$notification->Cancel("Captcha error", $this->_requestKey);
	}

	public function onAfterFormSave(Oops_Form_Notification $notification) {
	}

}