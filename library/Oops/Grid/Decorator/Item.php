<?php
class Oops_Grid_Decorator_Item
{
    public function createElement($id, $value, $flag = false)
    {   
            return  '<td>' . $value . '</td>';
    }
}