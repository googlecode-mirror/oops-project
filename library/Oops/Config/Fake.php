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

	public function __construct($allowModifications = false) {
		$this->_allowModifications = $allowModifications;
	}

	public function isValidConfig() {
		return false;
	}

	public function __toString() {
		return '';
	}
	
	protected function getData() {
		return $this->_data;
	}
}