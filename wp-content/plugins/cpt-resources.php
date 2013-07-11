<?php
/*
Plugin Name: Resource Listing Posts

Plugin URI: http://occupysandy.net

Description: Simply adds a Custom Post Type for a simple Resources listing

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

add_action( 'init', 'register_resources_post_type' );

function register_resources_post_type() {
  $labels = array(
    'name' => 'Resources',
    'singular_name' => 'resource',
    'add_new' => 'Add New',
    'add_new_item' => 'Add New Resource',
    'edit_item' => 'Edit Resource',
    'new_item' => 'New Resource',
    'all_items' => 'All Resources',
    'view_item' => 'View Resource',
    'search_items' => 'Search Resources',
    'not_found' =>  'No resources found',
    'not_found_in_trash' => 'No resources found in Trash', 
    'parent_item_colon' => '',
    'menu_name' => 'Resources',
    'description' => 'These resources aim to assist volunteers, coordinators, individuals and families impacted by Hurricane Sandy. They include important safety information for volunteers, coordinators and anyone involved in the cleanup effort. Many are fliers that can be printed by coordinators to hand out to volunteers and individuals in impacted areas.'
  );

  $args = array(
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true, 
    'show_in_menu' => true, 
    'query_var' => true,
    // 'rewrite' => array( 'slug' => 'projects' ),
    // 'has_archive' => true,
    'rewrite' =>  array( 'slug' => 'resources' ),
    'has_archive' => true,
    'capability_type' => 'post',
    'hierarchical' => false,
    'menu_position' => null,
    'supports' => array('title','editor','excerpt','custom-fields','comments','revisions','thumbnail','author'),
    'taxonomies' => array('resource-categories')
  ); 

  register_post_type( 'resources', $args );

}

add_action( 'init', 'register_resource_taxonomy' );

function  register_resource_taxonomy () {
    register_taxonomy('resource-categories', 'resources', 
    array( 'hierarchical' => true,
        'label' => 'Resource Categories',
        'show_ui' => true,
        'query_var' => true,
        'rewrite' => true,
        'singular_label' => 'Resource Category') 
    );
}

/**
 *  Register Field Groups
 *
 *  The register_field_group function accepts 1 array which holds the relevant data to register a field group
 *  You may edit the array as you see fit. However, this may result in errors if the array is not compatible with ACF
 */

if(function_exists("register_field_group"))
{
    register_field_group(array (
        'id' => 'acf_resource-fields',
        'title' => 'Resource Fields',
        'fields' => array (
            array (
                'key' => 'resource-link',
                'label' => 'Link',
                'name' => 'resource-link',
                'type' => 'radio',
                'instructions' => 'Please indicate if resource is internal (links to page on this site) or external (links to another site).',
                'required' => 1,
                'choices' => array (
                    'internal' => 'Internal',
                    'external' => 'External',
                ),
                'other_choice' => 0,
                'save_other_choice' => 0,
                'default_value' => 'internal',
                'layout' => 'horizontal',
            ),
            array (
                'key' => 'resource-url',
                'label' => 'External URL',
                'name' => 'resource-url',
                'type' => 'text',
                'instructions' => 'If the page link is external, enter URL to where resource should link',
                'conditional_logic' => array (
                    'status' => 1,
                    'rules' => array (
                        'field' => 'resource-link',
                        'operator' => '==',
                        'value' => 'external',
                    ),
                    'allorany' => 'all',
                ),
                'default_value' => '',
                'formatting' => 'none',
            ),
        ),
        'location' => array (
            array (
                'param' => 'post_type',
                'operator' => '==',
                'value' => 'resources',
                'order_no' => 0,
                'group_no' => 0,
            ),
        ),
        'options' => array (
            'position' => 'normal',
            'layout' => 'default',
            'hide_on_screen' => array (
                0 => 'custom_fields',
                1 => 'slug',
                2 => 'format',
                3 => 'categories',
                4 => 'send-trackbacks',
            ),
        ),
        'menu_order' => 0,
    ));
}

?>