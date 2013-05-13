<?php

class Oops_File_Utils {
	private static $openBasedir;

	/**
	 * Reads ini setting open_basedir and stores open real paths in class static
	 * array
	 * 
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
	 * @param $mode integer
	 *        	Permissions mode to set on created directories
	 * @return unknown_type
	 */
	public static function autoCreateDir($path, $mode = 0777) {
		if(is_dir($path)) return true;
		if(is_file($path)) throw new Exception("$path is a file");
		mkdir($path, $mode, TRUE);
		if(is_dir($path)) return true;
		
		self::initOpenBasedir();
		switch(DIRECTORY_SEPARATOR) {
			case '/':
				$path = str_replace('\\', DIRECTORY_SEPARATOR, $path);
				break;
			case '\\':
				$path = str_replace('/', DIRECTORY_SEPARATOR, $path);
				break;
		}
		
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

	/**
	 * Splits file path for efficient storing large amounts of file named by ny
	 * kind of ID
	 *
	 * @param string $prefix
	 *        	File path prefix (i.e. /var/www/myfiles)
	 * @param string $splitable
	 *        	File name to split (1234567)
	 * @param int $chunkLength
	 *        	Chunk size (defaults to 2)
	 * @param int $skipLeading
	 *        	How much leading symbols to skip in $spitable (defaults to 0)
	 * @return string Splitted file path (i.e. /var/www/myfiles/12/34/56/7)
	 */
	public static function splitPath($prefix, $splitable, $chunkLength = 2, $skipLeading = 0) {
		$ret = '';
		if(strlen($prefix)) $ret = rtrim($prefix, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
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

	/**
	 *
	 *
	 * Remove non-empty dir
	 * 
	 * @param string $dirname        	
	 */
	public static function removeDirRecursive($dirname) {
		$files = glob($dirname . "/*");
		if($files === FALSE) return;
		foreach($files as $file) {
			if(is_dir($file))
				self::removeDirRecursive($file);
			else
				unlink($file);
		}
		rmdir($dirname);
	}

	/**
	 * Get file's myme type by name
	 *
	 * @param string $filename        	
	 * @return string Ambigous
	 */
	public static function getMimeType($filename, $default = 'application/octet-stream') {
		if(function_exists('mime_content_type')) return mime_content_type($filename);
		$types = array(
			
			'txt' => 'text/plain', 
			'htm' => 'text/html', 
			'html' => 'text/html', 
			'php' => 'text/html', 
			'css' => 'text/css', 
			'js' => 'application/javascript', 
			'json' => 'application/json', 
			'xml' => 'application/xml', 
			'swf' => 'application/x-shockwave-flash', 
			'flv' => 'video/x-flv', 
			
			// images
			'png' => 'image/png', 
			'jpe' => 'image/jpeg', 
			'jpeg' => 'image/jpeg', 
			'jpg' => 'image/jpeg', 
			'gif' => 'image/gif', 
			'bmp' => 'image/bmp', 
			'ico' => 'image/vnd.microsoft.icon', 
			'tiff' => 'image/tiff', 
			'tif' => 'image/tiff', 
			'svg' => 'image/svg+xml', 
			'svgz' => 'image/svg+xml', 
			
			// archives
			'zip' => 'application/zip', 
			'rar' => 'application/x-rar-compressed', 
			'exe' => 'application/x-msdownload', 
			'msi' => 'application/x-msdownload', 
			'cab' => 'application/vnd.ms-cab-compressed', 
			
			// audio/video
			'mp3' => 'audio/mpeg', 
			'qt' => 'video/quicktime', 
			'mov' => 'video/quicktime', 
			
			// adobe
			'pdf' => 'application/pdf', 
			'psd' => 'image/vnd.adobe.photoshop', 
			'ai' => 'application/postscript', 
			'eps' => 'application/postscript', 
			'ps' => 'application/postscript', 
			
			// ms office
			'doc' => 'application/msword', 
			'rtf' => 'application/rtf', 
			'xls' => 'application/vnd.ms-excel', 
			'ppt' => 'application/vnd.ms-powerpoint', 
			
			// open office
			'odt' => 'application/vnd.oasis.opendocument.text', 
			'ods' => 'application/vnd.oasis.opendocument.spreadsheet');
		
		$ext = strtolower(array_pop(explode('.', basename($filename))));
		if(array_key_exists($ext, $types)) {
			return $types[$ext];
		} else {
			return $default;
		}
	}
}