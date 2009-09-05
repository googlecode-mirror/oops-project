<?php
if(!defined('PHPUnit_MAIN_METHOD')) {
	define('PHPUnit_MAIN_METHOD', 'tests_Oops_AllTests::main');
}

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
require_once 'UtilsTests.php';
require_once 'ConfigTests.php';
require_once 'ServerTests.php';
require_once 'RequestTests.php';
require_once 'ResponseTests.php';

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
		
		return $suite;
	}
	
// }}}
}

if(PHPUnit_MAIN_METHOD == 'tests_Oops_AllTests::main') {
	tests_Oops_AllTests::main();
}
