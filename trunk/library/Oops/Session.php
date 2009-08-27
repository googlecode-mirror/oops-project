<?php
/**
 * @package Oops
 * @author Dmitry Ivanov
 * @license CNUv3
 */

/**
 * 
 * Session factory. Session handler and params are defined by server config.
 *
 */
class Oops_Session {
	/**
	 * @var Session handler object
	 */
	public static $session;
	
	/**
	 * Init session handler
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
		require_once("Oops/Server.php");
		$cfg =& Oops_Server::getConfig();
		$sessCfg = @$cfg->session;
		if(is_object($sessCfg)) {
			$handlerClass = 'Oops_Session_' . $sessCfg->handler;
			if(Oops_Loader::find($handlerClass)) {
				self::$session = new $handlerClass($sessCfg);
				return;
			}
			require_once("Oops/Session/Native.php");
			self::$session = new Oops_Session_Native($sessCfg);
			return;
		}
		require_once("Oops/Session/Native.php");
		self::$session = new Oops_Session_Native($sessCfg); 
	}
}