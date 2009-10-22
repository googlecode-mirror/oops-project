<?php
class Oops_Form_Constructor_Decorator_Group extends Oops_Form_Constructor_Decorator
{
   public function createElement($name, $text, $html, $errors = false)
    {
        if(!empty($name))
        	$id = ' id="'.$name.'" ';
        else
        	$id = '';
    	return '<fieldset '.$id.'><legend>' . $text . '</legend>' . $html .'</fieldset>' . "\n";
    }
}