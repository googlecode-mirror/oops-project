<?php
class Oops_Form_Field_Dateinterval extends Oops_Form_Field
{
    protected $_calDefaults;
    protected $_calParams;
    
    public function __construct($name, $value = array('',''), $class = '', $extra = array())
    {
        
        if(!isset($value[0]))
	        $value[0]='';
	    if(!isset($value[1]))
	        $value[1]='';  
        
        $fieldParams = array();
	    $this->_calParams = array();
	    
	    if(!isset($extra['id']))
		    $extra['id'] = 'id_' . $name;
		
		if(is_array($extra)) {
    		foreach($extra as $key => $value) {
    		    if(substr($key,0,4)!='cal_')
    			    $fieldParams[$key]  = $value;
    			else
    			    $this->_calParams[$key] = $value; 
    		}
		}   
	    	    
	    $this->_calDefaults = array(
		                        'cal_but_img'		=>    '/i/b.gif',
		                        'cal_but_id_f'		=>    'f_' . $name . '_date_event_icon',
	                            'cal_but_id_t'		=>    't_' . $name . '_date_event_icon',
		                        'cal_but_class'		=>    'icon date',
		                        'cal_format'		=>    '%Y-%m-%d',
		                        'cal_align'			=>    'Tl',
		                        'cal_singleClick'	=>    'true', 
		);  
		
		parent::__construct($name,$class,$value,$fieldParams);			
    }
    
    protected function _make()
    {        		 
		foreach($this->_calDefaults as $k => $v)
		    if(!isset($this->_calParams[$k]))
		        $this->_calParams[$k] = $v;
		     	
		$html= '<input id="'.$this->_name.'_f" type="text" ' . join(' ', $this->_params) . ' name="' . $this->_name . '[0]" value="'.$this->_value[0].'"/>
				<img src="'.$this->_calParams['cal_but_img'].'" class="'. $this->_calParams['cal_but_class'] . '" id="'. $this->_calParams['cal_but_id_f'] . '" />			
				<script type="text/javascript">
					try{
						Calendar.setup({
							inputField		: "' . $this->_name . '_f'                   . '",			
							ifFormat 		: "' . $this->_calParams['cal_format']      . '",
							button			: "' . $this->_calParams['cal_but_id_f']    . '", 
							align 			: "' . $this->_calParams['cal_align']       . '", 
							singleClick 	: '  . $this->_calParams['cal_singleClick'] . ',
						});
					}
					catch(e){debugger;}
				</script>
				<input id="' . $this->_name . '_t" type="text" ' . join(' ', $this->_params) . ' name="' . $this->_name . '[1]" value="'.$this->_value[1].'"/>
				<img src="'.$this->_calParams['cal_but_img'].'" class="'. $this->_calParams['cal_but_class'] . '" id="'. $this->_calParams['cal_but_id_t'] . '" />
				<script type="text/javascript">
					try{
						Calendar.setup({
							inputField		: "' . $this->_name . '_t'                   . '",			
							ifFormat 		: "' . $this->_calParams['cal_format']      . '",
							button			: "' . $this->_calParams['cal_but_id_t']    . '", 
							align 			: "' . $this->_calParams['cal_align']       . '", 
							singleClick 	: '  . $this->_calParams['cal_singleClick'] . ',
						});
					}
					catch(e){debugger;}
				</script>
				';
    }
}