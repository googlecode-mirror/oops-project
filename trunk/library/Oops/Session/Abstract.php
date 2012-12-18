<?php

abstract class Oops_Session_Abstract {
	protected $_cookieLifetime = 0;
	protected $_cookiePath = '/';
	protected $_cookieDomain = null;

	public function __construct($config) {
		if(!is_object($config)) {
			require_once("Oops/Config.php");
			$config = new Oops_Config();
		}
		// @todo make it better
		if(strlen($config->domain)) $this->_cookieDomain = $config->domain;
		if(strlen($config->path)) $this->_cookiePath = $config->path;
		if(strlen($config->lifetime)) $this->_cookieLifetime = $config->lifetime;
		session_set_cookie_params($this->_cookieLifetime, $this->_cookiePath, $this->_cookieDomain);				
		
		
		if(strlen($config->name)) session_name($config->name);
		if(strlen($config->cache_limiter)) {
			session_cache_limiter($config->cache_limiter);
		} else {
			session_cache_limiter('nocache');
		}

	}

	/**
	 * Sets current class as session save handler
	 * 
	 * @return bool True on success
	 */
	protected function setHandler() {
		if(!$this instanceof Oops_Session_Interface) {
			// @todo Consider throwing exception or error if session handler does not implement required interface
			return false;
		}
		
		return session_set_save_handler(
			array($this, '_open'),
			array($this, '_close'),
			array($this, '_read'),
			array($this, '_write'),
			array($this, '_destroy'),
			array($this, '_gc')
		);
	}


	public function _open($path, $name) {
		return TRUE;
	}
	public function _close() {
		return TRUE;
	}

}