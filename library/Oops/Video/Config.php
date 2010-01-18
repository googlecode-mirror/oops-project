<?php

require_once 'Oops/Config.php';

class Oops_Video_Config extends Oops_Config implements Countable, Iterator {

	public function __construct() {
		$data = array(
			'stats' => array(
				'vo' => 'null', 
				'ao' => 'null', 
				'noconsolecontrols' => '', 
				'nojoystick' => '', 
				'nolirc' => '', 
				'noar' => '', 
				'nomouseinput' => '', 
				'really-quiet' => '', 
				'identify' => '', 
				'frames' => 0), 
			'preview' => array(
				"ss" => "00:00:15",
				"ao" => 'null' ,
				"vf" => "screenshot", 
				'noconsolecontrols' => '', 
				'nojoystick' => '', 
				'nolirc' => '', 
				'noar' => '', 
				'nomouseinput' => '', 
				'really-quiet' => '', 
				"frames" => "1"), 
			'mencoder' => array(
				'ofps' => 25, 
				'of' => 'lavf', 
				'oac' => 'mp3lame', 
				'lameopts' => 'abr:br=32', 
				'srate' => 22050, 
				'ovc' => 'lavc', 
				'lavcopts' => 'vcodec=flv:keyint=50:vbitrate=100:mbd=2:mv0:trell:v4mv:cbp:last_pred=3', 
				'quiet' => '', 
				'really-quiet' => '', 
				'vf' => 'scale=320:240', 
				'af' => 'channels=1',
				'forceidx' => '',
				'endpos' => 10, // Comment this line to get full FLV
			), 
			'tmpdir' => '.tmp');
		
		parent::__construct($data);
	}
}