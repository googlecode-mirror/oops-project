<?
/**
 * @package Oops
 * @subpackage Server
 * @author Dmitry Ivanov <rockmagic@yandex.ru>
 */
if(!defined("OOPS_Loaded")) die("OOPS not found");

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
		$this->_proceedRequestFiles($_FILES, $this->_files);

		$this->_params = array_merge($this->_files, $this->_post, $this->_get);

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
				if(isset($to[$k]) && is_array($to[$k])) {
					$to[$k] = array_merge($to[$k], $v);
				} else $to[$k] = $v;

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

}
