<?
/**
* @package Oops
* @subpackage Server
* @author Dmitry Ivanov <rockmagic@yandex.ru>
*/

/**
* Check if Oops is loaded
*/
if(!defined("OOPS_Loaded")) die("OOPS not found");

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
			if(is_string($value)) {
				header("$name: $value");
			} elseif(is_array($value)) {
				for($i=0,$cnt=count($value);$i<$cnt;$i++) {
					$subvalue = array_shift($value);
					header("$name: $subvalue",!(boolean)$i);
				}
			}
		}
	}
}