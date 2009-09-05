<?php

require_once 'Oops/Server/Response.php';

class tests_Response extends PHPUnit_Framework_TestCase {

	// {{{ tests
	function test_ResponseInit() {
		
		$response = new Oops_Server_Response();
		$this->assertFalse($response->isReady());
	}

	function test_ResponseRedirectAndReadyException() {
		
		$response = new Oops_Server_Response();
		
		$exceptionCaught = false;
		try {
			$response->redirect('http://www.somewhere.com/');
		} catch(Oops_Server_Exception $e) {
			switch($e->getCode()) {
				case OOPS_SERVER_EXCEPTION_RESPONSE_READY:
					$exceptionCaught = true;
					break;
				default:
					throw $e;
			}
		}
		
		$this->assertTrue($exceptionCaught);
		$this->assertEquals(302, $response->code);
		$this->assertTrue($response->isReady());
	}

	function test_ResponsePermanentRedirect() {
		
		$response = new Oops_Server_Response();
		
		$response->redirect('http://www.somewhere.com/', true, true);

		$this->assertTrue($response->isRedirect());
		$this->assertEquals(301, $response->code);
		$this->assertTrue($response->isReady());
	}
	
	function test_GetReady() {
		$response = new Oops_Server_Response();
		$response->getReady();
		$this->assertTrue($response->isReady());
		$this->assertEquals(200, $response->code);
	}
	
	function test_Headers() {
		$response = new Oops_Server_Response();
		$response->setHeader('key', 'value');
		$this->assertEquals('value', $response->getHeader('key'), 'getHeader(key) mismatch');
		$this->assertEquals(array('key' => 'value'), $response->getHeaders());
		$this->assertEquals("Key: value\n", $response->getHeadersAsString(false), 'headers as string do not match standards: ' . $response->getHeadersAsString(false));
		
		$response->setCode(403, true);
		
		$this->assertEquals("HTTP/1.x 403 Forbidden\nKey: value\n", $response->getHeadersAsString(), "response->getHeadersAsString with status line");
	}
	
// }}}
}

