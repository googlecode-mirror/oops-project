<?php 

/**
 * @author Kirill Egorov
 * 
 *
 */
class Oops_Form_Constructor
{
    /*
     * Form fields data
     */
    protected $_data;

    /*
     * Result container
     */
    protected $_result;
  
    /*
     * Form field decorator Obj
     */
    protected $_fieldDecorator;
    
     /*
     * Form group decorator Obj
     */
    protected $_groupDecorator;
    
    /**
     * Form field error decorator 
     * 
     */
    protected $_errorDecorator;
    
    /*
     *  Form properties
     */
    protected $_attr;
    
    /*
     * Group field names by parent container 
     */
    public $groupNames = false;
    
    /*
     * The variable showing whether the form's settings have been automatically redefined
     */
    protected $_defined = false;
    
    /*
     * Automatic addition of the "Submit" button
     */
    public $submitButton = true;
    
    /*
     * Submit button properties
     */
    public $submitButtonConfig = array(
                                          "text"	=> "",
		                        		  "name"    => "submit",
		                        		  "type"    => "submit",
		                        		  "value"   => "Сохранить",
		                        		  "extra"   => ""          
                                );
    
    /**
     * Fields values array
     */
    protected $_values;
    
    /**
     * 
     * @var array
     */
    protected $_errors;
    
                                
                                
    /**
     * @param array $attr  - form tag properties
     * @return void
     */
    
    public function __construct($attr = false)
    {
        if(empty($attr))
            $this->_attr = array("method"=>"post","target"=>"_self","action"=>"");
        else
            $this->_attr = $attr;
        
        $this->result = array();
        $this->values = array();
    }
    
    /**
     * Delete all fields values 
     * @return void
     */
    public function clearValues()
    {
        $this->_values = array();
    }
    
    /**
     * Set form fields values from array
     * @param array $data
     * @return void
     */
    public function setValues(array $data)
    {
        foreach($data as $k=>$v)
            $this->_values[$k] = $v;
    }
    
    /**
     * @param string $name field name
     * @param var $value field value
     * @return void
     */
    public function setValue($name,$value)
    {
        $this->_values[$name] = $value;
    }
    
    
    /*
     * @return array
     */
    public function getValues()
    {
        return $this->_values;
    }
    
    /**
     * Sets the form properties 
     * 
     * @param string $name
     * @param string $value
     * @return void
     */
    
    public function setAttr($name , $value)
    {
        $this->_attr[$name] = $value;
    }
    
    /**
     * Get a form property
     * 
     * @param string $name
     * @return string
     */
    public function getAttr($name)
    {
         if(isset($this->_attr[$name]))
             return $this->_attr[$name];
         else
             return NULL;
    }
       
    /**
     * @param array $data  like :  array(
		                				array(
            		                        "text"	  => "Field Text",  - string
            		                        "name"    => "fieldName",   - string
            		                        "type"    => "text",		- string
            		                        "value"   => "Field Value", - string
            		                        "extra"   => "array()		- array
            		                        
		                				),
		                			...
		                		  )		
     * @return void
     * 
     */
    public function setData(array $data)
    {
        $this->_data = $data;
        $this->_result = array();
        $this->_getValuesFromData($this->_data);
        //$this->_run();
    }
    
    
    /**
     * 
     * @param array $errors
     * @return void
     */
    public function setErrors(array $errors)
    {
        foreach ($errors as $value)
            $this->_errors[$value['data']][] = $value['string'];
    }
    
    
    /**
     * Set decorator for form fields
     * 
     * @param Oops_Form_Constructor_Decorator $decorator
     * @return void
     */
    public function setFieldDecorator(Oops_Form_Constructor_Decorator & $decorator)
    {
        $this->_fieldDecorator = $decorator;
    }
    
	/**
     * Set error decorator for form fields
     * 
     * @param Oops_Form_Constructor_Decorator $decorator
     * @return void
     */
    public function setErrorDecorator(Oops_Form_Constructor_Decorator & $decorator)
    {
        $this->_errorDecorator = $decorator;
    }
    
     /**
     * Set decorator for form grouops 
     * 
     * @param Oops_Form_Constructor_Decorator $decorator
     * @return void
     */
    public function setGroupDecorator(Oops_Form_Constructor_Decorator & $decorator)
    {
        $this->_groupDecorator = $decorator;
    }
    

    protected function _checkDecorators()
    {
        if(!is_object($this->_fieldDecorator))
            $this->_fieldDecorator = new Oops_Form_Constructor_Decorator_Field();
            
        if(!is_object($this->_groupDecorator))
            $this->_groupDecorator = new Oops_Form_Constructor_Decorator_Group();  
            
        if(!is_object($this->_errorDecorator))
            $this->_errorDecorator = new Oops_Form_Constructor_Decorator_Error();      
    }
    
    
    public function __toString()
    {
        return $this->getHtml();
    }
    
    
    /**
     * @return string
     */
    public function getHtml()
    {    
        $this->_run();
        $this->_checkDecorators();
        
          $result ='<form ';
          
          if(!empty($this->_attr))
              foreach($this->_attr as $k => $v)  
                      $result.= $k . '="'.$v.'" ';
                
          $result.='>';    

          if(!empty($this->_result))
              $result.=$this->_getFormGroup($this->_result,'','',true);
          

          $result.='</form>';       
          
          return $result;       
    }
    
    
    public function _getFormGroup(array & $items, $text , $name , $first = false)
    {
        $result='';
         
          foreach ($items as $k => $v)
          {
              if(!isset($v['items']))
              {
                  if(isset($this->_errors[$v['name']]))
                      $result.= $this->_errorDecorator->createElement($v['name'], $v['text'], $v['html'],$this->_errors[$v['name']]);
                  else
                      $result.=$this->_fieldDecorator->createElement($v['name'], $v['text'], $v['html']);
              }
              else
              {
                  $result.= $this->_getFormGroup($v['items'],$v['text'],$k);  
              } 
          }

         if($first)
             return $result;
         else
             return $this->_groupDecorator->createElement($name,$text,$result);       
    }
     
    protected function _run()
    {
       if(empty($this->_data))
            return false;
       
       if($this->submitButton)
           $this->_data[] = $this->submitButtonConfig;
            
       $this->_result = $this->_processData($this->_data);
       $this->_data = false;
    }
    protected function _getValuesFromData(array & $data)
    {
       foreach($data as $v)
           if( ($v['type'] === 'group') && isset($v['items'])  &&  !empty($v['items']))
               $this->_getValuesFromData($v['items']);                                                                         
           else
               $this->_values[$v['name']] =  $v['value'];             
    }
    
    protected function _processData(array & $data , $parentGroupName = false) 
    {
        $result = array();
           
        foreach($data as $v)
        {
           if($v['type']=='file' && !$this->_defined)
           {
               $this->setAttr('method','post');
               $this->setAttr('enctype','multipart/form-data');
               $this->_defined = true;
               require_once 'Oops/Utils.php';
               $max_file_size = min(Oops_Utils::toBytes(ini_get('upload_max_filesize')),
               						Oops_Utils::toBytes(ini_get('post_max_size')));
               array_unshift($data, array('type'=>'hidden',
               				   'name'=>'MAX_FILE_SIZE',
               				   'value'=>$max_file_size));
              
           }
        }
        foreach($data as $v) {
            
          if(!isset($v['text']))
               $v['text'] = '';    
           
           if( ($v['type'] === 'group') && isset($v['items'])  &&  !empty($v['items']))
           {
               if($this->groupNames && $parentGroupName)
                   $newName = $parentGroupName . '[' . $v['name'] . ']';
               elseif($this->groupNames && !$parentGroupName)
                   $newName = $v['name'];
               else
                   $newName = false;

                
                   
                $result[] = array(    			'name' => $v['name'],
    											'text'  => $v['text'],
    											'items' => $this->_processData($v['items'],$newName),                            
                                             );
           }
           else
           {
               if(isset($this->_values[$v['name']]))
                  $v['value'] = $this->_values[$v['name']];
               
               if($this->groupNames && $parentGroupName)
                   $v['name'] = $parentGroupName . '[' . $v['name'] . ']';
                       
                            $result[] = array( 
                                                'name' => $v['name'],
    											'text' => $v['text'],
    											'html' => $this->_makeField($v)                              
                                              );
           }  
       }   
       return $result;
    }
    
    
    
    /**
     * @return array
     * 	
     */
    public function getFormData()
    { 
       $this->_run(); 
       return $this->_result;
    }
    
    /**
     * @param array $data  like: array(
     *       		                        "text"	  => "Field Text",  					- string
     *       		                        "name"    => "fieldName",   					- string
     *       		                        "type"    => "text",							- string
     *       		                        "value"   => "Field Value", 					- string
     *       		                        "extra"   => array()							- array
     *       								"option"  => array(1=>'First',2=>'Second)		- array
     *       								"label"	  => 'Label1'							- string
     *       								
	 *	                				),
     * 			
     * 			
     * @return string
     */
    protected final function _makeField($data)
    {     
        if(isset($data['type']))
            $type = $data['type'];
        else
            $type = 'info';  
                  
        if(isset($data['name']))
            $name = $data['name'];
        else
             throw new Exception('FormCreator :: _makeField - data name is not defined');   
                
        $class = 'inputField';
        $extra = array();
        $options = array();
        $empty = false;  
        $value ='';  
        $label='';
             
        if(isset($data['class']))
            $class = $data['class'];
            
        if(isset($data['value']))
            $value = $data['value'];    

        if(isset($data['extra']) && is_array($data['extra']))
           $extra = $data['extra'];  

        if(isset($data['empty']) && $data['empty'])
            $empty = $data['empty'];  

        if(isset($data['label']))
            $label = $data['label'];
             
        if(isset($data['options']) && is_array($data['options']))
            $options = $data['options'];    
                        
        switch ($type)
        {
            
            case 'text'         : return Oops_Html::$type($name,$value,$class,$extra); 
                                    break;
                                    
            case 'password'     : return Oops_Html::$type($name,$value,$class,$extra);
                                   break;
                                    
            case 'file'         : return Oops_Html::$type($name,$class,$extra);
                                   break;
                                
            case 'hidden'       : return Oops_Html::$type($name,$value);
                                    break;
                                    
            case 'reset'     	: return Oops_Html::$type($value,$class,$extra);
                                    break;
                                    
            case 'submit'     	: return Oops_Html::$type($value,$class,$extra);
                                  break;
                                    
            case 'button'     	: return Oops_Html::$type($value,$class,$extra);
                                   break; 
                                      
            case 'select'       : return Oops_Html::$type($name,$options,$value,$class,$empty,$extra);
                                  break;       

            case 'multiSelect'	: return Oops_Html::$type($name,$options,$value,$class,$empty,$extra);
                                    break;                       

            case 'textarea'     : return Oops_Html::$type($name,$value,$class,$extra); 
                                    break;

            case 'checkbox'		: return Oops_Html::$type($name,$label,$value,$class,$extra); 
                                    break;
                                    
            case 'radioGroup'	: return Oops_Html::$type($name,$options,$value,$class,$extra); 
                                    break;  

            case 'kaptcha'      : return Oops_Html::$type($name,$class); 
                                    break;       
                                    
            case 'info'         : return Oops_Html::$type($value,$class,$extra); 
                                    break;
                                           
            case 'date'         : return Oops_Html::$type($name,$value,$class,$extra); 
                                    break;              
                                    
         /*
          *  Special Fields
          */         
                                                      
            case 'object'		:  $extra['readonly'] = true;
                                   return Oops_Html::$type($name,$value,$class,$extra,$data['ref_class']); 
                                    break;                           
			
            default             : return Oops_Html::info($value,$class,$extra); 
                                    break;              
        }
    }
}
