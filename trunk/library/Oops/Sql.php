<?php

/**
 * @package Oops
 * @subpackage Sql
 */

/**
 * MySQL connection and query functionality
 */
class Oops_Sql {
	
	/**
	 * MySQL connection resource
	 * @var resource
	 */
	protected static $_link;
	
	/**
	 * MySQL configuration
	 * 
	 * @var Oops_Config
	 */
	protected static $_config;
	
	/**
	 * True if configuration initialization complete
	 * 
	 * @var bool
	 */
	protected static $_initComplete = false;

	/**
	 * Get MySQL connection resource
	 * 
	 * @return resource
	 */
	public static function getLink() {
		return self::$_link;
	}

	protected static function _Init() {
		if(self::$_initComplete) return;
		self::$_initComplete = true;
		$cfg = & Oops_Server::getConfig();
		self::$_config = $cfg->MySQL;
	}

	protected static function _initError($message) {
		trigger_error("Mysql/$message/(" . mysql_errno() . ") " . mysql_error(), E_USER_ERROR);
		die();
	}

	protected static function Error($dieOnError = false) {
		$errCode = mysql_errno(self::$_link);
		$errStr = mysql_error(self::$_link);
		// @todo Refactor, now we can't see mysql errors in response, maybe we should use exceptions
		trigger_error("MySQL/QueryError/$errCode - $errStr", E_USER_ERROR);
		if($dieOnError) die();
		return false;
	}

	/**
	 * Connects to mysql server using server config and returns link identifier.
	 * If connection already established returns link identifier
	 * 
	 * @return resource MySQL link
	 */
	public static function Connect() {
		self::_Init();
		
		if(!is_resource(self::$_link)) {
			self::$_link = mysql_connect(self::$_config->host, self::$_config->user, self::$_config->password);
			
			if(!is_resource(self::$_link)) Oops_Sql::_initError("mysql_connect");
			
			$database = @self::$_config->database;
			if(strlen($database)) {
				$result = mysql_select_db($database, self::$_link);
				if(!$result) Oops_Sql::_initError("mysql_select_db/" . $database);
			}
			
			$names = @self::$_config->names;
			if(strlen($names)) mysql_query("SET NAMES " . $names, self::$_link);
		}
		return self::$_link;
	}

	/**
	 * 
	 */
	public static function Query($query, $dieOnError = false) {
		// @todo Consider using event dispatcher to run logger, adding listener for onBeforeSqlQuery inside the _init function
		Oops_Sql::Connect();
		
		static $loggerEnabled = null;
		if(!isset($loggerEnabled)) {
			if(is_object(self::$_config->logger))
				$loggerEnabled = self::$_config->logger->enabled;
			else
				$loggerEnabled = false;
		}
		
		if($loggerEnabled) {
			require_once ('Oops/Sql/Logger.php');
			static $l = null;
			if(!is_object($l)) $l = & Oops_Sql_Logger::getInstance(self::$_config->logger->table);
			
			if(self::$_config->logger->probability > mt_rand(0, 1)) {
				return $l->Analyze($query);
			}
		}
		
		$result = mysql_query($query, self::$_link);
		
		if(mysql_errno(self::$_link)) return self::Error($dieOnError);
		
		return $result;
	}

	public static function Escape($s) {
		Oops_Sql::Connect();
		return mysql_real_escape_string($s, self::$_link);
	}

	public static function EscapeIt(&$s) {
		$s = self::Escape($s);
	}

}
