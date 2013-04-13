<?php

/**
 * Controller class for the exporter.
 *
 * @author     Timely Network Inc
 * @since      2011.07.13
 *
 * @package    AllInOneEventCalendar
 * @subpackage AllInOneEventCalendar.App.Controller
 */
class Ai1ec_Exporter_Controller {
	/**
	 * _instance class variable
	 *
	 * Class instance
	 *
	 * @var null | object
	 **/
	private static $_instance = NULL;

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
	 * Constructor
	 *
	 * Default constructor
	 **/
	private function __construct() { }

	/**
	 * n_cron function
	 *
	 * @return void
	 **/
	function n_cron() {
		global $ai1ec_settings, $wpdb;

		$query = "SELECT COUNT( ID ) as num_events " .
		         "FROM $wpdb->posts " .
		         "WHERE post_type = '" . AI1EC_POST_TYPE . "' AND " .
		         "post_status = 'publish'";
		$n_events = $wpdb->get_var( $query );

		$query   = "SELECT COUNT( ID ) FROM $wpdb->users";
		$n_users = $wpdb->get_var( $query );

		$categories = $tags = array();
		foreach( get_terms( 'events_categories', array( 'hide_empty' => false ) ) as $term ) {
			if( isset( $term->name ) )
				$categories[] = $term->name;
		}
		foreach( get_terms( 'events_tags', array( 'hide_empty' => false ) ) as $term ) {
			if( isset( $term->name ) )
				$tags[] = $term->name;
		}
		$data = array(
			'n_users'        => $n_users,
			'n_events'       => $n_events,
			'categories'     => $categories,
			'tags'           => $tags,
			'blog_name'      => get_bloginfo( 'name' ),
			'cal_url'        => get_permalink( $ai1ec_settings->calendar_page_id ),
			'ics_url'        => AI1EC_EXPORT_URL,
			'php_version'    => phpversion(),
			'wp_version'     => get_bloginfo( 'version' ),
			'wp_lang'        => get_bloginfo( 'language' ),
			'wp_url'         => home_url(),
			'timezone'       => Ai1ec_Meta::get_option(
				'timezone_string',
				'America/Los_Angeles'
			),
			'privacy'        => Ai1ec_Meta::get_option( 'blog_public' ),
			'plugin_version' => AI1EC_VERSION,
			'active_theme'   => Ai1ec_Meta::get_option(
				'ai1ec_template',
				AI1EC_DEFAULT_THEME_NAME
			),
		);
		// send request
		wp_remote_post( AI1EC_STATS_API, array(
			'body' => $data
		) );

	}

	/**
	 * export_location function
	 *
	 * @param array $data
	 * @param bool $update
	 *
	 * @return void
	 **/
	function export_location( $data, $update = false ) {
		// if there is no data to send, return
		if (
			empty( $data['venue'] ) &&
			empty( $data['country'] ) &&
			empty( $data['address'] ) &&
			empty( $data['city'] ) &&
			empty( $data['province'] ) &&
			empty( $data['postal_code'] ) &&
			empty( $data['latitude'] ) &&
			empty( $data['longitude'] )
		) {
			return;
		}

		// For this remote call we need to remove cUrl, because the minimum timeout
		// of cUrl is 1 second. This causes Facebook import to fail when importing
		// many events (even from just a few friends). A timeout greater than 0.05s
		// will be a great hindrance to performance.
		add_filter( 'use_curl_transport', array( $this, 'remove_curl' ) );
		// Send data using post to locations API.
		@wp_remote_post( AI1EC_LOCATIONS_API, array(
			'body' => array(
				'venue'       => $data['venue'],
				'country'     => $data['country'],
				'address'     => $data['address'],
				'city'        => $data['city'],
				'province'    => $data['province'],
				'postal_code' => $data['postal_code'],
				'latitude'    => $data['latitude'],
				'longitude'   => $data['longitude'],
				'update'      => $update,
			),
			'timeout' => 0.01,
			'blocking' => false,
		) );
		// Revert cUrl setting to what it was.
		remove_filter( 'use_curl_transport', array( $this, 'remove_curl' ) );
	}

	/**
	 * Simple function that returns false, intended for the use_curl_transport
	 * filter to disable the use of cUrl.
	 *
	 * @return boolean
	 */
	public function remove_curl() {
		return false;
	}

	/**
	 * export_events function
	 *
	 * Export events
	 *
	 * @return void
	 **/
	function export_events() {
		global $ai1ec_events_helper,
		       $ai1ec_exporter_helper,
		       $ai1ec_localization_helper;

		$ai1ec_cat_ids  = ! empty( $_REQUEST['ai1ec_cat_ids'] )  ? $_REQUEST['ai1ec_cat_ids']  : false;
		$ai1ec_tag_ids  = ! empty( $_REQUEST['ai1ec_tag_ids'] )  ? $_REQUEST['ai1ec_tag_ids']  : false;
		$ai1ec_post_ids = ! empty( $_REQUEST['ai1ec_post_ids'] ) ? $_REQUEST['ai1ec_post_ids'] : false;
		if ( ! empty( $_REQUEST['lang'] ) ) {
			$ai1ec_localization_helper->set_language( $_REQUEST['lang'] );
		}

		$filter = array();
		if ( $ai1ec_cat_ids ) {
			$filter['cat_ids'] = explode( ',', $ai1ec_cat_ids );
		}
		if ( $ai1ec_tag_ids ) {
			$filter['tag_ids'] = explode( ',', $ai1ec_tag_ids );
		}
		if ( $ai1ec_post_ids ) {
			$filter['post_ids'] = explode( ',', $ai1ec_post_ids );
		}

		// when exporting events by post_id, do not look up the event's start/end date/time
		$start  = ( $ai1ec_post_ids !== false )
			? false
			: Ai1ec_Time_Utility::current_time( true ) - 24 * 60 * 60; // Include any events ending today
		$end    = false;
		$c = new vcalendar();
		$c->setProperty( 'calscale', 'GREGORIAN' );
		$c->setProperty( 'method', 'PUBLISH' );
		$c->setProperty( 'X-WR-CALNAME', get_bloginfo( 'name' ) );
		$c->setProperty( 'X-WR-CALDESC', get_bloginfo( 'description' ) );
		$c->setProperty( 'X-FROM-URL', home_url() );
		// Timezone setup
		$tz = Ai1ec_Meta::get_option( 'timezone_string' );
		if ( $tz ) {
			$c->setProperty( 'X-WR-TIMEZONE', $tz );
			$tz_xprops = array( 'X-LIC-LOCATION' => $tz );
			iCalUtilityFunctions::createTimezone( $c, $tz, $tz_xprops );
		}

		$events = $ai1ec_events_helper->get_matching_events( $start, $end, $filter );
		foreach ( $events as $event ) {
			$ai1ec_exporter_helper->insert_event_in_calendar( $event, $c, $export = true );
		}
		$str = ltrim( $c->createCalendar() );

		header( 'Content-type: text/calendar; charset=utf-8' );
		echo $str;
		exit;
	}
}
// END class
