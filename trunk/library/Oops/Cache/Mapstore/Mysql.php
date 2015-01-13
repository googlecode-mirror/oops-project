<?php

class Oops_Cache_Mapstore_Mysql extends Oops_Cache_Mapstore_Abstract {
	/**
	 *
	 * @var string
	 */
	private $_table = '';
	
	/**
	 *
	 * @var Oops_Sql_Selector
	 */
	private $_selector;

	public function __construct(Oops_Config $config) {
		if(!empty($config->dbname)) $this->_table = $config->dbname . '.';
		$this->_table .= $config->_table;
		if(!$this->_table) $this->_table = 'mapstore_default';
	}

	public function store($target, $sources) {
		try {
			foreach($sources as $s) {
				Oops_Sql_Common::insert($this->_table, array('target' => $target, 'source' => $s));
			}
		} catch(Oops_Sql_Exception $e) {
			self::__install($this->_table);
			throw $e;
		}
	}

	public function drop($target) {
		try {
			Oops_Sql_Common::delete($this->_table, array('target' => $target));
		} catch(Oops_Sql_Exception $e) {
			self::__install($this->_table);
		}
	}

	public function find($source) {
		$sel = $this->_getSelector();
		$sel->source = $source;
		$targets = array();
		try {
			foreach($sel->select() as $row) {
				$targets[] = $row['target'];
			}
		} catch(Oops_Sql_Exception $e) {
			self::__install($this->_table);
		}
		return $targets;
	}

	public function __install($table) {
		Oops_Sql::Query("CREATE TABLE IF NOT EXISTS `$table` (
			`target` char(63),
			`source` char(63),
			KEY (`target`),
			KEY (`source`)
		) DEFAULT CHARSET=UTF8");
	}

	/**
	 *
	 * @return Oops_Sql_Selector
	 */
	private function _getSelector() {
		if(!isset($this->_selector)) {
			$this->_selector = new Oops_Sql_Selector($this->_table, null, array('target', 'source'));
		} else {
			$this->_selector->resetAll();
		}
		return $this->_selector;
	}
}