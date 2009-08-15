<?
/**
* @package Demos
* @subpackage Tutorial
*/

require_once 'Oops/Controller.php';

/**
* Custrom controller class example. The only work is to translate request params into output data
*/
class Custom_Controller extends Oops_Controller {
	function Run() {
		$this->Data['request'] = array();
		$keys = $this->getRequestKeys();
		foreach($keys as $k) {
			$this->Data['request'][$k] = $this->Request($k);
		}

		$server =& Oops_Server::getInstance();
		$this->Data['server'] = array(
			'controller_params' => $server->get('controller_params'),
			'controller_ident' => $server->get('controller_ident'),
		);
		return $this->Data;
	}
}

