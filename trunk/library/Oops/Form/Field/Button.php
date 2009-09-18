<?php
class Oops_Form_Field_Button extends Oops_Form_Field
{
    protected function _make()
    {
        $this->html='<input type="button" ' . join(' ', $this->_params) . '/>';
    }
}