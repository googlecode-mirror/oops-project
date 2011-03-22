<?php
if(!defined('PHPUnit_MAIN_METHOD')) {
	define('PHPUnit_MAIN_METHOD', 'tests_Oops_AllTests::main');
}

require_once 'PHPUnit/Autoload.php';
//require_once 'PHPUnit/TextUI/TestRunner.php';
require_once 'UtilsTests.php';
require_once 'ConfigTests.php';
require_once 'ServerTests.php';
require_once 'RequestTests.php';
require_once 'ResponseTests.php';
require_once 'FileUtilsTests.php';
require_once 'ValidateTests.php';
require_once 'Oops_Sql_SelectorTest.php';
require_once 'Oops_Uri_ParserTest.php';

class tests_Oops_AllTests {

	// {{{ main()

	public static function main() {
		PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	// }}}
	// {{{ suite()

	public static function suite() {
		$suite = new PHPUnit_Framework_TestSuite('Oops tests');
		$suite->addTestSuite('tests_Utils');
		$suite->addTestSuite('tests_Config');
		$suite->addTestSuite('tests_Server');
		$suite->addTestSuite('tests_Request');
		$suite->addTestSuite('tests_Response');
		$suite->addTestSuite('tests_FileUtils');
		$suite->addTestSuite('tests_Validate');
		$suite->addTestSuite('Oops_Sql_SelectorTest');
		$suite->addTestSuite('Oops_Uri_ParserTest');
		
		return $suite;
	}
	
// }}}
}

if(PHPUnit_MAIN_METHOD == 'tests_Oops_AllTests::main') {
	tests_Oops_AllTests::main();
}
