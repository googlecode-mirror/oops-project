<?php

/**
 * @package Oops
 * @subpackage Template
 */

/**
 * Template helper
 */
class Oops_Template_Helper {
	protected static $defaultBasename;
	protected static $templatesPath;

	/**
	 * Points a filename for a given template name. If not found uses _default.php files;
	 *
	 * @static
	 * @param string template name
	 * @return string local php file name
	 */
	public static function getTemplateFilename($name) {
		self::_initDefaultBasename();
		self::_initTemplatesPath();
		
		/* From now, templates path must be defined */
		
		$name = trim($name, '/');
		if(!strlen($name)) {
			throw new Exception("Template_Helper/EmptyTemplateName");
		}
		
		$dirname = dirname($name);
		$basename = basename($name);
		
		$dirparts = explode('/', $dirname);
		
		while(sizeof($dirparts)) {
			$try = self::$templatesPath . DIRECTORY_SEPARATOR . join(DIRECTORY_SEPARATOR, $dirparts) . DIRECTORY_SEPARATOR . $basename;
			if(file_exists($try)) return $try;
			$try = self::$templatesPath . DIRECTORY_SEPARATOR . join(DIRECTORY_SEPARATOR, $dirparts) . DIRECTORY_SEPARATOR . self::$defaultBasename;
			if(file_exists($try)) return $try;
			
			array_pop($dirparts);
		}
		throw new Exception("Template_Helper/NoDefaultTemplate :: $name");
		return false;
	}

	static protected function _initDefaultBasename() {
		if(isset(self::$defaultBasename)) return;
		if(strlen($configValue = Oops_Server::getConfig()->oops->default_basename))
			self::$defaultBasename = $configValue;
		else
			self::$defaultBasename = '_default.php';
	}

	static protected function _initTemplatesPath() {
		if(!isset(self::$templatesPath)) {
			$config = Oops_Server::getConfig();
			$oopsConfig = $config->get('oops');
			if(is_object($oopsConfig)) self::$templatesPath = $oopsConfig->get('templates_path');
			
			if(!strlen(self::$templatesPath)) self::$templatesPath = './application/templates';
			
			if(!is_dir(self::$templatesPath)) {
				throw new Exception("Template_Helper/Invalid templates path :: " . self::$templatesPath);
			}
		}
	}
}
