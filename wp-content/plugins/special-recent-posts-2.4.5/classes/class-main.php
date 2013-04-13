<?php
/*
| ----------------------------------------------------------
| File        : class-main.php
| Project     : Special Recent Posts PRO plugin for Wordpress
| Version     : 2.4.5
| Description : This is the main plugin class which handles
|               the core of the Special Recent Post PRO plugin.
| Author      : Luca Grandicelli
| Author URL  : http://www.lucagrandicelli.com
| Plugin URL  : http://codecanyon.net/item/special-recent-posts-pro/552356
| Copyright (C) 2011-2012  Luca Grandicelli
| ----------------------------------------------------------
*/

class SpecialRecentPosts {

/*
| ---------------------------------------------
| CLASS PROPERTIES
| ---------------------------------------------
*/

	// Declaring default plugin options array.
	private $plugin_args;
	
	// Declaring widget instance options array.
	private $widget_args;
	
	// Declaring Single Post ID (the current one displayed when in single post view).
	private $singleID;
	
	// Defining Cache Folder Base Path.
	private $cache_basepath;
	
	// Defining upload dir for multi-site hack.
	private $uploads_dir;
	
	// Defining standard available wp image sizes.
	private $wp_thumb_sizes;
	
	// Defining current widget instance id.
	private $widget_id;

/*
| ---------------------------------------------
| CLASS CONSTRUCTOR & DECONSTRUCTOR
| ---------------------------------------------
*/
	// Class Constructor.
	// In this section we define the plugin global admin values and assign the selected widget values.
	public function __construct($args = array(), $widget_id = NULL) {

		// Setting up uploads dir for multi-site hack.
		$this->uploads_dir = wp_upload_dir();
		
		// Including global default widget values.
		global $srp_default_widget_values;
		
		// Setting up plugin options to be available throughout the plugin.
		$this->plugin_args = get_option('srp_plugin_options');
		
		// Setting up standard available wordpress sizes.
		$this->wp_thumb_sizes = array ('thumbnail', 'medium', 'large', 'full');
		
		// Double check if $args is an array.
		$args = (!is_array($args)) ? array() : SpecialRecentPosts::srp_version_map_check($args);
		
		// Setting up widget options to be available throughout the plugin.
		$this->widget_args = array_merge($srp_default_widget_values, $args);
		
		// Setting up post/page ID when on a single post/page.
		if (is_single() || is_page()) {
		
			// Including global $post object.
			global $post;
			
			// Assigning post ID.
			$this->singleID = $post->ID;
		}
		
		// Setting up Cache Folder Base Path.
		$this->cache_basepath = SRP_CACHE_DIR;
		
		// Setting up current widget instance id.
		$this->widget_id = ($widget_id) ? $widget_id : "";
	}
	
	// Class Deconstructor.
	public function __deconstruct() {}

/*
| ---------------------------------------------
| STATIC METHODS
| ---------------------------------------------
*/

	// This method handles all the actions for the plugin initialization.
	static function install_plugin() {
		
		// Loading text domain for translations.
		load_plugin_textdomain(SRP_TRANSLATION_ID, false, dirname(plugin_basename(__FILE__)) . SRP_LANG_FOLDER);
		
		// Doing a global database options check.
		SpecialRecentPosts::srp_dboptions_check();
	}
	
	// This method handles all the actions for the plugin uninstall process.
	static function uninstall_plugin() {
		
		// Deleting main WP Option.
		delete_option('srp_plugin_options');
	}
	
	/*
	| ---------------------------------------------
	| This method handles the visualization filter.
	| It returns true if the widget is allowed to be displayed
	| on the current page/post.
	| ---------------------------------------------
	*/
	static function visualizationCheck($instance, $call) {
		
		// Declaring global plugin values.
		global $srp_default_widget_values;
		
		// Checking source call.
		switch ($call) {
			
			case "phpcall":
			case "shortcode":
				$new_instance = array_merge($srp_default_widget_values, $instance);
			break;
			
			case "widget":
				$new_instance = $instance;
			break;
		}
		
		// Checking if the widget should appear on all the site through.
		if ( (isset($new_instance["vf_everything"])) && ($new_instance["vf_everything"] == 'yes')) {
			return true;
		
		// Checking if the widget should appear on home page.
		} else if ( (isset($new_instance["vf_home"])) && ($new_instance["vf_home"] == 'yes') && (is_home()) ){
			return true;
			
		// Checking if the widget should appear on all posts
		} else if ( (isset($new_instance["vf_allposts"])) && ($new_instance["vf_allposts"] == 'yes') && (is_single()) ) {
			return true;
			
		// Checking if the widget should appear on all pages
		} else if ( (isset($new_instance["vf_allpages"])) && ($new_instance["vf_allpages"] == 'yes') && (is_page()) ) {
			return true;
			
		// Checking if the widget should appear on all category pages
		} else if ( (isset($new_instance["vf_allcategories"])) && ($new_instance["vf_allcategories"] == 'yes') && (is_category()) ) {
			return true;
			
		// Checking if the widget should appear on all archive pages
		} else if ( (isset($new_instance["vf_allarchives"])) && ($new_instance["vf_allarchives"] == 'yes') && (is_archive()) ) {
			return true;
			
		// Widget is not allowed to be displayed here. Return false.
		} else {
			return false;
		}
	}
	
	/*
	| -------------------------------------------------------------------------
	| This method does a version check of old database options,
	| updating and passign existing values to new ones.
	| -------------------------------------------------------------------------
	*/
	static function srp_dboptions_check() {
		
		// Importing global default options array.
		global $srp_default_plugin_values;
		
		// Retrieving current db options.
		$srp_old_plugin_options = get_option('srp_plugin_options');
		
		// Checking if plugin db options exist and performing version comparison.
		if (isset($srp_old_plugin_options)) {

			if (version_compare($srp_old_plugin_options["srp_version"], SRP_PLUGIN_VERSION, '<')) {
			
				// Looping through available list of plugin values.
				foreach($srp_default_plugin_values as $k => $v) {
				
					// Checking for plugin options that haven't changed name since last version. In this case, assign the old value to the current new key.
					if ((isset($srp_old_plugin_options[$k])) && ($k != "srp_version")) $srp_default_plugin_values[$k] = $srp_old_plugin_options[$k];
				}
				
				// Deleting the old entry in the DB.
				delete_option('srp_plugin_options');
				
				// Re-creating a new entry in the database with the new values.
				add_option('srp_plugin_options', $srp_default_plugin_values);
			}
			
		} else {
		
			// First install. Creating WP Option with default values.
			add_option('srp_plugin_options', $srp_default_plugin_values);
		}
	}
	
	/*
	| -------------------------------------------------------------------------
	| This method does a version map check for old option arrays,
    | assigning old values to new ones.
	| -------------------------------------------------------------------------
	*/
	static function srp_version_map_check($oldargs) {
		
		// Including global version map super array.
		global $srp_version_map;
		
		if ( (is_array($oldargs)) && (!empty($oldargs))) {
		
			// Mapping eventual old parameters versions.
			foreach($oldargs as $oldargs_key => $oldargs_value) {
				
				// Checking if old parameter exists in the version map array, and if its name is different than the relative new one.
				if ( (array_key_exists($oldargs_key, $srp_version_map)) && ($oldargs_key != $srp_version_map[$oldargs_key]) ) {
					
					// Creating a new parameter key with the old parameter value, to respect options names.
					$oldargs[$srp_version_map[$oldargs_key]] = $oldargs_value;
					
					// Deleting old parameter key.
					unset($oldargs[$oldargs_key]);
				}
			}
			
		} else {
			
			// If $oldargs is not an array or it's empty, redefine it as a new empty array.
			$oldargs = array();
		}
		
		// Returning updated $args.
		return $oldargs;
	}

/*
| ---------------------------------------------
| CLASS MAIN METHODS
| ---------------------------------------------
*/
	/*
	| -----------------------------------------------------------------------
	| This is the main method for image manipulation. Every fetched image is
    | stored in the cache folder then displayed on screen.
    | Here lies the core of PHP Thumbnailer Class which takes care of all
    | image resizements and manipulations.
	| -----------------------------------------------------------------------
	*/
	private function generateGdImage($post, $image_origin, $image_to_render, $cached_image, $image_width, $image_height, $image_rotation) {

		// Adjust image path by clipping eventual (back)slashes.
		//if (($image_path[0] == "/") || ($image_path[0] == "\\")) $image_path = substr($image_path, 1);

		// Sometimes empty values can be posted to this funcion due to bad database arrays. In any case, exit this function returning false.
		if (!$image_to_render) return false;
		
		// Checking if we're processing a featured image or a first-post image.
		if ($image_origin == "firstimage") {
			
			// Building image path depending wheter this is a multi site WP or not.
			$image_path = (is_multisite())  ? $this->uploads_dir["basedir"] . "/" . $image_to_render : $_SERVER["DOCUMENT_ROOT"] . $image_to_render;
			
		} else {
		
			// Featured image path doesn't need to be processed because it's already a physical path.
			$image_path = $image_to_render;
		}
		
		// Checking if original image exists and can be properly read. If is not, throw an error.
		if ( (!is_file($image_path)) || (!file_exists($image_path))) {
		
			// Checking if "Log Errors on Screen" option is on.
			if ($this->plugin_args["srp_log_errors_screen"] == "yes") {
			
				// Displaying informations about the original file where the error has been found.
				echo __("Problem detected on post ID: $post->ID on file: ", SRP_TRANSLATION_ID) . $image_path . "<br />";
			}
			
			// Return false.
			return false;
		}
		
		/*
		| ---------------------------------------------
		| IMAGE PROCESS
		| ---------------------------------------------
		*/
		
		// Put the whole image process in a Try&Catch block.
		try {

			// Initializing PHP Thumb Class.
			$thumb = PhpThumbFactory::create($image_path);
		
			// Resizing thumbnail with adaptive mode.
			$thumb->adaptiveResize($image_width, $image_height);

			// Checking for rotation value.
			if (isset($image_rotation)) {

				// Checking for display mode.
				switch($image_rotation) {
					
					// No rotation. Do nothing.
					case "no":
					break;
					
					// Rotating CW.
					case "rotate-cw":
						
						// rotating image CW.
						$thumb->rotateImage('CW');
					break;
					
					// Rotating CCW.
					case "rotate-ccw":
					
						// rotating image CCW.
						$thumb->rotateImage('CCW');
					break;
				}
			}

			// Saving generated image in the cache folder.
			$thumb->save($cached_image);
			
			// Checking if thumbnail has been properly saved.
			return (file_exists($cached_image)) ? TRUE : FALSE;
			
		} catch (Exception $e) {

			// Handling catched errors.
			echo $e->getMessage() . "<br />" . __("Problem detected on file: ", SRP_TRANSLATION_ID) . $image_path . "<br />";
			
			// Return false.
			return false;
		}
	}
	
	/*
	| -----------------------------------------------------------------------
	| This is the main method to display the default "no-image" thumbnail.
	| -----------------------------------------------------------------------
	*/
	private function displayDefaultThumb($thumb_width, $thumb_height) {
		
		// Checking if a custom thumbnail url has been provided.
		$noimage_url = ($this->plugin_args['srp_noimage_url'] != '') ? $this->plugin_args['srp_noimage_url'] : SRP_PLUGIN_URL . SRP_DEFAULT_THUMB;

		// Returning default thumbnail image.
		return $this->srp_create_tag('img', null, array('class' => 'srp-widget-thmb', 'src' => $noimage_url, 'alt' => __('No thumbnail available'), 'width' => $thumb_width, 'height' => $thumb_height));
	}

	/*
	| ---------------------------------------------------------------------------------
	| This is the main method which retrieves the first image url in the post content.
	| ---------------------------------------------------------------------------------
	*/
	private function getFirstImageUrl($thumb_width, $thumb_height, $post_title) {
	
		// Including global WP Enviroment.
		global $post, $posts;
		
		// Using REGEX to find the first occurrence of an image tag in the post content.
		$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
		
		//Getting images attached to the post.
		$attachment_args = array(
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'numberposts'    => -1,
			'order'          => 'ASC',
			'post_status'    => null,
			'post_parent'    => $post->ID
		);
		
		$attachments = get_posts($attachment_args);
		
		// Setting up final attachment url to use as source for resize.
		$attachment_url_final = null;
		
		// Check for attachments.
		if (isset($attachments) && (!empty($attachments))) {

			// Looping through sizes to check which one is the next higher value to the desired thumbnail size.
			foreach ($this->wp_thumb_sizes as $size) {
			
				// Getting attachment data.
				$attach_url = wp_get_attachment_image_src($attachments[0]->ID, $size);
				
				// Checking if attachment size is higher than the desired thumbnail size, starting from the smallest wp one available, 'thumbnail'.
				if ( ($attach_url[1] > $thumb_width) && ($attach_url[2] > $thumb_height) ) {
				
					// First available size.
					$attachment_url_final = $attach_url;
					break;
					
				} else {
					
					// Original image is larger than the desired thumbnail dimensions. So stretch it starting from the largest size available.
					$attachment_url_final = wp_get_attachment_image_src($attachments[0]->ID, 'full');
				}
			}
			
			// Getting attachment URL.
			$attachment_url = parse_url($attachment_url_final[0]);
			
			// Getting the first image path from attachment metadata.
			$first_img = $attachment_url["path"];
			
		}	else if (!empty($output)) {
			
			// Image has been found. Analyize and extract the image src url.
			$first_img = $matches[1][0];

			// Checking for attachments.
			if (isset($attachments) && (!empty($attachments))) {
				
				// Cycling through attachments.
				foreach ($attachments as $attachment) {
				
					// Retrieving attachments metadata for multi-site support.
					$attachment_parts = wp_get_attachment_metadata($attachment->ID);
					break;
				}
			}
			
		} else {
			
			// No images are found. Checking if post_noimage_skip option is on. If it's so then skip this image from visualization.
			if ($this->widget_args["post_noimage_skip"] == "yes") {
			
				// Return false.
				return false;
				
			} else {
			
				// No images found in the post content. Display default 'no-image' thumbnail image.
				return ($this->displayDefaultThumb($this->widget_args["thumbnail_width"], $this->widget_args["thumbnail_height"]));
			}
		}
		
		// Parsing image URL.
		$parts = parse_url($first_img);
		
		// Getting the image basename pathinfo.
		$first_img_obj = pathinfo(basename($first_img));
		
		// Building the associated cached image URL.
		$imgabs_cache = $this->cache_basepath . base64_encode(urlencode($this->widget_args["thumbnail_width"] . $this->widget_args["thumbnail_height"] . $this->widget_args["thumbnail_rotation"] . $first_img_obj["filename"])) . "." . $first_img_obj["extension"];
		
		// Building image path depending wheter this is a multi site WP or not.
		$image_to_render = (is_multisite()) ? $attachment_parts["file"] : $parts["path"];
		
		// Checking if the thumbnail already exists. In this case, simply render it. Otherwise generate it.
		if ( (file_exists(SRP_PLUGIN_DIR . $imgabs_cache)) || ($this->generateGdImage($post, 'firstimage', $image_to_render, SRP_PLUGIN_DIR . $imgabs_cache, $thumb_width, $thumb_height, $this->widget_args["thumbnail_rotation"])) ) {
			
			// Building thumbnail image tag.
			return $this->srp_create_tag('img', null, array('class' => 'srp-widget-thmb', 'src' => SRP_PLUGIN_URL . $imgabs_cache, 'alt' => $post_title, 'width' => $this->widget_args["thumbnail_width"], 'height' => $this->widget_args["thumbnail_height"]));
		
		} elseif ($this->widget_args["post_noimage_skip"] == "yes") {

			// If some errors are generated from the thumbnail generation process and  the "post_noimage_skip" option is on, skip this image returning false.
			return false;
			
		} else {
		
			// If some errors are generated from the thumbnail generation process and  the "post_noimage_skip" option is off, display the default no-image placeholder.
			return ($this->displayDefaultThumb($this->widget_args["thumbnail_width"], $this->widget_args["thumbnail_height"]));
		}
	}

	/*
	| -----------------------------------------------------------------------
	| This is the main method to fetch the post thumbnail.
	| -----------------------------------------------------------------------
	*/
	private function displayThumb($post) {
		
		// Checking if thumbnail custom field option is on.
		if ($this->widget_args["thumbnail_custom_field"] != "") {
		
			// Fetching thumbnail post meta.
			$thumb_postmeta = get_post_meta($post->ID, $this->widget_args["thumbnail_custom_field"]);
			
			// Checking if thumbnail custom field option is on and it exists in the current post meta.
			if (!empty($thumb_postmeta)) {
			
				// Checking if thumbnail should be linked to post.
				if ('yes' == $this->widget_args['thumbnail_link']) {
				
					// Building thumbnail link and image tag.	
					$thumbimg  = $this->srp_create_tag('img', null, array('class' => 'srp-widget-thmb', 'src' => $thumb_postmeta[0], 'alt' => the_title_attribute(array('echo' => 0))));
					$thumb     = $this->srp_create_tag('a', $thumbimg, array('class' => 'srp-widget-thmblink', 'href' => get_permalink($post->ID), 'title' => the_title_attribute(array('echo' => 0))));
					
					// Return generated thumnail tag.
					return $thumb;
					
				} else {
				
					// Thumbnail is not linked to post. Building the image tag.
					$thumb = $this->srp_create_tag('img', null, array('class' => 'srp-widget-thmb', 'src' => $thumb_postmeta[0], 'alt' => the_title_attribute(array('echo' => 0))));
					
					// Return generated thumbnail image tag.
					return $thumb;
				}
				
			}
		}
		
		// Checking if featured thumbnails setting is active, if the current post has one and if it exists as file.
		if (function_exists('has_post_thumbnail') && has_post_thumbnail($post->ID)) {
			
			// Fetching Thumbnail ID.
			$thumbnail_id = get_post_thumbnail_id($post->ID);
			
			// Checking if current featured thumbnail comes from the NExtGen Plugin.
			if(stripos($thumbnail_id,'ngg-') !== false && class_exists('nggdb')){
			
				// Creating New NextGen Class instance.
				$nggdb = new nggdb();
				
				// Fetching NGG thumbnail object.
				$nggImage = $nggdb::find_image(str_replace('ngg-','',$thumbnail_id));
				
				// Retrieving physical path of NGG thumbnail image.
				$featured_physical_path = $nggImage->imagePath;
				
				// Fetching NGG thumbnail image URL.
				$featured_thumb_url = $nggImage->imageURL;

			}else{
				// Retrieving featured image attachment src.
				$featured_thumb_attachment = wp_get_attachment_image_src($thumbnail_id, 'large');
				
				// Retrieving physical path of featured image.
				$featured_physical_path = get_attached_file($thumbnail_id);

				// Retrieving featured image url.
				$featured_thumb_url = $featured_thumb_attachment[0];
			}

			// Parsing featured image url.
			$featured_thumb_url_obj = parse_url($featured_thumb_url);
			
			// Retrieving featured image basename.
			$featured_thumb_basename = pathinfo(basename($featured_thumb_url));			
			
			// Building featured image cached path.
			$featured_thumb_cache = $this->cache_basepath . base64_encode(urlencode($this->widget_args["thumbnail_width"] . $this->widget_args["thumbnail_height"] . $this->widget_args["thumbnail_rotation"] . $featured_thumb_basename["filename"])) . "." . $featured_thumb_basename["extension"];
			
			// Checking if the thumbnail already exists. In this case, simply render it. Otherwise generate it.
			if ( (file_exists(SRP_PLUGIN_DIR . $featured_thumb_cache)) || ($this->generateGdImage($post, 'featured', $featured_physical_path, SRP_PLUGIN_DIR . $featured_thumb_cache, $this->widget_args["thumbnail_width"], $this->widget_args["thumbnail_height"], $this->widget_args["thumbnail_rotation"]))) {
			
				// Return cached image as source (URL path).
				$featured_thumb_src = SRP_PLUGIN_URL . $featured_thumb_cache;
				
				// Generating Image HTML Tag.
				$featured_htmltag = $this->srp_create_tag('img', null, array('class' => 'srp-widget-thmb', 'src' => $featured_thumb_src, 'alt' => the_title_attribute(array('echo' => 0))));
				
			} else {
			
				// No featured image has been found. Trying to fetch the first image tag from the post content.
				$featured_htmltag = $this->getFirstImageUrl($this->widget_args["thumbnail_width"], $this->widget_args["thumbnail_height"], the_title_attribute(array('echo' => 0)));
			}

			// Checking if thumbnail should be linked to post.
			if ('yes' == $this->widget_args['thumbnail_link']) {
			
				// Building featured image link tag.
				$featured_temp_content  = $this->srp_create_tag('a', $featured_htmltag, array('class' => 'srp-widget-thmblink', 'href' => get_permalink($post->ID), 'title' => the_title_attribute(array('echo' => 0))));
			
			} else {
			
				// Displaying post thumbnail without link.
				$featured_temp_content = $featured_htmltag;
			}
			
		} else {
			
			// No featured image has been found. Trying to fetch the first image tag from the post content.
			$featured_htmltag = $this->getFirstImageUrl($this->widget_args["thumbnail_width"], $this->widget_args["thumbnail_height"], the_title_attribute(array('echo' => 0)));
			
			// Checking if returned image is real or it is a false value due to skip_noimage_posts option enabled.
			if ($featured_htmltag) {
			
				// Checking if thumbnail should be linked to post.
				if ('yes' == $this->widget_args['thumbnail_link']) {
				
					// Building image tag.
					$featured_temp_content = $this->srp_create_tag('a', $featured_htmltag, array('class' => 'srp-widget-thmblink', 'href' => get_permalink($post->ID), 'title' => the_title_attribute(array('echo' => 0))));
					
				} else {
				
					// Displaying post thumbnail without link.
					$featured_temp_content = $featured_htmltag;
				}
			} else {
			
				// Return false.
				return false;
			}
		}
		
		// Return all the image process.
		return $featured_temp_content;
	}
	
	/*
	| ----------------------------------------------------------------
	| This is the main method to extract and elaborate post excerpt.
	| ----------------------------------------------------------------
	*/
	private function extractContent($post, $content_type) {
		
		// Loading default plugin values.
		$content_length        = $this->widget_args['post_content_length'];
		$content_length_mode   = $this->widget_args['post_content_length_mode'];
		
		// Checking for post content "cut mode".
		switch($content_length_mode) {
		
			case 'words':
				
				// Switching through content type.
				switch($content_type) {
				
					case "content":
						// Sanitizing post content.
						$sanitized_string = $this->srp_sanitize($post->post_content);
					break;
					
					case "excerpt":
						// Sanitizing excerpt.
						$sanitized_string = $this->srp_sanitize($post->post_excerpt);
					break;
				}
				
				// Making a tag clean copy of the excerpt to calculate the total num of characters from words.
				$stripped_string = strip_tags($sanitized_string);
				
				// In order to cut by words without truncating html tags, we need to first calculate the approximate num of characters equal to the number of specified words limit .
				// This is done by the method substrWords() with the $mode parameter set to "count". Instead of returning the cutted string, it will return the num of characters that will be passed to the truncate_text() method as character limit. 
				return $this->srp_truncate_text($sanitized_string, $this->substrWords($stripped_string, $content_length, "count"), '', true);
				
			break;
			
			case 'chars':
				
				// Switching through content type.
				switch($content_type) {
					
					case "content":
						// Retrieving text from post content using 'characters cut'.
						//return mb_substr($this->srp_sanitize($post->post_content), 0, $content_length, 'UTF-8');
						return $this->srp_truncate_text($this->srp_sanitize($post->post_content), $content_length);
					break;
					
					case "excerpt":
						// Return normal excerpt using 'characters cut'.
						return $this->srp_truncate_text($this->srp_sanitize($post->post_excerpt), $content_length);
					break;
				}
				
			break;
			
			case 'fullcontent':
			
				// Switching through content type.
				switch($content_type) {
					
					case "content":
						// Retrieving text from post content using 'characters cut'.
						return $this->srp_sanitize($post->post_content);
					break;
					
					case "excerpt":
						// Return normal excerpt using 'characters cut'.
						return $this->srp_sanitize($post->post_excerpt);
					break;
				}
				
			break;
		}
	}
	
	/*
	| --------------------------------------------------------------
	| This is the main method to extract and elaborate post title.
	| --------------------------------------------------------------
	*/
	private function extractTitle($post) {
		
		// Loading default plugin values.
		$title_length        = $this->widget_args['post_title_length'];
		$title_length_mode   = $this->widget_args['post_title_length_mode'];
		$output_title        = "";
		
		// Checking for "cut mode".
		switch($title_length_mode) {
		
			case 'words':
			
				// Return normal title using 'words cut'.
				$output_title = $this->substrWords($this->srp_sanitize($post->post_title), $title_length);
			break;
			
			case 'chars':
			
				// Return normal title using 'characters cut'.
				$output_title = mb_substr($this->srp_sanitize($post->post_title), 0, $title_length, 'UTF-8');
			break;
			
			case 'fulltitle':
			
				// Return normal title using 'characters cut'.
				return $this->srp_sanitize($post->post_title);
			break;
		}
		
		if ($this->widget_args['title_string_break'] != "") {
			
			// Adding title string break to output.
			$output_title .= $this->widget_args['title_string_break'];
		}
		
		// Returning title.
		return $output_title;
	}

	/*
	| -------------------------------------------------------------------------
	| This is the main method to retrieve posts.
	| -------------------------------------------------------------------------
	*/
	private function getPosts() {
	
		// Defining args array.
		$args = array (
			'post_type'   => $this->widget_args["post_type"],
			'numberposts' => ($this->widget_args["post_limit"] * $this->plugin_args["srp_global_post_limit"]),
			'post_status' => $this->widget_args["post_status"]
		);
		
		// Checking for Compatibility Mode.
		if ($this->plugin_args["srp_compatibility_mode"] == 'yes') {
			
			// Compatibility mode filter. This might cause unknown problems. Deactivate it just in case.
			$args["suppress_filters"] = false;
		}
		
		// Checking for post order option.
		switch ($this->widget_args["post_order"]) {
			
			case "ASC":
			case "DESC":
				
				// Ordering posts by ASC/DESC order
				$args["order"] = $this->widget_args["post_order"];
			break;
			
			case "modified":
			
				// Ordering posts by last updated entries.
				$args["orderby"] = $this->widget_args["post_order"];
			break;
			
			case "alphab":
				
				// Ordering posts in alphabetically order.
				$args["orderby"] = 'title';
				$args["order"]   = "ASC";
			break;
			
			case "comment_count":
			
				// Ordering posts by most commented entries.
				$args["orderby"] = $this->widget_args["post_order"];
			break;
			
			default:
			
				// Default behaviour: ordering by DESC.
				$args["order"] = "DESC";
			break;
		}
		
		// Checking for custom post type option.
		if ($this->widget_args["custom_post_type"] != '') {
			
			// Filtering result posts by category ID.
			$args["post_type"] = $this->widget_args["custom_post_type"];
		}
		
		// Checking if category auto filtering is applied.
		if ( ($this->widget_args["category_autofilter"] == 'yes') && ( (is_category()) || (is_archive()) ) ) {
			
			// Fetching current category object.
			$thisCat = get_category(get_query_var('cat'),false);
			
			// Filtering according to the current viewd category page.
			$args["category"] = $thisCat->cat_ID;
			
		} else {
			
			// Checking if category filter is applied.
			if ($this->widget_args["category_include"] != '') {

				// Checking for Exclusive Filtering.
				if ($this->widget_args["category_include_exclusive"] == 'yes') {
				
					// Filtering posts that belong exclusively to all listed categories.
					$args["category__and"] = explode(',', $this->widget_args["category_include"]);
					
				} else {
				
					// Filtering result posts by category ID.
					$args["category"] = $this->widget_args["category_include"];
				}
				
			} else if ($this->widget_args["category_exclude"] != '') {
				
				// Category exclude filter is on.
				if (strpos($this->widget_args["category_exclude"], ',') !== false) {
				
					// Creating a temporary array to change sign on exclusion filtering values.
					$tempExcludeArray = explode(',', $this->widget_args["category_exclude"]);
					
					// Applying the "-" sign to match wordpress exclusion rules.
					foreach($tempExcludeArray as $k => &$v) {
						$v = '-' . $v;
					}
					
					// Excluding Categories by category ID.
					$args["category"] = implode(',', $tempExcludeArray);
					
				} else {
					
					// Only one ID provided. Applying the "-" sign to match Wordpress rules.
					$args["category"] = '-' . $this->widget_args["category_exclude"];
				}

			}
		}
		
		// Checking if "post current hide" option is enabled.
		if ($this->widget_args["post_current_hide"] == 'yes') {
		
			// Filtering current post from visualization.
			$args["exclude"] = $this->singleID;
		}
		
		// Check if post offset option is enabled.
		if ($this->widget_args["post_offset"] != 0) {
		
			// Applying post offset.
			$args["offset"] = $this->widget_args["post_offset"];
		}
		
		// Checking if exclude posts option is applied.
		if (!empty($this->widget_args["post_exclude"])) {
			
			// Excluding result posts by post IDs.
			$args["exclude"] = $this->widget_args["post_exclude"];
		}
		
		// Checking if include posts option is applied.
		if (!empty($this->widget_args["post_include"])) {
			
			// Including result posts by post IDs.
			$args["include"] = $this->widget_args["post_include"];
		}
		
		// Checking if tags filtering is on.
		if (!empty($this->widget_args["tags_include"])) {
			
			// Filtering result posts by tag.
			$args["tag"] = $this->widget_args["tags_include"];
		}
		
		// Checking if post custom field meta key option is applied.
		if (!empty($this->widget_args["post_meta_key"])) {
			
			// Filtering result posts by meta key.
			$args["meta_key"] = $this->widget_args["post_meta_key"];
		}
		
		// Check if post custom field meta value option is applied.
		if (!empty($this->widget_args["post_meta_value"])) {
			
			// Filtering result posts by meta value.
			$args["meta_value"] = $this->widget_args["post_meta_value"];
		}

		// Calling built-in Wordpress 'get_posts' function.
		$result_posts = get_posts($args);
		
		// Checking if result posts array is empty.
		if (empty($result_posts)) {
		
			// No recent posts available. Return empty array.
			return $result_posts;
		}
		
		// Checking if "post include sub" option is enabled. In this case, try to fetch all the child pages of the current post.
		if ( ($this->widget_args["post_include"] != "") && ($this->widget_args["post_include_sub"] == "yes") ) {
			
			// Setting up children array args.
			$children_args = $args;
			
			// Deactivate "include" option in order to reply the get_posts behaviour.
			$children_args["include"] = NULL;
			
			// Looping through children results.
			foreach($result_posts as $post) {
			
				// Setting up post parent ID.
				$children_args["post_parent"] = $post->ID;
				
				// Getting post children results.
				$children_posts = get_children($children_args);
				
				// Checking for valid results.
				if (!empty($children_posts)) {
				
					// Merging children and fathers.
					$result_posts = ($result_posts+$children_posts);
				}
			}
		}
		
		// Checking if random posts option is on.
		if ($this->widget_args["post_random"] == "yes") {
			
			// Shuffling the result array.
			shuffle($result_posts);
		}
		
		// Fixing issues that let included IDs override the max number of post displayed.
		$output_array = array_slice($result_posts, 0, $args["numberposts"]);

		// Return result array.
		return $output_array;
	}
	
	/*
	| -------------------------------------------------------------------------
	| This is the main method to display posts.
	| -------------------------------------------------------------------------
	*/
	public function displayPosts($widget_call = NULL, $return_mode) {
	
		// Declaring global $post variable.
		global $post;
		
		// Building special HTML comment with current SRP version.
		$srp_content  = "<!-- BOF Special Recent Posts PRO ver" . SRP_PLUGIN_VERSION . " -->";
		
		// Checking for "widget title hide" option.
		if ('yes' != $this->widget_args["widget_title_hide"]) {
		
			// Checking if SRP is displaying a category filter result and if it should use the linked category title.
			if ( ($this->widget_args["category_include"] != '') && ($this->widget_args["category_title"] == "yes") ) {
				
				// Fetching category link.
				$srp_category_link = get_category_link($this->widget_args["category_include"]);
				
				// Building category title HTML.
				$category_title_link = $this->srp_create_tag('a', get_cat_name($this->widget_args["category_include"]), array('class' => 'srp-widget-title-link', 'href' => $srp_category_link, 'title' => get_cat_name($this->widget_args["category_include"])));
				$srp_content .= $this->srp_create_tag('h3', $category_title_link, array('class' => 'widget-title'));
				
			} else {
			
				// Checking if widget title should be linked to a custom URL.
				if ($this->widget_args["widget_title_link"] != "") {
				
					// Building widget title HTML.
					$widget_title_link = $this->srp_create_tag('a', $this->srp_sanitize($this->widget_args["widget_title"]), array('class' => 'srp-widget-title-link', 'href' => $this->widget_args["widget_title_link"]));
					$srp_content .= $this->srp_create_tag('h3', $widget_title_link, array('class' => 'widget-title'));
				
				} else {
				
					// Building normal widget title HTML.
					$srp_content .= $this->srp_create_tag('h3', $this->srp_sanitize($this->widget_args["widget_title"]), array('class' => 'widget-title'));
				}
			}
		}
		
		// Opening Widget Container.
		$srp_content .= "<div ";
		
		// Checking for optional unique CSS ID or additional classes.
		if ($this->widget_args["widget_css_id"])
			$srp_content .= "id=\"" . $this->widget_args["widget_css_id"] . "\" ";
		
		$srp_content .= "class=\"srp-widget-container";
		
		if ($this->widget_args["widget_additional_classes"] != "")
			$srp_content .= " " . $this->widget_args["widget_additional_classes"];
		
		$srp_content .=  "\">";
		
		// Fetching recent posts.
		$recent_posts = $this->getPosts();
		
		// Checking if posts are available.
		if (empty($recent_posts)) {
		
			// No posts available. Displaying "no posts" message.
			$srp_content .= $this->srp_create_tag('p', $this->srp_sanitize($this->widget_args['noposts_text']));
			
		} else {
			
			// Defining global column counter.
			$post_colrow_counter = 0;
			
			// Defining global post counter.
			$post_global_counter = 0;
			
			// Recent posts are available. Cyclying through result posts.
			foreach($recent_posts as $post) {
				
				// Adding +1 to global post counter.
				$post_global_counter++;
				
				// Adding +1 to post column counter.
				$post_colrow_counter++;
			
				// Preparing access to all post data.
				setup_postdata($post);
				
				// Fetching post image.
				$post_thumb_content = $this->displayThumb($post);
				
				// Checking if current post has at least an image. If not, and Post Noimage Skip option is enabled, skip it.
				if (!$post_thumb_content)
					continue;
				
				// Setting up additional built-in classes.
				switch($this->widget_args["layout_mode"]) {
				
					case "single_column":
						$single_post_additional_classes = "srp-single-column";
					break;
					
					case "single_row":
						$single_post_additional_classes = "srp-single-row";
					break;
					
					case "multi_column":
						$single_post_additional_classes = "srp-multi-column";
					break;
				}

				// Opening column container.
				if ( ($post_colrow_counter == 1) && ($this->widget_args["layout_mode"] == "multi_column") ) {
				
					$srp_content .= "<div class=\"srp-widget-row\">";
				}
				
				// Opening single post container.
				$srp_content .= "<div id=\"" . $this->widget_id . "-srp-singlepost-" . $post_global_counter . "\" class=\"srp-widget-singlepost " . $single_post_additional_classes . "\">";
				
				
				
				// Checking if "post title above thumb" option is on.
				if ($this->widget_args["post_title_above_thumb"] == 'yes') {
					
					// Setting up post title HTML attributes
					$ptitle_heading_atts = array('class' => 'srp-post-title');
					
					// Checking if "post titles nolink" option is on.
					if ('yes' == $this->widget_args["post_title_nolink"]) {
					
						// Building post title HTML.
						$srp_content .= $this->srp_create_tag('h4', $this->extractTitle($post), $ptitle_heading_atts);
						
					} else {
					
						// Building linked post title HTML
						$ptitlelink  =  $this->srp_create_tag('a', $this->extractTitle($post), array('class' => 'srp-post-title-link', 'href' => get_permalink($post->ID), 'title' => the_title_attribute(array('echo' => 0))));
						$srp_content .= $this->srp_create_tag('h4', $ptitlelink, $ptitle_heading_atts);
					}
				}
				
				
				// Checking if thumbnail option is on.
				if ($this->widget_args["display_thumbnail"] == 'yes') {
					
					switch($this->widget_args["thumbnail_type"]) {
					
						case "thumb-post":
							// Opening container for thumbnail image.
							$srp_content .= $this->srp_create_tag('div', $post_thumb_content, array('class' => 'srp-thumbnail-box'));
						break;
						
						case "thumb-author":
							$thumb        = $this->srp_create_tag('a', get_avatar(get_the_author_meta('ID'), $this->widget_args["thumbnail_width"]), array('class' => 'srp-widget-thmblink', 'href' => get_permalink($post->ID), 'title' => the_title_attribute(array('echo' => 0))));
							$srp_content .= $this->srp_create_tag('div', $thumb, array('class' => 'srp-thumbnail-box'));
						break;
					}
				}
				
				// Checking for "no content at all" option. In this case, leave the content-box empty.
				if ('thumbonly' != $this->widget_args['post_content_mode']) {
				
					// Opening container for Content Box.
					$srp_content .= "<div class=\"srp-content-box\">";
				
					// Setting up post title HTML attributes
					$ptitle_heading_atts = array('class' => 'srp-post-title');
					
					// Checking if "post title above thumb" option is on.
					if ($this->widget_args["post_title_above_thumb"] == 'no') {
						
						// Checking if "post titles nolink" option is on.
						if ('yes' == $this->widget_args["post_title_nolink"]) {
						
							// Building post title HTML.
							$srp_content .= $this->srp_create_tag('h4', $this->extractTitle($post), $ptitle_heading_atts);
							
						} else {
						
							// Building linked post title HTML
							$ptitlelink  =  $this->srp_create_tag('a', $this->extractTitle($post), array('class' => 'srp-post-title-link', 'href' => get_permalink($post->ID), 'title' => the_title_attribute(array('echo' => 0))));
							$srp_content .= $this->srp_create_tag('h4', $ptitlelink, $ptitle_heading_atts);
						}
					}
					
					// Checking if "post_date" option is on.
					if ('yes' == $this->widget_args["post_date"]) {
					
						// Switching betweeb date formats.
						$date_format_mode = ($this->widget_args["date_timeago"] == 'yes') ? $this->themeblvd_time_ago() : get_the_time($this->widget_args['date_format']);
						
						// Building post date container.
						$srp_content .= $this->srp_create_tag('p', $date_format_mode, array('class' => 'srp-widget-date'));
					}

					// Checking if "post author" option is on.
					if ('yes' == $this->widget_args["post_author"]) {
						
						// Setting up category list string.
						$post_author_string = "";
						
						// Checking for post author PREFIX.
						if (!empty($this->widget_args["post_author_prefix"])) {
							
							// Building post author PREFIX HTML.
							$post_author_string .= $this->widget_args["post_author_prefix"];
						}
						
						// Checking if post author link option is on.
						$post_author_string .= ('yes' == $this->widget_args["post_author_url"]) ? get_the_author_link() : get_the_author();
						
						// Building post author HTML.
						$srp_content .= $this->srp_create_tag('p', $post_author_string, array('class' => 'srp-widget-author'));
					}
					
					// Checking if category option is on.
					if ('yes' == $this->widget_args["post_category"]) {
						
						// Setting up category list string.
						$post_cat_string = "";
						
						// Setting up category list.
						$post_cat_list = "";
						
						// Checking for post category PREFIX.
						if (!empty($this->widget_args["post_category_prefix"])) {
							
							// Building post category PREFIX HTML.
							$post_cat_string .= $this->widget_args["post_category_prefix"];
						}
						
						// Retrieving categories array.
						$srp_categories = get_the_category($post->ID);
						
						// Checking if "post category link" option is on.
						if ('yes' == $this->widget_args["post_category_link"]) {
							
							// Looping through categories array.
							foreach($srp_categories as $srp_cat) {
							
								// Fetching the current category link.
								$srp_category_link = get_category_link($srp_cat->cat_ID);
								
								// Building category link HTML.						
								$post_cat_list .= $this->srp_create_tag('a', $srp_cat->cat_name, array('href' => $srp_category_link, 'title' => $srp_cat->cat_name)) . $this->widget_args["post_category_separator"];
							}
							
						} else {
							
							// Looping through categories array.
							foreach($srp_categories as $srp_cat) {
							
								// Filling categories list.
								$post_cat_list .= $srp_cat->cat_name . $this->widget_args["post_category_separator"];
							}
						}
						
						// Right trimming the last category separator on the category list.
						$post_cat_string .= rtrim($post_cat_list, $this->widget_args["post_category_separator"]);
						
						// Building post category HTML.
						$srp_content .= $this->srp_create_tag('p', $post_cat_string, array('class' => 'srp-widget-category'));
					}
					
					// Checking for Post Content Option.
					if ('titleexcerpt' == $this->widget_args["post_content_mode"]) {
						
						// Building post excerpt container.
						$srp_content .= "<p class=\"srp-widget-excerpt\">";
						
						// Checking if "post link excerpt" option is on.
						if ($this->widget_args["post_link_excerpt"] == "yes") {
							
							// Building link tag to enclose the entire excerpt in.
							$srp_content .= $this->srp_create_tag('a', $this->extractContent($post, $this->widget_args["post_content_type"]), array('class' => 'srp-linked-excerpt', 'href' => get_permalink($post->ID), 'title' => the_title_attribute(array('echo' => 0))));
							
						} else {
						
							// Fetching post excerpt.
							$srp_content .= $this->extractContent($post, $this->widget_args["post_content_type"]);
						}
						
						// Checking if "image string break" option is set.
						if ($this->widget_args['image_string_break'] != "") {
							
							// Building HTML image tag for the image string break.
							$image_string_break = $this->srp_create_tag('img', null, array('class' => 'srp-widget-stringbreak-image', 'src' => $this->srp_sanitize($this->widget_args['image_string_break']), 'alt' => the_title_attribute(array('echo' => 0))));
							
							// Checking if "string break link" option is on.
							if ('yes' == $this->widget_args['string_break_link']) {
							
								// Building image string break link HTML tag.
								$srp_content .= $this->srp_create_tag('a', $image_string_break, array('class' => 'srp-widget-stringbreak-link-image', 'href' => get_permalink($post->ID), 'title' => the_title_attribute(array('echo' => 0))));
							
							} else {
							
								// Fetching the image string break URL.
								$srp_content .= $image_string_break;
							}
						
						} elseif ($this->widget_args['string_break'] != "") {
						
							// Using a text stringbreak. Checking if string break should be linked to post.
							if ('yes' == $this->widget_args['string_break_link']) {
							
								// Building string break link HTML tag.					
								$srp_content .= $this->srp_create_tag('a', $this->srp_sanitize($this->widget_args['string_break']), array('class' => 'srp-widget-stringbreak-link', 'href' => get_permalink($post->ID), 'title' => the_title_attribute(array('echo' => 0))));
								
							} else {
								
								// Building string break HTML without link.
								$srp_content .= $this->srp_create_tag('span', $this->srp_sanitize($this->widget_args['string_break']), array('class' => 'srp-widget-stringbreak'));
							}
						}
						
						// Closing post excerpt container.
						$srp_content .= "</p>";
					}
					
					// Checking if "post tags" option is on.
					if ('yes' == $this->widget_args["post_tags"]) {
						
						// Retrieving list of associated post tags.
						$post_tags = get_the_tags($post->ID);
						
						// Checking for valid results.
						if (!empty($post_tags)) {
						
							// Setting up Tag list.
							$tag_list = "";
							
							// Setting up tags list string.
							$post_tags_string = "";

							// Checking for post tags PREFIX.
							if (!empty($this->widget_args["post_tags_prefix"])) {
								
								// Inserting Post Category PREFIX.
								$post_tags_string .= $this->widget_args["post_tags_prefix"];
							}
							
							// Looping through tags.
							foreach($post_tags as $tag) {
							
								// Getting tag link.
								$tag_link  = get_tag_link($tag->term_id);
								
								// Building tag link HTML.
								$tag_list .= $this->srp_create_tag('a', $tag->name, array('href' => $tag_link)) . $this->widget_args["post_tags_separator"];
							}
							
							// Right trimming the last tag separator from the tag list.
							$post_tags_string .= rtrim($tag_list, $this->widget_args["post_tags_separator"]);
							
							// Building post tags HTML.
							$srp_content .= $this->srp_create_tag('p', $post_tags_string, array('class' => 'srp-widget-tags'));
						}
					}
					
					// EOF Content Box.
					$srp_content .= "</div>";
					
					// Adding a clear property for eventual floating elements.
					$srp_content .= $this->srp_create_tag('div', null, array('style' => 'clear:both; height: 0px;'));
				}
				
				// Closing Single Post Container.
				$srp_content .= "</div>";
				
				// Checking for "multi column" layout mode.
				if ($this->widget_args["layout_mode"] == "multi_column") {
				
					// Let's do some math to calculate if this should be the last post of the column or not.
					if ( ($post_colrow_counter == $this->widget_args["layout_num_cols"]) || ( ($post_colrow_counter < $this->widget_args["layout_num_cols"]) && ($post_global_counter == $this->widget_args["post_limit"]) ) ) {
						
						// Closing column.
						$srp_content .= "</div>";
						
						// Resetting column counter.
						$post_colrow_counter = 0;
						
					}
				}

				// Here we stop the visualization process to the max number of posts provided in the widget option panel.
				if ($post_global_counter == $this->widget_args["post_limit"]) break;
				
			} // EOF foreach cycle.
			
			// Resetting $post data array.
			wp_reset_postdata();
			
		} // EOF Empty posts check.
		
		// Adding a clear property for eventual floating elements.
		$srp_content .= $this->srp_create_tag('div', null, array('style' => 'clear:both; height: 0px;'));
		
		// Closing Widget Container.
		$srp_content .= "</div>";
		
		// Closing Special Recent Post PRO Version comment.
		$srp_content .= "<!-- EOF Special Recent Posts PRO ver" . SRP_PLUGIN_VERSION . " -->";
		
		// Switching through display return mode.
		switch($return_mode) {
		
			// Display HTML on screen.
			case"print":
				echo $srp_content;
			break;
			
			// Return HTML.
			case "return":
				return $srp_content;
			break;
		}
	}

/*
| -------------------------------------------------------------------------
| UTILITY METHODS
| In this section we collect several general utility methods.
| -------------------------------------------------------------------------
*/

	/*
	| -------------------------------------------------------------------------
	| This is the main method to build HTML tags.
	| -------------------------------------------------------------------------
	*/
	private function srp_create_tag($tagname, $tag_content = NULL, $tag_attrs = NULL) {
	
		// Defining DOM root.
		$tagdom = new DOMDocument('1.0');
		
		// Creating tag element.
		$tag = $tagdom->createElement($tagname, htmlentities($tag_content, ENT_QUOTES, "UTF-8"));
	
		// Checking if attributes array is empty.
		if (!empty($tag_attrs) && (isset($tag_attrs)) ) {
		
			// Looping through attributes.
			foreach ($tag_attrs as $att_name => $att_value) {
			
				// Setting attribute.
				$tag->setAttribute($att_name, $att_value);
			}
			
			// If the tag is a link (<a>), do the "nofollow_links" optio check. If it's enables, add the nofollow attribute.
			if ( ($tagname == "a") && ($this->widget_args["nofollow_links"] == 'yes') ) $tag->setAttribute('rel', 'nofollow');
		}
		
		// Appending created tag to DOM root.
		$tagdom->appendChild($tag);
		
		// Saving HTML.
		$taghtml = trim($tagdom->saveHTML());

		// Cleaning DOM Root.
		unset($tagdom);
		
		// Return the HTML tag.
		return htmlspecialchars_decode($taghtml);
	}

	/*
	| -------------------------------------------------------------------------
	| This the main method to sanitize strings output.
	| -------------------------------------------------------------------------
	*/
	private function srp_sanitize($string) {
		
		// We need to remove all the exceeding stuff. Removing shortcodes and slashes.
		$temp_output = trim(stripslashes(strip_shortcodes($string)));
		
		// Applying qTranslate Filter if this exists.
		if (function_exists('qtrans_useCurrentLanguageIfNotFoundShowAvailable')) {
			$temp_output = qtrans_useCurrentLanguageIfNotFoundShowAvailable($temp_output);
		}
		
		// If "allowed_tags" option is on, keep them separated from strip_tags.
		if (!empty($this->widget_args["allowed_tags"])) {
		
			// Stripping tags except the ones specified.
			return strip_tags($temp_output, htmlspecialchars_decode($this->widget_args["allowed_tags"]));
			
		} else {
		
			// Otherwise completely strip tags from text.
			return strip_tags($temp_output);
		}
	}
	
	/*
	| -------------------------------------------------------------------------
	| This method uses the same logic of PHP function 'substr',
	| but works with words instead of characters.
	| -------------------------------------------------------------------------
	*/
	private function substrWords($str, $n, $mode = 'return') {
		
		// Checking if max length is equal to original string length. In that case, return the string without making any 'cut'.
		if (str_word_count($str, 0) > $n) {

			// Uses PHP 'str_word_count' function to extract total words and put them into an array.
			$w = explode(" ", $str);
			
			// Let's cut the array using our max length variable ($n).
			array_splice($w, $n);
			
			// Switch mode.
			switch($mode) {
			
				case "return":
					// Re-converting array to string and return.
					return implode(" ", $w);
				breaK;
				
				case "count":
					// Return count.
					return strlen(implode(" ", $w));
				break;
			}
			
		} else {
			
			// Switch mode.
			switch($mode) {
				case "return":
					// Return string as it is, without making any 'cut'.
					return $str;
				breaK;
				
				case "count":
					// Return count.
					return strlen($str);
				break;
			}
		}
	}
	
	/*
	| -------------------------------------------------------------------------
	| This method truncates a string preserving html tags integrity.
	| Only works on characters. (Credits: http://jsfromhell.com)
	| -------------------------------------------------------------------------
	*/
	
	private function srp_truncate_text($s, $l, $e = '') {
	
		// Defining Internal counter.
		$i = 0;
		
		// Dafining array for tags collecting.
		$tags = array();
		
		// Checking if source string is HTML.
		if (!empty($this->widget_args["allowed_tags"])) {
			
			// Regex to find tags.
			preg_match_all('/<[^>]+>([^<]*)/', $s, $m, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
			
			// Looping inside the string.
			foreach ($m as $o) {
				
				// Check if chars limit is equal or superior the string length.
				if (($o[0][1] - $i) >= $l) {
					break;
				}
				
				// Trimming the string.
				$t = mb_substr(strtok($o[0][0], " \t\n\r\0\x0B>"), 0, 1, 'utf-8');
				
				// Repairing HTML tags.
				if($t[0] != '/') {
					$tags[] = $t;
					
				} elseif (end($tags) == substr($t, 0, 1, 'utf-8')) {
					
					array_pop($tags);
				}
				
				$i += $o[1][1] - $o[0][1];
			}
		}
		
		// Return result string.
		return mb_substr($s, 0, $l = min(strlen($s),  $l + $i), 'utf-8') . (count($tags = array_reverse($tags)) ? '</' . implode('></', $tags) . '>' : '') . (strlen($s) > $l ? $e : '');
	}
	
	// This method produces the magic "time ago" format on dates.
	// Function by: Jason Bobich http://www.jasonbobich.com/
	private function themeblvd_time_ago() {
 
		global $post;
	 
		$date = get_post_time('G', true, $post);
	 
		/**
		 * Where you see 'themeblvd' below, you'd
		 * want to replace those with whatever term
		 * you're using in your theme to provide
		 * support for localization.
		 */ 
	 
		// Array of time period chunks
		$chunks = array(
			array( 60 * 60 * 24 * 365 , __( 'year', 'themeblvd' ), __( 'years', 'themeblvd' ) ),
			array( 60 * 60 * 24 * 30 , __( 'month', 'themeblvd' ), __( 'months', 'themeblvd' ) ),
			array( 60 * 60 * 24 * 7, __( 'week', 'themeblvd' ), __( 'weeks', 'themeblvd' ) ),
			array( 60 * 60 * 24 , __( 'day', 'themeblvd' ), __( 'days', 'themeblvd' ) ),
			array( 60 * 60 , __( 'hour', 'themeblvd' ), __( 'hours', 'themeblvd' ) ),
			array( 60 , __( 'minute', 'themeblvd' ), __( 'minutes', 'themeblvd' ) ),
			array( 1, __( 'second', 'themeblvd' ), __( 'seconds', 'themeblvd' ) )
		);
	 
		if ( !is_numeric( $date ) ) {
			$time_chunks = explode( ':', str_replace( ' ', ':', $date ) );
			$date_chunks = explode( '-', str_replace( ' ', '-', $date ) );
			$date = gmmktime( (int)$time_chunks[1], (int)$time_chunks[2], (int)$time_chunks[3], (int)$date_chunks[1], (int)$date_chunks[2], (int)$date_chunks[0] );
		}
	 
		$current_time = current_time( 'mysql', $gmt = 0 );
		$newer_date = strtotime( $current_time );
	 
		// Difference in seconds
		$since = $newer_date - $date;
	 
		// Something went wrong with date calculation and we ended up with a negative date.
		if ( 0 > $since )
			return __( 'sometime', 'themeblvd' );
	 
		/**
		 * We only want to output one chunks of time here, eg:
		 * x years
		 * xx months
		 * so there's only one bit of calculation below:
		 */
	 
		//Step one: the first chunk
		for ( $i = 0, $j = count($chunks); $i < $j; $i++) {
			$seconds = $chunks[$i][0];
	 
			// Finding the biggest chunk (if the chunk fits, break)
			if ( ( $count = floor($since / $seconds) ) != 0 )
				break;
		}
	 
		// Set output var
		$output = ( 1 == $count ) ? '1 '. $chunks[$i][1] : $count . ' ' . $chunks[$i][2];
	 
	 
		if ( !(int)trim($output) ){
			$output = '0 ' . __( 'seconds', 'themeblvd' );
		}
	 
		$output .= __(' ago', 'themeblvd');
	 
		return $output;
	}
} // EOF Class.