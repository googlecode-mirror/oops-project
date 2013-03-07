<?php

/**
 * @package Oops
 * @author Dmitry Ivanov
 * @license CNUv3
 */

/**
 * Session factory.
 * Session handler and params are defined by server config.
 */
class Oops_Session {
	/**
	 *
	 * @var Session handler object
	 */
	public static $session;

	/**
	 * Init session handler
	 *
	 * @return unknown_type
	 */
	public static function init() {
		static $initComplete = false;
		if($initComplete) return;
		$initComplete = true;
		self::_initHandler();
		session_start();
	}

	private static function _initHandler() {
		// @todo merge given config to the default one to avoid missing fields
		$sessCfg = Oops_Server::getConfig()->session;
		$handlerClass = 'Oops_Session_' . $sessCfg->handler;
		if(!class_exists($handlerClass)) $handlerClass = 'Oops_Session_Native';
		self::$session = new Oops_Session_Native($sessCfg);
	}
}