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
class Oops_Server_Request_Custom extends Oops_Server_Request {
	var $uri;
	var $query_string;

	function __construct($url) {
		$parsed = parse_url($url);
		$this->uri = $parsed['path'];
		$this->query_string = $parsed['query'];
		parse_str($this->query_string,$this->_get);
		$this->host = $parsed['host'];

		$this->_params = $this->_get;
	}
}
?>