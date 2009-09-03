<?php

/**
 * HTML Elements
 */
class Oops_Html {

	/**
	 * Service functions
	 */
	
	/**
	 * Checks if valid element name is given and makes name="$name" string
	 * 
	 * @param string $name
	 * @return string
	 */
	protected static function _putName($name) {
		if(!preg_match("/^[a-zA-Z_][a-zA-Z0-9_\-\[\]]+$/", $name)) {
			throw new Exception("Invalid input name: $name");
		}
		return "name=\"$name\"";
	}

	/**
	 * Checks if valid element class (CSS) is given and makes class="$class" string
	 * 
	 * @param string $class
	 * @return string
	 */
	protected static function _putClass($class) {
		if(!strlen($class)) return '';
		if(!preg_match("/^[a-zA-Z_][a-zA-Z0-9_\-\[\] ]+$/", $class)) {
			throw new Exception("Invalid input name: $class");
		}
		return "class=\"$class\"";
	}

	/**
	 * Quotes the value and makes value="$value" string
	 * @param string $value
	 * @return string
	 */
	protected static function _putValue($value) {
		$value = (string) $value;
		if(!strlen($value)) return "";
		return 'value="' . self::_formsafe($value) . '"';
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
				$value = preg_replace('/[^\-_0-9a-zA-Z/', '', $value);
				return "id=\"$value\"";
			case 'multiple':
			case 'disabled':
			case 'readonly':
				return $key;
			case 'size':
			case 'cols':
			case 'rows':
			case 'maxlength':
				return "$key=\"" . (int) $value . "\"";
		}
		return '';
	}

	/**
	 * Simple text field
	 * 
	 * @param string $name Input name
	 * @param string $value Input value
	 * @param string $class Input CSS class
	 * @param array $extra Additional HTML tag parameters
	 * @return string
	 */
	public static function text($name, $value = '', $class = '', $extra = array()) {
		$params = array();
		$params[] = self::_putName($name);
		$params[] = self::_putClass($class);
		$params[] = self::_putValue($value);
		if(is_array($extra)) foreach($extra as $key => $value) {
			self::_putExtra($key, $value, 'text');
		}
		
		return '<input type="text" ' . join(' ', $params) . '/>';
	}

	/**
	 * Password field
	 * 
	 * @param $name name of HTML element
	 * @param $params array of additional parameters such as className (CSS), ID, etc
	 * @return string
	 */
	public static function password($name, $value = '', $class = '', $extra = array()) {
		$params = array();
		$params[] = self::_putName($name);
		$params[] = self::_putClass($class);
		$params[] = self::_putValue($value);
		if(is_array($extra)) foreach($extra as $key => $value) {
			self::_putExtra($key, $value, 'password');
		}
		
		return '<input type="password" ' . join(' ', $params) . '/>';
	}

	/**
	 * File field
	 * 
	 * @param $name name of HTML element
	 * @param $params array of additional parameters such as className (CSS), ID, etc
	 * @return string
	 */
	public static function file($name, $class = '', $extra = array()) {
		$params = array();
		$params[] = self::_putName($name);
		$params[] = self::_putClass($class);
		if(is_array($extra)) foreach($extra as $key => $value) {
			self::_putExtra($key, $value, 'file');
		}
		
		return '<input type="file" ' . join(' ', $params) . '/>';
	}

	/**
	 * Hidden input
	 * 
	 * @param $name name
	 * @param $value value
	 * @return string
	 */
	public static function hidden($name = '', $value = '') {
		$params = array();
		$params[] = self::_putName($name);
		$params[] = self::_putValue($value);
		return '<input type="hidden" ' . join(' ', $params) . '/>';
	}

	/**
	 * Reset button
	 * 
	 * @param string $value Reset button text
	 * @param string $class Button CSS class
	 * @param array $extra Additional HTML tag parameters
	 * @return string
	 */
	public static function reset($value = '', $class = '', $extra = array()) {
		$params[] = self::_putClass($class);
		$params[] = self::_putValue($value);
		if(is_array($extra)) foreach($extra as $key => $value) {
			$params[] = self::_putExtra($key, $value, 'reset');
		}
		return '<input type="reset" ' . join(' ', $params) . '/>';
	}

	/**
	 * Submit button
	 * 
	 * @param string $value Submit button text
	 * @param string $class Button CSS class
	 * @param array $extra Additional HTML tag parameters
	 * @return string
	 */
	public static function submit($value = '', $class = '', $extra = array()) {
		$params[] = self::_putClass($class);
		$params[] = self::_putValue($value);
		if(is_array($extra)) foreach($extra as $key => $value) {
			$params[] = self::_putExtra($key, $value, 'submit');
		}
		return '<input type="reset" ' . join(' ', $params) . '/>';
	}

	/**
	 * Button
	 * 
	 * @param string $value Button text
	 * @param string $class Button CSS class
	 * @param array $extra Additional HTML tag parameters
	 * @return string
	 */
	public static function button($value = '', $class = '', $extra = array()) {
		$params[] = self::_putClass($class);
		$params[] = self::_putValue($value);
		if(is_array($extra)) foreach($extra as $key => $value) {
			$params[] = self::_putExtra($key, $value, 'button');
		}
		return '<input type="reset" ' . join(' ', $params) . '/>';
	}

	/**
	 * Select options list
	 * 
	 * @param $name select name
	 * @param array $options available options array($optionValue => $optionText, ...)
	 * @param string $value selected
	 * @param string $class CSS classes
	 * @param false|string $empty If not FALSE parameter is used as a label for an empty value, prepended to the options list
	 * @param array $extra Extra HTML tag parameters 
	 * @return string select tag HTML code
	 */
	public static function select($name, array $options, $value = '', $class = '', $empty = false, $extra = array()) {
		$params = array();
		$params[] = self::_putName($name);
		$params[] = self::_putClass($class);
		if(is_array($extra)) foreach($extra as $key => $value) {
			$params[] = self::_putExtra($key, $value, 'reset');
		}
		$out = '<select ' . join(' ', $params) . '>';
		if($empty !== false && !isset($options[''])) {
			$out .= '<option value=""' . ($value === '' ? ' selected' : '') . '>';
			$out .= self::_formsafe($empty) . '</option>';
		}
		foreach($options as $optionValue => $optionLabel) {
			$selected = (strval($value) === strval($optionValue)) ? ' selected' : '';
			$out .= '<option ' . self::_putValue($optionValue) . $selected . '>' . self::_formsafe($optionLabel) . '</option>';
		}
		
		return $out . '</select>';
	}

	/**
	 * Multiple select options list
	 * 
	 * @param $name select name
	 * @param array $options available options array($optionValue => $optionText, ...)
	 * @param array $values Selected values
	 * @param string $class CSS classes
	 * @param array $extra Extra HTML tag parameters 
	 * @return string select tag HTML code
	 */
	public static function multiSelect($name, array $options, $values = array(), $class = '', $extra = array()) {
		$params = array();
		$params[] = self::_putName($name);
		$params[] = self::_putClass($class);
		if(is_array($extra)) foreach($extra as $key => $value) {
			$params[] = self::_putExtra($key, $value, 'reset');
		}
		$out = '<select multiple ' . join(' ', $params) . '>';
		foreach($options as $optionValue => $optionLabel) {
			$selected = in_array($optionValue, $values) ? ' selected' : '';
			$out .= '<option ' . self::_putValue($optionValue) . $selected . '>' . self::_formsafe($optionLabel) . '</option>';
		}
		
		return $out . '</select>';
	}

	/**
	 * Textarea
	 * 
	 * @param $name name of HTML element
	 * @param $value value of HTML element
	 * @param $params array of additional parameters such as className (CSS), ID, cols, rows, etc
	 * @return string
	 */
	public static function textarea($name, $value = '', $class, $extra = array()) {
		$params = array();
		$params[] = self::_putName($name);
		$params[] = self::_putClass($class);
		if(is_array($extra)) foreach($extra as $key => $value) {
			$params[] = self::_putExtra($key, $value, 'reset');
		}
		
		return '<textarea ' . join(' ', $params) . '>' . self::_formsafe($value) . '</textarea>';
	}

	/**
	 * Checkbox
	 * 
	 * @param string $name checkbox input name
	 * @param string $label checkbox label
	 * @param bool $checked if checked
	 * @param string $class CSS class
	 * @param array $extra extra params
	 * @return string
	 */
	public static function checkbox($name, $label = false, $checked = false, $class = '', array $extra = array()) {
		$params = array();
		$params[] = self::_putName($name);
		$params[] = self::_putClass($class);
		if(is_array($extra)) foreach($extra as $key => $value) {
			self::_putExtra($key, $value, 'password');
		}
		if($checked) $params[] = 'checked';
		
		$cBox = '<input type="checkbox" ' . join(' ', $params) . '/>';
		
		if($label !== false) {
			return '<label>' . $cBox . ' ' . self::_formsafe($label) . '</label>';
		}
		return $cBox;
	}

	/**
	 * Single Radiobutton
	 * 
	 * @param string $name button input name
	 * @param string $label button label
	 * @param string $value button value
	 * @param bool $checked if checked
	 * @param string $class CSS class
	 * @param array $extra extra params
	 * @return string
	 */
	public static function radio($name, $value, $label, $checked = false, $class = '', $extra = array()) {
		$params = array();
		$params[] = self::_putName($name);
		$params[] = self::_putClass($class);
		$params[] = self::_putValue($value);
		if(is_array($extra)) foreach($extra as $key => $value) {
			self::_putExtra($key, $value, 'password');
		}
		if($checked) $params[] = 'checked';
		
		$cBox = '<input type="radio" ' . join(' ', $params) . '/>';
		
		if($label !== false) {
			return '<label>' . $cBox . ' ' . self::_formsafe($label) . '</label>';
		}
		return $cBox;
	}
	
	/**
	 * Group of radio buttons
	 * 
	 * @param string $name name of radio group buttons
	 * @param array $options radio options array ($optionValue => $optionLabel)
	 * @param string $value seelcted value
	 * @param string $class CSS class
	 * @param array $extra Extra tag params. If 'id' param is given, every button will be marked with given id concatenated with optionValue
	 * @return string
	 */
	public static function radioGroup($name = '', array $options, string $value = null, $class = '', $extra = array()) {
		$out = '';
		foreach($options as $optionValue => $optionLabel) {
			$optionExtra = $extra;
			if(isset($extra['id'])) $optionExtra['id'] = $extra['id'] . '_' . $optionValue;
			$out .= self::radio($name, $optionValue, $optionLabel, $optionValue == $value, $class, $optionExtra);
		}
		return $out;
	}

}