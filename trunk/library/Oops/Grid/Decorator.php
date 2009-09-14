<?php
abstract class Oops_Grid_Decorator
{
    public function __construct(){}
    
   abstract public function createElement($id, $value, $flag = false);
    
}