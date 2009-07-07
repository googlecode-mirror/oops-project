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
	var $_get = array();
	var $_post = array();
	var $_cookie = array();
	var $_files = array();

	public function __construct() {
		$this->_get = $_GET;
		$this->_post = $_POST;
		$this->_cookie = $_COOKIE;
		$this->_files = $_FILES;
		
		$this->_params = array_merge($this->_post,$this->_get);
		$this->_proceedRequestFiles($this->_files);
		
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
	 * @param $files array Received files array, as formatted by PHP
	 * @param $keys array Current request keys stack
	 * @return void
	 */
	protected function _proceedRequestFiles($files, $keys=array()) {
		foreach($files as $k => $v) {
			$keys[]=$k;
			if(!is_array($v['name'])) {
				//add to Request
				$reqRef =& $this->_params;
				for($i=0, $c = sizeof($keys); $i < $c; $i++) {
					$reqRef =& $reqRef[$keys[$i]];
				}
				if(is_array($reqRef)) $reqRef = array_merge($reqRef,$v);
				else $reqRef = $v;
			} else {
				$subfiles = array();
				foreach(array_keys($v['name']) as $rk) {
					$subfiles[$rk] = array(
						'name' => $files[$k]['name'][$rk],
						'type' => $files[$k]['type'][$rk],
						'tmp_name' => $files[$k]['tmp_name'][$rk],
						'error' => $files[$k]['error'][$rk],
						'size' => $files[$k]['size'][$rk],
					);
					$this->_proceedRequestFiles($subfiles,$keys);
				}
			}
			array_pop($keys);
		}
	}
	
}
