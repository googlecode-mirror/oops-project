<?php

interface Oops_Session_Interface {

	public function _open($path, $name);

	public function _close();

	public function _read($ses_id);

	public function _write($ses_id, $data);

	public function _destroy($ses_id);

	public function _gc($life);
}