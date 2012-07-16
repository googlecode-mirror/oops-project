<?php

class Oops_Curl {
	private $_curl;
	private $_fp;
	private $_url;
	
	const TIMEOUT = 6000;
	const CONNECTTIMEOUT = 1200;

	public function __construct($url = null) {
		$this->_curl = curl_init($url);
		$this->_url = $url;
		
		curl_setopt($this->_curl, CURLOPT_FAILONERROR, 1);
		curl_setopt($this->_curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->_curl, CURLOPT_TIMEOUT, self::TIMEOUT);
		curl_setopt($this->_curl, CURLOPT_CONNECTTIMEOUT, self::CONNECTTIMEOUT);
		curl_setopt($this->_curl, CURLOPT_POST, 0);
		
		$cfg = Oops_Server::getConfig();
		if(is_string($cfg->oops->proxy)) curl_setopt($this->_curl, CURLOPT_PROXY, $cfg->oops->proxy);
	}

	public function setFile(Oops_File $file) {
		$this->_fp = fopen($file, "w");
		curl_setopt($this->_curl, CURLOPT_FILE, $this->_fp);
	}
	
	public function setPostFields($data) {
		curl_setopt($this->_curl, CURLOPT_POST, true);
		curl_setopt($this->_curl, CURLOPT_POSTFIELDS, $data);
	}

	public function execute() {
		if(($result = curl_exec($this->_curl)) === false) {
			throw new Exception("Can't download from URL }$this->_url}");
		}
		return $result;
	}

}

