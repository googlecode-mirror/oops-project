<?php
class Oops_Form_Field_Checkbox extends Oops_Form_Field
{
    protected $_label;
    
    public function __construct($name, $label = false, $value = false, $class = '', $extra = array())
    {
        parent::__construct($name,$value,$class,$extra); 
         
        $this->_label = $label;   
       
    }
    protected function _make()
    {
       if($this->_value) 
           $this->_params[] = 'checked';
		
		$this->html= '<input type="checkbox" ' . join(' ', $this->_params) . '/>';
		
		if($this->_label !== false) {
			$this->html='<label>' . $this->html . ' ' . self::_formsafe($this->_label) . '</label>';
		}
    }
}