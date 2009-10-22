<?php

class Oops_Form_Field_File extends Oops_Form_Field {
	
	protected $_isFile = true;

	public function __construct($name, $value, $class, $extra) {
		parent::__construct($name, $value, $class, $extra);
	}

	protected function _make() {
		$this->html = '<input type="file" ' . join(' ', $this->_params) . '/>';
	}
}