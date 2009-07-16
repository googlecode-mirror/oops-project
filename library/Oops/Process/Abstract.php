<?php

/**
 * 
 * @author Dmitry Ivanov
 * 
 * @property $pid string Process Id
 * @property $state
 */
abstract class Oops_Process_Abstract {
	/**
	 * 
	 * @var Oops_Process_State
	 */
	protected $_state;
	/**
	 * 
	 * @var array
	 */
	protected $_data;
	
	private $_pid;

	/**
	 * 
	 * @param Oops_Process_Helper_Abstract $helper
	 * @return unknown_type
	 */
	private final function __construct(Oops_Process_Helper_Abstract $helper, string $pid) {
		$this->_helper = $helper;
		$this->_pid = $pid;
		$this->_trigger_constructed();
	}
	
	protected function _trigger_constructed() {
		//Some actions after construction
	}

	static final public function newInstance(Oops_Process_Helper_Abstract $helper) {
		// @todo Make pid generator
		$pid = $helper->generatePid();
		$process = self::__construct($helper);
		$process->_trigger_newInstance();
	}

	static final public function getInstance(string $pid) {
		// @todo Collect process helper and environment
		$process = self::__construct($pid);
//		$process->_data = $data;
		$process->_trigger_restore();
		
	}
}