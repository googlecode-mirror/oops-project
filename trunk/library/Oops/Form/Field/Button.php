<?php
class Oops_Form_Field_Button extends Oops_Form_Field
{
    public function __construct($value,$class,$extra)
    {
        $name='';
        
        parent::__construct($name,$class,$value,$extra);      
    }
    protected function _make()
    {
        $this->html='<input type="button" ' . join(' ', $this->_params) . '/>';
    }
}