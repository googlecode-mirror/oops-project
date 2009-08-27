<?php
/**
* @package Oops
* @subpackage Config
* @author Dmitry Ivanov rockmagic@yandex.ru
* @license GNUv3
*/

require_once("Oops/Config.php");
/**
* Default Oops configuration
*
*/
class Oops_Config_Default extends Oops_Config {
	var $_parseError = false;

	/**
	* @param string Ini file name
	*/
	function __construct() {
		parent::__construct(
			array(
				"oops" => array(
					"default_action" => "index",
					"default_extension" => "php",
					"include_path" => "./application/library",
					"templates_path" => "./application/templates",
					"strict_views" => true,
				),
				"router" => array(
					"class" => "Oops_Server_Router",
					"source" => "",
				)
			)
		);
	}
}
