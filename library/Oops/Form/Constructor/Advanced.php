<?php
class Oops_Form_Constructor_Advanced extends Oops_Form_Constructor
{
    
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
                                    
            case 'file'         : $obj = new $oClass($name,$class,$extra);
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
                                    
         /*
          *  Special Fields
          */         
                                                      
            case 'object'		:  $extra['readonly'] = true;
                                        
                                    if(!empty($value)) {
                                        $obj = new Registry_Object($value);
                                        $title = $obj->__tostring();
                                    }
                                    else{
                                        $title='';
                                    } 
                                    
                                   return $obj = new $oClass($name,$value,$class,$extra,$data['ref_class'],$title); 
                                   
                                    break;                           
			
            default             :  $obj = new Oops_Form_Field_Info('',$value,$class,$extra); 
                                    break;              
        }

        if($this->viewOnly)
           return $obj->getAsText();
        else
           return $obj->__toString();
    }
}