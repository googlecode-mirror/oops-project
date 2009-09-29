<?php

/**
 * Class for custom request configuration
 * 
 * @author Dmitry Ivanov
 *
 */
class Oops_Server_Request_Clone extends Oops_Server_Request {
	public function __construct(Oops_Server_Request $request) {
		foreach(get_object_vars($request) as $k=>$v) {
			$this->$k = $request->$k;
		}
	}
	
	public function __set($name, $value) {
		$this->_params[$name] = $value;
	}
	
	public function offsetSet($offset, $value) {
		$this->_params[$offset] = $value;
	}
}