<?php
class Oops_Form_Constructor_Decorator_Field extends Oops_Form_Constructor_Decorator
{
    public function createElement($name, $text, $html, $errors = false)
    {
        return '<label>' . $text . '</label>' . $html .'<br>'."\n";
    }
}