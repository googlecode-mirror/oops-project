<?php
interface Oops_Process_Property_Interface {
	
	function restore($storedData);
	
	/**
	 * 
	 * @return mixed stored data
	 */

	function store();

}