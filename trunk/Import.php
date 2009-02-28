<?
/**
* Library import
*
* @package Oops
* @tutorial Oops/Oops.pkg#import
* 
*/
/**
* This defines the library path, don't change this definition
*/
define("OOPS_PATH",dirname(__FILE__));
define("OOPS_Loaded",true);

__autoload("OOPS_Object");
__autoload("OOPS_Sql");
__autoload("OOPS_Template");
__autoload("OOPS_Error");
__autoload("Oops_Factory");

/**
* Funciton for loading OOPS classes. FALSE is returned if error occurs.
*
* @param string class name
*
* <code><?php
*   __autoload("Oops_Some_Class.php"); // OOPS_PATH/Some/Class.php will be loaded.
*   $obj = new Oops_Some_Class(); //Now class is available
* ?></code>
*/
function __autoload($class) {
	if(class_exists($class)) return true;
	$c = strtolower($class);
	if(substr($c,0,5)!='oops_') return false;
	$parts = explode('_',$c);
	$fname = OOPS_PATH . DIRECTORY_SEPARATOR;
	for($i=1,$cnt=sizeof($parts);$i<$cnt-1;$i++) $fname .= (ucfirst($parts[$i]) . DIRECTORY_SEPARATOR);
	$fname .= (ucfirst($parts[$i]).'.php');
	if(file_exists($fname)) {
		include_once($fname);
	} else return false;
}

function isDebug() {
	static $res;
	if(!isset($res)) {
		$res=false;
		if($_SERVER['REMOTE_ADDR']=='127.0.0.1' || $_SERVER['REMOTE_ADDR']=='10.8.0.6') $res=true;
//		if(isset($_SESSION) && isset($_SESSION['tester']) && $_SESSION['tester']=='debug2') $res=true;
	}
	return $res;
}

/**
* Just like mysql_query, but connects to mysql server on demand and dies on mysql error
*/
function db_query($query,$skiperrors=false) {
	static $b=false;
	if(!$b) __autoload('Oops_Sql');
	$b=true;
	if(false && !$skiperrors) {
		__autoload('Oops_Db_Logger');
		$l =& Oops_Db_Logger::getInstance();
		return $l->Analyze($query);
	}
	return Oops_Sql::Query($query,$skiperrors);
}

/**
* Debugging function, a user-friendly print_r
*
* Usage:
* <code><?php
* debugPrint($var); //will output complete backtrace
* debugPrint($thatvar,"That var"); //will output complete backtrace
* debugPrint($var,"Some label",__CLASS__,__FUNCTION__,__FILE__,__LINE__);
* ?></code>
*
*
*
* @param mixed Any variable to output
* @param string Label
* @param boolean show full backtrace or not
* @return void
*/
function debugPrint($value, $name=null, $fulltrace=false) {
	if (!isDebug()) return ;
	?><div style="border:1px solid #FF0000"><?

	if ($name) echo "<b>".$name."</b>=";
	print_r_dhtml($value);
	$data= debug_backtrace();
	$count=count($data);
	$start = $count==1?0:1;
	$start=0;
	?><div style="border:1px solid #00FF00"><?
	for ($i=$start; $i<$count; $i++){
		$obj=$data[$i];
		?><span style="font:'Courier New', Courier, mono; color:#0000FF; font-size:14px;"><?=@$obj['class'].'->'.$obj['function'];?></span>::<b><?=@$obj['line'];?></b><small>(<?=@$obj['file'];?>)</small><br><?
		if(!$fulltrace) break;
	}
	?></div><?
	?></div><?
}

	/**
	* @ignore
	*/
	function print_r_dhtml ( $value ,  $first=true) {
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
				echo "document.getElementById('_ate_$i" ; 
				echo "').style.display == 'block' ?" ; 
				echo "'none' : 'block';return false;\" href=\"#\">" .ucfirst($type). " (".sizeof($value).")</a>\n" ; 
				echo "<ul id=\"_ate_$i\" style=\"$style\">";
				foreach ($value as $k => $v) {
					echo "<li>[" . htmlspecialchars ( $k ). "] => ";
					print_r_dhtml($v,false);
					echo "</li>\n";
				}
				echo "</ul>";
				break; 
			case "double" :
			case "float" :
			case "integer": 
				echo  '<span style="color:blue;">'.htmlspecialchars ( $value ).'</span>';
//				echo  '<span style="color:blue;">'.number_format( $value ).'</span>';
				break; 
			case "boolean" : 
				echo '<span style="color:#335577;">'.($value?"true":"false").'</span>';
				break; 
			case "string" : 
				echo '<span style="color:#337733;"><code>' . htmlspecialchars ( $value ). "</code></span>";
				break; 
			default: 
				echo $type; 
				break;
		} 
	} 

?>