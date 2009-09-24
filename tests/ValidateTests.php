<?php


class tests_Validate extends PHPUnit_Framework_TestCase {

	// tests {{{
	function test_Validate_Email() {
		require_once 'Oops/Validate/Email.php';
		$validator = new Oops_Validate_Email();
		$this->assertFalse($validator->validate(''));
		$this->assertTrue($validator->validate('rockmagic.net@gmail.com'));
		$this->assertTrue($validator->validate('di@mail.nnov.ru'));
		$this->assertFalse($validator->validate('one@domain.info, to@domain.info'));
		$this->assertFalse($validator->validate('one@two@domain.info'));
		$this->assertTrue($validator->validate('valid@domain.info'));
		$this->assertFalse($validator->validate('in^alid@email.com'));
		
	}
	// }}}
}

