<?php

class Oops_Image_Preview {

	protected function __construct($previewType) {
		require_once 'Oops/Image/Preview/Config.php';
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
			if($resizeCoeffX > 1) $resizeCoeffX = 1;
			if($resizeCoeffY > 1) $resizeCoeffY = 1;
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
		print_r(array(
		'$sourceWidth' => $sourceWidth,
			'previewWidth' => $previewWidth, 
		'previewHeight' => $previewHeight,
		'resizeWidth' => $resizeWidth,
		'resizeHeight' => $resizeHeight,
		'resizeCoeff' => $resizeCoeff,
		'resizeCoeffX' => $resizeCoeffX,
		'resizeCoeffY' => $resizeCoeffY,
		'rotate' => $rotate,
		'crop' => $crop,
		));
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
				//$frame->cropImage($previewWidth, $previewHeight, 0, 0);
			}
			$mgkPreview->cropImage($previewWidth, $previewHeight, 0, 0);
			//$mgkPreview->resetIterator();
			//$mgkPreview = $mgkPreview->deconstructImages();
		} else {
				$mgkSource->resizeImage($resizeWidth, $resizeHeight, Imagick::FILTER_LANCZOS, 1);
				
				$mgkPreview = new Imagick();
				$mgkPreview->newImage($previewWidth, $previewHeight, $backGroundPixel);

				$mgkPreview->compositeImage($mgkSource, Imagick::COMPOSITE_OVER, $previewPositionX, $previewPositionY);
		}
		
		$mgkPreview->stripImage();

		require_once 'Oops/File/Temporary.php';
		$previewFile = new Oops_File_Temporary();
		
		$mgkPreview->setImageFormat($mgkSource->getImageFormat());
		$mgkPreview->setCompressionQuality($mgkSource->getCompressionQuality());
		$mgkPreview->writeImages($previewFile->filename, true);

		print_r(array('mpWidth' => 
		$mgkPreview->getImageWidth(),
		'mpHeight' => $mgkPreview->getImageHeight()));
		
		return $previewFile;
		
		/**
		$typeOK = false;
		if(is_array($PreviewType)) {
			$TypeInfo = &$PreviewType;
			$typeOK = true; //���� ��� �����������
		} elseif(is_string($PreviewType)) {
			__autoload('ImagePreview_Type');
			$TypeInfo = ImagePreview_Type::Get($PreviewType);
			if($TypeInfo !== false && is_array($TypeInfo)) $typeOK = true;
		}
		if(!$typeOK) return false;
		
		$sceneOK = false;
		if(is_array($Scene)) {
			$SceneInfo = &$Scene;
			$sceneOK = true;
		} elseif(is_string($Scene)) {
			__autoload('ImagePreview_Scene');
			$SceneInfo = ImagePreview_Scene::Get($Scene);
			if($SceneInfo !== false && is_array($SceneInfo)) $sceneOK = true;
		}
		if(!$sceneOK) return false;
		
		//����� ���� �������� ���������, �������� ���� ���������, �������� �������������� ������
		__autoload('ImagePreview_Details');
		$PreviewInfo = ImagePreview_Details::Get($TypeInfo, $SceneInfo, $Source);
		
		if(!is_file(IPV_IMAGES_PATH . '/' . $PreviewInfo['SourceFile'])) {
			echo "invalid request";
			return false;
		}
		
		// ������� ��������       
		$mgkoriginal = new Imagick();
		$mgkpreview = new Imagick();
		
		// ������ �������� �� �����
		$mgkoriginal->readImage(IPV_IMAGES_PATH . '/' . $PreviewInfo['SourceFile']);
		//else return false;
		

		$Draws = array();
		// ����������, ������� �� �������
		if($TypeInfo['logo']) {
			$logoFile = IPV_IMAGES_PATH . "/" . $TypeInfo['logo'];
			if(is_file($logoFile)) {
				$mgklogo = new Imagick();
				$mgklogo->readImage($logoFile);
				$logowidth = $mgklogo->getImageWidth();
				$logoheight = $mgklogo->getImageHeight();
				if(!($logowidth + $logoheight)) $TypeInfo['logo'] = '';
			} else
				$TypeInfo['logo'] = '';
		}
		if($TypeInfo['Labels']) {
			foreach($TypeInfo['Labels'] as $k => $Label) {
				if($TypeInfo['Labels'][$k]['text'] == '#Extra#') $TypeInfo['Labels'][$k]['text'] = $PreviewInfo['Extra']['text'];
				$text = $TypeInfo['Labels'][$k]['text'];
				foreach($Label['Layers'] as $Set) {
					$draw = new ImagickDraw();
					if(isset($Set['Font'])) {
						$draw->setFont($Set['Font']);
					}
					if(isset($Set['StrokeColor'])) {
						$draw->setStrokeColor(new ImagickPixel($Set['StrokeColor']));
					} else {
						$draw->setStrokeWidth(0);
					}
					if(isset($Set['StrokeWidth'])) {
						$draw->setStrokeWidth($Set['StrokeWidth']);
					}
					if(isset($Set['StrokeAlpha'])) {
						$draw->setStrokeAlpha($Set['StrokeAlpha']);
					}
					if(isset($Set['FillColor'])) {
						$draw->setFillColor(new ImagickPixel($Set['FillColor']));
					} else {
						$draw->setFillAlpha(0);
					}
					
					if(isset($Set['TextUnderColor'])) {
						$draw->setTextUnderColor(new ImagickPixel($Set['TextUnderColor']));
					}
					
					if(isset($Set['Align'])) {
						$draw->setTextAlignment($Set['Align']);
					} else {
						$draw->setTextAlignment(Imagick::ALIGN_CENTER);
					}
					
					if(isset($Set['FontWeight'])) {
						$draw->setFontWeight($Set['FontWeight']);
					}
					if(isset($Set['FontStretch'])) {
						$draw->setFontStretch($Set['FontStretch']);
					}
					
					if(isset($Set['FontSize'])) {
						$draw->setFontSize($Set['FontSize']);
					}
					if(isset($Set['TextUnderColor'])) {
						$draw->setTextUnderColor(new ImagickPixel($Set['TextUnderColor']));
					}
					if(isset($Set['TextAntialias'])) {
						$draw->setTextAntialias($Set['TextAntialias']);
					}
					
					if(isset($Set['Gravity'])) $draw->setGravity($Set['Gravity']);
					
					$x = 0;
					$y = 0;
					if(isset($Set['x'])) $x = (float) $Set['x'];
					if(isset($Set['y'])) $y = (float) $Set['y'];
					
					$draw->annotation($x, $y, mb_convert_encoding($text, 'UTF-8'));
					
					$Draws[] = $draw;
				}
			
			}
		}
		 
		// ���������� ������ (���� �� GIF, �� 1 ���� (������))
		$nbr_images = ($PreviewInfo['format'] == 'gif') ? $mgkoriginal->getNumberImages() : 1;
		$nbr_images = min(20, $nbr_images);
		
		// ��������� � ������ ������������������ ��������
		// MagickResetIterator($mgkoriginal);
		$mgkoriginal->resetIterator();
		
		// ������� ������� ����� (background) - �� ���� ����������� ������ ��� ��������
		$origX = $mgkoriginal->getImageWidth();
		$origY = $mgkoriginal->getImageHeight();
		
		if(!($origX * $origY)) return false;
		
		if($origX * $origY > 200 * 200) $nbr_images = 1;
		
		// ���������, ���� ��������������� ��������
		$dstX = ($PreviewInfo['width'] - $PreviewInfo['previewimagewidth']) / 2;
		$dstY = ($PreviewInfo['height'] - $PreviewInfo['previewimageheight']) / 2;
		
		// ���� �������
		$bgColor = ($PreviewInfo['fillColor'] == 'crop') ? "" : $PreviewInfo['fillColor'];
		if($nbr_images && $PreviewInfo['format'] == 'gif') {
			$mgkPreview = $mgkoriginal->coalesceImages();
			foreach($mgkPreview as $frame) {
				$frame->thumbnailImage($PreviewInfo['previewimagewidth'], $PreviewInfo['previewimageheight']);
				$frame->setImagePage($PreviewInfo['width'], $PreviewInfo['height'], $dstX, $dstY);
				$frame->cropImage($PreviewInfo['width'], $PreviewInfo['height'], 0, 0);
			}
			$mgkPreview->cropImage($PreviewInfo['width'], $PreviewInfo['height'], 0, 0);
			$mgkpreview->resetIterator();
			$mgkpreview = $mgkpreview->deconstructImages();
		} elseif(!IPV_TRY_GIF_TRANSPARENT || $bgColor || $PreviewInfo['format'] != 'gif') { //���� ���� �������� ��� ��� ������� �� ������ �������
			if($nbr_images != 1) {
				$mgkoriginal->coalesceImages();
			}
			
			$mgkoriginal->resetIterator();
			
			$currentImage = 0;
			// ��������� ��� ����� ��� �������� ��������
			while($mgkoriginal->nextImage()) {
				//                MagickCropImage($mgkoriginal, $origX, $origY, 0, 0);
				$currentImage++;
				
				// ������������ ��������, ���� �����
				if($PreviewInfo['rotate']) $mgkoriginal->rotateImage(new ImagickPixel($bgColor ? $bgColor : "#FFFFFF"), $PreviewInfo['rotate']);
				
				// ������ ������ �������� ����� � ������������ �����������
				$mgkoriginal->resizeImage($PreviewInfo['previewimagewidth'], $PreviewInfo['previewimageheight'], Imagick::FILTER_LANCZOS, 1);
				
				// ��������� �� ��������� ���� ��� ���������� ����������
				$mgkpreview->setLastIterator();
				
				// ������� ����� ���� � ������������������, ��� � ��������
				$mgkpreview->newImage($PreviewInfo['width'], $PreviewInfo['height'], new ImagickPixel($bgColor ? $bgColor : "#FFFFFF"));
				
				$mgkpreview->setImageDelay($mgkoriginal->getImageDelay());
				
				// �������� �� ������� ���� �����������
				$mgkpreview->compositeImage($mgkoriginal, Imagick::COMPOSITE_OVER, $dstX, $dstY);
				
				// �������� ����, ���� �������
				if($TypeInfo['logo']) $mgkpreview->compositeImage($mgklogo, Imagick::COMPOSITE_OVER, $PreviewInfo['width'] - $logowidth, $PreviewInfo['height'] - $logoheight);
				
				// ���� ���� ��������� ����� �� �������� ��
				if(count($Draws)) {
					foreach($Draws as $draw) {
						$mgkpreview->drawImage($draw);
					}
				}
				
				$mgkpreview->cropImage($PreviewInfo['width'], $PreviewInfo['height'], 0, 0);
				
				if($currentImage >= $nbr_images) break;
				
			//                MagickMapImage($mgkpreview,$mgkoriginal);
			// ��������� � ���������� �����
			//                MagickNextImage($mgkoriginal);
			}
		
		} else { //���� ��������������� �� ����� �������
			// ��������� ��� ����� ��� �������� ��������
			$mgktmp = new Imagick();
			
			$i = 0;
			
			$qd = $mgktmp->getQuantumDepth();
			
			while($mgkoriginal->nextImage()) {
				
				// ��������������� ����� ��������
				$mgktmp = addImage($mgkoriginal);
				if($i) {
					if($qd == 8) {
						$mgktmp->setImageMatteColor($mgkoriginal->getImageMatteColor());
					} else {
						$mgktmp->setImageBackgroundColor("#FFFFFF");
					}
					$mgktmp->mosaicImages();
				}
				
				// ������������ ��������, ���� �����
				if($PreviewInfo['rotate']) $mgktmp->rotateImage("#FFFFFF", $PreviewInfo['rotate']);
				
				// ������ ������ �������� ����� � ������������ �����������
				$mgktmp->resizeImage($PreviewInfo['previewimagewidth'], $PreviewInfo['previewimageheight'], Imagick::FILTER_LANCZOS, 1);
				// �������� ����, ���� �������
				if($TypeInfo['logo']) $mgktmp->compositeImage($mgklogo, Imagick::COMPOSITE_OVER, $PreviewInfo['width'] - $logowidth, $PreviewInfo['height'] - $logoheight);
				
				// ���� ���� ��������� ����� �� �������� ��
				if(count($Draws)) {
					foreach($Draws as $draw) {
						$mgktmp->drawImage($draw);
					}
				}
				
				// ��������� �� ��������� ���� ��� ���������� ����������
				

				// �������� �� ������� ���� �����������
				$mgkpreview->setLastIterator();
				
				// ������� ����� ���� � ������������������, ��� � ��������
				$mgkpreview->addImage($mgktmp);
				
				// ��������� � ���������� �����
				$mgktmp->removeImage();
				$i++;
			}
		}
		
		// ���� ����������� ������ 1 (������������� GIF), �� ������ �����������
		if($nbr_images > 1) {
			$mgkpreview->resetIterator();
			$mgkpreview = $mgkpreview->deconstructImages();
		} else {
			$mgkpreview->stripImage();
		}
		
		/*
        if($PreviewInfo['format']=='jpeg') {
            MagickSetImageCompression($mgkpreview,MW_JPEGCompression);
        }

		
		// ������� �������
		$TargetDir = '';
		$dirs = explode("/", $Target);
		for($i = 0, $cnt = count($dirs); $i < $cnt - 1; $i++) {
			$TargetDir .= $dirs[$i] . DIRECTORY_SEPARATOR;
			if(!strlen($dirs[$i])) continue;
			if(!is_dir($TargetDir)) {
				@mkdir($TargetDir, 0777);
				@chmod($TargetDir, 0777);
			}
		}
		
		// ������ ������ � ����� �����������
		

		if($PreviewInfo['format'] == 'gif') {
			
			$NumColors = $mgkpreview->getImageColors();
			
			if($NumColors > 8) {
				if($NumColors > 128) {
					$NumColors = 128;
				} else {
					$i = floor(log($NumColors) / log(2));
					$NumColors = pow(2, $i);
				}
			}
			if($NumColors <= 256) {
				$mgkpreview->quantizeImages($NumColors, Imagick::COLORSPACE_TRANSPARENT, 0, false, false);
				$mgkpreview->setImageColorspace(Imagick::COLORSPACE_TRANSPARENT);
				$mgkpreview->setImageDepth(8);
				$mgkpreview->setImageType(Imagick::IMGTYPE_PALETTEMATTE);
			
			}
		}
		
		$mgkpreview->setFormat($PreviewInfo['format']);
		$mgkpreview->setCompressionQuality($TypeInfo['quality']);
		$mgkpreview->writeImages($Target, true);
		
		__autoload('ActualFile');
		ActualFile::Reg($PreviewInfo['AF_Src'], $Target, "ImagePreview");
		
		return array('dest' => $Target, 'format' => $PreviewInfo['format']);
		*/
	
	}

	/**
	 * Defines 
	 * 
	 * @param $SceneInfo
	 * @param $Source
	 * @param $width
	 * @param $height
	 * @param $orient
	 * @param $dontusesrc
	 *//*
	function Get($SceneInfo, $Source, $width = null, $height = null, $orient = null, $dontusesrc = false) {
		$SourceFile = $Source;
		$Extra = null;
		$AF_Src = null;
		
		if(isset($TypeInfo['ExtraClass'])) {
			$ManagerClass = $TypeInfo['ExtraClass'];
			//__autoload($ManagerClass);
			if(class_exists($ManagerClass)) {
				$Manager = & new $ManagerClass();
				list($SourceFile, $Extra, $AF_Src) = $Manager->Get($Source);
				if(!$SourceFile) return array();
			}
		}
		
		if(!$AF_Src) $AF_Src = IPV_IMAGES_PATH . '/' . $SourceFile;
		
		// �� ��������� ������ �� �����...
		$previewwidth = null;
		$previewheight = null;
		$previewformat = IPV_DEFAULT_FORMAT;
		$previewrotate = 0;
		
		$orientRotation = 0;
		
		// ���� ����������, ����� ������ � ������� �������� �� ������ �����
		if(!$width && !$height && !$dontusesrc) {
			if(false === (list($width, $height, $type) = @getimagesize(IPV_IMAGES_PATH . '/' . $SourceFile))) {
				//				echo("error: ".IPV_IMAGES_PATH.'/'.$SourceFile);
				echo "no image!";
				die();
			}
		}
		
		// ����������� ������� ������ ������ �� ����� ������������� �����
		$possibleFormats = array('gif', 'jpeg', 'png');
		preg_match('/\S+\.(\S+)$/', $Source, $m);
		$formatFromFilename = $m[1];
		if(strtolower($formatFromFilename) == 'jpg') $formatFromFilename = 'jpeg';
		if(in_array(strtolower($m[1]), $possibleFormats))
			$previewformat = strtolower($m[1]);
		else
			$previewformat = 'jpeg';
			// ������ exif ��� �������� �������� JPEG-�����������
		if(!$orient && !$dontusesrc && function_exists('exif_read_data')) {
			$exifData = @exif_read_data(IPV_IMAGES_PATH . '/' . $SourceFile);
			if(sizeof($exifData) && isset($exifData['Orientation']))
				$orient = $exifData['Orientation'];
			else
				$orient = 1;
			if($orient == 6)
				$orientRotation = 90;
			elseif($orient == 8)
				$orientRotation = 270;
			else
				$orientRotation = 0;
		}
		
		// ������� ������ �� ����� � ����������
		$previewrotate = $orientRotation + $SceneInfo['rotate'];
		$previewrotate = ($previewrotate % 360);
		
		// ����� ������� �����������, ������� ����� �� �������� �� ��������
		if($width && $height) {
			
			if(!(($previewrotate - 90) % 180)) {
				$temp = $width;
				$width = $height;
				$height = $temp;
			}
			
			// ���������� ����������� ���������� ������ ������ �� �������� �����������
			$resizeCoef = ($TypeInfo['fill'] == "crop") ? (max($TypeInfo['width'] / $width, $TypeInfo['height'] / $height)) : (min($TypeInfo['width'] / $width, $TypeInfo['height'] / $height));
			if(!$TypeInfo['enlarge'] && $resizeCoef > 1) $resizeCoef = 1;
			$previewimagewidth = round($width * $resizeCoef);
			$previewimageheight = round($height * $resizeCoef);
		
		}
		
		// ������� ���������� ������� ������ ������ ������ �� ���� ������
		if(isset($TypeInfo['fill']) && $TypeInfo['fill'] != '') {
			$previewwidth = $TypeInfo['width'];
			$previewheight = $TypeInfo['height'];
		} 

		// � ��������� ������ ����� ����� ������� ��������
		elseif($resizeCoef && $width && $height) {
			$previewwidth = $width * $resizeCoef;
			$previewheight = $height * $resizeCoef;
		}
		/*
		// ���� ����� ������� ��������, �� ��������� ������� ��������
		elseif($width && $height) {

			// ���������� ����������� ���������� ������ ������ �� �������� �����������
			$resizeCoef = ($TypeInfo['fill'] == "crop")?(max($TypeInfo['width']/$width,$TypeInfo['height']/$height)):(min($TypeInfo['width']/$width,$TypeInfo['height']/$height));
			if(!$TypeInfo['enlarge'] && $resizeCoef>1) $resizeCoef=1;
			$previewwidth = round($width * $resizeCoef);
			$previewheight = round($height * $resizeCoef);

		}

		
		// ���������� ���������
		return array(
			'format' => $previewformat, 
			'width' => $previewwidth, 
			'height' => $previewheight, 
			'rotate' => $previewrotate, 
			'fillColor' => $TypeInfo['fill'], 
			'previewimagewidth' => $previewimagewidth, 
			'previewimageheight' => $previewimageheight, 
			'Extra' => $Extra, 
			'SourceFile' => $SourceFile, 
			'AF_Src' => $AF_Src);
	
	}
	*/

} 
