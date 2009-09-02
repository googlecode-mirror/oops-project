<?php

/**
 * 
 * @author Dmitry Ivanov
 * 
 * Class for JSON data presentation
 *
 */
class Oops_Server_View_Json extends Oops_Server_View {
	public function getContentType() {
		return 'x-application/json; charset=utf-8';
	}
	
	public function Out() {
		// @todo Try to locate Data Converter (Adapter) here and use it
		return json_encode($this->_in);
	}
}