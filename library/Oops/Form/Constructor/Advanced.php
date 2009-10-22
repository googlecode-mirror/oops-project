<?php
class Oops_Form_Constructor_Advanced extends Oops_Form_Constructor
{
    
    
   protected function _processData(array & $data , $parentGroupName = false) 
    {
    	$result = array();
        foreach($data as $v) 
        {    
        	if(!isset($v['text']))
               $v['text'] = '';  

           
           if( ($v['type'] === 'group') && isset($v['items'])  &&  !empty($v['items']))
           {
               if(isset($this->_params[$v['name']]))
                	foreach($this->_params[$v['name']] as $paramKey=>$paramVal)
                    	$v[$paramKey] = $paramVal;
               
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
                if(isset($this->_params[$v['name']]['display']) && $this->_params[$v['name']]['display'] == false) 
                   continue;  
               
           		if(isset($this->_params[$v['name']]))
                	foreach($this->_params[$v['name']] as $paramKey=>$paramVal)
                    	$v[$paramKey] = $paramVal;
               
               if($this->groupNames && $parentGroupName)
                   $v['name'] = $parentGroupName . '[' . $v['name'] . ']';
               $html = $this->_makeField($v);
               $result[] = array('name' => $v['name'],'text' => $v['text'], 'html' =>  $html);
           }  
       }
       return $result;
    }
    
    protected function _makeField($data)
    {     
        if(isset($data['type']))
            $type = $data['type'];
        else
            $type = 'info';  
                  
        if(isset($data['name']))
            $name = $data['name'];
        else
             throw new Exception('FormCreator :: _makeField - data name is not defined');   
          
        $required = false;
             
        if(isset($data['required']))     
            $required = $data['required'];
                
        $class = 'inputField';
        $extra = array();
        $options = array();
        $empty = false;  
        $value ='';  
        $label='';
        $className='';
        $classParams = array();
             
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

         if(isset($data['className']) )
            $className = $data['className'];
            
         if(isset($data['classParams']) && is_array($data['classParams']))
            $classParams = $data['classParams'];     
              
        $namePrefix = 'Oops_Form_Field_';    
        $object = false;
        
        $oClass = strtolower($type);
        $oClass[0] = strtoupper($oClass[0]);
        $oClass = $namePrefix.$oClass; 
 
        switch ($type)
        {      
            case 'text'         : $obj = new $oClass($name,$value,$class,$extra); 
                                    break;
                                    
            case 'password'     : $obj = new $oClass($name,$value,$class,$extra);
                                   break;
                                    
            case 'file'         : $obj = new $oClass($name,$value, $class,$extra);
                                   break;
                                
            case 'hidden'       : $obj = new $oClass($name,$value);
                                    break;
                                    
            case 'reset'     	: $obj = new $oClass($value,$class,$extra);
                                    break;
                                    
            case 'submit'     	: $obj = new $oClass($value,$class,$extra);
                                  break;
                                    
            case 'button'     	: $obj = new $oClass($name,$value,$class,$extra);
                                   break; 
                                      
            case 'select'       : $obj = new $oClass($name,$options,$value,$class,$empty,$extra);
                                   break;       

            case 'multiSelect'	: $obj = new $oClass($name,$options,$value,$class,$empty,$extra);
                                    break;                       

            case 'textarea'     : $obj = new $oClass($name,$value,$class,$extra); 
                                    break;

            case 'checkbox'		: $obj = new $oClass($name,$label,$value,$class,$extra); 
                                    break;
                                    
            case 'radioGroup'	: $obj = new $oClass($name,$options,$value,$class,$extra); 
                                    break;  

            case 'kaptcha'      : $obj = new $oClass($name,$class); 
                                    break;       
                                    
            case 'info'         : $obj = new $oClass('',$value,$class,$extra); 
                                    break;
                                           
            case 'date'         : $obj = new $oClass($name,$value,$class,$extra); 
                                    break;    
                                    
            case 'dateinterval' : $obj = new $oClass($name,$value,$class,$extra); 
                                    break;                                      

            case 'adapter'		:   if(empty($className))
                                     throw new Exception('Invaild className for'.$name.' field');
                                    require_once 'Oops/Factory.php';
                                    
                                    if(isset($classParams['id']))
                                        $param = $classParams['id'];
                                    else
                                        $param = false;    
                                    
                                    $obj = Oops_Factory::instantiate($className, $param);
                                    $obj->name = $name;
                                    $obj->value = $value;
                                    $obj->class = $class;
                                    $obj->extra = $extra;
                                    $obj->data = $classParams;
                                    //$obj =  new $className($name,$value,$class,$extra,$classParams); 
                                    break;                     

            default             :  $obj = new Oops_Form_Field_Info('',$value,$class,$extra); 
                                    break;              
        }
        
        if($obj->isFile && !$this->_defined)
        {
             $this->setAttr('method','post');
             $this->setAttr('enctype','multipart/form-data');
             $this->_defined = true;
        }
        
        
        $obj->required($required);
        
        if($this->viewOnly)
           return $obj->getAsText();
        else
           return $obj;
           
    }
}