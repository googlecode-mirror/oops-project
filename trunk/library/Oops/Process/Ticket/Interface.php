<?php

interface Oops_Process_Ticket_Interface {
	/**
	 * Returns ticket timestamp
	 * 
	 * @return int Ticket timestamp as unixtime
	 */
	public function getTimestamp();
	
	/**
	 * Returns ticket creator
	 * 
	 * @return string User ID
	 */
	public function getUser();
}