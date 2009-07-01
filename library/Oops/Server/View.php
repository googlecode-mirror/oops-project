<?
/**
* @package Oops
* @subpackage Server
*/

/**
* @abstract
* Server output presentation class
*/
class Oops_Server_View {
	/**
	* @ignore
	*/
	var $_in;
	/**
	* @ignore
	*/
	var $_out;
	/**
	* @ignore
	*/
	var $_params=array();

	/**
	* Set input value
	*
	* @param mixed Input value
	*/
	function In(&$var) {
		$this->_in =& $var;
	}

	/**
	* Set output params
	*
	* @param string Param key
	* @param mixed Value
	*/
	function Set($k,$v) {
		$this->_params[$k] = $v;
	}

	/**
	* Run the view processing
	*
	* @return mixed Output
	*/
	function Out() {
		return $this->_out;
	}

	/**
	* !!! no singleton implemented here, just don't know yet if it's required
	*
	* @param string View type (basically request extension)
	* @return Oops_Server_View
	*/
	public static function &getInstance($type) {
		if(($class = Oops_Server_View::_getViewClass($type))!==false) {
			$ret = new $class;
		}
		else {
			return false;
		}
		return $ret;
	}

	public static function _getViewClass($type) {
		static $checked = array();
		if(!isset($checked[$type])) {
			$class = "Oops_Server_View_".ucfirst($type);
			require_once("Oops/Loader.php");
			if(Oops_Loader::find($class)) $checked[$type] = $class;
			else $checked[$type] = false;
		}
		return $checked[$type];
	}

	public static function isValidView($type) {
		return (Oops_Server_View::_getViewClass($type)!==false)?true:false;
	}


}
