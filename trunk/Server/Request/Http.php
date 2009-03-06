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
* Class for HTTP Request object representation
*/
class Oops_Server_Request_Http extends Oops_Server_Request {
	var $uri;
	var $query_string;

	function __construct() {
		$this->_get = $_GET;
		$this->_post = $_POST;
		$this->_cookie = $_COOKIE;

		list($this->uri,$this->query_string) = explode('?',$_SERVER['REQUEST_URI'],2);
	}
}
?>