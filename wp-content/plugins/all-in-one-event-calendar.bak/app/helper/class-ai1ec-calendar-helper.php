<?php

/**
 * Helper class for calendars.
 *
 * @author     Timely Network Inc
 * @since      2011.07.13
 *
 * @package    AllInOneEventCalendar
 * @subpackage AllInOneEventCalendar.App.Helper
 */
class Ai1ec_Calendar_Helper {
	/**
	 * _instance class variable
	 *
	 * Class instance
	 *
	 * @var null | object
	 */
	private static $_instance = NULL;

	/**
	 * Constructor
	 *
	 * Default constructor
	 */
	private function __construct() {
	}

	/**
	 * get_instance function
	 *
	 * Return singleton instance
	 *
	 * @return object
	 */
	static function get_instance() {
		if( self::$_instance === NULL ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * get_events_for_month function
	 *
	 * Return an array of all dates for the given month as an associative
	 * array, with each element's value being another array of event objects
	 * representing the events occuring on that date.
	 *
	 * @param int $time         the UNIX timestamp of a date within the desired month
	 * @param array $filter     Array of filters for the events returned:
	 *                          ['cat_ids']   => non-associatative array of category IDs
	 *                          ['tag_ids']   => non-associatative array of tag IDs
	 *                          ['post_ids']  => non-associatative array of post IDs
	 *
	 * @return array            array of arrays as per function's description
	 */
	function get_events_for_month( $time, $filter = array() ) {
		global $ai1ec_events_helper;

		$bits     = $ai1ec_events_helper->gmgetdate( $time );
		$last_day = gmdate( 't', $time );

		$day_entry = array(
			'multi'  => array(),
			'allday' => array(),
			'other'  => array(),
		);
		$days_events = array_fill(
			1,
			$last_day,
			$day_entry
		);
		unset( $day_entry );

		$start_time = gmmktime(
			0,
			0,
			0,
			$bits['mon'],
			1,
			$bits['year']
		);
		$end_time   = gmmktime(
			0,
			0,
			0,
			$bits['mon'],
			$last_day + 1,
			$bits['year']
		);

		$month_events = $this->get_events_between(
			$start_time,
			$end_time,
			$filter,
			true
		);

		foreach ( $month_events as $event ) {
			$event_start = $ai1ec_events_helper->gmt_to_local( $event->start );
			$event_end   = $ai1ec_events_helper->gmt_to_local( $event->end );

			/**
			 * REASONING: we assume, that event spans multiple periods, one of
			 * which happens to be current (month). Thus we mark, that current
			 * event starts at the very first day of current month and further
			 * we will mark it as having truncated beginning (unless it is not
			 * overlapping period boundaries).
			 * Although, if event starts after the first second of this period
			 * it's start day will be decoded as time 'j' format (`int`-casted
			 * to increase map access time), of it's actual start time.
			 */
			$day = 1;
			if ( $event_start > $start_time ) {
				$day = (int)gmdate( 'j', $event_start );
			}

			// Set multiday properties. TODO: Should these be made event object
			// properties? They probably shouldn't be saved to the DB, so I'm
			// not sure. Just creating properties dynamically for now.
			if ( $event_start < $start_time ) {
				$event->start_truncated = true;
			}
			if ( $event_end >= $end_time ) {
				$event->end_truncated = true;
			}

			// Categorize event.
			$priority = 'other';
			if ( $event->allday ) {
				$priority = 'allday';
			} elseif ( $event->get_multiday() ) {
				$priority = 'multi';
			}
			$days_events[$day][$priority][] = $event;
		}

		for ( $day = 1; $day <= $last_day; $day++ ) {
			$days_events[$day] = array_merge(
				$days_events[$day]['multi'],
				$days_events[$day]['allday'],
				$days_events[$day]['other']
			);
		}

		return apply_filters(
			'ai1ec_get_events_for_month',
			$days_events,
			$time,
			$filter
		);
	}

	/**
	 * get_month_cell_array function
	 *
	 * Return an array of weeks, each containing an array of days, each
	 * containing the date for the day ['date'] (if inside the month) and
	 * the events ['events'] (if any) for the day, and a boolean ['today']
	 * indicating whether that day is today.
	 *
	 * @param int $timestamp	    UNIX timestamp of the 1st day of the desired
	 *                            month to display
	 * @param array $days_events  list of events for each day of the month in
	 *                            the format returned by get_events_for_month()
	 *
	 * @return void
	 */
	function get_month_cell_array( $timestamp, $days_events ) {
		global $ai1ec_settings, $ai1ec_events_helper;

		// Decompose date into components, used for calculations below
		$bits  = $ai1ec_events_helper->gmgetdate( $timestamp );
		$today = $ai1ec_events_helper->gmgetdate(
			$ai1ec_events_helper->gmt_to_local(
				Ai1ec_Time_Utility::current_time()
			)
		);	// Used to flag today's cell

		// Figure out index of first table cell
		$first_cell_index = gmdate( 'w', $timestamp );
		// Modify weekday based on start of week setting
		$first_cell_index = ( 7 + $first_cell_index - $ai1ec_settings->week_start_day ) % 7;

		// Get the last day of the month
		$last_day = gmdate( 't', $timestamp );
		$last_timestamp = gmmktime( 0, 0, 0, $bits['mon'], $last_day, $bits['year'] );
		// Figure out index of last table cell
		$last_cell_index = gmdate( 'w', $last_timestamp );
		// Modify weekday based on start of week setting
		$last_cell_index = ( 7 + $last_cell_index - $ai1ec_settings->week_start_day ) % 7;

		$weeks = array();
		$week = 0;
		$weeks[$week] = array();

		// Insert any needed blank cells into first week
		for( $i = 0; $i < $first_cell_index; $i++ ) {
			$weeks[$week][] = array(
				'date'       => null,
				'events'     => array(),
				'date_link'  => null
			);
		}

		// Insert each month's day and associated events
		for( $i = 1; $i <= $last_day; $i++ ) {
			$exact_date = Ai1ec_Time_Utility::format_date_for_url(
				gmmktime( 0, 0, 0, $bits['mon'], $i, $bits['year'] ),
				$ai1ec_settings->input_date_format
			);
			$weeks[$week][] = array(
				'date' => $i,
				'date_link' => $this->create_link_for_day_view( $exact_date ),
				'today' =>
					$bits['year'] == $today['year'] &&
					$bits['mon']  == $today['mon'] &&
					$i            == $today['mday'],
				'events' => $days_events[$i]
			);
			// If reached the end of the week, increment week
			if( count( $weeks[$week] ) == 7 )
				$week++;
		}

		// Insert any needed blank cells into last week
		for( $i = $last_cell_index + 1; $i < 7; $i++ ) {
			$weeks[$week][] = array( 'date' => null, 'events' => array() );
		}

		return $weeks;
	}

	/**
	 *
	 * @param string $exact_date
	 */
	private function create_link_for_day_view( $exact_date ) {
		$href = Ai1ec_View_Factory::create_href_helper_instance( array(
			"action" => "oneday",
			"exact_date" => $exact_date,
		) );
		return $href->generate_href();
	}

	/**
	 * get_week_cell_array function
	 *
	 * Return an associative array of weekdays, indexed by the day's date,
	 * starting the day given by $timestamp, each element an associative array
	 * containing three elements:
	 *   ['today']     => whether the day is today
	 *   ['allday']    => non-associative ordered array of events that are all-day
	 *   ['notallday'] => non-associative ordered array of non-all-day events to
	 *                    display for that day, each element another associative
	 *                    array like so:
	 *     ['top']       => how many minutes offset from the start of the day
	 *     ['height']    => how many minutes this event spans
	 *     ['indent']    => how much to indent this event to accommodate multiple
	 *                      events occurring at the same time (0, 1, 2, etc., to
	 *                      be multiplied by whatever desired px/em amount)
	 *     ['event']     => event data object
	 *
	 * @param int $timestamp    the UNIX timestamp of the first day of the week
	 * @param array $filter     Array of filters for the events returned:
	 *                          ['cat_ids']   => non-associatative array of category IDs
	 *                          ['tag_ids']   => non-associatative array of tag IDs
	 *                          ['post_ids']  => non-associatative array of post IDs
	 *
	 * @return array            array of arrays as per function description
	 */
	function get_week_cell_array( $timestamp, $filter = array() ) {
		global $ai1ec_events_helper, $ai1ec_settings;

		// Decompose given date and current time into components, used below
		$bits = $ai1ec_events_helper->gmgetdate( $timestamp );
		$now  = $ai1ec_events_helper->gmgetdate(
			$ai1ec_events_helper->gmt_to_local(
				Ai1ec_Time_Utility::current_time()
			)
		);

		// Do one SQL query to find all events for the week, including spanning
		$week_events = $this->get_events_between(
			$timestamp,
			gmmktime( 0, 0, 0, $bits['mon'], $bits['mday'] + 7, $bits['year'] ),
			$filter,
			true );

		// Split up events on a per-day basis
		$all_events = array();
		foreach ( $week_events as $evt ) {
			$evt_start = $ai1ec_events_helper->gmt_to_local( $evt->start );
			$evt_end = $ai1ec_events_helper->gmt_to_local( $evt->end );

			// Iterate through each day of the week and generate new event object
			// based on this one for each day that it spans
			for ( $day = $bits['mday']; $day < $bits['mday'] + 7; $day++ ) {
				$day_start = gmmktime( 0, 0, 0, $bits['mon'], $day, $bits['year'] );
				$day_end = gmmktime( 0, 0, 0, $bits['mon'], $day + 1, $bits['year'] );

				// If event falls on this day, make a copy.
				if ( $evt_end > $day_start && $evt_start < $day_end ) {
					$_evt = clone $evt;
					if ( $evt_start < $day_start ) {
						// If event starts before this day, adjust copy's start time
						$_evt->start = $ai1ec_events_helper->local_to_gmt( $day_start );
						$_evt->start_truncated = true;
					}
					if ( $evt_end > $day_end ) {
						// If event ends after this day, adjust copy's end time
						$_evt->end = $ai1ec_events_helper->local_to_gmt( $day_end );
						$_evt->end_truncated = true;
					}

					// Store reference to original, unmodified event, required by view.
					$_evt->_orig = $evt;

					// Place copy of event in appropriate category
					if ( $_evt->allday ) {
						$all_events[$day_start]['allday'][] = $_evt;
					} else {
						$all_events[$day_start]['notallday'][] = $_evt;
					}
				}
			}
		}

		// This will store the returned array
		$days = array();
		// =========================================
		// = Iterate through each date of the week =
		// =========================================
		for ( $day = $bits['mday']; $day < $bits['mday'] + 7; $day++ ) {
			$day_date = gmmktime( 0, 0, 0, $bits['mon'], $day, $bits['year'] );
			// Re-fetch date bits, since $bits['mday'] + 7 might be in the next month
			$day_bits = $ai1ec_events_helper->gmgetdate( $day_date );
			$exact_date = Ai1ec_Time_Utility::format_date_for_url(
				$day_date,
				$ai1ec_settings->input_date_format
			);
			$href_for_date = $this->create_link_for_day_view( $exact_date );

			// Initialize empty arrays for this day if no events to minimize warnings
			if ( ! isset( $all_events[$day_date]['allday'] ) ) $all_events[$day_date]['allday'] = array();
			if ( ! isset( $all_events[$day_date]['notallday'] ) ) $all_events[$day_date]['notallday'] = array();

			$notallday = array();
			$evt_stack = array( 0 ); // Stack to keep track of indentation
			foreach ( $all_events[$day_date]['notallday'] as $evt ) {
				$start_bits = $ai1ec_events_helper->gmgetdate( $ai1ec_events_helper->gmt_to_local( $evt->start ) );

				// Calculate top and bottom edges of current event
				$top = $start_bits['hours'] * 60 + $start_bits['minutes'];
				$bottom = min( $top + $evt->getDuration() / 60, 1440 );

				// While there's more than one event in the stack and this event's top
				// position is beyond the last event's bottom, pop the stack
				while ( count( $evt_stack ) > 1 && $top >= end( $evt_stack ) ) {
					array_pop( $evt_stack );
				}
				// Indentation is number of stacked events minus 1
				$indent = count( $evt_stack ) - 1;
				// Push this event onto the top of the stack
				array_push( $evt_stack, $bottom );

				$notallday[] = array(
					'top'    => $top,
					'height' => $bottom - $top,
					'indent' => $indent,
					'event'  => $evt,
				);
			}

			$days[$day_date] = array(
				'today'     =>
					$day_bits['year'] == $now['year'] &&
					$day_bits['mon']  == $now['mon'] &&
					$day_bits['mday'] == $now['mday'],
				'allday'    => $all_events[$day_date]['allday'],
				'notallday' => $notallday,
				'href'      => $href_for_date,
			);
		}

		return apply_filters( 'ai1ec_get_week_cell_array', $days, $timestamp, $filter );
	}

	/**
	 * get_oneday_cell_array function
	 *
	 * Return an associative array of weekdays, indexed by the day's date,
	 * starting the day given by $timestamp, each element an associative array
	 * containing three elements:
	 *   ['today']     => whether the day is today
	 *   ['allday']    => non-associative ordered array of events that are all-day
	 *   ['notallday'] => non-associative ordered array of non-all-day events to
	 *                    display for that day, each element another associative
	 *                    array like so:
	 *     ['top']       => how many minutes offset from the start of the day
	 *     ['height']    => how many minutes this event spans
	 *     ['indent']    => how much to indent this event to accommodate multiple
	 *                      events occurring at the same time (0, 1, 2, etc., to
	 *                      be multiplied by whatever desired px/em amount)
	 *     ['event']     => event data object
	 *
	 * @param int $timestamp    the UNIX timestamp of the first day of the week
	 * @param array $filter     Array of filters for the events returned:
	 *                          ['cat_ids']   => non-associatative array of category IDs
	 *                          ['tag_ids']   => non-associatative array of tag IDs
	 *                          ['post_ids']  => non-associatative array of post IDs
	 *
	 * @return array            array of arrays as per function description
	 */
	function get_oneday_cell_array( $timestamp, $filter = array() ) {
		global $ai1ec_events_helper, $ai1ec_settings;

		// Decompose given date and current time into components, used below
		$bits = $ai1ec_events_helper->gmgetdate( $timestamp );
		$now  = $ai1ec_events_helper->gmgetdate(
			$ai1ec_events_helper->gmt_to_local(
				Ai1ec_Time_Utility::current_time()
			)
		);
		$day_events = $this->get_events_between( $timestamp, gmmktime( 0, 0, 0, $bits['mon'], $bits['mday'] + 1, $bits['year'] ), $filter, true );

		// Split up events on a per-day basis
		$all_events = array();

		foreach ( $day_events as $evt ) {
			$evt_start = $ai1ec_events_helper->gmt_to_local( $evt->start );
			$evt_end   = $ai1ec_events_helper->gmt_to_local( $evt->end );

			// generate new event object
			// based on this one day
			$day_start = gmmktime( 0, 0, 0, $bits['mon'], $bits['mday'], $bits['year'] );
			$day_end   = gmmktime( 0, 0, 0, $bits['mon'], $bits['mday']+1, $bits['year'] );

			// If event falls on this day, make a copy.
			if ( $evt_end > $day_start && $evt_start < $day_end ) {
				$_evt = clone $evt;
				if ( $evt_start < $day_start ) {
					// If event starts before this day, adjust copy's start time
					$_evt->start = $ai1ec_events_helper->local_to_gmt( $day_start );
					$_evt->start_truncated = true;
				}
				if ( $evt_end > $day_end ) {
					// If event ends after this day, adjust copy's end time
					$_evt->end = $ai1ec_events_helper->local_to_gmt( $day_end );
					$_evt->end_truncated = true;
				}

				// Store reference to original, unmodified event, required by view.
				$_evt->_orig = $evt;

				// Place copy of event in appropriate category
				if ( $_evt->allday ) {
					$all_events[$day_start]['allday'][] = $_evt;
				} else {
					$all_events[$day_start]['notallday'][] = $_evt;
				}
			}
		}

		// This will store the returned array
		$days = array();
		$day = $bits['mday'];

		$day_date = gmmktime( 0, 0, 0, $bits['mon'], $day, $bits['year'] );
		// Re-fetch date bits, since $bits['mday'] + 1 might be in the next month
		$day_bits = $ai1ec_events_helper->gmgetdate( $day_date );

		// Initialize empty arrays for this day if no events to minimize warnings
		if ( ! isset( $all_events[$day_date]['allday'] ) ) $all_events[$day_date]['allday'] = array();
		if ( ! isset( $all_events[$day_date]['notallday'] ) ) $all_events[$day_date]['notallday'] = array();

		$notallday = array();
		$evt_stack = array( 0 ); // Stack to keep track of indentation
		foreach ( $all_events[$day_date]['notallday'] as $evt ) {
			$start_bits = $ai1ec_events_helper->gmgetdate( $ai1ec_events_helper->gmt_to_local( $evt->start ) );

			// Calculate top and bottom edges of current event
			$top = $start_bits['hours'] * 60 + $start_bits['minutes'];
			$bottom = min( $top + $evt->getDuration() / 60, 1440 );

			// While there's more than one event in the stack and this event's top
			// position is beyond the last event's bottom, pop the stack
			while ( count( $evt_stack ) > 1 && $top >= end( $evt_stack ) ) {
				array_pop( $evt_stack );
			}
			// Indentation is number of stacked events minus 1
			$indent = count( $evt_stack ) - 1;
			// Push this event onto the top of the stack
			array_push( $evt_stack, $bottom );

			$notallday[] = array(
				'top'    => $top,
				'height' => $bottom - $top,
				'indent' => $indent,
				'event'  => $evt,
			);
		}

		$days[$day_date] = array(
			'today'     =>
				$day_bits['year'] == $now['year'] &&
				$day_bits['mon']  == $now['mon'] &&
				$day_bits['mday'] == $now['mday'],
			'allday'    => $all_events[$day_date]['allday'],
			'notallday' => $notallday,
		);

		return apply_filters( 'ai1ec_get_oneday_cell_array', $days, $timestamp, $filter );
	}

	/**
	 * get_events_between function
	 *
	 * Return all events starting after the given start time and before the
	 * given end time that the currently logged in user has permission to view.
	 * If $spanning is true, then also include events that span this
	 * period. All-day events are returned first.
	 *
	 * @param int $start_time   limit to events starting after this (local) UNIX time
	 * @param int $end_time     limit to events starting before this (local) UNIX time
	 * @param array $filter     Array of filters for the events returned:
	 *                          ['cat_ids']   => non-associatative array of category IDs
	 *                          ['tag_ids']   => non-associatative array of tag IDs
	 *                          ['post_ids']  => non-associatative array of post IDs
	 * @param bool $spanning    also include events that span this period
	 *
	 * @return array            list of matching event objects
	 */
	function get_events_between( $start_time, $end_time, $filter, $spanning = false ) {
		global $wpdb, $ai1ec_events_helper, $ai1ec_localization_helper;

		// Convert timestamps to MySQL format in GMT time
		$start_time = $ai1ec_events_helper->local_to_gmt( $start_time );
		$end_time = $ai1ec_events_helper->local_to_gmt( $end_time );

		// Query arguments
		$args = array(
			Ai1ec_Time_Utility::to_mysql_date( $start_time ),
			Ai1ec_Time_Utility::to_mysql_date( $end_time )
		);

		// Get post status Where snippet and associated SQL arguments
		$this->_get_post_status_sql( $post_status_where, $args );

		// Get the Join (filter_join) and Where (filter_where) statements based on
		// $filter elements specified
		$this->_get_filter_sql( $filter );

		$wpml_join_particle  = $ai1ec_localization_helper
			->get_wpml_table_join( 'p.ID' );

		$wpml_where_particle = $ai1ec_localization_helper
			->get_wpml_table_where();

		$query = $wpdb->prepare(
			"SELECT DISTINCT p.*, e.post_id, i.id AS instance_id, " .
			"i.start AS start, " .
			"i.end end, " .
			// Treat event instances that span 24 hours as all-day
			"IF( e.allday, e.allday, i.end = DATE_ADD( i.start, INTERVAL 1 DAY ) ) AS allday, " .
			"e.recurrence_rules, e.exception_rules, e.recurrence_dates, e.exception_dates, " .
			"e.venue, e.country, e.address, e.city, e.province, e.postal_code, " .
			"e.show_map, e.contact_name, e.contact_phone, e.contact_email, e.cost, " .
			"e.ical_feed_url, e.ical_source_url, e.ical_organizer, e.ical_contact, e.ical_uid " .
			"FROM {$wpdb->prefix}ai1ec_events e " .
				"INNER JOIN $wpdb->posts p ON p.ID = e.post_id " .
				$wpml_join_particle .
				"INNER JOIN {$wpdb->prefix}ai1ec_event_instances i ON e.post_id = i.post_id " .
				$filter['filter_join'] .
			"WHERE post_type = '" . AI1EC_POST_TYPE . "' " .
			$wpml_where_particle .
			"AND " .
				( $spanning ? "i.end > %s AND i.start < %s "
										: "i.start >= %s AND i.start < %s " ) .
			$filter['filter_where'] .
			$post_status_where .
			"ORDER BY allday DESC, i.start ASC, post_title ASC",
			$args );

		$events = $wpdb->get_results( $query, ARRAY_A );
		foreach ( $events as &$event ) {
			$event['start'] = Ai1ec_Time_Utility::from_mysql_date( $event['start'] );
			$event['end']   = Ai1ec_Time_Utility::from_mysql_date( $event['end'] );
			$event        = new Ai1ec_Event( $event );
		}

		return $events;
	}

	/**
	 * get_events_relative_to function
	 *
	 * Return all events starting after the given reference time, limiting the
	 * result set to a maximum of $limit items, offset by $page_offset. A
	 * negative $page_offset can be provided, which will return events *before*
	 * the reference time, as expected.
	 *
	 * @param int $time           limit to events starting after this (local) UNIX time
	 * @param int $limit          return a maximum of this number of items
	 * @param int $page_offset    offset the result set by $limit times this number
	 * @param array $filter       Array of filters for the events returned.
	 *		                        ['cat_ids']   => non-associatative array of category IDs
	 *                            ['tag_ids']   => non-associatative array of tag IDs
	 *                            ['post_ids']  => non-associatative array of post IDs
	 * @param int $last_day       Last day (time), that was displayed.
	 *                            NOTE FROM NICOLA: be careful, if you want a query with events
	 *                            that have a start date which is greater than today, pass 0 as
	 *                            this parameter. If you pass false ( or pass nothing ) you end up with a query
	 *                            with events that finish before today. I don't know the rationale
	 *                            behind this but that's how it works
	 *
	 * @return array              five-element array:
	 *                              ['events'] an array of matching event objects
	 *                              ['prev'] true if more previous events
	 *                              ['next'] true if more next events
	 *                              ['date_first'] UNIX timestamp (date part) of first event
	 *                              ['date_last'] UNIX timestamp (date part) of last event
	 */
	function get_events_relative_to(
		$time,
		$limit       = 0,
		$page_offset = 0,
		$filter      = array(),
		$last_day    = false
	) {
		global $wpdb,
		       $ai1ec_events_helper,
		       $ai1ec_localization_helper,
		       $ai1ec_settings;

		// Figure out what the beginning of the day is to properly query all-day
		// events; then convert to GMT time
		$bits = $ai1ec_events_helper->gmgetdate( $time );

		// Even if there ARE more than 5 times the limit results - we shall not
		// try to fetch and display these, as it would crash system
		$upper_boundary = $limit;
		if (
			$ai1ec_settings->agenda_include_entire_last_day &&
			( false !== $last_day )
		) {
			$upper_boundary *= 5;
		}

		// Convert timestamp to GMT time
		$time = $ai1ec_events_helper->local_to_gmt( $time );

		// Query arguments
		$args = array( Ai1ec_Time_Utility::to_mysql_date( $time ) );

		if( $page_offset >= 0 ) {
			$first_record = $page_offset * $limit;
		} else {
			$first_record = ( -$page_offset - 1 ) * $limit;
		}

		// Get post status Where snippet and associated SQL arguments
		$this->_get_post_status_sql( $post_status_where, $args );

		// Get the Join (filter_join) and Where (filter_where) statements based on
		// $filter elements specified
		$this->_get_filter_sql( $filter );

		$wpml_join_particle  = $ai1ec_localization_helper
			->get_wpml_table_join( 'p.ID' );

		$wpml_where_particle = $ai1ec_localization_helper
			->get_wpml_table_where();

		$filter_date_clause = ( $page_offset >= 0 )
			? 'i.end >= %s '
			: 'i.start < %s ';
		$order_direction    = ( $page_offset >= 0 ) ? 'ASC' : 'DESC';
		if ( false !== $last_day ) {
			if ( 0 == $last_day ) {
				$last_day = (int)$_SERVER['REQUEST_TIME'];
			}
			$filter_date_clause = ' i.start ';
			if ( $page_offset < 0 ) {
				$filter_date_clause .= '<';
				$order_direction     = 'DESC';
			} else {
				$filter_date_clause .= '>';
				$order_direction     = 'ASC';
			}
			$filter_date_clause .= ' %s ';
			$args[0]             = Ai1ec_Time_Utility::to_mysql_date( $last_day );
			$first_record        = 0;
		}

		$query = $wpdb->prepare(
					'SELECT DISTINCT SQL_CALC_FOUND_ROWS p.*, e.post_id, i.id AS instance_id, ' .
					'i.start AS start, ' .
					'i.end AS end, ' .
					// Treat event instances that span 24 hours as all-day
					'IF( e.allday, e.allday, i.end = DATE_ADD( i.start, INTERVAL 1 DAY ) ) AS allday, ' .
					'e.recurrence_rules, e.exception_rules, e.instant_event, e.recurrence_dates, e.exception_dates, ' .
					'e.venue, e.country, e.address, e.city, e.province, e.postal_code, ' .
					'e.show_map, e.contact_name, e.contact_phone, e.contact_email, e.cost, ' .
					'e.ical_feed_url, e.ical_source_url, e.ical_organizer, e.ical_contact, e.ical_uid ' .
					'FROM ' . $wpdb->prefix . 'ai1ec_events e ' .
						'INNER JOIN ' . $wpdb->posts . ' p ON e.post_id = p.ID ' .
						$wpml_join_particle .
						'INNER JOIN ' . $wpdb->prefix . 'ai1ec_event_instances i ON e.post_id = i.post_id ' .
						$filter['filter_join'] .
					"WHERE post_type = '" . AI1EC_POST_TYPE . "' " .
					'AND ' . $filter_date_clause .
					$wpml_where_particle .
					$filter['filter_where'] .
					$post_status_where .
					// Reverse order when viewing negative pages, to get correct set of
					// records. Then reverse results later to order them properly.
					'ORDER BY i.start ' . $order_direction .
						', post_title ' . $order_direction .
					' LIMIT ' . $first_record . ', ' . $upper_boundary,
					$args
		);

		$events = $wpdb->get_results( $query, ARRAY_A );

		// Limit the number of records to convert to data-object
		$events = $this->_limit_result_set(
			$events,
			$limit,
			( false !== $last_day )
		);

		// Reorder records if in negative page offset
		if( $page_offset < 0 ) {
			$events = array_reverse( $events );
		}

		$date_first = $date_last = NULL;

		foreach ( $events as &$event ) {
			$event['start'] = Ai1ec_Time_Utility::from_mysql_date( $event['start'] );
			$event['end']   = Ai1ec_Time_Utility::from_mysql_date( $event['end'] );
			if ( NULL === $date_first ) {
				$date_first = $event['start'];
			}
			$date_last = $event['start'];
			$event     = new Ai1ec_Event( $event );
		}

		// Find out if there are more records in the current nav direction
		$more = $wpdb->get_var( 'SELECT FOUND_ROWS()' ) > $first_record + $limit;

		// Navigating in the future
		if( $page_offset > 0 ) {
			$prev = true;
			$next = $more;
		}
		// Navigating in the past
		elseif( $page_offset < 0 ) {
			$prev = $more;
			$next = true;
		}
		// Navigating from the reference time
		else {
			$query = $wpdb->prepare(
				"SELECT COUNT(*) " .
				"FROM {$wpdb->prefix}ai1ec_events e " .
					"INNER JOIN {$wpdb->prefix}ai1ec_event_instances i ON e.post_id = i.post_id " .
					"INNER JOIN $wpdb->posts p ON e.post_id = p.ID " .
					$wpml_join_particle .
					$filter['filter_join'] .
				"WHERE post_type = '" . AI1EC_POST_TYPE . "' " .
				"AND i.start < %s " .
				$wpml_where_particle .
				$filter['filter_where'] .
				$post_status_where,
				$args );
			$prev = $wpdb->get_var( $query );
			$next = $more;
		}
		return array(
			'events'     => $events,
			'prev'       => $prev,
			'next'       => $next,
			'date_first' => $date_first,
			'date_last'  => $date_last,
		);
	}

	/**
	 * _limit_result_set function
	 *
	 * Slice given number of events from list, with exception when all
	 * events from last day shall be included.
	 *
	 * @param array $events   List of events to slice
	 * @param int   $limit    Number of events to slice-off
	 * @param bool  $last_day Set to true to include all events from last day ignoring {$limit}
	 *
	 * @return array Sliced events list
	 */
	protected function _limit_result_set(
		array $events,
		$limit,
		$last_day
	) {
		global $ai1ec_events_helper;
		$limited_events     = array();
		$start_day_previous = 0;
		foreach ( $events as $event ) {
			$start_day = date(
				'Y-m-d',
				Ai1ec_Time_Utility::from_mysql_date( $event['start'] )
			);
			--$limit; // $limit = $limit - 1;
			if ( $limit < 0 ) {
				if ( true === $last_day ) {
					if ( $start_day != $start_day_previous ) {
						break;
					}
				} else {
					break;
				}
			}
			$limited_events[]   = $event;
			$start_day_previous = $start_day;
		}
		return $limited_events;
	}

	/**
	 * Breaks down the given ordered array of event objects into dates, and
	 * outputs an ordered array of two-element associative arrays in the
	 * following format:
	 *	key: localized UNIX timestamp of date
	 *	value:
	 *		['events'] => two-element associatative array broken down thus:
	 *			['allday'] => all-day events occurring on this day
	 *			['notallday'] => all other events occurring on this day
	 *		['today'] => whether or not this date is today
	 *
	 * @param array                     $events Event results
	 * @param Ai1ec_Abstract_Query|null $query  Current calendar page request, if
	 *                                          any (null for widget)
	 *
	 * @return array
	 */
	function get_agenda_like_date_array(
		$events,
		Ai1ec_Abstract_Query $query = null
	) {
		global $ai1ec_events_helper, $ai1ec_settings;

		$dates = array();

		// Classify each event into a date/allday category
		foreach( $events as $event ) {
			if ( ! empty( $query ) ) {
				$event->set_request( $query );
			}
			$date = $ai1ec_events_helper->gmt_to_local( $event->start );
			$date = $ai1ec_events_helper->gmgetdate( $date );
			$timestamp = gmmktime( 0, 0, 0, $date['mon'], $date['mday'], $date['year'] );
			$exact_date = Ai1ec_Time_Utility::format_date_for_url(
				$timestamp,
				$ai1ec_settings->input_date_format
			);
			$href_for_date = $this->create_link_for_day_view( $exact_date );
			// Ensure all-day & non all-day categories are created in correct order.
			if ( ! isset( $dates[$timestamp]['events'] ) ) {
				$dates[$timestamp]['events'] = array(
					'allday'    => array(),
					'notallday' => array(),
				);
			}
			// Add the event.
			$category = $event->allday ? 'allday' : 'notallday';
			$dates[$timestamp]['events'][$category][] = $event;
			$dates[$timestamp]['href'] = $href_for_date;
		}
		// Flag today
		$today = $ai1ec_events_helper->gmt_to_local(
			Ai1ec_Time_Utility::current_time()
		);
		$today = $ai1ec_events_helper->gmgetdate( $today );
		$today = gmmktime( 0, 0, 0, $today['mon'], $today['mday'], $today['year'] );
		if( isset( $dates[$today] ) ) {
			$dates[$today]['today'] = true;
		}

		return $dates;
	}

	/**
	 * Returns the URL of the configured calendar page in the default view, with
	 * other optional arguments for the page.
	 *
	 * @param array       $args   Associative array of args for the calendar page.
	 *
	 * @return string The generated URL for the designated calendar page
	 */
	function get_calendar_url( $args = array() ) {
		$href = Ai1ec_View_Factory::create_href_helper_instance( $args );
		return $href->generate_href();
	}

	/**
	 * get_weekdays function
	 *
	 * Returns a list of abbreviated weekday names starting on the configured
	 * week start day setting.
	 *
	 * @return array
	 */
	function get_weekdays() {
		global $ai1ec_settings;
		static $weekdays;

		if( ! isset( $weekdays ) ) {
			$time = strtotime( 'next Sunday' );
			$time = strtotime( "+{$ai1ec_settings->week_start_day} days", $time );

			$weekdays = array();
			for( $i = 0; $i < 7; $i++ ) {
				$weekdays[] = Ai1ec_Time_Utility::date_i18n( 'D', $time, false );
				$time = strtotime( '+1 day', $time ); // Add a day
			}
		}
		return $weekdays;
	}

	/**
	 * Returns an associative array of two links for any agenda-like view of the
	 * calendar (posterboard, agenda, etc.):
	 *    previous page (if previous events exist),
	 *    next page (if next events exist).
	 * Each element is an associative array containing the link's enabled status
	 * ['enabled'], CSS class ['class'], text ['text'] and value to assign to
	 * link's href ['href'].
	 *
	 * @param array $args Current request arguments
	 *
	 * @param bool  $prev   Whether there are more events before the current page
	 * @param bool  $next   Whether there are more events after the current page
	 * @param int|null $date_first
	 * @param int|null $date_last
	 *
	 * @return array      Array of links
	 */
	function get_agenda_like_pagination_links(
		$args,
		$prev = false,
		$next = false,
		$date_first = null,
		$date_last  = null
	) {
		global $ai1ec_settings, $ai1ec_view_helper;

		$links = array();

		$args['page_offset'] = -1;
		$args['time_limit']  = $date_first - 1;
		$href = Ai1ec_View_Factory::create_href_helper_instance( $args );
		$links[] = array(
			'class'   => 'ai1ec-prev-page',
			'text'    => '<i class="icon-chevron-left"></i>',
			'href'    => $href->generate_href(),
			'enabled' => $prev,
		);

		// Minical datepicker.
		$links[] = Ai1ec_View_Factory::create_datepicker_link( $args, $date_first );

		$args['page_offset'] = 1;
		$args['time_limit']  = $date_last + 1;
		$href = Ai1ec_View_Factory::create_href_helper_instance( $args );
		$links[] = array(
			'class'   => 'ai1ec-next-page',
			'text'    => '<i class="icon-chevron-right"></i>',
			'href'    => $href->generate_href(),
			'enabled' => $next,
		);

		return $links;
	}

	/**
	 * Returns a non-associative array of four links for the month view of the
	 * calendar:
	 *    previous year, previous month, next month, and next year.
	 * Each element is an associative array containing the link's enabled status
	 * ['enabled'], CSS class ['class'], text ['text'] and value to assign to
	 * link's href ['href'].
	 *
	 * @param array $args	Current request arguments
	 *
	 * @return array      Array of links
	 */
	function get_month_pagination_links( $args ) {
		$links = array();

		$local_date = Ai1ec_Time_Utility::gmt_to_local( $args['exact_date'] );
		$bits = Ai1ec_Time_Utility::gmgetdate( $local_date );

		// =================
		// = Previous year =
		// =================
		// Align date to first of month, month offset applied, 1 year behind.
		$local_date = gmmktime(
			0, 0, 0,
			$bits['mon'] + $args['month_offset'], 1, $bits['year'] - 1
		);
		$args['exact_date'] = Ai1ec_Time_Utility::local_to_gmt( $local_date );
		$href = Ai1ec_View_Factory::create_href_helper_instance( $args );
		$links[] = array(
			'enabled' => true,
			'class'=> 'ai1ec-prev-year',
			'text' =>
				'<i class="icon-chevron-left"></i><i class="icon-chevron-left"></i> ' .
				Ai1ec_Time_Utility::date_i18n(
					'Y',
					$local_date,
					true
				),
			'href' => $href->generate_href(),
		);

		// ==================
		// = Previous month =
		// ==================
		// Align date to first of month, month offset applied, 1 month behind.
		$local_date = gmmktime(
			0, 0, 0,
			$bits['mon'] + $args['month_offset'] - 1, 1, $bits['year']
		);
		$args['exact_date'] = Ai1ec_Time_Utility::local_to_gmt( $local_date );
		$href = Ai1ec_View_Factory::create_href_helper_instance( $args );
		$links[] = array(
			'enabled' => true,
			'class'=> 'ai1ec-prev-month',
			'text' => '<i class="icon-chevron-left"></i> ' .
				Ai1ec_Time_Utility::date_i18n(
					'M',
					$local_date,
					true
				),
			'href' => $href->generate_href(),
		);

		// ======================
		// = Minical datepicker =
		// ======================
		// Align date to first of month, month offset applied.
		$local_date = gmmktime(
			0, 0, 0,
			$bits['mon'] + $args['month_offset'], 1, $bits['year']
		);
		$args['exact_date'] = Ai1ec_Time_Utility::local_to_gmt( $local_date );
		$links[] = Ai1ec_View_Factory::create_datepicker_link(
			$args,
			$args['exact_date']
		);

		// ==============
		// = Next month =
		// ==============
		// Align date to first of month, month offset applied, 1 month ahead.
		$local_date = gmmktime(
			0, 0, 0,
			$bits['mon'] + $args['month_offset'] + 1, 1, $bits['year']
		);
		$args['exact_date'] = Ai1ec_Time_Utility::local_to_gmt( $local_date );
		$href = Ai1ec_View_Factory::create_href_helper_instance( $args );
		$links[] = array(
			'enabled' => true,
			'class'=> 'ai1ec-next-month',
			'text' =>
				Ai1ec_Time_Utility::date_i18n(
					'M',
					$local_date,
					true
				) . ' <i class="icon-chevron-right"></i>',
			'href' => $href->generate_href(),
		);

		// =============
		// = Next year =
		// =============
		// Align date to first of month, month offset applied, 1 year ahead.
		$local_date = gmmktime(
			0, 0, 0,
			$bits['mon'] + $args['month_offset'], 1, $bits['year'] + 1
		);
		$args['exact_date'] = Ai1ec_Time_Utility::local_to_gmt( $local_date );
		$href = Ai1ec_View_Factory::create_href_helper_instance( $args );
		$links[] = array(
			'enabled' => true,
			'class'=> 'ai1ec-next-year',
			'text' =>
				Ai1ec_Time_Utility::date_i18n(
					'Y',
					$local_date,
					true
				) .
				' <i class="icon-chevron-right"></i><i class="icon-chevron-right"></i>',
			'href' => $href->generate_href(),
		);

		return $links;
	}

	/**
	 * Returns a non-associative array of two links for the week view of the
	 * calendar:
	 *    previous week, and next week.
	 * Each element is an associative array containing the link's enabled status
	 * ['enabled'], CSS class ['class'], text ['text'] and value to assign to
	 * link's href ['href'].
	 *
	 * @param array $args	Current request arguments
	 *
	 * @return array      Array of links
	 */
	function get_week_pagination_links( $args ) {
		$links = array();

		$orig_date = $args['exact_date'];
		$local_date = Ai1ec_Time_Utility::gmt_to_local( $args['exact_date'] );
		$bits = Ai1ec_Time_Utility::gmgetdate( $local_date );

		// =================
		// = Previous week =
		// =================
		$local_date = gmmktime(
			0, 0, 0,
			$bits['mon'], $bits['mday'] + $args['week_offset'] * 7 - 7, $bits['year']
		);
		$args['exact_date'] = Ai1ec_Time_Utility::local_to_gmt( $local_date );
		$href = Ai1ec_View_Factory::create_href_helper_instance( $args );
		$links[] = array(
			'enabled' => true,
			'class'=> 'ai1ec-prev-week',
			'text' => '<i class="icon icon-chevron-left"></i>',
			'href' => $href->generate_href(),
		);

		// ======================
		// = Minical datepicker =
		// ======================
		$args['exact_date'] = $orig_date;
		$links[] = Ai1ec_View_Factory::create_datepicker_link(
			$args,
			$args['exact_date']
		);

		// =============
		// = Next week =
		// =============
		$local_date = gmmktime(
			0, 0, 0,
			$bits['mon'], $bits['mday'] + $args['week_offset'] * 7 + 7, $bits['year']
		);
		$args['exact_date'] = Ai1ec_Time_Utility::local_to_gmt( $local_date );
		$href = Ai1ec_View_Factory::create_href_helper_instance( $args );
		$links[] = array(
			'enabled' => true,
			'class'=> 'ai1ec-next-week',
			'text' => '<i class="icon icon-chevron-right"></i>',
			'href' => $href->generate_href(),
		);

		return $links;
	}

	/**
	 * Returns a non-associative array of four links for the day view of the
	 * calendar:
	 *    previous day, and next day.
	 * Each element is an associative array containing the link's enabled status
	 * ['enabled'], CSS class ['class'], text ['text'] and value to assign to
	 * link's href ['href'].
	 *
	 * @param array $args	Current request arguments
	 *
	 * @return array      Array of links
	 */
	function get_oneday_pagination_links( $args ) {
		$links = array();

		$orig_date = $args['exact_date'];
		$local_date = Ai1ec_Time_Utility::gmt_to_local( $args['exact_date'] );
		$bits = Ai1ec_Time_Utility::gmgetdate( $local_date );

		// ================
		// = Previous day =
		// ================
		$local_date = gmmktime(
			0, 0, 0,
			$bits['mon'], $bits['mday'] + $args['oneday_offset'] - 1, $bits['year']
		);
		$args['exact_date'] = Ai1ec_Time_Utility::local_to_gmt( $local_date );
		$href = Ai1ec_View_Factory::create_href_helper_instance( $args );
		$links[] = array(
			'enabled' => true,
			'class'=> 'ai1ec-prev-day',
			'text' => '<i class="icon-chevron-left"></i>',
			'href' => $href->generate_href(),
		);

		// ======================
		// = Minical datepicker =
		// ======================
		$args['exact_date'] = $orig_date;
		$links[] = Ai1ec_View_Factory::create_datepicker_link(
			$args,
			$args['exact_date']
		);

		// =============
		// = Next week =
		// =============
		$local_date = gmmktime(
			0, 0, 0,
			$bits['mon'], $bits['mday'] + $args['oneday_offset'] + 1, $bits['year']
		);
		$args['exact_date'] = Ai1ec_Time_Utility::local_to_gmt( $local_date );
		$href = Ai1ec_View_Factory::create_href_helper_instance( $args );
		$links[] = array(
			'enabled' => true,
			'class'=> 'ai1ec-next-day',
			'text' => '<i class="icon-chevron-right"></i>',
			'href' => $href->generate_href(),
		);

		return $links;
	}

	/**
	 * _get_post_status_sql function
	 *
	 * Returns SQL snippet for properly matching event posts, as well as array
	 * of arguments to pass to $wpdb->prepare, in function argument references.
	 * Nothing is returned by the function.
	 *
	 * @param string &$sql  The variable to store the SQL snippet into
	 * @param array  &$args The variable to store the SQL arguments into
	 *
	 * @return void
	 */
	function _get_post_status_sql( &$post_status_where = '', &$args ) {
		global $current_user;

		// Query the correct post status
		if( current_user_can( 'administrator' ) || current_user_can( 'editor' ) )
		{
			// User has privilege of seeing all published and private

			$post_status_where = "AND ( post_status = %s OR post_status = %s ) ";
			$args[]            = 'publish';
			$args[]            = 'private';
		}
		elseif( is_user_logged_in() )
		{
			// User has privilege of seeing all published and only their own private
			// posts.

			// get user info
			get_currentuserinfo();

			// include post_status = published
			//   OR
			// post_status = private AND post_author = userID
			$post_status_where =
				"AND ( " .
					"post_status = %s " .
					"OR ( post_status = %s AND post_author = %d ) " .
				") ";

			$args[] = 'publish';
			$args[] = 'private';
			$args[] = $current_user->ID;
		} else {
			// User can only see published posts.
			$post_status_where = "AND post_status = %s ";
			$args[]            = 'publish';
		}
	}

	/**
	 * _get_filter_sql function
	 *
	 * Takes an array of filtering options and turns it into JOIN and WHERE statements
	 * for running an SQL query limited to the specified options
	 *
	 * @param array &$filter      Array of filters for the events returned.
	 *		                        ['cat_ids']   => non-associatative array of category IDs
	 *		                        ['tag_ids']   => non-associatative array of tag IDs
	 *		                        ['post_ids']  => non-associatative array of event post IDs
	 *														This array is modified to have:
	 *                              ['filter_join']  the Join statements for the SQL
	 *                              ['filter_where'] the Where statements for the SQL
	 *
	 * @return void
	 */
	function _get_filter_sql( &$filter ) {
		global $wpdb;

		// Set up the filter join and where strings
		$filter['filter_join']  = '';
		$filter['filter_where'] = '';

		// By default open the Where with an AND ( .. ) to group all statements.
		// Later, set it to OR to join statements together.
		// TODO - make this cleaner by supporting the choice of AND/OR logic
		$where_logic = ' AND (';

		foreach( $filter as $filter_type => $filter_ids ) {
			// If no filter elements specified, don't do anything
			if( $filter_ids && is_array( $filter_ids ) ) {
				switch ( $filter_type ) {
					// Limit by Category IDs
					case 'cat_ids':
						$filter['filter_join']   .= " LEFT JOIN $wpdb->term_relationships AS trc ON e.post_id = trc.object_id ";
						$filter['filter_join']   .= " LEFT JOIN $wpdb->term_taxonomy ttc ON trc.term_taxonomy_id = ttc.term_taxonomy_id AND ttc.taxonomy = 'events_categories' ";
						$filter['filter_where']  .= $where_logic . " ttc.term_id IN ( " . join( ',', $filter_ids ) . " ) ";
						$where_logic = ' OR ';
						break;
					// Limit by Tag IDs
					case 'tag_ids':
						$filter['filter_join']   .= " LEFT JOIN $wpdb->term_relationships AS trt ON e.post_id = trt.object_id ";
						$filter['filter_join']   .= " LEFT JOIN $wpdb->term_taxonomy ttt ON trt.term_taxonomy_id = ttt.term_taxonomy_id AND ttt.taxonomy = 'events_tags' ";
						$filter['filter_where']  .= $where_logic . " ttt.term_id IN ( " . join( ',', $filter_ids ) . " ) ";
						$where_logic = ' OR ';
						break;
					// Limit by post IDs
					case 'post_ids':
						$filter['filter_where']  .= $where_logic . " e.post_id IN ( " . join( ',', $filter_ids ) . " ) ";
						$where_logic = ' OR ';
						break;
				}
			}
		}

		// Close the Where statement bracket if any Where statements were set
		if( $filter['filter_where'] != '' ) {
			$filter['filter_where'] .= ' ) ';
		}
	}

	/**
	 * This function generates the html for the view dropdowns
	 *
	 * @param array $view_args
	 */
	public function get_html_for_views_dropdown( array $view_args ) {
		global $ai1ec_view_helper,
		       $ai1ec_settings,
		       $ai1ec_app_helper;

		$available_views = array();
		foreach( $ai1ec_app_helper->view_names() as $key => $val ) {
			$view_enabled = 'view_' . $key . '_enabled';
			$values = array();
			$options = $view_args;
			if( $ai1ec_settings->$view_enabled ) {
				if( $key === 'posterboard' || $key === 'agenda' || $key === 'stream' ) {
					if( isset( $options['exact_date'] ) && ! isset( $options['time_limit'] ) ) {
						$options['time_limit'] = $options['exact_date'];
					}
					unset( $options['exact_date'] );
				} else {
					unset( $options['time_limit'] );
				}
				unset( $options['month_offset'] );
				unset( $options['week_offset'] );
				unset( $options['oneday_offset'] );
				$options['action'] = $key;
				$href = Ai1ec_View_Factory::create_href_helper_instance( $options );
				$values['desc'] = $val;
				$values['href'] = $href->generate_href();
				$available_views[$key] = $values;
			}
		};
		$args = array(
			'view_names'              => $ai1ec_app_helper->view_names(),
			'available_views'         => $available_views,
			'current_view'            => $view_args['action'],
			'data_type'               => $view_args['data_type'],
		);
		return $ai1ec_view_helper->get_theme_view( 'views_dropdown.php', $args );
	}

	/**
	 * Generates the HTML for a category selector.
	 *
	 * @param array $view_args        Arguments to the parent view
	 *
	 * @return string                 Markup for categories selector
	 */
	public function get_html_for_categories( $view_args ) {
		global $ai1ec_events_helper, $ai1ec_view_helper;

		// Get categories & tags. Add category color info to available categories.
		$categories = get_terms( 'events_categories', array( 'orderby' => 'name' ) );
		if( empty( $categories ) ) {
			return '';
		}

		foreach( $categories as &$cat ) {
			$cat->color = $ai1ec_events_helper->get_category_color_square( $cat->term_id );
			$href = Ai1ec_View_Factory::create_href_helper_instance( $view_args, 'category' );
			$href->set_term_id( $cat->term_id );
			$cat->href = $href->generate_href();
		}

		$href_for_clearing_filter =
			$this->generate_href_without_arguments( $view_args, array( 'cat_ids' ) );

		$args = array(
			"categories"       => $categories,
			"selected_cat_ids" => $view_args['cat_ids'],
			"data_type"        => $view_args['data_type'],
			"clear_filter"     => $href_for_clearing_filter,
		);
		return $ai1ec_view_helper->get_theme_view( "categories.php", $args );
	}

	/**
	 * Generates the HTML for a tag selector.
	 *
	 * @param array $view_args        Arguments to the parent view
	 *
	 * @return string                 Markup for categories selector
	 */
	public function get_html_for_tags( $view_args ) {
		global $ai1ec_view_helper;

		$tags = get_terms( 'events_tags', array( 'orderby' => 'name' ) );
		if( empty( $tags ) ) {
			return '';
		}

		foreach( $tags as &$tag ) {
			$href = Ai1ec_View_Factory::create_href_helper_instance( $view_args, 'tag' );
			$href->set_term_id( $tag->term_id );
			$tag->href = $href->generate_href();
		}

		$href_for_clearing_filter =
			$this->generate_href_without_arguments( $view_args, array( 'tag_ids' ) );

		$args = array(
			"tags"             => $tags,
			"selected_tag_ids" => $view_args['tag_ids'],
			"data_type"        => $view_args['data_type'],
			"clear_filter"     => $href_for_clearing_filter,
		);
		return $ai1ec_view_helper->get_theme_view( "tags.php", $args );
	}

	/**
	 * Returns a link to a calendar page without the given arguments; does not
	 * otherwise disturb current page state.
	 *
	 * @param array $args           Current arguments to the calendar
	 * @param array $args_to_remove Names of arguments to remove from current args
	 *
	 * @return string
	 */
	private function generate_href_without_arguments(
		array $args,
		array $args_to_remove
	) {
		$args_to_remove = array_flip( $args_to_remove );
		$args = array_diff_key( $args, $args_to_remove );
		$href = Ai1ec_View_Factory::create_href_helper_instance( $args );
		return $href->generate_href();
	}

	/**
	 * get_html_for_subscribe_buttons method
	 *
	 * Render the HTML for the `subscribe' buttons
	 *
	 * @param array $view_args Args to pass
	 *
	 * @return string Rendered HTML to include in output
	 */
	public function get_html_for_subscribe_buttons( array $view_args ) {
		global $ai1ec_view_helper, $ai1ec_localization_helper;
		$args = array(
			'url_args'    => '',
			'is_filtered' => false,
		);
		if ( ! empty( $view_args['cat_ids'] ) ) {
			$args['url_args'] .= '&ai1ec_cat_ids=' .
				implode( ',', $view_args['cat_ids'] );
			$args['is_filtered'] = true;
		}
		if ( ! empty( $view_args['tag_ids'] ) ) {
			$args['url_args']  .= '&ai1ec_tag_ids=' .
				implode( ',', $view_args['tag_ids'] );
			$args['is_filtered'] = true;
		}
		if ( ! empty( $view_args['post_ids'] ) ) {
			$args['url_args']  .= '&ai1ec_post_ids=' .
				implode( ',', $view_args['post_ids'] );
			$args['is_filtered'] = true;
		}
		if (
			NULL !== ( $use_lang = $ai1ec_localization_helper->get_language() )
		) {
			$args['url_args'] .= '&lang=' . $use_lang;
		}
		return $ai1ec_view_helper->get_theme_view(
			'subscribe-buttons.php',
			$args
		);
	}

	/**
	 * Returns HTML for front-end contribution buttons, including modal skeleton
	 * for front-end forms if requested.
	 *
	 * @return string  HTML markup
	 */
	public function get_html_for_contribution_buttons() {
		global $ai1ec_settings,
		       $ai1ec_view_helper,
		       $ai1ec_events_helper;

		$modals = $create_event_url = '';

		// ===================
		// = Post Your Event =
		// ===================
		$show_post_your_event =
			$ai1ec_settings->show_create_event_button &&
			( current_user_can( 'edit_ai1ec_events' ) ||
				$ai1ec_settings->allow_anonymous_submissions );
		$show_front_end_create_form = $ai1ec_settings->show_front_end_create_form;

		if ( $show_post_your_event ) {
			// Show front-end creation button & modal skeleton.
			if ( $show_front_end_create_form ) {
				$modals .=
					$ai1ec_view_helper->get_theme_view( 'create-event-modal.php' );
			}
			// Show button link to traditional back-end form.
			else {
				$create_event_url = esc_attr(
					admin_url( 'post-new.php?post_type=' . AI1EC_POST_TYPE )
				);
			}
		}

		// ==========================
		// = Add Your Calendar Feed =
		// ==========================
		$show_add_your_calendar = $ai1ec_settings->show_add_calendar_button;

		if ( $show_add_your_calendar ) {
			if ( ! is_user_logged_in() &&
				$ai1ec_settings->recaptcha_key !== '' ) {
				$recaptcha_key = $ai1ec_settings->recaptcha_public_key;
			} else {
				$recaptcha_key = false;
			}
			$modal_args = array(
				"categories"    => $ai1ec_events_helper->get_html_for_category_selector(),
				"recaptcha_key" => $recaptcha_key
			);
			$modals .= $ai1ec_view_helper->get_theme_view(
				'submit-ics-modal.php', $modal_args
			);
		}

		$args = array(
			'show_post_your_event'       => $show_post_your_event,
			'show_add_your_calendar'     => $show_add_your_calendar,
			'show_front_end_create_form' => $show_front_end_create_form,
			'modals'                     => $modals,
			'create_event_url'           => $create_event_url,
		);

		return
			$ai1ec_view_helper->get_theme_view( 'contribution-buttons.php', $args );
	}
}
// END class
