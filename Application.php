<?
/**
* @package Oops
* @subpackage Application
*/

if(!defined('OOPS_Loaded')) die("OOPS not found");

/**
* Application object is used to proceed incoming request, init coresponding controller 
* and format the resulting output according to internal settings, data and defined rules
*/
class Oops_Application extends Oops_Object {
	/**
	* @var string Application ID, reserved for future needs
	* @protected
	*/
	var $_app;

	/**
	* @var string Controller associated with a given request
	* @protected
	*/
	var $_controller;

	/**
	* @var string Requested content-type, defined by the extension of requested resource
	* @protected
	*/
	var $_output_content_type;

	/**
	* @var string Definition of any filter to proceed the value returned by controller
	* @protected
	*/
	var $_output_filter;

	/**
	* @var string Requested action, default is 'index'
	* @protected
	*/
	var $_action;

	/**
	* @var string extension of requested script, 'php' by default
	* @protected
	*/
	var $_ext;

	/**
	* @var Oops_Controller Associated controller instance
	* @access protected
	*/
	var $_controller_instance;

	/**
	*
	*/
	var $_config;
	

	function &getInstance($config = null) {
		static $instance;
		if(!isset($instance)) {
			$instance = new Oops_Application();
			$instance->configure($config);
		}
		return $instance;
	}

	function &getConfig() {
		$application =& Oops_Application::getInstance();
		if(!is_object($application->_config)) {
			require_once("Oops/Config.php");
			$application->configure(new Oops_Config);
		}
		return $application->_config;
	}

	function configure(&$config) {
		$this->_config = $config;

		$oopsConfig = $this->_config->get('oops');

		if(is_object($oopsConfig) && $incPath = $oopsConfig->get('include_path')) {
			set_include_path (
				$incPath . PATH_SEPARATOR . get_include_path()
			);
		}

		$routerConfig = $this->_config->get('router');
		if(is_object($routerConfig)) {
			$routerClass = $routerConfig->get('class');
			require_once("Oops/Loader.php");
			if(Oops_Loader::find($routerClass)) $this->_router = new $routerClass($routerConfig->get('source'));
		}
		if(!is_object($this->_router)) {
			require_once("Oops_Application_Map");
			$this->_router = new Oops_Application_Map();
		}
	}

	/**
	* Runs the application and outputs the response
	*
	* @param string Application ID, reserved for future needs
	* @return void
	*/
	function Run($request = null) {
		if(!is_object($request)) {
			require_once("Oops/Request/Http.php");
			$request = new Oops_Request_Http();
		}
		$this->_request = $request;

		$this->ParseURI();
		$this->DetectController();
		$this->InitController();
		$this->InitOutputFilter();

		if(!is_object($this->_output_filter)) {
			require_once("Oops/Debug.php");
			Oops_Debug::Dump($this->_controller_instance,"No output filter specified");
			return;
		}

		$this->_controller_instance->Run();


		$this->_output_filter->In($this->_controller_instance);
		$this->_output_filter->Set('controller',$this->_controller);
		$this->_output_filter->Set('uri',$this->_uri);
		$this->_output_filter->Set('ext',$this->_ext);
		$this->_output_filter->Set('action',$this->_action);
		$this->_output_filter->Set('uri_parts',$this->_uri_parts);

		header("Content-type: ".$this->_output_filter->getContentType());
		echo $this->_output_filter->Out();
	}


	/**
	* Parses URI into parts, action and extension, also checks spelling using 301 to the right location
	*
	* @todo - use some constants or settings to init default path details
	*/
	function ParseURI() {
		list($this->_uri,$this->_query_string) = explode('?',$_SERVER['REQUEST_URI'],2);

		$parts = explode("/",$this->_uri);
		$coolparts = array();
		//Let's remove any empty parts. path//to/something/ should be turned into path/to/something
		for($i=0,$cnt = sizeof($parts);$i<$cnt;$i++) {
			if(strlen($parts[$i])) $coolparts[] = strtolower($parts[$i]);
		}
		if($cnt = sizeof($coolparts)) {
			$last = $coolparts[$cnt-1];
			if(($dotpos = strrpos($last,'.')) !== FALSE) {
				$ext = substr($last,$dotpos+1);
				if(Oops_Application_Filter::isValidFilter($ext)) {
					$this->_output_content_type = $output_content_type;
					$this->_action = substr($last,0,$dotpos);
					$this->_ext = $ext;
					array_pop($coolparts);
				}
			}
		}

			if(!isset($this->_action)) {
				//action should be index, content-type - php
				$this->_action = 'index';
				$this->_ext = 'php';
			}


		//Let's compile the one-and-only expected request_uri for this kind of request
		$expectedUri = sizeof($coolparts)?'/'.join('/',$coolparts).'/':'/';
		if($this->_action != 'index' || $this->_ext != 'php') $expectedUri .= "{$this->_action}.{$this->_ext}";

		if($this->_uri != $expectedUri) {
			if(strlen($this->_query_string)) $expectedUri .= ('?'.$this->_query_string);
			header("HTTP/1.x 301 Moved Permanently");
			header("Location: $expectedUri");
			die();
		}

		$this->_uri_parts = $coolparts;
	}

	/**
	* Routes the contoroller for a given URI, and places contoller class name into $this->_controller var
	* Found path is set into $this->_controller_ident, and all remaining parts into $this->_controller_params
	*
	* @uses Oops_Application_Map
	*/
	function DetectController() {
		$this->_controller = $this->_router->getController($this->_uri_parts);
		$level = $this->_router->getFoundLevel();
		$this->_controller_ident = join('/',array_slice($this->_uri_parts,0,$level));
		$this->_controller_params = array_slice($this->_uri_parts,$level);
	}

	/**
	* Controller instantiation. Uses $this->_controller as a class name (detected in DetectController), or starts default controller Oops_Controller
	*/
	function InitController() {
		if(strlen($this->_controller)) {
			if(!Oops_Loader::find($this->_controller)) {
				Oops_Error::Raise("Error/Application/MissingConroller",$this->_controller);
				$this->_controller=false;
			}
		}
		if(!strlen($this->_controller)) {
			require_once("Oops/Controller.php");
			$this->_controller = "Oops_Controller";
			$this->_controller_instance = new Oops_Controller();
		}
		$ctrlClass = $this->_controller;
		$this->_controller_instance = new $ctrlClass();
	}

	/**
	* Method is used to get private application params
	*/
	function get($what) {
		switch($what) {
			case 'uri':
				return $this->_uri;	
			case 'uri_parts':
				return $this->_uri_parts;	
			case 'ext':
				return $this->_ext;
			case 'action':
				return $this->_action;
			case 'controller_params':
				return $this->_controller_params;
			case 'controller_ident':
				return $this->_controller_ident;
		}
	}

	/**
	* Output filter class instantiation (filter factory)
	* Uses $this->_ext (from ParseURI) to choose filter class.
	*/
	function InitOutputFilter() {
		$this->_output_filter =& Oops_Application_Filter::getInstance($this->_ext);
	}

}
?>