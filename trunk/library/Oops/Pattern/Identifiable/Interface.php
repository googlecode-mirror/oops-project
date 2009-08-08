<?php

/**
 * 
 * @author Dmitry Ivanov
 * 
 * Interface for objects that could be constructed using single id
 *
 */
interface Oops_Pattern_Identifiable_Interface {
	/**
	 * Returns object id
	 * @return string|int Object ID
	 */
	public function getId();
}