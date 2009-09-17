<?php
class Oops_Form_Field_Password extends Oops_Form_Field
{
    protected function _make()
    {
        $this->html='<input type="password" ' . join(' ', $this->_params) . '/>';
    }
}