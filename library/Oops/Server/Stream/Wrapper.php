<?php

/**
 * @package Oops
 * @subpackage Server
 * @author Dmitry Ivanov <rockmagic@yandex.ru>
 */

/**
 * Stream wrapper to be registered for internal requests (subrequests) schema (i.e. oops://...)
 */
class Oops_Server_Stream_Wrapper {
	private $_position = 0;
	private $_content = '';
	private $_redirectLimit = 2;
	private $_request;
	private $_isHandled = false;

	function stream_open($path, $mode, $options, &$opened_path) {
		$this->_request = new Oops_Server_Request_Custom($path);
		$this->_isHandled = false;
		$this->_position = 0;
		$this->_content = '';
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
			'size' => strlen($this->_content), 
			'atime' => null, 
			'mtime' => null, 
			'ctime' => null);
	}

	function stream_seek($offset, $whence) {
		$this->_handle();
		switch($whence) {
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
		$server = Oops_Server::newInstance();
		$response = $server->Run($this->_request);
		
		while($response->isRedirect() && $this->_redirectLimit--) {
			/**
			 * @todo do something, use Location header
			 */
			$this->_request = new Oops_Server_Request_Custom($response->getHeader("Location"));
			$response = $server->Run($this->_request);
		}
		
		Oops_Server::popInstance();
		$server = null;

		if(!$response->isRedirect()) {
			$this->_content = $response->body;
			/* translate headers in order to deliver control headers like 'X-Accel-Expires' */
			$childHeaders = $response->getHeaders();
			if(count($childHeaders)) {
				$parentHeaders = Oops_Server::getResponse()->getHeaders();
				foreach($childHeaders as $k => $v) {
					if(!isset($parentHeaders[$k])) {
						Oops_Server::getResponse()->setHeader($k, $v);
					} elseif(is_array($parentHeaders[$k])) {
						Oops_Utils::ToArray($v);
						foreach($v as $vv)
							Oops_Server::getResponse()->setHeader($k, $vv, false);
					}
				}
			}
		}
		
		$this->_isHandled = true;
		$this->_position = 0;
	}
}

