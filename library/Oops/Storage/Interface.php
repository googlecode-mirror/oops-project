<?php

interface Oops_Storage_Interface {
	/**
	 * 
	 * @param string|int $id Object identifier 
	 * @return object data
	 */
	public function get($id);
	
	/**
	 * Store object data
	 * 
	 * @param string|int $id Object identifier
	 * @param $value Object data
	 * @return bool True on success, false on error
	 */
	public function set($id, $value);
	
	/**
	 * Add an object. Will not replace data if identifier already exists
	 * 
	 * @param $id Object identifier
	 * @param $value Object data
	 * @return bool True on success, false if identifier already stored or error occured
	 */
	public function add($id, $value);
	
	/**
	 * Replace object data. Returns FALSE in case if object with such id doesn't exists. 
	 * @param $id
	 * @param $value
	 * @return bool
	 */
	public function replace($id, $value);
	
	/**
	 * Delete object data. Returns FALSE in case if object with such id doesn't exists.
	 * @param $id Object identifier
	 * @param $value Object data
	 * @return bool
	 */
	public function delete($id);
	
}