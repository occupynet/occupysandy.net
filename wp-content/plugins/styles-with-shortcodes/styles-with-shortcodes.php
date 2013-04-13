<?php

/**
Plugin Name: Styles with Shortcodes for WordPress
Plugin URI: http://plugins.righthere.com/styles-with-shortcodes/
Description: Now you can customize your content faster and easier than ever before with custom style shortcodes. This plugin lets you easily customize your content by using Shortcodes. Choose from close to 100 built in shortcodes like; jQuery Toggles and Tabs, Tooltips, Column shortcodes, Gallery and Image shortcodes, Button Styles, Alert Box Styles, Pullquotes, Blockquotes, Twitter buttons, Retweet button, Facebook Like buttons, Follow me on Twitter buttons, Google +1 button, LinkedIn button and many more!
You can even create your own or import and export shortcodes.
Version: 1.7.9 rev23212
Author: Alberto Lau (RightHere LLC)
Author URI: http://plugins.righthere.com
 **/

define('WPCSS','1.7.9'); 
define('WPCSS_PATH', plugin_dir_path(__FILE__) ); 
define("WPCSS_URL", plugin_dir_url(__FILE__) );

if(!function_exists('property_exists')):
function property_exists($o,$p){
	return is_object($o) && 'NULL'!==gettype($o->$p);
}
endif;

if(!class_exists('custom_shortcode_styling')){
	require_once WPCSS_PATH.'includes/class.custom_shortcode_styling.php';
}

$settings = array(
	'options'=>array(
		'option_show_in_metabox'=>true
	)/*,
	'editor_parameters'=>array(
		'metabox_title'=> __('Styles with shortcodes','css'),
		'show_in_metabox'=>true	
	)*/
);

global $sws_plugin;
$sws_plugin = new custom_shortcode_styling($settings);

//-- Installation script:---------------------------------
if(!function_exists('sws_install')):
function sws_install(){
	global $bundle;
	require_once WPCSS_PATH.'includes/bundle.php';	
	require_once WPCSS_PATH.'includes/class.ImportExport.php';
	require_once WPCSS_PATH.'includes/class.CSShortcodes.php';
	CSShortcodes::init_taxonomy();
	CSShortcodes::init_post_type(true);
	$o = new ImportExport(); 
	$o->import_bundle($bundle,$error);
	//--custom capabilities
	global $wp_roles;
	$wp_roles->add_cap('administrator','manage_sws' );	
	//--
	return true;
}
register_activation_hook(__FILE__, 'sws_install');
endif;
//-------------------------------------------------------- 
?>