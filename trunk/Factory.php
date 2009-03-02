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
	function &getApplication() {
		static $instance;
		if(!is_object($instance)) {
			require_once("Oops/Application.php");
			$instance = new Oops_Application();
		}
		return $instance;
	}

	/**
	* @return Oops_Application_Map an application URI mapper object
	*/
	function &getApplicationMap($class = null, $source = null) {
		static $instance;
		if(!is_object($instance)) {
			require_once("Oops/Loader.php");
			if(!is_null($class) && Oops_Loader::find($class)) $instance = new $class($source);
			else {
				require_once("Oops/Application/Map.php");
				$instance = new Oops_Application_Map();
			}
		}
		return $instance;
	}

	/**
	*
	*/
	function &getUser($us_id=false) {
		require_once("Oops/User.php");
		require_once("Oops/User/Helper.php");
		if($us_id===false) $us_id = Oops_User_Helper::GetID();
		if(!$us_id) {
			require_once("Oops/User/Guest.php");
			return Oops_User_Guest::getInstance();
		}
		return Oops_User::getInstance($us_id);
	}

	/**
	* 
	*/
	function initSession() {
		require_once("Oops/Session.php");
		Oops_Session::getInstance();
	}

	function &getRequest($url = false) {
		require_once("Oops/Server/Request/Stack.php");
		$stack =& Oops_Server_Request_Stack::getInstance();
		return $stack->getRequest($url);
	}
}
?>