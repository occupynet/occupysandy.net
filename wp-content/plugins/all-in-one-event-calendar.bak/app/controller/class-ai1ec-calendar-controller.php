<?php

/**
 * Controller class for calendars.
 *
 * @author     Timely Network Inc
 * @since      2011.07.13
 *
 * @package    AllInOneEventCalendar
 * @subpackage AllInOneEventCalendar.App.Controller
 */
class Ai1ec_Calendar_Controller {
	/**
	 * _instance class variable
	 *
	 * Class instance
	 *
	 * @var null | object
	 **/
	static $_instance = NULL;

	/**
	 * __construct function
	 *
	 * Default constructor - calendar initialization
	 **/
	private function __construct() {

	}

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
	 * Returns rendered calendar page.
	 *
	 * @return string Rendered calendar page
	 */
	public function get_calendar_page( Ai1ec_Abstract_Query $request ) {
		global $ai1ec_view_helper,
		       $ai1ec_settings,
		       $ai1ec_events_helper,
		       $ai1ec_calendar_helper,
		       $ai1ec_themes_controller;

		if ( $notice = $ai1ec_themes_controller->frontend_outdated_themes_notice( false ) ) {
			return $notice;
		}

		// Queue any styles, scripts
		$this->load_css();
		// Are we loading a shortcode?
		$shortcode = $request->get( 'shortcode' );

		$view_args = $this->get_view_args_for_view( $request );
		$action = $view_args['action'];
		$type = $request->get( 'request_type' );

		$exact_date = $this->get_exact_date( $request );
		$get_view = "get_{$action}_view";

		$view = $this->$get_view( $view_args );
		$content = '';
		$args_for_filter = array();
		$categories = $ai1ec_calendar_helper->get_html_for_categories( $view_args );
		$tags = $ai1ec_calendar_helper->get_html_for_tags( $view_args );
		$dropdown_args = $view_args;
		if ( isset( $dropdown_args['time_limit'] ) && false !== $exact_date ) {
			$dropdown_args['exact_date'] = $exact_date;
		}
		$views_dropdown =
			$ai1ec_calendar_helper->get_html_for_views_dropdown( $dropdown_args );
		$subscribe_buttons = '';
		if ( ! $ai1ec_settings->turn_off_subscription_buttons ) {
			$subscribe_buttons =
				$ai1ec_calendar_helper->get_html_for_subscribe_buttons( $view_args );
		}
		if ( ( $view_args['no_navigation'] || $type !== 'standard' ) &&
		     'true' !== $shortcode ) {
			$args_for_filter = $view_args;
			// send data both for json and jsonp as shortcodes are jsonp
			$content = array(
				'html'              => $view,
				'categories'        => $categories,
				'tags'              => $tags,
				'views_dropdown'    => $views_dropdown,
				'subscribe_buttons' => $subscribe_buttons
			);

		} else {
			// Determine whether to display "Post your event" button on front-end.
			$contribution_buttons =
				$ai1ec_calendar_helper->get_html_for_contribution_buttons();

			// Define new arguments for overall calendar view
			$page_args = array(
				'current_view'            => $action,
				'views_dropdown'          => $views_dropdown,
				'view'                    => $view,
				'contribution_buttons'    => $contribution_buttons,
				'categories'              => $categories,
				'tags'                    => $tags,
				'subscribe_buttons'       => $subscribe_buttons,
				'data_type'               => $view_args['data_type'],
			);
			$content = $ai1ec_view_helper->get_theme_view( 'calendar.php', $page_args );
			$args_for_filter = $page_args;
		}

		return apply_filters(
			'ai1ec_view',
			$content,
			$args_for_filter
		);
	}

	public function get_view_args_for_view( Ai1ec_Abstract_Query $request ) {
		global $ai1ec_events_helper, $ai1ec_settings;
		// Define arguments for specific calendar sub-view (month, agenda,
		// posterboard, etc.)
		// Preprocess action.
		// Allow action w/ or w/o ai1ec_ prefix. Remove ai1ec_ if provided.
		$action = $request->get( 'action' );

		if ( 0 === strncmp( $action, 'ai1ec_', 6 ) ) {
			$action = substr( $action, 6 );
		}
		$view_args = $request->get_dict( array(
			'post_ids',
			'cat_ids',
			'tag_ids',
		) );

		if( $request->is_empty_request() ) {
			$view_args['cat_ids'] = $ai1ec_settings->default_categories;
			$view_args['tag_ids'] = $ai1ec_settings->default_tags;
		}

		$type = $request->get( 'request_type' );
		$view_args['data_type'] = $this->return_data_type_for_request_type( $type );

		$exact_date = $this->get_exact_date( $request );

		$view_args['no_navigation'] = $request->get( 'no_navigation' ) === 'true' ? true : false;

		// Find out which view of the calendar page was requested, and render it
		// accordingly.
		$view_args['action'] = $action;
		switch( $action ) {
			case 'posterboard':
			case 'stream':
			case 'agenda':
				$view_args += $request->get_dict( array(
				'page_offset',
				'time_limit',
				) );
				if( false !== $exact_date ) {
					$view_args['time_limit'] = $ai1ec_events_helper->local_to_gmt( $exact_date ) - 1;
				}
				break;

			case 'month':
			case 'oneday':
			case 'week':
				$view_args["{$action}_offset"] = $request->get( "{$action}_offset" );
				if( false !== $exact_date ) {
					$view_args['exact_date'] = $exact_date;
				}
				break;
		}
		$view_args['request'] = $request;
		return $view_args;
	}

	/**
	 * Get the exact date from request if available, or else from settings.
	 *
	 * @param Ai1ec_Abstract_Query $request
	 * @return boolean|int
	 */
	private function get_exact_date( Ai1ec_Abstract_Query $request ) {
		global $ai1ec_settings;

		// Preprocess exact_date.
		// Check to see if a date has been specified.
		$exact_date = $request->get( 'exact_date' );
		// Let's check if we have a date
		if ( false !== $exact_date ) {
			// If it's not a timestamp
			if( ! Ai1ec_Validation_Utility::is_valid_time_stamp( $exact_date ) ) {
				// Try to parse it
				$exact_date = $this->return_gmtime_from_exact_date( $exact_date );
			}
		}
		// Last try, let's see if an exact date is set in settings.
		if ( false === $exact_date && $ai1ec_settings->exact_date !== '' ) {
			$exact_date = $this->return_gmtime_from_exact_date(
				$ai1ec_settings->exact_date
			);
		}
		return $exact_date;
	}

	/**
	 * Returns the correct data attribute to use in views
	 *
	 * @param string $type
	 */
	private function return_data_type_for_request_type( $type ) {
		$data_type = 'data-type="json"';
		if ( $type === 'jsonp' ) {
			$data_type = 'data-type="jsonp"';
		}
		return $data_type;
	}

	/**
	 * Decomposes an 'exact_date' parameter into month, day, year components based
	 * on date pattern defined in settings (assumed to be in local time zone),
	 * then returns a timestamp in GMT.
	 *
	 * @param  string     $exact_date 'exact_date' parameter passed to a view
	 * @return bool|int               false if argument not provided or invalid,
	 *                                else UNIX timestamp in GMT
	 */
	private function return_gmtime_from_exact_date( $exact_date ) {
		global $ai1ec_settings;

		$bits = Ai1ec_Validation_Utility::validate_date_and_return_parsed_date(
			$exact_date,
			$ai1ec_settings->input_date_format
		);
		if( false === $bits ) {
			$exact_date = false;
		} else {
			$exact_date = Ai1ec_Time_Utility::local_to_gmt( gmmktime(
				0, 0, 0, $bits['month'], $bits['day'], $bits['year']
			) );
		}
		return $exact_date;
	}

	/**
	 * Return an agenda-like view of the calendar, one of 'posterboard', 'stream',
	 * or 'agenda', optionally filtered by event categories and tags.
	 *
	 * @param string $type    Type of view: 'posterboard', 'stream', or 'agenda'
	 * @param array $args     associative array with any of these elements:
	 *   int page_offset   => specifies which page to display relative to today's page
	 *   int time_limit    => specifies upper/lower (depending on direction) time limit
	 *   array categories  => restrict events returned to the given set of
	 *                        event category slugs
	 *   array tags        => restrict events returned to the given set of
	 *                        event tag names
	 *
	 * @return string	        returns string of view output
	 */
	function get_agenda_like_view( $type, $args ) {
		global $ai1ec_view_helper,
		       $ai1ec_events_helper,
		       $ai1ec_calendar_helper,
		       $ai1ec_settings;

		// Get localized time
		$timestamp = $ai1ec_events_helper->gmt_to_local(
			Ai1ec_Time_Utility::current_time()
		);

		// Get events, then classify into date array
		$per_page_setting = $type . '_events_per_page';
		$results = $ai1ec_calendar_helper->get_events_relative_to(
			$timestamp,
			$ai1ec_settings->$per_page_setting,
			$args['page_offset'],
			array(
				'post_ids' => $args['post_ids'],
				'cat_ids' => $args['cat_ids'],
				'tag_ids' => $args['tag_ids'],
			),
			$args['time_limit']
		);
		$dates = $ai1ec_calendar_helper->get_agenda_like_date_array(
			$results['events'],
			$args['request']
		);

		// Create pagination links.
		$pagination_links = '';
		if( ! $args['no_navigation'] ) {
			$pagination_links =
				$ai1ec_calendar_helper->get_agenda_like_pagination_links(
					$args,
					$results['prev'],
					$results['next'],
					$results['date_first'],
					$results['date_last']
				);
			$pagination_links = $ai1ec_view_helper->get_theme_view(
				'pagination.php',
				array( 'links' => $pagination_links, 'data_type' => $args['data_type'] )
			);
		}

		// Generate title of view based on date range month & year.
		$range_start = $results['date_first'] ? $results['date_first'] : $timestamp;
		$range_end   = $results['date_last']  ? $results['date_last'] : $timestamp;
		$range_start = Ai1ec_Time_Utility::gmt_to_local( $range_start );
		$range_end   = Ai1ec_Time_Utility::gmt_to_local( $range_end );
		$start_year  = Ai1ec_Time_Utility::date_i18n( 'Y', $range_start );
		$end_year    = Ai1ec_Time_Utility::date_i18n( 'Y', $range_end );
		$start_month = Ai1ec_Time_Utility::date_i18n( 'F', $range_start );
		$end_month   = Ai1ec_Time_Utility::date_i18n( 'F', $range_end );
		if ( $start_year === $end_year && $start_month === $end_month ) {
			$title_date_range = "$start_month $start_year";
		} elseif ( $start_year === $end_year ) {
			$title_date_range = "$start_month – $end_month $end_year";
		} else {
			$title_date_range = "$start_month $start_year – $end_month $end_year";
		}

		$view_args = array(
			'title'                     => $title_date_range,
			'dates'                     => $dates,
			'type'                      => $type,
			'tile_min_width'            => $ai1ec_settings->posterboard_tile_min_width,
			'show_year_in_agenda_dates' => $ai1ec_settings->show_year_in_agenda_dates,
			'expanded'                  => $ai1ec_settings->agenda_events_expanded,
			'show_location_in_title'    => $ai1ec_settings->show_location_in_title,
			'page_offset'               => $args['page_offset'],
			'pagination_links'          => $pagination_links,
			'post_ids'                  => join( ',', $args['post_ids'] ),
 			'data_type'                 => $args['data_type'],
			'data_type_events'          => '',
		);
		if( $ai1ec_settings->ajaxify_events_in_web_widget ) {
			$view_args['data_type_events'] = $args['data_type'];
		}
		// Add extra buttons to Agenda view if events were returned.
		if ( $type === 'agenda' && $dates ) {
			$view_args['before_pagination'] =
				$ai1ec_view_helper->get_theme_view( 'agenda-buttons.php', $view_args );
		}
		// Add navigation if requested.
		$view_args['navigation'] = $args['no_navigation'] ? '' :
			$ai1ec_view_helper->get_theme_view( 'navigation.php', $view_args );

		return apply_filters(
			'ai1ec_get_' . $type . '_view',
			$ai1ec_view_helper->get_theme_view( $type . '.php', $view_args ),
			$view_args
		);
	}

	/**
	 * Return the embedded posterboard view of the calendar, optionally filtered
	 * by event categories and tags.
	 *
	 * @param array $args     associative array with any of these elements:
	 *   int page_offset   => specifies which page to display relative to today's page
	 *   array categories  => restrict events returned to the given set of
	 *                        event category slugs
	 *   array tags        => restrict events returned to the given set of
	 *                        event tag names
	 *
	 * @return string	        returns string of view output
	 */
	function get_posterboard_view( $args ) {
		return $this->get_agenda_like_view( 'posterboard', $args );
	}

	/**
	 * Return the embedded stream view of the calendar, optionally filtered by
	 * event categories and tags.
	 *
	 * @param array $args     associative array with any of these elements:
	 *   int page_offset   => specifies which page to display relative to today's page
	 *   array categories  => restrict events returned to the given set of
	 *                        event category slugs
	 *   array tags        => restrict events returned to the given set of
	 *                        event tag names
	 *
	 * @return string	        returns string of view output
	 */
	function get_stream_view( $args ) {
		return $this->get_agenda_like_view( 'stream', $args );
	}

	/**
	 * get_month_view function
	 *
	 * Return the embedded month view of the calendar, optionally filtered by
	 * event categories and tags.
	 *
	 * @param array $args     associative array with any of these elements:
	 *   int month_offset  => specifies which month to display relative to the
	 *                        current month
	 *   array cat_ids     => restrict events returned to the given set of
	 *                        event category slugs
	 *   array tag_ids     => restrict events returned to the given set of
	 *                        event tag names
	 *   array post_ids    => restrict events returned to the given set of
	 *                        post IDs
	 *
	 * @return string	        returns string of view output
	 */
	function get_month_view( $args ) {
		global $ai1ec_view_helper,
		       $ai1ec_calendar_helper,
		       $ai1ec_settings;

		$defaults = array(
			'month_offset'  => 0,
			'cat_ids'       => array(),
			'tag_ids'       => array(),
			'post_ids'      => array(),
			'exact_date'    => Ai1ec_Time_Utility::current_time(),
		);
		$args = wp_parse_args( $args, $defaults );

		// Localize requested date and get components.
		$local_date = Ai1ec_Time_Utility::gmt_to_local( $args['exact_date'] );
		$bits = Ai1ec_Time_Utility::gmgetdate( $local_date );
		// Align date to first day of the month, with month offset applied.
		$local_date = gmmktime(
			0, 0, 0,
			$bits['mon'] + $args['month_offset'], 1, $bits['year']
		);

		$days_events = $ai1ec_calendar_helper->get_events_for_month(
			$local_date,
			array(
				'cat_ids'  => $args['cat_ids'],
				'tag_ids'  => $args['tag_ids'],
				'post_ids' => $args['post_ids']
			)
		);

		$cell_array = $ai1ec_calendar_helper->get_month_cell_array(
			$local_date,
			$days_events
		);

		// Create pagination links.
		$pagination_links =
			$ai1ec_calendar_helper->get_month_pagination_links( $args );
		$pagination_links = $ai1ec_view_helper->get_theme_view(
			'pagination.php',
			array( 'links' => $pagination_links, 'data_type' => $args['data_type'] )
		);

		$title = Ai1ec_Time_Utility::date_i18n(
			'F Y', $local_date, true
		);
		$view_args = array(
			'title'                  => $title,
			'type'                   => 'month',
			'weekdays'               => $ai1ec_calendar_helper->get_weekdays(),
			'cell_array'             => $cell_array,
			'show_location_in_title' => $ai1ec_settings->show_location_in_title,
			'pagination_links'       => $pagination_links,
			'post_ids'               => join( ',', $args['post_ids'] ),
 	 		'data_type'              => $args['data_type'],
			'data_type_events'       => '',
		);
		if( $ai1ec_settings->ajaxify_events_in_web_widget ) {
			$view_args['data_type_events'] = $args['data_type'];
		}
		// Add navigation if requested.
		$view_args['navigation'] = $args['no_navigation'] ? '' :
			$ai1ec_view_helper->get_theme_view( 'navigation.php', $view_args );

		return apply_filters(
			'ai1ec_get_month_view',
			$ai1ec_view_helper->get_theme_view( 'month.php', $view_args ),
			$view_args
		);
	}

	/**
	 * Return the embedded week view of the calendar, optionally filtered by
	 * event categories and tags.
	 *
	 * @param array $args     associative array with any of these elements:
	 *   int week_offset   => specifies which week to display relative to the
	 *                        current week
	 *   array cat_ids     => restrict events returned to the given set of
	 *                        event category slugs
	 *   array tag_ids     => restrict events returned to the given set of
	 *                        event tag names
	 *   array post_ids    => restrict events returned to the given set of
	 *                        post IDs
	 *
	 * @return string	        returns string of view output
	 */
	function get_week_view( $args ) {
		global $ai1ec_view_helper,
		       $ai1ec_events_helper,
		       $ai1ec_calendar_helper,
		       $ai1ec_settings;

		$defaults = array(
			'week_offset'   => 0,
			'cat_ids'       => array(),
			'tag_ids'       => array(),
			'post_ids'      => array(),
			'exact_date'    => Ai1ec_Time_Utility::current_time(),
		);
		$args = wp_parse_args( $args, $defaults );

		// Localize requested date and get components.
		$local_date = Ai1ec_Time_Utility::gmt_to_local( $args['exact_date'] );
		$bits = Ai1ec_Time_Utility::gmgetdate( $local_date );
		// Day shift is initially the first day of the week according to settings.
		$day_shift = $ai1ec_events_helper->get_week_start_day_offset( $bits['wday'] );
		// Then apply week offset.
		$day_shift += $args['week_offset'] * 7;
		// Now align date to start of week.
		$local_date = gmmktime(
			0, 0, 0,
			$bits['mon'], $bits['mday'] + $day_shift, $bits['year']
		);

		$cell_array = $ai1ec_calendar_helper->get_week_cell_array(
			$local_date,
			array(
				'cat_ids'  => $args['cat_ids'],
				'tag_ids'  => $args['tag_ids'],
				'post_ids' => $args['post_ids'],
			)
		);

		// Create pagination links.
		$pagination_links =
			$ai1ec_calendar_helper->get_week_pagination_links( $args );
		$pagination_links = $ai1ec_view_helper->get_theme_view(
			'pagination.php',
			array( 'links' => $pagination_links, 'data_type' => $args['data_type'] )
		);

		// Translators: "%s" below represents the week's start date.
		$title = sprintf(
			__( 'Week of %s', AI1EC_PLUGIN_NAME ),
			Ai1ec_Time_Utility::date_i18n(
				__( 'F j', AI1EC_PLUGIN_NAME ), $local_date, true
			)
		);
		$time_format = Ai1ec_Meta::get_option(
			'time_format',
			__( 'g a', AI1EC_PLUGIN_NAME )
		);

		// Calculate today marker's position.
		$now = Ai1ec_Time_Utility::current_time();
		$now = Ai1ec_Time_Utility::gmt_to_local( $now );
		$now_text = $ai1ec_events_helper->get_short_time( $now, false );
		$now = Ai1ec_Time_Utility::gmgetdate( $now );
		$now = $now['hours'] * 60 + $now['minutes'];
		// Find out if the current week view contains "now" and thus should display
		// the "now" marker.
		$show_now = false;
		foreach ( $cell_array as $day ) {
			if ( $day['today'] ) {
				$show_now = true;
				break;
			}
		}

		$view_args = array(
			'title'                  => $title,
			'type'                   => 'week',
			'cell_array'             => $cell_array,
			'show_location_in_title' => $ai1ec_settings->show_location_in_title,
			'now_top'                => $now,
			'now_text'               => $now_text,
			'show_now'               => $show_now,
			'pagination_links'       => $pagination_links,
			'post_ids'               => join( ',', $args['post_ids'] ),
			'time_format'            => $time_format,
			'done_allday_label'      => false,
			'done_grid'              => false,
	 		'data_type'              => $args['data_type'],
			'data_type_events'       => '',
		);
		if( $ai1ec_settings->ajaxify_events_in_web_widget ) {
			$view_args['data_type_events'] = $args['data_type'];
		}
		// Add navigation if requested.
		$view_args['navigation'] = $args['no_navigation'] ? '' :
			$ai1ec_view_helper->get_theme_view( 'navigation.php', $view_args );

		return apply_filters(
			'ai1ec_get_week_view',
			$ai1ec_view_helper->get_theme_view( 'week.php', $view_args ),
			$view_args
		);
	}

	/**
	 * Return the embedded day view of the calendar, optionally filtered by
	 * event categories and tags.
	 *
	 * @param array $args     associative array with any of these elements:
	 *   int oneday_offset  => specifies which day to display relative to the
	 *                        current day
	 *   array cat_ids     => restrict events returned to the given set of
	 *                        event category slugs
	 *   array tag_ids     => restrict events returned to the given set of
	 *                        event tag names
	 *   array post_ids    => restrict events returned to the given set of
	 *                        post IDs
	 *
	 * @return string	        returns string of view output
	 */
	function get_oneday_view( $args ) {
		global $ai1ec_view_helper,
		       $ai1ec_events_helper,
		       $ai1ec_calendar_helper,
		       $ai1ec_settings;

		$defaults = array(
			'oneday_offset' => 0,
			'cat_ids'       => array(),
			'tag_ids'       => array(),
			'post_ids'      => array(),
			'exact_date'    => Ai1ec_Time_Utility::current_time(),
		);
		$args = wp_parse_args( $args, $defaults );

		// Localize requested date and get components.
    $local_date = Ai1ec_Time_Utility::gmt_to_local( $args['exact_date'] );
		$bits = Ai1ec_Time_Utility::gmgetdate( $local_date );
		// Apply day offset.
		$day_shift = 0 + $args['oneday_offset'];
		// Now align date to start of day (midnight).
		$local_date = gmmktime(
			0, 0, 0,
			$bits['mon'], $bits['mday'] + $day_shift, $bits['year']
		);

		$cell_array = $ai1ec_calendar_helper->get_oneday_cell_array(
			$local_date,
			array(
				'cat_ids'  => $args['cat_ids'],
				'tag_ids'  => $args['tag_ids'],
				'post_ids' => $args['post_ids']
			)
		);

		// Create pagination links.
		$pagination_links =
			$ai1ec_calendar_helper->get_oneday_pagination_links( $args );
		$pagination_links = $ai1ec_view_helper->get_theme_view(
			'pagination.php',
			array( 'links' => $pagination_links, 'data_type' => $args['data_type'] )
		);

		$date_format = Ai1ec_Meta::get_option( 'date_format', 'l, M j, Y' );
		$title = Ai1ec_Time_Utility::date_i18n(
			$date_format, $local_date, true
		);
		$time_format = Ai1ec_Meta::get_option( 'time_format', 'g a' );

		// Calculate today marker's position.
		$now = Ai1ec_Time_Utility::current_time();
		$now = Ai1ec_Time_Utility::gmt_to_local( $now );
		$now_text = $ai1ec_events_helper->get_short_time( $now, false );
		$now = Ai1ec_Time_Utility::gmgetdate( $now );
		$now = $now['hours'] * 60 + $now['minutes'];

		$view_args = array(
			'title'                  => $title,
			'type'                   => 'oneday',
			'cell_array'             => $cell_array,
			'show_location_in_title' => $ai1ec_settings->show_location_in_title,
			'now_top'                => $now,
			'now_text'               => $now_text,
			'pagination_links'       => $pagination_links,
			'post_ids'               => join( ',', $args['post_ids'] ),
			'time_format'            => $time_format,
			'done_allday_label'      => false,
			'done_grid'              => false,
 	 		'data_type'              => $args['data_type'],
			'data_type_events'       => '',
		);
		if( $ai1ec_settings->ajaxify_events_in_web_widget ) {
			$view_args['data_type_events'] = $args['data_type'];
		}
		// Add navigation if requested.
		$view_args['navigation'] = $args['no_navigation'] ? '' :
			$ai1ec_view_helper->get_theme_view( 'navigation.php', $view_args );

		return apply_filters(
			'ai1ec_get_oneday_view',
			$ai1ec_view_helper->get_theme_view( 'oneday.php', $view_args ),
			$view_args
		);
	}

	/**
	 * Return the embedded agenda view of the calendar, optionally filtered by
	 * event categories and tags.
	 *
	 * @param array $args     associative array with any of these elements:
	 *   int page_offset   => specifies which page to display relative to today's page
	 *   int time_limit    => specifies upper/lower (depending on direction) time limit
	 *   array categories  => restrict events returned to the given set of
	 *                        event category slugs
	 *   array tags        => restrict events returned to the given set of
	 *                        event tag names
	 *
	 * @return string	        returns string of view output
	 */
	function get_agenda_view( $args ) {
		return $this->get_agenda_like_view( 'agenda', $args );
	}

	/**
	 *
	 * @param array $args
	 * @param string $param
	 * @param mixed $default The default value of $param as set in Ai1ec_Abstract_Query::add_rule()
	 */
	private function set_arg_param_if_set_in_request( array $args, $param, $default ) {
		$exact_date = $this->request->get( $param );
		if( $default !== $exact_date ) {
			$args[$param] = $exact_date;
		}
		return $args;
	}

	/**
	 * load_css function
	 *
	 * Enqueue any CSS files required by the calendar views, as well as embeds any
	 * CSS rules necessary for calendar container replacement.
	 *
	 * @return void
	 */
	function load_css() {
		global $ai1ec_settings, $ai1ec_view_helper;

		if( $ai1ec_settings->calendar_css_selector )
			add_action( 'wp_head', array( &$this, 'selector_css' ) );
	}

	/**
	 * selector_css function
	 *
	 * Inserts dynamic CSS rules into <head> section of page to replace
	 * desired CSS selector with calendar.
	 */
	function selector_css() {
		global $ai1ec_view_helper, $ai1ec_settings;

		$ai1ec_view_helper->display_admin_css(
			'selector.css',
			array( 'selector' => $ai1ec_settings->calendar_css_selector )
		);
	}

	/**
	 * Returns the comma-separated list of category IDs that the calendar page
	 * was requested to be prefiltered by.
	 *
	 * @return string
	 */
	function get_requested_categories() {
		return $this->request['ai1ec_cat_ids'];
	}
}
// END class
