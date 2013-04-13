<?php

/**
 * Modal class representing an event or an event instance.
 *
 * @author     Timely Network Inc
 * @since      2011.07.13
 *
 * @package    AllInOneEventCalendar
 * @subpackage AllInOneEventCalendar.App.Model
 */
class Ai1ec_Event {
	/**
	 * post class variable
	 *
	 * @var object
	 **/
	var $post;

	/**
	 * post_id class variable
	 *
	 * @var int
	 **/
	var $post_id;

	/**
	 * instance_id class variable
	 *
	 * Uniquely identifies the recurrence instance of this event object. This
	 * may be null.
	 *
	 * @var int|null
	 **/
	var $instance_id;

	/**
	 * start class variable
	 *
	 * @var int
	 **/
	var $start;

	/**
	 * end class variable
	 *
	 * @var int
	 **/
	var $end;

	/**
	 * start_truncated class variable
	 *
	 * Whether this copy of the event was broken up for rendering and the start
	 * time is not its "real" start time.
	 *
	 * @var bool
	 **/
	var $start_truncated;

	/**
	 * end_truncated class variable
	 *
	 * Whether this copy of the event was broken up for rendering and the end
	 * time is not its "real" end time.
	 *
	 * @var bool
	 **/
	var $end_truncated;

	/**
	 * allday class variable
	 *
	 * @var int
	 **/
	var $allday;

	/**
	 * instant_event class variable
	 *
	 * @var int
	 **/
	var $instant_event;

	/**
	 * recurrence_rules class variable
	 *
	 * @var string
	 **/
	var $recurrence_rules;

	/**
	 * exception_rules class variable
	 *
	 * @var string
	 **/
	var $exception_rules;

	/**
	 * recurrence_dates class variable
	 *
	 * @var string
	 **/
	var $recurrence_dates;

	/**
	 * exception_dates class variable
	 *
	 * @var string
	 **/
	var $exception_dates;

	/**
	 * venue class variable
	 *
	 * @var string
	 **/
	var $venue;

	/**
	 * country class variable
	 *
	 * @var string
	 **/
	var $country;

	/**
	 * address class variable
	 *
	 * @var string
	 **/
	var $address;

	/**
	 * city class variable
	 *
	 * @var string
	 **/
	var $city;

	/**
	 * province class variable
	 *
	 * @var string
	 **/
	var $province;

	/**
	 * postal_code class variable
	 *
	 * @var int
	 **/
	var $postal_code;

	/**
	 * show_map class variable
	 *
	 * @var int
	 **/
	var $show_map;

	/**
	 * longitude class variable
	 *
	 * @var int
	 **/
	var $show_coordinates;

	/**
	 * longitude class variable
	 *
	 * @var float
	 **/
	var $longitude;

	/**
	 * latitude class variable
	 *
	 * @var float
	 **/
	var $latitude;

	/**
	 * facebook_eid class variable
	 *
	 * @var bigint
	 **/
	var $facebook_eid;

	/**
	 * facebook_user class variable
	 *
	 * @var bigint
	 **/
	var $facebook_user;

	/**
	 * facebook_status class variable
	 *
	 * @var char
	 **/
	var $facebook_status;

	/**
	 * Event contact information - contact person
	 *
	 * @var string
	 */
	var $contact_name;

	/**
	 * Event contact information - phone number
	 *
	 * @var string
	 */
	var $contact_phone;

	/**
	 * Event contact information - e-mail address
	 *
	 * @var string
	 */
	var $contact_email;

	/**
	 * Event contact information - external URL
	 *
	 * @var string
	 */
	var $contact_url;

	/**
	 * cost class variable
	 *
	 * @var string
	 **/
	var $cost;

	/**
	 * ticket_url class variable
	 *
	 * @var string
	 **/
	var $ticket_url;

	// ====================================
	// = iCalendar feed (.ics) properties =
	// ====================================
	/**
	 * ical_feed_url class variable
	 *
	 * @var string
	 **/
	var $ical_feed_url;

	/**
	 * ical_source_url class variable
	 *
	 * @var string
	 **/
	var $ical_source_url;

	/**
	 * ical_organizer class variable
	 *
	 * @var string
	 **/
	var $ical_organizer;

	/**
	 * ical_contact class variable
	 *
	 * @var string
	 **/
	var $ical_contact;

	/**
	 * ical_uid class variable
	 *
	 * @var string | int
	 **/
	var $ical_uid;

	// ============
	// = Taxonomy =
	// ============
	/**
	 * tags class variable
	 *
	 * Associated event tag names (*not* IDs), joined by commas.
	 *
	 * @var string
	 **/
	var $tags;

	/**
	 * categories class variable
	 *
	 * Associated event category IDs, joined by commas.
	 *
	 * @var string
	 **/
	var $categories;

	/**
	 * feed class variable
	 *
	 * Associated event feed object
	 *
	 * @var string
	 **/
	var $feed;

	/**
	 * category_colors class variable
	 *
	 * @var string
	 **/
	private $category_colors;

	/**
	 * color_style class variable
	 *
	 * @var string
	 **/
	private $color_style;

	/**
	 * category_text_color class variable
	 *
	 * @var string
	 **/
	private $category_text_color;

	/**
	 * category_bg_color class variable
	 *
	 * @var string
	 **/
	private $category_bg_color;

	/**
	 * faded_color class variable
	 *
	 * @var string
	 **/
	private $faded_color;

	/**
	 * rgba_color class variable
	 *
	 * @var string
	 */
	private $rgba_color;

	/**
	 * tags_html class variable
	 *
	 * @var string
	 */
	private $tags_html;

	/**
	 * category_blocks_html class variable
	 *
	 * @var string
	 */
	private $category_blocks_html;

	/**
	 * category_inline_html class variable
	 *
	 * @var string
	 */
	private $category_inline_html;

	/**
	 * @var bool Flag, whereas event is a multiday one
	 */
	private $_is_multiday = NULL;

	/**
	 * The current request object
	 *
	 * @var Ai1ec_Abstract_Query
	 */
	private $request;

	/**
	 * @param Ai1ec_Abstract_Query $request
	 */
	public function set_request( $request ) {
		$this->request = $request;
	}

	/**
	 * __construct function
	 *
	 * Create new event object, using provided data for initialization.
	 *
	 * @param int|array $data  Look up post with id $data, or initialize fields
	 *                         with flat associative array $data containing both
	 *                         post and event fields returned by join query
	 *
	 * @return void
	 **/
	function __construct( $data = null, $instance = false ) {
		global $wpdb;

		if ( $data == null )
			return;

		// ===========
		// = Post ID =
		// ===========
		if ( is_numeric( $data ) ) {
			// ============================
			// = Fetch post from database =
			// ============================
			$post = get_post( $data );

			if ( ! $post || $post->post_status == 'auto-draft' )
				throw new Ai1ec_Event_Not_Found( "Post with ID '$data' could not be retrieved from the database." );

			$left_join  = "";
			$select_sql = "e.post_id, e.recurrence_rules, e.exception_rules, " .
				"e.allday, e.instant_event, e.recurrence_dates, e.exception_dates, " .
				"e.venue, e.country, e.address, e.city, e.province, e.postal_code, " .
				"e.show_map, e.contact_name, e.contact_phone, e.contact_email, " .
				"e.contact_url, e.cost, e.ticket_url, e.ical_feed_url, " .
				"e.ical_source_url, e.ical_organizer, e.ical_contact, e.ical_uid, " .
				"e.longitude, e.latitude, e.show_coordinates, e.facebook_eid, " .
				"e.facebook_status, e.facebook_user, " .
				"GROUP_CONCAT( ttc.term_id ) AS categories, " .
				"GROUP_CONCAT( ttt.term_id ) AS tags ";

			if( $instance != false && is_numeric( $instance ) ) {
				$select_sql .= ", IF( aei.start IS NOT NULL, aei.start, e.start ) as start," .
							   "  IF( aei.start IS NOT NULL, aei.end,   e.end )   as end ";

				$instance = (int) $instance;
				$this->instance_id = $instance;
				$left_join = 	"LEFT JOIN {$wpdb->prefix}ai1ec_event_instances aei ON aei.id = $instance AND e.post_id = aei.post_id ";
			} else {
				$select_sql .= ", e.start as start, e.end as end, e.allday ";
			}
			// =============================
			// = Fetch event from database =
			// =============================
			$query = $wpdb->prepare(
				"SELECT {$select_sql}" .
				"FROM {$wpdb->prefix}ai1ec_events e " .
					"LEFT JOIN $wpdb->term_relationships tr ON e.post_id = tr.object_id " .
					"LEFT JOIN $wpdb->term_taxonomy ttc ON tr.term_taxonomy_id = ttc.term_taxonomy_id AND ttc.taxonomy = 'events_categories' " .
					"LEFT JOIN $wpdb->term_taxonomy ttt ON tr.term_taxonomy_id = ttt.term_taxonomy_id AND ttt.taxonomy = 'events_tags' " .
					"{$left_join}" .
				"WHERE e.post_id = %d " .
				"GROUP BY e.post_id",
				$data );
			$event = $wpdb->get_row( $query );

			if ( $event === null || $event->post_id === null )
				throw new Ai1ec_Event_Not_Found( "Event with ID '$data' could not be retrieved from the database." );

			$event->start = Ai1ec_Time_Utility::from_mysql_date( $event->start );
			$event->end   = Ai1ec_Time_Utility::from_mysql_date( $event->end );

			// ===========================
			// = Assign post to property =
			// ===========================
			$this->post = $post;

			// ==========================
			// = Assign values to $this =
			// ==========================
			foreach( $this as $property => $value ) {
				if( $property != 'post' ) {
				  if( isset( $event->{$property} ) )
					$this->{$property} = $event->{$property};
				}
			}
		}
		// ===================
		// = Post/event data =
		// ===================
		elseif( is_array( $data ) )
		{
			// =======================================================
			// = Assign each event field the value from the database =
			// =======================================================
			foreach( $this as $property => $value ) {
				if ( $property != 'post' && array_key_exists( $property, $data ) ) {
					$this->{$property} = $data[$property];
					unset( $data[$property] );
				}
			}
			if ( isset( $data['post'] ) ) {
				$this->post = (object) $data['post'];
			} else {
				// ========================================
				// = Remaining fields are the post fields =
				// ========================================
				$this->post = (object) $data;
			}
		}
		else {
			throw new Ai1ec_Invalid_Argument( "Argument to constructor must be integer, array or null, not '$data'." );
		}
	}

	/**
	 * Returns timespan expression for the event. Properly handles:
	 * 	- instantaneous events
	 * 	- all-day events
	 * 	- multi-day events
	 * Display of start date can be hidden (non-all-day events only), weekday
	 * only, or full date. All-day status, if any, is enclosed in a
	 * span.ai1ec-allday-badge element.
	 *
	 * @param  string $start_date_display Can be one of 'hidden', 'weekday', 'short', or 'long'.
	 * @return string
	 */
	public function get_timespan_html( $start_date_display = 'long' ) {
		global $ai1ec_events_helper;

		// Makes no sense to hide start date for all-day events, so fix argument
		if ( 'hidden' === $start_date_display && $this->allday ) {
			$start_date_display = 'short';
		}

		// Localize time.
		$start      = $ai1ec_events_helper->gmt_to_local( $this->start );
		$end        = $ai1ec_events_helper->gmt_to_local( $this->end );

		// All-day events need to have their end time shifted by 1 second less
		// to land on the correct day.
		$end_offset = 0;
		if ( $this->allday ) {
			$end_offset = -1;
			$end += $end_offset;
		}

		// Get components of localized time to calculate start & end dates.
		$bits_start = $ai1ec_events_helper->gmgetdate( $start );
		$bits_end   = $ai1ec_events_helper->gmgetdate( $end );

		// Get timestamps of start & end dates without time component.
		$date_start = gmmktime( 0, 0, 0,
			$bits_start['mon'], $bits_start['mday'], $bits_start['year']
		);
		$date_end   = gmmktime( 0, 0, 0,
			$bits_end['mon'], $bits_end['mday'], $bits_end['year']
		);

		// Get start weekday.
		$day_start = Ai1ec_Time_Utility::date_i18n( 'l', $start, true );

		$output = '';

		// Display start date, depending on $start_date_display.
		switch ( $start_date_display ) {
			case 'hidden':
				break;
			case 'weekday':
				$output .= $day_start;
				break;
			default:
				$start_date_display = 'long';
			case 'short':
			case 'long':
				$property = $start_date_display . '_start_date';
				$output .= $this->{'get_' . $property}();
				break;
		}
		// Output start time for non-all-day events.
		if ( ! $this->allday ) {
			if ( 'hidden' !== $start_date_display ) {
				$output .= apply_filters(
					'ai1ec_get_timespan_html_time_separator',
					_x( ' @ ', 'Event time separator', AI1EC_PLUGIN_NAME )
				);
			}
			$output .= $this->get_start_time();
		}

		$instant = $this->instant_event || $this->start == $this->end;
		// Find out if we need to output the end time/date. Do not output it for
		// instantaneous events and all-day events lasting only one day.
		if (
			! (
				$instant ||
				( $this->allday && $date_start === $date_end )
			)
		) {
			$output .= apply_filters(
				'ai1ec_get_timespan_html_date_separator',
				_x( ' – ', 'Event start/end separator', AI1EC_PLUGIN_NAME )
			);

			// If event ends on a different day, output end date.
			if ( $date_start !== $date_end ) {
				$output .= $this->get_long_end_date( $end_offset );
			}

			// Output end time for non-all-day events.
			if ( ! $this->allday ) {
				if ( $date_start !== $date_end ) {
					$output .= apply_filters(
						'ai1ec_get_timespan_html_time_separator',
						_x( ' @ ', 'Event time separator', AI1EC_PLUGIN_NAME )
					);
				}
				$output .= $this->get_end_time();
			}
		}

		$output = esc_html( $output );

		// Add all-day label.
		if ( $this->allday ) {
			$output .= apply_filters(
				'ai1ec_get_timespan_html_allday_badge',
				' <span class="ai1ec-allday-badge">' .
				__( 'all-day', AI1EC_PLUGIN_NAME ) .
				'</span>'
			);
		}

		return $output;
	}

	public function get_uid() {
		static $_blog_url = NULL;
		if ( NULL === $_blog_url ) {
			$_blog_url = bloginfo( 'url' );
		}
		return $this->post_id . '@' . $_blog_url;
	}

	/**
	 * Month view multiday properties
	 */
	public function get_multiday() {
		if ( ! isset( $this->_is_multiday ) ) {
			global $ai1ec_events_helper;
			$this->_is_multiday = (
				$this->end - $this->start >= 24 * 60 * 60
				&&
				$ai1ec_events_helper->get_long_date( $this->start )
				!=
				$ai1ec_events_helper->get_long_date( $this->end - 1 )
			);
		}
		return $this->_is_multiday;
	}

	public function get_multiday_end_day() {
		global $ai1ec_events_helper;
		return $ai1ec_events_helper->get_multiday_end_day( $this->end - 1 );
	}

	/**
	 * Get short-form dates
	 */
	public function get_short_start_time() {
		global $ai1ec_events_helper;
		return $ai1ec_events_helper->get_short_time( $this->start );
	}

	public function get_short_end_time() {
		global $ai1ec_events_helper;
		return $ai1ec_events_helper->get_short_time( $this->end );
	}

	public function get_short_start_date() {
		global $ai1ec_events_helper;
		return $ai1ec_events_helper->get_short_date( $this->start );
	}

	/**
	 * Subtract 1 second so that all-day events'' end date still
	 * falls within the logical duration of days (since the end date
	 * is always midnight of the following day)
	 */
	public function get_short_end_date() {
		global $ai1ec_events_helper;
		return $ai1ec_events_helper->get_short_date( $this->end - 1 );
	}

	/**
	 * Get medium-form dates
	 */
	public function get_start_time() {
		global $ai1ec_events_helper;
		return $ai1ec_events_helper->get_short_time( $this->start );
	}

	public function get_end_time() {
		global $ai1ec_events_helper;
		return $ai1ec_events_helper->get_short_time( $this->end );
	}

	/**
	 * Get long-form times
	 */
	public function get_long_start_time() {
		global $ai1ec_events_helper;
		return $ai1ec_events_helper->get_long_time( $this->start );
	}

	public function get_long_end_time() {
		global $ai1ec_events_helper;
		return $ai1ec_events_helper->get_long_time( $this->end );
	}

	public function get_long_start_date() {
		global $ai1ec_events_helper;
		return $ai1ec_events_helper->get_long_date( $this->start );
	}

	/**
	 * Subtract 1 second so that all-day events' end date still
	 * falls within the logical duration of days' (since the end date
	 * is always midnight of the following day)
	 */
	public function get_long_end_date( $adjust = 0 ) {
		global $ai1ec_events_helper;
		return $ai1ec_events_helper->get_long_date( $this->end + $adjust );
	}

	/**
	 * Get excerpt of post content for display in popup view
	 */
	public function get_post_excerpt() {
		if (
			! isset( $this->post->post_excerpt ) ||
			empty( $this->post->post_excerpt )
		) {
			$content = strip_tags(
				strip_shortcodes(
					apply_filters( 'the_content', $this->post->post_content )
				)
			);
			$content = preg_replace( '/\s+/', ' ', $content );
			$words = explode( ' ', $content );
			if ( count( $words ) > 25 ) {
				$this->post->post_excerpt = implode(
					' ',
					array_slice( $words, 0, 25 )
				) . ' [...]';
			} else {
				$this->post->post_excerpt = $content;
			}
		}
		return $this->post->post_excerpt;
	}

	/*
	 * Return any available location details separated by newlines
	 */
	public function get_location() {
		$location = '';
		if ( $this->venue ) {
			$location .= "$this->venue\n";
		}
		if ( $this->address ) {
			$bits = explode( ',', $this->address );
			$bits = array_map( 'trim', $bits );

			// If more than three comma-separated values, treat first value as
			// the street address, last value as the country, and everything
			// in the middle as the city, state, etc.
			if ( count( $bits ) >= 3 ) {
				// Append the street address
				$street_address = array_shift( $bits ) . "\n";
				if ( $street_address ) {
					$location .= $street_address;
				}
				// Save the country for the last line
				$country = array_pop( $bits );
				// Append the middle bit(s) (filtering out any zero-length strings)
				$bits = array_filter( $bits, 'strval' );
				if ( $bits ) {
					$location .= join( ',', $bits ) . "\n";
				}
				if ( $country ) {
					$location .= $country . "\n";
				}
			} else {
				// There are two or less comma-separated values, so just append
				// them each on their own line (filtering out any zero-length strings)
				$bits      = array_filter( $bits, 'strval' );
				$location .= join( "\n", $bits );
			}
		}
		return $location;
	}

	/**
	 * Return location details in brief format, separated by | characters.
	 *
	 * @return $string Short location string
	 */
	public function get_short_location() {
		$location_items = array();
		foreach ( array( 'venue', 'city', 'province', 'country' ) as $field ) {
			if ( $this->$field !== '' ) {
				$location_items[] = $this->$field;
			}
		}
		return implode( ' | ', $location_items );
	}

	/**
	 * Categories as HTML, either as blocks or inline.
	 *
	 * @param   string $format      Return 'blocks' or 'inline' formatted result
	 * @return  string              String of HTML for category blocks
	 */
	public function get_categories_html( $format = 'blocks' ) {
		$cache_var = 'category_' . $format . '_html';
		if ( NULL === $this->$cache_var ) {
			global $ai1ec_calendar_controller, $ai1ec_events_helper;
			$categories = wp_get_post_terms(
				 $this->post_id,
				 'events_categories'
			);
			foreach ( $categories as &$category ) {
				$href = Ai1ec_View_Factory::create_href_helper_instance(
					array( 'cat_ids' => $category->term_id )
				);
				if ( isset( $this->request ) ) {
					$view_args = $ai1ec_calendar_controller
						->get_view_args_for_view( $this->request );
					$view_args['cat_ids'] = $category->term_id;
					$href = Ai1ec_View_Factory::create_href_helper_instance(
						$view_args
					);
				}
				$class = '';
				$data_type = '';
				if ( isset( $this->request ) ) {
					$class     = 'ai1ec-load-view';
					$data_type = $view_args['data_type'];
				}
				$title = '';
				if ( $category->description ) {
					$title = 'title="' .
						esc_attr( $category->description ) . '" ';
				}

				$html = '';
				$class .= ' ai1ec-category';
				$color_style = '';
				if ( $format === 'inline' ) {
					$color_style = $ai1ec_events_helper->get_category_color(
						$category->term_id
					);
					if ( $color_style !== '' ) {
						$color_style = 'style="color: ' . $color_style . ';" ';
					}
					$class .= '-inline';
				}

				$html .= '<a ' . $data_type . ' class="' . $class .
					' ai1ec-term-id-' . $category->term_id . '" ' .
					$title . $color_style . 'href="' . $href->generate_href() . '">';

				if ( $format === 'blocks' ) {
					$html .= $ai1ec_events_helper->get_category_color_square(
						$category->term_id
					) . ' ';
				}
				else {
					$html .=
						'<i ' . $color_style . 'class="icon-folder-open"></i>';
				}

				$html .= esc_html( $category->name ) . '</a>';
				$category = $html;
			}
			$this->$cache_var = join( ' ', $categories );
		}
		return $this->$cache_var;
	}

	/**
	 * Tags as HTML
	 */
	public function get_tags_html() {
		if ( NULL === $this->tags_html ) {
			global $ai1ec_calendar_controller;
			$tags = wp_get_post_terms(
				$this->post_id,
				'events_tags'
			);
			foreach ( $tags as &$tag ) {
				$href = Ai1ec_View_Factory::create_href_helper_instance(
					array( 'tag_ids' => $tag->term_id )
				);
				if ( isset( $this->request ) ) {
					$view_args = $ai1ec_calendar_controller
						->get_view_args_for_view( $this->request );
					$view_args['tag_ids'] = $tag->term_id;
					$href = Ai1ec_View_Factory::create_href_helper_instance(
						$view_args
					);
				}
				$class = '';
				$data_type = '';
				if ( isset( $this->request ) ) {
					$class     = 'ai1ec-load-view';
					$data_type = $view_args['data_type'];;
				}
				$title = '';
				if ( $tag->description ) {
					$title = 'title="' . esc_attr( $tag->description ) . '" ';
				}
				$tag = '<a ' . $data_type . ' class="ai1ec-tag ' . $class .
					' ai1ec-term-id-' . $tag->term_id . '" ' . $title .
					'href="' . $href->generate_href() . '">' .
					'<i class="icon-tag"></i>' . esc_html( $tag->name ) . '</a>';
			}
			$this->tags_html = join( ' ', $tags );
		}
		return $this->tags_html;
	}

	/**
	 * Style attribute for event category
	 */
	public function get_color_style() {
		if ( NULL === $this->color_style ) {
			global $ai1ec_events_helper;
			$categories = wp_get_post_terms(
				$this->post_id,
				'events_categories'
			);
			if ( $categories && ! empty( $categories ) ) {
				$this->color_style = $ai1ec_events_helper
					->get_event_category_color_style(
						$categories[0]->term_id,
						$this->allday || $this->get_multiday()
					);
			}
		}
		return $this->color_style;
	}

	/**
	 * Style attribute for event bg color
	 */
	public function get_category_bg_color() {
		if ( NULL === $this->category_bg_color ) {
			global $ai1ec_events_helper;
			$categories = wp_get_post_terms(
				$this->post_id,
				'events_categories'
			);
			if ( $categories && ! empty( $categories ) ) {
				$this->category_bg_color = $ai1ec_events_helper
					->get_event_category_bg_color(
						$categories[0]->term_id,
						$this->allday || $this->get_multiday()
					);
			}
		}
		return $this->category_bg_color;
	}

	/**
	 * Style attribute for event bg color
	 */
	public function get_category_text_color() {
		if ( NULL === $this->category_text_color ) {
			global $ai1ec_events_helper;
			$categories = wp_get_post_terms(
				$this->post_id,
				'events_categories'
			);
			if ( $categories && ! empty( $categories ) ) {
				$this->category_text_color = $ai1ec_events_helper
					->get_event_category_text_color(
						$categories[0]->term_id,
						$this->allday || $this->get_multiday()
					);
			}
		}
		return $this->category_text_color;
	}

	/**
	 * Faded version of event category color
	 */
	public function get_faded_color() {
		if ( NULL === $this->faded_color ) {
			global $ai1ec_events_helper;
			$categories = wp_get_post_terms( $this->post_id, 'events_categories' );
			if ( $categories && ! empty( $categories ) ) {
				$this->faded_color = $ai1ec_events_helper
					->get_event_category_faded_color( $categories[0]->term_id );
			}
		}
		return $this->faded_color;
	}

	/**
	 * rgba() format of faded category color.
	 *
	 * @return  string
	 */
	public function get_rgba_color() {
		if ( NULL === $this->rgba_color ) {
			global $ai1ec_events_helper;
			$categories = wp_get_post_terms(
				$this->post_id,
				'events_categories'
			);
			if ( $categories && ! empty( $categories ) ) {
				$this->rgba_color = $ai1ec_events_helper
					->get_event_category_rgba_color( $categories[0]->term_id );
			}
		}
		return $this->rgba_color;
	}

	/**
	 * HTML of category color boxes for this event
	 */
	public function get_category_colors() {
		if ( NULL === $this->category_colors ) {
			global $ai1ec_events_helper;
			$categories = wp_get_post_terms(
				$this->post_id,
				'events_categories'
			);
			$this->category_colors = $ai1ec_events_helper
				->get_event_category_colors( $categories );
		}
		return $this->category_colors;
	}

	/**
	 * Contact info as HTML
	 */
	public function get_contact_html() {
		$contact = '';
		if ( $this->contact_name ) {
			$contact .=
				'<span class="ai1ec-contact-name"><i class="icon-user"></i>' .
				esc_html( $this->contact_name ) .
				'</span>';
		}
		if ( $this->contact_phone ) {
			$contact .=
				'<span class="ai1ec-contact-phone"><i class="icon-phone"></i>' .
				esc_html( $this->contact_phone ) .
				'</span>';
		}
		if ( $this->contact_email ) {
			$contact .=
				'<span class="ai1ec-contact-email">' .
				'<a href="mailto:' . esc_attr( $this->contact_email ) . '"><i class="icon-envelope-alt"></i>' .
				__( 'E-mail', AI1EC_PLUGIN_NAME ) . '</a></span>';
		}
		if ( $this->contact_url ) {
			$contact .=
				'<span class="ai1ec-contact-url">' .
				'<a target="_blank" href="' . esc_attr( $this->contact_url ) . '"><i class="icon-link"></i>' .
				__( 'Event website', AI1EC_PLUGIN_NAME ) .
				' <i class="icon-external-link"></i></a></span>';
		}
		return $contact;
	}

	/**
	 * Get recurrence info as text.
	 *
	 * @return string
	 */
	public function get_recurrence_html() {
		if ( empty( $this->recurrence_rules ) ) {
			return NULL;
		}
		global $ai1ec_events_helper;
		return
			esc_html(
				$ai1ec_events_helper->rrule_to_text( $this->recurrence_rules )
			);
	}

	/**
	 * Get recurrence exclusion as text.
	 *
	 * @return string
	 */
	public function get_exclude_html() {
		global $ai1ec_events_helper;

		$excludes = array();
		if ( $this->exception_rules ) {
			$excludes[] =
				$ai1ec_events_helper->rrule_to_text( $this->exception_rules );
		}
		if ( $this->exception_dates ) {
			$excludes[] =
				$ai1ec_events_helper->exdate_to_text( $this->exception_dates );
		}
		return
			esc_html( implode( __( ', and ', AI1EC_PLUGIN_NAME ), $excludes ) );
	}

	/**
	 * __get function
	 *
	 * Magic get function
	 * Shortcuts for common formatted versions of event data.
	 *
	 * @param string $name Property name
	 *
	 * @return mixed Property value
	 *
	 * @throws E_USER_WARNING Always, because this method is deprecated and will be removed
	 */
	public function __get( $name ) {
		$method = 'get_' . $name;
		trigger_error(
			'Call to Ai1ec_Event::__get( ' .
				var_export( $name, true ) .
				' ) is deprecated',
			E_USER_WARNING
		);
		if ( ! method_exists( $this, $method ) ) {
			throw new Ai1ec_Invalid_Argument(
				'Variable ' . $name . ' not known'
			);
		}
		return $this->$method();
	}

	/**
	 * Read post meta for post-thumbnail and return its URL as a string.
	 *
	 * @param   null       $size           (width, height) array of returned image
	 *
	 * @return  string|null
	 */
	public function get_post_thumbnail_url( &$size = null ) {
		// Since WP does will return null if the wrong size is targeted,
		// we iterate over an array of sizes, breaking if a URL is found.
		$ordered_img_sizes = array( 'medium', 'large', 'full' );
		foreach ( $ordered_img_sizes as $size ) {
			$attributes = wp_get_attachment_image_src(
				get_post_thumbnail_id( $this->post_id ), $size
			);
			if ( $attributes ) {
				$url = array_shift( $attributes );
				$size = $attributes;
				break;
			}
		}

		return empty( $url ) ? null : $url;
	}

	/**
	 * Simple regex-parse of post_content for matches of <img src="foo" />; if
	 * one is found, return its URL.
	 *
	 * @param   null       $size           (width, height) array of returned image
	 *
	 * @return  string|null
	 */
	public function get_content_img_url( &$size = null ) {
		preg_match(
			'/<img([^>]+)src=["\']?([^"\'\ >]+)([^>]*)>/i',
			$this->post->post_content,
			$matches
		);
		// Check if we have a result, otherwise a notice is issued.
		if ( empty( $matches ) ) {
			return null;
		}

		$url = $matches[2];
		$size = array( 0, 0 );

		// Try to detect width and height.
		$attrs = $matches[1] . $matches[3];
		$matches = null;
		preg_match_all(
			'/(width|height)=["\']?(\d+)/i',
			$attrs,
			$matches,
			PREG_SET_ORDER
		);
		// Check if we have a result, otherwise a notice is issued.
		if ( ! empty( $matches ) ) {
			foreach ( $matches as $match ) {
				$size[ $match[1] === 'width' ? 0 : 1 ] = $match[2];
			}
		}

		return $url;
	}

	/**
	 * Returns avatar image for event's location, if any.
	 *
	 * @param   null       $size           (width, height) array of returned image
	 *
	 * @return  string|null
	 */
	public function get_location_avatar_url( &$size = null ) {
		// TODO: Add support for location avatars.
		return null;
	}

	/**
	 * Returns avatar image for event's deepest category, if any.
	 *
	 * @param   null       $size           (width, height) array of returned image
	 *
	 * @return  string|null
	 */
	public function get_category_avatar_url( &$size = null ) {
		global $ai1ec_tax_meta_class, $ai1ec_app_controller;

		$terms = get_the_terms( $this->post_id, 'events_categories' );

		if ( empty( $terms ) ) {
			return null;
		}

		$terms_by_id = array();
		// Key $terms by term_id rather than arbitrary int.
		foreach ( $terms as $term ) {
			$terms_by_id[$term->term_id] = $term;
		}

		// Array to store term depths, sorted later.
		$term_depths = array();
		foreach ( $terms_by_id as $term ) {
			$depth = 0;
			$ancestor = $term;
			while ( ! empty( $ancestor->parent ) ) {
				$depth++;
				if ( ! isset( $terms_by_id[$ancestor->parent] ) ) {
					break;
				}
				$ancestor = $terms_by_id[$ancestor->parent];
			}
			// Store negative depths for asort() to order from deepest to shallowest.
			$term_depths[$term->term_id] = -$depth;
		}

		// Order term IDs by depth.
		asort( $term_depths );

		// Starting at deepest depth, find the first category that has an avatar.
		foreach ( $term_depths as $term_id => $depth ) {
			$cat_img_meta = $ai1ec_tax_meta_class->get_tax_meta(
				$term_id,
				'ai1ec_image_field_id',
				'events_categories'
			);

			if ( isset( $cat_img_meta['id'] ) ) {
				$ordered_img_sizes = array( 'medium', 'large', 'full' );
				foreach ( $ordered_img_sizes as $size ) {
					$attributes = wp_get_attachment_image_src(
						$cat_img_meta['id'],
						$size
					);
					if ( $attributes ) {
						$url = array_shift( $attributes );
						$size = $attributes;
						break;
					}
				}
				break;
			}
		}

		return empty( $url ) ? null : $url;
	}

	/**
	 * Returns default avatar image (normally when no other ones are available).
	 *
	 * @param   null       $size           (width, height) array of returned image
	 *
	 * @return  string|null
	 */
	public function get_default_avatar_url( &$size = null ) {
		global $ai1ec_view_helper;

		$size = array( 256, 256 );
		return $ai1ec_view_helper->get_theme_img_url( 'default-event-avatar.png' );
	}

	/**
	 * Get the post's "avatar" image url according conditional fallback model.
	 *
	 * Accepts an ordered array of named methods for $fallback order. Returns
	 * image URL or null if no image found. Also returns matching fallback in the
	 * $source reference.
	 *
	 * @param   array|null $fallback_order Order of fallbacks in search for images
	 * @param   null       $source         Fallback that returned matching image,
	 *                                     returned format is string
	 * @param   null       $size           (width, height) array of returned image
	 *
	 * @return  string|null
	 */
	public function get_event_avatar_url(
		$fallback_order = null,
		&$source        = null,
		&$size          = null
	) {
		if ( empty( $fallback_order ) ) {
			$fallback_order = array(
				'post_thumbnail',
				'content_img',
				'location_avatar',
				'category_avatar',
				'default_avatar',
			);
		}

		$valid_fallbacks = array(
			'post_thumbnail'      => 'get_post_thumbnail_url',
			'content_img'         => 'get_content_img_url',
			'location_avatar'     => 'get_location_avatar_url',
			'category_avatar'     => 'get_category_avatar_url',
			'default_avatar'      => 'get_default_avatar_url',
		);

		foreach ( $fallback_order as $fallback ) {
			if ( ! array_key_exists( $fallback, $valid_fallbacks ) ) {
				continue;
			}

			$function = $valid_fallbacks[$fallback];
			$url      = $this->$function( $size );
			if ( $url != null ) {
				$source = $fallback;
				break;
			}
		}

		return empty( $url ) ? null : $url;
	}

	/**
	 * Get HTML markup for the post's "avatar" image according conditional
	 * fallback model.
	 *
	 * Accepts an ordered array of named avatar $fallbacks. Also accepts a string
	 * of space-separated classes to add to the default classes.
	 *
	 * @param   array|null  $fallback_order Order of fallback in searching for
	 *                                      images, or null to use default
	 * @param   string      $classes        A space-separated list of CSS classes
	 *                                      to apply to the outer <div> element.
	 * @param   boolean     $wrap_permalink Whether to wrap the element in a link
	 *                                      to the event details page.
	 *
	 * @return  string                   String of HTML if image is found
	 */
	public function get_event_avatar(
		$fallback_order = null,
		$classes = '',
		$wrap_permalink = true
	) {
		$source = $size = null;
		$url = $this->get_event_avatar_url( $fallback_order, $source, $size );

		if ( empty( $url ) ) {
			return '';
		}

		$url = esc_attr( $url );
		$classes = esc_attr( $classes );

		// Set the alt tag (helpful for SEO).
		$alt = $this->post->post_title;
		$location = $this->get_short_location();
		if ( ! empty( $location ) ) {
			$alt .= ' @ ' . $location;
		}

		$alt = esc_attr( $alt );
		$size_attr = $size[0] ? "width=\"$size[0]\" height=\"$size[1]\"" : "";
		$html = '<img src="' . $url . '" alt="' . $alt . '" ' . $size_attr . ' />';

		if ( $wrap_permalink ) {
			$permalink = get_permalink( $this->post_id ) . $this->instance_id;
			$html = '<a href="' . $permalink . '">' . $html . '</a>';
		}

		$classes .= ' ai1ec-' . $source;
		$classes .= $size[0] > $size[1] ? ' ai1ec-landscape' : ' ai1ec-portrait';
		$html = '<div class="ai1ec-event-avatar ' . $classes . '">' .
			$html . '</div>';

		return $html;
	}

	/**
	 * Returns the correct formatting to store the date on the db depending if its a timestamp or if its a datetime string like '2012-06-25'
	 *
	 * @param mixed string / numeric $date
	 */
	private function return_format_for_dates( $date ) {
		return is_numeric( $date ) ? 'FROM_UNIXTIME( %d )' : '%s';
	}

	/**
	 * Generate the html for the "Back to calendar" button for this event.
	 *
	 * @return string
	 */
	public function get_back_to_calendar_button_html() {
		global $ai1ec_calendar_controller;
		$class = '';
		$data_type = "";
		$href = Ai1ec_View_Factory::create_href_helper_instance( array() );
		$text = __( 'Back to Calendar', AI1EC_PLUGIN_NAME );
		if( isset( $this->request ) ) {
			$data_type = "data-type='jsonp'";
			$class = "ai1ec-load-view";
			$view_args = $ai1ec_calendar_controller->get_view_args_for_view( $this->request );
			$href = Ai1ec_View_Factory::create_href_helper_instance( $view_args );
		}
		$href = $href->generate_href();
		$html = <<<HTML
<a class="ai1ec-calendar-link btn btn-small pull-right $class"
	 href="$href"
	 $data_type>
	<i class="icon-arrow-left"></i> $text
</a>
HTML;
		return $html;
	}

	/**
	 * save function
	 *
	 * Saves the current event data to the database. If $this->post_id exists,
	 * but $update is false, creates a new record in the ai1ec_events table of
	 * this event data, but does not try to create a new post. Else if $update
	 * is true, updates existing event record. If $this->post_id is empty,
	 * creates a new post AND record in the ai1ec_events table for this event.
	 *
	 * @param  bool  $update  Whether to update an existing event or create a
	 *                        new one
	 * @return int            The post_id of the new or existing event.
	 **/
	function save( $update = false ) {
		global $wpdb,
		       $ai1ec_events_helper,
		       $ai1ec_exporter_controller;

		// ===========================
		// = Insert events meta data =
		// ===========================
		// Set facebook user and eid to 0 if they are not set, otherwise they
		// will be set to '' since we use %s for big ints
		$facebook_eid  = isset( $this->facebook_eid ) ? $this->facebook_eid : 0;
		$facebook_user = isset( $this->facebook_user ) ? $this->facebook_user : 0;
		$columns = array(
			'post_id'          => $this->post_id,
			'start'            => Ai1ec_Time_Utility::to_mysql_date( $this->start ),
			'end'              => Ai1ec_Time_Utility::to_mysql_date( $this->end ),
			'allday'           => $this->allday,
			'instant_event'    => $this->instant_event,
			'recurrence_rules' => $this->recurrence_rules,
			'exception_rules'  => $this->exception_rules,
			'recurrence_dates' => $this->recurrence_dates,
			'exception_dates'  => $this->exception_dates,
			'venue'            => $this->venue,
			'country'          => $this->country,
			'address'          => $this->address,
			'city'             => $this->city,
			'province'         => $this->province,
			'postal_code'      => $this->postal_code,
			'show_map'         => $this->show_map,
			'contact_name'     => $this->contact_name,
			'contact_phone'    => $this->contact_phone,
			'contact_email'    => $this->contact_email,
			'contact_url'      => $this->contact_url,
			'cost'             => $this->cost,
			'ticket_url'       => $this->ticket_url,
			'ical_feed_url'    => $this->ical_feed_url,
			'ical_source_url'  => $this->ical_source_url,
			'ical_uid'         => $this->ical_uid,
			'show_coordinates' => $this->show_coordinates,
			'latitude'         => $this->latitude,
			'longitude'        => $this->longitude,
			'facebook_eid'     => $facebook_eid,
			'facebook_user'    => $facebook_user,
			'facebook_status'  => $this->facebook_status,
		);

		$format = array(
			'%d',
			'%s',
			'%s',
			'%d',
			'%d',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%d',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%d',
			'%f',
			'%f',
			'%s',
			'%s',
			'%s',
		);

		$table_name = $wpdb->prefix . 'ai1ec_events';
		if ( $this->post_id ) {
			if ( ! $update ) {
				// =========================
				// = Insert new event data =
				// =========================
				$wpdb->query( $wpdb->prepare(
					"INSERT INTO $table_name ( " .
					join( ', ', array_keys( $columns ) ) .
					" ) VALUES ( " .
					join( ', ', $format ) .
					" )",
					$columns ) );
				$ai1ec_exporter_controller->export_location( $columns, false );
			} else {
				// ==============================
				// = Update existing event data =
				// ==============================
				$where         = array( 'post_id' => $this->post_id );
				$where_escape  = array( '%d'                        );
				$wpdb->update( $table_name, $columns, $where, $format, $where_escape );
				$ai1ec_exporter_controller->export_location( $columns, true );
			}
		} else {
			// ===================
			// = Insert new post =
			// ===================
			$this->post_id = wp_insert_post( $this->post );
			$columns['post_id'] = $this->post_id;
			wp_set_post_terms( $this->post_id, $this->categories, 'events_categories' );
			wp_set_post_terms( $this->post_id, $this->tags, 'events_tags' );

			if ( isset( $this->feed ) && isset( $this->feed->feed_id ) ) {
				$feed_name = $this->feed->feed_url;
				// If the feed is not from an imported file, parse the url.
				if ( ! isset( $this->feed->feed_imported_file ) ) {
					$url_components = parse_url( $this->feed->feed_url );
					$feed_name = $url_components["host"];
				}
				$term = term_exists( $feed_name, 'events_feeds' );
				if ( ! $term ) {
					// term doesn't exist, create it
					$term = wp_insert_term(
						$feed_name,     // term
						'events_feeds', // taxonomy
						array(
							'description' => $this->feed->feed_url
						)
					);
				}
				// term_exists returns object, wp_insert_term returns array
				$term = (object)$term;
				if ( isset( $term->term_id ) ) {
					// associate the event with the feed only if we have term id set
					$a = wp_set_object_terms( $this->post_id, (int)$term->term_id, 'events_feeds', false );
				}
			}

			// =========================
			// = Insert new event data =
			// =========================
			$wpdb->query( $wpdb->prepare(
				"INSERT INTO $table_name ( " .
				join( ', ', array_keys( $columns ) ) .
				" ) VALUES ( " .
				join( ', ', $format ) .
				" )",
				$columns ) );
			$ai1ec_exporter_controller->export_location( $columns, false );
		}

		return $this->post_id;
	}

	/**
	 * getProperty function
	 *
	 * Returns $property value
	 *
	 * @param string $property Property name
	 *
	 * @return mixed
	 **/
	function getProperty( $property ) {
		return $this->property;
	}

	/**
	 * isWholeDay function
	 *
	 * Determines if an event is a whole day event
	 *
	 * @return bool
	 **/
	function isWholeDay() {
		return ( bool ) $this->allday;
	}

	/**
	 * getStart function
	 *
	 * Returns the start time of the event
	 *
	 * @return int
	 **/
	function getStart() {
		return $this->start;
	}

	/**
	 * getEnd function
	 *
	 * Returns the end time of the event
	 *
	 * @return int
	 **/
	function getEnd() {
		return $this->end;
	}

	/**
	 * getFrequency function
	 *
	 * Returns the frequency of the event
	 *
	 * @return object
	 **/
	function getFrequency( $excluded = array() ) {
		return new SG_iCal_Freq( $this->recurrence_rules, $this->start, $excluded );
	}

	/**
	 * getDuration function
	 *
	 * Returns the duration of the event
	 *
	 * @return int
	 **/
	function getDuration() {
		return $this->end - $this->start;
	}
}
// END class
