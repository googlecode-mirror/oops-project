<?php
/**
 * @package Oops
 * @subpackage Template
 */

/**
 * Template helper
 */
class Oops_Template_Helper {

	/**
	 * Points a filename for a given template name. If not found uses _default.php files;
	 *
	 * @static
	 * @param string template name
	 * @return string local php file name
	 */
	public static function getTemplateFilename($name) {
		static $templatesPath = null;
		// @todo Check for config value 
		static $defaultBasename = '_default.php';
		
		if(!isset($templatesPath)) {
			$config = & Oops_Server::getConfig();
			$oopsConfig = $config->get('oops');
			if(is_object($oopsConfig)) $templatesPath = $oopsConfig->get('templates_path');
			
			if(!strlen($templatesPath)) $templatesPath = './application/templates';
			
			if(!is_dir($templatesPath)) {
				trigger_error("Invalid templates path", E_USER_ERROR);
				return false;
			}
		}
		
		/* From now, templates path must be defined */
		
		$name = trim($name, '/');
		if(!strlen($name)) {
			trigger_error("Template_Helper/EmptyTemplateName", E_USER_WARNING);
		}
		
		$dirname = dirname($name);
		$basename = basename($name);
		
		$dirparts = explode('/', $dirname);
		
		while(sizeof($dirparts)) {
			$try = $templatesPath . DIRECTORY_SEPARATOR . join(DIRECTORY_SEPARATOR, $dirparts) . DIRECTORY_SEPARATOR . $basename;
			if(file_exists($try)) return $try;
			$try = $templatesPath . DIRECTORY_SEPARATOR . join(DIRECTORY_SEPARATOR, $dirparts) . DIRECTORY_SEPARATOR . $defaultBasename;
			if(file_exists($try)) return $try;
			
			array_pop($dirparts);
		}
		trigger_error("Error/Template/NoDefaultTemplate/$name", E_USER_NOTICE);
		return false;
	}
}
