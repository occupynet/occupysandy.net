<?php
//
//  class-ai1ec-exporter-helper.php
//  all-in-one-event-calendar
//
//  Created by The Seed Studio on 2011-07-13.
//

/**
 * Ai1ec_Exporter_Helper class
 *
 * @package Helpers
 * @author time.ly
 **/
class Ai1ec_Exporter_Helper {
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
	 * Convert an event from a feed into a new Ai1ec_Event object and add it to
	 * the calendar.
	 *
	 * @param Ai1ec_Event $event    Event object
	 * @param vcalendar   $calendar Calendar object
	 * @param bool        $export   States whether events are created for export
	 *
	 * @return void
	 */
	function insert_event_in_calendar(
		Ai1ec_Event $event,
		vcalendar &$calendar,
		$export = false
	) {
		global $ai1ec_events_helper;

		$tz = Ai1ec_Meta::get_option( 'timezone_string' );

		$e = & $calendar->newComponent( 'vevent' );
		$uid = $event->ical_uid ?
			$event->ical_uid : addcslashes( $event->post->guid, "\\;,\n" );
		$e->setProperty( 'uid', $uid );
		$e->setProperty(
			'url',
			get_permalink( $event->post_id )
		);

		// =========================
		// = Summary & description =
		// =========================
		$e->setProperty(
			'summary',
			$this->_sanitize_value(
				html_entity_decode(
					apply_filters( 'the_title', $event->post->post_title ),
					ENT_QUOTES,
					'UTF-8'
				)
			)
		);
		$content = apply_filters( 'the_content', $event->post->post_content );
		$content = str_replace(']]>', ']]&gt;', $content);
		$content = html_entity_decode( $content, ENT_QUOTES, 'UTF-8' );
		// Prepend featured image if available.
		$size = null;
		if ( $img_url = $event->get_post_thumbnail_url( $size ) ) {
			$content = '<div class="ai1ec-event-avatar alignleft"><img src="' .
				esc_attr( $img_url ) . '" width="' . $size[0] . '" height="' .
				$size[1] . '" /></div>' . $content;
		}
		$e->setProperty( 'description', $this->_sanitize_value( $content ) );

		// =====================
		// = Start & end times =
		// =====================
		$dtstartstring = '';
		$dtstart = $dtend = array();
		if ( $event->allday ) {
			$dtstart["VALUE"] = $dtend["VALUE"] = 'DATE';
			// For exporting all day events, don't set a timezone
			if ( $tz && !$export ) {
				$dtstart["TZID"] = $dtend["TZID"] = $tz;
			}

			// For exportin' all day events, only set the date not the time
			if ( $export ) {
				$e->setProperty(
					'dtstart',
					$this->_sanitize_value( gmdate(
						"Ymd",
						$ai1ec_events_helper->gmt_to_local( $event->start )
					) ),
					$dtstart
				);
				$e->setProperty(
					'dtend',
					$this->_sanitize_value( gmdate(
						"Ymd",
						$ai1ec_events_helper->gmt_to_local( $event->end )
					) ),
					$dtend
				);
			} else {
				$e->setProperty(
					'dtstart',
					$this->_sanitize_value(
						gmdate(
							"Ymd\T",
							$ai1ec_events_helper->gmt_to_local( $event->start )
						)
					),
					$dtstart
				);
				$e->setProperty(
					'dtend',
					$this->_sanitize_value( gmdate(
						"Ymd\T",
						$ai1ec_events_helper->gmt_to_local( $event->end )
					) ),
					$dtend
				);
			}
		} else {
			if ( $tz ) {
				$dtstart["TZID"] = $dtend["TZID"] = $tz;
			}
			// This is used later.
			$dtstartstring = gmdate( "Ymd\THis",
				$ai1ec_events_helper->gmt_to_local( $event->start )
			);
			$e->setProperty(
				'dtstart',
				$this->_sanitize_value( $dtstartstring ),
				$dtstart
			);

			$e->setProperty(
				'dtend',
				$this->_sanitize_value( gmdate(
					"Ymd\THis",
					$ai1ec_events_helper->gmt_to_local( $event->end )
				) ),
				$dtend
			);
		}

		// ========================
		// = Latitude & longitude =
		// ========================
		if ( floatval( $event->latitude ) || floatval( $event->longitude ) ) {
			$e->setProperty( 'geo', $event->latitude, $event->longitude );
		}

		// ===================
		// = Venue & address =
		// ===================
		if ( $event->venue || $event->address ) {
			$location = array( $event->venue, $event->address );
			$location = array_filter( $location );
			$location = implode( ' @ ', $location );
			$e->setProperty( 'location', $this->_sanitize_value( $location ) );
		}

		// ==================
		// = Cost & tickets =
		// ==================
		if ( $event->cost ) {
			$e->setProperty( 'X-COST', $this->_sanitize_value( $event->cost ) );
		}
		if ( $event->ticket_url ) {
			$e->setProperty(
				'X-TICKETS-URL',
				$this->_sanitize_value( $event->ticket_url )
			);
		}

		// ====================================
		// = Contact name, phone, e-mail, URL =
		// ====================================
		$contact = array(
			$event->contact_name,
			$event->contact_phone,
			$event->contact_email,
			$event->contact_url,
		);
		$contact = array_filter( $contact );
		$contact = implode( '; ', $contact );
		$e->setProperty( 'contact', $this->_sanitize_value( $contact ) );

		// ====================
		// = Recurrence rules =
		// ====================
		$rrule = array();
		if ( ! empty( $event->recurrence_rules ) ) {
			$rules = array();
			foreach ( explode( ';', $event->recurrence_rules ) as $v) {
				if ( strpos( $v, '=' ) === false ) {
					continue;
				}

				list( $k, $v ) = explode( '=', $v );
				$k = strtoupper( $k );
				// If $v is a comma-separated list, turn it into array for iCalcreator
				switch ( $k ) {
					case 'BYSECOND':
					case 'BYMINUTE':
					case 'BYHOUR':
					case 'BYDAY':
					case 'BYMONTHDAY':
					case 'BYYEARDAY':
					case 'BYWEEKNO':
					case 'BYMONTH':
					case 'BYSETPOS':
						$exploded = explode( ',', $v );
						break;
					default:
						$exploded = $v;
						break;
				}
				// iCalcreator requires a more complex array structure for BYDAY...
				if ( $k == 'BYDAY' ) {
					$v = array();
					foreach ( $exploded as $day ) {
						$v[] = array( 'DAY' => $day );
					}
				} else {
					$v = $exploded;
				}
				$rrule[ $k ] = $v;
			}
		}

		// ===================
		// = Exception rules =
		// ===================
		$exrule = array();
		if ( ! empty( $event->exception_rules ) ) {
			$rules = array();
			foreach ( explode( ';', $event->exception_rules ) as $v) {
				if ( strpos( $v, '=' ) === false ) {
					continue;
				}

				list($k, $v) = explode( '=', $v );
				$k = strtoupper( $k );
				// If $v is a comma-separated list, turn it into array for iCalcreator
				switch ( $k ) {
					case 'BYSECOND':
					case 'BYMINUTE':
					case 'BYHOUR':
					case 'BYDAY':
					case 'BYMONTHDAY':
					case 'BYYEARDAY':
					case 'BYWEEKNO':
					case 'BYMONTH':
					case 'BYSETPOS':
						$exploded = explode( ',', $v );
						break;
					default:
						$exploded = $v;
						break;
				}
				// iCalcreator requires a more complex array structure for BYDAY...
				if ( $k == 'BYDAY' ) {
					$v = array();
					foreach ( $exploded as $day ) {
						$v[] = array( 'DAY' => $day );
					}
				} else {
					$v = $exploded;
				}
				$exrule[ $k ] = $v;
			}
		}

		// add rrule to exported calendar
		if ( ! empty( $rrule ) ) {
			$e->setProperty( 'rrule', $this->_sanitize_value( $rrule ) );
		}
		// add exrule to exported calendar
		if ( ! empty( $exrule ) ) {
			$e->setProperty( 'exrule', $this->_sanitize_value( $exrule ) );
		}

		// ===================
		// = Exception dates =
		// ===================
		// For all day events that use a date as DTSTART, date must be supplied
		// For other other events which use DATETIME, we must use that as well
		// We must also match the exact starting time
		if ( ! empty( $event->exception_dates ) ) {
			foreach( explode( ',', $event->exception_dates ) as $exdate ) {
				if( $event->allday ) {
					// the local date will be always something like 20121122T000000Z
					// we just need the date
					$exdate = substr(
						$ai1ec_events_helper->exception_dates_to_local( $exdate ),
						0,
						8
					);
					$e->setProperty( 'exdate', array( $exdate ), array( 'VALUE' => 'DATE' ) );
				} else {
					$params = array();
					if( $tz ) {
						$params["TZID"] = $tz;
					}
					$exdate = $ai1ec_events_helper->exception_dates_to_local( $exdate );
					// get only the date + T
					$exdate = substr(
						$exdate,
						0,
						9
					);
					// Take the time from
					$exdate .= substr( $dtstartstring, 9 );
					$e->setProperty(
						'exdate',
						array( $exdate ),
						$params
					);
				}
			}
		}
	}

	/**
	 * _sanitize_value method
	 *
	 * Convert value, so it be safe to use on ICS feed. Used before passing to
	 * iCalcreator methods, for rendering.
	 *
	 * @param string $value Text to be sanitized
	 *
	 * @return string Safe value, for use in HTML
	 */
	protected function _sanitize_value( $value ) {
		if ( ! is_scalar( $value ) ) {
			return $value;
		}
		$safe_eol = "\n";
		$value    = strtr(
			trim( $value ),
			array(
				"\r\n" => $safe_eol,
				"\r"   => $safe_eol,
				"\n"   => $safe_eol,
			)
		);
		$value = addcslashes( $value, '\\' );
		return $value;
	}

}
