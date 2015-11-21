<?php

/**
 * Event instance management model.
 *
 *
 * @author       Time.ly Network, Inc.
 * @since        2.0
 * @package      Ai1EC
 * @subpackage   Ai1EC.Model
 */
class Ai1ec_Event_Instance extends Ai1ec_Base {

	/**
	 * @var Ai1ec_Dbi Instance of database abstraction.
	 */
	protected $_dbi = null;

	/**
	 * DBI utils.
	 *
	 * @var Ai1ec_Dbi_Utils
	 */
	protected $_dbi_utils;

	/**
	 * Store locally instance of Ai1ec_Dbi.
	 *
	 * @param Ai1ec_Registry_Object $registry Injected object registry.
	 *
	 */
	public function __construct( Ai1ec_Registry_Object $registry ) {
		parent::__construct( $registry );
		$this->_dbi       = $this->_registry->get( 'dbi.dbi' );
		$this->_dbi_utils = $this->_registry->get( 'dbi.dbi-utils' );
	}

	/**
	 * Remove entries for given post. Optionally delete particular instance.
	 *
	 * @param int      $post_id     Event ID to remove instances for.
	 * @param int|null $instance_id Instance ID, or null for all.
	 *
	 * @return int|bool Number of entries removed, or false on failure.
	 */
	public function clean( $post_id, $instance_id = null ) {
		$where  = array( 'post_id' => $post_id );
		$format = array( '%d' );
		if ( null !== $instance_id ) {
			$where['id'] = $instance_id;
			$format[]    = '%d';
		}
		return $this->_dbi->delete( 'ai1ec_event_instances', $where, $format );
	}

	/**
	 * Remove and then create instance entries for given event.
	 *
	 * @param Ai1ec_Event $event Instance of event to recreate entries for.
	 *
	 * @return bool Success.
	 */
	public function recreate( Ai1ec_Event $event ) {
		$old_instances = $this->_load_instances( $event->get( 'post_id' ) );
		$instances     = $this->_create_instances_collection( $event );
		$insert        = array();
		foreach ( $instances as $instance ) {
			if ( ! isset( $old_instances[$instance['start'] . ':' . $instance['end']] ) ) {
				$insert[] = $instance;
				continue;
			}
			unset( $old_instances[$instance['start'] . ':' . $instance['end']] );
		}
		$this->_remove_instances_by_ids( array_values( $old_instances ) );
		$this->_add_instances( $insert );
		return true;
	}

	/**
	 * Create list of recurrent instances.
	 *
	 * @param Ai1ec_Event $event          Event to generate instances for.
	 * @param array       $event_instance First instance contents.
	 * @param int         $_start         Timestamp of first occurence.
	 * @param int         $duration       Event duration in seconds.
	 * @param string      $timezone       Target timezone.
	 *
	 * @return array List of event instances.
	 */
	public function create_instances_by_recurrence(
		Ai1ec_Event $event,
		array $event_instance,
		$_start,
		$duration,
		$timezone
	) {
		$restore_timezone  = date_default_timezone_get();
		$recurrence_parser = $this->_registry->get( 'recurrence.rule' );
		$events            = array();

		$start             = $event_instance['start'];
		$wdate             = $startdate = $enddate
			= $this->_parsed_date_array( $_start, $timezone );
		$enddate['year']   = $enddate['year'] + 3;
		$exclude_dates	   = array();
		$recurrence_dates  = array();
		if ( $recurrence_dates = $event->get( 'recurrence_dates' ) ) {
			$recurrence_dates  = $this->_populate_recurring_dates(
				$recurrence_dates,
				$startdate,
				$timezone
			);
		}
		if ( $exception_dates = $event->get( 'exception_dates' ) ) {
			$exclude_dates  = $this->_populate_recurring_dates(
				$exception_dates,
				$startdate,
				$timezone
			);
		}
		if ( $event->get( 'exception_rules' ) ) {
			// creat an array for the rules
			$exception_rules = $recurrence_parser
				->build_recurrence_rules_array(
					$event->get( 'exception_rules' )
				);
			unset($exception_rules['EXDATE']);
			if ( ! empty( $exception_rules ) ) {
				$exception_rules = iCalUtilityFunctions::_setRexrule(
					$exception_rules
				);
				$result = array();
				date_default_timezone_set( $timezone );
				// The first array is the result and it is passed by reference
				iCalUtilityFunctions::_recur2date(
					$exclude_dates,
					$exception_rules,
					$wdate,
					$startdate,
					$enddate
				);
				date_default_timezone_set( $restore_timezone );
			}
		}
		$recurrence_rules = $recurrence_parser
			->build_recurrence_rules_array(
				$event->get( 'recurrence_rules' )
			);

		$recurrence_rules = iCalUtilityFunctions::_setRexrule( $recurrence_rules );
		if ( $recurrence_rules ) {
			date_default_timezone_set( $timezone );
			iCalUtilityFunctions::_recur2date(
				$recurrence_dates,
				$recurrence_rules,
				$wdate,
				$startdate,
				$enddate
			);
			date_default_timezone_set( $restore_timezone );
		}

		if ( ! is_array( $recurrence_dates ) ) {
			$recurrence_dates = array();
		}
		$recurrence_dates = array_keys( $recurrence_dates );
		// Add the instances
		foreach ( $recurrence_dates as $timestamp ) {
			// The arrays are in the form timestamp => true so an isset call is what we need
			if ( ! isset( $exclude_dates[$timestamp] ) ) {
				$event_instance['start'] = $timestamp;
				$event_instance['end']	 = $timestamp + $duration;
				$events[$timestamp] = $event_instance;
			}
		}

		return $events;
	}

	/**
	 * Generate and store instance entries in database for given event.
	 *
	 * @param Ai1ec_Event $event Instance of event to create entries for.
	 *
	 * @return bool Success.
	 */
	public function create( Ai1ec_Event $event ) {
		$instances = $this->_create_instances_collection( $event );
		$this->_add_instances( $instances );
		return true;
	}

	/**
	 * Check if given date match dates in EXDATES rule.
	 *
	 * @param string $date     Date to check.
	 * @param string $ics_rule ICS EXDATES rule.
	 * @param string $timezone Timezone to evaluate value in.
	 *
	 * @return bool True if given date is in rule.
	 */
	public function date_match_exdates( $date, $ics_rule, $timezone ) {
		$ranges = $this->_get_date_ranges( $ics_rule, $timezone );
		foreach ( $ranges as $interval ) {
			if ( $date >= $interval[0] && $date <= $interval[1] ) {
				return true;
			}
			if ( $date <= $interval[0] ) {
				break;
			}
		}
		return false;
	}

	/**
	 * Prepare date range list for fast exdate search.
	 *
	 * NOTICE: timezone is relevant in only first run.
	 *
	 * @param string $date_list ICS list provided from data model.
	 * @param string $timezone  Timezone in which to evaluate.
	 *
	 * @return array List of date ranges, sorted in increasing order.
	 */
	protected function _get_date_ranges( $date_list, $timezone ) {
		static $ranges = array();
		if ( ! isset( $ranges[$date_list] ) ) {
			$ranges[$date_list] = array();
			$exploded = explode( ',', $date_list );
			sort( $exploded );
			foreach ( $exploded as $date ) {
				// COMMENT on `rtrim( $date, 'Z' )`:
				// user selects exclusion date in event timezone thus it
				// must be parsed as such as opposed to UTC which happen
				// when 'Z' is preserved.
				$date = $this->_registry
					->get( 'date.time', rtrim( $date, 'Z' ), $timezone )
					->format_to_gmt();
				$ranges[$date_list][] = array(
					$date,
					$date + (24 * 60 * 60) - 1
				);
			}
		}
		return $ranges[$date_list];
	}

	protected function _populate_recurring_dates( $rule, array $start_struct, $timezone ) {
		$start = clone $start_struct['_dt'];
		$dates = array();
		foreach ( explode( ',', $rule ) as $date ) {
			$i_date = clone $start;
			$spec   = sscanf( $date, '%04d%02d%02d' );
			$i_date->set_date(
				$spec[0],
				$spec[1],
				$spec[2]
			);
			$dates[$i_date->format_to_gmt()] = $i_date;
		}
		return $dates;
	}

	protected function _parsed_date_array( $startdate, $timezone ) {
		$datetime = $this->_registry->get( 'date.time', $startdate, $timezone );
		$parsed   = array(
			'year'  => intval( $datetime->format( 'Y' ) ),
			'month' => intval( $datetime->format( 'm' ) ),
			'day'   => intval( $datetime->format( 'd' ) ),
			'hour'  => intval( $datetime->format( 'H' ) ),
			'min'   => intval( $datetime->format( 'i' ) ),
			'sec'   => intval( $datetime->format( 's' ) ),
			'tz'    => $datetime->get_timezone(),
			'_dt'   => $datetime,
		);
		return $parsed;
	}

	/**
	 * Returns current instances map.
	 *
	 * @param int post_id Post ID.
	 *
	 * @return array Array of data.
	 */
	protected function _load_instances( $post_id ) {
		$query = $this->_dbi->prepare(
			'SELECT `id`, `start`, `end` FROM ' .
			$this->_dbi->get_table_name( 'ai1ec_event_instances' ) .
			' WHERE post_id = %d',
			$post_id
		);
		$results   = $this->_dbi->get_results( $query );
		$instances = array();
		foreach ( $results as $result ) {
			$instances[(int)$result->start . ':' . (int)$result->end] = (int)$result->id;
		}
		return $instances;
	}

	/**
	 * Generate and store instance entries in database for given event.
	 *
	 * @param Ai1ec_Event $event Instance of event to create entries for.
	 *
	 * @return bool Success.
	 */
	protected function _create_instances_collection( Ai1ec_Event $event ) {
		$events     = array();
		$event_item = array(
			'post_id' => $event->get( 'post_id' ),
			'start'   => $event->get( 'start'   )->format_to_gmt(),
			'end'     => $event->get( 'end'     )->format_to_gmt(),
		);
		$duration = $event->get( 'end' )->diff_sec( $event->get( 'start' ) );

		$_start = $event->get( 'start' )->format_to_gmt();
		$_end   = $event->get( 'end'   )->format_to_gmt();

		// Always cache initial instance
		$events[$_start] = $event_item;

		if ( $event->get( 'recurrence_rules' ) || $event->get( 'recurrence_dates' ) ) {
			/**
			 * NOTE: this timezone switch is intentional, because underlying
			 * library doesn't allow us to pass it as an argument. Though no
			 * lesser importance shall be given to the restore call bellow.
			 */
			$start_datetime = $event->get( 'start' );
			$start_datetime->assert_utc_timezone();
			$start_timezone = $this->_registry->get( 'date.timezone' )
			                                  ->get_name( $start_datetime->get_timezone() );
			$events += $this->create_instances_by_recurrence(
				$event,
				$event_item,
				$_start,
				$duration,
				$start_timezone
			);
		}

		$search_helper = $this->_registry->get( 'model.search' );
		foreach ( $events as &$event_item ) {
			// Find out if this event instance is already accounted for by an
			// overriding 'RECURRENCE-ID' of the same iCalendar feed (by comparing the
			// UID, start date, recurrence). If so, then do not create duplicate
			// instance of event.
			$start             = $event_item['start'];
			$matching_event_id = null;
			if ( $event->get( 'ical_uid' ) ) {
				$matching_event_id = $search_helper->get_matching_event_id(
					$event->get( 'ical_uid' ),
					$event->get( 'ical_feed_url' ),
					$event->get( 'start' ),
					false,
					$event->get( 'post_id' )
				);
			}

			// If no other instance was found
			if ( null !== $matching_event_id ) {
				$event_item = false;
			}
		}

		return array_filter( $events );
	}

	/**
	 * Removes ai1ec_event_instances entries using their IDS.
	 *
	 * @param array $ids Collection of IDS.
	 *
	 * @return bool Result.
	 */
	protected function _remove_instances_by_ids( array $ids ) {
		if ( empty( $ids ) ) {
			return false;
		}
		$query  = 'DELETE FROM ' . $this->_dbi->get_table_name(
				'ai1ec_event_instances'
			) . ' WHERE id IN (';
		$ids    = array_filter( array_map( 'intval', $ids ) );
		$query .= implode( ',', $ids ) . ')';
		$this->_dbi->query( $query );
		return true;
	}

	/**
	 * Adds new instances collection.
	 *
	 * @param array $instances Collection of instances.
	 *
	 * @return void
	 */
	protected function _add_instances( array $instances ) {
		$chunks    = array_chunk( $instances, 50 );
		foreach ( $chunks as $chunk ) {
			$query = 'INSERT INTO ' . $this->_dbi->get_table_name(
					'ai1ec_event_instances'
				) . '(`post_id`, `start`, `end`) VALUES';
			$chunk  = array_map(
				array( $this->_dbi_utils, 'array_value_to_sql_value' ),
				$chunk
			);
			$query .= implode( ',', $chunk );
			$this->_dbi->query( $query );
		}
	}
}
