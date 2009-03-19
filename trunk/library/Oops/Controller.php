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
		static $filesDone = false;
		if(!$filesDone) $this->_proceedRequestFiles();
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
	* @todo move this to Oops_Server_Request_Http::__construct
	*/
	function _proceedRequestFiles($files = null,$keys=array()) {
		if(is_null($files)) $files = $_FILES;
		foreach($files as $k=>$v) {
			$keys[]=$k;
			if(!is_array($v['name'])) {
				//add to Request
				$reqRef =& $_REQUEST;
				for($i=0;$i<sizeof($keys);$i++) {
					$reqRef =& $reqRef[$keys[$i]];
				}
				if(is_array($reqRef)) $reqRef = array_merge($reqRef,$v);
				else $reqRef = $v;
			} else {
				$subfiles = array();
				foreach(array_keys($v['name']) as $rk) {
					$subfiles[$rk] = array(
						'name' => $files[$k]['name'][$rk],
						'type' => $files[$k]['type'][$rk],
						'tmp_name' => $files[$k]['tmp_name'][$rk],
						'error' => $files[$k]['error'][$rk],
						'size' => $files[$k]['size'][$rk],
					);
					$this->_proceedRequestFiles($subfiles,$keys);
				}
			}
			array_pop($keys);
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
