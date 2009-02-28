<?
/**
* @package Oops
*/

if(!defined('OOPS_Loaded')) die("OOPS not found");

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
	function ToArray(&$a) {
		if(is_array($a)) return;
		if(!is_null($a) && $a!==false) {
			$a=array($a);
			return;
		}
		$a=array();
	}

	/**
	* Turms passed non-array variable to array containing the passed value, and converts all values to integers
	*
	* @static
	*/
	function ToIntArray(&$a,$keepzero=false) {
		Oops_Utils::ToArray($a);
		if(count($a)) {
			foreach($a as $k=>$v) {
				if(!(int)$v && (!$keepzero || $v!==(int)$v)) unset($a[$k]);
			}
		}
	}

	/**
	* Turms passed non-array variable to array containing the passed value, and converts all values to integers
	*
	* @static
	*/
	function ToNonEmptyArray(&$a) {
		Oops_Utils::ToArray($a);
		if(count($a)) {
			foreach($a as $k=>$v) {
				if(!$v) unset($a[$k]);
			}
		}
	}

	/**
	* Same as ToIntArray but escapes all strings with mysql_real_escape_string
	*
	* @static
	*/
	function ToEscapedArray(&$a,$keepillegal=false) {
		Oops_Utils::ToArray($a);
		if(count($a)) {
			foreach($a as $k=>$v) {
				$v1=Oops_Sql::Escape($v);
				if($v!=$v1) {
					if(!$keepillegal) {
						unset($a[$k]);
					} else {
						$a[$k]=$v1;
					}
				}
			}
		}
	}
	/**
	 * Приводит произвольное дерево вида:
	 * 
	 * к линейном массиву, где автоматически проставляется уровень в иерархии (от 0)	 
	 *
	 * @param unknown_type $Tree
	 * @param unknown_type $ChildsID
	 * 
	 * @author cloud
	 * @access public
	 * @static Utils::Tree2Line()
	 * 
	 * Используется:
	 * comments_assembler::ToLine();
	 */
	function Tree2Line(&$Tree, $ChildsID, $LevelID='Level'){
		$ret=array();
		Oops_Utils::_Tree2Line($ret, $Tree, $ChildsID, $LevelID, 0);
		return $ret;
	}
	
	/**
	 * Линейный вид треда
	 *
	 * @param array $ret
	 * @param array $Tree участок дерева
	 * @param string $ChildsID ключ потомков
	 * @param string $LevelID ключ уровня
	 * 
	 * @author cloud
	 * @access public
	 */
	function _Tree2Line(&$ret, &$Tree, $ChildsID, $LevelID, $Level=0){
		if ($count=count($Tree)){
			for ($i=0; $i<$count; $i++){					
				$ret[]=$Tree[$i];
				unset($ret[count($ret)-1][$ChildsID]);
				$ret[count($ret)-1][$ChildsID][$LevelID]=$Level;
				if (isset($Tree[$i][$ChildsID])){					
					Utils::_Tree2Line($ret, $Tree[$i][$ChildsID], $ChildsID, $LevelID, $Level+1);
				}
			}
		}
	}


	/**
	* @param array Line array
	* @param string parentid key name
	* @param string childrenid key name
	* @return array
	*/
	function Line2Tree($Line,$ParentID='parent',$ChildrenID='children',$skipIfNoKey=false) {
		$ret = array();
		foreach($Line as $k=>$v) {
			if(!isset($v[$ParentID])) { //NULL is like !isset - No Parent
				$Line[$k][$ParentID]=false;
			}
			$Parent =& $Line[$k][$ParentID];
			if($Parent!==false && !isset($Line[$Parent])) {
				$Line[$k][$ParentID]=false;
			}
			if(!$Parent) {
				$ret[$k] =& $Line[$k];
				continue;
			}
			if(!isset($Line[$Parent][$ChildrenID]) || !is_array($Line[$Parent][$ChildrenID])) $Line[$Parent][$ChildrenID]=array();
			$Line[$Parent][$ChildrenID][$k]=&$Line[$k];
		}
		if(true && $skipIfNoKey!==false) {
			$dontskip=array();
			foreach($Line as $k=>$v) {
				if(array_key_exists($skipIfNoKey,$v)) {
					while($k) {
						$dontskip[]=$k;
						$k = $Line[$k][$ParentID];
					}
				}
			}
			foreach($Line as $k=>$v) {
				if(!in_array($k,$dontskip)) {
					unset($Line[$k]);
					if($v[$ParentID]) unset($Line[$v[$ParentID]][$ChildrenID][$k]);
					else unset($ret[$k]);
				}
			}
		}
		return $ret;
	}
}
?>