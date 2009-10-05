<?php
class Oops_Form_Field_Events{
    
    public $onClick,$onChange,$onDblClick,$onKeyPress,$onKeyDown,$onKeyUp;
    
    public function __construct()
    {
        
    }
    
    public function getList()
    {
        $events = get_object_vars($this);
        $str = '';
        
        foreach($events as $k=>$v)
            if(!empty($v))
                $str.=''.$k.'="'.$v.'" ';

        return $str;        
    }
    
}