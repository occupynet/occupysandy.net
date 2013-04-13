<?php
//
//  class-ai1ec-app-controller.php
//  all-in-one-event-calendar
//
//  Created by The Seed Studio on 2011-07-13.
//
/**
 * Ai1ec_App_Controller class
 *
 * @package Controllers
 * @author time.ly
 **/
class Ai1ec_App_Controller {
	/**
	 * _instance class variable
	 *
	 * Class instance
	 *
	 * @var null | object
	 **/
	private static $_instance = NULL;

	/**
	 * _load_domain class variable
	 *
	 * Load domain
	 *
	 * @var bool
	 **/
	private static $_load_domain = FALSE;

	/**
	 * page_content class variable
	 *
	 * String storing page content for output by the_content()
	 *
	 * @var null | string
	 **/
	private $page_content = NULL;

	/**
	 * request class variable
	 *
	 * Stores a custom $_REQUEST array for all calendar requests
	 *
	 * @var Ai1ec_Abstract_Query
	 **/
	private $request;

	/**
	 * get_instance function
	 *
	 * Return singleton instance
	 *
	 * @return object
	 **/
	static function get_instance( $preview_mode ) {
		if( self::$_instance === NULL ) {
			self::$_instance = new self( $preview_mode );
		}

		return self::$_instance;
	}

	private $init = 'false';
	/**
	 * Constructor
	 *
	 * Default constructor - application initialization
	 **/
	private function __construct( $preview_mode )
	{
		global $wpdb,
		       $wp_locale,
		       $wp_scripts,
		       $ai1ec_app_helper,
		       $ai1ec_view_helper,
		       $ai1ec_events_controller,
		       $ai1ec_events_helper,
		       $ai1ec_importer_controller,
		       $ai1ec_exporter_controller,
		       $ai1ec_settings_controller,
		       $ai1ec_settings,
		       $ai1ec_themes_controller,
		       $ai1ec_calendar_controller,
		       $ai1ec_calendar_helper,
		       $ai1ec_importer_plugin_helper,
		       $ai1ec_requirejs_controller,
		       $ai1ec_rss_feed,
		       $ai1ec_duplicate_controller;

		$aie1c_admin_notices_helper = Ai1ec_Admin_Notices_Helper::get_instance();
		$ai1ec_duplicate_controller->set_admin_notice_helper( $aie1c_admin_notices_helper );
		$css_controller = Ai1ec_Less_Factory::create_css_controller_instance();


		// register_activation_hook
		register_activation_hook(
			AI1EC_PLUGIN_NAME . '/' . AI1EC_PLUGIN_NAME . '.php',
			array( &$this, 'activation_hook' )
		);

		// Configure MySQL to operate in GMT time
		$wpdb->query( "SET time_zone = '+0:00'" );

		// Load plugin text domain
		$this->load_textdomain();

		// Install/update database schema as necessary
		$this->install_schema();

		// Enable stats collection
		$this->install_n_cron();

		// Enable plugins for importing events from external sources
		$this->install_plugins();

		$lessphp_controller = Ai1ec_Less_Factory::create_lessphp_controller();

		// Update less variables in the db
		if (
			isset( $_POST[Ai1ec_Less_Variables_Editing_Page::FORM_SUBMIT_NAME] ) ||
			isset( $_POST[Ai1ec_Less_Variables_Editing_Page::FORM_SUBMIT_RESET_THEME] )
		) {
			$css_controller->handle_less_variables_page_form_post();
		}

		// Adds the image field only if we are on event categories page
		$this->add_image_field_to_event_categories();

		// Enable checking for cron updates
		$this->install_u_cron();

		// Continue loading hooks only if themes are installed. Otherwise display a
		// notification on the backend with instructions how to install themes.
		if ( ! $ai1ec_themes_controller->are_themes_available() ) {
			// Enables the hidden themes installer page
			add_action( 'admin_menu', array( &$ai1ec_themes_controller, 'register_theme_installer' ), 10 );
			// Redirects the user to install theme page
			add_action( 'admin_menu', array( &$this, 'check_themes' ), 2 );
			return;
		}
		if ( false === $preview_mode &&
		     ! $ai1ec_themes_controller->are_themes_outdated() ) {
			// Create the less variables if they are not set, but only if we are not in preview mode.
			// this is because there is an edge case when you activate a theme and then you preview another
			// the variables of the other theme are set.
			$lessphp_controller->initialize_less_variables_if_not_set(
				Ai1ec_Less_Factory::create_less_file_instance( Ai1ec_Less_File::USER_VARIABLES_FILE )
			);
		}
		// Check for legacy format themes when viewing WP dashboard; do not perform
		// the check when switching themes or if current theme files are outdated.
		if ( is_admin() &&
		     ! isset( $_GET['ai1ec_template'] ) &&
		     ! $ai1ec_themes_controller->are_themes_outdated() ) {
			$ai1ec_themes_controller->generate_notice_if_legacy_theme_installed();
		}


		// ===========
		// = ACTIONS =
		// ===========
		// Very early on in WP bootstrap, prepare to do any requested theme preview.
		add_action( 'setup_theme',                              array( &$ai1ec_themes_controller, 'preview_theme' ) );
		// Calendar theme initialization
		add_action( 'after_setup_theme',                        array( &$ai1ec_themes_controller, 'setup_theme' ) );
		// Create custom post type
		add_action( 'init',                                     array( &$ai1ec_app_helper, 'create_post_type' ) );
		// Handle ICS export requests
		add_action( 'init',                                     array( &$this, 'parse_standalone_request' ) );
		// RSS Feed
		add_action( 'init',                                     array( $ai1ec_rss_feed, 'add_feed' ) );
		// Add the link for CSS generation
		if( ! is_admin() ) {
			add_action( 'init',                                   array( $css_controller, 'add_link_to_html_for_frontend' ), 1 );
		}
		// Load plugin text domain
		add_action( 'init',                                     array( &$this, 'load_textdomain' ) );
		// Load back-end javascript files
		add_action( 'init',                                     array( $ai1ec_requirejs_controller, 'load_admin_js' ) );
		// Load the scripts for the backend for wordpress version < 3.3
		add_action( 'admin_footer',                             array( $ai1ec_requirejs_controller, 'print_admin_script_footer_for_wordpress_32' ) );
		// Load the scripts for the frontend for wordpress version < 3.3
		add_action( 'wp_footer',                                array( $ai1ec_requirejs_controller, 'print_frontend_script_footer_for_wordpress_32' ) );
		// Set an action to load front-end javascript
		add_action( 'ai1ec_load_frontend_js',                   array( $ai1ec_requirejs_controller, 'load_frontend_js' ), 10, 1 );
		// Check if themes are installed
		add_action( 'init',                                     array( &$ai1ec_themes_controller, 'check_themes' ) );
		// Register The Event Calendar importer
		add_action( 'admin_init',                               array( &$ai1ec_importer_controller, 'register_importer' ) );
		// Install admin menu items.
		add_action( 'admin_menu',                               array( &$this, 'admin_menu' ), 9 );
		// Enable theme updater page if last version of core themes is older than
		// current version.
		if ( $ai1ec_themes_controller->are_themes_outdated() ) {
			add_action( 'admin_menu',                       array( &$ai1ec_themes_controller, 'register_theme_updater' ), 10 );
		}
		// Add Event counts to dashboard.
		add_action( 'right_now_content_table_end',              array( &$ai1ec_app_helper, 'right_now_content_table_end' ) );
		// add content for our custom columns
		add_action( 'manage_ai1ec_event_posts_custom_column',   array( &$ai1ec_app_helper, 'custom_columns' ), 10, 2 );
		// Add filtering dropdowns for event categories and tags
		add_action( 'restrict_manage_posts',                    array( &$ai1ec_app_helper, 'taxonomy_filter_restrict_manage_posts' ) );
		// Trigger display of page in front-end depending on request
		add_action( 'template_redirect',                        array( &$this, 'route_request' ) );
		// Add meta boxes to event creation/edit form.
		add_action( 'add_meta_boxes',                           array( &$ai1ec_app_helper, 'add_meta_boxes' ) );
		add_action( 'show_user_profile',                        array( &$ai1ec_app_helper, 'add_profile_boxes' ) );
		add_action( 'personal_options_update',                  array( &$ai1ec_app_helper, 'save_user_profile' ), 10, 1 );
		// Save event data when post is saved
		add_action( 'save_post',                                array( &$ai1ec_events_controller, 'save_post' ), 10, 2 );
		// Delete event data when post is deleted
		add_action( 'delete_post',                              array( &$ai1ec_events_controller, 'delete_post' ) );
		add_action( 'delete_term',                              array( $ai1ec_settings, 'term_deletion' ), 10, 3 );
		// Notification cron job hook
		add_action( 'ai1ec_n_cron',                             array( &$ai1ec_exporter_controller, 'n_cron' ) );
		// Updates cron job hook
		add_action( 'ai1ec_u_cron',                             array( &$ai1ec_settings_controller, 'u_cron' ) );
		// Category colors
		add_action( 'events_categories_add_form_fields',        array( &$ai1ec_events_controller, 'events_categories_add_form_fields' ) );
		add_action( 'events_categories_edit_form_fields',       array( &$ai1ec_events_controller, 'events_categories_edit_form_fields' ) );
		add_action( 'created_events_categories',                array( &$ai1ec_events_controller, 'created_events_categories' ) );
		add_action( 'edited_events_categories',                 array( &$ai1ec_events_controller, 'edited_events_categories' ) );
		add_action( 'admin_notices',                            array( &$ai1ec_app_helper, 'admin_notices' ) );
		// The new object that handles notices.
		add_action( 'admin_notices',                            array( &$aie1c_admin_notices_helper, 'render' ) );
		// Scripts/styles for settings and widget admin screens.
		add_action( 'admin_enqueue_scripts',                    array( &$ai1ec_app_helper, 'admin_enqueue_scripts' ) );
		// Widgets
		add_action( 'widgets_init', create_function( '', "return register_widget( 'Ai1ec_Agenda_Widget' );" ) );
		// Modify WP admin bar
		add_action( 'admin_bar_menu',                           array( &$ai1ec_app_helper, 'modify_admin_bar' ) );

		// ===========
		// = FILTERS =
		// ===========
		if (
			is_admin() &&
			'admin-ajax.php' !== basename( $_SERVER['SCRIPT_NAME'] )
		) {
			add_filter(
				'the_title',
				array( &$ai1ec_view_helper, 'the_title_admin' ),
				1,
				2
			);
		}
		add_filter( 'posts_orderby',                            array( &$ai1ec_app_helper, 'orderby' ), 10, 2 );
		// add custom column names and change existing columns
		add_filter( 'manage_ai1ec_event_posts_columns',         array( &$ai1ec_app_helper, 'change_columns' ) );
		// filter the post lists by custom filters
		add_filter( 'parse_query',                              array( &$ai1ec_app_helper, 'taxonomy_filter_post_type_request' ) );
		// Override excerpt filters for proper event display in excerpt form
		add_filter( 'get_the_excerpt',                          array( &$ai1ec_events_controller, 'event_excerpt' ), 11 );
		add_filter( 'the_excerpt',                              array( &$ai1ec_events_controller, 'event_excerpt_noautop' ), 11 );
		remove_filter( 'the_excerpt',                           'wpautop', 10 );
		// Update event post update messages
		add_filter( 'post_updated_messages',                    array( &$ai1ec_events_controller, 'post_updated_messages' ) );
		// Sort the custom columns
		add_filter( 'manage_edit-ai1ec_event_sortable_columns', array( &$ai1ec_app_helper, 'sortable_columns' ) );
		add_filter( 'map_meta_cap',                             array( &$ai1ec_app_helper, 'map_meta_cap' ), 10, 4 );
		// Inject event categories, only in front-end, depending on setting
		if( $ai1ec_settings->inject_categories && ! is_admin() ) {
			add_filter( 'get_terms',                              array( &$ai1ec_app_helper, 'inject_categories' ), 10, 3 );
			add_filter( 'wp_list_categories',                     array( &$ai1ec_app_helper, 'selected_category_link' ), 10, 2 );
		}
		// Rewrite event category URLs to point to calendar page.
		add_filter( 'term_link',                                array( &$ai1ec_app_helper, 'calendar_term_link' ), 10, 3 );
		// Add a link to settings page on the plugin list page.
		add_filter( 'plugin_action_links_' . AI1EC_PLUGIN_BASENAME, array( &$ai1ec_settings_controller, 'plugin_action_links' ) );
		// Add a link to donate page on plugin list page.
		add_filter( 'plugin_row_meta',                          array( &$ai1ec_settings_controller, 'plugin_row_meta' ), 10, 2 );
		add_filter( 'post_type_link',                           array( &$ai1ec_events_helper, 'post_type_link' ), 10, 3 );
		add_filter( 'ai1ec_template_root_path',                 array( &$ai1ec_themes_controller, 'template_root_path' ) );
		add_filter( 'ai1ec_template_root_url',                  array( &$ai1ec_themes_controller, 'template_root_url' ) );

		// ========
		// = AJAX =
		// ========

		// RRule to Text
		add_action( 'wp_ajax_ai1ec_rrule_to_text', array( &$ai1ec_events_helper, 'convert_rrule_to_text' ) );

		// Display Repeat Box
		add_action( 'wp_ajax_ai1ec_get_repeat_box', array( &$ai1ec_events_helper, 'get_repeat_box' ) );
		add_action( 'wp_ajax_ai1ec_get_date_picker_box', array( &$ai1ec_events_helper, 'get_date_picker_box' ) );

		// Disable notifications
		add_action( 'wp_ajax_ai1ec_disable_notification', array( &$ai1ec_settings_controller, 'disable_notification' ) );
		add_action( 'wp_ajax_ai1ec_disable_intro_video', array( &$ai1ec_settings_controller, 'disable_intro_video' ) );

		// Front-end event creation
		add_action( 'wp_ajax_ai1ec_front_end_create_event_form',
			array( &$ai1ec_events_helper, 'get_front_end_create_event_form' ) );
		add_action( 'wp_ajax_ai1ec_front_end_submit_event',
			array( &$ai1ec_events_helper, 'submit_front_end_create_event_form' ) );
		if ( $ai1ec_settings->allow_anonymous_submissions ) {
			add_action( 'wp_ajax_nopriv_ai1ec_front_end_create_event_form',
				array( &$ai1ec_events_helper, 'get_front_end_create_event_form' ) );
			add_action( 'wp_ajax_nopriv_ai1ec_front_end_submit_event',
				array( &$ai1ec_events_helper, 'submit_front_end_create_event_form' ) );
		}

		// Invalid license status warning.
		add_action( 'wp_ajax_ai1ec_set_license_warning', array( &$ai1ec_settings_controller, 'set_license_warning' ) );

		// ==============
		// = Shortcodes =
		// ==============
		add_shortcode( 'ai1ec', array( &$ai1ec_events_helper, 'shortcode' ) );
	}

	/**
	 * Adds an image field to the event categories page
	 */
	private function add_image_field_to_event_categories(){
		global $ai1ec_tax_meta_class;
		/*
		 * prefix of meta keys, optional
		*/
		$prefix = 'ai1ec_';

		/*
		 * Add fields to your meta box
		*/
		// Image field
		$ai1ec_tax_meta_class->addImage(
			$prefix . 'image_field_id',
			array(
				'name' => __( 'Category Image', AI1EC_PLUGIN_NAME ),
				'desc' => '<p class="description">' . __( 'Assign an optional image to the category. Recommended size: square, minimum 400&times;400 pixels.', AI1EC_PLUGIN_NAME ) . '</p>',
			)
		);
		//Finish Meta Box Decleration
		$ai1ec_tax_meta_class->Finish();
	}

	/**
	 * activation_hook function
	 *
	 * This function is called when activating the plugin
	 *
	 * @return void
	 **/
	function activation_hook() {
		// Load plugin text domain.
		$this->load_textdomain();

		// Flush rewrite rules.
		$this->rewrite_flush();
	}

	/**
	 * load_textdomain function
	 *
	 * Loads plugin text domain
	 *
	 * @return void
	 **/
	function load_textdomain() {
		if( self::$_load_domain === FALSE ) {
			load_plugin_textdomain( AI1EC_PLUGIN_NAME, false, AI1EC_LANGUAGE_PATH );
			self::$_load_domain = TRUE;
		}
	}

	/**
	 * rewrite_flush function
	 *
	 * Get permalinks to work when activating the plugin
	 *
	 * @return void
	 **/
	function rewrite_flush() {
		global $ai1ec_app_helper;
		$ai1ec_app_helper->create_post_type();
		flush_rewrite_rules( true );
	}

	/**
	 * install_schema function
	 *
	 * This function sets up the database, and upgrades it if it is out of date.
	 *
	 * @return void
	 **/
	function install_schema() {
		global $wpdb;

		// If existing DB version is not consistent with current plugin's version,
		// or does not exist, then create/update table structure using dbDelta().
		if (
			Ai1ec_Meta::get_option( 'ai1ec_db_version' ) != AI1EC_DB_VERSION
		) {

			// =======================
			// = Create table events =
			// =======================
			$table_name = $wpdb->prefix . 'ai1ec_events';
			$sql = "CREATE TABLE $table_name (
				post_id bigint(20) NOT NULL,
				start datetime NOT NULL,
				end datetime,
				allday tinyint(1) NOT NULL,
				instant_event tinyint(1) NOT NULL DEFAULT 0,
				recurrence_rules longtext,
				exception_rules longtext,
				recurrence_dates longtext,
				exception_dates longtext,
				venue varchar(255),
				country varchar(255),
				address varchar(255),
				city varchar(255),
				province varchar(255),
				postal_code varchar(32),
				show_map tinyint(1),
				contact_name varchar(255),
				contact_phone varchar(32),
				contact_email varchar(128),
				contact_url varchar(255),
				cost varchar(255),
				ticket_url varchar(255),
				ical_feed_url varchar(255) CHARACTER SET ascii COLLATE ascii_general_ci,
				ical_source_url varchar(255) CHARACTER SET ascii COLLATE ascii_general_ci,
				ical_organizer varchar(255),
				ical_contact varchar(255),
				ical_uid varchar(255),
				show_coordinates tinyint(1),
				latitude decimal(20,15),
				longitude decimal(20,15),
				facebook_eid bigint(20),
				facebook_user bigint(20),
				facebook_status varchar(1) NOT NULL DEFAULT '',
				PRIMARY KEY  (post_id),
				KEY feed_source (ical_feed_url)
			) CHARACTER SET utf8;";

			// ==========================
			// = Create table instances =
			// ==========================
			$table_name = $wpdb->prefix . 'ai1ec_event_instances';
			$sql .= "CREATE TABLE $table_name (
				id bigint(20) NOT NULL AUTO_INCREMENT,
				post_id bigint(20) NOT NULL,
				start datetime NOT NULL,
				end datetime NOT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY evt_instance (post_id,start)
			) CHARACTER SET utf8;";

			// ================================
			// = Create table category colors =
			// ================================
			$table_name = $wpdb->prefix . 'ai1ec_event_category_colors';
			$sql .= "CREATE TABLE $table_name (
				term_id bigint(20) NOT NULL,
				term_color varchar(255) NOT NULL,
				PRIMARY KEY  (term_id)
			) CHARACTER SET utf8;";

			if ( Ai1ec_Database::instance()->apply_delta( $sql ) ) {
				update_option( 'ai1ec_db_version', AI1EC_DB_VERSION );
			} else {
				trigger_error(
					'Failed to upgrade DB schema',
					E_USER_WARNING
				);
			}
		}
	}
	/**
	 *  This function scans the connector plugins directory and adds the plugin to the plugin helper
	 *
	 */
	function install_plugins() {
		global $ai1ec_importer_plugin_helper;

		// Scan the plugin directory for php files
		foreach ( glob( AI1EC_IMPORT_PLUGIN_PATH . "/*.php" ) as $filename ) {
			// The class name should be the same as the php file
			$class_name = Ai1ec_String_Utility::classify( $filename );
			// If the class exist
			if (
				class_exists( $class_name ) &&
				is_subclass_of( $class_name, 'Ai1ec_Connector_Plugin' )
			) {
				// Instantiate a new object and add it as a plugin.
				// In the constructor the plugin will add his hooks.
				$ai1ec_importer_plugin_helper->add_plugin( new $class_name() );
			}
		}
		$ai1ec_importer_plugin_helper->sort_plugins();
	}

	/**
	 * install_notification_cron function
	 *
	 * This function sets up the cron job for collecting stats
	 *
	 * @return void
	 **/
	function install_n_cron() {
		global $ai1ec_settings;

		// if stats are disabled, cancel the cron
		if( $ai1ec_settings->allow_statistics == false ) {
			// delete our scheduled crons
			wp_clear_scheduled_hook( 'ai1ec_n_cron' );

			// remove the cron version
			delete_option( 'ai1ec_n_cron_version' );

			// prevent the execution of the code below
			return;
		}

		// If existing CRON version is not consistent with current plugin's version,
		// or does not exist, then create/update cron using
		if (
			Ai1ec_Meta::get_option( 'ai1ec_n_cron_version' ) != AI1EC_N_CRON_VERSION
		) {
			// delete our scheduled crons
			wp_clear_scheduled_hook( 'ai1ec_n_cron_version' );
			// set the new cron
			wp_schedule_event(
				Ai1ec_Time_Utility::current_time(),
				AI1EC_N_CRON_FREQ,
				'ai1ec_n_cron'
			);
			// update the cron version
			update_option( 'ai1ec_n_cron_version', AI1EC_N_CRON_VERSION );
		}
	}

	/**
	 * install_u_cron function
	 *
	 * This function sets up the cron job that checks for available updates
	 *
	 * @return void
	 **/
	function install_u_cron() {
		// If existing CRON version is not consistent with current plugin's version,
		// or does not exist, then create/update cron using
		if (
			Ai1ec_Meta::get_option( 'ai1ec_u_cron_version' ) != AI1EC_U_CRON_VERSION
		) {
			// delete our scheduled crons
			wp_clear_scheduled_hook( 'ai1ec_u_cron' );
			// reset flags
			update_option( 'ai1ec_update_available', 0 );
			update_option( 'ai1ec_update_message', '' );
			update_option( 'ai1ec_package_url', '' );
			// set the new cron
			wp_schedule_event(
				Ai1ec_Time_Utility::current_time(),
				AI1EC_U_CRON_FREQ,
				'ai1ec_u_cron'
			);
			// update the cron version
			update_option( 'ai1ec_u_cron_version', AI1EC_U_CRON_VERSION );
		}
	}

	/**
	 * admin_menu function
	 * Display the admin menu items using the add_menu_page WP function.
	 *
	 * @return void
	 */
	function admin_menu() {
		global $ai1ec_settings_controller,
					 $ai1ec_settings_helper,
					 $ai1ec_settings,
					 $ai1ec_themes_controller,
					 $submenu;

		// =======================
		// = Calendar Feeds Page =
		// =======================
		$ai1ec_settings->feeds_page = add_submenu_page(
			AI1EC_ADMIN_BASE_URL,
			__( 'Calendar Feeds', AI1EC_PLUGIN_NAME ),
			__( 'Calendar Feeds', AI1EC_PLUGIN_NAME ),
			'manage_ai1ec_feeds',
			AI1EC_PLUGIN_NAME . '-feeds',
			array( &$ai1ec_settings_controller, 'view_feeds' )
		);
		// Allow feeds page to have additional meta boxes added to it.
		add_action( "load-{$ai1ec_settings->feeds_page}", array( &$ai1ec_settings_helper, 'add_feeds_meta_boxes') );
		// Load our plugin's meta boxes.
		add_action( "load-{$ai1ec_settings->feeds_page}", array( &$ai1ec_settings_controller, 'add_feeds_meta_boxes' ) );

		// ===============
		// = Themes Page =
		// ===============
		$themes_page = add_submenu_page(
			AI1EC_ADMIN_BASE_URL,
			__( 'Calendar Themes', AI1EC_PLUGIN_NAME ),
			__( 'Calendar Themes', AI1EC_PLUGIN_NAME ),
			'switch_ai1ec_themes',
			AI1EC_PLUGIN_NAME . '-themes',
			array( &$ai1ec_themes_controller, 'view' )
		);
		// Make copy of Themes page at its old location.
		$submenu['themes.php'][] = array(
			__( 'Calendar Themes', AI1EC_PLUGIN_NAME ),
			'switch_ai1ec_themes',
			AI1EC_THEME_SELECTION_BASE_URL,
		);

		// ======================
		// = Theme Options Page =
		// ======================
		// if themes are out of date do not show the page
		if( ! $ai1ec_themes_controller->are_themes_outdated() ) {
			$less_variable_editing_page = Ai1ec_Page_Factory::create_page( 'less_variable_editing' );
			$ai1ec_settings->less_variables_page = $less_variable_editing_page->add_page_to_menu(
				AI1EC_ADMIN_BASE_URL,
				__( 'Theme Options', AI1EC_PLUGIN_NAME ),
				__( 'Theme Options', AI1EC_PLUGIN_NAME ),
				'manage_ai1ec_options',
				AI1EC_PLUGIN_NAME . '-edit-css'
			);
			// Make copy of Theme Options page at its old location.
			$submenu['themes.php'][] = array(
				__( 'Calendar Theme Options', AI1EC_PLUGIN_NAME ),
				'manage_ai1ec_options',
				AI1EC_THEME_OPTIONS_BASE_URL,
			);
		}


		// =================
		// = Settings Page =
		// =================
		$ai1ec_settings->settings_page = add_submenu_page(
			AI1EC_ADMIN_BASE_URL,
			__( 'Settings', AI1EC_PLUGIN_NAME ),
			__( 'Settings', AI1EC_PLUGIN_NAME ),
			'manage_ai1ec_options',
			AI1EC_PLUGIN_NAME . '-settings',
			array( &$ai1ec_settings_controller, 'view_settings' )
		);
		// Make copy of Settings page at its old location.
		$submenu['options-general.php'][] = array(
			__( 'Calendar', AI1EC_PLUGIN_NAME ),
			'manage_ai1ec_options',
			AI1EC_SETTINGS_BASE_URL,
		);
		// Allow settings page to have additional meta boxes added to it.
		add_action( "load-{$ai1ec_settings->settings_page}", array( &$ai1ec_settings_helper, 'add_settings_meta_boxes') );
		// Load our plugin's meta boxes.
		add_action( "load-{$ai1ec_settings->settings_page}", array( &$ai1ec_settings_controller, 'add_settings_meta_boxes' ) );

		// ========================
		// = Calendar Update Page =
		// ========================
		add_submenu_page(
			'plugins.php',
			__( 'Upgrade', AI1EC_PLUGIN_NAME ),
			__( 'Upgrade', AI1EC_PLUGIN_NAME ),
			'update_plugins',
			AI1EC_PLUGIN_NAME . '-upgrade',
			array( &$this, 'upgrade' )
		);
		remove_submenu_page( 'plugins.php', AI1EC_PLUGIN_NAME . '-upgrade' );
	}

	/**
	 * is_calendar_page method
	 *
	 * Check if current page matches calendar page, as selected by user, or
	 * it's one of page relatives
	 *
	 * @return int|bool Matching calendar page ID, or false if this is not a
	 *                  calendar page
	 */
	public function is_calendar_page() {
		global $ai1ec_settings,
		       $ai1ec_localization_helper;
		if ( empty( $ai1ec_settings->calendar_page_id ) ) {
			return false;
		}
		$page_ids_to_match = array( $ai1ec_settings->calendar_page_id ) +
			$ai1ec_localization_helper->get_translations_of_page(
				$ai1ec_settings->calendar_page_id
			);
		foreach ( $page_ids_to_match as $page_id ) {
			if ( is_page( $page_id ) ) {
				return $page_id;
			}
		}
		return false;
	}

	/**
	 * route_request function
	 *
	 * Determines if the page viewed should be handled by this plugin, and if so
	 * schedule new content to be displayed.
	 *
	 * @return void
	 **/
	function route_request() {
		global $ai1ec_settings,
		       $ai1ec_calendar_controller,
		       $ai1ec_events_controller,
		       $ai1ec_events_helper,
		       $ai1ec_view_helper;

		// This is needed to load the correct javascript
		$is_calendar_page = false;
		$this->process_request();
		$type = $this->request->get( 'request_type' );
		// Find out if the calendar page ID is defined, and we're on it
		if ( $curr_page = $this->is_calendar_page() ) {
			// Proceed only if the page password is correctly entered OR
			// the page doesn't require a password
			if( ! post_password_required( $curr_page ) ) {
				// Save rendered page content to local variable.
				$this->page_content = $ai1ec_calendar_controller->get_calendar_page( $this->request );

				if( $type === 'json' ) {
					$ai1ec_view_helper->json_response( $this->page_content );
				} elseif ( $type === 'jsonp' ) {
					$ai1ec_view_helper->jsonp_response( $this->page_content, $this->request->get( 'callback' ) );
				} else {
					// Replace page content - make sure it happens at (almost) the very end of
					// page content filters (some themes are overly ambitious here)
					add_filter( 'the_content', array( &$this, 'append_content' ), PHP_INT_MAX - 1 );
					// Tell the javascript loader to load the js for the calendar
					$is_calendar_page = true;
				}
			}
		} elseif ( get_post_type() === AI1EC_POST_TYPE ) {
			if( $type === 'json' ) {
				$ai1ec_view_helper->json_response( $this->page_content );
			} elseif ( $type === 'jsonp' ) {
				$data = array(
					'html' => $ai1ec_events_controller->event_content_jsonp( $this->request )
				);
				$ai1ec_view_helper->jsonp_response( $data, $this->request->get( 'callback' ) );
			} else {
				// Filter event post content, in single- and multi-post views
				add_filter( 'the_content',                              array( $ai1ec_events_controller, 'event_content' ), PHP_INT_MAX - 1 );
			}

		}
		do_action( 'ai1ec_load_frontend_js', $is_calendar_page );
	}

	/**
	 * parse_standalone_request function
	 *
	 * @return void
	 **/
	function parse_standalone_request() {
		global $ai1ec_exporter_controller,
					 $ai1ec_app_helper;

		$plugin     = $ai1ec_app_helper->get_param( 'plugin' );
		$action     = $ai1ec_app_helper->get_param( 'action' );
		$controller = $ai1ec_app_helper->get_param( 'controller' );

		if( ! empty( $plugin ) && $plugin == AI1EC_PLUGIN_NAME && ! empty( $controller ) && ! empty( $action ) ) {
			if( $controller == "ai1ec_exporter_controller" ) :
				switch( $action ) :
					case 'export_events':
						$ai1ec_exporter_controller->export_events();
						break;
				endswitch;
			endif; // ai1ec_exporter_controller
		}
	}

	/**
	 * Append locally generated content to normal page content. By default,
	 * first checks if we are in The Loop before outputting to prevent multiple
	 * calendar display - unless setting is turned on to skip this check.
	 *
	 * @param  string $content Post/Page content
	 * @return string          Modified Post/Page content
	 */
	function append_content( $content ) {
		global $ai1ec_settings;

		// Include any admin-provided page content in the placeholder specified in
		// the calendar theme template.
		if ( $ai1ec_settings->skip_in_the_loop_check || in_the_loop() ) {
			$content = str_replace(
				'<!-- AI1EC_PAGE_CONTENT_PLACEHOLDER -->',
				$content,
				$this->page_content
			);
		}

		return $content;
	}



	/**
	 * upgrade function
	 *
	 * @return void
	 **/
	function upgrade() {
		// continue only if user can update plugins
		if ( ! current_user_can( 'update_plugins' ) )
			wp_die( __( 'You do not have sufficient permissions to update plugins for this site.' ) );

		$package_url = Ai1ec_Meta::get_option( 'ai1ec_package_url' );
		$plugin_name = Ai1ec_Meta::get_option( 'ai1ec_plugin_name' );
		if(
			empty( $package_url ) ||
			empty( $plugin_name )
		) {
			wp_die( __( 'Download package is needed and was not supplied. Visit <a href="http://time.ly/" target="_blank">time.ly</a> to download the newest version of the plugin.' ) );
		}

		// use our custom class
		$upgrader = new Ai1ec_Updater();
		// update the plugin
		$upgrader->upgrade( $plugin_name, $package_url );
		// clear update notification
		update_option( 'ai1ec_update_available', 0 );
		update_option( 'ai1ec_update_message', '' );
		update_option( 'ai1ec_package_url', '' );
		update_option( 'ai1ec_plugin_name', '' );
	}

	/**
	 * Checks if the user is not on install themes page and redirects the user to
	 * that page.
	 *
	 * @return void
	 */
	function check_themes() {
		if ( ! isset( $_REQUEST["page"] ) ||
		     $_REQUEST["page"] != AI1EC_PLUGIN_NAME . '-install-themes' ) {
			wp_redirect( admin_url( AI1EC_INSTALL_THEMES_BASE_URL ) );
		}
	}

	/**
	 * process_request function
	 *
	 * Initialize/validate custom request array, based on contents of $_REQUEST,
	 * to keep track of this component's request variables.
	 *
	 * @return void
	 **/
	private function process_request()
	{
		global $ai1ec_settings;
		$this->request = Ai1ec_Routing_Factory::create_argument_parser_instance();

		if (
			! is_admin() &&
			$ai1ec_settings->calendar_page_id &&
			is_page( $ai1ec_settings->calendar_page_id )
		) {
			foreach ( array( 'cat', 'tag' ) as $name ) {
				$implosion = $this->_add_defaults( $name );
				if ( $implosion ) {
					$this->request['ai1ec_' . $name . '_ids'] = $implosion;
					$_REQUEST['ai1ec_' . $name . '_ids']	  = $implosion;
				}
			}
		}
	}


	/**
	 * _add_defaults method
	 *
	 * Add (merge) default options to given query variable.
	 *
	 * @param string $name Name of query variable to ammend
	 *
	 * @return string|NULL Modified variable values or NULL on failure
	 *
	 * @global    Ai1ec_Settings $ai1ec_settings Instance of settings object
	 *                                           to pull data from
	 * @staticvar array          $mapper         Mapping of query names to
	 *                                           default in settings
	 */
	protected function _add_defaults( $name ) {
		global $ai1ec_settings;
		static $mapper = array(
			'cat' => 'categories',
			'tag' => 'tags',
		);
		$rq_name = 'ai1ec_' . $name . '_ids';
		if (
			! isset( $mapper[$name] ) ||
			! array_key_exists( $rq_name, $this->request )
		) {
			return NULL;
		}
		$options  = explode( ',', $this->request[$rq_name] );
		$property = 'default_' . $mapper[$name];
		$options  = array_merge(
			$options,
			$ai1ec_settings->{$property}
		);
		$filtered = array();
		foreach ( $options as $item ) { // avoid array_filter + is_numeric
			$item = (int)$item;
			if ( $item > 0 ) {
				$filtered[] = $item;
			}
		}
		unset( $options );
		if ( empty( $filtered ) ) {
			return NULL;
		}
		return implode( ',', $filtered );
	}

}
// END class
