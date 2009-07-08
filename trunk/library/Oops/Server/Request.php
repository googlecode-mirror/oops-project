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
* OOPS Request object handing
* @abstract
*/
class Oops_Server_Request {
	protected $_params = array();
	protected $_body = '';
	protected $_headers = array();

	public $scheme = 'http';
	public $host;
	public $port = 80;
	public $user;
	public $pass;
	public $path;
	public $query;
	public $fragment;

	
	function get($key) {
		return isset($this->_params[$key])?$this->_params[$key]:null;
	}

	function getKeys() {
		return array_keys($this->_params);
	}

	function getBody() {
		return isset($this->_body)?$this->_body:null;
	}

	function getHeader($key) {
		return isset($this->_headers[$key])?$this->_headers[$key]:null;
	}

	function getHost() {
		return isset($this->host)?$this->host:null;
	}

	function getPath() {
		return isset($this->path)?$this->path:'';
	}
	
	function getResourcePath() {
		if(!isset($this->path)) {
			return '';
		} elseif(substr($this->path,-1,1) == '/') {
			//pat ends with '/' - it's a directory
			return $this->path;
		} 
		else return dirname($this->path).'/';
	}

	function getQuery() {
		return isset($this->query)?$this->query:'';
	}

	function getUri() {
		$uri = $this->path;
		if(strlen($this->query)) $uri .= "?{$this->query}";
		return $uri;
	}

	function getUrl() {
		$uri = $this->getUri();
		if(!strlen($this->host)) return $uri;

		$host = $this->host;
		if($this->port != 80) $host.=":{$this->port}";

		if(strlen($this->user)) {
			$auth = $this->user;
			if(strlen($this->pass)) $auth.=":{$this->pass}";
			$host = "$auth@$host";
		}
		$url = "{$this->scheme}://$host".$uri;
		return $url;
	}

}
