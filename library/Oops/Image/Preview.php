<?php

class Oops_Image_Preview {

	protected function __construct($previewType) {
		$this->_config = Oops_Image_Preview_Config::getInstance($previewType);
	}

	/**
	 * Singleton pattern implementation
	 * 
	 * @param string $previewType Preview type id given in config
	 * @return Oops_Image_Preview
	 */
	public static function getInstance($previewType) {
		static $instances = array();
		if(!isset($instances[$previewType])) {
			$instances[$previewType] = new self($previewType);
		}
		return $instances[$previewType];
	}

	/**
	 * 
	 * @param Oops_File $source
	 * @param unknown_type $Scene
	 * return Oops_File_Temporary
	 */
	public function make($source, $scene = null) {
		// 1. Collect source stats
		if(!($source instanceof Oops_Image_File)) {
			require_once 'Oops/Image/File.php';
			$source = new Oops_Image_File($source);
		}
		// $source object contains image's width, height and orientation
		

		// 2. Get scene specification
		require_once 'Oops/Image/Preview/Scene.php';
		$sceneObject = Oops_Image_Preview_Scene::getInstance($scene);
		$rotate = $sceneObject->rotate;
		
		// 3. Now calculate target image dimensions, rotate angle, etc.
		// 3.1. First calculate rotation
		switch($source->orient) {
			case 8:
				$rotate += 90;
			case 3:
				$rotate += 90;
			case 6:
				$rotate += 90;
				$rotate = $rotate % 360;
		}
		
		// 3.2. Calculate target image dimensions
		$sourceWidth = $source->width;
		$sourceHeight = $source->height;
		
		if($rotate == 90 || $rotate == 270) {
			$tmp = $sourceWidth;
			$sourceWidth = $sourceHeight;
			$sourceHeight = $tmp;
		}
		
		$resizeCoeffX = $this->_config->width / $sourceWidth;
		$resizeCoeffY = $this->_config->height / $sourceHeight;
		
		if(!$this->_config->enlarge) {
			if($resizeCoeffX > 1 || $resizeCoeffX <= 0) $resizeCoeffX = 1;
			if($resizeCoeffY > 1 || $resizeCoeffY <= 0) $resizeCoeffY = 1;
		}
		
		if($this->_config->crop) {
			// Fit side with maximum resized rate and crop larger side
			$resizeCoeff = max($resizeCoeffX, $resizeCoeffY);
		} else {
			$resizeCoeff = min($resizeCoeffX, $resizeCoeffY);
		}
		
		$resizeWidth = round($resizeCoeff * $sourceWidth);
		$resizeHeight = round($resizeCoeff * $sourceHeight);
		
		// Now we have instructions for source modifications - rotate it, resize it
		// 3.3 Calculate preview instructions - crop it, fill it, etc
		

		$previewWidth = $resizeWidth;
		$previewHeight = $resizeHeight;
		
		$crop = false;
		if($this->_config->crop) {
			if($resizeWidth > $this->_config->width) {
				$previewWidth = $this->_config->width;
				$crop = true;
			}
			if($resizeHeight > $this->_config->height) {
				$previewHeight = $this->_config->height;
				$crop = true;
			}
		}
		
		$fillColor = null;
		$fill = false;
		if($this->_config->fill) {
			$previewWidth = $this->_config->width;
			$previewHeight = $this->_config->height;
			if($resizeWidth < $previewWidth || $resizeHeight < $previewHeight) {
				$fillColor = $this->_config->fill;
				$fill = true;
			}
		}
		
		$previewPositionX = ($previewWidth - $resizeWidth) / 2;
		$previewPositionY = ($previewHeight - $resizeHeight) / 2;
		
		/*
		 *  Now we have instructions:
		 *  	rotate angle
		 *  	resize dimensions
		 *  	whenever to crop
		 *  	whenever to fill and fill color
		 *  And result image width and height
		 */
		// Read source image to new Imagick object
		$mgkSource = new Imagick();
		$mgkSource->readImage($source->filename);
		$framesCount = $mgkSource->getNumberImages();
		if(strlen($this->_config->maxFrames)) $framesCount = min($framesCount, $this->_config->maxFrames);
		
		$backGroundPixel = is_null($fillColor) ? new ImagickPixel() : new ImagickPixel($fillColor);
		
		if($framesCount > 1) {
			$mgkPreview = $mgkSource->coalesceImages();
			if($rotate) $mgkPreview->rotateimage($backGroundPixel, $rotate);
			foreach($mgkPreview as $frame) {
				$frame->thumbnailImage($resizeWidth, $resizeHeight);
				$frame->setImagePage($previewWidth, $previewWidth, $previewPositionX, $previewPositionY);
			
			}
			$mgkPreview->cropImage($previewWidth, $previewHeight, 0, 0);
		
		} else {
			$mgkSource->resizeImage($resizeWidth, $resizeHeight, Imagick::FILTER_LANCZOS, 1);
			
			$mgkPreview = new Imagick();
			$mgkPreview->newImage($previewWidth, $previewHeight, $backGroundPixel);
			
			$mgkPreview->compositeImage($mgkSource, Imagick::COMPOSITE_OVER, $previewPositionX, $previewPositionY);
		}
		
		$mgkPreview->stripImage();
		
		$previewFile = new Oops_File_Temporary();
		
		$mgkPreview->setImageFormat($mgkSource->getImageFormat());
		$mgkPreview->setCompressionQuality($mgkSource->getCompressionQuality());
		$mgkPreview->writeImages($previewFile->filename, true);
		
		return $previewFile;
	}
} 
