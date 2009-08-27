<?php
/**
* @package Oops
*/

require_once("Oops/Session/Abstract.php");
require_once("Oops/Session/Interface.php");

class Oops_Session_Database extends Oops_Session_Abstract implements Oops_Session_Interface {

	/**
	 * @var string Initial session data being read as session starts
	 */
	private $_savedData='';
	
	private $_tableSessions = 'sessions';
	
	public function _read($ses_id) {
		$ses_id=preg_replace("/\W+$/","",$ses_id);
		if(!strlen($ses_id)) return;
		$result = Oops_Sql::Query("SELECT ses_value FROM {$this->_tableSessions} WHERE ses_id = '$ses_id'", true);
		list($ses_data) = mysql_fetch_row($result);
		$this->_savedData=$ses_data;
		return $ses_data;
	}
  
	public function _write($ses_id, $data) {
		if($data == $this->_savedData) return;
		$ses_id = preg_replace("/\W+$/","",$ses_id);
		if (!strlen($ses_id)) return;

		if(Oops_Loader::load("Oops_User_Helper")) {
			$user_id = Oops_User_Helper::GetID();
		} else $user_id = 0;

		$ses_time = date("Ymd");
		//Oops_Sql::Query("SET SQL_LOG_BIN=0");
		if(strlen($data)) {
			$data = Oops_Sql::Escape($data);
			$query = "INSERT INTO {$this->_tableSessions} (ses_id, ses_time, ses_start, ses_value, user_id)
				VALUES ('$ses_id', '$ses_time', '$ses_time', '$data', '$user_id')
				ON DUPLICATE KEY UPDATE ses_time = '$ses_time', ses_value='$data', user_id = '$user_id'";
			Oops_Sql::Query($query);
			debugPrint(mysql_error(), 'mysql_error');
		} else {
			Oops_Sql::Query("DELETE FROM {$this->_tableSessions} WHERE ses_id='$ses_id'");
		}
		//Oops_Sql::Query("SET SQL_LOG_BIN=1");
		return true;
	}
  
	public function _destroy($ses_id) {
		$ses_id=preg_replace("/\W+$/","",$ses_id);
		Oops_Sql::Query("DELETE FROM {$this->_tableSessions} WHERE ses_id = '$ses_id'");
		return true;
	}

	/**
	 * 
	 * @param $life
	 * @return unknown_type
	 * 
	 * @todo Check config, etc.
	 */
	public function _gc($life) {
	}

        
	// var $dbName = OOPS_SESSIONS_DB;

	/**
	 * @param Oops_Config Session configuration
	 * @return unknown_type
	 */
	public function __construct($config) {
		parent::__construct($config);

		if($config->table) $this->_tableSessions = $config->table;
		if($config->database) $this->_tableSessions = $config->database . '.' . $this->_tableSessions;
		$this->setHandler();
	}

}