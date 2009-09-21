<?php
class Oops_Form_Field_Date extends Oops_Form_Field
{
    protected $_calDefaults;
    protected $_calParams;
    
    public function __construct($name, $value = '', $class = '', $extra = array())
    {
        if(!is_array($extra))
	        $extra = array();
        
	    $fieldParams = array();
	    $this->_calParams = array();    
	        
        if(!empty($extra)) {
    		foreach($extra as $key => $value) {
    		    if(substr($key,0,4)!='cal_')
    			    $fieldParams[$key]  = $value;
    			else
    			    $this->_calParams[$key] = $value; 
    		}
		}   
		
	    if(!isset($fieldParams['id']))
		    $fieldParams['id'] = 'id_' . $name;	    
        
        parent::__construct($name,$value,$class,$fieldParams);	
        
	   if(!isset($this->_extra['id']))
		    $this->_extra['id'] = 'id_' . $name;
		
		
	    $this->_calDefaults = array(
		                        'cal_but_img'		=>    '/i/b.gif',
		                        'cal_but_id'		=>    $name . '_date_event_icon',
		                        'cal_but_class'		=>    'icon date',
		                        'cal_format'		=>    '%Y-%m-%d',
		                        'cal_align'			=>    'Tl',
		                        'cal_singleClick'	=>    'true', 
		);  			
    }
    protected function _make()
    {		 
		foreach($this->_calDefaults as $k => $v)
		    if(!isset($this->_calParams[$k]))
		        $this->_calParams[$k] = $v;
		     	
		$this->html= '<input type="text" ' . join(' ', $this->_params) . '/>
				<img src="' . $this->_calParams['cal_but_img'].'" class="'. $this->_calParams['cal_but_class'] . '" id="'. $this->_calParams['cal_but_id'] . '" />
				<script type="text/javascript">
					try{
						Calendar.setup({
							inputField		: "' . $this->_extra['id']                   . '",			
							ifFormat 		: "' . $this->_calParams['cal_format']      . '",
							button			: "' . $this->_calParams['cal_but_id']      . '", 
							align 			: "' . $this->_calParams['cal_align']       . '", 
							singleClick 	: '  . $this->_calParams['cal_singleClick'] . ',
						});
					}
					catch(e){debugger;}
				</script>';
        
    }
}