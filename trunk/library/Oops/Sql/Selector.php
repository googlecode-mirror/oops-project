<?php

require_once 'Oops/Utils.php';
require_once 'Oops/Sql/Common.php';
require_once 'Oops/Sql/Selector/Exception.php';

/**
 * 
 * Select statesment constructor and mysql results fetcher
 * @author DI
 *
 */
class Oops_Sql_Selector {
	protected $_fields = array();
	protected $_table;
	protected $_alias;
	protected $_primaryKey;
	protected $_selectFields = array();
	
	protected $_where = array();
	protected $_joined = array();
	
	protected $_useAlias = false;
	
	protected $_limit = 0;
	protected $_offset = 0;
	
	protected $_selectFieldPositions = array();
	protected $_joinedAliases = array();
	
	protected $_orderBy = array();
	
	const CMP_EQ = 'EQ';
	const CMP_NULL = 'NULL';
	const CMP_NOTNULL = 'NOTNULL';
	const CMP_NE = 'NE';
	const CMP_GT = 'GT';
	const CMP_LT = 'LT';
	const CMP_GE = 'GE';
	const CMP_LE = 'LE';
	const CMP_LIKE = 'LIKE'; // LIKE '$value'
	const CMP_MLIKE = 'MLIKE'; // LIKE '%$value%'
	

	const JOIN_INNER = 'INNER';
	const JOIN_LEFT = 'LEFT';
	const JOIN_RIGHT = 'RIGHT';
	const JOIN_OUTTER = 'OUTTER';
	
	const ORDER_ASC = 'ASC';
	const ORDER_DESC = 'DESC';
	
	protected static $_aliasCounter = 1;
	protected static $_aliasesUsed = array();

	public function __construct($table, $primaryKey = null, $selectFields = null) {
		if($table instanceof Oops_Config) {
			$this->_initFromConfig($table);
		} elseif(is_string($table)) {
			$this->_table = $table;
			if(!is_null($primaryKey)) $this->_primaryKey = $primaryKey;
			if(is_array($selectFields)) $this->_selectFields = $selectFields;
		}
		
		if(!strlen($this->_table)) throw new Oops_Sql_Selector_Exception("Selector table not defined", Oops_Sql_Selector_Exception::NoTable);
		
		$this->_ensurePrimaryKeySelected();
		$this->_ensureFieldsContainSelected();
	}

	private function _ensurePrimaryKeySelected() {
		if(is_null($this->_primaryKey)) return;
		if(is_string($this->_primaryKey)) {
			if(!in_array($this->_primaryKey, $this->_selectFields)) array_unshift($this->_selectFields, $this->_primaryKey);
		} elseif(is_array($this->_primaryKey)) {
			foreach($this->_primaryKeys as $p) {
				if(!in_array($p, $this->_selectFields)) array_unshift($this->_selectFields, $p);
			}
		}
	}

	private function _ensureFieldsContainSelected() {
		foreach($this->_selectFields as $f) {
			if(is_string($f) && !in_array($f, $this->_fields)) $this->_fields[] = $f;
		}
	}

	protected function _initFromConfig($config) {
		$this->_table = $config->table;
		$this->_primaryKey = $config->primaryKey;
		$this->_fields = $config->fields->__toArray();
		$this->_selectFields = $config->_selectFields->__toArray();
	}

	/**
	 * 
	 * Used to set simple where conditions when $name is field id
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value) {
		if(in_array($name, $this->_fields)) {
			$this->where($name, self::CMP_EQ, $value);
		}
	}

	public function __get($name) {
		switch($name) {
			case 'selectFields':
				return $this->_selectFields;
			case 'alias':
				return $this->_alias;
			case 'hasJoined':
				return count($this->_joined) ? true : false;
			default:
				if(isset($this->_joinedAliases[$name])) return $this->_joinedAliases[$name];
				return null;
		}
	}

	public function count($returnSql = false) {
		$sql = $this->_getCountSql();
		if($returnSql) return $sql;
		
		$r = Oops_Sql::Query($sql);
		list($result) = mysql_fetch_row($r);
		return $result;
	}

	public function select($returnSql = false) {
		$sql = $this->_getSelectSql();
		
		if($returnSql) return $sql;
		$r = Oops_Sql::Query($sql);
		
		return $this->_fetchResults($r);
	}

	final public function selectList($returnSql = false) {
		return $this->select($returnSql);
	}

	public function where($field, $compare = null, $value = null) {
		if($value === null) $compare = $compare == self::CMP_EQ ? self::CMP_NULL : self::CMP_NOTNULL;
		$this->_where[] = array($field, $compare, $value);
	}

	public function orderBy($field, $direction = self::ORDER_ASC) {
		$this->_orderBy[$field] = $direction;
	}

	public function limit($limit, $offset = 0) {
		$this->_limit = (int) $limit;
		$this->_offset = (int) $offset;
	}

	public function resetAll() {
		$this->resetWhere();
		$this->resetLimit();
		$this->resetJoins();
	}

	public function resetWhere() {
		$this->_where = array();
	}

	public function resetLimit() {
		$this->_limit = $this->_offset = 0;
	}

	public function resetJoins() {
		$this->_joined = array();
	}

	/**
	 * 
	 * Join selector
	 * 
	 * @param Oops_Sql_Selector $selector
	 * @param string $foreignKey current selector's field
	 * @param string $joinedKey joined selector's field
	 * @param const $joinType join type
	 * @param string $alias joined selector alias in result row
	 */
	public function join($selector, $foreignKey, $joinedKey, $joinType = null, $alias = null, $additionalConditions = array()) {
		$this->_useAlias = true;
		
		//Ensure joinType is correct
		switch($joinType) {
			case self::JOIN_INNER:
			case self::JOIN_LEFT:
			case self::JOIN_OUTTER:
			case self::JOIN_RIGHT:
				break;
			default:
				$joinType = self::JOIN_INNER;
		}
		
		//Store join setting
		$this->_joined[] = array(
			$selector, 
			$foreignKey, 
			$joinedKey, 
			$joinType, 
			$alias, 
			$additionalConditions);
		
		if(!is_null($alias)) $this->_joinedAliases[$alias] = $selector;
	}

	final public function innerJoin($selector, $foreignKey, $joinedKey, $alias) {
		$this->join($selector, $foreignKey, $joinedKey, self::JOIN_INNER, $alias);
	}

	final public function leftJoin($selector, $foreignKey, $joinedKey, $alias, $additionalConditions = array()) {
		$this->join($selector, $foreignKey, $joinedKey, self::JOIN_LEFT, $alias, $additionalConditions);
	}

	protected function _sqlFrom() {
		$from = Oops_Sql_Common::escapeIdentifiers($this->_table);
		if($this->_useAlias) $from .= " AS `{$this->_alias}`";
		
		foreach($this->_joined as $joined) {
			list($selector, $fk, $jk, $joinType, $addCond) = $joined;
			$sFrom = $selector->_sqlFrom();
			
			//if selector has joined other selector use brackets
			if($selector->hasJoined) $sFrom = "($sFrom)";
			
			$on = "`{$this->alias}`.`$fk` = `{$selector->alias}`.`$jk`";
			if(count($addCond)) {
				foreach($addCond as $cond) {
					list($jField, $compare, $value) = $cond;
					$on .= " AND `{$selector->alias}`.`$jField`";
					
					switch($compare) {
						case self::CMP_NULL:
							$on .= 'IS NULL';
							break;
						case self::CMP_NOTNULL:
							$cond .= 'IS NOT NULL';
							break;
						case self::CMP_NE:
							$cond .= is_array($value) ? 'NOT ' : '!';
						case self::CMP_EQ:
							if(is_array($value)) {
								$cond .= 'IN (' . join(',', array_map(array(
									'Oops_Sql_Common', 
									'quoteValue'), $value)) . ')';
							} else
								$cond .= '= ' . Oops_Sql_Common::quoteValue($value);
							break;
					}
				}
			}
			
			switch($joinType) {
				case self::JOIN_INNER:
					$from .= ", $sFrom";
					break;
				case self::JOIN_LEFT:
					$from .= " LEFT JOIN $sFrom ON $on";
					break;
				case self::JOIN_RIGHT:
					$from .= " RIGHT JOIN $sFrom ON $on";
					break;
				case self::JOIN_OUTTER:
					$from .= " OUTTER JOIN $sFrom ON $on";
					break;
			}
		}
		
		return $from;
	}

	protected function _sqlWhere() {
		if(!count($this->_where) && !count($this->_joined)) return '';
		$wheres = array();
		foreach($this->_where as $condition) {
			list($field, $compare, $value) = $condition;
			if(!is_array($field)) {
				$wheres[] = $this->_sqlCondition($field, $compare, $value);
			} else {
				$conds = array();
				foreach($field as $orCond) {
					@list($field, $compare, $value) = $orCond;
					$conds[] = $this->_sqlCondition($field, $compare, $value);
				}
				Oops_Utils::ToNonEmptyArray($conds);
				$wheres[] = '(' . join(' OR ', $conds) . ')';
			}
		}
		
		foreach($this->_joined as $joined) {
			list($selector, $fk, $jk, $joinType) = $joined;
			if($joinType == self::JOIN_INNER) {
				$wheres[] = Oops_Sql_Common::escapeIdentifiers($this->_alias . '.' . $fk) . ' = ' . Oops_Sql_Common::escapeIdentifiers($selector->_alias . '.' . $jk);
			}
			$wheres[] = $selector->_sqlWhere();
		}
		
		Oops_Utils::ToNonEmptyArray($wheres);
		return join(' AND ', $wheres);
	}

	protected function _sqlCondition($field, $compare, $value) {
		$fid = Oops_Sql_Common::escapeIdentifiers($this->_useAlias ? $this->_alias . '.' . $field : $field);
		$cond = $fid . ' ';
		switch($compare) {
			case self::CMP_NULL:
				$cond .= 'IS NULL';
				break;
			case self::CMP_NOTNULL:
				$cond .= 'IS NOT NULL';
				break;
			case self::CMP_NE:
				$cond .= is_array($value) ? 'NOT ' : '!';
			case self::CMP_EQ:
				if(is_array($value)) {
					$cond .= 'IN (' . join(',', array_map(array('Oops_Sql_Common', 'quoteValue'), $value)) . ')';
				} else
					$cond .= '= ' . Oops_Sql_Common::quoteValue($value);
				break;
			case self::CMP_GT:
			case self::CMP_LT:
			case self::CMP_GE:
			case self::CMP_LE:
				if(is_array($value)) throw new Oops_Sql_Selector_Exception("Unexcepted array", Oops_Sql_Selector_Exception::UnexpectedValueType);
				$operands = array(
					self::CMP_GT => '> ', 
					self::CMP_GE => '>= ', 
					self::CMP_LT => '< ', 
					self::CMP_LE => '<= ');
				$cond .= $operands[$compare] . Oops_Sql_Common::quoteValue($value);
				break;
			case self::CMP_LIKE:
				Oops_Utils::ToArray($value);
				if(!count($value))
					$cond = '';
				else {
					$value = array_map(array('Oops_Sql', 'Escape'), $value);
					$cond = "($fid LIKE '" . join("' OR $fid LIKE '", $value) . "')";
				}
				break;
			case self::CMP_MLIKE:
				Oops_Utils::ToArray($value);
				if(!count($value))
					$cond = '';
				else {
					$value = array_map(array('Oops_Sql', 'Escape'), $value);
					$cond = "($fid LIKE '%" . join("%' OR $fid LIKE '%", $value) . "%')";
				}
				break;
			default:
				throw new Oops_Sql_Selector_Exception("Unexpected compare type $compare", Oops_Sql_Selector_Exception::UnexpectedCompareType);
		}
		
		return $cond;
	}

	protected function _sqlFields($startPosition = 0) {
		if(!$startPosition) $this->_ensurePrimaryKeySelected();
		$sqlFields = '';
		$this->_selectFieldPositions = array();
		
		foreach($this->_selectFields as $f) {
			$sqlFields .= $this->_useAlias ? Oops_Sql_Common::escapeIdentifiers($this->_alias . '.' . $f) : "`$f`";
			$this->_selectFieldPositions[$f] = $startPosition++;
			$sqlFields .= ', ';
		}
		
		foreach($this->_joined as $joined) {
			list($selector) = $joined;
			if(count($selector->selectFields)) {
				$sqlFields .= $selector->_sqlFields($startPosition) . ', ';
				$startPosition += count($selector->selectFields);
			}
		}
		
		return substr($sqlFields, 0, -2);
	}

	protected function _sqlGroupBy() {
		return '';
	}

	protected function _sqlOrderBy() {
		$orderBy = '';
		if(count($this->_orderBy)) $orderBy = ' ORDER BY ';
		foreach($this->_orderBy as $f => $direction) {
			$orderBy .= Oops_Sql_Common::escapeIdentifiers($this->_useAlias ? $this->_alias . '.' . $f : $f);
			if($direction == self::ORDER_DESC) $orderBy .= ' DESC';
			$orderBy .= ', ';
		}
		return substr($orderBy, 0, -2);
	}

	protected function _sqlLimit() {
		if(!$this->_limit) return '';
		return ' LIMIT ' . $this->_offset . ', ' . $this->_limit;
	}

	protected function _getCountSql() {
		$this->_setQueryAliases();
		$sql = "SELECT COUNT(*) FROM " . $this->_sqlFrom();
		if(strlen($where = $this->_sqlWhere())) $sql .= " WHERE " . $where;
		return $sql;
	}

	protected function _getSelectSql() {
		$this->_setQueryAliases();
		$sql = "SELECT " . $this->_sqlFields() . " FROM " . $this->_sqlFrom();
		if(strlen($where = $this->_sqlWhere())) $sql .= " WHERE " . $where;
		$sql .= $this->_sqlGroupBy() . $this->_sqlOrderBy() . $this->_sqlLimit();
		return $sql;
	}

	protected function _setQueryAliases($root = true, $preffered = null) {
		static $used = array();
		
		if($root) {
			if(!count($this->_joined)) {
				$this->_useAlias = false;
				return;
			}
			
			self::$_aliasesUsed = array();
			self::$_aliasCounter = 1;
		}
		$this->_useAlias = true;
		if(strlen($preffered) && !in_array($preffered, self::$_aliasesUsed)) {
			self::$_aliasesUsed[] = $this->_alias = $preffered;
		} else {
			$this->_alias = 't' . self::$_aliasCounter++;
		}
		
		foreach($this->_joined as $joined) {
			list($selector, $fk, $jk, $jt, $alias) = $joined;
			$selector->_setQueryAliases(false, $alias);
		}
	}

	protected function _fetchResults($r) {
		$results = array();
		if(is_string($this->_primaryKey)) {
			$pkPos = $this->_selectFieldPositions[$this->_primaryKey];
		} else {
			$pkPos = null;
		}
		
		while(($row = mysql_fetch_row($r)) !== false) {
			if(is_null($pkPos))
				$results[] = $this->_parseRow($row);
			else
				$results[$row[$pkPos]] = $this->_parseRow($row);
		}
		return $results;
	}

	protected function _parseRow($row) {
		$result = array();
		
		foreach($this->_selectFieldPositions as $f => $i)
			$result[$f] = $row[$i];
		foreach($this->_joined as $joined) {
			list($selector, $joinedKey, $foreignKey, $joinType, $alias) = $joined;
			if(!strlen($alias) || isset($result[$alias])) $alias = $selector->alias;
			$result[$alias] = $selector->_parseRow($row);
		}
		
		$this->__postParseRow($result);
		return $result;
	}

	protected function __postParseRow(&$row) {
	}

}