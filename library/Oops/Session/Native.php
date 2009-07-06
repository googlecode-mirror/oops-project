<?
/**
 * 
 * @author Dmitry Ivanov
 *
 */
require_once("Oops/Session/Abstract.php");

class Oops_Session_Native extends Oops_Session_Abstract {
	public function __construct($config) {
		parent::__construct($config);
	}
}