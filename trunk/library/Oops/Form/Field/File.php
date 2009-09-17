<?php
class Oops_Form_Field_File extends Oops_Form_Field
{
    public function __construct($name,$class,$extra)
    {
        $value='';
        
        parent::__construct($name,$class,$value,$extra);      
    }
    protected function _make()
    {
        $this->html='<input type="file" ' . join(' ', $this->_params) . '/>';
    }
}