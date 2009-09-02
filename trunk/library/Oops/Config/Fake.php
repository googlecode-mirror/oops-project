<?php
/**
 * @package Oops
 * @subpackage Config
 * @author Dmitry Ivanov rockmagic@yandex.ru
 * @license GNUv3
 */

require_once 'Oops/Config.php';

/**
 * Class for silent handling requests to invalid config keys 
 */
class Oops_Config_Fake extends Oops_Config {

	public function __construct() {
	}

	public function isValidConfig() {
		return false;
	}

	public function __get($key) {
		return $this;
	}

	public function __toString() {
		return '';
	}
}