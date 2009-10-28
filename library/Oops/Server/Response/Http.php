<?php
/**
* @package Oops
* @subpackage Server
* @author Dmitry Ivanov <rockmagic@yandex.ru>
*/

/**
* Load required classes
*/
require_once("Oops/Server/Response.php");

/**
* Oops server response corresponding to incoming (first) HTTP request
*/
class Oops_Server_Response_Http extends Oops_Server_Response {
	function toString() {
		$this->getReady();
		if(headers_sent()) {
			trigger_error("Server_Response_Http/HeadersAlreadySent", E_USER_WARNING);
		} else {
			$this->_sendHeaders();
		}
		return $this->body;
	}

	function _sendHeaders() {
		header($this->getStatusLine());
		foreach($this->headers as $name=>$value) {
			$name = ucfirst($name);
			if(!is_array($value)) {
				header("$name: $value");
			} else {
				for($i=0,$cnt=count($value);$i<$cnt;$i++) {
					$subvalue = array_shift($value);
					header("$name: $subvalue",!(boolean)$i);
				}
			}
		}
	}
}