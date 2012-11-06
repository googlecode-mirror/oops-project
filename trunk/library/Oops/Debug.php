<?php

/**
 * @package Oops
 */

/**
 * Runtime debug tools
 */
class Oops_Debug {
	
	protected static $_allow;

	/**
	 * Dumps a given value with optional name and backtrace if dump is allowed for current user state
	 *
	 * Usage:
	 * <code>
	 * Oops_Debug::Dump($var);
	 * Oops_Debug::Dump($thatvar,"That var");
	 * Oops_Debug::Dump($var,"Some label",true); //will output complete backtrace
	 * </code>
	 *
	 * @param mixed Value to dump
	 * @param string Label
	 * @param bool Output full backtrace or only the last element
	 * @return void
	 */
	public static function Dump($value, $name = null, $fullTrace = false) {
		if(!Oops_Debug::allow()) return;
		echo '<div class="oops-debug" style="border: 1px solid #FF0000">';
		
		if(!is_null($name)) echo "<b>" . $name . "</b>=";
		
		Oops_Debug::print_r_dhtml($value);
		$data = debug_backtrace();
		$count = count($data);
		$start = $count == 1 ? 0 : 1;
		$start = 0;
		echo '<div style="border: 1px solid #00FF00">';
		for($i = $start; $i < $count; $i++) {
			$obj = $data[$i];
			echo '<span style="font: Courier New, Courier, mono; color: #0000FF; font-size: 14px;">';
			echo "{$obj['class']}->{$obj['function']}</span>::<b>{$obj['line']}</b><small>({$obj['file']})</small><br>";
			if(!$fullTrace) break;
		}
		echo "</div>\n</div>";
	}

	public static function allow() {
		if(!isset(self::$_allow)) {
			self::$_allow = false;
			
			$requestKeyValue = (string) Oops_Server::getConfig()->oops->debug_key;
			if(strlen($requestKeyValue) && isset($_REQUEST['debug']) && $_REQUEST['debug'] == $requestKeyValue) {
				return self::$_allow = true;
			}
			
			$debug_ip = (string) Oops_Server::getConfig()->oops->debug_ip;
			if(!strlen($debug_ip)) $debug_ip = '127.0.0.1/24';
			
			$remote = ip2long($_SERVER['REMOTE_ADDR']);
			if($remote === false) {
				return self::$_allow = false;
			}
			
			$ips = explode(',', $debug_ip);
			foreach($ips as $ip) {
				list($ip, $mask) = explode('/', trim($ip));
				$mask = (int) $mask;
				if($mask > 32 || $mask < 0) {
					// Invalid mask 
					continue;
				}
				$allowed = ip2long($ip);
				if($allowed === false) {
					// Invalid IP
					continue;
				}
				$push = 32 - $mask;
				if($remote >> $push == $allowed >> $push) {
					self::$_allow = true;
					break;
				}
			}
		}
		
		return self::$_allow;
	}
	
	/**
	 * Sets debug mode
	 * 
	 * @param boolean $allow
	 */
	public static function setAllow($allow) {
		$allow = (bool) $allow;
		self::$_allow = $allow;
	}
		

	/**
	 * @ignore
	 */
	public static function print_r_dhtml($value, $first = true) {
		static $style = '';
		static $i = 0;
		$i++;
		static $j = 0;
		if($first) {
			$j = 0;
			$style = "display: block;";
			//			$value = unserialize(serialize($value));
		}
		if(++$j > 10) $style = "display: none;";
		$type = gettype($value);
		switch($type) {
			case "object":
				$type .= " <i>" . get_class($value) . "</i>";
				$value = array('__dump__' => print_r($value, true));
			case "array":
				echo "<a onclick=\"document.getElementById('_ate_$i').style.display = ";
				echo "document.getElementById('_ate_$i";
				echo "').style.display == 'block' ?";
				echo "'none' : 'block';return false;\" href=\"#\">" . ucfirst($type) . " (" . sizeof($value) . ")</a>\n";
				echo "<ul id=\"_ate_$i\" style=\"$style\">";
				foreach($value as $k => $v) {
					echo "<li>[" . htmlspecialchars($k) . "] => ";
					Oops_Debug::print_r_dhtml($v, false);
					echo "</li>\n";
				}
				echo "</ul>";
				break;
			case "double":
			case "float":
			case "integer":
				echo '<span style="color:blue;">' . htmlspecialchars($value) . '</span>';
				break;
			case "boolean":
				echo '<span style="color:#335577;">' . ($value ? "true" : "false") . '</span>';
				break;
			case "string":
				echo '<span style="color:#337733;"><pre><code>' . htmlspecialchars($value) . "</code></pre></span>";
				break;
			default:
				echo $type;
				break;
		}
	}
}