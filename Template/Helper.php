<?
/**
* @package Oops
*/
if(!defined('OOPS_Loaded')) die("OOPS not found");

/**
* Template helper
*/
class Oops_Template_Helper extends Oops_Object {

	/**
	* Suggests native template name to output object of a given class
	* 
	* @static
	* @param string class name
	* @return string template name, f.e. 
	*/
	function getClassTemplate($class) {
		if(strlen($class)) return "/classes/".str_replace('_','/',strtolower($class));
		else return "/classes/_default.php";
	}

	/**
	* Points a filename for a given template name. If not found uses _default.php files;
	*
	* @static
	* @param string template name
	* @return string local php file name
	*/
	function getTemplateFilename($name) {
		if(!defined("TEMPLATES_PATH")) return Oops_Error::Raise("Error/Template/PathNotDefined");
		if(!is_dir(TEMPLATES_PATH)) return Oops_Error::Raise("Error/Template/PathIsNotDir");

		$name = trim($name,'/');
		if(!strlen($name)) return Oops_Error::Raise("Error/Template/EmptyTemplateName");

		$dirname = dirname($name);
		$basename = basename($name);

		$dirparts = explode('/',$dirname);

		while(sizeof($dirparts)) {
			$try = TEMPLATES_PATH.DIRECTORY_SEPARATOR . join(DIRECTORY_SEPARATOR,$dirparts).DIRECTORY_SEPARATOR . $basename;
			if(file_exists($try)) return $try;
			array_pop($dirparts);
		}
		Oops_Error::Raise("Error/Template/NoDefaultTemplate",$name);
		return false;
	}
}
?>