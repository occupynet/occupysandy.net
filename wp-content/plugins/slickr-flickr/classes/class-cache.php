<?php
class Slickr_Flickr_Cache {

	const FLICKR_CACHE_TABLE = 'flickr_cache'; 
	const FLICKR_CACHE_FOLDER = 'flickr-cache';
	const FLICKR_CACHE_CUSTOM = 'sflickr';

	static function get_cache($id) { return @unserialize(base64_decode(get_transient(self::FLICKR_CACHE_CUSTOM.$id))); }
	
	static function set_cache($id, $photos, $expiry) { 
	  return set_transient(self::FLICKR_CACHE_CUSTOM.$id, serialize(base64_encode($photos)), $expiry); 
	}

	static function clear_cache() {
    	self::clear_rss_cache();
    	self::clear_rss_cache_transient();
    	self::clear_transient_flickr_cache();
    	self::clear_flickr_cache();
    	self::optimise_options();
	}

	private static function clear_rss_cache() {
    	global $wpdb;
    	$table = self::get_options_table();
    	$sql = "DELETE FROM ".$table." WHERE option_name LIKE 'rss_%' and LENGTH(option_name) IN (36, 39)";
    	$wpdb->query($sql);
	}
	
	private static function clear_rss_cache_transient() {
    	global $wpdb;
    	$table = self::get_options_table();
    	$sql = "DELETE FROM ".$table." WHERE option_name LIKE '_transient_feed_%' or option_name LIKE '_transient_rss_%' or option_name LIKE '_transient_timeout_%'";
    	$wpdb->query($sql);
	}
	
	private static function clear_transient_flickr_cache() {
    	global $wpdb;
    	$table = self::get_options_table();
    	$wpdb->query("DELETE FROM ".$table." WHERE option_name LIKE '_transient_".self::FLICKR_CACHE_CUSTOM."%' ");
    	$wpdb->query("DELETE FROM ".$table." WHERE option_name LIKE '_transient_timeout_".self::FLICKR_CACHE_CUSTOM."%' ");
    	$wpdb->query("DELETE FROM ".$table." WHERE option_name LIKE '_transient_flickr_%' ");
    	$wpdb->query("DELETE FROM ".$table." WHERE option_name LIKE '_transient_timeout_flickr_%' ");
	}

	private static function clear_flickr_cache() {
    	global $wpdb;
		$table_name = self::get_cache_table();
		if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {				
	    	try {
	    		$wpdb->query("TRUNCATE TABLE ".$table_name); //ignore error if user does not have permission to truncate table
			}
			catch (Exception $e) {
			}
		}
	}

	private static function optimise_options() {
    	global $wpdb;
    	try {
    		$wpdb->query("OPTIMIZE TABLE ".self::get_options_table()); //ignore error if user does not have permission to optimise table
		}
		catch (Exception $e) {
		}
	}
	
	private static function get_options_table() {
    	global $wpdb, $table_prefix;
    	$prefix = $table_prefix ? $table_prefix : $wpdb->prefix;
    	return $prefix.'options';
    }

	private static function get_cache_table() {
    	global $wpdb, $table_prefix;
    	$prefix = $table_prefix ? $table_prefix : $wpdb->prefix;
    	return $prefix . self::FLICKR_CACHE_TABLE;
    }	

}