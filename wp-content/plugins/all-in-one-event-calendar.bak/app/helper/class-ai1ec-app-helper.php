<?php
//
//  class-ai1ec-app-helper.php
//  all-in-one-event-calendar
//
//  Created by The Seed Studio on 2011-07-13.
//

/**
 * Ai1ec_App_Helper class
 *
 * @package Helpers
 * @author time.ly
 **/
class Ai1ec_App_Helper {
	/**
	 * _instance class variable
	 *
	 * Class instance
	 *
	 * @var null | object
	 **/
	private static $_instance = NULL;

	/**
	 * Constructor
	 *
	 * Default constructor
	 **/
	private function __construct() { }

	/**
	 * get_instance function
	 *
	 * Return singleton instance
	 *
	 * @return object
	 **/
	static function get_instance() {
		if( self::$_instance === NULL ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * map_meta_cap function
	 *
	 * Assigns proper capability
	 *
	 * @return void
	 **/
	function map_meta_cap( $caps, $cap, $user_id, $args ) {
		// If editing, deleting, or reading an event, get the post and post type object.
		if( 'edit_ai1ec_event' == $cap || 'delete_ai1ec_event' == $cap || 'read_ai1ec_event' == $cap ) {
			$post = get_post( $args[0] );
			$post_type = get_post_type_object( $post->post_type );
			/* Set an empty array for the caps. */
			$caps = array();
		}

		/* If editing an event, assign the required capability. */
		if( 'edit_ai1ec_event' == $cap ) {
			if( $user_id == $post->post_author )
				$caps[] = $post_type->cap->edit_posts;
			else
				$caps[] = $post_type->cap->edit_others_posts;
		}

		/* If deleting an event, assign the required capability. */
		else if( 'delete_ai1ec_event' == $cap ) {
			if( $user_id == $post->post_author )
				$caps[] = $post_type->cap->delete_posts;
			else
				$caps[] = $post_type->cap->delete_others_posts;
		}

		/* If reading a private event, assign the required capability. */
		else if( 'read_ai1ec_event' == $cap ) {
			if( 'private' != $post->post_status )
				$caps[] = 'read';
			elseif ( $user_id == $post->post_author )
				$caps[] = 'read';
			else
				$caps[] = $post_type->cap->read_private_posts;
		}

		/* Return the capabilities required by the user. */
		return $caps;
	}

	/**
	 * create_post_type function
	 *
	 * Create event's custom post type
	 * and registers events_categories and events_tags under
	 * event's custom post type taxonomy
	 *
	 * @return void
	 **/
	function create_post_type() {
		global $ai1ec_settings;

		// Create event contributor role with the same capabilities
		// as subscriber role, plus event managing capabilities
		// if we have not created it yet.
		if ( ! get_role( 'ai1ec_event_assistant' ) ) {
			$caps = get_role( 'subscriber' )->capabilities;
			$role = add_role(
				'ai1ec_event_assistant',
				'Event Contributor',
				$caps
			);
			$role->add_cap( 'edit_ai1ec_events' );
			$role->add_cap( 'delete_ai1ec_event' );
			$role->add_cap( 'read' );
			unset( $caps, $role );
		}

		// Add event managing capabilities to administrator, editor, author.
		// The last created capability is "manage_ai1ec_feeds", so check for
		// that one.
		$role = get_role( 'administrator' );
		if ( is_object( $role ) && ! $role->has_cap( 'manage_ai1ec_feeds' ) ) {
			$role_list = array( 'administrator', 'editor', 'author' );
			foreach ( $role_list as $role_name ) {
				$role = get_role( $role_name );
				if ( NULL === $role || ! ( $role instanceof WP_Role ) ) {
					continue;
				}
				// Read events.
				$role->add_cap( 'read_ai1ec_event' );
				// Edit events.
				$role->add_cap( 'edit_ai1ec_event' );
				$role->add_cap( 'edit_ai1ec_events' );
				$role->add_cap( 'edit_others_ai1ec_events' );
				$role->add_cap( 'edit_private_ai1ec_events' );
				$role->add_cap( 'edit_published_ai1ec_events' );
				// Delete events.
				$role->add_cap( 'delete_ai1ec_event' );
				$role->add_cap( 'delete_ai1ec_events' );
				$role->add_cap( 'delete_others_ai1ec_events' );
				$role->add_cap( 'delete_published_ai1ec_events' );
				$role->add_cap( 'delete_private_ai1ec_events' );
				// Publish events.
				$role->add_cap( 'publish_ai1ec_events' );
				// Read private events.
				$role->add_cap( 'read_private_ai1ec_events' );
				// Manage categories & tags.
				$role->add_cap( 'manage_events_categories' );
				// Manage calendar feeds.
				$role->add_cap( 'manage_ai1ec_feeds' );

				if ( 'administrator' === $role_name ) {
					// Change calendar themes & manage calendar options.
					$role->add_cap( 'switch_ai1ec_themes' );
					$role->add_cap( 'manage_ai1ec_options' );
				}
			}
		}

		// ===============================
		// = labels for custom post type =
		// ===============================
		$labels = array(
			'name'               => _x( 'Events', 'Custom post type name', AI1EC_PLUGIN_NAME ),
			'singular_name'      => _x( 'Event', 'Custom post type name (singular)', AI1EC_PLUGIN_NAME ),
			'add_new'            => __( 'Add New', AI1EC_PLUGIN_NAME ),
			'add_new_item'       => __( 'Add New Event', AI1EC_PLUGIN_NAME ),
			'edit_item'          => __( 'Edit Event', AI1EC_PLUGIN_NAME ),
			'new_item'           => __( 'New Event', AI1EC_PLUGIN_NAME ),
			'view_item'          => __( 'View Event', AI1EC_PLUGIN_NAME ),
			'search_items'       => __( 'Search Events', AI1EC_PLUGIN_NAME ),
			'not_found'          => __( 'No Events found', AI1EC_PLUGIN_NAME ),
			'not_found_in_trash' => __( 'No Events found in Trash', AI1EC_PLUGIN_NAME ),
			'parent_item_colon'  => __( 'Parent Event', AI1EC_PLUGIN_NAME ),
			'menu_name'          => __( 'Events', AI1EC_PLUGIN_NAME ),
			'all_items'          => $this->get_all_items_name(),
		);


		// ================================
		// = support for custom post type =
		// ================================
		$supports = array( 'title', 'editor', 'comments', 'custom-fields', 'thumbnail' );

		// =============================
		// = args for custom post type =
		// =============================
		$args = array(
			'labels'							=> $labels,
			'public' 							=> true,
			'publicly_queryable' 	=> true,
			'show_ui' 						=> true,
			'show_in_menu' 				=> true,
			'query_var' 					=> true,
			'rewrite' 						=> true,
			'capability_type'			=> array( 'ai1ec_event', 'ai1ec_events' ),
			'capabilities'        => array(
				'read_post'               => 'read_ai1ec_event',
				'edit_post'               => 'edit_ai1ec_event',
				'edit_posts'              => 'edit_ai1ec_events',
				'edit_others_posts'       => 'edit_others_ai1ec_events',
				'edit_private_posts'      => 'edit_private_ai1ec_events',
				'edit_published_posts'    => 'edit_published_ai1ec_events',
				'delete_post'             => 'delete_ai1ec_event',
				'delete_posts'            => 'delete_ai1ec_events',
				'delete_others_posts'     => 'delete_others_ai1ec_events',
				'delete_published_posts'  => 'delete_published_ai1ec_events',
				'delete_private_posts'    => 'delete_private_ai1ec_events',
				'publish_posts'           => 'publish_ai1ec_events',
				'read_private_posts'      => 'read_private_ai1ec_events' ),
			'has_archive' 				=> true,
			'hierarchical' 				=> true,
			'menu_position' 			=> 5,
			'supports'						=> $supports,
			'exclude_from_search' => $ai1ec_settings->exclude_from_search,
		);

		// ========================================
		// = labels for event categories taxonomy =
		// ========================================
		$events_categories_labels = array(
			'name'					=> _x( 'Event Categories', 'Event categories taxonomy', AI1EC_PLUGIN_NAME ),
			'singular_name'	=> _x( 'Event Category', 'Event categories taxonomy (singular)', AI1EC_PLUGIN_NAME )
		);

		// ==================================
		// = labels for event tags taxonomy =
		// ==================================
		$events_tags_labels = array(
			'name'					=> _x( 'Event Tags', 'Event tags taxonomy', AI1EC_PLUGIN_NAME ),
			'singular_name'	=> _x( 'Event Tag', 'Event tags taxonomy (singular)', AI1EC_PLUGIN_NAME )
		);

		// ==================================
		// = labels for event feeds taxonomy =
		// ==================================
		$events_feeds_labels = array(
			'name'					=> _x( 'Event Feeds', 'Event feeds taxonomy', AI1EC_PLUGIN_NAME ),
			'singular_name'	=> _x( 'Event Feed', 'Event feed taxonomy (singular)', AI1EC_PLUGIN_NAME )
		);

		// ======================================
		// = args for event categories taxonomy =
		// ======================================
		$events_categories_args = array(
			'labels'				=> $events_categories_labels,
			'hierarchical'	=> true,
			'rewrite'				=> array( 'slug' => 'events_categories' ),
			'capabilities'	=> array(
				'manage_terms' => 'manage_events_categories',
				'edit_terms'   => 'manage_events_categories',
				'delete_terms' => 'manage_events_categories',
				'assign_terms' => 'edit_ai1ec_events'
			)
		);

		// ================================
		// = args for event tags taxonomy =
		// ================================
		$events_tags_args = array(
			'labels'				=> $events_tags_labels,
			'hierarchical'	=> false,
			'rewrite'				=> array( 'slug' => 'events_tags' ),
			'capabilities'	=> array(
				'manage_terms' => 'manage_events_categories',
				'edit_terms'   => 'manage_events_categories',
				'delete_terms' => 'manage_events_categories',
				'assign_terms' => 'edit_ai1ec_events'
			)
		);

		// ================================
		// = args for event feeds taxonomy =
		// ================================
		$events_feeds_args = array(
			'labels'				=> $events_feeds_labels,
			'hierarchical'	=> false,
			'rewrite'				=> array( 'slug' => 'events_feeds' ),
			'capabilities'	=> array(
				'manage_terms' => 'manage_events_categories',
				'edit_terms'   => 'manage_events_categories',
				'delete_terms' => 'manage_events_categories',
				'assign_terms' => 'edit_ai1ec_events'
			),
			'public'        => false // don't show taxonomy in admin UI
		);

		// ======================================
		// = register event categories taxonomy =
		// ======================================
		register_taxonomy( 'events_categories', array( AI1EC_POST_TYPE ), $events_categories_args );

		// ================================
		// = register event tags taxonomy =
		// ================================
		register_taxonomy( 'events_tags', array( AI1EC_POST_TYPE ), $events_tags_args );

		// ================================
		// = register event tags taxonomy =
		// ================================
		register_taxonomy( 'events_feeds', array( AI1EC_POST_TYPE ), $events_feeds_args );

		// ========================================
		// = register custom post type for events =
		// ========================================
		register_post_type( AI1EC_POST_TYPE, $args );
	}

	/**
	 * taxonomy_filter_restrict_manage_posts function
	 *
	 * Adds filter dropdowns for event categories and event tags
	 *
	 * @return void
	 **/
	function taxonomy_filter_restrict_manage_posts() {
		global $typenow;

		// =============================================
		// = add the dropdowns only on the events page =
		// =============================================
		if( $typenow == AI1EC_POST_TYPE ) {
			$filters = get_object_taxonomies( $typenow );
			foreach( $filters as $tax_slug ) {
				$tax_obj = get_taxonomy( $tax_slug );
				wp_dropdown_categories( array(
					'show_option_all'	=> __( 'Show All ', AI1EC_PLUGIN_NAME ) . $tax_obj->label,
					'taxonomy'				=> $tax_slug,
					'name'						=> $tax_obj->name,
					'orderby'					=> 'name',
					'selected'				=> isset( $_GET[$tax_slug] ) ? $_GET[$tax_slug] : '',
					'hierarchical'		=> $tax_obj->hierarchical,
					'show_count'			=> true,
					'hide_if_empty'   => true
				));
			}
		}
	}

	/**
	 * get_all_items_name function
	 *
	 * If current user can publish events and there
	 * is at least 1 event pending, append the pending
	 * events number to the menu
	 *
	 * @return string
	 **/
	function get_all_items_name() {

		// if current user can publish events
		if( current_user_can( 'publish_ai1ec_events' ) ) {
			// get all pending events
			$query = new WP_Query(  array ( 'post_type' => 'ai1ec_event', 'post_status' => 'pending', 'posts_per_page' => -1,  ) );

			// at least 1 pending event?
			if( $query->post_count > 0 ) {
				// append the pending events number to the menu
				return sprintf(
					__( 'All Events <span class="update-plugins count-%d" title="%d Pending Events"><span class="update-count">%d</span></span>', AI1EC_PLUGIN_NAME ),
					$query->post_count, $query->post_count, $query->post_count );
			}
		}

		// no pending events, or the user doesn't have sufficient capabilities
		return __( 'All Events', AI1EC_PLUGIN_NAME );
	}

	/**
	 * taxonomy_filter_post_type_request function
	 *
	 * Adds filtering of events list by event tags and event categories
	 *
	 * @return void
	 **/
	function taxonomy_filter_post_type_request( $query ) {
		global $pagenow, $typenow;
		if( 'edit.php' == $pagenow ) {
			$filters = get_object_taxonomies( $typenow );
			foreach( $filters as $tax_slug ) {
				$var = &$query->query_vars[$tax_slug];
				if( isset( $var ) ) {
					$term = null;

					if( is_numeric( $var ) )
						$term = get_term_by( 'id', $var, $tax_slug );
					else
						$term = get_term_by( 'slug', $var, $tax_slug );

					if( isset( $term->slug ) ) {
						$var = $term->slug;
					}
				}
			}
		}
		// ===========================
		// = Order by Event date ASC =
		// ===========================
		if( $typenow === 'ai1ec_event' ) {
			if( ! array_key_exists( 'orderby', $query->query_vars ) ) {
				$query->query_vars["orderby"] = 'ai1ec_event_date';
				$query->query_vars["order"] 	= 'desc';
			}
		}
	}

	/**
	 * orderby function
	 *
	 * Orders events by event date
	 *
	 * @param string $orderby Orderby sql
	 * @param object $wp_query
	 *
	 * @return void
	 **/
	function orderby( $orderby, $wp_query ) {
		global $typenow, $wpdb, $post;

		if( $typenow === 'ai1ec_event' ) {
			$wp_query->query = wp_parse_args( $wp_query->query );
			$table_name = $wpdb->prefix . 'ai1ec_events';
			if( 'ai1ec_event_date' === @$wp_query->query['orderby'] ) {
				$orderby = "(SELECT start FROM {$table_name} WHERE post_id =  $wpdb->posts.ID) " . $wp_query->get('order');
			} else if( empty( $wp_query->query['orderby'] ) || $wp_query->query['orderby'] === 'menu_order title' ) {
				$orderby = "(SELECT start FROM {$table_name} WHERE post_id =  $wpdb->posts.ID) " . 'desc';
			}
		}
		return $orderby;
	}

	/**
	 * add_profile_boxes method
	 *
	 * Add options HTML to `wp-admin/profile.php` page.
	 *
	 * @return void Method does not return
	 */
	public function add_profile_boxes() {
		global $ai1ec_view_helper;
		$user		 = wp_get_current_user();
		$selected_tz = '';
		if ( $user->ID > 0 ) {
			$selected_tz = $this->user_selected_tz( $user->ID );
		}
		$argv = array(
			'tz_selector' => wp_timezone_choice( $selected_tz ),
		);
		$ai1ec_view_helper->display_admin(
			'box_profile_timezone.php',
			$argv
		);
	}

	/**
	 * save_user_profile method
	 *
	 * Process user selections made on forms generated
	 * in {@see self::add_profile_boxes()} method.
	 *
	 * @param int $user_id ID of user whose profile is being updated
	 *
	 * @return void Method does not return
	 */
	public function save_user_profile( $user_id ) {
		if ( isset( $_POST['ai1ec_user_timezone'] ) ) {
			$this->user_selected_tz(
				$user_id,
				$_POST['ai1ec_user_timezone']
			);
		}
	}

	/**
	 * user_selected_tz method
	 *
	 * Get/set user selected (preferred) timezone.
	 * If only {@see $user_id} is provided - method acts as getter.
	 * Otherwise it acts as setter.
	 *
	 * @param int    $user_id      ID of user whose timezone is being checked/changed
	 * @param string $new_value    New timezone string value to set user preferrence
	 * @param bool   $force_update Set to true to force value update instead of add
	 *
	 * @return mixed Return value depends on activity:
	 *     - [getter] string User preferred timezone name (might be empty string)
	 *     - [setter] bool   Success of preferrence change
	 */
	public function user_selected_tz(
		$user_id,
		$new_value    = NULL,
		$force_update = false
	) {
		$meta_key  = 'ai1ec_timezone';
		$user_id   = (int)$user_id;
		$old_value = Ai1ec_Meta::instance( 'User' )->get(
			$user_id,
			$meta_key,
			NULL,
			true
		);
		if ( NULL !== $new_value ) {
			if ( ! in_array( $new_value, timezone_identifiers_list() ) ) {
				return false;
			}
			$success = false;
			if ( true === $force_update || ! empty( $old_value ) ) {
				$success = update_user_meta(
					$user_id,
					$meta_key,
					$new_value,
					$old_value
				);
			} else {
				$success = add_user_meta(
					$user_id,
					$meta_key,
					$new_value,
					true
				);
				if ( false === $success ) {
					return $this->user_selected_tz(
						$user_id,
						$new_value,
						true
					);
				}
			}
			return $success;
		}
		return $old_value;
	}

	/**
	 * add_meta_boxes function
	 *
	 * Display event meta box when creating or editing an event.
	 *
	 * @return void
	 **/
	function add_meta_boxes() {
		global $ai1ec_events_controller;

		add_meta_box(
			AI1EC_POST_TYPE,
			__( 'Event Details', AI1EC_PLUGIN_NAME ),
			array( &$ai1ec_events_controller, 'meta_box_view' ),
			AI1EC_POST_TYPE,
			'normal',
			'high'
		);
	}

	/**
	 * change_columns function
	 *
	 * Adds Event date/time column to our custom post type
	 * and renames Date column to Post Date
	 *
	 * @param array $columns Existing columns
	 *
	 * @return array Updated columns array
	 */
	function change_columns( array $columns = array() ) {
		$columns['author']           = __( 'Author',          AI1EC_PLUGIN_NAME );
		$columns['date']             = __( 'Post Date',       AI1EC_PLUGIN_NAME );
		$columns['ai1ec_event_date'] = __( 'Event date/time', AI1EC_PLUGIN_NAME );
		return $columns;
	}

	/**
	 * custom_columns function
	 *
	 * Adds content for custom columns
	 *
	 * @return void
	 **/
	function custom_columns( $column, $post_id ) {
		global $ai1ec_events_helper;
		switch( $column ) {
			case 'ai1ec_event_date':
				try {
					$e = new Ai1ec_Event( $post_id );
					echo $e->get_timespan_html();
				} catch( Exception $e ) {
					// event wasn't found, output empty string
					echo "";
				}
				break;
		}
	}

	/**
	 * sortable_columns function
	 *
	 * Enable sorting of columns
	 *
	 * @return void
	 **/
	function sortable_columns( $columns ) {
		$columns["ai1ec_event_date"] = 'ai1ec_event_date';
		return $columns;
	}

	/**
	 * get_param function
	 *
	 * Tries to return the parameter from POST and GET
	 * incase it is missing, default value is returned
	 *
	 * @param string $param Parameter to return
	 * @param mixed $default Default value
	 *
	 * @return mixed
	 **/
	function get_param( $param, $default='' ) {
		if( isset( $_POST[$param] ) )
			return $_POST[$param];
		if( isset( $_GET[$param] ) )
			return $_GET[$param];
		return $default;
	}

	/**
	 * inject_categories function
	 *
	 * Displays event categories whenever post categories are requested
	 *
	 * @param array $terms Terms to be returned by get_terms()
	 * @param array $taxonomies Taxonomies requested in get_terms()
	 * @param array $args Args passed to get_terms()
	 *
	 * @return string|array If "category" taxonomy was requested, then returns
	 *                      $terms with fake category pointing to calendar page
	 *                      with its children being the event categories
	 **/
	function inject_categories( $terms, $taxonomies, $args )
	{
		global $ai1ec_settings;

		if( in_array( 'category', $taxonomies ) )
		{
			// Create fake calendar page category
			$count_args = $args;
			$count_args['fields'] = 'count';
			$count = get_terms( 'events_categories', $count_args );
			$post = get_post( $ai1ec_settings->calendar_page_id );
			switch( $args['fields'] )
			{
				case 'all':
					$calendar = (object) array(
						'term_id'     => AI1EC_FAKE_CATEGORY_ID,
						'name'		    => $post->post_title,
						'slug'		    => $post->post_name,
						'taxonomy'    => 'events_categories',
						'description' => '',
						'parent'      => 0,
						'count'       => $count,
					);
					break;
				case 'ids':
					$calendar = 'ai1ec_calendar';
					break;
				case 'names':
					$calendar = $post->post_title;
					break;
			}
			$terms[] = $calendar;

			if( $args['hierarchical'] ) {
				$children = get_terms( 'events_categories', $args );
				foreach( $children as &$child ) {
					if( is_object( $child ) && $child->parent == 0 )
						$child->parent = AI1EC_FAKE_CATEGORY_ID;
					$terms[] = $child;
				}
			}
		}

		return $terms;
	}

	/**
	 * Corrects the URL for the calendar page when injected into the post
	 * categories.
	 *
	 * @param string $link The normally generated link
	 * @param object $term The term that we're getting the link for
	 * @param string $taxonomy The name of the taxonomy of interest
	 *
	 * @return string The correct link to the calendar page
	 */
	function calendar_term_link( $link, $term, $taxonomy ) {
		global $ai1ec_calendar_helper;

		if( $taxonomy == 'events_categories' ) {
			if( $term->term_id == AI1EC_FAKE_CATEGORY_ID )
				$link = $ai1ec_calendar_helper->get_calendar_url();
			else
				$link = $ai1ec_calendar_helper->get_calendar_url(
					array( 'cat_ids' => array( $term->term_id ) )
				);
		}

		return $link;
	}

	/**
	 * function selected_category_link
	 *
	 * Corrects the output of wp_list_categories so that the currently viewed
	 * event category (in calendar view) has the "active" CSS class applied to it.
	 *
	 * @param string $output The normally generated output of wp_list_categories()
	 * @param object $args The args passed to wp_list_categories()
	 *
	 * @return string The corrected output
	 */
	function selected_category_link( $output, $args )
	{
		global $ai1ec_calendar_controller, $ai1ec_settings;

		// First check if current page is calendar
		if( is_page( $ai1ec_settings->calendar_page_id ) ) {
			$cat_ids = array_filter( explode( ',', $ai1ec_calendar_controller->get_requested_categories() ), 'is_numeric' );
			if( $cat_ids ) {
				// Mark each filtered event category link as selected
				foreach( $cat_ids as $cat_id ) {
					$output = str_replace(
						'class="cat-item cat-item-' . $cat_id . '"',
						'class="cat-item cat-item-' . $cat_id . ' current-cat current_page_item"',
						$output );
				}
				// Mark calendar page link as selected parent
				$output = str_replace(
					'class="cat-item cat-item-' . AI1EC_FAKE_CATEGORY_ID . '"',
					'class="cat-item cat-item-' . AI1EC_FAKE_CATEGORY_ID . ' current-cat-parent"',
					$output );
			} else {
				// No categories filtered, so mark calendar page link as selected
				$output = str_replace(
					'class="cat-item cat-item-' . AI1EC_FAKE_CATEGORY_ID . '"',
					'class="cat-item cat-item-' . AI1EC_FAKE_CATEGORY_ID . ' current-cat current_page_item"',
					$output );
			}
		}

		return $output;
	}

	/**
	* admin_notices function
	*
	* Notify the user about anything special.
	*
	* @return void
	**/
	function admin_notices() {
		global $ai1ec_view_helper,
		       $ai1ec_settings,
		       $plugin_page,
		       $ai1ec_themes_controller,
		       $ai1ec_importer_plugin_helper;

		if ( 'invalid' == $ai1ec_settings->license_warning ) {
			$args = array(
				'label'  => __( 'All-in-One Event Calendar Warning', AI1EC_PLUGIN_NAME ),
				'msg'    =>
					sprintf(
						__( '<p><strong>Our records indicate that your license for the Pro Calendar is missing or expired.</strong></p><p>Please visit <a href="%s">Calendar Settings</a> page, and verify that your license key is correct. You can find your license key on <a href="%s" target="_blank">My Timely</a>.</p>', AI1EC_PLUGIN_NAME ),
						admin_url( AI1EC_SETTINGS_BASE_URL ),
						AI1EC_TIMELY_ACCOUNT_URL
					),
				'message_type' => 'error',
			);
			$ai1ec_view_helper->display_admin( 'admin_notices.php', $args );
			setcookie( 'ai1ec_general_settings_active_tab', '#ai1ec-license-key' );
		}

		// Display introductory video notice if not disabled.
		if( $ai1ec_settings->show_intro_video ) {
			$args = array(
				'label' => __( 'Welcome to the All-in-One Event Calendar, by Timely', AI1EC_PLUGIN_NAME ),
				'msg' => sprintf(
					'<div class="timely"><a href="#ai1ec-video-modal" data-toggle="modal" ' .
						'class="button-primary pull-left">%s</a>' .
						'<div class="pull-left">&nbsp;</div></div>',
					__( 'Watch the introductory video »', AI1EC_PLUGIN_NAME )
				),
				'button' => (object) array(
					'class' => 'ai1ec-dismiss-intro-video',
					'value' => __( 'Dismiss', AI1EC_PLUGIN_NAME ),
				),
			);
			$ai1ec_view_helper->display_admin( 'admin_notices.php', $args );
			// Find out if CSS for Bootstrap modals has been attached. If not, embed
			// it inline.
			if ( ! wp_style_is( 'timely-bootstrap' ) ) {
				$ai1ec_view_helper->display_admin_css( 'bootstrap.min.css' );
			}
			$args = array(
				'title' => __( 'Introducing the All-in-One Event Calendar, by Timely',
					AI1EC_PLUGIN_NAME ),
				'youtube_id' => 'XJ-KHOqBKuQ',
			);
			$ai1ec_view_helper->display_admin( 'video_modal.php', $args );
		}

		// No themes available notice.
		if( ! $ai1ec_themes_controller->are_themes_available() ) {
			$args = array(
				'label'  => __( 'All-in-One Event Calendar Notice', AI1EC_PLUGIN_NAME ),
				'msg'    => sprintf(
					__( '<p><strong>Core Calendar Themes are not installed.</strong></p>' .
					'<p>Our automated install couldn\'t install the core Calendar Themes automatically. ' .
					'You will need to install calendar themes manually by following these steps:</p>' .
					'<ol><li>Gain access to your WordPress files. Either direct filesystem access or FTP access is fine.</li>' .
					'<li>Navigate to the <strong>%s</strong> folder.</li>' .
					'<li>Copy the <strong>%s</strong> folder and all of its contents into the <strong>%s</strong> folder.</li>' .
					'<li>You should now have a folder named <strong>%s</strong> containing all the same files and sub-folders as <strong>%s</strong> does.</li>' .
					'<li>Refresh this page and if this notice is gone, the core Calendar Themes are installed.</li></ol>', AI1EC_PLUGIN_NAME ),
					AI1EC_PATH,
					AI1EC_THEMES_FOLDER,
					WP_CONTENT_DIR,
					WP_CONTENT_DIR . '/' . AI1EC_THEMES_FOLDER,
					AI1EC_PATH . '/' . AI1EC_THEMES_FOLDER )
			);
			$ai1ec_view_helper->display_admin( 'admin_notices.php', $args );
		}

		// Outdated themes notice (on all pages except update themes page).
		if (
			$plugin_page != AI1EC_PLUGIN_NAME . '-update-themes' &&
			$ai1ec_themes_controller->are_themes_outdated()
		) {
			$args = array(
				'label' => __( 'All-in-One Event Calendar Notice', AI1EC_PLUGIN_NAME ),
				'msg' => sprintf(
					__( '<p><strong>Core Calendar Themes are out of date.</strong> ' .
					'We have found updates for some of your core Calendar Theme files and you should update them now to ensure proper functioning of your calendar.</p>' .
					'<p><strong>Warning:</strong> If you have previously modified any <strong>core</strong> Calendar Theme files, ' .
					'your changes will be lost during update. Please make a backup of all modifications to core themes before proceeding.</p>' .
					'<p>Once you are ready, please <a href="%s">update your core Calendar Themes</a>.</p>', AI1EC_PLUGIN_NAME ),
					admin_url( AI1EC_UPDATE_THEMES_BASE_URL )
				),
			);
			$ai1ec_view_helper->display_admin( 'admin_notices.php', $args );
		}

		if( $ai1ec_settings->show_data_notification ) {
			$args = array(
				'label'  => __( 'All-in-One Event Calendar Notice', AI1EC_PLUGIN_NAME ),
				'msg'    =>
					sprintf(
						__( '<p>We collect some basic information about how your calendar works in order to deliver a better ' .
						'and faster calendar system and one that will help you promote your events even more.</p>' .
						'<p>You can find more detailed information on our privacy policy by <a href="%s" target="_blank">clicking here</a>.</p>', AI1EC_PLUGIN_NAME ),
						'http://time.ly/event-search-calendar',
						admin_url( AI1EC_SETTINGS_BASE_URL )
					),
				'button' => (object) array(
					'class' => 'ai1ec-dismiss-notification',
					'value' => __( 'Dismiss', AI1EC_PLUGIN_NAME ),
				),
			);
			$ai1ec_view_helper->display_admin( 'admin_notices.php', $args );
		}

		// If calendar page or time zone has not been set, this is a fresh install.
		// Additionally, if we're not already updating the settings, alert user
		// appropriately that the calendar is not properly set up.
		if (
			(
				! $ai1ec_settings->calendar_page_id ||
				! Ai1ec_Meta::get_option( 'timezone_string' )
			) &&
			! isset( $_REQUEST['ai1ec_save_settings'] )
		) {
			$args = array();
			$messages = array();

			// Display messages for blog admin.
			if( current_user_can( 'manage_ai1ec_options' ) ) {
				// If on the settings page, instruct user as to what to do.
				if( $plugin_page == AI1EC_PLUGIN_NAME . '-settings' ) {
					if( ! $ai1ec_settings->calendar_page_id ) {
						$messages[] = __( 'Select an option in the <strong>Calendar page</strong> dropdown list.', AI1EC_PLUGIN_NAME );
					}
					if ( ! Ai1ec_Meta::get_option( 'timezone_string' ) ) {
						$messages[] = __( 'Select an option in the <strong>Timezone</strong> dropdown list.', AI1EC_PLUGIN_NAME );
					}
					$messages[] = __( 'Click <strong>Update Settings</strong>.', AI1EC_PLUGIN_NAME );
				}
				// Else, not on the settings page, so direct user there.
				else {
					$msg = sprintf(
						__( 'The plugin is installed, but has not been configured. <a href="%s">Click here to set it up now &raquo;</a>', AI1EC_PLUGIN_NAME ),
						admin_url( AI1EC_SETTINGS_BASE_URL )
					);
					$messages[] = $msg;
				}
			}
			// Else display messages for other blog users
			else {
				$messages[] = __( 'The plugin is installed, but has not been configured. Please log in as an Administrator to set it up.', AI1EC_PLUGIN_NAME );
			}

			// Format notice message.
			if (count($messages) > 1) {
				$args['msg'] = __( '<p>To set up the plugin:</p>', AI1EC_PLUGIN_NAME );
				$args['msg'] .= '<ol><li>';
				$args['msg'] .= implode( '</li><li>', $messages );
				$args['msg'] .= '</li></ol>';
			}
			else {
				$args['msg'] = "<p>$messages[0]</p>";
			}
			$args['label'] = __( 'All-in-One Event Calendar Notice', AI1EC_PLUGIN_NAME );
			$ai1ec_view_helper->display_admin( 'admin_notices.php', $args );
		}

		// Premium plugin update available notice.
		$meta = Ai1ec_Meta::instance( 'Option' );
		if (
			$meta->get( 'ai1ec_update_available', NULL, false ) &&
			current_user_can( 'update_plugins' )
		) {
			$args = array(
				'label' => __( 'All-in-One Event Calendar Update', AI1EC_PLUGIN_NAME ),
			);
			$update_url   = AI1EC_UPGRADE_PLUGIN_BASE_URL;

			$args['msg']  = '<p>' .
				$meta->get( 'ai1ec_update_message', NULL, '' ) .
				'</p>';
			$args['msg'] .= '<p><a class="button" href="' .
				admin_url( $update_url ) . '">';
			$args['msg'] .= __( 'Upgrade now', AI1EC_PLUGIN_NAME );
			$args['msg'] .= '</a></p>';
			$ai1ec_view_helper->display_admin( 'admin_notices.php', $args );
		}
		// Let the plugin display their notice.
		$ai1ec_importer_plugin_helper->display_admin_notices();
	}

	/**
	 * Add Events items to "Right Now" widget in Dashboard.
	 *
	 * @return  void
	 */
	function right_now_content_table_end() {
		$num_events = wp_count_posts( AI1EC_POST_TYPE );
		$num_cats  = wp_count_terms( 'events_categories' );
		$num_tags = wp_count_terms( 'events_tags' );

		// Events.
		$num = number_format_i18n( $num_events->publish );
		$text = _n( 'Event', 'Events', $num_events->publish );
		if ( current_user_can( 'edit_ai1ec_events' ) ) {
			$num = '<a href="' . AI1EC_ADMIN_BASE_URL . '">' . $num . '</a>';
			$text = '<a href="' . AI1EC_ADMIN_BASE_URL . '">' . $text . '</a>';
		}
		echo '<td class="first b b-ai1ec-event">' . $num . '</td>';
		echo '<td class="t ai1ec-event">' . $text . '</td>';

		echo '</tr><tr>';

		// Event categories.
		$num = number_format_i18n( $num_cats );
		$text = _n( 'Event Category', 'Event Categories', $num_cats );
		if ( current_user_can( 'manage_events_categories' ) ) {
			$num = "<a href='edit-tags.php?taxonomy=events_categories'>$num</a>";
			$text = "<a href='edit-tags.php?taxonomy=events_categories'>$text</a>";
		}
		echo '<td class="first b b-events-categories">' . $num . '</td>';
		echo '<td class="t events-categories">' . $text . '</td>';

		echo '</tr><tr>';

		// Event tags.
		$num = number_format_i18n( $num_tags );
		$text = _n( 'Event Tag', 'Event Tags', $num_tags );
		if ( current_user_can( 'manage_events_categories' ) ) {
			$num = "<a href='edit-tags.php?taxonomy=events_tags'>$num</a>";
			$text = "<a href='edit-tags.php?taxonomy=events_tags'>$text</a>";
		}
		echo '<td class="first b b-events-tags">' . $num . '</td>';
		echo '<td class="t events-tags">' . $text . '</td>';
	}

	/**
	 * admin_enqueue_scripts function
	 *
	 * Enqueue any scripts and styles in the admin side, depending on context.
	 *
	 * @return void
	 */
	function admin_enqueue_scripts( $hook_suffix ) {
		global $ai1ec_settings, $ai1ec_view_helper;

		// Common styles.
		$ai1ec_view_helper->admin_enqueue_style( 'ai1ec-admin', 'admin.css' );

		switch( $hook_suffix ) {
			// Event lists.
			// Widgets screen.
			case 'widgets.php':
				// Styles.
				$ai1ec_view_helper->admin_enqueue_style( 'ai1ec-widget', 'widget.css' );
				break;

			// Calendar settings & feeds screens.
			case $ai1ec_settings->settings_page:
				// Scripts.
				wp_enqueue_script( 'common' );
				wp_enqueue_script( 'wp-lists' );
				wp_enqueue_script( 'postbox' );
				// Styles.
				$ai1ec_view_helper->admin_enqueue_style( 'ai1ec-settings', 'settings.css' );
				$ai1ec_view_helper->admin_enqueue_style( 'timely-bootstrap', 'bootstrap.min.css' );
				$ai1ec_view_helper->admin_enqueue_style( 'timely-boootstrap-datepicker', 'bootstrap_datepicker.css' );
				break;

			case $ai1ec_settings->feeds_page:
				// Scripts.
				wp_enqueue_script( 'common' );
				wp_enqueue_script( 'wp-lists' );
				wp_enqueue_script( 'postbox' );
				// Styles.
				$ai1ec_view_helper->admin_enqueue_style( 'ai1ec-settings', 'settings.css' );
				$ai1ec_view_helper->admin_enqueue_style( 'timely-bootstrap', 'bootstrap.min.css' );
				// include plugins style
				$ai1ec_view_helper->admin_enqueue_style( 'ai1ec_plugins_common', 'plugins/plugins-common.css' );
				break;

			case "post.php":
			case "post-new.php":
				$ai1ec_view_helper->admin_enqueue_style( 'timely-bootstrap', 'bootstrap.min.css' );
				// include add new event style
				$ai1ec_view_helper->admin_enqueue_style( 'ai1ec_add_new_event', 'add_new_event.css' );
				// include datepicker style
				$ai1ec_view_helper->admin_enqueue_style( 'ai1ec_datepicker', 'datepicker.css' );
				break;

			case $ai1ec_settings->less_variables_page:
				$ai1ec_view_helper->admin_enqueue_style( 'ai1ec-settings', 'settings.css' );
				$ai1ec_view_helper->admin_enqueue_style( 'timely-bootstrap', 'bootstrap.min.css' );
				$ai1ec_view_helper->admin_enqueue_style( 'timely-bootstrap-colorpicker', 'bootstrap_colorpicker.css' );
				break;

			case "edit-tags.php":
				$ai1ec_view_helper->admin_enqueue_style( 'timely-bootstrap-colorpicker', 'colorpicker.css' );
				break;
		}
	}

	/**
	 * Returns a map of calendar view keys to translated names.
	 *
	 * @return  array
	 */
	public function view_names() {
		return array(
			'posterboard' => __( 'Posterboard', AI1EC_PLUGIN_NAME ),
			'stream' => __( 'Stream', AI1EC_PLUGIN_NAME ),
			'month' => __( 'Month', AI1EC_PLUGIN_NAME ),
			'week' => __( 'Week', AI1EC_PLUGIN_NAME ),
			'oneday' => __( 'Day', AI1EC_PLUGIN_NAME ),
			'agenda' => __( 'Agenda', AI1EC_PLUGIN_NAME ),
		);
	}

	public function modify_admin_bar() {
		if ( current_user_can( 'manage_ai1ec_options' ) ) {
			global $wp_admin_bar;
			$wp_admin_bar->add_node( array(
				'id'       =>'timely-account',
				'title'    => __( 'View my Timely Account', AI1EC_PLUGIN_NAME ),
				'href'     => AI1EC_TIMELY_ACCOUNT_URL,
				'parent'   => 'my-account',
				'meta'     => array(
					'target' => '_blank',
				),
			) );
		}
	}
}
// END class
