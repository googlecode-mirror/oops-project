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
                                    
            case 'dateinterval' : return Oops_Html::$type($name,$value,$class,$extra); 
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
                                    
                                   return Oops_Html::$type($name,$value,$class,$extra,$data['ref_class'],$title); 
                                   
                                    break;                           
			
            default             : return Oops_Html::info($value,$class,$extra); 
                                    break;              
        }
        if($this->viewOnly)
           return $obj->getAsText();
        else
            return $obj->_tostring();
    }
}