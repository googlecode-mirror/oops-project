<?
/**
* @package Oops
*/

if(!defined('OOPS_Loaded')) die("OOPS not found");

/**
* Oops factory
*/
class Oops_Factory {
	/**
	* @param string application name
	* @return Oops_Application an application object
	*/
	function &getApplication($name='site') {
		static $instance;
		if(!is_object($instance)) {
			__autoload("Oops_Application");
			$instance = new Oops_Application($name);
		}
		return $instance;
	}

	/**
	* @return Oops_Application_Map an application URI mapper object
	*/
	function &getApplicationMap() {
		static $instance;
		if(!is_object($instance)) {
			__autoload("Oops_Application_Map");
			$instance = new Oops_Application_Map();
		}
		return $instance;
	}

	/**
	*
	*/
	function &getUser($us_id=false) {
		__autoload("Oops_User");
		__autoload("Oops_User_Helper");
		if($us_id===false) $us_id = Oops_User_Helper::GetID();
		if(!$us_id) {
			__autoload("Oops_User_Guest");
			return Oops_User_Guest::getInstance();
		}
		return Oops_User::getInstance($us_id);
	}

	/**
	* 
	*/
	function initSession() {
		__autoload("Oops_Session");
		Oops_Session::getInstance();
	}

	function &getRequest($url = false) {
		__autoload("Oops_Server_Request_Stack");
		$stack =& Oops_Server_Request_Stack::getInstance();
		return $stack->getRequest($url);
	}
}
?>