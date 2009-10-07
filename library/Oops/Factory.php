<?php

class Oops_Factory {

	/**
	 * @deprecated Use Oops_User_Helper::getUser($userId)
	 */
	public static function getUser($us_id = false) {
		require_once ("Oops/User/Helper.php");
		return Oops_User_Helper::getUser($us_id);
	}

	/**
	 * @deprecated Use Oops_Session::init()
	 */
	public static function initSession() {
		require_once ("Oops/Session.php");
		Oops_Session::init();
	}

	/**
	 * Instantiates objects by class and id, respecting pattern implemented by given class
	 * 
	 * @param string $class Class name
	 * @param $id
	 * @return unknown_type
	 */
	public static function instantiate($class, $id = null) {
		if(!strlen($class)) {
			require_once 'Oops/Exception.php';
			throw new Oops_Exception("Empty class name given");
		}
		if(!Oops_Loader::find($class)) {
			require_once 'Oops/Exception.php';
			throw new Oops_Exception("Class '$class' not found");
		}
		
		$reflectionClass = new ReflectionClass($class);
		
		if($reflectionClass->implementsInterface('Oops_Pattern_Identifiable_Factored_Interface')) {
			/**
			 * Object can be instantiated using corresponding factory
			 */
			$factoryCallback = call_user_func($class, 'getFactoryCallback');
			$result = call_user_func($factoryCallback, $id);
		} elseif($reflectionClass->implementsInterface('Oops_Pattern_Identifiable_Singleton_Interface')) {
			/**
			 * This object can be instantiated using $class::getInstance($id)
			 */
			$result = call_user_func(array($class, 'getInstance'), $id);
		} elseif($reflectionClass->implementsInterface('Oops_Pattern_Singleton_Interface')) {
			/**
			 * This object is the single available instance of this class, so it can be instantiated using $class::getInstance()
			 */
			$result = call_user_func(array($class, 'getInstance'));
		} else {
			/**
			 * This type of object should be constructed with given $id
			 */
			$result = $reflectionClass->newInstance($id);
		}
		return $result;
	}
}
