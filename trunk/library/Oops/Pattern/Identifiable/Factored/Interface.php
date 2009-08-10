<?php
require_once("Oops/Pattern/Identifiable/Interface.php");

interface Oops_Pattern_Identifiable_Factored_Interface extends Oops_Pattern_Identifiable_Interface {
	public static function getFactoryCallback();
}