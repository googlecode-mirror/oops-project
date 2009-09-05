<?php

class tests_Request extends PHPUnit_Framework_TestCase {

	// {{{ tests
	function test_RequestCustom() {
		
		require_once 'Oops/Server/Request.php';
		
		$url = 'oops://local.host/some/path/action.view';
		$request = new Oops_Server_Request_Custom($url);
		
		/**
		 * test basic request parameters
		 */
		$this->assertEquals('oops', $request->scheme);
		$this->assertEquals('local.host', $request->host);
		$this->assertEquals('/some/path/action.view', $request->path);
		$this->assertEquals(80, $request->port);
		$this->assertEquals('', $request->query);
		//test for url compilation
		$this->assertEquals($url, $request->getUrl());
		//test for uri compilation
		$this->assertEquals('/some/path/action.view', $request->getUri());
	}

	function test_RequestCustomQuery() {
		
		require_once 'Oops/Server/Request.php';
		
		$url = 'oops://local.host/some/path/action.view?port=99&path=/passed/var/path&requestKey=requestValue';
		$request = new Oops_Server_Request_Custom($url);
		$this->assertEquals('oops', $request->scheme);
		$this->assertEquals('local.host', $request->host);
		$this->assertEquals('/some/path/action.view', $request->path);
		$this->assertEquals($url, $request->getUrl());
		$this->assertEquals(80, $request->port);
		
		$this->assertEquals('99', $request['port']);
		$this->assertEquals('/passed/var/path', $request['path']);
		$this->assertEquals('requestValue', $request->requestKey);
		$this->assertEquals('requestValue', $request['requestKey']);
		
		$this->assertEquals('/some/path/action.view?port=99&path=/passed/var/path&requestKey=requestValue', $request->getUri());
		$this->assertEquals('port=99&path=/passed/var/path&requestKey=requestValue', $request->query);
	
	}

	function test_RequestCustomQueryNoHost() {
		
		require_once 'Oops/Server/Request.php';
		//let's test for [] in query
		$request = new Oops_Server_Request_Custom('?foo=bar&bar[1]=bar1&bar[sally]=john');
		$this->assertEquals('bar', $request->foo);
		$this->assertEquals(array('1' => 'bar1', 'sally' => 'john'), $request->bar);
		$this->assertEquals(null, $request->host);
		$this->assertEquals('?foo=bar&bar[1]=bar1&bar[sally]=john', $request->getUrl());
		$this->assertEquals('?foo=bar&bar[1]=bar1&bar[sally]=john', $request->getUri());
		$this->assertEquals('foo=bar&bar[1]=bar1&bar[sally]=john', $request->getQuery());
	}
	
// }}}
}

