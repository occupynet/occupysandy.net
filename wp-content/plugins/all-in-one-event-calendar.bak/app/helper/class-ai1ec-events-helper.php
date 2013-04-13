<?php

/**
 * Helper class for events.
 *
 * @author     Timely Network Inc
 * @since      2011.07.13
 *
 * @package    AllInOneEventCalendar
 * @subpackage AllInOneEventCalendar.App.Helper
 */
class Ai1ec_Events_Helper {
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
	 * get_event function
	 *
	 * Fetches the event object with the given post ID. Uses the WP cache to
	 * make this more efficient if possible.
	 *
	 * @param int $post_id  The ID of the post associated with the event
	 *
	 * @return Ai1ec_Event  The associated event object
	 **/
	static function get_event( $post_id )
	{
		$event = wp_cache_get( $post_id, AI1EC_POST_TYPE );
		if( $event === false ) {
			// try to get the event instance id, if it is not set get the post id
			$instance_id = isset( $_REQUEST["instance_id"] ) ? (int) $_REQUEST["instance_id"] : false;
			$event = new Ai1ec_Event( $post_id, $instance_id );

			if( ! $event->post_id )
				throw new Ai1ec_Event_Not_Found( "Event with ID '$post_id' could not be retrieved from the database." );

			// Cache the event data
			wp_cache_add( $post_id, $event, AI1EC_POST_TYPE );
		}
		return $event;
	}

	/**
	 * get_matching_event function
	 *
	 * Return event ID by iCalendar UID, feed url, start time and whether the
	 * event has recurrence rules (to differentiate between an event with a UID
	 * defining the recurrence pattern, and other events with with the same UID,
	 * which are just RECURRENCE-IDs).
	 *
	 * @param int $uid iCalendar UID property
	 * @param string $feed Feed URL
	 * @param int $start Start timestamp (GMT)
	 * @param bool $has_recurrence Whether the event has recurrence rules
	 * @param int|null $exclude_post_id Do not match against this post ID
	 *
	 * @return object|null Matching event's post ID, or null if no match
	 **/
	function get_matching_event_id( $uid, $feed, $start, $has_recurrence = false, $exclude_post_id = null ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'ai1ec_events';
		$query = "SELECT post_id FROM {$table_name} " .
			"WHERE ical_feed_url = %s " .
			"AND ical_uid = %s " .
			"AND start = %s " .
			( $has_recurrence ? 'AND NOT ' : 'AND ' ) .
			"( recurrence_rules IS NULL OR recurrence_rules = '' )";
		$args = array(
			$feed,
			$uid,
			Ai1ec_Time_Utility::to_mysql_date( $start ),
		);
		if( ! is_null( $exclude_post_id ) ) {
			$query .= 'AND post_id <> %d';
			$args[] = $exclude_post_id;
		}

		return $wpdb->get_var( $wpdb->prepare( $query, $args ) );
	}

	/**
	 * delete_event_cache function
	 *
	 * Delete cache of event
	 *
	 * @param int $pid Event post ID
	 *
	 * @return void
	 **/
	function delete_event_cache( $pid ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'ai1ec_event_instances';
		$wpdb->query( $wpdb->prepare( "DELETE FROM $table_name WHERE post_id = %d", $pid ) );
	}

	/**
	 * delete_event_instance_cache function
	 *
	 * Delete cache of event instance
	 *
	 * @param int $post_id     Event post ID
	 * @param int $instance_id Event instance ID
	 *
	 * @return bool Success
	 **/
	public function delete_event_instance_cache( $post_id, $instance_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ai1ec_event_instances';
		$query      = 'DELETE FROM `' . $table_name .
			'` WHERE `post_id` = %d AND `id` = %d';
		$statement  = $wpdb->prepare( $query, $post_id, $instance_id );
		return $wpdb->query( $statement );
	}

	/**
	 * get the last day of a recurring event
	 *
	 * @param int $post_id
	 */
	public function get_final_instance_of_recurring_event( $post_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ai1ec_event_instances';
		$query = "SELECT end from $table_name WHERE post_id = %d ORDER BY end DESC limit 1";
		$statement  = $wpdb->prepare( $query, $post_id );
		$record     = $wpdb->get_var( $statement );
		if ( ! empty( $record ) ) {
			$record = Ai1ec_Time_Utility::from_mysql_date( $record );
		}
		return $record;
	}

	/**
	 * when using BYday you need an array of arrays.
	 * This function create valid arrays that keep into account the presence
	 * of a week number beofre the day
	 *
	 * @param string $val
	 * @return array
	 */
	private function create_byday_array( $val ) {
		$week = substr( $val, 0, 1 );
		if( is_numeric( $week ) ) {
			return array( $week, 'DAY' => substr( $val, 1 ) );
		} else {
			return array( 'DAY' => $val );
		}
	}

	/**
	 * Parse a `recurrence rule' into an array that can be used to calculate
	 * recurrence instances.
	 *
	 * @see http://kigkonsult.se/iCalcreator/docs/using.html#EXRULE
	 *
	 * @param string $rule
	 * @return array
	 */
	private function build_recurrence_rules_array( $rule ) {
		$rules     = array();
		$rule_list = explode( ';', $rule );
		foreach ( $rule_list as $single_rule ) {
			if ( false === strpos( $single_rule, '=' ) ) {
				continue;
			}
			list( $key, $val ) = explode( '=', $single_rule );
			$key               = strtoupper( $key );
			switch ( $key ) {
				case 'BYDAY':
					$rules['BYDAY'] = array();
					foreach ( explode( ',', $val ) as $day ) {
						$rules['BYDAY'][] = $this->create_byday_array( $day );
					}
					break;

				case 'BYMONTHDAY':
				case 'BYMONTH':
					if ( false === strpos( $val, ',' ) ) {
						$rules[$key] = $val;
					} else {
						$rules[$key] = explode( ',', $val );
					}
					break;

				default:
					$rules[$key] = $val;
			}
		}
		return $rules;
	}

	/**
	 * cache_event function
	 *
	 * Creates a new entry in the cache table for each date that the event appears
	 * (and does not already have an explicit RECURRENCE-ID instance, given its
	 * iCalendar UID).
	 *
	 * @param object $event Event to generate cache table for
	 *
	 * @return void
	 **/
	function cache_event( &$event ) {
		global $wpdb;

		// Convert event's timestamps to local for correct calculations of
		// recurrence. Need to also remove PHP timezone offset for each date for
		// SG_iCal to calculate correct recurring instances.
		$event->start = $this->gmt_to_local( $event->start ) - date( 'Z', $event->start );
		$event->end = $this->gmt_to_local( $event->end ) - date( 'Z', $event->end );

		$evs = array();
		$e	 = array(
			'post_id' => $event->post_id,
			'start'   => $event->start,
			'end'     => $event->end,
		);
		$duration = $event->getDuration();

		// Timestamp of today + 3 years
		$tif = Ai1ec_Time_Utility::current_time( true ) + 94608000; //94 608 000 = 3 years in seconds
		// Always cache initial instance
		$evs[] = $e;

		$_start = $event->start;
		$_end   = $event->end;

		if ( $event->recurrence_rules ) {
			$start  = $event->start;
			$wdate = $startdate = iCalUtilityFunctions::_timestamp2date( $_start, 6 );
			$enddate = iCalUtilityFunctions::_timestamp2date( $tif, 6 );
			$exclude_dates = array();
			$recurrence_dates = array();
			if( $event->exception_rules ) {
				// creat an array for the rules
				$exception_rules = $this->build_recurrence_rules_array( $event->exception_rules );
				$exception_rules = iCalUtilityFunctions::_setRexrule( $exception_rules );
				$result = array();
				// The first array is the result and it is passed by reference
				iCalUtilityFunctions::_recur2date(
					$exclude_dates,
					$exception_rules,
					$wdate,
					$startdate,
					$enddate
				);
			}
			$recurrence_rules = $this->build_recurrence_rules_array( $event->recurrence_rules );
			$recurrence_rules = iCalUtilityFunctions::_setRexrule( $recurrence_rules );
			iCalUtilityFunctions::_recur2date(
				$recurrence_dates,
				$recurrence_rules,
				$wdate,
				$startdate,
				$enddate
			);
			// Add the instances
			foreach ( $recurrence_dates as $date => $bool ) {
				// The arrays are in the form timestamp => true so an isset call is what we need
				if( isset( $exclude_dates[$date] ) ) {
					continue;
				}
				$e['start'] = $date;
				$e['end'] 	= $date + $duration;
				$excluded   = false;


				// Check if exception dates match this occurence
				if( $event->exception_dates ) {
					if( $this->date_match_exdates( $date, $event->exception_dates ) )
						$excluded = true;
				}

				// Add event only if it is not excluded
				if( $excluded == false )
					$evs[] = $e;
			}
		}

		foreach( $evs as $e ) {
			// Find out if this event instance is already accounted for by an
			// overriding 'RECURRENCE-ID' of the same iCalendar feed (by comparing the
			// UID, start date, recurrence). If so, then do not create duplicate
			// instance of event.
			$matching_event_id = $event->ical_uid ?
					$this->get_matching_event_id(
						$event->ical_uid,
						$event->ical_feed_url,
						$start = $this->local_to_gmt( $e['start'] ) - date( 'Z', $e['start'] ),
						false,	// Only search events that don't define recurrence (i.e. only search for RECURRENCE-ID events)
						$event->post_id
					)
				: null;


			// If no other instance was found
			if( is_null( $matching_event_id ) )
			{
				$start = getdate( $e['start'] );
				$end = getdate( $e['end'] );

				/*
				// Commented out for now
				// If event spans a day and end time is not midnight, or spans more than
				// a day, then create instance for each spanning day
				if( ( $start['mday'] != $end['mday'] &&
							( $end['hours'] || $end['minutes'] || $end['seconds'] ) )
						|| $e['end'] - $e['start'] > 60 * 60 * 24 ) {
					$this->create_cache_table_entries( $e );
				// Else cache single instance of event
				} else {
					$this->insert_event_in_cache_table( $e );
				}
				*/
				$this->insert_event_in_cache_table( $e );
			}
		}
	}

	/**
	 * date_match_exdates function
	 *
	 * @return bool
	 **/
	function date_match_exdates( $date, $ics_rule ) {
		foreach( explode( ",", $ics_rule ) as $_date ) {
			// convert to timestamp
			$_date_start = strtotime( $_date );
			// convert from UTC to local time
			$_date_start = $this->gmt_to_local( $_date_start ) - date( 'Z', $_date_start );
			if( $_date_start != false ) {
				// add 23h 59m 59s so the whole day is excluded
				$_date_end = $_date_start + (24 * 60 * 60) - 1;
				if( $date >= $_date_start && $date <= $_date_end ) {
					// event is within the time-frame
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * generate_dates_array_from_ics_rule function
	 *
	 * @return array
	 **/
	function generate_dates_array_from_ics_rule( $start, $ics_rule ) {
		$freq = new SG_iCal_Freq( $ics_rule, $start, array(), array(), true );
		return $freq->getAllOccurrences();
	}

	/**
	 * insert_event_in_cache_table function
	 *
	 * Inserts a new record in the cache table
	 *
	 * @param array $event Event array
	 *
	 * @return void
	 **/
	 function insert_event_in_cache_table( $event ) {
		 global $wpdb;

		 // Return the start/end times to GMT zone
		 $event['start'] = $this->local_to_gmt( $event['start'] ) + date( 'Z', $event['start'] );
		 $event['end']   = $this->local_to_gmt( $event['end'] )   + date( 'Z', $event['end'] );
		 $event['start'] = Ai1ec_Time_Utility::to_mysql_date( $event['start'] );
		 $event['end']   = Ai1ec_Time_Utility::to_mysql_date( $event['end'] );

		 $wpdb->query(
			 $wpdb->prepare(
				 "INSERT INTO {$wpdb->prefix}ai1ec_event_instances " .
				 "       ( post_id,  start,  end ) " .
				 "VALUES ( %d,       %s,     %s  )",
				 $event
			 )
		 );
	 }

	 /**
		* create_cache_table_entries function
		*
		* Create a new entry for each day that the event spans.
		*
		* @param array $e Event array
		*
		* @return void
		**/
		function create_cache_table_entries( $e )
		{
			global $ai1ec_events_helper;

			// Decompose start dates into components
			$start_bits = getdate( $e['start'] );

			// ============================================
			// = Calculate the time for event's first day =
			// ============================================
			// Start time is event's original start time
			$event_start = $e['start'];
			// End time is beginning of next day
			$event_end = mktime(
				0,                       // hour
				0,                       // minute
				0,                       // second
				$start_bits['mon'],      // month
				$start_bits['mday'] + 1, // day
				$start_bits['year']      // year
			);
			// Cache first day
			$this->insert_event_in_cache_table( array( 'post_id' => $e['post_id'], 'start' => $event_start, 'end' => $event_end ) );

			// ====================================================
			// = Calculate the time for event's intermediate days =
			// ====================================================
			// Start time is previous end time
			$event_start = $event_end;
			// End time one day ahead
			$event_end += 60 * 60 * 24;
			// Cache intermediate days
			while( $event_end < $e['end'] ) {
				$this->insert_event_in_cache_table( array( 'post_id' => $e['post_id'], 'start' => $event_start, 'end' => $event_end ) );
				$event_start  = $event_end;    // Start time is previous end time
				$event_end    += 24 * 60 * 60; // Increment end time by 1 day
			}

			// ===========================================
			// = Calculate the time for event's last day =
			// ===========================================
			// Start time is already correct (previous end time)
			// End time is event end time
			// Only insert if the last event instance if span is > 0
			$event_end = $e['end'];
			if( $event_end > $event_start )
				// Cache last day
				$this->insert_event_in_cache_table( array( 'post_id' => $e['post_id'], 'start' => $event_start, 'end' => $event_end ) );
		}

	/**
	 * event_parent method
	 *
	 * Get/set event parent
	 *
	 * @param int $event_id    ID of checked event
	 * @param int $parent_id   ID of new parent [optional=NULL, acts as getter]
	 * @param int $instance_id ID of old instance id
	 *
	 * @return int|bool Value depends on mode:
	 *     Getter:
	 *         - false for entry without parent
	 *         - parent_id when found
	 *     Setter:
	 *         - true on success.
	 */
	public function event_parent( $event_id, $parent_id = NULL, $instance_id = NULL ) {
		$meta_key = '_ai1ec_event_parent';
		if ( NULL === $parent_id ) {
			$parent_id = (int)Ai1ec_Meta::instance( 'Post' )
				->get( $event_id, $meta_key, 0, true );
			if ( empty( $parent_id ) || $parent_id <= 0 ) {
				$parent_id = false;
			}
			return $parent_id;
		}
		$parent_id = (int)$parent_id;
		add_post_meta( $event_id, $meta_key, $parent_id,  true );
		$meta_key .= '_' . $parent_id;
		$meta_value = json_encode( array(
				'created'  => Ai1ec_Time_Utility::current_time(),
				'instance' => $instance_id,
		) );
		add_post_meta( $event_id, $meta_key, $meta_value, true );
		return true;
	}

	/**
	 * Returns the various preset recurrence options available (e.g.,
	 * 'DAILY', 'WEEKENDS', etc.).
	 *
	 * @return string        An associative array of pattern names to English
	 *                       equivalents
	 */
	function get_repeat_patterns() {
		// Calling functions when creating an array does not seem to work when
		// the assigned to variable is static. This is a workaround.
		static $options;
		if( !isset( $options ) ) {
			$temp = array(
				' ' => __( 'No repeat', AI1EC_PLUGIN_NAME ),
				'1' => __( 'Every day', AI1EC_PLUGIN_NAME ),
				'2' => __( 'Every week', AI1EC_PLUGIN_NAME ),
				'3' => __( 'Every month', AI1EC_PLUGIN_NAME ),
				'4' => __( 'Every year', AI1EC_PLUGIN_NAME ),
				'5' => '-----------',
				'6' => __( 'Custom...', AI1EC_PLUGIN_NAME ),
			);
			$options = $temp;
		}
		return $options;
	}

	/**
	 * Generates and returns repeat dropdown
	 *
	 * @param Integer|NULL $selected Selected option
	 *
	 * @return String Repeat dropdown
	 */
	function create_repeat_dropdown( $selected = null ) {
		$options = array(
			' ' => __( 'No repeat', AI1EC_PLUGIN_NAME ),
			1   => __( 'Every day', AI1EC_PLUGIN_NAME ),
			2   => __( 'Every week', AI1EC_PLUGIN_NAME ),
			3   => __( 'Every month', AI1EC_PLUGIN_NAME ),
			4   => __( 'Every year', AI1EC_PLUGIN_NAME ),
			5   => '-----------',
			6   => __( 'Custom...', AI1EC_PLUGIN_NAME ),
		);
		return $this->create_select_element( 'ai1ec_repeat', $options, $selected, array( 5 ) );
	}

	/**
	 * Returns an associative array containing the following information:
	 *   string 'repeat' => pattern of repetition ('DAILY', 'WEEKENDS', etc.)
	 *   int    'count'  => end after 'count' times
	 *   int    'until'  => repeat until date (as UNIX timestamp)
	 * Elements are null if no such recurrence information is available.
	 *
	 * @param  Ai1ec_Event  Event object to parse recurrence rules of
	 * @return array        Array structured as described above
	 **/
	function parse_recurrence_rules( &$event )
	{
		$repeat   = null;
		$count    = null;
		$until    = null;
		$end      = 0;
		if( ! is_null( $event ) ) {
			if( strlen( $event->recurrence_rules ) > 0 ) {
				$line = new SG_iCal_Line( $event->recurrence_rules );
				$rec = new SG_iCal_Recurrence( $line );
				switch( $rec->req ) {
					case 'DAILY':
						$by_day = $rec->getByDay();
						if( empty( $by_day ) ) {
							$repeat = 'DAILY';
						} elseif( $by_day[0] == 'SA+SU' ) {
							$repeat = 'WEEKENDS';
						} elseif( count( $by_day ) == 5 ) {
							$repeat = 'WEEKDAYS';
						} else {
							foreach( $by_day as $d ) {
								$repeat .= $d . '+';
							}
							$repeat = substr( $repeat, 0, -1 );
						}
						break;
					case 'WEEKLY':
						$repeat = 'WEEKLY';
						break;
					case 'MONTHLY':
						$repeat = 'MONTHLY';
						break;
					case 'YEARLY':
						$repeat = 'YEARLY';
						break;
				}
				$count = $rec->getCount();
				$until = $rec->getUntil();
				if( $until ) {
					$until = strtotime( $rec->getUntil() );
					$until += date( 'Z', $until ); // Add timezone offset
					$end = 2;
				} elseif( $count )
					$end = 1;
				else
					$end = 0;
			}
		}
		return array(
			'repeat'  => $repeat,
			'count'   => $count,
			'until'   => $until,
			'end'     => $end
		);
	}

	/**
	 * Generates and returns "End after X times" input
	 *
	 * @param Integer|NULL $count Initial value of range input
	 *
	 * @return String Repeat dropdown
	 */
	function create_count_input( $name, $count = 100, $max = 365 ) {
		ob_start();

		if( ! $count ) $count = 100;
		?>
<input type="range" name="<?php echo $name ?>" id="<?php echo $name ?>"
	min="1" max="<?php echo $max ?>"
	<?php if( $count ) echo 'value="' . $count . '"' ?> />
<?php
		return ob_get_clean();
	}

	/**
	 * create_select_element function
	 *
	 *
	 *
	 * @return void
	 **/
	function create_select_element( $name, $options = array(), $selected = false, $disabled_keys = array() ) {
		ob_start();
		?>
<select name="<?php echo $name ?>" id="<?php echo $name ?>">
			<?php foreach( $options as $key => $val ): ?>
				<option value="<?php echo $key ?>"
		<?php echo $key === $selected ? 'selected="selected"' : '' ?>
		<?php echo in_array( $key, $disabled_keys ) ? 'disabled="disabled"' : '' ?>>
					<?php echo $val ?>
				</option>
			<?php endforeach ?>
		</select>
<?php
		return ob_get_clean();
	}

	/**
	 * create_on_the_select function
	 *
	 *
	 *
	 * @return void
	 **/
	function create_on_the_select( $f_selected = false, $s_selected = false ) {
		$ret = "";

		$first_options = array(
			'0' => __( 'first', AI1EC_PLUGIN_NAME ),
			'1' => __( 'second', AI1EC_PLUGIN_NAME ),
			'2' => __( 'third', AI1EC_PLUGIN_NAME ),
			'3' => __( 'fourth', AI1EC_PLUGIN_NAME ),
			'4' => '------',
			'5' => __( 'last', AI1EC_PLUGIN_NAME )
		);
		$ret = $this->create_select_element( 'ai1ec_monthly_each_select', $first_options, $f_selected, array( 4 ) );

		$second_options = array(
			'0'   => __( 'Sunday', AI1EC_PLUGIN_NAME ),
			'1'   => __( 'Monday', AI1EC_PLUGIN_NAME ),
			'2'   => __( 'Tuesday', AI1EC_PLUGIN_NAME ),
			'3'   => __( 'Wednesday', AI1EC_PLUGIN_NAME ),
			'4'   => __( 'Thursday', AI1EC_PLUGIN_NAME ),
			'5'   => __( 'Friday', AI1EC_PLUGIN_NAME ),
			'6'   => __( 'Saturday', AI1EC_PLUGIN_NAME ),
			'7'   => '--------',
			'8'   => __( 'day', AI1EC_PLUGIN_NAME ),
			'9'   => __( 'weekday', AI1EC_PLUGIN_NAME ),
			'10'  => __( 'weekend day', AI1EC_PLUGIN_NAME )
		);

		return $ret . $this->create_select_element( 'ai1ec_monthly_on_the_select', $second_options, $s_selected, array( 7 ) );
	}

	/**
	 * undocumented function
	 *
	 *
	 *
	 * @return void
	 **/
	function create_list_element( $name, $options = array(), $selected = array() ) {
		ob_start();
		?>
<ul class="ai1ec_date_select <?php echo $name?>" id="<?php echo $name?>">
			<?php foreach( $options as $key => $val ): ?>
				<li
		<?php echo in_array( $key, $selected ) ? 'class="ai1ec_selected"' : '' ?>>
					<?php echo $val ?>
					<input type="hidden" name="<?php echo $name . '_' . $key ?>"
		value="<?php echo $key ?>" />
	</li>
			<?php endforeach ?>
		</ul>
<input type="hidden" name="<?php echo $name ?>"
	value="<?php echo implode( ',', $selected ) ?>" />
<?php
		return ob_get_clean();
	}

	/**
	 * create_montly_date_select function
	 *
	 *
	 *
	 * @return void
	 **/
	function create_montly_date_select( $selected = array() ) {
		$options = array();

		for( $i = 1; $i <= 31; ++$i )
			$options[$i] = $i;

		return $this->create_list_element( 'ai1ec_montly_date_select', $options, $selected );
	}

	/**
	 * create_yearly_date_select function
	 *
	 *
	 *
	 * @return void
	 **/
	function create_yearly_date_select( $selected = array() ) {
		global $wp_locale;
		$options = array();

		for( $i = 1; $i <= 12; ++$i ) {
			$x = $i < 10 ? 0 . $i : $i;
			$options[$i] = $wp_locale->month_abbrev[$wp_locale->month[$x]];
		}

		return $this->create_list_element( 'ai1ec_yearly_date_select', $options, $selected );
	}

	function get_frequency( $index ) {
		$frequency = array(
			0 => __( 'Daily', AI1EC_PLUGIN_NAME ),
			1 => __( 'Weekly', AI1EC_PLUGIN_NAME ),
			2 => __( 'Monthly', AI1EC_PLUGIN_NAME ),
			3 => __( 'Yearly', AI1EC_PLUGIN_NAME ),
		);
		return $frequency[$index];
	}

	/**
	 * row_frequency function
	 *
	 * @return void
	 **/
	function row_frequency( $visible = false, $selected = false ) {
		global $ai1ec_view_helper;

		$frequency = array(
			0 => __( 'Daily', AI1EC_PLUGIN_NAME ),
			1 => __( 'Weekly', AI1EC_PLUGIN_NAME ),
			2 => __( 'Monthly', AI1EC_PLUGIN_NAME ),
			3 => __( 'Yearly', AI1EC_PLUGIN_NAME ),
		);

		$args = array(
		 'visible'    => $visible,
		 'frequency'  => $this->create_select_element( 'ai1ec_frequency', $frequency, $selected )
		);
		return $ai1ec_view_helper->get_admin_view( 'row_frequency.php', $args );
	}

	/**
	 * row_daily function
	 *
	 * Returns daily selector
	 *
	 * @return void
	 **/
	function row_daily( $visible = false, $selected = 1 ) {
		global $ai1ec_view_helper;

		$args = array(
		 'visible'  => $visible,
		 'count'    => $this->create_count_input( 'ai1ec_daily_count', $selected, 365 ) . __( 'day(s)', AI1EC_PLUGIN_NAME )
		);
		return $ai1ec_view_helper->get_admin_view( 'row_daily.php', $args );
	}

	/**
	 * row_weekly function
	 *
	 * Returns weekly selector
	 *
	 * @return void
	 **/
	function row_weekly( $visible = false, $count = 1, $selected = array() ) {
		global $ai1ec_view_helper, $wp_locale;
		$start_of_week = Ai1ec_Meta::get_option( 'start_of_week', 1 );

		$options = array();
		// get days from start_of_week until the last day
		for( $i = $start_of_week; $i <= 6; ++$i )
			$options[$this->get_weekday_by_id( $i )] = $wp_locale->weekday_initial[$wp_locale->weekday[$i]];

		// get days from 0 until start_of_week
		if( $start_of_week > 0 ) {
			for( $i = 0; $i < $start_of_week; $i++ )
				$options[$this->get_weekday_by_id( $i )] = $wp_locale->weekday_initial[$wp_locale->weekday[$i]];
		}

		$args = array(
		 'visible'    => $visible,
		 'count'      => $this->create_count_input( 'ai1ec_weekly_count', $count, 52 ) . __( 'week(s)', AI1EC_PLUGIN_NAME ),
		 'week_days'  => $this->create_list_element( 'ai1ec_weekly_date_select', $options, $selected )
		);
		return $ai1ec_view_helper->get_admin_view( 'row_weekly.php', $args );
	}

	/**
	 * get_weekday_by_id function
	 *
	 * Returns weekday name in English
	 *
	 * @param int $day_id Day ID
	 *
	 * @return string
	 **/
	function get_weekday_by_id( $day_id, $by_value = false ) {
		// do not translate this !!!
		$week_days = array(
		 0 => 'SU',
		 1 => 'MO',
		 2 => 'TU',
		 3 => 'WE',
		 4 => 'TH',
		 5 => 'FR',
		 6 => 'SA'
		);

		if( $by_value ) {
			while( $_name = current( $week_days ) ) {
					if( $_name == $day_id ) {
							return key( $week_days );
					}
					next( $week_days );
			}
			return false;
		}
		else
			return $week_days[$day_id];
	}

	/**
	 * row_monthly function
	 *
	 * Returns monthly selector
	 *
	 * @return void
	 **/
	function row_monthly( $visible = false, $count = 1, $ai1ec_monthly_each = 0, $ai1ec_monthly_on_the = 0, $month = array(), $first = false, $second = false ) {
		global $ai1ec_view_helper, $wp_locale;
		$start_of_week = Ai1ec_Meta::get_option( 'start_of_week', 1 );

		$options_wd = array();
		// get days from start_of_week until the last day
		for( $i = $start_of_week; $i <= 6; ++$i )
			$options_wd[$this->get_weekday_by_id( $i )] = $wp_locale->weekday[$i];

		// get days from 0 until start_of_week
		if( $start_of_week > 0 ) {
			for( $i = 0; $i < $start_of_week; $i++ )
				$options_wd[$this->get_weekday_by_id( $i )] = $wp_locale->weekday[$i];
		}

		// get options like 1st/2nd/3rd for "day number"
		$options_dn = array( 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5 );
		foreach( $options_dn as $_dn ) {
			$options_dn[$_dn] = Ai1ec_Time_Utility::date_i18n(
				'jS',
				strtotime( $_dn . '-01-1998 12:00:00' )
			);
		}

		$args = array(
		 'visible'              => $visible,
		 'count'                => $this->create_count_input( 'ai1ec_monthly_count', $count, 12 ) . __( 'month(s)', AI1EC_PLUGIN_NAME ),
		 'ai1ec_monthly_each'   => $ai1ec_monthly_each,
		 'ai1ec_monthly_on_the' => $ai1ec_monthly_on_the,
		 'month'                => $this->create_montly_date_select( $month ),
		 'on_the_select'        => $this->create_on_the_select( $first, $second ),
		 'day_nums'             => $this->create_select_element( 'ai1ec_monthly_byday_num', $options_dn ),
		 'week_days'            => $this->create_select_element( 'ai1ec_monthly_byday_weekday', $options_wd )
		);
		return $ai1ec_view_helper->get_admin_view( 'row_monthly.php', $args );
	}

	/**
	 * row_yearly function
	 *
	 * Returns yearly selector
	 *
	 * @return void
	 **/
	function row_yearly( $visible = false, $count = 1, $year = array(), $first = false, $second = false ) {
		global $ai1ec_view_helper;

		$args = array(
		 'visible'              => $visible,
		 'count'                => $this->create_count_input( 'ai1ec_yearly_count', $count, 10 ) . __( 'year(s)', AI1EC_PLUGIN_NAME ),
		 'year'                 => $this->create_yearly_date_select( $year ),
		 'on_the_select'        => $this->create_on_the_select( $first, $second )
		);
		return $ai1ec_view_helper->get_admin_view( 'row_yearly.php', $args );
	}

	/**
	 * get_all_matching_posts function
	 *
	 * Gets existing event posts that are between the interval
	 *
	 * @param int $s_time Start time
	 * @param int $e_time End time
	 *
	 * @return Array of matching event posts
	 **/
	function get_all_matching_posts( $s_time, $e_time ) {
		global $ai1ec_calendar_helper;
		return $ai1ec_calendar_helper->get_events_between( $s_time, $e_time );
	}

	/**
	 * get_matching_events function
	 *
	 * Get events that match with the arguments provided.
	 *
	 * @param int | bool          $start      Events start before this (GMT) time
	 * @param int | bool          $end        Events end before this (GMT) time
	 * @param array $filter       Array of filters for the events returned.
	 *                            ['cat_ids']   => non-associatative array of category IDs
	 *                            ['tag_ids']   => non-associatative array of tag IDs
	 *                            ['post_ids']  => non-associatative array of post IDs
	 *
	 * @return array Matching events #'
	 **/
	function get_matching_events( $start = false, $end = false, $filter = array() ) {
		global $wpdb, $ai1ec_calendar_helper, $ai1ec_localization_helper;

		// holds event_categories sql
		$c_sql = '';
		$c_where_sql = '';
		// holds event_tags sql
		$t_sql = '';
		$t_where_sql ='';
		// holds posts sql
		$p_where_sql = '';
		// holds start sql
		$start_where_sql = '';
		// holds end sql
		$end_where_sql = '';
		// hold escape values
		$args = array();

		// =============================
		// = Generating start date sql =
		// =============================
		if( $start !== false ) {
			$start_where_sql = "AND (e.start >= %s OR e.recurrence_rules != '')";
			$args[] = Ai1ec_Time_Utility::to_mysql_date( $start );
		}

		// ===========================
		// = Generating end date sql =
		// ===========================
		if( $end !== false ) {
			$end_where_sql = "AND (e.end <= %s OR e.recurrence_rules != '')";
			$args[] = Ai1ec_Time_Utility::to_mysql_date( $end );
		}

		$wpml_join_particle  = $ai1ec_localization_helper
			->get_wpml_table_join();
		$wpml_where_particle = $ai1ec_localization_helper
			->get_wpml_table_where();

		// Get the Join (filter_join) and Where (filter_where) statements based on $filter elements specified
		$ai1ec_calendar_helper->_get_filter_sql( $filter );
		$query = $wpdb->prepare(
			"SELECT *, e.post_id, e.start as start, e.end as end, e.allday, e.recurrence_rules, e.exception_rules,
				e.recurrence_dates, e.exception_dates, e.venue, e.country, e.address, e.city, e.province, e.postal_code,
				e.show_map, e.contact_name, e.contact_phone, e.contact_email, e.cost, e.ical_feed_url, e.ical_source_url,
				e.ical_organizer, e.ical_contact, e.ical_uid " .
			"FROM $wpdb->posts " .
				"INNER JOIN {$wpdb->prefix}ai1ec_events AS e ON e.post_id = ID " .
				$wpml_join_particle .
				$filter['filter_join'] .
			"WHERE post_type = '" . AI1EC_POST_TYPE . "' " .
				"AND post_status = 'publish' " .
				$wpml_where_particle .
				$filter['filter_where'] .
				$start_where_sql .
				$end_where_sql,
			$args );

		$events = $wpdb->get_results( $query, ARRAY_A );

		foreach( $events as &$event ) {
			$event['start'] = Ai1ec_Time_Utility::from_mysql_date( $event['start'] );
			$event['end']   = Ai1ec_Time_Utility::from_mysql_date( $event['end'] );
			try {
				$event = new Ai1ec_Event( $event );
			} catch( Ai1ec_Event_Not_Found $n ) {
				unset( $event );
				// The event is not found, continue to the next event
				continue;
			}

			// if there are recurrence rules, include the event, else...
			if( empty( $event->recurrence_rules ) ) {
				// if start time is set, and event start time is before the range
				// it, continue to the next event
				if( $start !== false && $event->start < $start ) {
					unset( $event );
					continue;
				}
				// if end time is set, and event end time is after
				// it, continue to the next event
				if( $end !== false && $ev->end < $end ) {
					unset( $event );
					continue;
				}
			}
		}

		return $events;
	}

	/**
	 * fuzzy_string_compare function
	 *
	 * Compares string A to string B using fuzzy comparison algorithm
	 *
	 * @param String $a String to compare
	 * @param String $b String to compare
	 *
	 * @return boolean True if the two strings match, false otherwise
	 **/
	function fuzzy_string_compare( $a, $b ) {
		$percent = 0;
		similar_text( $a, $b, $percent );
		return ( $percent > 50 );
	}

	/**
	 * get_short_time function
	 *
	 * Format a short-form time for use in compressed (e.g. month) views;
	 * this is also converted to the local timezone.
	 *
	 * @param int $timestamp
	 * @param bool $convert_from_gmt Whether to convert from GMT time to local
	 *
	 * @return string
	 **/
	function get_short_time( $timestamp, $convert_from_gmt = true ) {
		$time_format = Ai1ec_Meta::get_option( 'time_format', 'g:i a' );
		if( $convert_from_gmt ) {
			$timestamp = $this->gmt_to_local( $timestamp );
		}
		return Ai1ec_Time_Utility::date_i18n( $time_format, $timestamp, true );
	}

	/**
	 * get_short_date function
	 *
	 * Format a short-form date for use in compressed (e.g. month) views;
	 * this is also converted to the local timezone.
	 *
	 * @param int $timestamp
	 * @param bool $convert_from_gmt Whether to convert from GMT time to local
	 *
	 * @return string
	 **/
	function get_short_date( $timestamp, $convert_from_gmt = true ) {
		if ( $convert_from_gmt ) {
			$timestamp = $this->gmt_to_local( $timestamp );
		}
		return Ai1ec_Time_Utility::date_i18n( 'M j', $timestamp, true );
	}

	/**
	 * Return the value used in JS functions to extend multiday bars;
	 * this is also converted to the local timezone.
	 *
	 * @param int  $end_timestamp    Event's end date
	 * @param bool $convert_from_gmt Whether to convert from GMT time to local
	 *
	 * @return string
	 **/
	function get_multiday_end_day( $end_timestamp, $convert_from_gmt = true ) {
		if( $convert_from_gmt )
			$end_timestamp = $this->gmt_to_local( $end_timestamp );
		return Ai1ec_Time_Utility::date_i18n( 'd', $end_timestamp, true );
	}

	/**
	 * Returns a short-format time. DEPRECATED: Use get_short_time() instead.
	 *
	 * @param int $timestamp
	 * @param bool $convert_from_gmt Whether to convert from GMT time to local
	 *
	 * @return string
	 */
	function get_medium_time( $timestamp, $convert_from_gmt = true ) {
		trigger_error(
			__( 'Ai1ec_Events_Helper::get_medium_time() is deprecated.', AI1EC_PLUGIN_NAME ),
			E_USER_WARNING
		);
		return $this->get_short_time( $timestamp, $convert_from_gmt );
	}

	/**
	 * get_long_time function
	 *
	 * Format a long-length time for use in other views (e.g., single event);
	 * this is also converted to the local timezone.
	 *
	 * @param int $timestamp
	 * @param bool $convert_from_gmt Whether to convert from GMT time to local
	 *
	 * @return string
	 **/
	function get_long_time( $timestamp, $convert_from_gmt = true ) {
		$meta        = Ai1ec_Meta::instance( 'Option' );
		$date_format = $meta->get( 'date_format', NULL, 'l, M j, Y' );
		$time_format = $meta->get( 'time_format', NULL, 'g:i a' );
		if ( $convert_from_gmt ) {
			$timestamp = $this->gmt_to_local( $timestamp );
		}
		return Ai1ec_Time_Utility::date_i18n( $date_format, $timestamp, true ) .
			' @ ' .
			Ai1ec_Time_Utility::date_i18n( $time_format, $timestamp, true );
	}

	/**
	 * get_long_date function
	 *
	 * Format a long-length date for use in other views (e.g., single event);
	 * this is also converted to the local timezone if desired.
	 *
	 * @param int $timestamp
	 * @param bool $convert_from_gmt Whether to convert from GMT time to local
	 *
	 * @return string
	 **/
	function get_long_date( $timestamp, $convert_from_gmt = true ) {
		$date_format = Ai1ec_Meta::get_option( 'date_format', 'l, M j, Y' );
		if ( $convert_from_gmt ) {
			$timestamp = $this->gmt_to_local( $timestamp );
		}
		return Ai1ec_Time_Utility::date_i18n( $date_format, $timestamp, true );
	}

	/**
	 * gmt_to_local function
	 *
	 * Returns the UNIX timestamp adjusted to the local timezone.
	 *
	 * @param int $timestamp
	 *
	 * @return int
	 **/
	function gmt_to_local( $timestamp ) {
		return Ai1ec_Time_Utility::gmt_to_local( $timestamp );
	}

	/**
	 * get_local_timezone method
	 *
	 * Get applicable timezone name.
	 * First attempt is to check current user prefference.
	 * If that fails - global timezone offset is checked.
	 * If no timezone is used - {@see $default} is assumed.
	 *
	 * @param string $default Fallback timezone name [optional=America/Los_Angeles]
	 *
	 * @return string Timezone identifier
	 */
	public function get_local_timezone( $default = 'America/Los_Angeles' ) {
		return Ai1ec_Time_Utility::get_local_timezone( $default );
	}

	/**
	 * get_gmt_offset method
	 *
	 * Local wrapper to {@see get_option( 'gmt_offset' )} to incorporate
	 * user-specific timezone selections.
	 *
	 * @uses self::get_local_timezone() To get effective timezone string
	 *
	 * @return float Timezone offset from GMT
	 */
	public function get_gmt_offset() {
		return Ai1ec_Time_Utility::get_gmt_offset();
	}

	/**
	 * local_to_gmt function
	 *
	 * Returns the UNIX timestamp adjusted from the local timezone to GMT.
	 *
	 * @param int $timestamp
	 *
	 * @return int
	 **/
	function local_to_gmt( $timestamp ) {
		return Ai1ec_Time_Utility::local_to_gmt( $timestamp );
	}

	/**
	 * get_timezone_offset function
	 *
	 * Returns the offset from the origin timezone to the remote timezone, in seconds.
	 *
	 * @param string $remote_tz Remote TimeZone
	 * @param string $origin_tz Origin TimeZone
	 * @param string/int $timestamp Unix Timestamp or 'now'
	 *
	 * @return int
	 **/
	function get_timezone_offset( $remote_tz, $origin_tz = null, $timestamp = false ) {
		return Ai1ec_Time_Utility::get_timezone_offset(
			$remote_tz,
			$origin_tz,
			$timestamp
		);
	}

	/**
	 * A GMT-version of PHP getdate().
	 *
	 * @param int $timestamp  UNIX timestamp
	 *
	 * @return array          Same result as getdate(), but based in GMT time.
	 */
	function gmgetdate( $timestamp = null ) {
		return Ai1ec_Time_Utility::gmgetdate( $timestamp );
	}

	/**
	 * time_to_gmt function
	 *
	 * Converts time to GMT
	 *
	 * @param int $timestamp
	 *
	 * @return int
	 **/
	function time_to_gmt( $timestamp ) {
		return strtotime( gmdate( 'M d Y H:i:s', $timestamp ) );
	}

	/**
	 * date_to_gmdatestamp function
	 *
	 * Converts date (date-time) to GMT date expressed as UNIX timestamp
	 *
	 * @param int $input_time Local timestamp
	 *
	 * @return int Timestamp date representation in GMT zone
	 **/
	function date_to_gmdatestamp( $input_time ) {
		$time  = $this->gmt_to_local( $input_time );
		$time  = $this->gmgetdate( $time );
		$stamp = gmmktime(
			0,             // hour
			0,             // minute
			0,             // second
			$time['mon'],  // month
			$time['mday'], // day-of-month
			$time['year']  // year
		);
		return $stamp;
	}

	/**
	 * Returns the latitude/longitude coordinates as a textual string
	 * parsable by the Geocoder API.
	 *
	 * @param  Ai1ec_Event &$event The event to return data from
	 *
	 * @return string              The latitude & longitude string, or null
	 */
	function get_latlng( &$event ) {
		// If the coordinates are set, use those, otherwise use the address.
		$location = NULL;
		// If the coordinates are set by hand use them.
		if ( $event->show_coordinates ) {
			$longitude = floatval( $event->longitude );
			$latitude = floatval( $event->latitude );
			$location = "$latitude,$longitude";
		}
		return $location;
	}

	/**
	 * get_gmap_url function
	 *
	 * Returns the URL to the Google Map for the given event object.
	 *
	 * @param Ai1ec_Event &$event  The event object to display a map for
	 *
	 * @return string
	 **/
	function get_gmap_url( &$event ) {
		$location = $this->get_latlng( $event );
		if ( $location ) {
			$location .= empty( $event->venue ) ? '' : " ({$event->venue})";
		} else {
			// Otherwise use the address
			$location = $event->address;
		}

		$lang = $this->get_lang();

		return "https://www.google.com/maps?f=q&hl=" . $lang . "&source=embed&q=" . urlencode( $location );
	}

	/**
	 * get_lang function
	 *
	 * Returns the ISO-639 part of the configured locale. The default
	 * language is English (en).
	 *
	 * @return string
	 **/
	function get_lang() {
		$locale = explode( '_', get_locale() );

		return ( isset( $locale[0] ) && $locale[0] != '' ) ? $locale[0] : 'en';
	}

	/**
	 * get_region function
	 *
	 * Returns the ISO-3166 part of the configured locale as a ccTLD.
	 * Used for region biasing in the geo autocomplete plugin.
	 *
	 * @return string
	 **/
	function get_region() {
		$locale = explode( '_', get_locale() );

		$region = ( isset( $locale[1] ) && $locale[1] != '' ) ? strtolower( $locale[1] ) : '';

		// Primary ccTLD for United Kingdom is uk.
		return ( $region == 'gb' ) ? 'uk' : $region;
	}

	/**
	 * trim_excerpt function
	 *
	 * Generates an excerpt from the given content string. Adapted from
	 * WordPress's wp_trim_excerpt function that is not useful for applying
	 * to custom content.
	 *
	 * @param string $text The content to trim.
	 *
	 * @return string      The excerpt.
	 **/
	function trim_excerpt( $text )
	{
		$raw_excerpt = $text;

		$text = strip_shortcodes( $text );

		$text = str_replace(']]>', ']]&gt;', $text);
		$text = strip_tags($text);
		$excerpt_length = apply_filters('excerpt_length', 55);
		$excerpt_more = apply_filters('excerpt_more', ' ' . '[...]');
		$words = preg_split("/[\n\r\t ]+/", $text, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY);
		if ( count($words) > $excerpt_length ) {
			array_pop($words);
			$text = implode(' ', $words);
			$text = $text . $excerpt_more;
		} else {
			$text = implode(' ', $words);
		}
		return apply_filters('wp_trim_excerpt', $text, $raw_excerpt);
	}

	/**
	 * filter_by_terms function
	 *
	 * Returns a subset of post IDs from the given set of post IDs that have any
	 * of the given taxonomy term IDs. This is actually useful for all posts and
	 * taxonomies in general, not just event posts and event-specific taxonomies.
	 *
	 * @param array|string $post_ids  Post IDs as an array of ints or
	 *                                comma-separated string
	 * @param array|string $term_ids  Term IDs as an array of ints or
	 *                                comma-separated string
	 *
	 * @return array                  Filtered post IDs as an array of ints
	 */
	function filter_by_terms( $post_ids, $term_ids )
	{
		global $wpdb;

		// ===============================================
		// = Sanitize provided IDs against SQL injection =
		// ===============================================
		if( ! is_array( $post_ids ) )
			$post_ids = explode( ',', $post_ids );
		foreach( $post_ids as &$post_id ) {
			$post_id = intval( $post_id );
		}
		$post_ids = join( ',', $post_ids );

		if( ! is_array( $term_ids ) )
			$term_ids = explode( ',', $term_ids );
		foreach( $term_ids as &$term_id ) {
			$term_id = intval( $term_id );
		}
		$term_ids = join( ',', $term_ids );

		$query =
			"SELECT DISTINCT p.ID " .
			"FROM $wpdb->posts p " .
				"INNER JOIN $wpdb->term_relationships tr ON p.ID = tr.object_id " .
				"INNER JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id " .
			"WHERE p.ID IN ( " . $post_ids . " ) " .
				"AND tt.term_id IN ( " . $term_ids . " )";

		return $wpdb->get_col( $query );
	}

	/**
	 * get_category_color function
	 *
	 * Returns the color of the Event Category having the given term ID.
	 *
	 * @param int $term_id The ID of the Event Category
	 *
	 * @return string Color to use
	 *
	 * @staticvar Ai1ec_Memory_Utility $colors Cached entries instance
	 */
	function get_category_color( $term_id ) {
		static $colors = NULL;
		if ( ! isset( $colors ) ) {
			$colors = Ai1ec_Memory_Utility::instance( __METHOD__ );
		}
		$term_id = (int)$term_id;
		if ( NULL === ( $color = $colors->get( $term_id ) ) ) {
			global $wpdb;

			$color = (string)$wpdb->get_var(
				'SELECT term_color FROM ' . $wpdb->prefix .
				'ai1ec_event_category_colors' . ' WHERE term_id = ' .
				$term_id
			);
			$colors->set( $term_id, $color );
		}
		return $color;
	}

	/**
	 * get_category_color_square function
	 *
	 * Returns the HTML markup for the category color square of the given Event
	 * Category term ID.
	 *
	 * @param int $term_id The Event Category's term ID
	 * @return string
	 **/
	function get_category_color_square( $term_id ) {
		$color = $this->get_category_color( $term_id );
		$cat = get_term( $term_id, 'events_categories' );
		if( ! is_null( $color ) && ! empty( $color ) )
			return '<span class="ai1ec-category-color ai1ec-tooltip-trigger" style="background:' . $color . '" title="' . esc_attr( $cat->name ) . '"></span>';

		return '';
	}

	/**
	 * get_event_category_color_style function
	 *
	 * Returns the style attribute assigning the category color style to an event.
	 *
	 * @param int $term_id The Event Category's term ID
	 * @param bool $allday Whether the event is all-day
	 * @return string
	 **/
	function get_event_category_color_style( $term_id, $allday = false ) {
		$color = $this->get_category_color( $term_id );
		if( ! is_null( $color ) && ! empty( $color ) ) {
			if( $allday )
				return 'background-color: ' . $color . ';';
			else
				return 'color: ' . $color . ' !important;';
		}

		return '';
	}

	/**
	 * get_event_text_color function
	 *
	 * Returns the style attribute assigning the category color style to an event.
	 *
	 * @param int $term_id The Event Category's term ID
	 * @param bool $allday Whether the event is all-day
	 * @return string
	 **/
	function get_event_category_text_color( $term_id ) {
		$color = $this->get_category_color( $term_id );
		if( ! is_null( $color ) && ! empty( $color ) ) {
			return 'style="color: ' . $color . ';"';
		}
		return '';
	}

	/**
	 * get_event_category_bg_color function
	 *
	 * Returns the style attribute assigning the category color style to an event.
	 *
	 * @param int $term_id The Event Category's term ID
	 * @param bool $allday Whether the event is all-day
	 * @return string
	 **/
	function get_event_category_bg_color( $term_id ) {
		$color = $this->get_category_color( $term_id );
		if( ! is_null( $color ) && ! empty( $color ) ) {
			return 'style="background-color: ' . $color . ';"';
		}
		return '';
	}

	/**
	 * Returns a faded version of the event's category color in hex format.
	 *
	 * @param int $term_id The Event Category's term ID
	 *
	 * @return string
	 */
	function get_event_category_faded_color( $term_id ) {
		$color = $this->get_category_color( $term_id );
		if( ! is_null( $color ) && ! empty( $color ) ) {

			$color1 = substr( $color, 1 );
			$color2 = 'ffffff';

			$c1_p1 = hexdec( substr( $color1, 0, 2 ) );
			$c1_p2 = hexdec( substr( $color1, 2, 2 ) );
			$c1_p3 = hexdec( substr( $color1, 4, 2 ) );

			$c2_p1 = hexdec( substr( $color2, 0, 2 ) );
			$c2_p2 = hexdec( substr( $color2, 2, 2 ) );
			$c2_p3 = hexdec( substr( $color2, 4, 2 ) );

			$m_p1 = dechex( round( $c1_p1 * 0.5 + $c2_p1 * 0.5 ) );
			$m_p2 = dechex( round( $c1_p2 * 0.5 + $c2_p2 * 0.5 ) );
			$m_p3 = dechex( round( $c1_p3 * 0.5 + $c2_p3 * 0.5 ) );

			return '#' . $m_p1 . $m_p2 . $m_p3;
		}

		return '';
	}

	/**
	 * Returns the rgba() format of the event's category color, with '%s' in place
	 * of the opacity (to be substituted by sprintf).
	 *
	 * @param int $term_id The Event Category's term ID
	 *
	 * @return string
	 */
	function get_event_category_rgba_color( $term_id ) {
		$color = $this->get_category_color( $term_id );
		if( ! is_null( $color ) && ! empty( $color ) ) {
			$p1 = hexdec( substr( $color, 1, 2 ) );
			$p2 = hexdec( substr( $color, 3, 2 ) );
			$p3 = hexdec( substr( $color, 5, 2 ) );
			return "rgba($p1, $p2, $p3, %s)";
		}

		return '';
	}

	/**
	 * get_event_category_colors function
	 *
	 * Returns category color squares for the list of Event Category objects.
	 *
	 * @param array $cats The Event Category objects as returned by get_terms()
	 * @return string
	 **/
	function get_event_category_colors( $cats ) {
		$sqrs = '';

		foreach( $cats as $cat ) {
			$tmp = $this->get_category_color_square( $cat->term_id );
			if( ! empty( $tmp ) )
				$sqrs .= $tmp;
		}

		return $sqrs;
	}

	/**
	 * create_end_dropdown function
	 *
	 * Outputs the dropdown list for the recurrence end option.
	 *
	 * @param int $selected The index of the selected option, if any
	 * @return void
	 **/
	function create_end_dropdown( $selected = null ) {
		ob_start();

		$options = array(
			0 => __( 'Never', AI1EC_PLUGIN_NAME ),
			1 => __( 'After', AI1EC_PLUGIN_NAME ),
			2 => __( 'On date', AI1EC_PLUGIN_NAME )
		);

		?>
<select name="ai1ec_end" id="ai1ec_end">
			<?php foreach( $options as $key => $val ): ?>
				<option value="<?php echo $key ?>"
		<?php if( $key === $selected ) echo 'selected="selected"' ?>>
					<?php echo $val ?>
				</option>
			<?php endforeach ?>
		</select>
<?php

		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

	/**
	 * Return given recurrence data as text.
	 *
	 * @param  string  $rrule   Recurrence rule
	 *
	 * @return string
	 */
	function rrule_to_text( $rrule = '' ) {
		$txt = '';
		$rc = new SG_iCal_Recurrence( new SG_iCal_Line( 'RRULE:' . $rrule ) );
		switch( $rc->getFreq() ) {
			case 'DAILY':
				$this->_get_interval( $txt, 'daily', $rc->getInterval() );
				$this->_ending_sentence( $txt, $rc );
				break;
			case 'WEEKLY':
				$this->_get_interval( $txt, 'weekly', $rc->getInterval() );
				$this->_get_sentence_by( $txt, 'weekly', $rc );
				$this->_ending_sentence( $txt, $rc );
				break;
			case 'MONTHLY':
				$this->_get_interval( $txt, 'monthly', $rc->getInterval() );
				$this->_get_sentence_by( $txt, 'monthly', $rc );
				$this->_ending_sentence( $txt, $rc );
				break;
			case 'YEARLY':
				$this->_get_interval( $txt, 'yearly', $rc->getInterval() );
				$this->_get_sentence_by( $txt, 'yearly', $rc );
				$this->_ending_sentence( $txt, $rc );
				break;
			default:
				$txt = $rrule;
		}
		return $txt;
	}

	/**
	 * Return given exception dates as text.
	 *
	 * @param  array   $exception_dates Dates to translate
	 *
	 * @return string
	 */
	function exdate_to_text( $exception_dates ) {
		$dates_to_add = array();
		foreach( explode( ",", $exception_dates ) as $_exdate ) {
			// convert to timestamp
			$_exdate = strtotime( $_exdate );
			$dates_to_add[] = $this->get_long_date( $_exdate, true );
		}
		// append dates to the string and return it;
		return implode( ", ", $dates_to_add );
	}

	/**
	 * ics_rule_to_local function
	 *
	 * @return void
	 **/
	function ics_rule_to_local( $rule ) {
		return $this->ics_rule_to( $rule, false );
	}

	/**
	 * ics_rule_to_gmt function
	 *
	 * @return void
	 **/
	function ics_rule_to_gmt( $rule ) {
		return $this->ics_rule_to( $rule, true );
	}

	/**
	 * ics_rule_to function
	 *
	 * @return void
	 **/
	private function ics_rule_to( $rule, $to_gmt = false ) {
		$rc = new SG_iCal_Recurrence( new SG_iCal_Line( 'RRULE:' . $rule ) );
		if( $until = $rc->getUntil() ) {
			if( ! is_int( $until ) ) {
				$until = strtotime( $until );
			}
			if( $to_gmt ) {
				$until = $this->local_to_gmt( $until );
			} else {
				$until = $this->gmt_to_local( $until );
			}

			$until = gmdate( "Ymd\THis\Z", $until );
			$rule_props = explode( ';', $rule );
			$_rule = array();
			foreach( $rule_props as $property ) {
				// don't apply any logic to empty properties
				if( empty( $property ) ) {
					$_rule[] = $property;
					continue;
				}
				$name_and_value = explode( '=', $property );
				if( isset( $name_and_value[0] ) && strtolower( $name_and_value[0] ) == 'until' ) {
					if( isset( $name_and_value[1] ) ) {
						$_rule[] = "UNTIL=" . $until;
					}
				} else {
					$_rule[] = $property;
				}
			}
			$rule = implode( ';', $_rule );
		}
		return $rule;
	}

	/**
	 * exception_dates_to_local function
	 *
	 * @return string
	 **/
	function exception_dates_to_local( $exception_dates ) {
		return $this->exception_dates_to( $exception_dates, false );
	}

	/**
	 * exception_dates_to_gmt function
	 *
	 * @return string
	 **/
	function exception_dates_to_gmt( $exception_dates ) {
		return $this->exception_dates_to( $exception_dates, true );
	}

	/**
	 * add_exception_date method
	 *
	 * Add exception (date) to event.
	 *
	 * @param int   $post_id Event edited post ID
	 * @param mixed $date    Parseable date representation to exclude
	 *
	 * @return bool Success
	 */
	public function add_exception_date( $post_id, $date ) {
		if ( ! is_int( $date ) && ! ctype_digit( $date ) ) {
			$date = strtotime( $date );
		}
		$event        = new Ai1ec_Event( $post_id );
		$dates_list   = explode( ',', $event->exception_dates );
		if ( empty( $dates_list[0] ) ) {
			unset( $dates_list[0] );
		}
		$dates_list[] = gmdate(
			'Ymd\THis\Z',
			$this->local_to_gmt( $date )
		);
		$event->exception_dates = implode( ',', $dates_list );
		return $event->save( true );
	}

	/**
	 * exception_dates_to function
	 *
	 * @return string
	 **/
	private function exception_dates_to( $exception_dates, $to_gmt = false ) {
		$dates_to_add = array();
		foreach( explode( ",", $exception_dates ) as $_exdate ) {
			// convert to timestamp
			$_exdate = strtotime( $_exdate );
			if( $to_gmt ) {
				$_exdate = $this->local_to_gmt( $_exdate );
			} else {
				$_exdate = $this->gmt_to_local( $_exdate );
			}

			$dates_to_add[] = gmdate( "Ymd\THis\Z", $_exdate );
		}
		// append dates to the string and return it;
		return implode( ",", $dates_to_add );
	}

	/**
	 * _get_sentence_by function
	 *
	 * @internal
	 *
	 * @return void
	 **/
	function _get_sentence_by( &$txt, $freq, $rc ) {
		global $wp_locale;

		switch( $freq ) {
			case 'weekly':
				if( $rc->getByDay() ) {
					if( count( $rc->getByDay() ) > 1 ) {
						// if there are more than 3 days
						// use days's abbr
						if( count( $rc->getByDay() ) > 2 ) {
							$_days = '';
							foreach( $rc->getByDay() as $d ) {
								$day = $this->get_weekday_by_id( $d, true );
								$_days .= ' ' . $wp_locale->weekday_abbrev[$wp_locale->weekday[$day]] . ',';
							}
							// remove the last ' and'
							$_days = substr( $_days, 0, -1 );
							$txt .= ' ' . _x( 'on', 'Recurrence editor - weekly tab', AI1EC_PLUGIN_NAME ) . $_days;
						} else {
							$_days = '';
							foreach( $rc->getByDay() as $d ) {
								$day = $this->get_weekday_by_id( $d, true );
								$_days .= ' ' . $wp_locale->weekday[$day] . ' ' . __( 'and', AI1EC_PLUGIN_NAME );
							}
							// remove the last ' and'
							$_days = substr( $_days, 0, -4 );
							$txt .= ' ' . _x( 'on', 'Recurrence editor - weekly tab', AI1EC_PLUGIN_NAME ) . $_days;
						}
					} else {
						$_days = '';
						foreach( $rc->getByDay() as $d ) {
							$day = $this->get_weekday_by_id( $d, true );
							$_days .= ' ' . $wp_locale->weekday[$day];
						}
						$txt .= ' ' . _x( 'on', 'Recurrence editor - weekly tab', AI1EC_PLUGIN_NAME ) . $_days;
					}
				}
				break;
			case 'monthly':
				if( $rc->getByMonthDay() ) {
					// if there are more than 2 days
					if( count( $rc->getByMonthDay() ) > 2 ) {
						$_days = '';
						foreach( $rc->getByMonthDay() as $m_day ) {
							$_days .= ' ' . $this->_ordinal( $m_day ) . ',';
						}
						$_days = substr( $_days, 0, -1 );
						$txt .= ' ' . _x( 'on', 'Recurrence editor - monthly tab', AI1EC_PLUGIN_NAME ) . $_days . ' ' . __( 'of the month', AI1EC_PLUGIN_NAME );
					} else if( count( $rc->getByMonthDay() ) > 1 ) {
						$_days = '';
						foreach( $rc->getByMonthDay() as $m_day ) {
							$_days .= ' ' . $this->_ordinal( $m_day ) . ' ' . __( 'and', AI1EC_PLUGIN_NAME );
						}
						$_days = substr( $_days, 0, -4 );
						$txt .= ' ' . _x( 'on', 'Recurrence editor - monthly tab', AI1EC_PLUGIN_NAME ) . $_days . ' ' . __( 'of the month', AI1EC_PLUGIN_NAME );
					} else {
						$_days = '';
						foreach( $rc->getByMonthDay() as $m_day ) {
							$_days .= ' ' . $this->_ordinal( $m_day );
						}
						$txt .= ' ' . _x( 'on', 'Recurrence editor - monthly tab', AI1EC_PLUGIN_NAME ) . $_days . ' ' . __( 'of the month', AI1EC_PLUGIN_NAME );
					}
				} elseif( $rc->getByDay() ) {
					$_days = '';
					foreach( $rc->getByDay() as $d ) {
						$_dnum  = substr( $d, 0, 1);
						$_day   = substr( $d, 1, 3 );
						$dnum   = ' ' . Ai1ec_Time_Utility::date_i18n(
							'jS',
							strtotime( $_dnum . '-01-1998 12:00:00' )
						);
						$day    = $this->get_weekday_by_id( $_day, true );
						$_days .= ' ' . $wp_locale->weekday[$day];
					}
					$txt .= ' ' . _x( 'on', 'Recurrence editor - monthly tab', AI1EC_PLUGIN_NAME ) . $dnum . $_days;
				}
				break;
			case 'yearly':
				if( $rc->getByMonth() ) {
					// if there are more than 2 months
					if( count( $rc->getByMonth() ) > 2  ) {
						$_months = '';
						foreach( $rc->getByMonth() as $_m ) {
							$_m = $_m < 10 ? 0 . $_m : $_m;
							$_months .= ' ' . $wp_locale->month_abbrev[$wp_locale->month[$_m]] . ',';
						}
						$_months = substr( $_months, 0, -1 );
						$txt .= ' ' . _x( 'on', 'Recurrence editor - yearly tab', AI1EC_PLUGIN_NAME ) . $_months;
					} else if( count( $rc->getByMonth() ) > 1 ) {
						$_months = '';
						foreach( $rc->getByMonth() as $_m ) {
							$_m = $_m < 10 ? 0 . $_m : $_m;
							$_months .= ' ' . $wp_locale->month[$_m] . ' ' . __( 'and', AI1EC_PLUGIN_NAME );
						}
						$_months = substr( $_months, 0, -4 );
						$txt .= ' ' . _x( 'on', 'Recurrence editor - yearly tab', AI1EC_PLUGIN_NAME ) . $_months;
					} else {
						$_months = '';
						foreach( $rc->getByMonth() as $_m ) {
							$_m = $_m < 10 ? 0 . $_m : $_m;
							$_months .= ' ' . $wp_locale->month[$_m];
						}
						$txt .= ' ' . _x( 'on', 'Recurrence editor - yearly tab', AI1EC_PLUGIN_NAME ) . $_months;
					}
				}
				break;
		}
	}

	/**
	 * _ordinal function
	 *
	 * @internal
	 *
	 * @return void
	 **/
	function _ordinal( $cdnl ) {
		$locale = explode( '_', get_locale() );

		if( isset( $locale[0] ) && $locale[0] != 'en' )
			return $cdnl;

		$test_c = abs($cdnl) % 10;
		$ext = ( ( abs( $cdnl ) % 100 < 21 && abs( $cdnl ) % 100 > 4 ) ? 'th'
							: ( ( $test_c < 4 ) ? ( $test_c < 3 ) ? ( $test_c < 2 ) ? ( $test_c < 1 )
							? 'th' : 'st' : 'nd' : 'rd' : 'th' ) );
		return $cdnl.$ext;
	}

	/**
	 * Returns the textual representation of the given recurrence frequency and
	 * interval, with result stored in $txt.
	 *
	 * @internal
	 *
	 * @return void
	 */
	function _get_interval( &$txt, $freq, $interval ) {
		switch( $freq ) {
			case 'daily':
				// check if interval is set
				if( ! $interval || $interval == 1 ) {
					$txt = __( 'daily', AI1EC_PLUGIN_NAME );
				} else {
					if( $interval == 2 ) {
						$txt = __( 'every other day', AI1EC_PLUGIN_NAME );
					} else {
						$txt = sprintf( __( 'every %d days', AI1EC_PLUGIN_NAME ), $interval );
					}
				}
				break;
			case 'weekly':
				// check if interval is set
				if( ! $interval || $interval == 1 ) {
					$txt = __( 'weekly', AI1EC_PLUGIN_NAME );
				} else {
					if( $interval == 2 ) {
						$txt = __( 'every other week', AI1EC_PLUGIN_NAME );
					} else {
						$txt = sprintf( __( 'every %d weeks', AI1EC_PLUGIN_NAME ), $interval );
					}
				}
				break;
			case 'monthly':
				// check if interval is set
				if( ! $interval || $interval == 1 ) {
					$txt = __( 'monthly', AI1EC_PLUGIN_NAME );
				} else {
					if( $interval == 2 ) {
						$txt = __( 'every other month', AI1EC_PLUGIN_NAME );
					} else {
						$txt = sprintf( __( 'every %d months', AI1EC_PLUGIN_NAME ), $interval );
					}
				}
				break;
			case 'yearly':
				// check if interval is set
				if( ! $interval || $interval == 1 ) {
					$txt = __( 'yearly', AI1EC_PLUGIN_NAME );
				} else {
					if( $interval == 2 ) {
						$txt = __( 'every other year', AI1EC_PLUGIN_NAME );
					} else {
						$txt = sprintf( __( 'every %d years', AI1EC_PLUGIN_NAME ), $interval );
					}
				}
				break;
		}
	}

	/**
	 * _ending_sentence function
	 *
	 * Ends rrule to text sentence
	 *
	 * @internal
	 *
	 * @return void
	 **/
	function _ending_sentence( &$txt, &$rc ) {
		if ( $until = $rc->getUntil() ) {
			if ( ! is_int( $until ) ) {
				$until = strtotime( $until );
			}
			$txt .= ' ' . sprintf(
				__( 'until %s', AI1EC_PLUGIN_NAME ),
				Ai1ec_Time_Utility::date_i18n(
					Ai1ec_Meta::get_option( 'date_format' ),
					$until,
					true
				)
			);
		} else if ( $count = $rc->getCount() ) {
			$txt .= ' ' . sprintf(
				__( 'for %d occurrences', AI1EC_PLUGIN_NAME ),
				$count
			);
		} else {
			$txt .= ', ' . __( 'forever', AI1EC_PLUGIN_NAME );
		}
	}

	/**
	 * Convert a recurrence rule to text to display it on screen
	 *
	 * @return void
	 **/
	function convert_rrule_to_text() {
		$error = false;
		$message = '';
		// check to see if RRULE is set
		if ( isset( $_REQUEST["rrule"] ) ) {
			// check to see if rrule is empty
			if ( empty( $_REQUEST["rrule"] ) ) {
				$error = true;
				$message = __( 'Recurrence rule cannot be empty.', AI1EC_PLUGIN_NAME );
			} else {
				// convert rrule to text
				$message = ucfirst( $this->rrule_to_text( $_REQUEST["rrule"] ) );
			}
		} else {
			$error = true;
			$message = __( 'Recurrence rule was not provided.', AI1EC_PLUGIN_NAME );
		}
		$output = array(
			"error" 	=> $error,
			"message"	=> get_magic_quotes_gpc() ? stripslashes( $message ) : $message,
		);

		echo json_encode( $output );
		exit();
	}

	private function add_count_to_rrule_if_not_present( $rrule ) {
		if( false === strpos( 'COUNT', $rrule ) ) {
			$rrule .= "COUNT={$this->max_number_of_cache_entries};";
		}
		return $rrule;
	}

	/**
	 * Filters AI1EC_POST_TYPE permalinks by appending [?&]instance_id= to it.
	 *
	 * @param string  $permalink Original permalink
	 * @param object  $post      Associated post object
	 * @param unknown $leavename Unknown
	 *
	 * @return string
	 */
	function post_type_link( $permalink, $post, $leavename ) {
		if( $post->post_type == AI1EC_POST_TYPE ) {
			$delimiter = Ai1ec_Href_Helper::get_param_delimiter_char( $permalink );
			return $permalink . $delimiter . 'instance_id=';
		}

		return $permalink;
	}

	/**
	 * get_repeat_box function
	 *
	 * @return string
	 **/
	function get_repeat_box() {
		global $ai1ec_view_helper;

		$repeat  = (int) $_REQUEST["repeat"];
		$repeat  = $repeat == 1 ? 1 : 0;
		$post_id = (int) $_REQUEST["post_id"];
		$count   = 100;
		$end     = null;
		$until   = Ai1ec_Time_Utility::current_time( true );

		// try getting the event
		try {
			$event = new Ai1ec_Event( $post_id );
			$rule = '';

			if( $repeat ) {
				$rule = empty( $event->recurrence_rules ) ? '' : $event->recurrence_rules;
			} else {
				$rule = empty( $event->exception_rules )  ? '' : $event->exception_rules;
			}

			$rc = new SG_iCal_Recurrence( new SG_iCal_Line( 'RRULE:' . $rule ) );

			if( $until = $rc->getUntil() ) {
				$until = ( is_numeric( $until ) ) ? $until : strtotime( $until );
			}
			else if( $count = $rc->getCount() ) {
				$count = ( is_numeric( $count ) ) ? $count : 100;
			}
		} catch( Ai1ec_Event_Not_Found $e ) { /* event wasn't found, keep defaults */ }

		$args = array(
			'row_daily'       => $this->row_daily(),
			'row_weekly'      => $this->row_weekly(),
			'row_monthly'     => $this->row_monthly(),
			'row_yearly'      => $this->row_yearly(),
			'count'           => $this->create_count_input( 'ai1ec_count', $count ) . __( 'times', AI1EC_PLUGIN_NAME ),
			'end'             => $this->create_end_dropdown( $end ),
			'until'           => $until,
			'repeat'          => $repeat
		);
		$output = array(
			"error" 	=> false,
			"message"	=> $ai1ec_view_helper->get_admin_view( 'box_repeat.php', $args ),
			"repeat"  => $repeat
		);

		echo json_encode( $output );
		exit();
	}
	/**
	 * get_date_picker_box function
	 *
	 * @return string
	 **/
	function get_date_picker_box() {
		global $ai1ec_view_helper;

		$dates = '';

		$args = array(
			'dates' => $dates
		);

		$output = array(
			"error" 	=> false,
			"message"	=> $ai1ec_view_helper->get_admin_view( 'box_date_picker.php', $args ),
		);

		echo json_encode( $output );
		exit();
	}

	/**
	 * shortcode method
	 *
	 * Generate replacement content for [ai1ec] shortcode.
	 *
	 * @param array	 $atts	  Attributes provided on shortcode
	 * @param string $content Tag internal content (shall be empty)
	 * @param string $tag	  Used tag name (must be 'ai1ec' always)
	 *
	 * @staticvar $call_count Used to restrict to single calendar per page
	 *
	 * @return string Replacement for shortcode entry
	 **/
	function shortcode( $atts, $content = '', $tag = 'ai1ec' ) {
		static $call_count = 0;
		global $ai1ec_settings,
		       $ai1ec_app_helper;
		$view_names = $ai1ec_app_helper->view_names();

		++$call_count;
		if ( $call_count > 1 ) { // not implemented
			return false; // so far process only first request
		}
		$view = $ai1ec_settings->default_calendar_view;
		$categories = $tags = $post_ids = array();
		if ( isset( $atts['view'] ) ) {
			if ( 'ly' === substr( $atts['view'], -2 ) ) {
				$atts['view'] = substr( $atts['view'], 0, -2 );
			}
			if ( ! isset( $view_names[$atts['view']] ) ) {
				return false;
			}
			$view = $atts['view'];
		}

		$mappings = array(
			'cat_name' => 'categories',
			'cat_id'   => 'categories',
			'tag_name' => 'tags',
			'tag_id'   => 'tags',
			'post_id'  => 'post_ids',
		);
		foreach ( $mappings as $att_name => $type ) {
			if ( ! isset( $atts[$att_name] ) ) {
				continue;
			}
			$raw_values = explode( ',', $atts[$att_name] );
			foreach ( $raw_values as $argument ) {
				if ( 'post_id' === $att_name ) {
					if ( ( $argument = (int)$argument ) > 0 ) {
						$post_ids[] = $argument;
					}
				} else {
					if ( ! is_numeric( $argument ) ) {
						$search_val = trim( $argument );
						$argument   = false;
						foreach ( array( 'name', 'slug' ) as $field ) {
							$record = get_term_by(
								$field,
								$search_val,
								'events_' . $type
							);
							if ( false !== $record ) {
								$argument = $record;
								break;
							}
						}
						unset( $search_val, $record, $field );
						if ( false === $argument ) {
							continue;
						}
						$argument = (int)$argument->term_id;
					} else {
						if ( ( $argument = (int)$argument ) <= 0 ) {
							continue;
						}
					}
					${$type}[] = $argument;
				}
			}
		}
		$query = array(
			'ai1ec_cat_ids'	 => implode( ',', $categories ),
			'ai1ec_tag_ids'	 => implode( ',', $tags ),
			'ai1ec_post_ids' => implode( ',', $post_ids ),
			'action'         => $view,
			'request_type'   => 'jsonp',
			'shortcode'      => 'true'
		);
		if( isset( $atts['exact_date'] ) ) {
			$query['exact_date'] = $atts['exact_date'];
		}

		return $this->_get_view_and_restore_globals( $query );
	}

	/**
	 * get_week_start_day_offset function
	 *
	 * Returns the day offset of the first day of the week given a weekday in
	 * question.
	 *
	 * @param int $wday      The weekday to get information about
	 * @return int           A value between -6 and 0 indicating the week start
	 *                       day relative to the given weekday.
	 */
	function get_week_start_day_offset( $wday ) {
		global $ai1ec_settings;

		return - ( 7 - ( $ai1ec_settings->week_start_day - $wday ) ) % 7;
	}

	/**
	 * _get_view_and_restore_globals method
	 *
	 * Set global request ($_REQUEST) variables, call rendering routines
	 * and reset $_REQUEST afterwards.
	 *
	 * @uses do_action				To launch
	 *	'ai1ec_load_frontend_js' action
	 *
	 * @param array $arguments Arguments to set for rendering
	 *
	 * @return string Rendered view
	 */
	protected function _get_view_and_restore_globals( $arguments ) {
		global $ai1ec_calendar_controller, $ai1ec_app_controller, $ai1ec_settings;

		$request = Ai1ec_Routing_Factory::create_argument_parser_instance( $arguments );
		$page_content = $ai1ec_calendar_controller->get_calendar_page( $request );

		// Load requirejs for the calendar
		do_action( 'ai1ec_load_frontend_js', true );

		return $page_content;
	}

	/**
	 * Handle AJAX request to display front-end create event form content.
	 *
	 * @return null
	 */
	public function get_front_end_create_event_form() {
		global $ai1ec_view_helper,
		       $ai1ec_settings;

		$date_format_pattern = Ai1ec_Time_Utility::get_date_pattern_by_key(
			$ai1ec_settings->input_date_format
		);
		$week_start_day      = get_option( 'start_of_week' );
		$input_24h_time      = $ai1ec_settings->input_24h_time;
		$cat_select          = $this->get_html_for_category_selector();
		$tag_select          = $this->get_html_for_tag_selector();
		$form_action         = admin_url(
			'admin-ajax.php?action=ai1ec_front_end_submit_event'
		);
		$default_image       = $ai1ec_view_helper->get_theme_img_url(
			'default-event-avatar.png'
		);

		if (
			! is_user_logged_in() &&
			$ai1ec_settings->allow_anonymous_submissions &&
			$ai1ec_settings->recaptcha_key !== ''
		) {
			$recaptcha_key = $ai1ec_settings->recaptcha_public_key;
		} else {
			$recaptcha_key = false;
		}

		$allow_uploads = is_user_logged_in() ||
			$ai1ec_settings->allow_anonymous_submissions &&
			$ai1ec_settings->allow_anonymous_uploads;

		$args = array(
			'date_format_pattern' => $date_format_pattern,
			'week_start_day'      => $week_start_day,
			'input_24h_time'      => $input_24h_time,
			'cat_select'          => $cat_select,
			'tag_select'          => $tag_select,
			'form_action'         => $form_action,
			'interactive_gmaps'   => ! $ai1ec_settings->disable_autocompletion,
			'default_image'       => $default_image,
			'recaptcha_key'       => $recaptcha_key,
			'allow_uploads'       => $allow_uploads,
		);

		$ai1ec_view_helper->display_theme( 'create-event-form.php', $args );
		exit( 0 );
	}

	/**
	 * Generates the HTML for a category selector.
	 *
	 * @param array $selected_cat_ids Preselected category IDs
	 *
	 * @return string                 Markup for categories selector
	 */
	public function get_html_for_category_selector( $selected_cat_ids = array() ) {
		global $ai1ec_view_helper;

		// Get categories. Add category color info to available categories.
		$categories = get_terms(
			'events_categories',
			array(
				'orderby' => 'name',
				'hide_empty' => 0,
			)
		);
		if ( empty( $categories ) ) {
			return '';
		}
		foreach ( $categories as &$cat ) {
			$cat->color = $this->get_category_color( $cat->term_id );
		}

		$args = array(
			'categories'       => $categories,
			'selected_cat_ids' => $selected_cat_ids,
			'id'               => 'ai1ec_categories',
			'name'             => 'ai1ec_categories[]',
		);
		return $ai1ec_view_helper->get_theme_view( 'categories-select.php', $args );
	}

	/**
	 * Generates the HTML for a tag selector.
	 *
	 * @param array $selected_tag_ids Preselected tag IDs
	 *
	 * @return string                 Markup for tag selector
	 */
	private function get_html_for_tag_selector( $selected_tag_ids = array() ) {
		global $ai1ec_view_helper;

		// Get tags.
		$tags = get_terms(
			'events_tags',
			array(
				'orderby' => 'name',
				'hide_empty' => 0,
			)
		);
		if ( empty( $tags ) ) {
			return '';
		}

		// Build tags array to pass as JSON.
		$tags_json = array();
		foreach ( $tags as $term ) {
			$tags_json[] = $term->name;
		}
		$tags_json = json_encode( $tags_json );
		$tags_json = _wp_specialchars( $tags_json, 'single', 'UTF-8' );

		$args = array(
			'tags_json'        => $tags_json,
			'selected_tag_ids' => implode( ', ', $selected_tag_ids ),
			'id'               => 'ai1ec_tags',
			'name'             => 'ai1ec_tags',
		);
		return $ai1ec_view_helper->get_theme_view( 'tags-select.php', $args );
	}

	/**
	 * Handle AJAX request for submission of front-end create event form.
	 *
	 * @return null
	 */
	public function submit_front_end_create_event_form() {
		global $ai1ec_view_helper,
					 $ai1ec_calendar_helper,
					 $ai1ec_settings,
					 $ai1ec_events_helper;

		$error             = false;
		$html              = '';
		$default_error_msg =
			__( 'There was an error creating your event.', AI1EC_PLUGIN_NAME ) . ' ' .
			__( 'Please try again or contact the site administrator for help.', AI1EC_PLUGIN_NAME );

		$valid = $this->validate_front_end_create_event_form( $message );

		// If valid submission, proceed with event creation.
		if ( $valid ) {
			// Determine post publish status.
			if ( current_user_can( 'publish_ai1ec_events' ) ) {
				$post_status = 'publish';
			} else if ( current_user_can( 'edit_ai1ec_events' ) ) {
				$post_status = 'pending';
			} else if ( $ai1ec_settings->allow_anonymous_submissions ) {
				$post_status = 'pending';
			}

			// Strip slashes if ridiculous PHP setting magic_quotes_gpc is enabled.
			foreach ( $_POST as $param_name => $param ) {
				if (
					'ai1ec' === substr( $param_name, 0, 5 ) &&
					is_scalar( $param )
				) {
					$_POST[$param_name] = stripslashes( $param );
				}
			}

			// Build post array from submitted data.
			$post = array(
				'post_type'    => AI1EC_POST_TYPE,
				'post_author'  => get_current_user_id(),
				'post_title'   => $_POST['post_title'],
				'post_content' => $_POST['post_content'],
				'post_status'  => $post_status,
			);

			// Copy posted event data to new empty event object.
			$event = new Ai1ec_Event();
			$event->post          = $post;
			$event->categories    = isset( $_POST['ai1ec_categories'] )    ? implode( ',', $_POST['ai1ec_categories'] ) : '';
			$event->tags          = isset( $_POST['ai1ec_tags'] )          ? $_POST['ai1ec_tags']                       : '';
			$event->allday        = isset( $_POST['ai1ec_all_day_event'] ) ? (bool) $_POST['ai1ec_all_day_event']       : 0;
			$event->instant_event = isset( $_POST['ai1ec_instant_event'] ) ? (bool) $_POST['ai1ec_instant_event']       : 0;
			$event->start         = isset( $_POST['ai1ec_start_time'] )    ? $_POST['ai1ec_start_time']                 : '';
			if( $event->instant_event ) {
				$event->end         = $event->start + 1800;
			} else {
				$event->end         = isset( $_POST['ai1ec_end_time'] )      ? $_POST['ai1ec_end_time']                   : '';
			}
			$event->address       = isset( $_POST['ai1ec_address'] )       ? $_POST['ai1ec_address']                    : '';
			$event->show_map      = isset( $_POST['ai1ec_google_map'] )    ? (bool) $_POST['ai1ec_google_map']          : 0;

			$scalar_field_list = array(
				'ai1ec_venue'         => FILTER_SANITIZE_STRING,
				'ai1ec_cost'          => FILTER_SANITIZE_STRING,
				'ai1ec_ticket_url'    => FILTER_VALIDATE_URL,
				'ai1ec_contact_name'  => FILTER_SANITIZE_STRING,
				'ai1ec_contact_phone' => FILTER_SANITIZE_STRING,
				'ai1ec_contact_email' => FILTER_VALIDATE_EMAIL,
				'ai1ec_contact_url'   => FILTER_VALIDATE_URL,
			);
			foreach ( $scalar_field_list as $scalar_field => $field_filter ) {
				$scalar_value = filter_input(
					INPUT_POST,
					$scalar_field,
					$field_filter
				);
				if ( ! empty( $scalar_value ) ) {
					$use_name         = substr( $scalar_field, 6 );
					$event->$use_name = $scalar_value;
				}
			}

			// Save the event to the database.
			try {
				$event->save();
				$ai1ec_events_helper->cache_event( $event );

				// Check if uploads are enabled and there is an uploaded file.
				if ( ( is_user_logged_in() ||
				       $ai1ec_settings->allow_anonymous_submissions &&
				       $ai1ec_settings->allow_anonymous_uploads ) &&
				     ! empty( $_FILES['ai1ec_image']['name'] ) ) {
					require_once( ABSPATH . 'wp-admin/includes/image.php' );
					require_once( ABSPATH . 'wp-admin/includes/file.php' );
					require_once( ABSPATH . 'wp-admin/includes/media.php' );
					$attach_id = media_handle_upload( 'ai1ec_image', $event->post_id );
					if ( is_int( $attach_id ) ) {
						update_post_meta( $event->post_id, '_thumbnail_id', $attach_id );
					}
				}

				if ( current_user_can( 'publish_ai1ec_events' ) ) {
					$message   = sprintf(
						__( 'Thank you for your submission. Your event <em>%s</em> was published successfully.', AI1EC_PLUGIN_NAME ),
						$post['post_title']
					);
					$link_text = __( 'View Your Event', AI1EC_PLUGIN_NAME );
					$link_url  = get_permalink( $event->post_id );
				} else {
					$message   = sprintf(
						__( 'Thank you for your submission. Your event <em>%s</em> will be reviewed and published once approved.', AI1EC_PLUGIN_NAME ),
						$post['post_title']
					);
					$link_text = __( 'Back to Calendar', AI1EC_PLUGIN_NAME );
					$link_url  = $ai1ec_calendar_helper->get_calendar_url();
				}
			}
			catch ( Exception $e ) {
				trigger_error(
					sprintf(
						__( 'There was an error during event creation: %s', AI1EC_PLUGIN_NAME ),
						$e->getMessage()
					),
					E_USER_WARNING
				);
				$error = true;
				$message = $default_error_msg;
			}

			$args = array(
				'message_type' => $error ? 'error' : 'success',
				'message'      => $message,
				'link_text'    => $link_text,
				'link_url'     => $link_url,
			);

			$html = $ai1ec_view_helper->get_theme_view(
				'create-event-message.php',
				$args
			);
		}
		// Form submission was invalid.
		else {
			$error = true;
		}

		$response = array(
			'error'   => $error,
			'message' => $message,
			'html'    => $html,
		);

		$ai1ec_view_helper->xml_response( $response );
	}

	/**
	 * Performs a captcha check
	 *
	 * @return array
	 */
	public function check_captcha() {
		global $ai1ec_settings;
		$response = array( 'success' => true );
		if ( empty( $_POST['recaptcha_challenge_field'] ) ||
			empty( $_POST['recaptcha_response_field'] ) ) {
			$response['message'] = __( 'There was an error reading the word verification data. Please try again.', AI1EC_PLUGIN_NAME );
			$response['success'] = false;
		}

		require_once( AI1EC_LIB_PATH . '/recaptcha/recaptchalib.php' );
		$resp = recaptcha_check_answer(
			$ai1ec_settings->recaptcha_private_key,
			$_SERVER["REMOTE_ADDR"],
			$_POST["recaptcha_challenge_field"],
			$_POST["recaptcha_response_field"]
		);

		if ( ! $resp->is_valid ) {
			$response['message'] = __( 'Please try answering the word verification again.', AI1EC_PLUGIN_NAME );
			$response['success'] = false;
		}
		return $response;
	}

	/**
	 * Checks if the current front-end create event form submission is valid.
	 *
	 * @param  string  $message  Error message returned if form is invalid.
	 * @return boolean True if valid, false otherwise
	 */
	private function validate_front_end_create_event_form( &$message ) {
		global $ai1ec_settings;

		// Check nonce.
		if ( isset( $_POST[AI1EC_POST_TYPE] ) &&
		     ! wp_verify_nonce( $_POST[AI1EC_POST_TYPE], 'ai1ec_front_end_form' ) ) {
			$message = __( 'Access denied.', AI1EC_PLUGIN_NAME );
			return false;
		}

		// Check CAPTCHA.
		if ( ! is_user_logged_in() &&
		     $ai1ec_settings->allow_anonymous_submissions &&
		     ! empty( $ai1ec_settings->recaptcha_public_key ) ) {

			$response = $this->check_captcha();
			if( false === $response['success'] ) {
				$message = $response['message'];
				return false;
			}
		}

		// Check permission based on settings.
		if ( ! current_user_can( 'edit_ai1ec_events' ) &&
		     ! $ai1ec_settings->allow_anonymous_submissions ) {
			$message = __(
				'You do not have permission to create events.',
				AI1EC_PLUGIN_NAME
			);
			return false;
		}

		// Ensure uploaded file is an image.
		if ( ! empty( $_FILES['ai1ec_image']['name'] ) ) {
			$is_image = 1 === preg_match(
				'/\.(jpg|jpe|jpeg|gif|png)$/i',
				$_FILES['ai1ec_image']['name']
			);
			if ( ! $is_image ) {
				$message = __(
					'Please upload a valid image file.',
					AI1EC_PLUGIN_NAME
				);
				return false;
			}
		}

		return true;
	}
}
// END class
