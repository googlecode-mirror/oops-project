<?php
/**
* @package Oops
* @subpackage Server
*/

/**
* Server URI router
*/
class Oops_Server_Router {
	/**
	* Routing settings, array with URI parts as a key, controller class as a value 
	*
	* @access private
	*/
	private $_set=array();

	/*
	 * @var string Not routed part of a given path
	 */
	public $notFoundPath;

	/*
	 * @var string Routed part of a given path
	 */
	public $foundPath;

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
	function route($request) {
		//Obtaining path as string and split into parts
		$parts = explode('/', trim($request->getResourcePath(), '/'));
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
		$this->notFoundPath = substr($path,strlen($foundPath)+1);
		return true;
	}
	
	/**
	 * Finds first path defined for a given controller class
	 * 
	 * @param $controllerClass Controller class
	 * @return string path without leading and trailing '/'
	 */
	public function backRoute($controllerClass) {
		return array_search($controllerClass, $this->_set);
	}
	
	/**
	 * Finds all paths defined for a given controller class
	 * 
	 * @param $controllerClass Controller class
	 * @return string array of paths without leading and trailing '/'
	 */
	public function backRouteAll($controllerClass) {
		return array_keys($this->_set, $controllerClass);
	}
}
