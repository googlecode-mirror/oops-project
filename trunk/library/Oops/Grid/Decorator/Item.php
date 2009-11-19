<?php
class Oops_Grid_Decorator_Item extends Oops_Grid_Decorator
{
    public function createElement($id, $value, $flag = false)
    {   
            return  '<td>' . $value . '</td>';
    }
}