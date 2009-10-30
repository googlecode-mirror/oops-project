<?php

/**
 * Defines simple image transformation
 * 
 * @property-read int $rotate Rotation counterclockwise angle
 *
 */
class Oops_Image_Preview_Scene {
	
	protected $_rotate = 0;
	
	/**
	 * 
	 * @param int $scene (0..3) scene number
	 * @return Oops_Image_Preview_Scene
	 */
	public static function getInstance($scene) {
		switch($scene) {
			case 3:
			case 2:
			case 1:
				break;
			default:
				$scene = 0;
		}
		
		static $scenes = array();
		if(!isset($scenes[$scene])) {
			$scenes[$scene] = new self($scene);
		}
		return $scenes[$scene];
	}
	
	protected function __construct($scene) {
		switch($scene) {
			case 3:
				$this->_rotate = 270;
				break;
			case 2:
				$this->_rotate = 180;
				break;
			case 1:
				$this->_rotate = 90;
				break;
		}
	}
	
	public function __get($var) {
		switch($var) {
			case 'rotate':
				return $this->_rotate;
		}
		return null;
	}
}