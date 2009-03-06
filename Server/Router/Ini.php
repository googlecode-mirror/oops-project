<?                                                           	
/**
* @package Oops
* @subpackage Server
*/

if(!defined("OOPS_Loaded")) die("OOPS not loaded");

require_once("Oops/Server/Router.php");

class Oops_Server_Router_Ini extends Oops_Server_Router {
	function __construct($filename) {
		set_error_handler(array($this,"_parseIniErrorHandler"));
		$data = parse_ini_file($filename,true);
		restore_error_handler();

		if($this->_parseError) {
			require_once("Oops/Error.php");
			Oops_Error::Raise("Error/ServerRouter/InvalidIniFile",$filename);
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