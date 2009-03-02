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
	* Points a filename for a given template name. If not found uses _default.php files;
	*
	* @static
	* @param string template name
	* @return string local php file name
	*/
	function getTemplateFilename($name) {
		static $templatesPath;
		if(!isset($templatesPath)) {
			if(defined("TEMPLATES_PATH")) $templatesPath = TEMPLATES_PATH;
			elseif(isset($_SERVER) && isset($_SERVER['DOCUMENT_ROOT'])) $templatesPath = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'.templates';
			else $templatesPath = '.templates';

			if(!is_dir($templatesPath)) {
				trigger_error("Invalid templates path", E_USER_ERROR);
				return false;
			}
		}

		/* From now, templates path must be defined */

		$name = trim($name,'/');
		if(!strlen($name)) return Oops_Error::Raise("Error/Template/EmptyTemplateName");

		$dirname = dirname($name);
		$basename = basename($name);

		$dirparts = explode('/',$dirname);

		while(sizeof($dirparts)) {
			$try = $templatePath . DIRECTORY_SEPARATOR . join(DIRECTORY_SEPARATOR, $dirparts) . DIRECTORY_SEPARATOR . $basename;
			if(file_exists($try)) return $try;
			array_pop($dirparts);
		}
		Oops_Error::Raise("Error/Template/NoDefaultTemplate",$name);
		return false;
	}
}
