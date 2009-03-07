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
require_once("Oops/Object.php");

/**
* OOPS Request object handing
*/
class Oops_Server_Request extends Oops_Object {
	var $_params = array();
	var $_body = '';
	var $_headers = array();

	var $scheme = 'http';
	var $host;
	var $port = 80;
	var $user;
	var $pass;
	var $path;
	var $query;
	var $fragment;

	
	function get($key) {
		return isset($this->_params[$key])?$this->_params[$key]:null;
	}

	function getBody() {
		return isset($this->_body)?$this->b_ody:null;
	}

	function getHeader($key) {
		return isset($this->_headers[$key])?$this->_headers[$key]:null;
	}

	function getHost() {
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
			$auth = $user;
			if(strlen($this->pass)) $auth.=":{$this->pass}";
			$host = "$auth@$host";
		}
		$url = "{$this->scheme}://$host".$uri;
		return $url;
	}

}
?>