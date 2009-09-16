<?php

require_once 'Oops/Config.php';
require_once 'Oops/Sql.php';

class Oops_Config_Db extends Oops_Config {

	/**
	 * Reads config values from DB and constructs Config object
	 * 
	 * @param string $table
	 * @param string|array $keyFields Field name(s) to use as config key. If not given table's primary key will be used
	 * @param string|array $valueFields Field name(s) to use as config value. If not given all fields excluding keys will be used
	 * @param string $keyDelimiter Explode keys by this delimiter and group values for each exploded part  
	 */
	public function __construct($table, $keyFields = null, $valueFields = null, $keyDelimiter = '.') {
		require_once 'Oops/Sql/Common.php';
		$table = Oops_Sql_Common::escapeIdentifiers($table);
		
		if(is_null($keyFields)) {
			$keyFields = array();
			$r = Oops_Sql::Query("SHOW COLUMNS FROM $table", OOPS_SQL_EXCEPTION);
			while(($row = mysql_fetch_row($r)) !== false) {
				if(strtoupper($row[3]) == 'PRI') $keyFields[] = $row[0];
			}
			
		} else {
			require_once 'Oops/Utils.php';
			Oops_Utils::ToArray($keyFields);
		}
		
		if(!count($keyFields)) throw new Exception("No key fields for config");
		
		if(is_null($valueFields)) {
			$sql = "SELECT * FROM $table";
		} else {
			require_once 'Oops/Utils.php';
			Oops_Utils::ToArray($valueFields);
			$select = array_merge($keyFields, $valueFields);
			foreach($select as $k => $v)
				$select[$k] = Oops_Sql_Common::escapeIdentifiers($v);
			$sql = 'SELECT ' . join(',', $select) . " FROM $table";
		}
		
		$r = Oops_Sql::Query($sql);
		$data = array();
		while(($row = mysql_fetch_assoc($r)) !== false) {
			$keyParts = array();
			foreach($keyFields as $keyField) {
				$keyParts[] = $row[$keyField];
				unset($row[$keyField]);
			}
			if(count($row) == 1) $row = array_pop($row);
			$data[join($keyDelimiter, $keyParts)] = $row;
		}
		parent::__construct($data, $keyDelimiter);
	}
}