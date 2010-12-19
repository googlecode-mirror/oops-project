<?php
/**
 * @author Kirill Egorov
 *
 */

abstract class Oops_Form_Field
{
    /*
     * Field name attr.
     */
    protected $_name;
    
    /*
     * Field class attr.
     */
    protected $_class;
    
    /*
     * Field value attr.
     */
    protected $_value;
    
    /*
     * Field extra params
     */
    protected $_extra;
    
    
    /*
     *  Field attr. array for html
     */
    protected $_params;
    
    /*
     * 
     */
    protected $_required;
    
    protected $_isFile = false;
    
    
    /*
     *  Field Events & Oops_Form_Field_Events
     */
    public $events;
    
    /*
     * Field html
     */
    protected $html='';
    
      
    abstract protected function _make();
    
    public function __toString()
    {
    	try {
        	return $this->getHtml();
    	} catch (Exception $e) {
    		Oops_Debug::Dump($e, 'exception', true);
    		return '';
    	}
    }
    
    public function getHtml()
    {
        $this->_getParams();
        $this->_make();
        
        return $this->html;
    }
    
    public function make()
    {
        $this->_make();
    }
    
    public function __construct($name,$value,$class,$extra)
    {
        $this->_name = $name;
        $this->_class = $class;
        $this->_value = $value;
        $this->_required = false;
        $this->events = new Oops_Form_Field_Events();
        
        if(is_array($extra) && !empty($extra))
            $this->_extra = $extra;
        else
            $this->_extra = array();

        $this->_params = array();
        
    }
    public function getType()
    {
        $name = str_replace('Oops_Form_Field_','',get_class($this));
        $name[0] = strtolower($name[0]); 
        return $name;
    }
    public  function required($bool = true)
    {
        if($bool)
            $this->_required = true;
        else
            $this->_required = false;    
    }

    public function isRequired()
    {
        return $this->_required;
    }
    
    /**
	 * Checks if valid element name is given and makes name="$name" string
	 * 
	 * @param string $name
	 * @return void
	 */
	protected  function _putName($name) {
	    
		if(!preg_match("/^[a-zA-Z_][a-zA-Z0-9_\-\[\]]+$/", $name)) {
			throw new Exception("Invalid input name: $name");
		}
		return 'name="' . $name .'"';
	}
	
	/**
	 * Checks if valid element class (CSS) is given and makes class="$class" string
	 * 
	 * @param string $class
	 * @return void
	 */
	protected  function _putClass($class) {
	    
		if(!strlen($class))
		     $this->_class = '';
		     
		if(!preg_match("/^[a-zA-Z_][a-zA-Z0-9_\-\[\] ]+$/", $class)) {
			throw new Exception("Invalid input class: $class");
		}	
		return 'class ="' . $class .'"';
	}
	
	/**
	 * Quotes the value and makes value="$value" string
	 * @param string $value
	 * @return void
	 */
	protected  function _putValue($value) {
		$value = (string) $value;

		if(!strlen($value)) 
		    $this->_value = '';

		return 'value="'.$this->_formsafe($value).'"';
	}
	
    protected  function _formsafe($string) {
		return str_replace(array('"', "'", "<", ">" ), array(
															"&quot", 
															"&#039;", 
															"&lt;", 
															"&gt;" ), $string);
	}
	
    protected  function _putExtra($key, $value, $invoker = null) {
		$key = strtolower($key);
		switch($key) {
			case 'id':
				$value = preg_replace('/[^\-_0-9a-zA-Z]+/', '', $value);
				return 'id="' . $value . '"';
			case 'multiple':
			case 'disabled':
			case 'readonly':
				return $key;
			case 'size':
			case 'cols':
			case 'rows':
			case 'maxlength':
				return $key.'="' . (int) $value . '"';
		}
	}
	protected function _getParams()
	{
	    if(!empty($this->_name))
	        $this->_params[] = $this->_putName($this->_name);
	    
         if(!empty($this->_class))
	        $this->_params[] = $this->_putClass($this->_class);
	            
	     if(!($this instanceof Oops_Form_Field_Select) && isset($this->_value))   
	        $this->_params[] = $this->_putValue($this->_value);
	    	    
	    if(is_array($this->_extra)) 
	        foreach($this->_extra as $key => $value) {
			    $this->_params[] = $this->_putExtra($key, $value, 'text');
		}

	}
	
	public function getAsText()
	{
	    if(strlen($this->value)>0)
	        return $this->_value;
	    else
	        return '';
	}
	
	public function __set($var, $value) {
		switch($var) {
			case 'class':
			case 'extra':
			case 'name':
			case 'params':
			case 'required':
			case 'value':
				$this->{'_' . $var} = $value;
				break;
			case 'isFile':
				break;
			default:
				$this->{$var} = $value;
				break;
		}
	}
	
	public function __get($var) {
		if(isset($this->{'_' . $var})) return $this->{'_' . $var};
		return null; 
	}
	
}