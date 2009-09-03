<?php
abstract class Oops_Form_Constructor_Decorator
{
    public function __construct(){}
    
   abstract public function createElement($name, $text, $html);
    
}