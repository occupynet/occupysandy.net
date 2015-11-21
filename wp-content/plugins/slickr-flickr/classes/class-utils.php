<?php
class Slickr_Flickr_Utils {

	static protected $is_html5 = null;

	static function is_html5() {
		if (self::$is_html5 == null)
			self::$is_html5 = function_exists('current_theme_supports') && current_theme_supports('html5');		
		return self::$is_html5;
	}

	static function get_post_id() {
		global $post;

		if (is_object($post) 
		&& property_exists($post, 'ID') 
		&& ($post_id = $post->ID))
			return $post_id ;
		else
			return false;
	}

	static function post_has_shortcode($shortcode, $attribute = false) {
		global $wp_query;
		if (isset($wp_query)
		&& isset($wp_query->post)
		&& isset($wp_query->post->post_content)
		&& function_exists('has_shortcode')
		&& has_shortcode($wp_query->post->post_content, $shortcode)) 
			if ($attribute)
				return strpos($wp_query->post->post_content, $attribute) !== FALSE ;
			else
				return true;
		else
			return false;
	}

	static function get_meta ($post_id, $key) {
		if ($post_id && $key
		&& ($meta = get_post_meta($post_id, $key, true))
		&& ($options = (is_serialized($meta) ? @unserialize($meta) : $meta))
		&& (is_array($options) || is_string($options)))
			return $options;
		else
			return false;
	}

	static function json_encode($params) {
   		//fix numerics and booleans
		$pat = '/(\")([0-9]+)(\")/';	
		$rep = '\\2';
		return str_replace (array('"false"','"true"'), array('false','true'), 
			preg_replace($pat, $rep, json_encode($params)));
	} 
   
	static function is_mobile_device() {
		return  preg_match("/wap.|.wap/i", $_SERVER["HTTP_ACCEPT"])
    		|| preg_match("/iphone|ipad/i", $_SERVER["HTTP_USER_AGENT"]);
	} 

	static function is_landing_page($page_template='') {	
		if (empty($page_template)
		&& ($post_id = self::get_post_id()))
			$page_template = get_post_meta($post_id,'_wp_page_template',TRUE);
		
		if (empty($page_template)) return false;

		$landing_pages = (array) apply_filters('diy_landing_page_templates', array('page_landing.php'));
		return in_array($page_template, $landing_pages );
	}

	static function hide_widget($visibility ) {
		$hide = false;
		$is_landing = self::is_landing_page();
		switch ($visibility) {
			case 'hide_landing' : $hide = $is_landing; break; //hide only on landing pages
			case 'show_landing' : $hide = ! $is_landing; break; //hide except on landing pages
		}
		return $hide;
	}
	
    static function get_visibility_options(){
		return array(
			'' => 'Show on all pages', 
			'hide_landing' => 'Hide on landing pages', 
			'show_landing' => 'Show only on landing pages');
	}

	static function selector($fld_id, $fld_name, $value, $options, $multiple = false) {
		$input = '';
		if (is_array($options)) {
			foreach ($options as $optkey => $optlabel)
				$input .= sprintf('<option%1$s value="%2$s">%3$s</option>',
					selected($optkey, $value, false), $optkey, $optlabel); 
		} else {
			$input = $options;
		}
		return sprintf('<select id="%1$s" name="%2$s"%4$s>%3$s</select>', $fld_id, $fld_name, $input, $multiple ? ' multiple':'');							
	}

	static function form_field($fld_id, $fld_name, $label, $value, $type, $options = array(), $args = array(), $wrap = false) {
		if ($args) extract($args);
		$input = '';
		$label = sprintf('<label class="diy-label" for="%1$s">%2$s</label>', $fld_id, __($label));
		switch ($type) {
			case 'text':
			case 'password':
				$input .= sprintf('<input type="%9$s" id="%1$s" name="%2$s" value="%3$s" %4$s%5$s%6$s%7$s /> %8$s',
					$fld_id, $fld_name, $value, 
					isset($readonly) ? (' readonly="'.$readonly.'"') : '',
					isset($size) ? (' size="'.$size.'"') : '', 
					isset($maxlength) ? (' maxlength="'.$maxlength.'"') : '',
					isset($class) ? (' class="'.$class.'"') : '', 
					isset($suffix) ? $suffix : '', 
					$type);
				break;
			case 'file':
				$input .= sprintf('<input type="file" id="%1$s" name="%2$s" value="%3$s" %4$s%5$s%6$s accept="image/*" />',
					$fld_id, $fld_name, $value, 
					isset($size) ? ('size="'.$size.'"') : '', 
					isset($maxlength) ? (' maxlength="'.$maxlength.'"') : '',
					isset($class) ? (' class="'.$class.'"') : '');
				break;
			case 'textarea':
				$input .= sprintf('<textarea id="%1$s" name="%2$s"%3$s%4$s%5$s%6$s>%7$s</textarea>',
					$fld_id, $fld_name, 
					isset($readonly) ? (' readonly="'.$readonly.'"') : '', 
					isset($rows) ? (' rows="'.$rows.'"') : '', 
					isset($cols) ? (' cols="'.$cols.'"') : '',
					isset($class) ? (' class="'.$class.'"') : '', $value);
				break;
			case 'checkbox':
				if (is_array($options) && (count($options) > 0)) {
					if (isset($legend))
						$input .= sprintf('<legend class="screen-reader-text"><span>%1$s</span></legend>', $legend);
					if (!isset($separator)) $separator = '';
					foreach ($options as $optkey => $optlabel)
						$input .= sprintf('<input type="checkbox" id="%1$s" name="%2$s[]" %3$s value="%4$s" /><label for="%1$s">%5$s</label>%6$s',
							$fld_id, $fld_name, str_replace('\'','"',checked($optkey, $value, false)), $optkey, $optlabel, $separator); 
					$input = sprintf('<fieldset class="diy-fieldset">%1$s</fieldset>',$input); 						
				} else {		
					$input .= sprintf('<input type="checkbox" class="checkbox" id="%1$s" name="%2$s" %3$svalue="1" class="diy-checkbox" />',
						$fld_id, $fld_name, checked($value, '1', false));
				}
				break;
				
			case 'checkboxes': 
			   $values = (array) $value;
			   $options = (array) $options;
			   if (isset($legend))
				  $input .= sprintf('<legend class="screen-reader-text"><span>%1$s</span></legend>', $legend);
				foreach ($options as $optkey => $optlabel)
				  $input .= sprintf('<li><input type="checkbox" id="%1$s" name="%2$s[]" %3$s value="%4$s" /><label for="%1$s">%5$s</label></li>',
					$fld_id, $fld_name, in_array($optkey, $values) ? 'checked="checked"' : '', $optkey, $optlabel); 
				$input = sprintf('<fieldset class="diy-fieldset%2$s"><ul>%1$s</ul></fieldset>',$input, isset($class) ? (' '.$class) : ''); 						
		
				break;
			case 'radio': 
				if (is_array($options) && (count($options) > 0)) {
					if (isset($legend))
						$input .= sprintf('<legend class="screen-reader-text"><span>%1$s</span></legend>', $legend);
					if (!isset($separator)) $separator = '';
					foreach ($options as $optkey => $optlabel)
						$input .= sprintf('<input type="radio" id="%1$s" name="%2$s" %3$s value="%4$s" /><label for="%1$s">%5$s</label>%6$s',
							$fld_id, $fld_name, str_replace('\'','"',checked($optkey, $value, false)), $optkey, $optlabel, $separator); 
					$input = sprintf('<fieldset class="diy-fieldset">%1$s</fieldset>',$input); 						
				}
				break;		
			case 'select': 
				$input =  self::selector($fld_id, $fld_name, $value, $options, isset($multiple));							
				break;	
			case 'hidden': return sprintf('<input type="hidden" name="%1$s" value="%2$s" />', $fld_name, $value);	
			default: $input = $value;	
		}
		if (!$wrap) $wrap = 'div';
		switch ($wrap) {
			case 'tr': $format = '<tr class="diy-row"><th scope="row">%1$s</th><td>%2$s</td></tr>'; break;
			case 'br': $format = 'checkbox'==$type ? '%2$s%1$s<br/>' : '%1$s%2$s<br/>'; break;
			default: $format = strpos($input,'fieldset') !== FALSE ? 
				'<div class="wrapfieldset">%1$s%2$s</div>' : ('<'.$wrap.'>%1$s%2$s</'.$wrap.'>');
		}
		return sprintf($format, $label, $input);
	}

    static function register_icons_font() {
        global $wp_styles;
        wp_enqueue_style( 'font-awesome', '//netdna.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css',array(),'4.3.0','all' );
        wp_enqueue_style('font-awesome-ie7', plugins_url('styles/font-awesome-ie7.min.css', dirname(__FILE__)), array(), '4.3.0', 'all');
        $wp_styles->add_data('font-awesome-ie7', 'conditional', 'lte IE 7');
    }
	
	static function register_tooltip_styles() {
		wp_register_style('diy-tooltip', plugins_url('styles/tooltip.css',dirname(__FILE__)), array(), null); 
	}

	static function enqueue_tooltip_styles() {
         wp_enqueue_style('diy-tooltip');
         wp_enqueue_style('dashicons');
    }
}
