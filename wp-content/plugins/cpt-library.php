<?php
/*
Plugin Name: Reading Library Posts

Plugin URI: http://occupysandy.net

Description: Simply adds a Custom Post Type for a simple reading Library

Version: 1
Author: Occupy
Author URI: http://www.occupywallstreet.net

****************************************************************************************** 
    Copyright (C) 2009-2013 Andre Braekling (email: webmaster@braekling.de)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*******************************************************************************************/

add_action('init', 'register_library_post_type');

function register_library_post_type() {
  $labels = array(
    'name' => 'Library',
    'singular_name' => 'libary-item',
    'add_new' => 'Add New',
    'add_new_item' => 'Add New Library Item',
    'edit_item' => 'Edit Library Item',
    'new_item' => 'New Library Item',
    'all_items' => 'All Library Items',
    'view_item' => 'View Library Item',
    'search_items' => 'Search Library',
    'not_found' =>  'No library items found',
    'not_found_in_trash' => 'No library items found in Trash', 
    'parent_item_colon' => '',
    'menu_name' => 'Library',
    'description' => 'The aftermath of Sandy is complicated. The storm affected many different communities, and made climate change feel much more real. It also provided ways for the class divide to worsen. These big-picture effects can be hard to identify, but the Occupy Sandy Trainers group (who organize orientations and other popular education opportunities) has compiled a list of resources to help us understand Sandy’s real meaning.'
  );

  $args = array(
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true, 
    'show_in_menu' => true, 
    'query_var' => true,
    'rewrite' =>  array( 'slug' => 'library' ),
    'has_archive' => true,
    'capability_type' => 'post',
    'hierarchical' => false,
    'menu_position' => null,
    'supports' => array('title','editor','excerpt','custom-fields','comments','revisions','thumbnail','author'),
	'taxonomies' => 'library-categories'
  ); 

	register_post_type( 'library', $args );
}

function library_flush_rules(){
    //defines the post type so the rules can be flushed.
    register_library_post_type();

    //and flush the rules.
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'library_flush_rules');
add_action( 'init', 'register_library_taxonomy' );

function  register_library_taxonomy () {
	register_taxonomy('library-categories', 'library', array( 
		'hierarchical' => true,
		'label' => 'Library Categories',
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => true,
		'singular_label' => 'Library Category') 
	);

}


?>