<?php
class Oops_Grid_Decorator_Headitem extends Oops_Grid_Decorator
{
    public function createElement($id,$value,$flag = false)
    {
        if($flag=='first')
            $class='class="thfirst"';
        elseif($flag=='last')
             $class='class="thlast"';   
        else
            $class='';
                
            return  '<th '.$class.'>' . $value . '</th>';
    }
}