<?
/**
* @package Oops
* @subpackage Server
*/

if(!defined('OOPS_Loaded')) die("OOPS not found");

/**
* Application server object is used to proceed incoming request, init coresponding controller 
* and format the resulting output according to internal settings, data and defined rules
*/
class Oops_Server {
	/**
	* @var string Server instance number, reserved for future needs
	* @protected
	*/
	protected $_app;

	/**
	* @var string Controller associated with a given request
	* @protected
	*/
	protected $_controller;

	/**
	* @var string Definition of any filter to proceed the value returned by controller
	* @protected
	*/
	protected $_view;

	/**
	* @var string Requested action, default is 'index'
	* @protected
	*/
	protected $_action;

	/**
	* @var string extension of requested script, 'php' by default
	* @protected
	*/
	protected $_extension;

	/**
	* @var Oops_Controller Associated controller instance
	* @access protected
	*/
	protected $_controller_instance;

	/**
	*
	*/
	protected $_config;

	protected $_errorHandler;


	private function __construct() {
		require_once("Oops/Error/Handler.php");
		$this->_errorHandler = new Oops_Error_Handler();

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
	

	public static function &getInstance() {
		require_once("Oops/Server/Stack.php");
		$instance =& Oops_Server_Stack::last();
		if(!is_object($instance)) $instance = new Oops_Server();
		return $instance;
	}

	public static function &newInstance() {
		return new Oops_Server();
	}

	public static function &getConfig() {
		$server =& Oops_Server::getInstance();
		return $server->_config;
	}

	public static function &getRequest() {
		$server =& Oops_Server::getInstance();
		return $server->_request;
	}

	public static function &getResponse() {
		$server =& Oops_Server::getInstance();
		return $server->_response;
	}

	public function configure(&$config) {
		$this->_config->mergeConfig($config);
		$oopsConfig = $this->_config->get('oops');
	}

	protected function _useConfig() {
		$oopsConfig = $this->_config->get('oops');
		if(is_object($oopsConfig)) {
			if((bool) $oopsConfig->get('register_autoload')) {
				require_once("Oops/_Autoload.php");
			}

			if($incPath = $oopsConfig->get('include_path')) {
				$currentIncludePath = get_include_path();
				if(!in_array($incPath,explode(PATH_SEPARATOR,$currentIncludePath))) {
					set_include_path (
						$incPath . PATH_SEPARATOR . get_include_path()
					);
				}
			}
		}
	}

	/**
	* Run the application and output the response
	*
	* @todo return the Response object, use special function to return a Response for additional processing of error codes (404, 415, 501)
	*
	* @param string Application ID, reserved for future needs
	* @return void
	*/
	public function Run($request = null) {
		if(!$this->_config->used) {
			$this->_config->used = true;
			$this->_useConfig();
		}

		if(!is_object($request)) {
			require_once("Oops/Server/Request/Http.php");
			$this->_request = new Oops_Server_Request_Http();

			require_once("Oops/Server/Response/Http.php");
			$this->_response = new Oops_Server_Response_Http();
		} else {
			$this->_request = $request;

			require_once("Oops/Server/Response.php");
			$this->_response = new Oops_Server_Response();
		}

		/**
		* @todo Use exceptions in PHP5 to check if response is ready (or trigger_error) or just chck the return and call some method for error or redirect response
		*/
		$this->_parseRequest();
		if($this->_response->isReady()) return $this->_response;

		$this->_initView();
		if($this->_response->isReady()) return $this->_response;

		$this->_routeRequest();
		if($this->_response->isReady()) return $this->_response;

		/**
		* @todo try to find controller action, then do everything else
		*/
		$this->_initController();
		if($this->_response->isReady()) return $this->_response;

		/** @todo Controller should return boolean, and data should be in response object?*/
		$data = $this->_controller_instance->Run();
		if($this->_response->isReady()) return $this->_response;


		/**
		* @todo Let the view handler use getRequest and getResponse as it need it
		*/
		$this->_view->In($data);
		$this->_view->Set('controller',$this->_router->controller);
		$this->_view->Set('uri',$this->_request->getUri());
		$this->_view->Set('ext',$this->_extension);
		$this->_view->Set('action',$this->_action);
		$this->_view->Set('uri_parts',$this->_uri_parts);

		$this->_response->setHeader("Content-type",$this->_view->getContentType());
		$this->_response->setBody($this->_view->Out());
		$this->_response->reportErrors($this->_errorHandler);
		restore_error_handler();

		return $this->_response;
	}


	/**
	* Parses URI into parts, action and extension, also checks spelling using 301 to the right location
	*
	*/
	protected function _parseRequest() {

		$oopsConfig = $this->_config->get("oops");
		$parts = explode("/",$this->_request->path);
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
				if(Oops_Server_View::isValidView($ext) || $oopsConfig->get('strict_views')) {
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
		$expectedPath = sizeof($coolparts)?'/'.join('/',$coolparts).'/':'/';
		if($this->_action != $oopsConfig->get('default_action') || $this->_extension != $oopsConfig->get('default_extension')) $expectedPath .= "{$this->_action}.{$this->_extension}";

		//If given path mismatch the one-and-only expected one, make a redirect to the expected
		if($this->_request->path != $expectedPath) {
			$correctRequest = clone($this->_request);
			$correctRequest->path = $expectedPath;

			$this->_response->redirect($correctRequest->getUrl(),true);
			return;
		}

		$this->_uri_parts = $coolparts;
	}

	protected function _initRouter() {
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
	* Routes the contoroller for a given URI, and places contoller class name into $this->_controller var
	* Found path is set into $this->_controller_ident, and all remaining parts into $this->_controller_params
	*
	* @todo Move controller_ident and controller_params to the Request object
	*
	* @uses Oops_Server_Router
	*/
	function _routeRequest() {
		$this->_initRouter();
		if($this->_router->route($this->_request)) {
			//Routed OK
		}
		else {
			//We got 404 here, let's work it out
			$this->_response->setCode(404);
		}
	}

	/**
	* @todo Set error response code if there's controller class not found
	*
	* Controller instantiation. Uses $this->_controller as a class name (detected in DetectController), or starts default controller Oops_Controller
	*/
	function _initController() {
		$ctrl = $this->_router->controller;
		if(!Oops_Loader::find($ctrl)) {
			$this->_response->setCode(500);
			$this->_response->setHeader("Oops-Error", "Controller $ctrl not found");
			return;
		}
		$this->_controller_instance = new $ctrl();
	}

	/**
	* @deprecated
	* @todo Move this to Request object
	*
	* Method is used to get private application params
	*/
	public function get($what) {
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
	protected function _initView() {
		require_once("Oops/Server/View.php");
		$this->_view =& Oops_Server_View::getInstance($this->_extension);

		if(!is_object($this->_view)) {
			//suggested view is not available
			$this->_response->setCode(415);
		}
	}

	/**
	* Initial server run using http request and processing http response (use it to prevent errors in index.php at php4
	*
	* @static
	*/
	public static function RunHttpDefault() {
		require_once("Oops/Config/Ini.php");
		$server =& new Oops_Server();
		$server->configure(new Oops_Config_Ini('application/config/oops.ini'));
		$response = $server->Run();
		echo $response->toString();
	}
	
}