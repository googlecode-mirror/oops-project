<?php
/**
 * @package Oops
 * @subpackage Server
 * @author Dmitry Ivanov <rockmagic@yandex.ru>
 */

/**
 * Load required classes
 */
require_once("Oops/Server/Request.php");

/**
 * HTTP request object representation
 */
class Oops_Server_Request_Http extends Oops_Server_Request {
	protected $_get = array();
	protected $_post = array();
	protected $_cookie = array();
	protected $_files = array();

	public function __construct() {
		$this->_get = $_GET;
		$this->_post = $_POST;
		$this->_cookie = $_COOKIE;
		
		/**
		 * strip slashes if magic_quotes is enabled
		 */
		if(get_magic_quotes_gpc()) {
			$this->_stripSlashesRecursive($this->_get);
			$this->_stripSlashesRecursive($this->_post);
			$this->_stripSlashesRecursive($this->_cookie);
		}
		
		$this->_proceedRequestFiles($_FILES, $this->_files);

		$this->_params = array_merge($this->_get, $this->_post, $this->_files);

		$parsed = parse_url($_SERVER['REQUEST_URI']);
		foreach($parsed as $name=>$value) $this->$name = $value;

		if(!isset($this->host)) $this->host = $_SERVER['HTTP_HOST'];
		if(!isset($this->port)) $this->port = $_SERVER['SERVER_PORT'];
		if(!isset($this->user) && isset($_SERVER['PHP_AUTH_USER'])) $this->user = $_SERVER['PHP_AUTH_USER'];
		if(!isset($this->pass) && isset($_SERVER['PHP_AUTH_PW'])) $this->pass = $_SERVER['PHP_AUTH_PW'];

	}

	/**
	 * Transform incomming files array
	 * 
	 * @todo Use objects to represent files
	 * @todo Handle asyncronous file uploads
	 *
	 * @param $files array Received files array, as formatted by PHP
	 * @param $keys array Current request keys stack
	 * @return void
	 */
	protected function _proceedRequestFiles($from, &$to) {
		foreach($from as $k => $v) {
			if(!is_array($v['name'])) {
				if($v['error']) {
					unset($to[$k]);
				} else {
					require_once 'Oops/File/Uploaded.php';
					$to[$k] = new Oops_File_Uploaded($v);
				}

			} else {
				if(!isset($to[$k]) || !is_array($to[$k])) $to[$k] = array();
				$subfiles = array();
				foreach(array_keys($v['name']) as $rk) {
					$subfiles[$rk] = array(
						'name' => $from[$k]['name'][$rk],
						'type' => $from[$k]['type'][$rk],
						'tmp_name' => $from[$k]['tmp_name'][$rk],
						'error' => $from[$k]['error'][$rk],
						'size' => $from[$k]['size'][$rk],
					);
				}
				$this->_proceedRequestFiles($subfiles,$to[$k]);
			}
		}
	}
	
	protected function _stripSlashesRecursive(array &$var) {
		if(!is_array($var)) return;
		foreach($var as $k=>$v) {
			if(is_string($var[$k])) $var[$k] = stripslashes($v);
			elseif(is_array($var[$k])) $this->_stripSlashesRecursive($var[$k]);
		}
	}

}
