<?php

class tests_Request extends PHPUnit_Framework_TestCase {

	// {{{ tests
	function test_RequestCustom() {
		
		require_once 'Oops/Server/Request/Custom.php';
		
		$url = 'oops://local.host/some/path/action.view';
		$request = new Oops_Server_Request_Custom($url);
		
		/**
		 * test basic request parameters
		 */
		$this->assertEquals('oops', $request->scheme, 'Custom request scheme mismatch');
		$this->assertEquals('local.host', $request->host, 'Custom request host mismatch');
		$this->assertEquals('/some/path/action.view', $request->path, 'Custom request path mismatch');
		$this->assertEquals(80, $request->port, 'Custom request default port mismatch');
		$this->assertEquals('', $request->query, 'Custom request query mismatch');
		//test for url compilation
		$this->assertEquals($url, $request->getUrl(), 'Custom request URL compilation error');
		//test for uri compilation
		$this->assertEquals('/some/path/action.view', $request->getUri(), 'Custom request URI compilation error');
	}

	function test_RequestCustomQuery() {
		
		require_once 'Oops/Server/Request.php';
		
		$url = 'oops://local.host/some/path/action.view?port=99&path=/passed/var/path&requestKey=requestValue';
		$request = new Oops_Server_Request_Custom($url);
		$this->assertEquals('oops', $request->scheme, 'Custom request scheme mismatch');
		$this->assertEquals('local.host', $request->host, 'Custom request host mismatch');
		$this->assertEquals('/some/path/action.view', $request->path, 'Custom request path mismatch');
		$this->assertEquals($url, $request->getUrl(), 'Custom request URL compilation error');
		$this->assertEquals(80, $request->port, 'Custom request default port mismatch');
		
		$this->assertEquals('99', $request['port'], 'Custom request ArrayAccess INTEGER value error');
		$this->assertEquals('/passed/var/path', $request['path'], 'Custom request ArrayAccess STRING value error');
		$this->assertEquals('requestValue', $request->requestKey, 'Custom request object property access error (__get())');
		$this->assertEquals('requestValue', $request['requestKey'], 'Custom request ArrayAccess STRING value error');
		
		$this->assertEquals('/some/path/action.view?port=99&path=/passed/var/path&requestKey=requestValue', $request->getUri(), 'Custom request URI compilation error');
		$this->assertEquals('port=99&path=/passed/var/path&requestKey=requestValue', $request->query, 'Custom request query compilation error');
	
	}

	function test_RequestCustomQueryNoHost() {
		
		require_once 'Oops/Server/Request.php';
		//let's test for [] in query
		$request = new Oops_Server_Request_Custom('?foo=bar&bar[1]=bar1&bar[sally]=john');
		$this->assertEquals('bar', $request->foo, 'Custom URI request property access error');
		$this->assertEquals(array('1' => 'bar1', 'sally' => 'john'), $request->bar, 'Custrom URI request array parse error');
		$this->assertEquals(null, $request->host, 'Custom URI request HOST is not null');
		$this->assertEquals('?foo=bar&bar[1]=bar1&bar[sally]=john', $request->getUrl(), 'Custom URI request URL compilation error');
		$this->assertEquals('?foo=bar&bar[1]=bar1&bar[sally]=john', $request->getUri(), 'Custom URI request URI compilation error');
		$this->assertEquals('foo=bar&bar[1]=bar1&bar[sally]=john', $request->getQuery(), 'Custom URI request Query compilation error');
	}
	
// }}}
}

