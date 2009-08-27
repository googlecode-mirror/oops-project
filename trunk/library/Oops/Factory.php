<?php
/**
* @package Oops
* @deprecated
*/

/**
* Oops factory
*/
class Oops_Factory {
	/**
	*
	*/
	public static function &getUser($us_id=false) {
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
	public static function initSession() {
		require_once("Oops/Session.php");
		Oops_Session::init();
	}
}
