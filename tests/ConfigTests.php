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

	function test_ConfigWriterIni() {
		require_once 'Oops/Config/Ini.php';
		require_once 'Oops/Config/Writer/Ini.php';
		$config = new Oops_Config_Ini('./resources/sampleConfig.ini', '.', true);
		$config->newSection = array('newKey1' => 'newValue1', 'newKey2' => 'newValue2');
		$config->newRootKey = 'New root value';
		
		$writer = new Oops_Config_Writer_Ini();
		$writer->setFilename('./resources/sampleConfigWritten.ini');
		$writer->setConfig($config);

		$this->assertTrue($writer->write());

		$written = new Oops_Config_Ini('./resources/sampleConfigWritten.ini');
		
		//Check new values
		$this->assertEquals($written->newSection->newKey1, 'newValue1');
		$this->assertEquals($written->newSection->newKey2, 'newValue2');
		$this->assertEquals($written->newRootKey, 'New root value');
		
		$this->assertEquals('value', $written->key);
		$this->assertEquals(true, (bool) $written->booleanTrue);
		$this->assertEquals(false, (bool) $written->booleanFalse);
		
		$this->assertEquals('sectionValue', $written->section->sectionKey);
		$this->assertEquals('delimitedValue1', $written->section->delimited->key1);
		$this->assertEquals('delimitedValue2', $written->section->delimited->key2);
		
		unlink('./resources/sampleConfigWritten.ini');
	}
	// }}}
}

