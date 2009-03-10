<?
error_reporting(E_ALL ^ E_NOTICE);
require_once("../../library/Oops/_Import.php");

require_once("Oops/Server.php");
require_once("Oops/Config/Ini.php");


$server = new Oops_Server();
$server->configure(new Oops_Config_Ini('application/config/oops.ini'));
echo $server->Run();
