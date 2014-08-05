<?php

class Oops_Image_Preview_Config {
	protected static $_config;
	
	protected static function _init() {
		if(isset(self::$_config)) return;
		require_once 'Oops/Config/Ini.php';
		self::$_config = new Oops_Config_Ini('./application/config/preview.ini');
	}

	/**
	 * 
	 * @param string $previewType
	 * @return Oops_Config
	 */
	public static function getInstance($previewType) {
		self::_init();
		if(!self::$_config->{$previewType}->isValidConfig()) {
			throw new Oops_Image_Preview_Exception("Invalid preview type $previewType");
		}
		
		// @todo Consider cache these configs in static var
		$config = new Oops_Config(array('crop' => false, 'fill' => false, 'enlarge' => false));
		$config->mergeConfig(self::$_config->{$previewType});
		return $config;
	}
}