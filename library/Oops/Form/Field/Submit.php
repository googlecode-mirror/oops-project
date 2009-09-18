<?php
class Oops_Form_Field_Submit extends Oops_Form_Field
{
    public function __construct($value,$class,$extra)
    {
        parent::__construct('',$value,$class,$extra);      
    }
    protected function _make()
    {
        $this->html='<input type="submit" ' . join(' ', $this->_params) . '/>';
    }
}