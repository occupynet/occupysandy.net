<?php

/**
 * @author W-Shadow 
 * @copyright 2012
 *
 * The uninstallation script.
 */

if( defined( 'ABSPATH') && defined('WP_UNINSTALL_PLUGIN') ) {

	//Remove the plugin's settings
	delete_option('ws_menu_editor_pro');
	if ( function_exists('delete_site_option') ){
		delete_site_option('ws_menu_editor_pro');
	}
	//Remove update metadata
	delete_option('ame_pro_external_updates');

    //Remove hint visibility flags
    if ( function_exists('delete_metadata') ) {
        delete_metadata('user', 0, 'ame_show_hints', '', true);
    }

	//Remove license data (if any).
	if ( file_exists(dirname(__FILE__) . '/extras.php') ) {
		require_once dirname(__FILE__) . '/extras.php';
		if ( isset($ameProLicenseManager) ) {
			$ameProLicenseManager->unlicenseThisSite();
		}
	}
}