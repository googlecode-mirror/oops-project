<?php
class Oops_Form_Field_Hidden extends Oops_Form_Field
{
    public function __construct($name,$value)
    {
        $class ='';
        $extra = false;
                
        parent::__construct($name,$value,$class,$extra);      
    }
    protected function _make()
    {
        $this->html='<input type="hidden" ' . join(' ', $this->_params) . '/>';
    }
}