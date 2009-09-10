<?php

/**
 * @package Oops
 * @subpackage Server
 * @author Dmitry Ivanov <rockmagic@yandex.ru>
 */

/**
 * OOPS Request object handing
 * @abstract
 */
class Oops_Server_Request implements ArrayAccess, Countable, Iterator {
	protected $_params = array();
	protected $_body = '';
	protected $_headers = array();
	protected $_arrayPosition = 0; 
	
	public $scheme = 'http';
	public $host;
	public $port = 80;
	public $user;
	public $pass;
	public $path;
	public $query;
	public $fragment;

	public function __get($key) {
		return $this->get($key);
	}

	public function get($key) {
		return isset($this->_params[$key]) ? $this->_params[$key] : null;
	}
	
	public function getAsArray() {
		return $this->_params;
	}

	public function getKeys() {
		return array_keys($this->_params);
	}

	public function getBody() {
		return isset($this->_body) ? $this->_body : null;
	}

	public function getHeader($key) {
		return isset($this->_headers[$key]) ? $this->_headers[$key] : null;
	}

	public function getHost() {
		return isset($this->host) ? $this->host : null;
	}

	public function getPath() {
		return isset($this->path) ? $this->path : '';
	}

	public function getResourcePath() {
		if(!isset($this->path)) {
			return '';
		} elseif(substr($this->path, -1, 1) == '/') {
			//pat ends with '/' - it's a directory
			return $this->path;
		} else {
			$dirname = str_replace(DIRECTORY_SEPARATOR, '/', dirname($this->path));
			if(substr($dirname, -1, 1) == '/') return $dirname;
			return $dirname . '/';
		}
	}

	public function getQuery() {
		return isset($this->query) ? $this->query : '';
	}

	public function getUri() {
		$uri = $this->path;
		if(strlen($this->query)) $uri .= "?{$this->query}";
		return $uri;
	}

	public function getUrl() {
		$uri = $this->getUri();
		if(!strlen($this->host)) return $uri;
		
		$host = $this->host;
		if($this->port != 80) $host .= ":{$this->port}";
		
		if(strlen($this->user)) {
			$auth = $this->user;
			if(strlen($this->pass)) $auth .= ":{$this->pass}";
			$host = "$auth@$host";
		}
		$url = "{$this->scheme}://$host" . $uri;
		return $url;
	}
	
	/**
	 * ArrayAccess interface methods
	 */
	public function offsetExists($offset) {
		return array_key_exists($offset, $this->_params);
	}
	
	public function offsetGet($offset) {
		return $this->get($offset);
	}
	
	public function offsetSet($offset, $value) {
		/**
		 * Protected
		 */
		return false;
	}
	
	public function offsetUnset($offset) {
		return false;
	}
	
	/**
	 * Countable insterface method
	 */
	public function count() {
		return count($this->_params);
	}
	
	/**
	 * Iterator interface method
	 */
	public function current() {
		return current($this->_params);
	}
	
	public function key() {
		return key($this->_params);
	}
	
	public function next() {
		++$this->_arrayPosition;
		next($this->_params);
	}
	
	public function rewind() {
		$this->_arrayPosition = 0;
		reset($this->_params);
	}
	
	public function valid() {
		if($this->_arrayPosition < count($this->_params) && $this->_arrayPosition >= 0) return true;
		return false;
	}
}
