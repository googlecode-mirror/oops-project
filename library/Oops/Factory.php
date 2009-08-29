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
	* @deprecated Use Oops_User_Helper::getUser($userId)
	*/
	public static function getUser($us_id=false) {
		require_once("Oops/User/Helper.php");
		return Oops_User_Helper::getUser($us_id);
	}

	/**
	* @deprecated Use Oops_Session::init()
	*/
	public static function initSession() {
		require_once("Oops/Session.php");
		Oops_Session::init();
	}
}
