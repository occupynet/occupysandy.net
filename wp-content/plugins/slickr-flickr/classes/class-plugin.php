<?php
class Slickr_Flickr_Plugin {

    const ACTIVATE_KEY = 'slickr_flickr_activation';

	static function init() {
		$dir = dirname(__FILE__) . '/';
		require_once($dir . 'class-diy-options.php');		
		require_once($dir . 'class-options.php');
		require_once($dir . 'class-utils.php');
		require_once($dir . 'class-cache.php');
		Slickr_Flickr_Options::init();
	}

	static function public_init() {
		$dir = dirname(__FILE__) . '/';
		require_once($dir . 'class-feed-photo.php');
		require_once($dir . 'class-api-photo.php');
		require_once($dir . 'class-feed.php');
		require_once($dir . 'class-fetcher.php');
		require_once($dir . 'class-display.php');
		require_once($dir . 'class-public.php');		
		Slickr_Flickr_Public::init();
	}

	static function admin_init() {
		$dir = dirname(__FILE__) . '/';	
		require_once($dir . 'class-tooltip.php');
		require_once($dir . 'class-admin.php');
		require_once($dir . 'class-feed-widget.php');
		require_once($dir . 'class-dashboard.php');
		new Slickr_Flickr_Dashboard(SLICKR_FLICKR_VERSION, SLICKR_FLICKR_PATH, SLICKR_FLICKR_SLUG);
 		if (get_option(self::ACTIVATE_KEY)) add_action('admin_init',array(__CLASS__, 'upgrade'));   		
	}
	
	static function upgrade() { 
		Slickr_Flickr_Options::upgrade_options(); //save any new options on plugin update
		delete_option(self::ACTIVATE_KEY); //delete key so upgrade runs only on activation		
		Slickr_Flickr_Cache::clear_cache(); //clear out the cache
	}		

	static function activate() { //called on plugin activation
    	update_option(self::ACTIVATE_KEY, true);
	}	

}
