<?php

require_once("Oops/Pattern/Identifiable/Interface.php");

interface Oops_Pattern_Identifiable_Singleton_Interface extends Oops_Pattern_Identifiable_Interface {
	public static function &getInstance($id);
	private function __construct($id);
}