<?php

class Oops_Kcaptcha_Config {

	/**
	* @var string Do not change without changing font files!
	*/
	var $alphabet = "0123456789abcdefghijklmnopqrstuvwxyz";

	/**
	* @var string alphabet without similar symbols (o=0, 1=l, i=j, t=f) or digits
	*/
//	var $allowed_symbols = "23456789abcdeghkmnpqsuvxyz",	alphabet without similar symbols (o=0, 1=l, i=j, t=f)
	var $allowed_symbols = "0123456789";

	/**
	* @var int String length
	*/
	var $length = 6;

	/**
	* @var int Image width in pixels
	*/
	var $width = 120;

	/**
	* @var int Image height in pixels
	*/
	var $height = 60;

	/**
	*  @var int Symbol's vertical fluctuation amplitude divided by 2
	*/
	var $fluctualtion_amplitude = 4;	

	/**
	* @var boolean Increase safety by prevention of spaces between symbols
	*/
	var $no_spaces = true;

	/**
	* @var boolean Set to false to remove credits line. Credits adds 12 pixels to image height
	*/
	var $show_credits = false;

	/**
	* If empty, HTTP_HOST will be shown
	*/
	var $credits = '';

	/**
	* @var int JPEG quality of CAPTCHA image (bigger is better quality, but larger file size)
	*/
	var $jpeg_quality = 90;

	/**
	* @var string Default request key for captcha checker
	*/
	var $request_key = 'captcha';

	/**
	* @var string Storage key for captcha data (session key or cache key)
	*/
	var $storage_key = 'captcha';

	/**
	* @var int How many generated keys to store
	*/
	var $storage_maxkeys = 5;

	var $crypt_key = '78933364752029274656389264759027';
	var $crypt_iv = '94528045';

	function __construct() {
		$this->foreground = new stdClass();
		$this->foreground->red	= mt_rand(0,100);
		$this->foreground->green= mt_rand(0,100);
		$this->foreground->blue	= mt_rand(0,100);

		$this->background = new stdClass();
		$this->background->red	= mt_rand(200,255);
		$this->background->green= mt_rand(200,255);
		$this->background->blue	= mt_rand(200,255);

		$this->length = mt_rand(5,6);
	}
}