<?php

/**
Theme integration file.
 **/
define('WPCSS','1.6.1');
define('WPCSS_THEME',1);
  
if(!function_exists('property_exists')):
function property_exists($o,$p){
	return is_object($o) && 'NULL'!==gettype($o->$p);
}
endif;

if(!class_exists('custom_shortcode_styling')){
	require_once WPCSS_PATH.'includes/class.custom_shortcode_styling.php';
}

?>