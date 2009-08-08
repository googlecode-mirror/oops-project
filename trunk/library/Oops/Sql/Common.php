<?php

require_once ("Oops/Sql.php");

class Oops_Sql_Common {

	/**
	 * Inserts data row into the table
	 * 
	 * @param string $table Table
	 * @param array $data Row fields and values ('field' => 'value')
	 * @return number of affected rows
	 * 
	 * @throws Oops_Sql_Exception
	 */
	public static function insert($table, $data) {
		if(!strlen($table)) {
			require_once ("Oops/Sql/Exception.php");
			throw new Oops_Sql_Exception("Invalid table name");
		}
		if(!is_array($data)) {
			require_once ("Oops/Sql/Exception.php");
			throw new Oops_Sql_Exception("Invalid row data");
		}
		
		$keys = array();
		$values = array();
		
		foreach($data as $k => $v) {
			$k = trim($k, "`");
			$keys[] = "`$k`";
			$values[] = "'" . Oops_Sql::Escape((string) $v) . "'";
		}
		
		$query = "INSERT INTO $table (" . join(', ', $keys) . ") VALUES (" . join(', ', $values) . ")";
		Oops_Sql::Query($query);
		return mysql_affected_rows();
	}

	/**
	 * Replaces data row into the table
	 * 
	 * @param string $table Table
	 * @param array $data Row fields and values ('field' => 'value')
	 * @return number of affected rows
	 */
	public static function replace($table, $data) {
		if(!strlen($table)) {
			require_once ("Oops/Sql/Exception.php");
			throw new Oops_Sql_Exception("Invalid table name");
		}
		if(!is_array($data)) {
			require_once ("Oops/Sql/Exception.php");
			throw new Oops_Sql_Exception("Invalid row data");
		}
		
		$keys = array();
		$values = array();
		
		foreach($data as $k => $v) {
			$k = trim($k, "`");
			$keys[] = "`$k`";
			$values[] = "'" . Oops_Sql::Escape((string) $v) . "'";
		}
		
		$query = "REPLACE INTO $table (" . join(', ', $keys) . ") VALUES (" . join(', ', $values) . ")";
		Oops_Sql::Query($query);
		return mysql_affected_rows();
	}

	/**
	 * Updates table row with values from $data array
	 * 
	 * @param string $table Database table
	 * @param array $data New row values
	 * @param string|array Match definitions as array of field=>value, or string field from $data
	 * @return int number of affected rows
	 */
	public static function update($table, $data, $match) {
		if(!strlen($table)) {
			require_once ("Oops/Sql/Exception.php");
			throw new Oops_Sql_Exception("Invalid table name");
		}
		if(!is_array($data)) {
			require_once ("Oops/Sql/Exception.php");
			throw new Oops_Sql_Exception("Invalid row data");
		}
		
		if(is_string($match)) {
			if(!isset($data[$match])) {
				require_once("Oops/Sql/Exception.php");
				throw new Oops_Sql_Exception("Invalid match conditions");
			}
			$where = "`$match` = '".Oops_Sql::Escape($data[$match])."'";
			unset($data[$match]);
			
		} elseif(is_array($match)) {
			$wheres = array();
			foreach($match as $k=>$v) {
				$wheres[] = "`$k` = '".Oops_Sql::Escape($v)."'";
			}
			$where = join(' AND ', $wheres);
		}
		
		if(!count($data)) {
			/**
			 * @quiz Do we need to throw exception if data aray is empty?
			 */
			return 0;
		}
		
		$sets = array();
		foreach($data as $k=>$v) {
			$sets[] = "`$k` = '".Oops_Sql::Escape($v)."'"; 
		}
		$query = "UPDATE `$table` SET " . join(', ', $sets) . " WHERE $where";
		Oops_Sql::Query($query);
		
		return mysql_affected_rows();
	}

	/**
	 * Deletes row or rows from table matching given values
	 * 
	 * @param string $table Table name
	 * @param array $match Rows match criterias as 'field'=>'value'
	 * @return int Number of affected rows
	 */
	public static function delete($table, $match) {
		if(!strlen($table)) {
			require_once ("Oops/Sql/Exception.php");
			throw new Oops_Sql_Exception("Invalid table name");
		}
		if(!is_array($match)) {
			require_once ("Oops/Sql/Exception.php");
			throw new Oops_Sql_Exception("Invalid match condition");
		}
		
		$wheres = array();
		foreach($match as $k=>$v) {
			$wheres[] = "`$k` = '".Oops_Sql::Escape($v)."'";
		}
		$where = join(' AND ', $wheres);
		
		$query = "DELETE FROM `$table` WHERE $where";
		Oops_Sql::Query($query);
		return mysql_affected_rows();
	}

}