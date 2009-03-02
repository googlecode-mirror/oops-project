<?                                                           	
/**
* @package Oops
* @subpackage Application
*/

if(!defined("OOPS_Loaded")) die("OOPS not loaded");

require_once("Oops/Application/Map.php");

class Oops_Application_Map_Ini extends Oops_Application_Map {
	function __construct($filename) {
		set_error_handler(array($this,"_parseIniErrorHandler"));
		$data = parse_ini_file($filename,true);
		restore_error_handler();

		if($this->_parseError) {
			require_once("Oops/Error.php");
			Oops_Error::Raise("Error/ApplicationMap/InvalidIniFile",$filename);
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