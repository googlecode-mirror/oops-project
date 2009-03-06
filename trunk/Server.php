<?
/**
* @package Oops
* @subpackage Server
*/

if(!defined('OOPS_Loaded')) die("OOPS not found");

require_once("Oops/Object.php");

/**
* Application server object is used to proceed incoming request, init coresponding controller 
* and format the resulting output according to internal settings, data and defined rules
*/
class Oops_Server extends Oops_Object {
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
	* @var string Definition of any filter to proceed the value returned by controller
	* @protected
	*/
	var $_view;

	/**
	* @var string Requested action, default is 'index'
	* @protected
	*/
	var $_action;

	/**
	* @var string extension of requested script, 'php' by default
	* @protected
	*/
	var $_extension;

	/**
	* @var Oops_Controller Associated controller instance
	* @access protected
	*/
	var $_controller_instance;
/** ===cut to=== **/
	/**
	*
	*/
	var $_config;


	function __construct() {
		require_once("Oops/Server/Stack.php");
		if(Oops_Server_Stack::size()) {
			$last =& Oops_Server_Stack::last();
			$this->_config = $last->_config;
		} else {
			require_once("Oops/Config/Default.php");
			$this->_config = new Oops_Config_Default();
		}
		Oops_Server_Stack::push($this);
	}
	

	function &getInstance() {
		require_once("Oops/Server/Stack.php");
		$instance =& Oops_Server_Stack::last();
		if(!is_object($instance)) $instance = new Oops_Server();
		return $instance;
	}

	function &getConfig() {
		$server =& Oops_Server::getInstance();
		return $server->_config;
	}

	function &getRequest() {
		$server =& Oops_Server::getInstance();
		return $server->_request;
	}

	function &getResponse() {
		$server =& Oops_Server::getInstance();
		return $server->_response;
	}

	function configure(&$config) {
		$this->_config->mergeConfig($config);
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
			require_once("Oops_Server_Router");
			$this->_router = new Oops_Server_Router();
		}
	}

	/**
	* Run the application and output the response
	*
	* @param string Application ID, reserved for future needs
	* @return void
	*/
	function Run($request = null) {
		if(!is_object($request)) {
			require_once("Oops/Server/Request/Http.php");
			$request = new Oops_Server_Request_Http();
		}
		$this->_request = $request;

		$this->_parseRequest();
		$this->_initView();
		if(!is_object($this->_view)) {
			require_once("Oops/Debug.php");
			Oops_Debug::Dump($this->_request,"No output filter specified");
			return;
		}

		$this->_routeRequest();
		$this->_initController();


		$data = $this->_controller_instance->Run();


		$this->_view->In($this->_controller_instance);
		$this->_view->Set('controller',$this->_controller);
		$this->_view->Set('uri',$this->_uri);
		$this->_view->Set('ext',$this->_extension);
		$this->_view->Set('action',$this->_action);
		$this->_view->Set('uri_parts',$this->_uri_parts);

		header("Content-type: ".$this->_view->getContentType());
		echo $this->_view->Out();
	}


	/**
	* Parses URI into parts, action and extension, also checks spelling using 301 to the right location
	*
	*/
	function _parseRequest() {

//		$this->_request->verify();
/*
		switch($request->getScheme()) {
			case 'http':
				$response = new Oops_Server_Response_Http();
			case 'oops':
				$response = new Oops_Server_Response_Oops();
		}
*/


		$oopsConfig = $this->_config->get("oops");
		$parts = explode("/",$this->_request->uri);
		$coolparts = array();
		//Let's remove any empty parts. path//to/something/ should be turned into path/to/something
		for($i=0,$cnt = sizeof($parts);$i<$cnt;$i++) {
			if(strlen($parts[$i])) $coolparts[] = strtolower($parts[$i]);
		}
		if($cnt = sizeof($coolparts)) {
			$last = $coolparts[$cnt-1];
			if(($dotpos = strrpos($last,'.')) !== FALSE) {
				$ext = substr($last,$dotpos+1);
				require_once("Oops/Server/View.php");
				if(Oops_Server_View::isValidView($ext)) {
					$this->_action = substr($last,0,$dotpos);
					$this->_extension = $ext;
					array_pop($coolparts);
				}
			}
		}

		if(!isset($this->_action)) {
			//action should be index, content-type - php
			$this->_action = $oopsConfig->get('default_action');
			$this->_extension = $oopsConfig->get('default_extension');
		}


		//Let's compile the one-and-only expected request_uri for this kind of request
		$expectedUri = sizeof($coolparts)?'/'.join('/',$coolparts).'/':'/';
		if($this->_action != $oopsConfig->get('default_action') || $this->_extension != $oopsConfig->get('default_extension')) $expectedUri .= "{$this->_action}.{$this->_extension}";

		if($this->_request->uri != $expectedUri) {
			if(strlen($this->_request->query_string)) $expectedUri .= ('?'.$this->_request->query_string);
			header("HTTP/1.x 301 Moved Permanently");
			header("Location: $expectedUri");
			die();
		}

		$this->_uri_parts = $coolparts;

		return;

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
				if(Oops_Server_View::isValidView($ext)) {
					$this->_output_content_type = $output_content_type;
					$this->_action = substr($last,0,$dotpos);
					$this->_extension = $ext;
					array_pop($coolparts);
				}
			}
		}

			if(!isset($this->_action)) {
				//action should be index, content-type - php
				$this->_action = 'index';
				$this->_extension = 'php';
			}


		//Let's compile the one-and-only expected request_uri for this kind of request
		$expectedUri = sizeof($coolparts)?'/'.join('/',$coolparts).'/':'/';
		if($this->_action != 'index' || $this->_extension != 'php') $expectedUri .= "{$this->_action}.{$this->_extension}";

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
	* @uses Oops_Server_Router
	*/
	function _routeRequest() {
		$this->_controller = $this->_router->getController($this->_uri_parts);
		$level = $this->_router->getFoundLevel();
		$this->_controller_ident = join('/',array_slice($this->_uri_parts,0,$level));
		$this->_controller_params = array_slice($this->_uri_parts,$level);
	}

	/**
	* Controller instantiation. Uses $this->_controller as a class name (detected in DetectController), or starts default controller Oops_Controller
	*/
	function _initController() {
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
				return $this->_extension;
			case 'action':
				return $this->_action;
			case 'controller_params':
				return $this->_controller_params;
			case 'controller_ident':
				return $this->_controller_ident;
		}
	}

	/**
	* Output processing class instantiation (view or presentation factory)
	* Uses $this->_extension (from ParseURI) to choose a view class.
	*/
	function _initView() {
		require_once("Oops/Server/View.php");
		$this->_view =& Oops_Server_View::getInstance($this->_extension);
	}

}
?>