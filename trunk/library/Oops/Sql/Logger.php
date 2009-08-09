<?
/**
 * @package Oops
 * @subpackage Sql
 */

/**
 * Logging mysql querries for debug purposes
 */
class Oops_Sql_Logger {
	private static $_instance;
	
	private $_table;
	
	var $temporary = true;
	var $filesort = true;
	var $all = true;
	var $maxrows = 2500;
	var $trace = true;
	var $maxtime = 0.1;
	var $preg = false;
	
	var $registerKeys = false;
	
	var $_trace = false;
	var $log = array();

	function Analyze($query) {
		static $connected = false;
		if(!$connected) {
			$connected = true;
			$mysqlLink = Oops_Sql::Connect();
		}
		$this->_trace = false;
		$this->_worktime = 0;
		list($t, $m) = explode(' ', microtime());
		$start = (double) $t + $m;
		
		$r = Oops_Sql::Query($query);
		if(mysql_errno($mysqlLink)) {
			$this->_Log($query, 'mysqlerror');
			return;
		}
		
		list($t, $m) = explode(' ', microtime());
		$end = (double) $t + $m;
		
		$this->_worktime = $end - $start;
		
		if($this->maxtime && $this->_worktime > $this->maxtime) $this->_Log($query, 'querytime');
		if($this->preg && preg_match($this->preg, $query)) $this->_Log($query, 'preg');
		if(!preg_match('/\s*select\s+/i', $query)) return $r;
		
		if($this->temporary || $this->filesort || $this->all || $this->maxrows || $this->registerKeys) {
			$tableKeys = array();
			$rex = Oops_Sql::Query("Explain $query");
			if(mysql_errno($mysqlLink)) return $r;
			
			$reasons = array();
			while(($row = mysql_fetch_assoc($rex)) !== false) {
				if($this->temporary && strpos($row['Extra'], 'temporary') !== false) {
					$reasons[] = 'temporary';
				}
				if($this->filesort && strpos($row['Extra'], 'filesort') !== false) {
					$reasons[] = 'filesort';
				}
				if($this->all && strtoupper($row['type']) == 'ALL') {
					$reasons[] = 'ALL';
				}
				if($this->maxrows && $row['rows'] > $this->maxrows) {
					$reasons[] = 'manyrows';
				}
				if($this->registerKeys && $row['table'] && substr($row['table'], 0, 6) != '<union') {
					$tableid = $row['table'];
					if(preg_match("/FROM\s.*([\w\`\.]+)\s+AS\s+$tableid\b/siU", $query, $match) || preg_match("/FROM\s.*([\w\`]+\.\`?$tableid\b\`?)/siU", $query, $match)) {
						$tbl = str_replace('`', '', $match[1]);
					} else {
						$tbl = DATABASE_NAME . '.' . $tableid;
					}
					if(!strpos($tbl, '.', 1)) $tbl = DATABASE_NAME . '.' . $tbl;
					$tableKeys[$tbl] = $row['key'];
					if(!strlen($row['key'])) {
						if(!strlen($row['possible_keys']))
							$reasons[] = 'nopossiblekeys';
						else
							$reasons[] = 'nokeys';
					}
				}
			}
			if(sizeof($reasons)) {
				$reasons = array_unique($reasons);
				for($i = 0, $c = sizeof($reasons); $i < $c; $i++) {
					$this->_Log($query, $reasons[$i]);
				}
			}
			if($this->registerKeys) {
				foreach($tableKeys as $t => $k) {
					mysql_query("INSERT IGNORE INTO moscow_service.querriesLogKeys (tbl,k) values ('$t','$k')");
				}
			}
		
		}
		return $r;
	}

	function _Log(&$query, $reason) {
		if(!$this->_trace) {
			$data = debug_backtrace();
			$count = sizeof($data);
			$trace = '';
			for($i = 2; $i < $count; $i++) {
				$obj = $data[$i];
				$trace .= $obj['class'] . '->' . $obj['function'] . '::' . $obj['line'] . ' (' . $obj['file'] . ')' . "\n";
			}
			$this->_trace = $trace;
		}
		$this->log[] = array(
							'query' => $query, 
							'reason' => $reason, 
							'worktime' => $this->_worktime, 
							'trace' => $this->_trace );
	}

	/**
	 * Singleton pattern implementation
	 * 
	 * @param string $table Log table
	 * @return Oops_Sql_Logger
	 */
	function &getInstance($table) {
		if(!is_object(self::$_instance)) self::$_instance = new self($table);
		return self::$_instance;
	}

	function __construct($table) {
		$this->_table = $table;
	}

	function getLog() {
		$l = & Oops_Sql_Logger::getInstance();
		return $l->log;
	}
}
