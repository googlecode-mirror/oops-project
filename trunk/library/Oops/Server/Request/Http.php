<?
/**
* @package Oops
* @subpackage Server
* @author Dmitry Ivanov <rockmagic@yandex.ru>
*/
if(!defined("OOPS_Loaded")) die("OOPS not found");

/**
* Load required classes
*/
require_once("Oops/Server/Request.php");

/**
* HTTP request object representation
*/
class Oops_Server_Request_Http extends Oops_Server_Request {
	var $_get = array();
	var $_post = array();
	var $_cookie = array();

	function __construct() {
		$this->_get = $_GET;
		$this->_post = $_POST;
		$this->_cookie = $_COOKIE;

		$this->_params = array_merge($this->_post,$this->_get);

		$parsed = parse_url($_SERVER['REQUEST_URI']);
		foreach($parsed as $name=>$value) $this->$name = $value;

		if(!isset($this->host)) $this->host = $_SERVER['HTTP_HOST'];
		if(!isset($this->port)) $this->port = $_SERVER['SERVER_PORT'];
		if(!isset($this->user) && isset($_SERVER['PHP_AUTH_USER'])) $this->user = $_SERVER['PHP_AUTH_USER'];
		if(!isset($this->pass) && isset($_SERVER['PHP_AUTH_PW'])) $this->pass = $_SERVER['PHP_AUTH_PW'];
		
	}
}
