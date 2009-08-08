<?php

interface Oops_Pattern_Identifiable_Singleton_Interface {
	public static function &getInstance($id);
	private function __construct($id);
}