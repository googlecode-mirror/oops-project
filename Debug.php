<?
/**
* @package Oops
*/

if(!defined('OOPS_Loaded')) die("OOPS not found");

/**
* Runtime debug tools
*/
class Oops_Debug {
	/**
	* Dumps a given value with optional name and backtrace if dump is allowed for current user state
	*
	* Usage:
	* <code><?php
	* Oops_Debug::Dump($var);
	* Oops_Debug::Dump($thatvar,"That var");
	* Oops_Debug::Dump($var,"Some label",true); //will output complete backtrace
	* ?></code>
	*
	* @param mixed Value to dump
	* @param string Label
	* @param bool Output full backtrace or only the last element
	* @return void
	*/
	function Dump($value, $name = null, $fullTrace = false) {
		if (!Oops_Debug::allow()) return;
		?><div style="border:1px solid #FF0000"><?

		if(!is_null($name)) echo "<b>".$name."</b>=";

		Oops_Debug::print_r_dhtml($value);
		$data = debug_backtrace();
		$count=count($data);
		$start = $count==1?0:1;
		$start=0;
		?><div style="border:1px solid #00FF00"><?
		for ($i=$start; $i<$count; $i++){
			$obj=$data[$i];
			?><span style="font:'Courier New', Courier, mono; color:#0000FF; font-size:14px;"><?=@$obj['class'].'->'.$obj['function'];?></span>::<b><?=@$obj['line'];?></b><small>(<?=@$obj['file'];?>)</small><br><?
			if(!$fullTrace) break;
		}
		?></div><?
		?></div><?
	}

	function allow() {
		static $ret;
		if(!isset($ret)) {
			$ret = false;
			if($_SERVER['REMOTE_ADDR'] == '127.0.0.1') $ret=true;
		}
		return $ret;
	}

	/**
	* @ignore
	*/
	function print_r_dhtml ($value, $first=true) {
		static $style;
		static $i=0;$i++; 
		static $j=0;
		if($first) {
			$j=0;
			$style="display: block;";
//			$value = unserialize(serialize($value));
		}
		if(++$j>10) $style="display: none;";
		$type=gettype($value);
		switch ($type) {
			case "object":
				$type .= " <i>".get_class($value)."</i>";
				$value = get_object_vars($value);
			case "array":
				echo "<a onclick=\"document.getElementById('_ate_$i').style.display = ";
				echo "document.getElementById('_ate_$i";
				echo "').style.display == 'block' ?";
				echo "'none' : 'block';return false;\" href=\"#\">" .ucfirst($type). " (".sizeof($value).")</a>\n";
				echo "<ul id=\"_ate_$i\" style=\"$style\">";
				foreach ($value as $k => $v) {
					echo "<li>[" . htmlspecialchars ( $k ). "] => ";
					Oops_Debug::print_r_dhtml($v,false);
					echo "</li>\n";
				}
				echo "</ul>";
				break;
			case "double":
			case "float":
			case "integer":
				echo  '<span style="color:blue;">'.htmlspecialchars ( $value ).'</span>';
				break;
			case "boolean":
				echo '<span style="color:#335577;">'.($value?"true":"false").'</span>';
				break;
			case "string":
				echo '<span style="color:#337733;"><code>' . htmlspecialchars ( $value ). "</code></span>";
				break;
			default:
				echo $type;
				break;
		}
	}
}