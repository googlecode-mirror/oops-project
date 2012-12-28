<?php

class Oops_Controller_Actions extends Oops_Controller {
	
	public function Run() {
		$method = 'action' . $this->_server->action;
		if(!method_exists($this, $method)) $method = 'actionDefault';
		return $this->$method();
	}
	
	public function actionDefault() {
		$this->_response->setCode(400);
	}
	
}