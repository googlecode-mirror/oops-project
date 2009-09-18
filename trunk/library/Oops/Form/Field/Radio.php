<?php
class Oops_Form_Field_Radio extends Oops_Form_Field
{
    protected $_label;
    protected $_checked;
    
    public function __construct($name, $value, $label, $checked = false, $class = '', $extra = array())
    {
        parent::__construct($name,$value,$class,$extra); 
         
        $this->_label = $label;
        $this->_checked = $checked;   
       
    }
    
    
    protected function _make()
    {
        if($this->checked) 
            $this->params[] = 'checked';
		
		$this->html = '<input type="radio" ' . join(' ', $this->_params) . '/>';
		
		if($this->_label !== false) 
			$this->html = '<label>' . $this->html . ' ' . self::_formsafe($this->_label) . '</label>';
    }
}