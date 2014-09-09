<?php

/**
 * @package Oops
 * @subpackage Server
 */

/**
 * Application server object is used to proceed incoming request, init
 * coresponding controller
 * and format the resulting output according to internal settings, data and
 * defined rules
 *
 * @property-read string $uri Request URI
 * @property-read array $uri_parts Request URI path parts (exploded path)
 * @property-read string $ext Request extension (view)
 * @property-read string $extension Request extension (view)
 * @property-read string $action Requested action
 * @property-read array $controller_params Controller params (not routed parts
 *                of the request path)
 * @property-read string $controller_ident Controller identification (routed
 *                path substring)
 * @property-read string $controllerClass Router controller class name
 * @property-read Oops_Server_Router $router Router instance
 * @property-read Oops_Controller $controller_instance Controller instance
 * @property-read Oops_Config $config Server's config instance
 */
class Oops_Server {
	
	/**
	 *
	 * @var string Definition of any filter to proceed the value returned by
	 *      controller
	 *      @protected
	 */
	protected $_view;
	
	/**
	 *
	 * @var string Requested action, default is 'index'
	 *      @protected
	 */
	protected $_action;
	
	/**
	 *
	 * @var string extension of requested script, 'php' by default
	 *      @protected
	 */
	protected $_extension;
	
	/**
	 *
	 * @var Oops_Controller Associated controller instance
	 */
	protected $_controller_instance;
	
	/**
	 *
	 * @var Oops_Config
	 */
	protected $_config;
	
	/**
	 *
	 * @var Oops_Error_Handler
	 */
	protected $_errorHandler;
	
	/**
	 *
	 * @var Oops_Server_Router
	 */
	protected $_router;
	
	/**
	 *
	 * @var Oops_Server_Response
	 */
	protected $_response;
	
	/**
	 *
	 * @var Oops_Server_Code_Handler
	 */
	protected $_responseCodeHandler;

	private function __construct() {
	}

	/**
	 * Singleton pattern implementation
	 *
	 * @return Oops_Server The current server object which is the last server in
	 *         stack
	 */
	public static function getInstance() {
		$instance = Oops_Server_Stack::last();
		if(!is_object($instance)) $instance = new Oops_Server();
		return $instance;
	}

	/**
	 * Use this static method to invoke a new server instance.
	 * Note that constructor is private.
	 *
	 * @param
	 *        	Oops_Config Config for the new server instance
	 * @return Oops_Server
	 */
	public static function newInstance($config = null) {
		$new = new Oops_Server();
		
		if(Oops_Server_Stack::size()) {
			$last = Oops_Server_Stack::last();
			$new->_config = $last->_config;
		} else {
			$new->_config = new Oops_Config_Default();
		}
		
		$new->configure($config);
		
		Oops_Server_Stack::push($new);
		
		return $new;
	}

	/**
	 *
	 * @return Oops_Config Server configuration object
	 */
	public static function getConfig() {
		$server = Oops_Server::getInstance();
		return $server->_config;
	}

	/**
	 *
	 * @return Oops_Server_Request Current request object
	 */
	public static function getRequest() {
		$server = Oops_Server::getInstance();
		return $server->_request;
	}

	/**
	 *
	 * @return Oops_Server_Response Current response object
	 */
	public static function getResponse() {
		$server = Oops_Server::getInstance();
		return $server->_response;
	}

	/**
	 *
	 * @return Oops_Server_Router
	 */
	public static function getRouter() {
		$server = Oops_Server::getInstance();
		if(!isset($server->_router)) $server->_initRouter();
		return $server->_router;
	}

	/**
	 *
	 * @param Oops_Config $config
	 *        	new configuration
	 * @param boolean $replace
	 *        	replace current config or merge with new one (default)
	 * @return void
	 */
	public function configure($config, $replace = false) {
		if(is_object($this->_config))
			$this->_config->mergeConfig($config);
		else
			$this->_config = $config;
	}

	/**
	 * Run the application and output the response
	 *
	 * @param Oops_Server_Request $request
	 *        	Request to dispatch
	 * @return void
	 */
	public function Run($request = null) {
		// @todo skip hander and use error log, or use static handler that will
		// log errors?
		$this->_errorHandler = new Oops_Error_Handler();
		
		try {
			
			if(!is_object($request)) {
				$this->_request = new Oops_Server_Request_Http();
				$this->_response = new Oops_Server_Response_Http();
			} else {
				$this->_request = $request;
				$this->_response = new Oops_Server_Response();
			}
			
			$this->_parseRequest();
			
			$this->_initView();
			
			$this->_routeRequest();
			
			// @todo try to find controller action, then do everything else
			$this->_initController();
			
			$data = $this->_controller_instance->Run();
			
			// @todo Let the view handler use getRequest and getResponse as it
			// need it
			
			$this->_view->In($data);
			$this->_view->Set('controller', $this->_router->controller);
			$this->_view->Set('uri', $this->_request->getUri());
			$this->_view->Set('ext', $this->_extension);
			$this->_view->Set('action', $this->_action);
			$this->_view->Set('uri_parts', $this->_uri_parts);
			
			$this->_response->setHeader("Content-type", $this->_view->getContentType());
			$this->_response->setBody($this->_view->Out());
		} catch(Oops_Server_Exception $e) {
			// Here we catch cases where response code was set by router or
			// controller (302, 301, 404 еtс)
			switch($e->getCode()) {
				case OOPS_SERVER_EXCEPTION_RESPONSE_READY:
					//
					// init response code handler here
					$responseCodeHandler = $this->loadResponseCodeHandler();
					$responseCodeHandler->handle($this->_response);
					if(strlen($e->getMessage())) {
						// @todo log exception
					}
					
					break;
				default:
					throw $e;
			}
		} catch(Exception $e) {
			trigger_error($e->getMessage(), E_USER_ERROR);
		}
		
		if((string) $this->_config->oops->errors2Headers && Oops_Debug::allow()) $this->_response->reportErrors($this->_errorHandler);
		
		// @todo refactor
		if((string) $this->_config->oops->errorlog->enabled) {
			Oops_Log_Error::report($this->_errorHandler, $this->_config->oops->errorlog->path);
		}
		restore_error_handler();
		
		return $this->_response;
	}

	public function loadResponseCodeHandler() {
		if(!isset($this->_responseCodeHandler)) $this->_responseCodeHandler = new Oops_Server_Code_Handler();
		return $this->_responseCodeHandler;
	}

	/**
	 *
	 * @param Oops_Server_Code_Handler $handler        	
	 */
	public function pushCodeHandler($handler) {
		$handler->setPrevHander($this->loadResponseCodeHandler());
		$this->_responseCodeHandler = $handler;
	}

	public function popCodeHandler() {
		if(!is_object($this->_responseCodeHandler)) {
			// @todo notice here
			return;
		}
		
		$prev = $this->_responseCodeHandler->getPrevHandler();
		if(is_object($prev)) {
			$this->_responseCodeHandler = $prev;
		} else {
			// @todo notice here
		}
	}

	/**
	 * Parses URI into parts, action and extension, also checks spelling using
	 * 301 to the right location
	 */
	protected function _parseRequest() {
		$oopsConfig = $this->_config->oops;
		$parts = explode("/", $this->_request->path);
		$coolparts = array();
		// Let's remove any empty parts. path//to/something/ should be turned
		// into path/to/something
		for($i = 0, $cnt = count($parts); $i < $cnt; $i++) {
			if(strlen($parts[$i])) $coolparts[] = (string) $oopsConfig->request_anycase ? $parts[$i] : strtolower($parts[$i]);
		}
		if(($cnt = count($coolparts)) != 0) {
			$last = $coolparts[$cnt - 1];
			if(($dotpos = strrpos($last, '.')) !== FALSE) {
				$ext = substr($last, $dotpos + 1);
				
				if(Oops_Server_View::isValidView($ext) || $oopsConfig->strict_views) {
					$this->_action = substr($last, 0, $dotpos);
					$this->_extension = $ext;
					array_pop($coolparts);
				}
			}
		}
		
		if(!isset($this->_action)) {
			// action should be index, content-type - php
			$this->_action = $oopsConfig->default_action;
			$this->_extension = $oopsConfig->default_extension;
		}
		
		// Let's compile the one-and-only expected request_uri for this kind of
		// request
		$expectedPath = sizeof($coolparts) ? '/' . join('/', $coolparts) . '/' : '/';
		if($this->_action != $oopsConfig->get('default_action') || $this->_extension != $oopsConfig->get('default_extension')) $expectedPath .= "{$this->_action}.{$this->_extension}";
		
		// If given path mismatch the one-and-only expected one, make a redirect
		// to the expected
		if($this->_request->path != $expectedPath) {
			$correctRequest = clone ($this->_request);
			$correctRequest->path = $expectedPath;
			
			$this->_response->redirect($correctRequest->getUrl(), true);
			return;
		}
		
		$this->_uri_parts = $coolparts;
	}

	/**
	 * Initializes router object using server's config
	 *
	 * @ignore
	 *
	 *
	 *
	 *
	 *
	 */
	protected function _initRouter() {
		$routerConfig = $this->_config->router;
		if(is_object($routerConfig)) {
			$routerClass = $routerConfig->class;
			if(class_exists($routerClass)) $this->_router = new $routerClass($routerConfig->source);
		}
		
		if(!is_object($this->_router)) {
			$this->_router = new Oops_Server_Router();
		}
	}

	/**
	 * Routes the contoroller for a given URI, and places contoller class name
	 * into $this->_controller var
	 * Found path is set into $this->_controller_ident, and all remaining parts
	 * into $this->_controller_params
	 *
	 * @todo Move controller_ident and controller_params to the Request object
	 *      
	 * @uses Oops_Server_Router
	 */
	function _routeRequest() {
		$this->_initRouter();
		if($this->_router->route($this->_request)) {
			// Routed OK
			$this->_controller_ident = trim($this->_router->foundPath, '/');
			$this->_controller_params = strlen($this->_router->notFoundPath) ? explode('/', $this->_router->notFoundPath) : array();
		} else {
			// We got 404 here, let's work it out
			$this->_response->setCode(404);
		}
	}

	/**
	 *
	 * @todo Set error response code if there's no controller class found
	 *      
	 *       Controller instantiation. Uses $this->_controller as a class name
	 *       (detected in DetectController), or starts default controller
	 *       Oops_Controller
	 */
	function _initController() {
		$ctrl = $this->_router->controller;
		if(!Oops_Loader::find($ctrl)) {
			trigger_error("Controller $ctrl not found", E_USER_ERROR);
			$this->_response->setCode(500);
			return;
		}
		$this->_controller_instance = new $ctrl();
	}

	/**
	 * Method is used to get private application params, decoration
	 * pattern here i think
	 */
	public function __get($what) {
		switch($what) {
			case 'uri':
				return $this->_request->getUri();
			case 'uri_parts':
				return $this->_uri_parts;
			case 'ext':
			case 'extension':
				return $this->_extension;
			case 'action':
				return $this->_action;
			case 'controller_params':
				return $this->_controller_params;
			case 'controller_ident':
				return $this->_controller_ident;
			case 'controller_instance':
				return $this->_controller_instance;
			case 'controller':
			case 'controllerClass':
				return $this->_router->controller;
			case 'router':
				return $this->_router;
			case 'config':
				return $this->_config;
		}
		return null;
	}

	/**
	 *
	 * @deprecated use magic properties
	 *            
	 */
	public function get($var) {
		return $this->__get($var);
	}

	/**
	 * Output processing class instantiation (view or presentation factory)
	 * Uses $this->_extension (from ParseURI) to choose a view class.
	 */
	protected function _initView() {
		$this->_view = Oops_Server_View::getInstance($this->_extension);
		
		if(!is_object($this->_view)) {
			// suggested view is not available
			$this->_response->setCode(415);
		}
	}

	/**
	 * Initial server run using http request and processing http response
	 * Uses default config location of application/config/oops.ini
	 * Outputs the response
	 *
	 * @return void
	 */
	public static function RunHttpDefault() {
		$server = Oops_Server::newInstance(new Oops_Config_Ini('./application/config/oops.ini'));
		$response = $server->Run();
		echo $response->toString();
	}
}
