<?php
interface Oops_Process_Interface {
	
	function start($input);
	function tick();
	function close();
	function getState();
	
}