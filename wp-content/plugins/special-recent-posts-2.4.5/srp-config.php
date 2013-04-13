<?php
/*
| ----------------------------------------------------------
| File        : srp-config.php
| Project     : Special Recent Posts PRO plugin for Wordpress
| Version     : 2.4.5
| Description : The main config file.
| Author      : Luca Grandicelli
| Author URL  : http://www.lucagrandicelli.com
| Plugin URL  : http://codecanyon.net/item/special-recent-posts-pro/552356
| Copyright (C) 2011-2012  Luca Grandicelli
| ----------------------------------------------------------
*/

/*
| ----------------------------------------------------
|
| GLOBAL ENVIROMENT VALUES
| These are the default plugin settings.
|
| ****************************************************
| ATTENTION: DO NOT CHANGE THESE VALUES HERE.
| ALL THESE VALUES CAN BE CHANGED IN THE WIDGET PANEL
| OR IN THE SETTINGS PAGE.
| ****************************************************
| ----------------------------------------------------
*/

// Defining global default widget values.
global $srp_default_widget_values;

// The global widget options array.
$srp_default_widget_values = array(
	'widget_title'               => 'Special Recent Posts', // Default widget title. 
	'widget_title_link'          => '',                     // Optional widget title URL.
	'widget_css_id'              => '',                     // Optional widget CSS unique ID.
	'widget_additional_classes'  => '',                     // Optional additional CSS classes.
	'display_thumbnail'          => 'yes',                  // Display thumbnails?
	'widget_title_hide'          => 'no',                   // Hide widget title?
	'thumbnail_type'             => 'thumb-post',           // Select which type of thumbnail to show.
	'thumbnail_width'            => 100,                    // Default thumbnails width.
	'thumbnail_height'           => 100,                    // Default thumbnails height.
	'thumbnail_link'             => 'yes',                  // Link thumbnails to post?
	'thumbnail_custom_field'     => '',                     // Optional custom field for thumbnails image source.
	'thumbnail_rotation'         => 'no',                   // Default thumbnails rotation option.
	'post_title_above_thumb'     => 'no',                   // Set the post title above the thumbnail.
	'post_type'                  => 'post',                 // Default displayed post types.
	'post_status'                => 'publish',              // Default displayed post status.
	'post_limit'                 => 5,                      // Default max number of posts to display.
	'post_title_nolink'          => 'no',                   // Disable post titles link.
	'post_content_type'          => 'content',              // Default post content type.
	'post_content_length'        => '100',                  // Default displayed post content length. 
	'post_content_length_mode'   => 'chars',                // Default displayed post content length mode.
	'post_title_length'          => '100',                  // Default displayed post title length. 
	'post_title_length_mode'     => 'fulltitle',            // Default displayed post title length mode. 
	'post_order'                 => 'DESC',                 // Default displayed post order.
	'post_offset'                => 0,                      // Default post offset.
	'post_random'                => 'no',                   // Randomize displayed posts?
	'post_noimage_skip'          => 'no',                   // Skip posts without images?
	'post_link_excerpt'          => 'no',                   // Link the entire post excerpt to post?
	'post_current_hide'          => 'yes',                  // Hide current post from visualization when in single post view?
	'post_content_mode'          => 'titleexcerpt',         // Default layout content mode.
	'post_date'                  => 'yes',                  // Display post date?
	'post_author'                => 'no',                   // Display post author?
	'post_author_url'            => 'yes',                  // Display post author URL link?
	'post_author_prefix'         => 'Published by: ',       // Default post author PREFIX.
	'post_category'              => 'no',                   // Display post categories?
	'post_category_link'         => 'yes',                  // Display post categories URL link?
	'post_category_separator'    => ',',                    // Default post categories separator.
	'post_category_prefix'       => 'Category: ',           // Display post categories PREFIX.
	'post_tags'                  => 'no',                   // Display post tags?
	'post_tags_prefix'           => 'Tags: ',               // Default post tags PREFIX.
	'post_tags_separator'        => ',',                    // Default post tags separator.
	'post_include'               => '',                     // Filter posts by including post IDs.
	'post_include_sub'           => 'no',                   // Include sub-pages when filtering by pages IDs?
	'post_exclude'               => '',                     // Exclude posts from visualization by IDs.
	'post_meta_key'              => '',                     // Filter post by Meta Key.
	'post_meta_value'            => '',                     // Filter post by Meta Value.
	'custom_post_type'           => '',                     // Filter post by Custom Post Type.
	'tags_include'               => '',                     // Filter post by Tags.
	'noposts_text'               => 'No posts available',   // Default 'No posts available' text.
	'allowed_tags'               => '',                     // List of allowed tags to display in the excerpt visualization.
	'title_string_break'         => '...',                  // Default title string break text.
	'string_break'               => '[...]',                // Default string break text.
	'image_string_break'         => '',                     // Path to optional image string break.
	'string_break_link'          => 'yes',                  // Link (image)string break to post?
	'date_format'                => 'F jS, Y',              // Post date format.
	'date_timeago'               => 'no',                   // Use the date format 'time ago'.
	'category_include'           => '',                     // Filter posts by including categories IDs.
	'category_include_exclusive' => 'no',                   // Include posts that belong exclusively to both categories.
	'category_autofilter'        => 'no',                   // Automatically switch recent posts according to the current views category.
	'category_exclude'           => '',                     // Filter posts by excluding categories IDs.
	'category_title'             => 'no',                   // When filtering by caqtegories, switch the widget title to a linked category title.
	'nofollow_links'             => 'no',                   // Add the 'no-follow' attribute to all widget links.
	'layout_mode'                => 'single_column',        // Default layout visualization mode.
	'layout_num_cols'            => '2',                    // Default number of columns when in multi-columns layout mode.
	'shortcode_generator_area'   => '',                     // Value for generated shortcode.
	'phpcode_generator_area'     => '',                     // Value for generated PHP code.
	'vf_home'                    => 'no',                   // Display widget on home page.
	'vf_allposts'                => 'no',                   // Display widget on all posts.
	'vf_allpages'                => 'no',                   // Display widget on all pages.
	'vf_everything'              => 'yes',                  // Display widget on all site through.
	'vf_allcategories'           => 'no',                   // Display widget on all category pages.
	'vf_allarchives'             => 'no'                    // Display widget on all archive pages.
);

// Defining global default plugin values.
global $srp_default_plugin_values;

// The global plugin options array.
$srp_default_plugin_values = array(
	'srp_version'               => SRP_PLUGIN_VERSION,                 // The Special Recent Post current version.
	'srp_global_post_limit'     => 3,                                  // *** DO NOT CHANGE THIS ***.
	'srp_compatibility_mode'    => 'yes',                              // Compatibility Mode Option.
	'srp_noimage_url'           => SRP_PLUGIN_URL . SRP_DEFAULT_THUMB, // Defaul URL to the no-image placeholder.
	'srp_log_errors_screen'     => 'no',
	'srp_disable_theme_css'     => 'no'                                // Disable plugin CSS?
);
?>