<?php
class Oops_Form_Constructor_Decorator_Group extends Oops_Form_Constructor_Decorator
{
   public function createElement($name, $text, $html)
    {
        return '<fieldset><legend>' . $text . '</legend>' . $html .'</fieldset>' . "\n";
    }
}