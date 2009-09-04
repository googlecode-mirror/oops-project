<?php

require_once 'Oops/Server.php';

class tests_Server extends PHPUnit_Framework_TestCase {

	// {{{ tests
	function test_ServerNewInstance() {

		require_once 'Oops/Server/Stack.php';

		$stackSize = Oops_Server_Stack::size();

		//Instantiate without config
		$server = Oops_Server::newInstance();

		//Assert the stack has been increased
		$this->assertEquals($stackSize + 1, Oops_Server_Stack::size());

		Oops_Server_Stack::pop();

		$this->assertEquals($stackSize, Oops_Server_Stack::size());
	}

	function test_ServerGetInstance() {
		$server = Oops_Server::newInstance();
		$server2 = Oops_Server::getInstance();
		$this->assertEquals($server, $server2);

		//Try to configure server
		$server->configure(new Oops_Config(array('testKey' => 'testValue')));
		$this->assertEquals($server->config->testKey, $server2->config->testKey);

		//destroy servers
		$server = null;
		$server2 = null;
		Oops_Server_Stack::pop();

		$this->assertEquals(0, Oops_Server_Stack::size());
	}

	function test_ServerDefaultConfiguration() {
		$server = Oops_Server::newInstance();
		$this->assertNotEquals('', (string) $server->config->oops->default_action);
		$this->assertNotEquals('', (string) $server->config->oops->default_extension);
		$this->assertNotEquals('', (string) $server->config->router->class);
		
	}
	
	// }}}
}

