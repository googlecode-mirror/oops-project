<?
/**
 * @package Oops
 * @author Dmitry Ivanov rockmagic@yandex.ru
 * @license GNUv3
 */

if(!defined('OOPS_Loaded')) die("OOPS not loaded");

/**
 * Loader class
 */
class Oops_Loader {

	/**
	 * @static
	 * @access public
	 * @param string Oops library Class name or php file name
	 */
	public static function find($class) {
		if(class_exists($class) || interface_exists($class)) return true;
		
		$c = strtolower($class);
		$parts = explode('_', $c);
		$fname = '';
		
		for($i = 0, $cnt = sizeof($parts); $i < $cnt - 1; $i++)
			$fname .= (ucfirst($parts[$i]) . DIRECTORY_SEPARATOR);
		$fname .= (ucfirst($parts[$i]) . '.php');
		
		$incPaths = explode(PATH_SEPARATOR, get_include_path());
		foreach($incPaths as $incPath) {
			if(file_exists($incPath . DIRECTORY_SEPARATOR . $fname)) {
				require_once ($incPath . DIRECTORY_SEPARATOR . $fname);
				return true;
			}
		}
		return false;
	}

	public static function load($class) {
		if(Oops_Loader::find($class)) return $class;
		return false;
	}
}