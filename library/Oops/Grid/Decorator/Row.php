<?php
class Oops_Grid_Decorator_Row extends Oops_Grid_Decorator
{
    public function createElement($id, $value, $flag = false)
    {
        if($flag)
            $class='class="even"';   
        else
            $class='';
                
            return  '<tr '.$class.'>' . $value . '</tr>';
    }
}