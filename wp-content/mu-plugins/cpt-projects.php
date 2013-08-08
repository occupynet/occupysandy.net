<?php
/*
Plugin Name: Projects Listing Posts

Plugin URI: http://occupysandy.net

Description: Simply adds a Custom Post Type for a simple Project listing

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

// Fields 
add_action('acf/register_fields', 'sandy_register_fields');

function sandy_register_fields()
{
    // include_once('add-ons/acf-repeater/repeater.php');
    // include_once('add-ons/acf-gallery/gallery.php');
    // include_once('add-ons/acf-flexible-content/flexible-content.php');
}

// Options Page 
// include_once( 'add-ons/acf-options-page/acf-options-page.php' );

function register_project_post_type() {
  $labels = array(
    'name' => 'Projects',
    'singular_name' => 'project',
    'add_new' => 'Add New',
    'add_new_item' => 'Add New Project',
    'edit_item' => 'Edit Project',
    'new_item' => 'New Project',
    'all_items' => 'All Projects',
    'view_item' => 'View Project',
    'search_items' => 'Search Projects',
    'not_found' =>  'No projects found',
    'not_found_in_trash' => 'No projects found in Trash', 
    'parent_item_colon' => '',
    'menu_name' => 'Projects',
    'description' => '<p>Welcome to the OccupySandy Projects list, where you can learn more about the work being done by people involved in the Occupy Sandy effort.</p>
<p style="clear:both; margin: 25px 0;">The projects listed here have been compiled by the Occupy Sandy Projects Team.</p>
<p><a class="button" href="/projects/team">About the projects team</a> &nbsp; <a class="button" href="/projects/submit">Submit a project</a></p>'
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
    'rewrite' => false,
    'has_archive' => true,
    'capability_type' => 'post',
    'hierarchical' => false,
    'menu_position' => null,
    'supports' => array('title','editor','excerpt','custom-fields','comments','revisions','thumbnail','author'),
	'taxonomies' => array('project_categories','project_services')
  ); 

  register_post_type( 'projects', $args );
}

function project_flush_rules(){
    //defines the post type so the rules can be flushed.
    register_project_post_type();

    //and flush the rules.
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'project_flush_rules');

add_action( 'init', 'register_project_post_type' );
add_action( 'init', 'register_project_taxonomy' );

function  register_project_taxonomy () {
	register_taxonomy('project-categories',
		array (0 => 'projects',)
		,array ( 'hierarchical' => true,
			'label' => 'Project Categories',
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => array('slug' => 'project-categories'),
			'singular_label' => 'Project Category') 
		);
	register_taxonomy('project-services',
		array (0 => 'projects',)
		,array ( 'hierarchical' => true,
			'label' => 'Project Services',
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => array('slug' => 'project-services'),
			'singular_label' => 'Project Service') 
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
        'id' => 'acf_project-fields',
        'title' => 'Project Fields',
        'fields' => array (
            array (
                'key' => 'project-background',
                'label' => 'Background',
                'name' => 'project-background',
                'type' => 'wysiwyg',
                'default_value' => '',
                'toolbar' => 'basic',
                'media_upload' => 'yes',
            ),
            array (
                'key' => 'help-needed',
                'label' => 'Help Needed',
                'name' => 'help-needed',
                'type' => 'wysiwyg',
                'default_value' => '',
                'toolbar' => 'basic',
                'media_upload' => 'yes',
            ),
            array (
                'key' => 'project-status',
                'label' => 'Volunteers Needed?',
                'name' => 'project-status',
                'type' => 'true_false',
                'message' => '',
                'default_value' => 1,
            ),
            array (
                'key' => 'project-volunteer-link',
                'label' => 'Volunteer Link',
                'name' => 'project-volunteer-link',
                'type' => 'text',
                'conditional_logic' => array (
                    'status' => 1,
                    'rules' => array (
                        array (
                            'field' => 'project-status',
                            'operator' => '==',
                            'value' => '1',
                        ),
                    ),
                    'allorany' => 'all',
                ),
                'default_value' => '',
                'formatting' => 'none',
            ),
            array (
                'key' => 'project-partners',
                'label' => 'Partners',
                'name' => 'project-partners',
                'type' => 'wysiwyg',
                'default_value' => '',
                'toolbar' => 'basic',
                'media_upload' => 'yes',
            ),
            array (
                'key' => 'project-location-name',
                'label' => 'Location',
                'name' => 'project-location-name',
                'type' => 'text',
                'default_value' => '',
                'formatting' => 'none',
            ),
            array (
                'key' => 'project-location-street',
                'label' => 'Street',
                'name' => 'project-location-street',
                'type' => 'text',
                'default_value' => '',
                'formatting' => 'none',
            ),
            array (
                'key' => 'project-location-city',
                'label' => 'City',
                'name' => 'project-location-city',
                'type' => 'text',
                'default_value' => '',
                'formatting' => 'none',
            ),
            array (
                'key' => 'project-location-state',
                'label' => 'State',
                'name' => 'project-location-state',
                'type' => 'text',
                'default_value' => '',
                'formatting' => 'none',
            ),
            array (
                'key' => 'project-location-zip',
                'label' => 'Zip',
                'name' => 'project-location-zip',
                'type' => 'text',
                'default_value' => '',
                'formatting' => 'none',
            ),
            array (
                'key' => 'project-contact-name-external',
                'label' => 'Contact Name (Public)',
                'name' => 'project-contact-name-external',
                'type' => 'text',
                'default_value' => '',
                'formatting' => 'none',
            ),
            array (
                'key' => 'project-contact-email-external',
                'label' => 'Contact Email (Public)',
                'name' => 'project-contact-email-external',
                'type' => 'email',
                'required' => 1,
                'default_value' => '',
            ),
            array (
                'key' => 'project-contact-phone-external',
                'label' => 'Contact Phone (Public)',
                'name' => 'project-contact-phone-external',
                'type' => 'text',
                'default_value' => '',
                'formatting' => 'none',
            ),
            array (
                'key' => 'project-website',
                'label' => 'Website',
                'name' => 'project-website',
                'type' => 'text',
                'default_value' => '',
                'formatting' => 'none',
            ),
            array (
                'key' => 'project-facebook-link',
                'label' => 'Facebook Link',
                'name' => 'project-facebook-link',
                'type' => 'text',
                'default_value' => '',
                'formatting' => 'none',
            ),
            array (
                'key' => 'project-twitter-handle',
                'label' => 'Twitter Link',
                'name' => 'project-twitter-handle',
                'type' => 'text',
                'default_value' => '',
                'formatting' => 'none',
            ),
            array (
                'key' => 'project-flickr',
                'label' => 'Flickr Link',
                'name' => 'project-flickr',
                'type' => 'text',
                'default_value' => '',
                'formatting' => 'none',
            ),
            array (
                'key' => 'project-media-link',
                'label' => 'Media Link',
                'name' => 'project-media-link',
                'type' => 'text',
                'default_value' => '',
                'formatting' => 'none',
            ),
            array (
                'key' => 'project-contact-name-internal',
                'label' => 'Contact Name (Private)',
                'name' => 'project-contact-name-internal',
                'type' => 'text',
                'default_value' => '',
                'formatting' => 'none',
            ),
            array (
                'key' => 'project-contact-email-internal',
                'label' => 'Contact Email (Private)',
                'name' => 'project-contact-email-internal',
                'type' => 'email',
                'default_value' => '',
            ),
            array (
                'key' => 'project-contact-phone-internal',
                'label' => 'Contact Phone (Internal)',
                'name' => 'project-contact-phone-internal',
                'type' => 'text',
                'default_value' => '',
                'formatting' => 'none',
            ),
            array (
                'key' => 'project-fundraising-status',
                'label' => 'Fundraising Status',
                'name' => 'project-fundraising-status',
                'type' => 'true_false',
                'instructions' => 'Are you actively trying to raise funds?',
                'required' => 1,
                'message' => '',
                'default_value' => 1,
            ),
            array (
                'key' => 'project-fundraising-goal',
                'label' => 'Fundraising Goal',
                'name' => 'project-fundraising-goal',
                'type' => 'number',
                'instructions' => 'How much money are you trying to raise for this project?',
                'conditional_logic' => array (
                    'status' => 1,
                    'rules' => array (
                        array (
                            'field' => 'project-fundraising-status',
                            'operator' => '==',
                            'value' => '1',
                        ),
                    ),
                    'allorany' => 'all',
                ),
                'default_value' => '',
                'min' => '',
                'max' => '',
                'step' => '',
            ),
            array (
                'key' => 'project-donate-link',
                'label' => 'Donation Link',
                'name' => 'project-donate-link',
                'type' => 'text',
                'conditional_logic' => array (
                    'status' => 1,
                    'rules' => array (
                        array (
                            'field' => 'project-fundraising-status',
                            'operator' => '==',
                            'value' => '1',
                        ),
                    ),
                    'allorany' => 'all',
                ),
                'default_value' => '',
                'formatting' => 'none',
            ),
            array (
                'key' => 'project_spokescouncil_member',
                'label' => 'Spokescouncil Member',
                'name' => 'project_spokescouncil_member',
                'type' => 'true_false',
                'required' => 1,
                'message' => '',
                'default_value' => '',
            ),
            array (
                'key' => 'project_goals',
                'label' => 'Goals',
                'name' => 'project_goals',
                'type' => 'wysiwyg',
                'default_value' => '',
                'toolbar' => 'basic',
                'media_upload' => 'no',
            ),
            array (
                'key' => 'project_member_list',
                'label' => 'Member List',
                'name' => 'project_member_list',
                'type' => 'wysiwyg',
                'default_value' => '',
                'toolbar' => 'basic',
                'media_upload' => 'no',
            ),
            array (
                'key' => 'project_endorsements',
                'label' => 'Endorsements',
                'name' => 'project_endorsements',
                'type' => 'wysiwyg',
                'default_value' => '',
                'toolbar' => 'basic',
                'media_upload' => 'no',
            ),
            array (
                'key' => 'project_additional_information',
                'label' => 'Additional Information',
                'name' => 'project_additional_information',
                'type' => 'wysiwyg',
                'default_value' => '',
                'toolbar' => 'basic',
                'media_upload' => 'no',
            ),
        ),
        'location' => array (
            array (
                array (
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'projects',
                    'order_no' => 0,
                    'group_no' => 0,
                ),
            ),
        ),
        'options' => array (
            'position' => 'normal',
            'layout' => 'default',
            'hide_on_screen' => array (
                0 => 'custom_fields',
                1 => 'comments',
                2 => 'slug',
                3 => 'format',
                4 => 'categories',
                5 => 'tags',
                6 => 'send-trackbacks',
            ),
        ),
        'menu_order' => 0,
    ));
}


?>