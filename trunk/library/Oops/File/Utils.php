<?php

class Oops_File_Utils {
	
	private static $openBasedir;

	/**
	 * Reads ini setting open_basedir and stores open real paths in class static array 
	 * @return unknown_type
	 */
	public static function initOpenBasedir() {
		if(!isset(self::$openBasedir)) {
			self::$openBasedir = array();
			$dirs = explode(PATH_SEPARATOR, ini_get('open_basedir'));
			foreach($dirs as $dir) {
				self::$openBasedir[] = realpath($dir);
			}
		}
	}

	/**
	 * Create path with a given permissions if path not already exists
	 *
	 * @param $path string
	 * @param $mode integer Permissions mode to set on created directories
	 * @return unknown_type
	 */
	public static function autoCreateDir($path, $mode = 0777) {
		self::initOpenBasedir();
		if(is_dir($path)) return true;
		if(is_file($path)) throw new Exception("$path is a file");
		
		$parts = explode(DIRECTORY_SEPARATOR, $path);
		$subPath = '';
		for($i = 0, $cnt = count($parts); $i < $cnt; $i++) {
			$subPath .= $parts[$i] . DIRECTORY_SEPARATOR;

			if($subPath == '/') continue;

			foreach(self::$openBasedir as $opendir) {
				if($opendir === $subPath || strpos($opendir . DIRECTORY_SEPARATOR, $subPath) === 0) continue 2;
			}
			
			if(is_dir($subPath)) continue;
			if(is_file($subPath)) throw new Exception("Can't create directory $subPath. File exists");
			
			if(!mkdir($subPath, $mode)) throw new Exception("Can't create directory $subPath");
			chmod($subPath, $mode);
		}
		return true;
	}

	public static function splitPath($prefix, $splitable, $chunkLength = 2, $skipLeading = 0) {
		$ret = rtrim($prefix, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		if($skipLeading > 0) {
			$ret .= substr($splitable, 0, $skipLeading);
			$ret .= DIRECTORY_SEPARATOR;
		}
		$splitable = substr($splitable, $skipLeading);
		for($i = 0, $max = strlen($splitable) - $chunkLength; $i < $max; $i += $chunkLength) {
			$ret .= substr($splitable, $i, $chunkLength) . DIRECTORY_SEPARATOR;
		}
		$ret .= substr($splitable, $i);
		return rtrim($ret, DIRECTORY_SEPARATOR);
	}

}