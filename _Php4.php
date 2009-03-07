<?php
/**
* Clone an object function for PHP4
*
* @param object Object to clone
* @return object
*/
function clone(&$object) {
	return unserialize(serialize($object));
}