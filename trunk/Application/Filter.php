<?
/**
* @package Oops
* @subpackage Application
*/

/**
* @abstract
* Application output filter class
*/
class Oops_Application_Filter {
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
	* Run the filter
	*
	* @return mixed Output
	*/
	function Out() {
		return $this->_out;
	}

	/**
	* !!! no singleton implemented here, just don't know yet if it's required
	*
	* @param string Filter type
	* @return Oops_Application_Filter
	*/
	function &getInstance($type) {
		if(($class = Oops_Application_Filter::_getFilterClass($type))!==false) {
			$ret = new $class;
		}
		else {
			return false;
		}
		return $ret;
	}

	function _getFilterClass($type) {
		static $checked = array();
		if(!isset($checked[$type])) {
			$class = "Oops_Application_Filter_".ucfirst($type);
			if(Oops_Loader::find($class)) $checked[$type] = $class;
			else $checked[$type] = false;
		}
		return $checked[$type];
	}

	function isValidFilter($type) {
		return (Oops_Application_Filter::_getFilterClass($type)!==false)?true:false;
	}


}
?>