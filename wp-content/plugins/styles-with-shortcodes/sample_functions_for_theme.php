<?php

/**
Theme integration instructions:
copy the plugin folder into your theme directory
copy the code in this file into your themes functions.php file
 **/

//--SWS IN A THEME--------------------------------------------------------------------------------------------------------------------
if(!defined('WPCSS')):
$sws_settings = array(
	'show_ui'=>true, // show the shortcodes creation tool
	'options'=>array(
		'show_in_metabox'=>true,  //show the shortcode insert tool in a metabox instead of the s icon
		'option_show_in_metabox'=>false,//enable the option to choose between showing the insert tool in metabox or S icon	
		'metabox_title'=> __('Styles with shortcodes','css')
	),	
	'options_parameters'=>array(
		'page_title'			=>'SWS Options', //the page title of the options menu
		'menu_text'				=>'SWS Options', //the options menu text on the admin menu
		'option_menu_parent'	=>'plugins.php'  //where does the Options menu should show?
	)
);
//--- If you place the plugin folder into a diferent location, adjust the following 2 lines:
define('WPCSS_PATH', dirname( __FILE__ ). "/styles-with-shortcodes/" ); 
define("WPCSS_URL", get_bloginfo('stylesheet_directory') . '/styles-with-shortcodes/' );

require WPCSS_PATH.'styles-with-shortcodes-theme.php';
global $sws_plugin;
$sws_plugin = new custom_shortcode_styling($sws_settings);
$sws_plugin->plugins_loaded();
//-- SWS Bundle Installation script:---------------------------------
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

if ( is_admin() && isset($_GET['activated'] ) && $pagenow == "themes.php" ) {
//workaround until finding a hook for activation of themes.
	sws_install();
}
//-------------------------------------------------------- 
endif;
//--------------------------------------------------------------------------------------------------------------------


?>