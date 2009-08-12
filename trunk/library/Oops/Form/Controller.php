<?php

/**
 * 
 * @author Dmitry Ivanov
 * @property string $subject Form subject 
 *
 * Usage:
 * 	$formController = new Oops_Form_Controller('ObjectCreate');
 * 	$objectCreator = new Some_Class_Creator($someParams); //must implement Oops_Form_Observer_Interface
 * 	$request = $_REQUEST;
 * 
 *  $doUpdate = (bool) $_REQUEST['doUpdate'];
 *  $formController->attachObserver($objectCreator);
 *  $result = $formController->action($request, $doUpdate);
 */
class Oops_Form_Controller {
	/**
	 * Form subject, i.e. what does the form do.
	 * Ex. RegisterUser, ElementCreate, ElementEdit
	 * @var string
	 */
	protected $_subject;
	
	/**
	 * Event to throw before showing the form string id
	 * @var string
	 */
	protected $_onBeforeShowEvent;
	
	/**
	 * Event to throw before savinbg the form string id
	 * @var string
	 */
	protected $_onBeforeSaveEvent;
	
	/**
	 * Event to throw after saving the form string id
	 * @var string
	 */
	protected $_onAfterSaveEvent;
	
	/**
	 * 
	 * @var Oops_Form_Dispatcher
	 */
	protected $_dispatcher;

	/**
	 * Constructs new form controller.
	 * 
	 * Subject will be used to identify Form_Dispatcher instance
	 * Events will be named after subject as onBefore$subjectFormShow, onBefore$subjectFormSave and onAfter$subjectFormSave
	 * 
	 * @param string $subject form subject
	 * @return array Flags flagShowForm, flagErrors, flagUpdate, form data, errors and results of afterSave action 
	 */
	public function __construct($subject) {
		$this->subject = $subject;
	}

	protected function __set($name, $value) {
		switch($name) {
			case 'subject':
				$this->_subject = $value;
				$this->_onBeforeShowEvent = 'onBefore' . $value . 'FormShow';
				$this->_onBeforeSaveEvent = 'onBefore' . $value . 'FormSave';
				$this->_onAfterSaveEvent = 'onAfter' . $value . 'FormSave';
				return $value;
			default:
				return false;
		
		}
	}

	/**
	 * 
	 * @param Oops_Form_Observer_Interface $object
	 * @return bool
	 */
	public function attachObserver(Oops_Form_Observer_Interface $object) {
		$this->_initDispatcher();
		$this->_dispatcher->addObserver(array($object, 'onBeforeFormShow' ), $this->_onBeforeShowEvent);
		$this->_dispatcher->addObserver(array($object, 'onBeforeFormSave' ), $this->_onBeforeSaveEvent);
		$this->_dispatcher->addObserver(array($object, 'onAfterFormSave' ), $this->_onAfterSaveEvent);
		return true;
	}

	protected function _initDispatcher() {
		if(!isset($this->_dispatcher)) {
			$this->_dispatcher = new Oops_Form_Dispatcher($this->_subject);
		}
	}

	/**
	 * Do the work
	 * If there's 'Update' value in $request, try to store submitted values
	 * Returns definitions of the form structure, errors or detailed success status
	 * 
	 * @param array $request
	 * @param bool $doUpdate 
	 * @return array Response to be templated
	 */
	public function action($request, $doUpdate = false) {
		/**
		 * Return value
		 * @var array
		 */
		$returnValue = array();
		/**
		 * Dispather should be initialized to this point, but for now let's get sure
		 * @todo throw exception if there still no dispatcher?
		 */
		$this->_initDispatcher();
		
		/**
		 * Collect form specifications
		 * @v ar Oops_Form_Notification
		 */
		$beforeShowNotification = $this->_dispatcher->post($this->_onBeforeShowEvent);
		$returnValue['flagShowForm'] = false;
		$returnValue['flagUpdate'] = false;
		$returnValue['attached'] = $beforeShowNotification->getAttachedData();
		
		if($beforeShowNotification->isCancelled()) {
			$returnValue['flagErrors'] = true;
			$returnValue['status'] = false;
			$returnValue['errors'] = $beforeShowNotification->getFormErrors();
			return $returnValue;
		}
		
		$returnValue['flagShowForm'] = true;
		$returnValue['flagErrors'] = false;
		
		/**
		 * If there's update flag, try to save the sumbitted values
		 */
		if($doUpdate) {
			$returnValue['flagUpdate'] = true;
			
			/**
			 * Let attached observers check the request 
			 * @var Oops_Form_Notification
			 */
			$beforeSaveNotification = $this->_dispatcher->post($this->_onBeforeSaveEvent, $request);
			$attached = $beforeSaveNotification->getAttachedData();
			foreach($attached as $k => $v) {
				$returnValue['attached'][$k] = $v;
			}
			if($beforeSaveNotification->isCancelled()) {
				/**
				 * Notification was cancelled, collect errors
				 */
				$returnValue['status'] = false;
				$returnValue['flagErrors'] = true;
				$returnValue['errors'] = $beforeSaveNotification->getFormErrors();
			} else {
				/**
				 * If notification was not cancelled store submitted values 
				 */
				// @todo Let beforeSaveForm observer modify request?
				$afterSaveNotification = new Oops_Form_Notification($this->_onAfterSaveEvent, $request);
				$afterSaveNotification->attachData('beforeSaveNotification', $beforeSaveNotification);
				$afterSaveNotification = $this->_dispatcher->postNotification($afterSaveNotification);
				
				$returnValue['status'] = true;
				$attached = $afterSaveNotification->getAttachedData();
				foreach($attached as $k => $v) {
					$returnValue['attached'][$k] = $v;
				}
				
				$returnValue['flagShowForm'] = false;
				$returnValue['flagErrors'] = false;
			
			}
		} else {
		}
		
		return $returnValue;
	}
}