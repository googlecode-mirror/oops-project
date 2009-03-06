<?

class Oops_Server_Response {
	var $code;
	var $message;
	var $version = '1.1';
	var $headers = array();
	var $body = '';

    	var $_messages = array(
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
		509 => 'Bandwidth Limit Exceeded'
	);


	function isReady() {
		if(isset($this->code)) return true;
		return false;
	}

	function setCode($code) {
		if(!isset($this->_messages[$code])) return false;
		$this->code = $code;
		$this->message = $this->_messages[$code];
		return true;
	}

	function setHeaders($headers) {
		$this->headers = $headers;
	}

	function setHeader($key,$value,$replace = true) {
		$key = ucfirst(strtolower($key));
		if($replace || !isset($this->headers[$key])) {
			$this->headers[$key] = $value;
		} else {
			if(!is_array($this->headers[$key])) {
				require_once("Oops/Utils.php");
				Oops_Utils::toArray($this->headers[$key]);
			}
			$this->headers[$key][]=$value;
		}
	}

	function getHeaders() {
		return $this->headers;
	}

	function getHeader($key) {
		if(isset($this->headers[$key])) return $this->headers[$key];
		return null;
	}

	function getStatusLine() {
		return "HTTP/{$this->version} {$this->code} {$this->message}";
	}

	/**
	* Get all headers as string
	*
	* @param boolean $status_line Whether to return the first status line (IE "HTTP 200 OK")
	* @param string $br Line breaks (eg. "\n", "\r\n", "<br />")
	* @return string
	*/
	function getHeadersAsString($status_line = true, $br = "\n") {
		$str = '';

		if ($status_line) {
			$str = $this->getStatusLine() . $br;
		}

		// Iterate over the headers and stringify them
		foreach ($this->headers as $name => $value)
		{
			if (is_string($value))
				$str .= "{$name}: {$value}{$br}";

			elseif (is_array($value)) {
				foreach ($value as $subval) {
					$str .= "{$name}: {$subval}{$br}";
				}
			}
		}
		return $str;
	}

	function redirect($location,$permanent = false) {
		$this->setCode($permanent?301:302);
		$this->setHeader('Location',$location);
	}

	function getReady() {
		if(!$this->isReady()) $this->setCode(200);
	}

	function toString() {
		return $this->body;
	}

	function setBody($body) {
		$this->body = $body;
	}
}