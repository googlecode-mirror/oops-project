<?php

class Oops_Kcaptcha_Controller extends Oops_Controller {

	public function Run() {
		require_once 'Oops/Kcaptcha/Image.php';
		$captcha = new Oops_Kcaptcha_Image();
		$img = $captcha->action();
		
		require_once 'Oops/File/Temporary.php';
		$file = new Oops_File_Temporary();
		
		if(function_exists("imagejpeg")) {
			$this->_response->setHeader("Content-type", "image/jpeg");
			imagejpeg($img, $file->filename, $captcha->config->jpeg_quality);
		} elseif(function_exists("imagegif")) {
			$this->_response->setHeader("Content-type", "image/gif");
			imagegif($img, $file->filename);
		} elseif(function_exists("imagepng")) {
			$this->_response->setHeader("Content-type", "image/png");
			imagepng($img, $file->filename);
		}

		$this->_response->setBody($file->getContents());
		$this->_response->setCode(200);
	}
}