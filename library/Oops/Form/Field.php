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
        $this->_getParams();
        $this->_make();
        
        return $this->html;
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
	protected static function _putName($name) {
	    
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
	protected static function _putClass($class) {
	    
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
	protected  static function _putValue($value) {
		$value = (string) $value;
		
		if(!strlen($value)) 
		    $this->_value='';
		    
		return 'value="'.self::_formsafe($value).'"';
	}
	
    protected static function _formsafe($string) {
		return str_replace(array('"', "'", "<", ">" ), array(
															"&quot", 
															"&#039;", 
															"&lt;", 
															"&gt;" ), $string);
	}
	
    protected static function _putExtra($key, $value, $invoker = null) {
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
	        $this->_params[] = self::_putName($this->_name);
	    
         if(!empty($this->_class))
	        $this->_params[] = self::_putClass($this->_class);
	    
	     if(!empty($this->_value))
	        $this->_params[] = self::_putValue($this->_value);
	    	    
	    if(is_array($this->_extra)) 
	        foreach($this->_extra as $key => $value) {
			    $this->_params[] = self::_putExtra($key, $value, 'text');
		}

	}
	
	public function getAsText()
	{
	    return $this->_value;
	}
	
}