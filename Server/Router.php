<?
/**
* @package Oops
* @subpackage Server
*/

if(!defined("OOPS_Loaded")) die("OOPS not loaded");

require_once("Oops/Object.php");

/**
* Server URI router
*/
class Oops_Server_Router extends Oops_Object {
	/**
	* Routing settings, array with URI parts as a key, controller class as a value 
	*
	* @access private
	*/
	var $_set=array();

	/**
	* Found route level (number of matched URI parts)
	*
	* @access private
	*/
	var $_foundLevel;

	/**
	* Default controller class
	*
	* @access private
	*/
	var $_default = false;

	/**
	* Method is used to define settings
	*
	* @param string Routed path (/news/ or news or news/). If empty sets the default controller.
	* @param string Routed controller class
	*/
	function Set($path,$ctrl) {
		$path = trim($path,'/');
		if(!strlen($path)) $this->_default = $ctrl;
		else $this->_set[$path] = $ctrl;
	}

	/**
	* Routing method
	*
	* @param array URI parts splitted
	* @return string Controller class
	*/
	function getController($uri_parts) {
		if(!is_array($uri_parts)) $uri_parts = explode('/',trim($uri_parts,'/'));
		$ret = $this->_default;
		$this->_foundLevel=0;
		$cur='';
		for($i=0,$cnt = sizeof($uri_parts);$i<$cnt;$i++) {
			$cur .= $uri_parts[$i];
			if(isset($this->_set[$cur])) {
				$ret = $this->_set[$cur];
				$this->_foundLevel = $i+1;
			}
			$cur .= '/';
		}
		return $ret;
	}

	/**
	* Use it after getController to obtain routed level
	*
	* @return int
	*/
	function getFoundLevel() {
		return $this->_foundLevel;
	}
}
?>