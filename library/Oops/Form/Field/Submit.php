<?php
class Oops_Form_Field_Submit extends Oops_Form_Field
{
    public function __construct($value,$class,$extra)
    {
        $name='';
        
        parent::__construct($name,$class,$value,$extra);      
    }
    protected function _make()
    {
        $this->html='<input type="rsubmit" ' . join(' ', $this->_params) . '/>';
    }
}