<?php
class Oops_Form_Field_Kaptcha extends Oops_Form_Field
{
    public function __construct($name,$class,$extra)
    {
        $value='';
        
        parent::__construct($name,$class,$value,$extra);      
    }
    protected function _make()
    {
        $this->html='<img src="/kaptcha?rnd' . rand(0,10000) . '" ' . join(' ', $this->_params) . '/>';
    }
}