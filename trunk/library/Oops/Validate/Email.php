<?php

class Oops_Validate_Email {

	/**
	 * Validates e-mail address
	 * 
	 * @param string $value Email to validate
	 * @return bool TRUE if $value is a valid email
	 */
	public function validate($value) {
		if(preg_match('/^[-0-9a-z_\.]+@[-0-9a-z^\.]+\.[a-z]{2,4}$/i', $value)) return true;
		return false;
	}
}