<?php
class Oops_Form_Field_Select extends Oops_Form_Field
{
    protected $_options;
    protected $_empty;
    
    public function __construct($name, array $options, $value = '', $class = '', $empty = false, $extra = array())
    {
        parent::__construct($name,$value,$class,$extra);  
        $this->_options = $options;   
        $this->_empty = $empty; 
    }
    protected function _make()
    {    
        $this->html = '<select ' . join(' ', $this->_params) . ' ' . $this->events->getList() . '>';
        
		if($this->_empty !== false && !isset($this->_options[''])) {
			$this->html .= '<option value=""' . ($this->_value === '' ? ' selected' : '') . '>';
			$this->html .= self::_formsafe($this->_empty) . '</option>';
		}
		foreach($this->_options as $optionValue => $optionLabel) {
			$selected = (strval($this->_value) === strval($optionValue)) ? ' selected' : '';
			$this->html .= '<option ' . self::_putValue($optionValue) . $selected . '>' . self::_formsafe($optionLabel) . '</option>';
		}
		
		return $this->html .= "</select>";
    }
}