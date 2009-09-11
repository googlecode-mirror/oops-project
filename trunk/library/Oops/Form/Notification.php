<?php
/**
 * Class for handling form events notification
 * 
 * @author Dmitry Ivanov
 * @property-read array $formErrors Form notification errors
 *
 */
class Oops_Form_Notification extends Oops_Event_Notification {
	private $_formErrors = array();
	
	/**
	 * Cancels notification
	 * 
	 * @param $errorString Error description
	 * @param $data Error additional info
	 * @return unknown_type
	 */
	public function Cancel($errorString = null, $data = null) {
		$this->_formErrors[] = array('string' => $errorString, 'data' => $data);
		parent::Cancel($errorString);
	}
	
	/**
	 * Returns form errors registered with Cancel method
	 * @return array Form errors
	 */
	public function getFormErrors() {
		return $this->_formErrors;
	}
	
	/**
	 * Getter
	 * 
	 * @param string $var
	 * @return mixed
	 */
	public function __get($var) {
		switch($var) {
			case 'formErrors':
				return $this->_formErrors;
		}
		return null;
	}

}