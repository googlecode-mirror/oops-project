<?php

/**
 * @package Oops
 * @subpackage Sql
 */

define("OOPS_SQL_EXCEPTION", 1);

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

	/**
	 * Sets MySQL connection for Oops. This method should be used before any query or Oops_Sql_Utils call if you handle connection by your own module  
	 */
	public static function setLink($link) {
		if(is_resource($link) && get_resource_type($link) == 'mysql link') self::$_link = $link;
	}

	/**
	 * Init Oops_Sql configuration using Oops_Server environment
	 */
	protected static function _Init() {
		if(self::$_initComplete) return;
		self::$_initComplete = true;
		self::$_config = Oops_Server::getConfig()->MySQL;
	}

	protected static function _initError($message) {
		require_once 'Oops/Sql/Exception.php';
		throw new Oops_Sql_Exception("Mysql connection error (" . mysql_errno() . ": " . mysql_error());
	}

	protected static function Error($dieOnError = false, $query = '') {
		$errCode = mysql_errno(self::$_link);
		$errStr = mysql_error(self::$_link);
		// @todo Refactor, now we can't see mysql errors in response, maybe we should use exceptions
		if($dieOnError == OOPS_SQL_EXCEPTION) {
			throw new Exception("MySQL/QueryError/$errCode - $errStr/$query");
		}
		trigger_error("MySQL/QueryError/$errCode - $errStr/$query", E_USER_ERROR);
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
		if(!is_resource(self::$_link)) {
			self::_Init();
			
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
		$result = mysql_query($query, self::$_link);
		if(mysql_errno(self::$_link)) return self::Error($dieOnError, $query);
		return $result;
	}

	public static function Escape($s) {
		Oops_Sql::Connect();
		return mysql_real_escape_string($s, self::$_link);
	}

	public static function EscapeIt(&$s) {
		$s = self::Escape($s);
	}

	public static function getConfig() {
		self::_Init();
		return self::$_config;
	}

	public static function lastInsertId() {
		return mysql_insert_id(self::$_link);
	}
}
