<?php
/*
Plugin Name: Special Recent Posts PRO
Plugin URI: http://codecanyon.net/item/special-recent-posts-pro/552356
Description: Special Recent Posts PRO is a very powerful plugin/widget which displays your latest posts with thumbnails. <strong>The perfect solution for online magazines or simple blogs, it comes with more than 60+ customization options available.</strong> To get started: 1) Click the "Activate" link to the left of this description. 2) Go to the Special Recent Post <a href="options-general.php?page=special-recent-posts-2.4.5/lib/lib-admin.php">settings page</a> and configure the plugin. 3) Go to the Widgets page and drag the 'Special Recent Posts PRO' widget onto your sidebar and configure its settings. 4) If you wish to use PHP code or shortcodes for your pages, simply copy and paste the code provided under the panel "Code Generator" in the widget options. Or, alternatively, please refer to the manual provided in the plugin folder. You can also check the readme.txt file for further details. 5) Enjoy.
Version: 2.4.5
Author: Luca Grandicelli
Author URI: http://www.lucagrandicelli.com
Copyright (C) 2011-2012  Luca Grandicelli
*/

/*
| ---------------------------------------------
| GLOBAL DECLARATIONS
| In this section we define the enviroment
| basic constants and global paths.
| ---------------------------------------------
*/

define('SRP_PLUGIN_URL'          , plugin_dir_url( __FILE__ ));                  // Defining plugin url path.
define('SRP_PLUGIN_DIR'          , dirname(__FILE__) . '/');                     // Defining plugin dir path.
define('SRP_PLUGIN_MAINFILE'     , __FILE__);                                    // Defining plugin main filename.
define('SRP_PLUGIN_VERSION'      , '2.4.5');                                     // Defining plugin version.
define('SRP_PLUGIN_VERSION_MODE' , 'PRO');                                       // Defining plugin version mode.
define('SRP_REQUIRED_PHPVER'     , '5.0.0');                                     // Defining required PHP version.
define('SRP_TRANSLATION_ID'      , 'srplang');                                   // Defining gettext translation ID.
define('SRP_CLASS_FOLDER'        , 'classes/');                                  // Defining path: main plugin classes folder.
define('SRP_CSS_FOLDER'          , 'css/');                                      // Defining path: CSS folder.
define('SRP_JS_FOLDER'           , 'js/');                                       // Defining path: javascript folder.
define('SRP_IMAGES_FOLDER'       , 'images/');                                   // Defining path: global images folder.
define('SRP_LIB_FOLDER'          , 'lib/');                                      // Defining path: external libraries folder.
define('SRP_LANG_FOLDER'         , 'languages/');                                // Defining path: language packs folder.
define('SRP_CACHE_DIR'           , 'cache/');                                    // Defining path: cached images folder. [Remember to set this folder to 0775 or 0777 permissions.]
define('SRP_ICONS_FOLDER'        , SRP_IMAGES_FOLDER    . 'icons/');             // Defining path: icons images folder.
define('SRP_STRUCTURE_FOLDER'    , SRP_IMAGES_FOLDER    . 'structure/');         // Defining path: structure images folder.
define('SRP_ADMIN_CSS'           , SRP_CSS_FOLDER       . 'css-admin.css');      // Defining path: administration stylesheet.
define('SRP_FRONT_CSS'           , SRP_CSS_FOLDER       . 'css-front.css');      // Defining path: Front End CSS.
define('SRP_IEFIX_CSS'           , SRP_CSS_FOLDER       . 'css-ie7-fix.css');    // Defining path: IE browsers CSS fix.
define('SRP_JS_INIT'             , SRP_JS_FOLDER        . 'srp-init.js');        // Defining path: custom js init script.
define('SRP_DEFAULT_THUMB'       , SRP_ICONS_FOLDER     . 'default-thumb.gif');  // Defining path: default no-image thumbnail placeholder.
define('SRP_WIDGET_HEADER'       , SRP_STRUCTURE_FOLDER . 'widget_header.gif');  // Defining path: the widget header image.

/*
| ---------------------------------------------
| GLOBAL INCLUDES
| In this section we include all the needed
| files for the plugin to properly work.
| ---------------------------------------------
*/
require_once('srp-config.php');                                               // Including main config file.
require_once('srp-versionmap.php');                                           // Including the version map superarry, to prevent version conflicts.
require_once(SRP_LIB_FOLDER    . 'phpthumb/ThumbLib.inc.php');                // Including PHP Thumbnailer external library.
require_once(SRP_CLASS_FOLDER  . 'class-main.php');                           // Including main plugin class.
require_once(SRP_CLASS_FOLDER  . 'class-widgets.php');                        // Including widgets class.
require_once(SRP_LIB_FOLDER    . 'lib-admin.php');                            // Including plugin admin library.

/*
| -------------------------------------------------------------
| External functions to call plugin from PHP inline code.
| Check the manual provided for further configuration settings.
| -------------------------------------------------------------
*/

// External PHP function call.
function special_recent_posts($args = array()) {

	// Checking Visualization filter.
	if (SpecialRecentPosts::visualizationCheck($args, 'phpcall')) {
	
		// Creating an instance of Special Posts Class with widget args passed in manual mode.
		$srp = new SpecialRecentPosts($args);
		
		// Displaying posts.
		$srp->displayPosts(NULL, 'print');
	}
}

// Shortcode function call.
function srp_shortcode($atts) {

	// Including external widget values.
	global $srp_default_widget_values;
	
	// Checking Visualization filter.
	if (SpecialRecentPosts::visualizationCheck($srp_default_widget_values, 'shortcode')) {
	
		// If shortcode comes without parameters, make $atts a valid array.
		if (!is_array($atts)) $atts = array();
		
		// Assembling default widget options with available shortcode options.
		extract(shortcode_atts($srp_default_widget_values, $atts));
		
		// Creating an instance of Special Posts Class with widget args passed in manual mode.
		$srp = new SpecialRecentPosts($atts);
		
		// Displaying Posts.
		return $srp->displayPosts(NULL, 'return');
	}
}

// Load Translation Table.
load_plugin_textdomain(SRP_TRANSLATION_ID, false, dirname(plugin_basename( __FILE__ )) . '/' . SRP_LANG_FOLDER );

/*
| ---------------------------------------------
| PLUGIN HOOKS & ACTIONS
| Listing plugin hooks and actions.
| ---------------------------------------------
*/

register_activation_hook(__FILE__     , array('SpecialRecentPosts', 'install_plugin'));   // Registering plugin activation hook.
register_deactivation_hook( __FILE__  , array('SpecialRecentPosts', 'uninstall_plugin')); // Registering plugin uninstall hook.
add_action('widgets_init'             , 'srp_install_widgets');                           // Defining actions on widgets page init.
add_action('init'                     , 'srp_init');                                      // Defining actions on every plugin init.
add_action('admin_init'               , 'srp_admin_init');                                // Defining actions on admin page init.
add_action('admin_menu'               , 'srp_admin_setup');                               // Defining actions for admin page setup.
add_action('wp_head'                  , 'srp_front_head', 0);                             // Inlcuding main theme CSS in the header section.
add_shortcode('srp'                   , 'srp_shortcode' );                                // Registering SRP Shortcode.