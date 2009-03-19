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
	* Method is used to define settings
	*
	* @param string Routed path (/news/ or news or news/). If empty sets the default controller.
	* @param string Routed controller class
	*/
	function Set($path,$ctrl) {
		$path = '/'.trim($path,'/');
			$this->_set[$path] = $ctrl;
	}

	/**
	* Routing method
	*
	* @param array Oops_Server_Request Request object
	* @return bool true if routed, false on 'not found'
	* @todo set controller and other params in a non-conflicting way
	*/
	function route(&$request) {
		//Obtaining path as string and split into parts
		$parts = explode('/', trim($request->getPath(), '/'));
		$path = '/' . join('/', $parts);

		$isSuccessful = false;

		if(isset($this->_set[$path])) {
			//Exact match, document_root requests first of all (if set the root route)
			$controller = $this->_set[$path];
			$foundPath = $path;
			$isSuccessful = true;
		} else {
			$cur = '';
			for($i = 0, $cnt = count($parts); $i < $cnt; $i++) {
				$cur .= '/' . $parts[$i];
				if(isset($this->_set[$cur])) {
					$controller = $this->_set[$cur];
					$foundPath = $cur;
					$isSuccessful = true;
				}
			}
		}
		//Can't route the request
		if(!$isSuccessful) return false;

		//Routed OK, set request params
		$this->controller = $controller;
		$this->foundPath = $foundPath;
		$this->notFoundPath = substr($path,strlen($notFoundPath));
		return true;
	}
}
