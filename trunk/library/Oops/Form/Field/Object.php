<?php
class Oops_Form_Field_Object extends Oops_Form_Field
{
    protected $_title;
    protected $_refClass;
    
    public function __construct($name,$value='',$class='',$extra = array(), $refClass,$title ='')
    {
        if(!isset($extra['id']))
	        $extra['id'] = 'id_'.$name;
	        
        parent::__construct($name,$value,$class,$extra);   
        
        $this->_title = $title;
        $this->_refClass = $refClass;   
    }
    protected function _make()
    {        
       // $this->html = '<input type="hidden" ' . join(' ', $this->_params) . ' ><input type="text" id="' . $this->_extra['id'] . '_title" value="'.$this->_title.'" /><input type="button" value="select" onClick="registry_showSelectObject(\'' . $this->_refClass . '\',\'' . $this->_extra['id'] . '\')" >';
        $this->html = '<input type="hidden" ' . join(' ', $this->_params)
				. ' ><span class="linkvalue exec registry_showselectobject ' . $this->_refClass . ' '
				. $this->_extra['id'] . '" id="' . $this->_extra['id'] . '_title">'
				. ((strlen($this->title)) ? $this->title : '-') . '</span>';	
    }
}
