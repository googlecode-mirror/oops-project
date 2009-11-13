<?php
class Oops_Form_Field_Checkbox extends Oops_Form_Field
{
    protected $_label;
    protected $_checked;
    
    public function __construct($name, $label = false, $value = false, $class = '', $extra = array())
    {
        $this->_checked = $value;
        parent::__construct($name,1,$class,$extra); 
         
        $this->_label = $label;   
       
    }
    protected function _make()
    {
       if($this->_checked) 
           $this->_params[] = 'checked';
		
		$this->html= '<input type="checkbox" ' . join(' ', $this->_params) . ' ' . $this->events->getList() . '/>';
		
		if($this->_label !== false) {
			$this->html='<label>' . $this->html . ' ' . self::_formsafe($this->_label) . '</label>';
		}
    }
}