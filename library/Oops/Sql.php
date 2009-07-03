<?
/**
* @package Oops
* @subpackage Sql
*/

if(!defined('OOPS_Loaded')) die("OOPS not found");

/**
* MySQL connection and query functionality
*/
class Oops_Sql {

	private static $_config;
	private static $_initComplete = false;

	private static function _Init() {
		if(self::$_initComplete) return;
		self::$_initComplete = true;
		$cfg =& Oops_Server::getConfig();
		self::$_config = $cfg->MySQL;
	}

	function Error($message) {
		trigger_error("Mysql/$message/(".mysql_errno().") ".mysql_error(), E_USER_ERROR);
		die();
	}

	function Connect() {
		self::_Init();

		static $dbh;

		if (!$dbh) {
			$dbh = mysql_connect(self::$_config->host, self::$_config->user, self::$_config->password);

			if(!$dbh)
				Oops_Sql::Error("mysql_connect");

			$database = @self::$_config->database;
			if(strlen($database)) {
				$result = mysql_select_db($database);
				if (!$result)
					Oops_Sql::Error("mysql_select_db/".$database);
			}

			$names = @self::$_config->names;
			if(strlen($names)) mysql_query("SET NAMES ".$names);
		}
	}

	/**
	* @todo Use event dispatcher for logger run , add listener for @onBeforeSqlQuery inside the init function
	*/
	function Query($query,$dieOnError = false) {
		Oops_Sql::Connect();

		if(@self::$_config->logger->enabled) {
			require_once('Oops/Sql/Logger.php');
			static $l;
			if(!isset($l)) $l =& Oops_Sql_Logger::getInstance(self::$_config->logger->table);

			if(self::$_config->logger->probability > mt_rand(0,1)) {
				return $l->Analyze($query);
			}
		}

		$result = mysql_query($query);

		if(mysql_errno()) {
			$errCode = mysql_errno();
			$errStr = mysql_error();
			trigger_error("MySQL/QueryError/$errCode - $errStr",E_USER_ERROR);
			if($dieOnError) die();
			return false;
		} 
		return $result;
	}

	function Escape($s) {
		Oops_Sql::Connect();
		return mysql_real_escape_string($s);
	}

}
