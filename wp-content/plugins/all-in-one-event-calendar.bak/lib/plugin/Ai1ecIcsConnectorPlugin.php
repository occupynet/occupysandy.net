<?php

/**
 *
 * @author time.ly
 */

class Ai1ecIcsConnectorPlugin extends Ai1ec_Connector_Plugin {
	const ICS_OPTION_DB_VERSION = 'ai1ec_ics_db_version';
	const ICS_DB_VERSION        = 105;
	/**
	 *
	 * @var array
	 *   title: The title of the tab and the title of the configuration section
	 *   id: The id used in the generation of the tab
	 */
	protected $variables = array(
			"title" => "ICS",
			"id"    => "ics"
	);
	public function __construct() {
		// Add AJAX Actions.
		// Add iCalendar feed
		add_action( 'wp_ajax_ai1ec_add_ics',    array( $this, 'add_ics_feed' ) );
		// Delete iCalendar feed
		add_action( 'wp_ajax_ai1ec_delete_ics', array( $this, 'delete_feeds_and_events' ) );
		// Update iCalendar feed
		add_action( 'wp_ajax_ai1ec_update_ics', array( $this, 'update_ics_feed' ) );
		// Add iCalendar feed frontend handling
		add_action( 'wp_ajax_nopriv_ai1ec_add_ics_frontend', array( $this, 'add_ics_feed_frontend' ) );
		add_action( 'wp_ajax_ai1ec_add_ics_frontend', array( $this, 'add_ics_feed_frontend' ) );
		// Cron job hook
		add_action( 'ai1ec_cron'              , array( $this, 'cron' ) );
		// Handle schema changes.
		$this->install_schema();
		// Install the CRON
		$this->install_cron();
	}

	/**
	 * Validates the add new feed url form
	 *
	 * @return array
	 */
	private function validate_form() {
		global $ai1ec_settings, $ai1ec_events_helper;
		$response = array( 'success' => true );
		// Check nonce.
		if ( isset( $_POST[AI1EC_POST_TYPE] ) &&
			! wp_verify_nonce( $_POST[AI1EC_POST_TYPE], 'ai1ec_submit_ics_form' ) ) {
			$response['message'] = __( 'Access denied.', AI1EC_PLUGIN_NAME );
			$response['success'] = false;
			return $response;
		}

		// Check CAPTCHA.
		if ( ! is_user_logged_in() &&
			! empty( $ai1ec_settings->recaptcha_public_key ) ) {

			$check = $ai1ec_events_helper->check_captcha();
			if( false === $check['success'] ) {
				$response['success'] = false;
				$response['message'] = $check['message'];
				return $response;
			}
		}
		$response['message'] = __( 'Your suggestion was submitted to a site administrator for review.', AI1EC_PLUGIN_NAME );
		return $response;
	}

	/**
	 * Send an e-mail to the admin and another to the user if form passes
	 * validation.
	 */
	public function add_ics_feed_frontend() {
		global $wpdb, $ai1ec_settings;

		$table_name = $wpdb->prefix . 'ai1ec_event_feeds';
		$check = $this->validate_form();

		$check['nonce'] = wp_nonce_field(
			'ai1ec_submit_ics_form',
			AI1EC_POST_TYPE,
			true,
			false
		);

		if ( true === $check['success'] ) {
			// Strip slashes if ridiculous PHP setting magic_quotes_gpc is enabled.
			if ( get_magic_quotes_gpc() ) {
				foreach ( $_POST as &$param ) {
					$param = stripslashes( $param );
				}
			}

			// Send admin e-mail.
			$admin_email = get_option( 'admin_email' );
			$admin_mail_subject = $this->parse_mail( $ai1ec_settings->admin_mail_subject );
			$admin_mail_body = $this->parse_mail( $ai1ec_settings->admin_mail_body );
			wp_mail( $admin_email, $admin_mail_subject, $admin_mail_body );

			// Send user e-mail.
			$user_email = $_POST['ai1ec_submitter_email'];
			$user_mail_subject = $this->parse_mail( $ai1ec_settings->user_mail_subject );
			$user_mail_body = $this->parse_mail( $ai1ec_settings->user_mail_body );
			wp_mail( $user_email, $user_mail_subject, $user_mail_body );
		}

		echo json_encode( $check );
		exit();
	}

	/**
	 * Parses the string, substituting the [...] tokens with values.
	 *
	 * @param string $text
	 * @return string
	 */
	private function parse_mail( $text ) {
		$categories = isset( $_POST['ai1ec_categories'] ) ? $_POST['ai1ec_categories'] : array();
		foreach ( $categories as &$cat ) {
			$term = get_term( $cat, 'events_categories' );
			$cat = $term->name;
		}
		$translations = array(
			'[feed_url]'   => $_POST['ai1ec_calendar_url'],
			'[categories]' => implode( ', ' , $categories ),
			'[user_email]' => $_POST['ai1ec_submitter_email'],
			'[site_title]' => get_bloginfo( 'name' ),
			'[site_url]'   => site_url(),
			'[feeds_url]'  => admin_url( AI1EC_FEED_SETTINGS_BASE_URL . '#ics' ),
		);
		return strtr( $text, $translations );
	}

	/**
	 * This function sets up the cron job for updating the events, and upgrades it if it is out of date.
	 *
	 * @return void
	 */
	private function install_cron() {
		// If existing CRON version is not consistent with current plugin's version,
		// or does not exist, then create/update cron using
		if (
			Ai1ec_Meta::get_option( 'ai1ec_cron_version' ) != AI1EC_CRON_VERSION
		) {
			global $ai1ec_settings;
			// delete our scheduled crons
			wp_clear_scheduled_hook( 'ai1ec_cron' );
			// set the new cron
			wp_schedule_event( current_time( 'timestamp' ) + 600, $ai1ec_settings->cron_freq, 'ai1ec_cron' );
			// update the cron version
			update_option( 'ai1ec_cron_version', AI1EC_CRON_VERSION );
		}
	}

	/**
	 * Handles all the required steps to install / update the schema
	 */
	private function install_schema() {
		// If existing DB version is not consistent with current plugin's version,
		// or does not exist, then create/update table structure using dbDelta().
		if (
			Ai1ec_Meta::get_option( self::ICS_OPTION_DB_VERSION ) !=
			self::ICS_DB_VERSION
		) {
			global $wpdb;
			// ======================
			// = Create table feeds =
			// ======================
			$table_name = $wpdb->prefix . 'ai1ec_event_feeds';
			$sql = "CREATE TABLE $table_name (
				feed_id bigint(20) NOT NULL AUTO_INCREMENT,
				feed_url varchar(255) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
				feed_category bigint(20) NOT NULL,
				feed_tags varchar(255) NOT NULL,
				comments_enabled tinyint(1) NOT NULL DEFAULT '1',
				map_display_enabled tinyint(1) NOT NULL DEFAULT '0',
				PRIMARY KEY  (feed_id),
				UNIQUE KEY feed (feed_url)
			) CHARACTER SET utf8;";


			if ( Ai1ec_Database::instance()->apply_delta( $sql ) ) {
				update_option( self::ICS_OPTION_DB_VERSION, self::ICS_DB_VERSION );
			} else {
				trigger_error(
					'Failed to upgrade ICS DB schema',
					E_USER_WARNING
				);
			}

		}
	}

	/**
	 * cron function
	 *
	 * Import all ICS feeds
	 *
	 * @return void
	 **/
	function cron() {
		global $wpdb,
		       $ai1ec_importer_helper,
		       $ai1ec_events_helper,
		       $ai1ec_app_helper,
		       $ai1ec_settings_controller;

		// Initializing custom post type and custom taxonomies
		$ai1ec_app_helper->create_post_type();

		// ====================
		// = Select all feeds =
		// ====================
		$table_name = $wpdb->prefix . 'ai1ec_event_feeds';
		$sql = "SELECT * FROM {$table_name}";
		$feeds = $wpdb->get_results( $sql );

		// ===============================
		// = go over each iCalendar feed =
		// ===============================
		foreach( $feeds as $feed ) {
			// update the feed
			$this->update_ics_feed( $feed->feed_id );

		}
	}
	/**
	 * (non-PHPdoc)
	 * @see Ai1ec_Connector_Plugin::handle_feeds_page_post()
	 */
	public function handle_feeds_page_post() {
		global $ai1ec_settings_controller;
		if( isset( $_POST['ai1ec_save_settings'] ) ) {
			$ai1ec_settings_controller->save( 'feeds' );
		}
	}
	/**
	 * (non-PHPdoc)
	 * @see Ai1ec_Connector_Plugin::render_tab_content()
	 */
	public function render_tab_content() {
		// Load the scripts for the plugin-
		$this->load_scripts();
		// Render the opening div
		$this->render_opening_div_of_tab();
		// Render the body of the tab
		global $ai1ec_view_helper,
		       $ai1ec_settings_helper,
		       $ai1ec_settings;

		$args = array(
				'cron_freq'          => $ai1ec_settings_helper->get_cron_freq_dropdown( $ai1ec_settings->cron_freq ),
				'event_categories'   => $ai1ec_settings_helper->get_event_categories_select(),
				'feed_rows'          => $ai1ec_settings_helper->get_feed_rows()
		);
		$ai1ec_view_helper->display_admin( 'plugins/ics/display_feeds.php', $args );
		$this->render_closing_div_of_tab();
	}
	/**
	 * (non-PHPdoc)
	 * @see Ai1ec_Connector_Plugin::display_admin_notices()
	 */
	public function display_admin_notices() {
		return;
	}
	/**
	 * (non-PHPdoc)
	 * @see Ai1ec_Connector_Plugin::run_uninstall_procedures()
	 */
	public function run_uninstall_procedures() {
		// Delete tables
		global $wpdb;
		$table_name = $wpdb->prefix . 'ai1ec_event_feeds';
		$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
		// Delete scheduled tasks
		wp_clear_scheduled_hook( 'ai1ec_cron' );
		// Delete options
		delete_option( self::ICS_DB_VERSION );
		delete_option( self::ICS_OPTION_DB_VERSION );
	}
	private function load_scripts() {
		global $ai1ec_view_helper;
		// Load javascript for the plugin
		//$ai1ec_view_helper->admin_enqueue_script( 'ai1ec_ics', 'plugins/ics.js', array( 'jquery', 'ai1ec-utils' ) );
	}
	/**
	 * add_ics_feed function
	 *
	 * Adds submitted ics feed to the database
	 *
	 * @return string JSON output
	 **/
	public function add_ics_feed() {
		global $ai1ec_view_helper,
		       $wpdb;

		$table_name = $wpdb->prefix . 'ai1ec_event_feeds';

		$entry = array( // convert webcal to http on 'feed_url'
			'feed_url'            => $_REQUEST['feed_url'],
			'feed_category'       => $_REQUEST['feed_category'],
			'feed_tags'           => $_REQUEST['feed_tags'],
			'comments_enabled'    => Ai1ec_Number_Utility::db_bool(
				$_REQUEST['comments_enabled']
			),
			'map_display_enabled' => Ai1ec_Number_Utility::db_bool(
				$_REQUEST['map_display_enabled']
			),
		);

		$format = array(
			'%s',
			'%d',
			'%s',
			'%d',
			'%d',
		);

		$wpdb->insert( $table_name, $entry, $format );
		$feed_id = $wpdb->insert_id;
		ob_start();
		$feed_category = get_term( $_REQUEST['feed_category'], 'events_categories' );
		$args          = array(
			'feed_url'            => $_REQUEST['feed_url'],
			'event_category'      => $feed_category->name,
			'tags'                => $_REQUEST['feed_tags'],
			'feed_id'             => $feed_id,
			'comments_enabled'    => (bool)intval(
				$_REQUEST['comments_enabled']
			),
			'map_display_enabled' => (bool)intval(
				$_REQUEST['map_display_enabled']
			),
			'events'              => 0,
		);
		// display added feed row
		$ai1ec_view_helper->display_admin( 'feed_row.php', $args );

		$output = ob_get_contents();
		ob_end_clean();

		$output = array(
				"error"   => 0,
				"message" => stripslashes( $output )
		);

		echo json_encode( $output );
		exit();
	}

	/**
	 * Delete feeds and events
	 *
	 */
	public function delete_feeds_and_events() {
		$remove_events = $_POST['remove_events'] === 'true' ? TRUE : FALSE;
		$ics_id = isset( $_POST['ics_id'] ) ? (int) $_REQUEST['ics_id'] : 0;
		if( $remove_events ) {
			$output = $this->flush_ics_feed( TRUE, FALSE );
			if( $output['error'] === FALSE ) {
				$this->delete_ics_feed( FALSE, $ics_id );
			}
			echo json_encode( $output );
			exit();
		} else {
			$this->delete_ics_feed( TRUE, $ics_id );;
		}

	}
	/**
	 * flush_ics_feed function
	 *
	 * Deletes all event posts that are from that selected feed
	 *
	 * @param bool $ajax When set to TRUE, the data is outputted using json_response
	 * @param bool|string $feed_url Feed URL
	 *
	 * @return void
	 **/
	public function flush_ics_feed( $ajax = TRUE, $feed_url = FALSE ) {
		global $wpdb,
		       $ai1ec_view_helper;
		$ics_id = isset( $_REQUEST['ics_id'] ) ? (int) $_REQUEST['ics_id'] : 0;
		$table_name = $wpdb->prefix . 'ai1ec_event_feeds';
		if( $feed_url === FALSE ) {
			$feed_url = $wpdb->get_var( $wpdb->prepare( "SELECT feed_url FROM $table_name WHERE feed_id = %d", $ics_id ) );
		}
		if( $feed_url ) {
			$table_name = $wpdb->prefix . 'ai1ec_events';
			$sql = "SELECT post_id FROM {$table_name} WHERE ical_feed_url = '%s'";
			$events = $wpdb->get_results( $wpdb->prepare( $sql, $feed_url ) );
			$total = count( $events );
			foreach( $events as $event ) {
				// delete post (this will trigger deletion of cached events, and remove the event from events table)
				wp_delete_post( $event->post_id, 'true' );
			}
			$output = array(
					'error'   => FALSE,
					'message' => sprintf( __( 'Deleted %d events', AI1EC_PLUGIN_NAME ), $total ),
					'count'   => $total,
			);
		} else {
			$output = array(
					'error'   => TRUE,
					'message' => __( 'Invalid ICS feed ID', AI1EC_PLUGIN_NAME )
			);
		}
		if( $ajax ) {
			$output['ics_id'] = $ics_id;
			return $output;
		}
	}

	/**
	 * update_ics_feed function
	 *
	 * Imports the selected iCalendar feed
	 *
	 * @return void
	 **/
	public function update_ics_feed( $feed_id = false ) {
		global $wpdb,
		       $ai1ec_view_helper,
		       $ai1ec_importer_helper;
		$ajax = false;
		// if no feed is provided, we are using ajax
		if( ! $feed_id ) {
			$ajax = true;
			$feed_id = (int) $_REQUEST['ics_id'];
		}
		$table_name = $wpdb->prefix . 'ai1ec_event_feeds';
		$feed = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE feed_id = %d", $feed_id ) );

		if( $feed ) {
			// flush the feed
			$this->flush_ics_feed( false, $feed->feed_url );
			$count = 0;
			$message = false;
			// reimport the feed
			$response = wp_remote_get( $feed->feed_url, array( 'sslverify' => false, 'timeout' => 120 ) );
			if (
			! is_wp_error( $response )             &&
			isset( $response['response'] )         &&
			isset( $response['response']['code'] ) &&
			$response['response']['code'] == 200   &&
			isset( $response['body'] )             &&
			! empty( $response['body'] )
			) {
				$count = @$ai1ec_importer_helper->parse_ics_feed( $feed,  $response['body']);
			} else {
				$message = __( 
					"We couldn't find a valid transport to fetch the calendar data.
					You should set allow_url_fopen in php.ini as suggested in 
					<a href='http://forums.hostdime.com/showthread.php?8620-PHP-allow_url_fopen' target='_blank' >this</a> article", 
					AI1EC_PLUGIN_NAME
				);
			}

			if ( $count == 0 ) {
				// If results are 0, it could be result of a bad URL or other error, send a specific message
				$return_message = false === $message ?
					__( 'No events were found', AI1EC_PLUGIN_NAME ) :
					$message;
				$output = array(
						'error'   => true,
						'message' => $return_message
				);
			} else {
				$output = array(
						'error'       => false,
						'message'     => sprintf( _n( 'Imported %s event', 'Imported %s events', $count, AI1EC_PLUGIN_NAME ), $count ),
				);
			}
		} else {
			$output = array(
					'error' 	=> true,
					'message'	=> __( 'Invalid ICS feed ID', AI1EC_PLUGIN_NAME )
			);
		}
		$output['ics_id'] = $feed_id;
		if( true === $ajax ) {
			$ai1ec_view_helper->json_response( $output );
		}
	}

	/**
	 * delete_ics_feed function
	 *
	 * Deletes submitted ics feed id from the database
	 *
	 * @param bool $ajax When set to TRUE, the data is outputted using json_response
	 * @param bool|string $ics_id Feed URL
	 *
	 * @return String JSON output
	 **/
	public function delete_ics_feed( $ajax = TRUE, $ics_id = FALSE ) {
		global $wpdb,
		       $ai1ec_view_helper;
		if( $ics_id === FALSE ) {
			$ics_id = (int) $_REQUEST['ics_id'];
		}
		$table_name = $wpdb->prefix . 'ai1ec_event_feeds';
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$table_name} WHERE feed_id = %d", $ics_id ) );
		$output = array(
				'error'   => false,
				'message' => __( 'Feed deleted', AI1EC_PLUGIN_NAME ),
				'ics_id'  => $ics_id,
		);
		if( $ajax === TRUE ) {
			$ai1ec_view_helper->json_response( $output );
		}
	}
}
