<?php

require_once 'Oops/Uri/Parser.php';

/**
 * Oops_Uri_Parser test case.
 */
class Oops_Uri_ParserTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * Constructs the test case.
	 */
	public function __construct() {
		// TODO Auto-generated constructor
	}

	public function test_defaults() {
		$uriParser = new Oops_Uri_Parser('/path/to/resource/');
		$this->assertEquals('/path/to/resource/', $uriParser->path);
		$this->assertEquals('/path/to/resource/', $uriParser->expectedPath);
		$this->assertEquals('index', $uriParser->action);
		$this->assertEquals('php', $uriParser->extension);
		$this->assertEquals(array('path', 'to', 'resource'), $uriParser->parts);
	}

	public function test_custom() {
		$uriParser = new Oops_Uri_Parser('/path/to/resource/', 'myAction', 'myExtension');
		$this->assertEquals('/path/to/resource/', $uriParser->path);
		$this->assertEquals('/path/to/resource/', $uriParser->expectedPath);
		$this->assertEquals('myAction', $uriParser->action);
		$this->assertEquals('myExtension', $uriParser->extension);
		$this->assertEquals(array('path', 'to', 'resource'), $uriParser->parts);
	}
	
	public function test_spellcheck() {
		$uriParser = new Oops_Uri_Parser('/path/to//resource', 'myAction', 'myExtension');
		$this->assertEquals('/path/to//resource', $uriParser->path);
		$this->assertEquals('/path/to/resource/', $uriParser->expectedPath);
		$this->assertEquals('myAction', $uriParser->action);
		$this->assertEquals('myExtension', $uriParser->extension);
		$this->assertEquals(array('path', 'to', 'resource'), $uriParser->parts);
	}

	public function test_illegalExtension() {
		$uriParser = new Oops_Uri_Parser('path/to/resource/my.file', 'myAction', 'myExtension', array('php', 'html'));
		$this->assertEquals('path/to/resource/my.file', $uriParser->path);
		$this->assertEquals('/path/to/resource/my.file/', $uriParser->expectedPath);
		$this->assertEquals('myAction', $uriParser->action);
		$this->assertEquals('myExtension', $uriParser->extension);
		$this->assertEquals(array('path', 'to', 'resource', 'my.file'), $uriParser->parts);
	}
	
	public function test_legalExtension() {
		$uriParser = new Oops_Uri_Parser('path/to/resource/my.php', 'myAction', 'myExtension', array('php', 'html'));
		$this->assertEquals('path/to/resource/my.php', $uriParser->path);
		$this->assertEquals('/path/to/resource/my.php', $uriParser->expectedPath);
		$this->assertEquals('my', $uriParser->action);
		$this->assertEquals('php', $uriParser->extension);
		$this->assertEquals(array('path', 'to', 'resource'), $uriParser->parts);
	}
}

