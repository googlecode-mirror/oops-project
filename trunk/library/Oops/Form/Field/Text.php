<?php
class Oops_Form_Field_Text extends Oops_Form_Field
{
    protected function _make()
    {
        $this->html='<input type="text" ' . join(' ', $this->_params) . ' ' . $this->events->getList() . '/>';
    }
}