<?php

require_once 'Oops/Config.php';

class tests_Config extends PHPUnit_Framework_TestCase {

	// {{{ tests
	function test_ConfigConstruct() {
		$config = new Oops_Config(array('key' => 'value'));
		$this->assertEquals('value', $config->key);

		// @todo Consider implement ArrayAccess
		//$this->assertEquals('value', $config['key']);
		
		$this->assertEquals('', (string) $config->invalidKey);
	}
	
	function test_ConfigIni() {
		require_once 'Oops/Config/Ini.php';
		$config = new Oops_Config_Ini('./resources/sampleConfig.ini');
		
		$this->assertEquals('value', $config->key);
		$this->assertEquals(true, (bool) $config->booleanTrue);
		$this->assertEquals(false, (bool) $config->booleanFalse);
		
		$this->assertEquals('sectionValue', $config->section->sectionKey);
		$this->assertEquals('delimitedValue1', $config->section->delimited->key1);
		$this->assertEquals('delimitedValue2', $config->section->delimited->key2);

		$this->assertEquals('another Section Value', $config->another_section->sectionKey);
	}
	// }}}
}

