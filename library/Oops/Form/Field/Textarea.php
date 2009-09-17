<?php
class Oops_Form_Field_Textarea extends Oops_Form_Field
{
    protected function _make()
    {
        $this->html='<textarea ' . join(' ', $this->_params) . '>' . self::_formsafe($this->_value) . '</textarea>';
    }
}