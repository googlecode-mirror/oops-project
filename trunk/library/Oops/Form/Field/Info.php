<?php
class Oops_Form_Field_Info extends Oops_Form_Field
{
    protected function _make()
    {
        $this->html='<label ' . join(' ', $this->_params) . ' > ' . $this->_value . '</label>';
    }
}