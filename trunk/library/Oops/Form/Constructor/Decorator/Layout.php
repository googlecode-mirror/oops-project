<?php
class Oops_Form_Constructor_Decorator_layout extends Oops_Form_Constructor_Decorator
{
   public function createElement($name, $text, $html, $errors = false)
    {
        return $html;
    }
}