<?php

/**
 * @package Oops
 * @license GPLv3
 * @author Dmitry Ivanov rockmagic@yandex.ru
 */

/**
 * Class for some special tasks
 *
 * @static
 */
class Oops_Utils {

	/**
	 * Turms passed non-array variable to array containing the passed value
	 *
	 * @static
	 */
	public static function ToArray(&$a) {
		if(is_array($a)) return;
		if(!is_null($a) && $a !== false) {
			$a = array($a);
			return;
		}
		$a = array();
	}

	/**
	 * Turms passed non-array variable to array containing the passed value, and converts all values to integers
	 *
	 * @static
	 */
	public static function ToIntArray(&$a, $keepzero = false) {
		Oops_Utils::ToArray($a);
		if(count($a)) {
			$renumberArray = false;
			foreach($a as $k => $v) {
				// If it's an integer value
				if($v === (int) $v) continue;
				
				if(strval($v) == strval((int) $v)) {
					//it's an integer string
					$a[$k] = (int) $v;
					continue;
				}
				// it's not an integer string, so it's not valid, assume zero
				if($keepzero) {
					//Replace value with 0
					$a[$k] = 0;
					continue;
				}
				
				//value should be unset
				unset($a[$k]);
				//Trigger a flag to renumber array keys
				$renumberArray = true;
				continue;
			}
			if($renumberArray) {
				$a = array_merge(array(), $a);
			}
		}
	}

	/**
	 * Turms passed non-array variable to array containing the passed value, and converts all values to integers
	 *
	 * @static
	 */
	public static function ToNonEmptyArray(&$a) {
		Oops_Utils::ToArray($a);
		if(count($a)) {
			foreach($a as $k => $v) {
				if(!$v) unset($a[$k]);
			}
		}
	}

	/**
	 * Same as ToIntArray but escapes all strings with mysql_real_escape_string
	 *
	 * @static
	 */
	public static function ToEscapedArray(&$a, $keepillegal = false) {
		Oops_Utils::ToArray($a);
		if(count($a)) {
			foreach($a as $k => $v) {
				$v1 = Oops_Sql::Escape($v);
				if($v != $v1) {
					if(!$keepillegal) {
						unset($a[$k]);
					} else {
						$a[$k] = $v1;
					}
				}
			}
		}
	}

	/**
	 * Compile a given Tree to one-dimensional List having a depth level set for each element
	 * 
	 * @param array Tree
	 * @param array childrenId
	 * 
	 * @author cloud
	 * @access public
	 * @static Utils::Tree2Line()
	 * 
	 */
	public static function Tree2Line(&$Tree, $childrenId = "children", $levelId = 'level') {
		$ret = array();
		Oops_Utils::_Tree2Line($ret, $Tree, $childrenId, $levelId, 0);
		return $ret;
	}

	/**
	 * @ignore
	 *
	 * @param array $ret Return value
	 * @param array $Tree Tree segment
	 * @param string $childrenId Children key in Element array
	 * @param string $levelId A key to set with element's depth level value
	 * 
	 * @author cloud
	 * @access public
	 */
	protected static function _Tree2Line(&$ret, &$Tree, $childrenId, $levelId, $Level = 0) {
		foreach(array_keys($Tree) as $i) {
			$ret[$i] = $Tree[$i];
			unset($ret[$i][$childrenId]);
			$ret[$i][$levelId] = $Level;
			if(isset($Tree[$i][$childrenId])) {
				Oops_Utils::_Tree2Line($ret, $Tree[$i][$childrenId], $childrenId, $levelId, $Level + 1);
			}
		}
	}

	/**
	 * @param array Line array
	 * @param string parentid key name
	 * @param string childrenid key name
	 * @return array
	 */
	public static function Line2Tree($Line, $ParentID = 'parent', $childrenId = 'children', $skipIfNoKey = false) {
		$ret = array();
		foreach($Line as $k => $v) {
			if(!isset($v[$ParentID])) { //NULL is like !isset - No Parent
				$Line[$k][$ParentID] = false;
			}
			$Parent = & $Line[$k][$ParentID];
			if($Parent !== false && !isset($Line[$Parent])) {
				$Line[$k][$ParentID] = false;
			}
			if(!$Parent) {
				$ret[$k] = & $Line[$k];
				continue;
			}
			if(!isset($Line[$Parent][$childrenId]) || !is_array($Line[$Parent][$childrenId])) $Line[$Parent][$childrenId] = array();
			$Line[$Parent][$childrenId][$k] = &$Line[$k];
		}
		if(true && $skipIfNoKey !== false) {
			$dontskip = array();
			foreach($Line as $k => $v) {
				if(array_key_exists($skipIfNoKey, $v)) {
					while($k) {
						$dontskip[] = $k;
						$k = $Line[$k][$ParentID];
					}
				}
			}
			foreach($Line as $k => $v) {
				if(!in_array($k, $dontskip)) {
					unset($Line[$k]);
					if($v[$ParentID])
						unset($Line[$v[$ParentID]][$childrenId][$k]);
					else
						unset($ret[$k]);
				}
			}
		}
		return $ret;
	}

	public static function toBytes($string) {
		$string = trim($string);
		$int = intval($string);
		$last = strtolower(substr($string, -1));
		switch($last) {
			case 'g':
				$int *= 1024;
			case 'm':
				$int *= 1024;
			case 'k':
				$int *= 1024;
		}
		return $int;
	}

}
