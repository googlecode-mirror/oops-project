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
* Stream wrapper to be registered for internal requests (subrequests) schema (i.e. oops://...)
*/
class Oops_Server_Stream_Wrapper {
	var $_position = 0;
	var $_content = '';

	var $_redirectLimit = 2;

	var $_request;
	var $_isHandled = false;
   
	function stream_open($path, $mode, $options, &$opened_path) {
		require_once("Oops/Server/Request/Custom.php");
		$this->_request = new Oops_Server_Request_Custom($path);
		return true;
	}

	function stream_read($count) {
		$this->_handle();

		$ret = substr($this->_content, $this->_position, $count);
		$this->_position += strlen($ret);
		return $ret;
	}

	function stream_write($data) {
		if($this->_isHandled) return false;
		return $this->_request->setRawPost($data);
	}

	function stream_tell() {
		return $this->_position;
	}

	function stream_eof() {
		$this->_handle();
		return $this->_position >= strlen($this->_content);
	}

	function stream_stat() {
		$this->_handle();
		return array(
			'size' => strlen($this->content),
			'atime' => null,
			'mtime' => null,
			'ctime' => null,
		);
	}

	function stream_seek($offset, $whence) {
		$this->_handle();
		switch ($whence) {
			case SEEK_SET:
				$newPosition = $offset;
				break;
			case SEEK_CUR:
				$newPosition += $offset;
				break;
			case SEEK_END:
				$newPosition = strlen($this->_content) + $offset;
				break;
			default:
				return false;
		}
		if($newPosition < 0 || $newPosition > strlen($this->_content)) return false;
		$this->_position = $newPosition;
		return true;
	}

	/**
	* Handle the request
	*
	* @todo proceed 301, 302, 404 and other statuses. Current response object should be used for this, or Oops_Server::Run should return a response object instead of text
	*
	* @return void
	*/
	function _handle() {
		if($this->_isHandled) return;
		require_once("Oops/Server.php");
		$server =& new Oops_Server();
		$response = $server->Run($this->_request);

		while($response->isRedirect() && $this->_redirectLimit--) {
			/**
			* @todo do something, use Location header
			*/
			$this->_request = new Oops_Server_Request_Custom($response->getHeader("Location"));
			$response = $server->Run($this->_request);
		}

		$this->_content = $response->body;

		require_once("Oops/Server/Stack.php");
		Oops_Server_Stack::pop();
		$server = null;

		$this->_isHandled = true;
		$this->_position = 0;
	}
}

