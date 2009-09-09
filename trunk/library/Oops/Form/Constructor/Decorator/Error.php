<?php
class Oops_Form_Constructor_Decorator_Error extends Oops_Form_Constructor_Decorator
{
    public function createElement($name, $text, $html, $errors = false)
    {
        $errStr ='<ul style="color:red">';
        
        foreach ($errors as $v)
            $errStr.='<li>'.$v.'</li><br>';
        
        $errStr.='</ul>' ;  
            
        return $errStr.'<label>' . $text . '</label>' . $html .'<br>'."\n";
    }
}