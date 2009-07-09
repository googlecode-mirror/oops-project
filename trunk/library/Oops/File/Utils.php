<?php

class Oops_File_Utils {

	/**
	 * Create path with a given permissions if path not already exists
	 *
	 * @param $path string
	 * @param $mode integer Permissions mode to set on created directories
	 * @return unknown_type
	 */
	public static function autoCreateDir($path, $mode = 0777) {
		if(is_dir($path)) return true;
		if(is_file($path)) throw new Exception("$path is a real file");
		
		$parts = explode(DIRECTORY_SEPARATOR, $path);
		$subPath = '';
		for($i = 0, $cnt = count($parts); $i < $cnt; $i++) {
			$subPath = $parts[$i] . DIRECTORY_SEPARATOR;
			if(is_dir($subPath)) continue;
			if(is_file($subPath)) throw new Exception("Can't create directory $subPath. File exists");
			
			if(!mkdir($subPath, $mode)) throw new Exception("Can't create directory $subPath");
		}
		return true;
	}

}