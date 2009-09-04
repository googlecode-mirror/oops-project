<?

require_once 'Oops/Utils.php';

class tests_Utils extends PHPUnit_Framework_TestCase {

	// {{{ tests
	function test_ToArray() {
		$stringVar = 'string';
		$objectVar = new stdClass();
		$objectVar->prop = 'propValue';
		
		$testVar = $stringVar;
		Oops_Utils::ToArray($testVar);
		$this->assertEquals(array('string'), $testVar);
		
		$testVar = $objectVar;
		Oops_Utils::ToArray($testVar);
		$this->assertEquals(array($objectVar), $testVar);
	}

	function test_ToIntArray() {
		$stringVar = 'string';
		
		// One string to empty array
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
		
		$mixedVar = array(
			'string' => $stringVar, 
			'zero' => 0, 
			10, 
			'float' => 2.5, 
			'intstring' => '15', 
			'floatstring' => '3.5');
		
		$testVar = $mixedVar;
		Oops_Utils::ToIntArray($testVar);
		$this->assertEquals(array('zero' => 0, 10, 'intstring' => 15), $testVar);
		
		$testVar = $mixedVar;
		Oops_Utils::ToIntArray($testVar, false);
		$this->assertEquals(array('zero' => 0, 10, 'intstring' => 15), $testVar);
		
		$testVar = $mixedVar;
		Oops_Utils::ToIntArray($testVar, true);
		$this->assertEquals(array(
			'string' => 0, 
			'zero' => 0, 
			10, 
			'float' => 0, 
			'intstring' => 15, 
			'floatstring' => 0), $testVar);
	}

	function test_Tree2Line() {
		//Test with default service keys - children and level
		$tree = array(
			'ele1' => array(
				'name' => 'Name 1', 
				'value' => 'Value 1', 
				'children' => array(
					'ele1_1' => array('name' => 'Name 1-1'), 
					'ele1_2' => array(
						'name' => 'Name 1-2', 
						'children' => array()))), 
			'ele2' => array('name' => 'Name 2', 'children' => array()));
		$line = Oops_Utils::Tree2Line($tree);
		
		$expected = array(
			'ele1' => array(
				'name' => 'Name 1', 
				'value' => 'Value 1', 
				'level' => 0), 
			'ele1_1' => array('name' => 'Name 1-1', 'level' => 1), 
			'ele1_2' => array('name' => 'Name 1-2', 'level' => 1), 
			'ele2' => array('name' => 'Name 2', 'level' => 0));
		
		$this->assertEquals($expected, $line, 'tree to line error');
		
		//Test with more depth level
		$tree = array(
			'ele1' => array(
				'name' => 'Name 1', 
				'value' => 'Value 1', 
				'children' => array(
					'ele1_1' => array(
						'name' => 'Name 1-1', 
						'children' => array(
							'ele1_2' => array(
								'name' => 'Name 1-2', 
								'children' => array(
									'ele2' => array(
										'name' => 'Name 2', 
										'children' => array()))))))));
		
		$line = Oops_Utils::Tree2Line($tree);
		
		$expected = array(
			'ele1' => array(
				'name' => 'Name 1', 
				'value' => 'Value 1', 
				'level' => 0), 
			'ele1_1' => array('name' => 'Name 1-1', 'level' => 1), 
			'ele1_2' => array('name' => 'Name 1-2', 'level' => 2), 
			'ele2' => array('name' => 'Name 2', 'level' => 3));
		
		$this->assertEquals($expected, $line, 'Tree to line error');
	}

	function test_Line2Tree() {
		//Test with default service keys - children and level
		$line = array(
			'ele1' => array('name' => 'Name 1', 'value' => 'Value 1'), 
			'ele1_1' => array('name' => 'Name 1-1', 'parent' => 'ele1'), 
			'ele1_2' => array('name' => 'Name 1-2', 'parent' => 'ele1'), 
			'ele2' => array('name' => 'Name 2'));
		
		$expected = array(
			'ele1' => array(
				'name' => 'Name 1', 
				'value' => 'Value 1', 
				'parent' => null, 
				'children' => array(
					'ele1_1' => array(
						'name' => 'Name 1-1', 
						'parent' => 'ele1'), 
					'ele1_2' => array(
						'name' => 'Name 1-2', 
						'parent' => 'ele1'))), 
			'ele2' => array('name' => 'Name 2', 'parent' => null));
		
		$tree = Oops_Utils::Line2Tree($line);
		$this->assertEquals($expected, $tree, 'line 2 tree error');
		
		//Test with more depth level
		$line = array(
			'ele1' => array('name' => 'Name 1', 'value' => 'Value 1'), 
			'ele1_1' => array('name' => 'Name 1-1', 'parent' => 'ele1'), 
			'ele1_2' => array('name' => 'Name 1-2', 'parent' => 'ele1_1'), 
			'ele2' => array('name' => 'Name 2', 'parent' => 'ele1_2'));
		
		$expected = array(
			'ele1' => array(
				'name' => 'Name 1', 
				'value' => 'Value 1', 
				'parent' => null, 
				'children' => array(
					'ele1_1' => array(
						'name' => 'Name 1-1', 
						'parent' => 'ele1', 
						'children' => array(
							'ele1_2' => array(
								'name' => 'Name 1-2', 
								'parent' => 'ele1_1', 
								'children' => array(
									'ele2' => array(
										'parent' => 'ele1_2', 
										'name' => 'Name 2'))))))));
		
		$tree = Oops_Utils::Line2Tree($line);
		
		$this->assertEquals($expected, $tree, 'Line to tree more depth error');
	
	}
	// }}}
}

