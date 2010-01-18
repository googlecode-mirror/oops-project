<?php

require_once 'Oops/File.php';

/**
 * 
 * @author Dmitry ivanov
 * 
 * Usage: <?php
 * 	$f = new Oops_Video_File('test.wmv');
 * 	$preview = $f->getPreview();
 * 	$preview->rename('test.jpg');
 * 
 * 	$flv = $f->getFlv();
 * 	$flv->rename('test.flv');
 * ?>
 * 
 */
class Oops_Video_File extends Oops_File {
	
	protected static $_config;
	
	protected $_videoStats = array();

	/**
	 * 
	 * @param string|Oops_File $filename
	 * @return unknown_type
	 */
	public function __construct($filename) {
		if($filename instanceof Oops_File) {
			foreach(get_object_vars($filename) as $k => $v) {
				$this->{$k} = $v;
			}
		} else {
			parent::__construct($filename);
		}
		
		// @todo check if valid video
		$this->_statVideo();
	}

	public function __get($name) {
		switch($name) {
			case 'videoStats':
				return $this->_videoStats;
			default:
				return parent::__get($name);
		}
	}

	public function isVideo() {
		if(isset($this->_videoStats['VIDEO_FORMAT'])) return true;
		return false;
	}

	protected function _statVideo() {
		$execString = "mplayer";
		self::_initConfig();
		foreach(self::$_config->stats as $k => $v) {
			$execString .= " -$k $v";
		}
		$pipeSign = DIRECTORY_SEPARATOR == '\\' ? '\\' : '|';
		$execString .= " {$this->_filename} $pipeSign grep ID_";
		
		$lines = array();
		$execString = preg_replace('/\s\s+/', ' ', $execString);
		exec($execString, $lines);
		foreach($lines as $line) {
			list($k, $v) = explode("=", $line);
			$k = substr($k, 3);
			$this->_videoStats[$k] = $v;
		}
	}

	protected function _initConfig() {
		if(isset(self::$_config)) return;
		require_once 'Oops/Video/Config.php';
		self::$_config = new Oops_Video_Config();
		if(file_exists('./application/config/video.ini')) {
			require_once 'Oops/Config/Ini.php';
			self::$_config->mergeConfig(new Oops_Config_Ini('./application/config/video.ini'));
		}
	}

	/**
	 * @return Oops_File_Temporary
	 */
	public function getPreview() {
		if(!$this->isVideo()) {
			require_once 'Oops/Video/Exception.php';
			throw new Oops_Video_Exception("Not a video file");
		}
		/**
		 * Prepare tmp dir
		 */
		require_once 'Oops/File/Temporary.php';
		$tmp = new Oops_File_Temporary(self::$_config->tmpdir);
		$tmp->toDir();
		$outdir = $tmp->filename;
		if(strpos($outdir, ':') !== false || strpos($outdir, ' ') !== false) {
			//trying to prevent windows-speciefic path symbols
			if(strpos($outdir, realpath('.') . DIRECTORY_SEPARATOR) === 0) {
				// Temp dir path is inside working dir, use relative path
				$outdir = str_replace(realpath('.') . DIRECTORY_SEPARATOR, '', $outdir);
			} elseif(strpos($outdir, ":") === 1 && substr($outdir, 0, 2) == substr(realpath('.'), 0, 2)) {
				// Temp dir located on the same drive as working dir, replace X:\ with / and replace directory separator to unix style
				$outdir = substr(str_replace(DIRECTORY_SEPARATOR, '/', $outdir), 2);
			} elseif(strpos($outdir, ":")) {
				require_once 'Oops/Video/Exception.php';
				throw new Oops_Video_Exception("Can not eliminate semicolon sign in temp dir path");
			}
		}
		
		if(strpos($outdir, ' ')!==false) $outdir = '"' . $outdir . '"';
		
		$execString = "mplayer";
		self::_initConfig();
		$config = clone self::$_config->preview;
		$config->mergeConfig(new Oops_Config(array("vo" => "jpeg:outdir=$outdir")));
		
		foreach($config as $k => $v) {
			$execString .= " -$k $v";
		}
		$execString .= " {$this->_filename}";
		exec($execString);
		
		$files = glob($tmp->filename . DIRECTORY_SEPARATOR . "/*.*");
		if(!is_array($files) || !count($files)) {
			require_once 'Oops/Video/Exception.php';
			throw new Oops_Video_Exception("No frames extracted with $execString");
		}
		
		$jpegContents = file_get_contents(end($files));
		$result = new Oops_File_Temporary();
		$result->putContents($jpegContents);
		$result->type = 'image/jpeg';
		
		return $result;
	}

	/**
	 * @return Oops_File_Temporary
	 */
	public function getFlv() {
		if(!$this->isVideo()) {
			require_once 'Oops/Video/Exception.php';
			throw new Oops_Video_Exception("Not a video file");
		}
		/**
		 * Prepare tmp file
		 */
		require_once 'Oops/File/Temporary.php';
		$result = new Oops_File_Temporary(self::$_config->tmpdir);
		$result->filename = $result->filename . ".flv";
		$outfile = $result->filename;
		if(strpos($outfile, ':') !== false || strpos($outfile, ' ') !== false) {
			//trying to prevent windows-speciefic path symbols
			$outfile = str_replace(realpath('.') . DIRECTORY_SEPARATOR, '', $outfile);
		}
		if(strpos($outfile, ' ') !== false) $outfile = '"' . $outfile . '"';
		
		$execString = "mencoder {$this->_filename} -o $outfile";
		self::_initConfig();
		
		foreach(self::$_config->mencoder as $k => $v) {
			$execString .= " -$k $v";
		}

		exec($execString);
		return $result;
	}

}