<?php
class Oops_Form_Field_Multiselect extends Oops_Form_Field_Select
{
    protected $_options;
    protected $_empty;
    protected $values;
    
    public function __construct($name, array $options, array $values, $class = '', $empty = false, $extra = array())
    {
        $value='';
        parent::__construct($name,$class,$value,$extra);  
        $this->_values = $values;
        $this->_options = $options;   
        $this->_empty = $empty; 
    }
    protected function _make()
    {
        $this->html = '<select multiple ' . join(' ', $this->_params) . '>';
		foreach($this->_options as $optionValue => $optionLabel) {
			$selected = in_array($optionValue, $this->_values) ? ' selected' : '';
			$this->html .= '<option ' . self::_putValue($optionValue) . $selected . '>' . self::_formsafe($optionLabel) . '</option>';
		}
		
		return $this->html . '</select>';
    }
}