<?php
class Oops_Grid
{
    /*
     * th decorator const name
     */
    const decoratorHeadItem = 1;
    
    /*
     * tr decorator const name
     */
    const decoratorRow = 2;
    
    /*
     * td decorator const name
     */
    const decoratorItem = 3;
     
    /*
     * Grid data array
     */
    protected $_data;
    
    /*
     * Grid titles array
     */
    protected $_titles;
    
    /*
     * Grid html result
     */
    protected $_result;
    
    /*
     * Table th decorator object
     */
    protected $_headItemDecorator;
    
    /*
     * Table tr decorator object
     */
    protected $_rowDecorator;
    
    /*
     * Table td decorator object
     */
    protected $_itemDecorator;
    
    /*
     * Table css class
     */
    public $class = 'table';
    
    
    public function __construct()
    {
       $this->clear();
    }
    
    /**
     * Clear grid
     * @return void
     */
    public function clear()
    {
        $this->_data = array();
        $this->_titles = array();
        $this->_result = '';
    }
    
    /**
     * @param array $data
     * @return void
     */
    public function setData(array $data)
    {
        $this->_data = $data;
    }
    
    /**
     * @param array $titles
     * @return void
     */
    public function setTitles(array $titles)
    {
        $this->_titles = $titles;
    }
    
    public function __tostring()
    {
         $this->_make();
         return $this->_result;   
    }
    
    protected function _checkDecorators()
    {
        if(!is_object($this->_headItemDecorator))
            $this->_headItemDecorator = new Oops_Grid_Decorator_Headitem();

        if(!is_object($this->_rowDecorator))
            $this->_rowDecorator = new Oops_Grid_Decorator_Row();

        if(!is_object($this->_itemDecorator))
            $this->_itemDecorator = new Oops_Grid_Decorator_Item();
    }
    
    protected function _make()
    {
        $this->_checkDecorators();
            
        if(empty($this->_data)) 
            return;   
            
        if(empty($this->_titles)) 
        {
            $key = key($this->data);
            foreach($this->_data[$key] as $v)
                $this->_titles[] = $v;
            
             reset($this->_data);   
        }   
        
        $this->_result = '<table class="'.$this->class.'">';
        
        
        $row ='';
        $i=0;
        $size = sizeof($this->_titles);
        foreach($this->_titles as $k=>$v)
        {
            if($i==0)
                $key = 'first';
            elseif($i==$size-1)
                $key = 'last';
            else
                $key = false;
 
            $row.= $this->_headItemDecorator->createElement($k,$v,$key);
            $i++;
        }
        $this->_result.= $this->_rowDecorator->createElement('head',$row,false);
        
        $k=0;
        foreach ($this->_data as $rkey=>$value)   
        {
            $row = '';
            foreach ($value as $key=>$item)
                $row.=$this->_itemDecorator->createElement($key,$item,false);
                               
            if($k==1){
                $key = true;
                $k=0;
            } else {
                $key = false;
                $k=1;
            }
                
            $this->_result.= $this->_rowDecorator->createElement($rkey,$row,$key);
        }         
        $this->_result.='</table>';       
    }
    
    /**
     * @param const Oops_Grid_Decorator
     * @param Oops_Grid_Decorator obj
     * @return void
     */
    public function setDecorator($decorator ,Oops_Grid_Decorator $obj )
    {
        switch($decorator)
        {
            case self::decoratorHeadItem : $this->_headItemDecorator = $obj;break;
            case self::decoratorRow      : $this->_rowDecorator = $obj;break;
            case self::decoratorItem     : $this->_itemDecorator = $obj;break;
        }
    }
}