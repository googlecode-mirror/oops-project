<?php
/**
* @package Oops
* @subpackage Server
* @author Dmitry Ivanov <rockmagic@yandex.ru>
*/

/**
 * Load required classes
 */
require_once 'Oops/Server/Response.php';

/**
 * Oops server response corresponding to incoming (first) HTTP request
 */
class Oops_Server_Response_Http extends Oops_Server_Response {

	public function toString() {
		$this->getReady();
		$this->_sendHeaders();
		return $this->body;
	}

	protected function _sendHeaders() {
		header($this->getStatusLine());
		$this->setHeader('Content-length', strlen($this->body));
		foreach($this->headers as $name => $value) {
			$name = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));
			if(!is_array($value)) {
				header("$name: $value");
			} else {
				for($i = 0, $cnt = count($value); $i < $cnt; $i++) {
					$subvalue = array_shift($value);
					header("$name: $subvalue", !(boolean) $i);
				}
			}
		}
	}

	public function sendFile($file, $name = null) {
		if(!file_exists($file)) $this->setCode(404);
		if(!is_readable($file)) $this->setCode(403);
		if(!strlen($name)) $name = $file;
		
		// @todo try to use webserver for serving file
		$this->setHeader('Content-type', Oops_File_Utils::getMimeType($name));
		
		switch(true) {
			case strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false:
				$name = mb_convert_encoding($name, 'windows-1251', 'utf-8');
			case strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'opera') !== false:
				break;
			default:
				$name = '=?utf-8?B?' . base64_encode($name) . '?=';
		}
		$contentDisposition = 'attachment; filename="' . $name . '"';
		
		$this->setHeader('Content-Disposition', $contentDisposition);
		$this->getReady();
		$this->_sendHeaders();
		readfile($file);
		die();
	}
}