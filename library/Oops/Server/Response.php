<?php

/**
 * @package Oops
 * @subpackage Server
 * @author Dmitry Ivanov <rockmagic@yandex.ru>
 */

/**
 * Oops server response representation
 * 
 * @property integer $code Response code (HTTP)
 * @property-read string $message Response message according to code
 * @property string $version Protocol version
 * @property array $headers Response headers array
 */
class Oops_Server_Response {
	protected $_code;
	protected $_message;
	protected $_version = '1.x';
	protected $_headers = array();
	
	/**
	 * Response body
	 * @var string
	 */
	public $body = '';
	
	protected static $_messages = array(
		// Informational 1xx
		100 => 'Continue', 
		101 => 'Switching Protocols', 
		
		// Success 2xx
		200 => 'OK', 
		201 => 'Created', 
		202 => 'Accepted', 
		203 => 'Non-Authoritative Information', 
		204 => 'No Content', 
		205 => 'Reset Content', 
		206 => 'Partial Content', 
		
		// Redirection 3xx
		300 => 'Multiple Choices', 
		301 => 'Moved Permanently', 
		302 => 'Found',  // 1.1
		303 => 'See Other', 
		304 => 'Not Modified', 
		305 => 'Use Proxy', 
		// 306 is deprecated but reserved
		307 => 'Temporary Redirect', 
		
		// Client Error 4xx
		400 => 'Bad Request', 
		401 => 'Unauthorized', 
		402 => 'Payment Required', 
		403 => 'Forbidden', 
		404 => 'Not Found', 
		405 => 'Method Not Allowed', 
		406 => 'Not Acceptable', 
		407 => 'Proxy Authentication Required', 
		408 => 'Request Timeout', 
		409 => 'Conflict', 
		410 => 'Gone', 
		411 => 'Length Required', 
		412 => 'Precondition Failed', 
		413 => 'Request Entity Too Large', 
		414 => 'Request-URI Too Long', 
		415 => 'Unsupported Media Type', 
		416 => 'Requested Range Not Satisfiable', 
		417 => 'Expectation Failed', 
		
		// Server Error 5xx
		500 => 'Internal Server Error', 
		501 => 'Not Implemented', 
		502 => 'Bad Gateway', 
		503 => 'Service Unavailable', 
		504 => 'Gateway Timeout', 
		505 => 'HTTP Version Not Supported', 
		509 => 'Bandwidth Limit Exceeded');

		
	public function __get($name) {
		switch($name) {
			case 'code':
				return $this->_code;
			case 'message':
				return $this->_message;
			case 'headers':
				return $this->_headers;
			case 'version':
				return $this->_version;
			default:
				return null;
		}
	}

	public function __set($name, $value) {
		switch($name) {
			case 'code':
				return $this->setCode($value);
			case 'headers':
				return $this->setHeaders($value);
			case 'version':
				return $this->setVersion($value);
		}
	}

	public function isReady() {
		return isset($this->_code) ? true : false;
	}

	public function setCode($code, $dontThrowException = false) {
		if(!isset(self::$_messages[$code])) {
			// @todo Consider throw exception here
			return false;
		}
		$this->_code = $code;
		$this->_message = self::$_messages[$code];
		if($dontThrowException) return true;
		require_once 'Oops/Server/Exception.php';
		throw new Oops_Server_Exception("Done", OOPS_SERVER_EXCEPTION_RESPONSE_READY);
	}

	public function setHeaders($headers) {
		$this->_headers = $headers;
	}
	
	public function setVersion($version) {
		if(preg_match('/^1\.[x\d]$/', $version)) $this->_version = $version;
	}

	public function setHeader($name, $value, $replace = true) {
		$name = strtolower($name);
		if($replace || !isset($this->_headers[$name])) {
			$this->_headers[$name] = $value;
		} else {
			if(!is_array($this->_headers[$name])) {
				require_once ("Oops/Utils.php");
				Oops_Utils::toArray($this->_headers[$name]);
			}
			$this->_headers[$name][] = $value;
		}
	}

	/**
	 * Get all headers as array
	 *
	 * @return array
	 */
	public function getHeaders() {
		return $this->_headers;
	}

	/**
	 * Get header identified by name
	 *
	 * @param string
	 * @return string|array
	 */
	public function getHeader($name) {
		$name = strtolower($name);
		if(isset($this->_headers[$name])) return $this->_headers[$name];
		return '';
	}

	/**
	 * Get HTTP response status line
	 *
	 * @return string
	 */
	public function getStatusLine() {
		return "HTTP/{$this->_version} {$this->_code} {$this->_message}";
	}

	/**
	 * Get all headers as string
	 *
	 * @param boolean $status_line Whether to return the first status line (IE "HTTP 200 OK")
	 * @param string $br Line breaks (eg. "\n", "\r\n", "<br />")
	 * @return string
	 */
	public function getHeadersAsString($status_line = true, $br = "\n") {
		$str = '';
		
		if($status_line) {
			$str = $this->getStatusLine() . $br;
		}
		
		// Iterate over the headers and stringify them
		foreach($this->_headers as $name => $value) {
			$name = str_replace(' ', '-', ucwords(str_replace('-',' ',$name)));
			if(!is_array($value))
				$str .= "{$name}: {$value}{$br}";
			
			else {
				foreach($value as $subval) {
					$str .= "{$name}: {$subval}{$br}";
				}
			}
		}
		return $str;
	}

	/**
	 * Check whether the response is a redirection
	 *
	 * @return boolean
	 */
	public function isRedirect() {
		$restype = floor($this->_code / 100);
		if($restype == 3) {
			return true;
		}
		
		return false;
	}

	public function redirect($location, $permanent = false, $dontThrowException = false) {
		$this->setHeader('Location', $location);
		$this->setCode($permanent ? 301 : 302, $dontThrowException);
	}

	public function getReady() {
		if(!$this->isReady()) $this->setCode(200, true);
	}

	public function __toString() {
		return $this->toString();
	}

	public function toString() {
		return $this->body;
	}

	public function setBody($body) {
		$this->body = $body;
	}

	public function reportErrors($errorHandler) {
		if(!is_object($errorHandler)) return;
		foreach($errorHandler->getErrors() as $err)
			$this->setHeader("Oops-Error", $err, false);
		foreach($errorHandler->getWarnings() as $err)
			$this->setHeader("Oops-Warning", $err, false);
		foreach($errorHandler->getNotices() as $err)
			$this->setHeader("Oops-Notice", $err, false);
		foreach($errorHandler->getPhps() as $err)
			$this->setHeader("PHP-Errors", $err, false);
	}
}