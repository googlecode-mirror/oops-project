<?php

/**
 * @package Oops
 * @subpackage Sql
 */

define("OOPS_SQL_EXCEPTION", 2);

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

	protected static function _initError($message) {
		error_log("Mysql connection error (" . mysql_errno() . ": " . mysql_error());
		throw new Oops_Sql_Exception("Mysql connection error (" . mysql_errno() . ": " . mysql_error());
	}

	protected static function Error($dieOnError = false, $query = '') {
		$errCode = mysql_errno(self::$_link);
		$errStr = mysql_error(self::$_link);
		// @todo Refactor, now we can't see mysql errors in response, maybe we should use exceptions
		if($dieOnError == OOPS_SQL_EXCEPTION) {
			throw new Oops_Sql_Exception($errStr, $errCode, $query);
		}
		trigger_error("MySQL/QueryError/$errCode - $errStr/$query", E_USER_ERROR);
		error_log('MySQL Error #' . $errCode . ': ' . $query);
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
			$cfg = Oops_Server::getConfig()->MySQL;
			
			self::$_link = mysql_connect($cfg->host, $cfg->user, $cfg->password);
			
			if(!is_resource(self::$_link)) Oops_Sql::_initError("mysql_connect");
			
			$database = $cfg->database;
			if(strlen($database)) {
				$result = mysql_select_db($database, self::$_link);
				if(!$result) Oops_Sql::_initError("mysql_select_db/" . $database);
			}
			
			$names = $cfg->names;
			if(strlen($cfg->names)) mysql_query("SET NAMES " . $names, self::$_link);
		}
		return self::$_link;
	}

	/**
	 *
	 */
	public static function Query($query, $dieOnError = false) {
		if(!isset(self::$_link)) Oops_Sql::Connect();
		$result = mysql_query($query, self::$_link);
		if(mysql_errno(self::$_link)) return self::Error($dieOnError, $query);
		return $result;
	}

	public static function Escape($s) {
		if(!isset(self::$_link)) Oops_Sql::Connect();
		return mysql_real_escape_string($s, self::$_link);
	}

	public static function EscapeIt(&$s) {
		$s = self::Escape($s);
	}

	public static function getConfig() {
		return Oops_Server::getConfig()->MySQL;
	}

	public static function lastInsertId() {
		if(!isset(self::$_link)) return null;
		return mysql_insert_id(self::$_link);
	}
}
