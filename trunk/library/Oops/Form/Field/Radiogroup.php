<?php 
class Oops_Form_Field_Radiogroup extends Oops_Form_Field
{
    protected $_options;
    
    public function __construct($name = '', array $options, $value = null, $class = '', $extra = array())
    {
        parent::__construct($name,$value,$class,$extra);      
        $this->_options = $options;   
    }
    
    
    protected function _make()
    {
       $this->html ='';
       
		foreach($this->_options as $optionValue => $optionLabel) {
			$optionExtra = $this->_extra;
			if(isset($this->_extra['id'])) 
			    $optionExtra['id'] = $this->_extra['id'] . '_' . $optionValue;
			    
			 $this->html .= new Oops_Form_Field_Radio($this->_name, $optionValue, $optionLabel, $optionValue == $this->_value, $this->_class, $optionExtra);

		}
    }
}