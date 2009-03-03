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
	var $_application;

	/**
	*
	*/
	function __construct() {
		$this->_application =& Oops_Application::getInstance();
	}

	/**
	* Возвращение значение из запроса по заданному ключу, преобразованное к затребованному типу
	*
	* @param string Ключ в запросе
	* @param string Требуемый тип значения
	* @return mixed 
	* @tutorial Oops/Oops/Controller.cls#handling_request
	*/
	function Request($key,$type=null,$default=null) {
		static $filesDone = false;
		if(!$filesDone) $this->_proceedRequestFiles();
		if(!strlen($key)) return false;
		if(!isset($_REQUEST[$key])) return $default;
		$value = $_REQUEST[$key];
		if(is_null($type)) return $_REQUEST[$key];
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
	* Возвращает все ключи запроса
	*/
	function getRequestKeys() {
		if(!isset($_REQUEST)) return array();
		return array_keys($_REQUEST);
	}

	function getData() {return $this->Data;}
	function getTemplate() {return $this->Template;}

	/**
	*
	*/
	function getControllerParams() {
		if(is_object($this->_application)) return $this->_application->get('controller_params');
		return array();
	}

	function Run() {
		return true;
	}
}
