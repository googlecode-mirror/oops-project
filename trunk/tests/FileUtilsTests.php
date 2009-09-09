<?php

@define('DS', DIRECTORY_SEPARATOR);

require_once 'Oops/File/Utils.php';

class tests_FileUtils extends PHPUnit_Framework_TestCase {

	// tests {{{
	function test_FileUtils_SplitPath() {
		
		$string = 'abcdef0123456789a0b1c2d3e4f56789';
		
		$result = Oops_File_Utils::splitPath('PreFix', $string, 4);
		$expected = 'PreFix' . DS . 'abcd' . DS . 'ef01' . DS . '2345' . DS . '6789' . DS . 'a0b1' . DS . 'c2d3' . DS . 'e4f5' . DS . '6789';
		$this->assertEquals($expected, $result, "Split string to chunks with prefix");
		
		$result = Oops_File_Utils::splitPath('PreFix', $string, 4, 6);
		$expected = 'PreFix' . DS . 'abcdef' . DS . '0123' . DS . '4567' . DS . '89a0' . DS . 'b1c2' . DS . 'd3e4' . DS . 'f567' . DS . '89';
		$this->assertEquals($expected, $result, "Split string to chunks with prefix and skipping some leading chars");
		
		$result = Oops_File_Utils::splitPath('', $string, 17);
		$expected = 'abcdef0123456789a' . DS . '0b1c2d3e4f56789';
		$this->assertEquals($expected, $result, "Split string without prefix");
	}
	// }}}
}

