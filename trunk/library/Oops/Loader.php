<?php

/**
 * @package Oops
 * @author Dmitry Ivanov rockmagic@yandex.ru
 * @license GNUv3
 */

/**
 * Loader class
 */
class Oops_Loader {

	/**
	 *
	 * @static
	 *
	 *
	 *
	 *
	 * @access public
	 * @param
	 *        	string Oops library Class name or php file name
	 */
	public static function find($class) {
		
		/* try requested case */
		$fname = str_replace('_', '/', $class) . '.php';
		if(($found = self::resolvePath($fname)) !== false) return $found;
		
		/* requested case not found, trying canonical (Some_Classname) */
		
		$c = strtolower($class);
		$parts = explode('_', $c);
		$fname = '';
		
		for($i = 0, $cnt = sizeof($parts); $i < $cnt - 1; $i++)
			$fname .= (ucfirst($parts[$i]) . DIRECTORY_SEPARATOR);
		$fname .= (ucfirst($parts[$i]) . '.php');
		
		if(($found = self::resolvePath($fname)) !== false) return $found;
		
		return false;
	}

	public static function load($class) {
		if(class_exists($class) || interface_exists($class)) return;
		if(($found = self::find($class)) !== false) require $found;
	}

	public static function resolvePath($file) {
		if(function_exists('stream_resolve_include_path'))
			return stream_resolve_include_path($file);
		else {
			$include_path = explode(PATH_SEPARATOR, get_include_path());
			foreach($include_path as $path) {
				$tryFile = $path . DIRECTORY_SEPARATOR . $file;
				if(file_exists($tryFile)) return $tryFile;
			}
			return false;
		}
	}
}