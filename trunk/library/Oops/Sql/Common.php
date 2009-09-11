<?php

require_once ("Oops/Sql.php");

class Oops_Sql_Common {

	/**
	 * Inserts data row into the table
	 * 
	 * @param string $table Table
	 * @param array $data Row fields and values ('field' => 'value')
	 * @param bool $returnQuery whenever to return query or run it. Default is false.
	 * @return number of affected rows or sql query string
	 * 
	 * @throws Oops_Sql_Exception
	 */
	public static function insert($table, $data, $returnQuery = false) {
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
			$keys[] = self::escapeIdentifiers($k);
			$values[] = self::quoteValue($v);
		}
		
		$query = "INSERT INTO ".self::escapeIdentifiers($table)." (" . join(', ', $keys) . ") VALUES (" . join(', ', $values) . ")";
		if($returnQuery) return $query;
		Oops_Sql::Query($query);
		return mysql_affected_rows();
	}

	/**
	 * Replaces data row into the table
	 * 
	 * @param string $table Table
	 * @param array $data Row fields and values ('field' => 'value')
	 * @param bool $returnQuery whenever to return query or run it. Default is false.
	 * @return number of affected rows or sql query string
	 */
	public static function replace($table, $data, $returnQuery = false) {
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
			$keys[] = self::escapeIdentifiers($k);
			$values[] = self::quoteValue($v);
		}
		
		$query = "REPLACE INTO ".self::escapeIdentifiers($table)." (" . join(', ', $keys) . ") VALUES (" . join(', ', $values) . ")";
		if($returnQuery) return $query;
		Oops_Sql::Query($query);
		return mysql_affected_rows();
	}

	/**
	 * Updates table row with values from $data array
	 * 
	 * @param string $table Database table
	 * @param array $data New row values
	 * @param string|array Match definitions as array of field=>value, or string field from $data
	 * @param bool $returnQuery whenever to return query or run it. Default is false.
	 * @return int number of affected rows or sql query string
	 */
	public static function update($table, $data, $match, $returnQuery = false) {
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
				require_once ("Oops/Sql/Exception.php");
				throw new Oops_Sql_Exception("Invalid match conditions");
			}
			$where = self::escapeIdentifiers($match)." = " . self::quoteValue($data[$match]);
			unset($data[$match]);
		
		} elseif(is_array($match)) {
			$wheres = array();
			foreach($match as $k => $v) {
				$wheres[] = self::escapeIdentifiers($k)." = " . self::quoteValue($v);
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
		foreach($data as $k => $v) {
			$sets[] = self::escapeIdentifiers($k)." = " . self::quoteValue($v);
		}
		$query = "UPDATE ".self::escapeIdentifiers($table)." SET " . join(', ', $sets) . " WHERE $where";
		if($returnQuery) return $query;
		Oops_Sql::Query($query);
		
		return mysql_affected_rows();
	}

	/**
	 * Deletes row or rows from table matching given values
	 * 
	 * @param string $table Table name
	 * @param array $match Rows match criterias as 'field'=>'value'
	 * @param bool $returnQuery whenever to return query or run it. Default is false.
	 * @return int Number of affected rows or sql query string
	 */
	public static function delete($table, $match, $returnQuery = false) {
		if(!strlen($table)) {
			require_once ("Oops/Sql/Exception.php");
			throw new Oops_Sql_Exception("Invalid table name");
		}
		if(!is_array($match)) {
			require_once ("Oops/Sql/Exception.php");
			throw new Oops_Sql_Exception("Invalid match condition");
		}
		
		$wheres = array();
		foreach($match as $k => $v) {
			$wheres[] = self::escapeIdentifiers($k)." = " . self::quoteValue($v);
		}
		$where = join(' AND ', $wheres);
		
		$query = "DELETE FROM ".self::escapeIdentifiers($table)." WHERE $where";
		if($returnQuery) return $query;
		Oops_Sql::Query($query);
		return mysql_affected_rows();
	}

	public static function escapeIdentifiers($string) {
		$string = str_replace('`', '', $string);
		$parts = explode('.', $string);
		return '`' . join('`.`', $parts) . '`';
	}
	
	public static function quoteValue($v) {
		static $userVarPatterns = array(
			'{^@[a-zA-Z_\.$]+$}',
			"{^@'[^'\s]'$}",
			"{^@`[^`\s]`$}",
			'{^@"[^"\s]"$}',
		);
		
		if(is_null($v)) return 'NULL';
		
		/**
		 * Check if value is a MySQL user variable
		 */
		foreach($userVarPatterns as $pattern) {
			if(preg_match($pattern, $v)) return $v;
		}

		// return numeric as is
		if(is_numeric($v)) return $v;
		
		// it's a string
		return "'".Oops_Sql::Escape((string) $v)."'";
	}

}