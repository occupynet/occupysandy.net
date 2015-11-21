<?php
/*
 * Plugin Name: Slickr Flickr
 * Plugin URI: http://www.slickrflickr.com
 * Description: Displays photos from Flickr in slideshows and galleries
 * Version: 2.5.4
 * Author: Russell Jamieson
 * Author URI: http://www.diywebmastery.com/about/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
define('SLICKR_FLICKR_VERSION','2.5.4');
define('SLICKR_FLICKR_PLUGIN_NAME', 'Slickr Flickr');
define('SLICKR_FLICKR_SLUG', 'slickr-flickr');
define('SLICKR_FLICKR_PATH', SLICKR_FLICKR_SLUG.'/slickr-flickr.php');
define('SLICKR_FLICKR_PLUGIN_URL', plugins_url(SLICKR_FLICKR_SLUG));
define('SLICKR_FLICKR_ICON', 'dashicons-format-gallery');
define('SLICKR_FLICKR_DOMAIN', 'SLICKR_FLICKR_DOMAIN');
define('SLICKR_FLICKR_HOME', 'http://www.slickrflickr.com');
define('SLICKR_FLICKR_PRO', 'http://www.slickrflickr.com/members');
define('SLICKR_FLICKR_NEWS', 'http://www.slickrflickr.com/tags/newsfeed/feed/?images=1&featured_only=1');
if (!defined('DIYWEBMASTERY_NEWS')) define('DIYWEBMASTERY_NEWS', 'http://www.diywebmastery.com/tags/newsfeed/feed/?images=1&featured_only=1');
require_once(dirname(__FILE__) . '/classes/class-plugin.php');
register_activation_hook(SLICKR_FLICKR_PATH, array('Slickr_Flickr_Plugin', 'activate'));
add_action ('init',  array('Slickr_Flickr_Plugin', 'init'), 0);
add_action ('init',  array('Slickr_Flickr_Plugin', is_admin() ? 'admin_init' : 'public_init'), 0);
?>