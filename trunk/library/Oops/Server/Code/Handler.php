<?php

class Oops_Server_Code_Handler {
	
	/**
	 *
	 * @var Oops_Server_Code_Handler
	 */
	private $_prevHandler;

	final public function setPrevHander($handler) {
		// @todo check class
		$this->_prevHandler = $handler;
	}

	final protected function usePrevHandler($response) {
		if(!is_object($this->_prevHandler)) return;
		$this->_prevHandler->handle($response);
	}

	final public function getPrevHandler() {
		return $this->_prevHandler;
	}

	/**
	 * Should set body to $response, may change code
	 *
	 * @param Oops_Server_Response $response        	
	 */
	public function handle($response) {
		// find template, use it or call prev handler
		if($response->code >= 400) {
			/**
			 *
			 * @todo check requested view, use same View
			 */
			$templateData = array(
				'errormessage' => $response->message, 
				'errorcode' => $response->code);
			$errorTpl = Oops_Template::getInstance('_errorpage/' . $response->code . '.php');
			if($errorTpl->isValid()) $response->setBody($errorTpl->Out($templateData));
		}
	}

	/**
	 *
	 * @param Oops_Server_Response $response        	
	 * @param int $code        	
	 */
	protected function _changeCode($response, $code) {
		$response->setCode($code, true);
		$this->handle($response);
	}
}