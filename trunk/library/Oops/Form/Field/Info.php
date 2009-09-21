<?php
class Oops_Form_Field_Info extends Oops_Form_Field
{
    protected function _make()
    {
        $this->html = $this->_value;
    }
}