<?

require_once 'Oops/Utils.php';

class tests_Utils extends PHPUnit_Framework_TestCase {
	function testToArray() {
		$stringVar = 'string';
		$testVar = $stringVar;
		Oops_Utils::ToArray($testVar);
		$this->assertEquals(array('string'), $testVar);

		$testVar = $stringVar;
		Oops_Utils::ToIntArray($testVar);
		$this->assertEquals(array(), $testVar);

		$testVar = $stringVar;
		Oops_Utils::ToIntArray($testVar, false);
		$this->assertEquals(array(), $testVar);

		$testVar = $stringVar;
		Oops_Utils::ToIntArray($testVar, true);
		$this->assertEquals(array(0), $testVar);

		$mixedVar = array($stringVar, 0, 10, 2.5, '15', '3.5');

		$testVar = $mixedVar;
		Oops_Utils::ToIntArray($testVar);
		$this->assertEquals(array(0, 10, 15), $testVar);

		$testVar = $mixedVar;
		Oops_Utils::ToIntArray($testVar, false);
		$this->assertEquals(array(0, 10, 15), $testVar);

		$testVar = $mixedVar;
		Oops_Utils::ToIntArray($testVar, true);
		$this->assertEquals(array(0, 0, 10, 0, 15, 0), $testVar);


		$mixedVar = array('string' => $stringVar, 'zero' => 0, 10, 'float' => 2.5, 'intstring' => '15', 'floatstring' => '3.5');

		$testVar = $mixedVar;
		Oops_Utils::ToIntArray($testVar);
		$this->assertEquals(array('zero' => 0, 10, 'intstring' => 15), $testVar);

		$testVar = $mixedVar;
		Oops_Utils::ToIntArray($testVar, false);
		$this->assertEquals(array('zero' => 0, 10, 'intstring' => 15), $testVar);

		$testVar = $mixedVar;
		Oops_Utils::ToIntArray($testVar, true);
		$this->assertEquals(array('string' => 0, 'zero' => 0, 10, 'float' => 0, 'intstring' => 15, 'floatstring' => 0), $testVar);
	}
}