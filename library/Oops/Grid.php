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
    
    /*
     * Use ExtJs Grid component
     */
    public $isExt = false;
    
    /*
     *  Ext Js Grid params
     *  array(	
     *  		'id'=>'',
     *  		'dataUrl'=>'',
     *  		'idField'=>'',
     *  		'data'=>''
     *  )
     */
    public $extParams;
    
    /*
     * Ext Js grid config array
     */
    protected $_extData;
    
    
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
        if($this->isExt)
             $this->_makeExtGrid();
        else 
             $this->_make();
             
         return $this->_result;   
    }/*
    protected function _makeExtGrid()
    {
        if(empty($this->extParams))
            throw new Exception('Need ext js params');
            
        if(empty($this->_data)) 
            return;  

        $this->_checkDecorators();
        $this->_checkTitles();
        
        if(!isset($this->extParams['columns']))
            $this->extParams['columns'] = array();
        
        $this->_extData['columns'] = '';
        $this->_extData['readerFields']='';    
         foreach($this->_titles as $k=>$v)
         {
                 $width = 160;
                 $sortable = 'true';
                 $dataIndex = $k;
                 
             if(isset($this->extParams['columns'][$k]))
             {
                 $colConfig = $this->extParams['columns'][$k];
                 if(isset($colConfig['width']))
                     $width = $colConfig['width'];
                 if(isset($colConfig['sortable']))
                     $width = $colConfig['sortable'];
                 if(isset($colConfig['dataIndex']))
                     $width = $colConfig['dataIndex'];         
             }

             $this->_extData['columns'].= '{id:\''.$k.'\',header: \''.$v.'\', width: '.$width.', sortable: '.$sortable.', dataIndex: \''.$dataIndex.'\'},
             ';
             
             if($k===$this->extParams['idField'])
                 $this->_extData['readerFields'].='{name: \''.$k.'\'},
                 ';
             else
                 $this->_extData['readerFields'].='{name: \''.$k.'\', allowBlank: false},
                 ';    
         }
            

         $this->_extData['store'] = '
         var reader = new Ext.data.JsonReader(
         						{
                                    totalProperty: \''.$this->extParams['totalProperty'].'\',
                                    successProperty: \''.$this->extParams['successProperty'].'\',
                                    idProperty: \''.$this->extParams['idField'].'\',
                                    root: \''.$this->extParams['data'].'\',
                            	}, 
                            	[
                            	'.$this->_extData['readerFields'].']
                          );
               
         var store = new Ext.data.Store({
                                    url: \''.$this->extParams['dataUrl'].'\',
                                    reader: reader,
        				});
        ';
         
         $this->_result = '
         <script language="javascript">
         	Ext.onReady(function(){
         	
         		'.$this->_extData['store'].'
         		
         		store.loadData();
         		
         	    var grid = new Ext.grid.GridPanel({
                                    store: store,
                                    columns: [
                                    '.$this->_extData['columns'].'
                                    ],
                                    stripeRows: true,
                                   // autoExpandColumn: \'company\',
                                    height: 350,
                                    width: 600,
                                    title: \'Grid\',                             
                                    stateful: true,
                                    stateId: \'grid\',        
                            	});
                            
               grid.render(\''.$this->extParams['id'].'\');       	
         	}
         </script>';
    }*/
    protected function _checkDecorators()
    {
        if(!is_object($this->_headItemDecorator))
            $this->_headItemDecorator = new Oops_Grid_Decorator_Headitem();

        if(!is_object($this->_rowDecorator))
            $this->_rowDecorator = new Oops_Grid_Decorator_Row();

        if(!is_object($this->_itemDecorator))
            $this->_itemDecorator = new Oops_Grid_Decorator_Item();
    }
    protected function _checkTitles()
    {
        if(empty($this->_titles)) 
            throw new Exception('No titles');
    }
    protected function _make()
    {
        if(empty($this->_data)) 
            return;  

        $this->_checkDecorators();
        $this->_checkTitles();
              
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
            
            foreach($this->_titles as $k=>$v){
                if(!isset($value[$k]))
                    $value[$k] = '&nbsp;';
               
               $row.= $this->_itemDecorator->createElement($k,$value[$k],false);
              
            }                   
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