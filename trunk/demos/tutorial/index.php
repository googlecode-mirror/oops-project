<?
error_reporting(E_ALL ^ E_NOTICE);
require_once("../../library/Oops/_Import.php");

require_once("Oops/Server.php");
Oops_Server::RunHttpDefault();