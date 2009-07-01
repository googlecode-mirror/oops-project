<?                                                           	
/**
* @package Oops
* @subpackage Server
*/

if(!defined("OOPS_Loaded")) die("OOPS not loaded");

require_once("Oops/Server/Router.php");

class Oops_Server_Router_Ini extends Oops_Server_Router {
	private $_parseError = false;

	function __construct($filename) {
		set_error_handler(array($this,"_parseIniErrorHandler"));
		$data = parse_ini_file($filename,true);
		restore_error_handler();

		if($this->_parseError) {
			trigger_error("Server_Router/InvalidIniFile/$filename", E_USER_WARNING);
			return;
		}
		foreach($data as $controller => $sections) {
			foreach($sections as $path=>$title) {
				parent::Set($path,$controller);
			}
		}
	}

	function _parseIniErrorHandler($errno,$errstr) {
		$this->_parseError = true;
		return true;
	}
}