<?php
class Slickr_Flickr_DIY_Options {

	protected $option_name;
	protected $options = array();
	protected $defaults = array();
	protected $encoded = false;

	function __construct($option_name, $defaults = array(), $encoded = false) {
		$this->option_name = $option_name;
		$this->defaults = $defaults;
		$this->encoded = $encoded;
	}

	function add_defaults($more = array()) {
		$this->defaults = array_merge($this->defaults, (array)$more);
		$this->options = array(); //clear cache
	}	

	function get_defaults() {
		return $this->defaults;
	}

	function get_default($option_name) {
    	if ($option_name && array_key_exists($option_name, $this->defaults))
        	return  $this->defaults[$option_name];
    	else
        	return false;
	}	

	function get_option_name() {
		return $this->option_name;
	}

	function get_options($cache = true) {
		if ($cache && (count($this->options) > 0)) return $this->options;
		$the_options = get_option($this->get_option_name());
		if (! empty($the_options) && ! is_array($the_options) && $this->encoded) 
			$the_options = unserialize(strrev(base64_decode($the_options)));
		$this->options = empty($the_options) ? $this->get_defaults() : shortcode_atts( $this->get_defaults(), $the_options);
		return $this->options;
	}

	function get_option($option_name, $cache = true) {
    	$options = $this->get_options($cache);
    	if ($option_name && $options && array_key_exists($option_name,$options))
         if (($defaults = $this->get_default($option_name)) && is_array($defaults) && is_array($options[$option_name])) 
            return $this->validate_options($defaults, $options[$option_name]);
         else
            return $options[$option_name];
    	else
        	return $this->get_default($option_name);     		
    }

	function save_options($new_options) {
		$options = $this->get_options(false);
		$new_options = shortcode_atts( $this->get_defaults(), array_merge($options, $new_options));
		if ($this->encoded) $new_options = base64_encode(strrev(serialize($new_options)));
		$updated = update_option($this->get_option_name(),$new_options);
		if ($updated) $this->get_options(false);
		return $updated;
	}	

	function validate_options($defaults, $options ) {
		if (is_array($options) && is_array($defaults) )
    		return shortcode_atts($defaults, $options);		
		else
    		return false;		
    }		

	function upgrade_options() {
		$new_options = array();
		$defaults = $this->get_defaults();
		$options = get_option($this->get_option_name());

		if (is_array($options)) {
			/* Remove old options and set defaults for new options */ 
			foreach ($defaults as $key => $subdefaults) 
				if (array_key_exists($key, $options)) 
					if (is_array($options[$key]) && is_array($subdefaults)) 
						$new_options[$key] = shortcode_atts($subdefaults, $options[$key]);
					else
						$new_options[$key] = $options[$key];
		} else {		
			$new_options = $defaults;
		}
		$this->save_options($new_options);
	}
}

