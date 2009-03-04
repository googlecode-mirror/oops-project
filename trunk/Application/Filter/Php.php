<?
/**
* @package Oops
* @subpackage Application
*/

require_once("Oops/Application/Filter.php");
/**
* Application HTML output filter
*/
class Oops_Application_Filter_Php extends Oops_Application_Filter {
	function getContentType() {
		return "text/html";
	}

	/**
	* HTML filtration. Expects Oops_Controller as an Input. and ext, action and uri_parts as params
	*
	* @return string HTML output
	*/
	function Out() {
		$data = $this->_in->getData();

		$template =& $this->_getContentTemplate();
		$content = $template->Out($data);

		$filter =& $this->_getFilterTemplate();
		if($filter->isValid()) $content = $filter->Out($content);

		return $content;
	}

	function &_getContentTemplate() {
		$templateName = $this->_params['controller'].'/'.$this->_params['ext'];

		if(sizeof($this->_params['uri_parts'])) $templateName .= '/'.join('/',$this->_params['uri_parts']);
		$templateName .= "/".$this->_params['action'].".php";

		require_once("Oops/Template.php");
		$template =& Oops_Template::getInstance($templateName);
		return $template;
	}

	function &_getFilterTemplate() {
		$templateName = "_filter/".$this->_params['ext'];

		if(sizeof($this->_params['uri_parts'])) $templateName .= '/'.join('/',$this->_params['uri_parts']);
		$templateName .= "/".$this->_params['action'].".php";

		require_once("Oops/Template.php");
		$template =& Oops_Template::getInstance($templateName);
		return $template;
	}
}
?>