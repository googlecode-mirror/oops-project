<?php
abstract class Oops_Form_Adapter_Field extends  Oops_Form_Field
{
    public function __construct($name,$value='',$class='',$extra=array(),$data = array())
    {
        parent::__construct($name,$value,$class,$extra);
        $this->_data = $data;
    }
}