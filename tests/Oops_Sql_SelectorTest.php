<?php

require_once 'Oops\Sql\Selector.php';

require_once 'PHPUnit\Framework\TestCase.php';

/**
 * Oops_Sql_Selector test case.
 */
class Oops_Sql_SelectorTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * @var Oops_Sql_Selector
	 */
	private $Oops_Sql_Selector;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		parent::setUp();
		
		// TODO Auto-generated Oops_Sql_SelectorTest::setUp()
		

		$this->Oops_Sql_Selector = new Oops_Sql_Selector('testDatabase.testTable', 'testPrimaryKey', array(
			'testField1', 
			'testField2', 
			'testField3'));
		Oops_Sql::setLink(mysql_connect());
	
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		// TODO Auto-generated Oops_Sql_SelectorTest::tearDown()
		

		$this->Oops_Sql_Selector = null;
		
		parent::tearDown();
	}

	/**
	 * Constructs the test case.
	 */
	public function __construct() {
		// TODO Auto-generated constructor
	}

	/**
	 * Tests Oops_Sql_Selector->count()
	 */
	public function testCount() {
		$this->assertEquals('SELECT COUNT(*) FROM `testDatabase`.`testTable`', $this->Oops_Sql_Selector->count(true));
		
		$this->Oops_Sql_Selector->testField1 = 5;
		$this->assertEquals('SELECT COUNT(*) FROM `testDatabase`.`testTable` WHERE `testField1` = 5', $this->Oops_Sql_Selector->count(true));
		
		$this->Oops_Sql_Selector->resetWhere();
		$this->assertEquals('SELECT COUNT(*) FROM `testDatabase`.`testTable`', $this->Oops_Sql_Selector->count(true), 'resetWhereFailed');
	
	}

	/**
	 * Tests Oops_Sql_Selector->selectList()
	 */
	public function testSelect() {
		$this->assertEquals('SELECT `testPrimaryKey`, `testField1`, `testField2`, `testField3` FROM `testDatabase`.`testTable`', $this->Oops_Sql_Selector->select(true));
	}

	public function testInitFromConfig() {
		$config = new Oops_Config(array('table' => 'configTable', 'primaryKey' => 'id'));
		$selector = new Oops_Sql_Selector($config);
		$this->assertEquals('SELECT COUNT(*) FROM `configTable`', $selector->count(true));
	}

	/**
	 * Tests Oops_Sql_Selector->where()
	 */
	public function testWhere() {
		// TODO Auto-generated Oops_Sql_SelectorTest->testWhere()
		//$this->markTestIncomplete("where test not implemented");
		$this->Oops_Sql_Selector->resetAll();
		
		$this->Oops_Sql_Selector->where('testField2', Oops_Sql_Selector::CMP_GE, 88.2);
		$expected = "SELECT COUNT(*) FROM `testDatabase`.`testTable` WHERE `testField2` >= '88.2'";
		$this->assertEquals($expected, $this->Oops_Sql_Selector->count(true));
		
		$this->Oops_Sql_Selector->where('testField3', Oops_Sql_Selector::CMP_LT, 5);
		$expected = "SELECT COUNT(*) FROM `testDatabase`.`testTable` WHERE `testField2` >= '88.2' AND `testField3` < 5";
		$this->assertEquals($expected, $this->Oops_Sql_Selector->count(true));
		
		$this->Oops_Sql_Selector->resetWhere();
		$this->Oops_Sql_Selector->where('testField3', Oops_Sql_Selector::CMP_LT, 5);
		$expected = "SELECT COUNT(*) FROM `testDatabase`.`testTable` WHERE `testField3` < 5";
		$this->assertEquals($expected, $this->Oops_Sql_Selector->count(true));
	}

	/**
	 * Tests Oops_Sql_Selector->limit()
	 */
	public function testLimit() {
		$this->Oops_Sql_Selector->resetAll();
		$this->Oops_Sql_Selector->limit(10);
		$this->assertEquals("SELECT `testPrimaryKey`, `testField1`, `testField2`, `testField3` FROM `testDatabase`.`testTable` LIMIT 0, 10", $this->Oops_Sql_Selector->select(true));
		
		$this->Oops_Sql_Selector->limit(50, 10);
		$this->assertEquals("SELECT `testPrimaryKey`, `testField1`, `testField2`, `testField3` FROM `testDatabase`.`testTable` LIMIT 10, 50", $this->Oops_Sql_Selector->select(true));
		
		$this->Oops_Sql_Selector->limit(0);
		$this->assertEquals("SELECT `testPrimaryKey`, `testField1`, `testField2`, `testField3` FROM `testDatabase`.`testTable`", $this->Oops_Sql_Selector->select(true));
	}

	public function testJoin() {
		$this->Oops_Sql_Selector->resetAll();
		$jSelector = new Oops_Sql_Selector('testDatabase.joinTable', 'testField2', array(
			'testFieldX', 
			'testFieldY'));
		$this->Oops_Sql_Selector->join($jSelector, 'testField2', 'testField2', Oops_Sql_Selector::JOIN_LEFT);
		$this->assertEquals("SELECT COUNT(*) FROM `testDatabase`.`testTable` AS `t1` LEFT JOIN `testDatabase`.`joinTable` AS `t2` ON `t1`.`testField2` = `t2`.`testField2`", $this->Oops_Sql_Selector->count(true));
		
		$this->Oops_Sql_Selector->resetJoins();
		$this->Oops_Sql_Selector->join($jSelector, 'testField2', 'testField2', Oops_Sql_Selector::JOIN_LEFT, 'joiny');
		$this->assertEquals("SELECT COUNT(*) FROM `testDatabase`.`testTable` AS `t1` LEFT JOIN `testDatabase`.`joinTable` AS `joiny` ON `t1`.`testField2` = `joiny`.`testField2`", $this->Oops_Sql_Selector->count(true));
		
		$jj = clone $jSelector;
		$jSelector->join($jj, 'testFieldX', 'testFieldY', Oops_Sql_Selector::JOIN_INNER, 'joiny');
		$this->assertEquals("SELECT COUNT(*) FROM `testDatabase`.`testTable` AS `t1` LEFT JOIN (`testDatabase`.`joinTable` AS `joiny`, `testDatabase`.`joinTable` AS `t2`) ON `t1`.`testField2` = `joiny`.`testField2` WHERE `joiny`.`testFieldX` = `t2`.`testFieldY`", $this->Oops_Sql_Selector->count(true));
	}

	/**
	 * Tests Oops_Sql_Selector->resetAll()
	 */
	public function testResetAll() {
		// TODO Auto-generated Oops_Sql_SelectorTest->testResetAll()
	//$this->markTestIncomplete("resetAll test not implemented");
	

	//$this->Oops_Sql_Selector->resetAll(/* parameters */);
	

	}

	/**
	 * Tests Oops_Sql_Selector->resetWhere()
	 */
	public function testResetWhere() {
		// TODO Auto-generated Oops_Sql_SelectorTest->testResetWhere()
	//$this->markTestIncomplete("resetWhere test not implemented");
	

	//$this->Oops_Sql_Selector->resetWhere(/* parameters */);
	

	}

	/**
	 * Tests Oops_Sql_Selector->resetLimit()
	 */
	public function testResetLimit() {
		// TODO Auto-generated Oops_Sql_SelectorTest->testResetLimit()
	//$this->markTestIncomplete("resetLimit test not implemented");
	

	//$this->Oops_Sql_Selector->resetLimit(/* parameters */);
	

	}

	/**
	 * Tests Oops_Sql_Selector->resetJoins()
	 */
	public function testResetJoins() {
		// TODO Auto-generated Oops_Sql_SelectorTest->testResetJoins()
	//$this->markTestIncomplete("resetJoins test not implemented");
	

	//$this->Oops_Sql_Selector->resetJoins(/* parameters */);
	

	}

}

