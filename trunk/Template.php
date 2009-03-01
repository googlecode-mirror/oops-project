<?
/**
* @package Oops
*/

if(!defined('OOPS_Loaded')) die("OOPS not found");

/**
* Class for evaluating templates
*/
class Oops_Template extends Oops_Object {
	/**
	* State of the template
	*
	* @access private
	*/
	var $_valid = false;

	/**
	* Tells whenever object is a valid template (template file exists)
	*
	* @return bool TRUE if valid
	*/
	function isValid() {
		return $this->_valid;
	}

	/**
	* @param string template name
	* @access private
	*/
	function __construct($tplname) {
		$this->_tplname = $tplname;
		require_once("Oops/Template/Helper.php");
		if(($this->_tplfile = Oops_Template_Helper::getTemplateFilename($tplname))!==false) $this->_valid = true;
	}

	/**
	* @param mixed Template data, will be accessable as $this->Data
	*/
	function out(&$var) {
		if(!$this->_valid) return;
		$this->Data =& $var;
		ob_start();
		include($this->_tplfile);
		return ob_get_clean();
		
		
	}

	/**
	* Singleton pattern implementaion
	*
	* @param string template name
	*/
	function &getInstance($tplname) {
		$tplname = strtolower($tplname);
		static $a=array();
		if(!isset($a[$tplname])) $a[$tplname] = new Oops_Template($tplname);
		return $a[$tplname];
	}

	/**
	* Call another template
	*
	* @access private
	*/
	function call($tplname,$data = null) {
		$template =& Oops_Template::getInstance($tplname);
		if($template->isValid()) {
			if(is_null($data)) $data =& $this->Data;
			return $template->out($data);
		}
	}

	function store($key,$value = null) {
		static $store = array();
		if(is_null($value)) return isset($store[$key])?$store[$key]:null;
		else $store[$key] = $value;
	}
}
