<?php
/*
| ----------------------------------------------------------
| File        : srp-versionmap.php
| Project     : Special Recent Posts PRO plugin for Wordpress
| Version     : 2.4.5
| Description : This file contains a super array which maps
|               all the previous option keys with the current ones.
| Author      : Luca Grandicelli
| Author URL  : http://www.lucagrandicelli.com
| Plugin URL  : http://codecanyon.net/item/special-recent-posts-pro/552356
| Copyright (C) 2011-2012  Luca Grandicelli
| ----------------------------------------------------------
*/

// Defining the version map super array.
$srp_version_map = array(
	
	// Mapping the widget options for the 1.x SRP versions.
	'srp_post_type'                 => 'post_type',
	'srp_post_status_option'        => 'post_status',
	'srp_custom_post_type_option'   => 'custom_post_type',
	'srp_widget_title'              => 'widget_title',
	'srp_widget_title_hide_option'  => 'widget_title_hide',
	'srp_thumbnail_wdg_width'       => 'thumbnail_width',
	'srp_thumbnail_wdg_height'      => 'thumbnail_height',
	'srp_thumbnail_option'          => 'display_thumbnail',
	'srp_thumbnail_rotation'        => 'thumbnail_rotation',
	'srp_number_post_option'        => 'post_limit',
	'srp_wdg_excerpt_length'        => 'post_content_length',
	'srp_wdg_excerpt_length_mode'   => 'post_content_length_mode',
	'srp_wdg_title_length'          => 'post_title_length',
	'srp_wdg_title_length_mode'     => 'post_title_length_mode',
	'srp_order_post_option'         => 'post_order',
	'srp_post_global_offset_option' => 'post_offset',
	'srp_orderby_post_option'       => 'post_random',
	'srp_filter_cat_option'         => 'category_include',
	'srp_content_post_option'       => 'post_content_mode',
	'srp_post_date_option'          => 'post_date',
	'srp_include_option'            => 'post_include',
	'srp_exclude_option'            => 'post_exclude',
	'srp_add_nofollow_option'       => 'nofollow_links'
);
	