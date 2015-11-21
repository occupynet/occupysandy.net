<?php
class Slickr_Flickr_Options {
	const OPTIONS_NAME  = 'slickr_flickr_options';		
	protected static $defaults = array(	    
		'id' => '',
	    'group' => 'n',
	    'use_key' => '',
	    'api_key' => '',
	    'use_rss' => '',  
	    'search' => 'photos',
 		'photo_id' => '',
	    'tag' => '',
	    'tagmode' => '',
	    'set' => '',
	    'gallery' => '',
	    'license' => '',
	    'date_type' => '',
	    'date' => '',
	    'before' => '',
	    'after' => '',
	    'text' => '',
	    'cache' => 'on',
	    'cache_expiry' => 43200,
	    'items' => '20',
	    'type' => 'gallery',
	    'captions' => 'on',
	    'lightbox' => 'sf-lightbox',
	    'galleria' => 'galleria-latest',
	    'galleria_theme' => 'classic',
	    'galleria_theme_loading' => 'static',
    	'galleria_themes_folder' => 'galleria/themes',
    	'galleria_options' => '',
    	'options' => '',
    	'delay' => '5',
    	'transition' => '0.5',
    	'start' => '1',
    	'autoplay' => 'on',
    	'pause' => '',
    	'orientation' => 'landscape',
    	'size' => 'medium',
    	'responsive' => '',
    	'bottom' => '',
    	'thumbnail_size' => '',
    	'thumbnail_scale' => '',
    	'thumbnail_captions' => '',
    	'thumbnail_border' => '',
    	'photos_per_row' => '',
		'class' => '',
    	'align' => '',
    	'border' => '',
    	'descriptions' => '',
    	'ptags' => '',
    	'flickr_link' => '',
    	'flickr_link_title' => 'Click to see the photo on Flickr',
    	'flickr_link_target' => '',
    	'link' => '',
    	'target' => '_self',
    	'attribution' => '',
    	'nav' => '',
    	'sort' => '',
    	'direction' => '',
    	'per_page' => 50,
    	'page' => 1,
    	'pagination'=> '',
	    'element_id' => '',
    	'restrict' => '',
    	'scripts_in_footer' => false,
    	'silent' => false,
        'message' => '' 	
	); 

    protected static $options = null;	

    public static function init($more = array()) {
        if (self::$options === null) self::$options = new Slickr_Flickr_DIY_Options(self::OPTIONS_NAME, self::$defaults);
		if (count($more) > 0) self::$options->add_defaults($more);
    }

	public static function get_default($option_name) {
	    return self::$options->get_default($option_name); 
	}

	public static function get_options ($cache = true) {
		return self::$options->get_options($cache = true); 
	}

	public static function get_option($option_name, $cache = true) {
	    return self::$options->get_option($option_name, $cache); 
	}

	public static function save_options ($options) {
		return self::$options->save_options($options);
	}

	public static function validate_options ($defaults, $options) {
		return self::$options->validate_options((array)$defaults, (array)$options);
	}

	public static function upgrade_options() {
		return self::$options->upgrade_options();
	}

}
