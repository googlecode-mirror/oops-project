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
* Internal (sub)request representation object (oops://local/path/to/resource/action.view)
*/
class Oops_Server_Request_Custom extends Oops_Server_Request {

	function __construct($url) {
		$parsed = parse_url($url);
		foreach($parsed as $name=>$value) $this->$name = $value;

		parse_str($this->query,$this->_get);
		$this->_params = $this->_get;
	}
}
