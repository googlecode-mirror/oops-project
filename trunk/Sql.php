<?
/**
* @package Oops
*/

if(!defined('OOPS_Loaded')) die("OOPS not found");

/**
* MySQL connection and query functionality
*/
class Oops_Sql {

	function Error($message) {
		require_once("Oops/Debug.php");
		Oops_Debug::Dump("Mysql error (".mysql_errno().") ".mysql_error(),$message,true);
		die();
	}

	function Connect() {
		static $dbh;

		if (!$dbh) {
			$dbh = mysql_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWD);

			if (!$dbh)
				Oops_Sql::Error("mysql_connect");

			if(defined("DATABASE_NAME")) {
				$result = mysql_select_db(DATABASE_NAME);
				if (!$result)
					Oops_Sql::Error("mysql_select_db: ".DATABASE_NAME);
			}
			if(defined("DATABASE_NAMES")) mysql_query("SET NAMES ".DATABASE_NAMES);
		}
	}

	function Query($query,$skiperrors=false) {
		Oops_Sql::Connect();
		if(false && !$skiperrors) {
			require_once('Oops/Sql/Logger.php');
			$l =& Oops_Sql_Logger::getInstance();
			return $l->Analyze($query);
		}

		$result = mysql_query($query);

		if(!$skiperrors && mysql_errno()) {
			Oops_Sql::Error($query);
			return false;
		}
		return $result;
	}

	function Escape($s) {
		Oops_Sql::Connect();
		return mysql_real_escape_string($s);
	}

}
?>