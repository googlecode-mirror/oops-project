<?
/**
* @package Oops
*/

if(!defined('OOPS_Loaded')) die("OOPS not found");

/**
* Abstract controller class
* 
* @abstract
*/
class Oops_Controller extends Oops_Object {
	/**
	* @deprecated
	*/
	var $_server;

	/**
	* @todo Consider using request and response as controller constructor params?
	*/
	function __construct() {
		$this->_server =& Oops_Server::getInstance();
		$this->_request =& Oops_Server::getRequest();
		$this->_response =& Oops_Server::getResponse();
	}

	/**
	* Get requested value, modified to the requested type
	*
	* @param string Request key
	* @param string Required value type
	* @return mixed 
	* @tutorial Oops/Oops/Controller.cls#handling_request
	*/
	function Request($key,$type=null,$default=null) {
		if(!strlen($key)) return false;
		if(is_null($value = $this->_request->get($key))) return $default;
		if(is_null($type)) return $value;

		switch (strtolower($type)) {
			case 'bool':
			case 'boolean':
				return (bool) $value;
			case 'int':
			case 'integer':
				return (int) $value;
			case 'float':
			case 'double':
			case 'numeric':
			case 'decimal':
				return (float) $value;
			case 'array':
				require_once("Oops/Utils.php");
				Oops_Utils::ToArray($value);
				return $value;
			case 'arrayint':
				require_once("Oops/Utils.php");
				Oops_Utils::ToIntArray($value);
				return $value;
			case 'arraysql':
				require_once("Oops/Utils.php");
				Oops_Utils::ToIntArray($value);
				return $value;
			case 'sql':
				require_once("Oops/Sql.php");
				return Oops_Sql::Escape($value);
			case 'words':
				return preg_replace('/[^\s\w]/','',$value);
			case 'trimmedwords':
				return trim(preg_replace('/[^\s\w]/','',$value));
			default:
				return $value;
				
		}
	}

	/**
	* List all defined request keys
	*/
	function getRequestKeys() {
		return $this->_request->getKeys();
	}

	/**
	* @todo Move controller params and controller_ident to the request object (fill 'em woth router)
	*/
	function getControllerParams() {
		if(is_object($this->_server)) return $this->_server->get('controller_params');
		return array();
	}

	/**
	* @return boolean True on successful request handling
	*/
	function Run() {
		return true;
	}
}
