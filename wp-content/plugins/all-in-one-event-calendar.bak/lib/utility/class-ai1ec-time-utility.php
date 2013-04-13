<?php

/**
 * Time and date manipulations library.
 *
 * @author     Timely Network Inc
 * @since      2012.10.05
 *
 * @package    AllInOneEventCalendar
 * @subpackage AllInOneEventCalendar.Lib.Utility
 */
class Ai1ec_Time_Utility
{

	/**
	 * @var Ai1ec_Time_Utility Instance of self to save resources
	 */
	static protected $_instance  = NULL;

	/**
	 * @var Ai1ec_Memory_Utility Instance, where DateTimeZone objects are held
	 */
	protected $_timezones         = NULL;

	/**
	 * @var Ai1ec_Memory_Utility Instance, where timezone GMT offsets (in hours)
	 *                           are held
	 */
	protected $_gmt_offsets       = NULL;

	/**
	 * @var Ai1ec_Memory_Utility Instance, where parsed GMT timestamp
	 *                           information is stored
	 */
	protected $_gmtdates          = NULL;

	/**
	 * @var Ai1ec_Time_I18n_Utility Instance of I18n time management utility
	 */
	protected $_time_i18n         = NULL;

	/**
	 * @var array Information about default timezone to speedup access
	 */
	protected $_default_timezone  = NULL;

	/**
	 * @var array Current time UNIX timestamp at 0 and GMT timestamp at 1
	 */
	protected $_current_time      = NULL;

	/**
	 * instance method
	 *
	 * Singleton creation method, to allow saving resources and having single
	 * object instance throughout system.
	 *
	 * @return Ai1ec_Time_Utility Single instance of self
	 */
	static public function instance() {
		if ( ! ( self::$_instance instanceof Ai1ec_Time_Utility ) ) {
			self::$_instance = new Ai1ec_Time_Utility();
		}
		return self::$_instance;
	}

	/**
	 * to_mysql_date method
	 *
	 * Convert UNIX timestamp to date, that may be used within
	 * MySQL `DATE` field.
	 *
	 * @param int $timestamp Timestamp to convert to MySQL date
	 *
	 * @return string MySQL date to use in queries
	 */
	static public function to_mysql_date( $timestamp ) {
		return date( 'Y-m-d H:i:s', $timestamp );
	}

	/**
	 * from_mysql_date method
	 *
	 * Convert date, stored in MySQL `DATE` type field to UNIX timestamp.
	 *
	 * @param string $date Date retrieved from MySQL
	 *
	 * @return int UNIX timestamp decoded from date given
	 */
	static public function from_mysql_date( $date ) {
		return strtotime( $date );
	}

	/**
	 * Returns the associative array of date patterns supported by the plugin,
	 * currently:
	 *   array(
	 *     'def' => 'd/m/yyyy',
	 *     'us'  => 'm/d/yyyy',
	 *     'iso' => 'yyyy-m-d',
	 *     'dot' => 'm.d.yyyy',
	 *   );
	 *
	 * 'd' or 'dd' represent the day, 'm' or 'mm' represent the month, and 'yy'
	 * or 'yyyy' represent the year.
	 *
	 * @return array Supported date patterns
	 */
	static public function get_date_patterns() {
		return array(
			'def' => 'd/m/yyyy',
			'us'  => 'm/d/yyyy',
			'iso' => 'yyyy-m-d',
			'dot' => 'm.d.yyyy',
		);
	}

	/**
	 * Returns the date pattern (in the form 'd-m-yyyy', for example) associated
	 * with the provided key, used by plugin settings. Simply a static map as
	 * follows:
	 *
	 * @param  string $key Key for the date format
	 * @return string      Associated date format pattern
	 */
	static public function get_date_pattern_by_key( $key = 'def' ) {
		$patterns = self::get_date_patterns();
		return $patterns[$key];
	}

	/**
	 * Returns a formatted date given a timestamp, based on the given date format,
	 * with any '/' characters replaced with URL-friendly '-' characters.
	 * @see  Ai1ec_Time_Utility::get_date_patterns() for supported date formats.
	 *
	 * @param  int $timestamp    UNIX timestamp representing a date (in GMT)
	 * @param  string $pattern   Key of date pattern (@see
	 *                           Ai1ec_Time_Utility::get_date_patterns()) to
	 *                           format date with
	 * @return string            Formatted date string
	 */
	static public function format_date_for_url( $timestamp, $pattern = 'def' ) {
		$date = self::format_date( $timestamp, $pattern );
		$date = str_replace( '/', '-', $date );
		return $date;
	}

	/**
	 * Returns a formatted date given a timestamp, based on the given date format.
	 * @see  Ai1ec_Time_Utility::get_date_patterns() for supported date formats.
	 *
	 * @param  int $timestamp    UNIX timestamp representing a date (in GMT)
	 * @param  string $pattern   Key of date pattern (@see
	 *                           Ai1ec_Time_Utility::get_date_patterns()) to
	 *                           format date with
	 * @return string            Formatted date string
	 */
	static public function format_date( $timestamp, $pattern = 'def' ) {
		$pattern = self::get_date_pattern_by_key( $pattern );
		$pattern = str_replace(
			array( 'dd', 'd', 'mm', 'm', 'yyyy', 'yy' ),
			array( 'd', 'j', 'm', 'n', 'Y', 'y' ),
			$pattern
		);
		return gmdate( $pattern, $timestamp );
	}

	/**
	 * get_default_timezone method
	 *
	 * Singleton backed interface method, to get name of system-default
	 * timezone name.
	 *
	 * @return string Name of default timezone
	 *
	 * @throws Ai1ec_Datetime_Exception If default timezone is invalid/undefined
	 */
	static public function get_default_timezone() {
		$_this = self::instance();
		if ( NULL === $_this->_default_timezone ) {
			$name       = date_default_timezone_get();
			$name_entry = array(
				'name'  => $name,
				'valid' => false,
			);
			if ( is_string( $name ) ) {
				$name_entry['valid'] = true;
			}
			$_this->_default_timezone = $name_entry;
			unset( $name, $name_entry );
		}
		if ( ! $_this->_default_timezone['valid'] ) {
			throw new Ai1ec_Datetime_Exception( 'Default timezone undefined' );
		}
		return $_this->_default_timezone['name'];
	}

	/**
	 * get_local_timezone method
	 *
	 * Get timezone used by current user/installation, as local.
	 *
	 * @param string $default Timezone to use, if none is detected
	 *
	 * @return string Timezone identified as local
	 */
	static public function get_local_timezone( $default = 'America/Los_Angeles' ) {
		static $_cached = NULL;
		if ( NULL === $_cached ) {
			$user = wp_get_current_user();
			$zone = '';
			if ( $user->ID > 0 ) {
				global $ai1ec_app_helper;
				$zone = $ai1ec_app_helper->user_selected_tz( $user->ID );
			}
			unset( $user );
			if ( empty( $zone ) ) {
				$zone = get_option( 'timezone_string', $default );
				if ( empty( $zone ) ) {
					$zone = false;
				}
			}
			$_cached = $zone;
			unset( $zone );
		}
		if ( false === $_cached ) {
			return $default;
		}
		return $_cached;
	}

	/**
	 * get_gmt_offset method
	 *
	 * Get timezone offset from GMT, in hours, given time, at which this shall
	 * be evaluated, and, optionally, zone name, which defaults to the name of
	 * local time zone.
	 *
	 * @param int    $timestamp Timestamp to use for evaluation
	 * @param string $zone      Timezone name, from which to calculate offset
	 *
	 * @return float Timezone offset from GMT in hours
	 */
	static public function get_gmt_offset( $timestamp = false, $zone = NULL ) {
		$_this = self::instance();
		if ( NULL === $zone ) {
			$zone = $_this->get_local_timezone();
		}
		if ( NULL === ( $offset = $_this->_gmt_offsets->get( $zone ) ) ) {
			$timezone  = $_this->_get_timezone( $zone );
			$reference = $_this->_date_time_from_timestamp( $timestamp );
			if ( false === $timezone || false === $reference ) {
				$offset = get_option( 'gmt_offset' );
			} else {
				$offset = round( $timezone->getOffset( $reference ) / 3600, 2);
			}
			unset( $timezone, $reference );
			$_this->_gmt_offsets->set( $zone, $offset );
		}
		return $offset;
	}

	/**
	 * get_local_offset_from_gmt method
	 *
	 * Get local timezone offset from GMT in seconds.
	 * NOTICE: this uses assumption, that within the same day the timezone will
	 * remain the same (the DST happens after midnight, so any timezone should
	 * round over to it).
	 *
	 * @param int $timestamp Timestamp at which difference must be evaluated
	 *
	 * @return int Local timezone offset from GMT
	 *
	 * @staticvar Ai1ec_Memory_Utility $offsets Instance of UTC offsets storage
	 *
	 * @throws Ai1ec_Datetime_Exception If local timezone is invalid
	 */
	static public function get_local_offset_from_gmt( $timestamp ) {
		$_this  = self::instance();
		$zone   = $_this->get_local_timezone();
		$offset = false;
		try {
			$offset = $_this->get_timezone_offset( 'UTC', $zone, $timestamp );
		} catch ( Exception $tz_excpt ) {
			try {
				$offset = $this->get_gmt_offset( $timestamp, $zone ) * 3600;
			} catch ( Exception $gmt_excpt ) {
				throw new Ai1ec_Datetime_Exception(
					'Invalid local timezone ' . var_export( $zone, true )
				);
			}
		}
		return $offset;
	}

	/**
	 * gmt_to_local method
	 *
	 * Convert timestamp given in GMT to timestamp in local timezone.
	 *
	 * @param int $timestamp Timestamp to convert
	 *
	 * @return int UNIX timestamp in local timezone
	 */
	static public function gmt_to_local( $timestamp ) {
		return $timestamp + self::get_local_offset_from_gmt( $timestamp );
	}

	/**
	 * Get time difference occuring during DST change time
	 *
	 * Return the offset required to add to local time required to
	 * counteract offset introduced by DST during change time.
	 * Only case, when this must return non-zero result is at the
	 * time DST is being changed.
	 *
	 * @param int $timestamp Time for which DST counteraction must be calculated
	 *
	 * @return int Number of seconds to add to local time to counteract DST
	 *             effect when converting to UTC
	 */
	static public function dst_difference( $timestamp ) {
		$_this          = self::instance();
		$local_tz       = $_this->get_local_timezone();
		$tz_object      = $_this->_get_timezone( $local_tz );
		$transitions    = $tz_object->getDetailedTransitions( $timestamp );
		$dst_offset     = absint( $transitions['curr']['offset'] );
		$dst_length     = $transitions['curr']['offset'] -
			$transitions['prev']['offset'];
		if ( $transitions['curr']['offset'] > 0 ) {
			$dst_next_diff = $transitions['next']['ts'] - $timestamp;
			$abs_length    = absint( $dst_length );
			$dst_offset   += $dst_length;
			if (
				$dst_next_diff >  $dst_offset &&
				$dst_next_diff <= $abs_length
			) {
				return $dst_length;
			}
		} else {
			$dst_start_diff = $timestamp - $transitions['curr']['ts'];
			$dst_offset    += $dst_length;
			if (
				$dst_start_diff >= $dst_length &&
				$dst_start_diff <  $dst_offset
			) {
				return $dst_length;
			}
		}
		return 0;
	}

	/**
	 * local_to_gmt method
	 *
	 * Convert timestamp given in local timezone to GMT timestamp.
	 *
	 * @param int $timestamp Timestamp to convert
	 *
	 * @return int UNIX timestamp in GMT
	 */
	static public function local_to_gmt( $timestamp ) {
		$gmtized    = $timestamp - self::get_local_offset_from_gmt(
			$timestamp
		);
		// {$gmtized} used intentionally as TZs are set on GMT basis
		$dst_offset = self::dst_difference( $gmtized );
		$result     = $gmtized - $dst_offset;
		return $result;
	}

	/**
	 * get_timezone_offset method
	 *
	 * Get difference, in seconds, between two given timezones at specified time
	 * given as UNIX timestamp (when not provided - current timestamp is used).
	 *
	 * @param string $remote_tz Remote timezone, to calculate offset from
	 * @param string $origin_tz Origin timezone, to calculate offset to
	 * @param int    $timestamp Reference timestamp, at which offset shall be
	 *                          evaluated [optional=false]
	 *
	 * @return int Difference, in seconds, between timezones
	 *
	 * @throws Ai1ec_Datetime_Exception If some of arguments were invalid
	 */
	static public function get_timezone_offset(
		$remote_tz,
		$origin_tz = NULL,
		$timestamp = false
	) {
		$_this           = self::instance();
		if ( NULL === $origin_tz ) {
			$origin_tz = $_this->get_default_timezone();
		}
		if ( $remote_tz === $origin_tz ) {
			return 0;
		}
		$remote_zone_obj = $_this->_get_timezone( $remote_tz );
		$origin_zone_obj = $_this->_get_timezone( $origin_tz );
		$reference_time  = $_this->_date_time_from_timestamp( $timestamp );
		return $origin_zone_obj->getOffset( $reference_time ) -
			$remote_zone_obj->getOffset( $reference_time );
	}

	/**
	 * gmgetdate method
	 *
	 * Get date/time information in GMT
	 *
	 * @param int $timestamp Timestamp at which information shall be evaluated
	 *
	 * @return array Associative array of information related to the timestamp
	 */
	static public function gmgetdate( $timestamp = NULL ) {
		$_this = self::instance();
		if ( NULL === $timestamp ) {
			$timestamp = (int)$_SERVER['REQUEST_TIME'];
		}
		if ( NULL === ( $date = $_this->_gmtdates->get( $timestamp ) ) ) {
			$particles = explode(
				',',
				gmdate( 's,i,G,j,w,n,Y,z,l,F,U', $timestamp )
			);
			$date      = array_combine(
				array(
					'seconds',
					'minutes',
					'hours',
					'mday',
					'wday',
					'mon',
					'year',
					'yday',
					'weekday',
					'month',
					0
				),
				$particles
			);
			$_this->_gmtdates->set( $timestamp, $date );
		}
		return $date;
	}

	/**
	 * date_i18n method
	 *
	 * Method to be used in place of `date_i18n()` to improve performance
	 * of date-related operations. Useful when replacing several calls on
	 * same timestamp with different formatting options.
	 *
	 * @param string $format    Format string to output timestamp in
	 * @param int    $timestamp UNIX timestamp to output in given format
	 * @param bool   $is_gmt    Set to true, to treat {$timestamp} as GMT
	 *
	 * @return string Formatted date-time entry
	 */
	static public function date_i18n(
		$format,
		$timestamp = false,
		$is_gmt    = true
	) {
		return self::instance()->_time_i18n
			->format( $format, $timestamp, $is_gmt );
	}

	/**
	 * current_time method
	 *
	 * Get current time UNIX timestamp.
	 *
	 * @param bool $is_gmt Set to true to return GMT timestamp
	 *
	 * @return int Current time UNIX timestamp
	 */
	static public function current_time( $is_gmt = false ) {
		return self::instance()->_current_time( $is_gmt );
	}

	/**
	 * _get_timezone method
	 *
	 * Parse given timezone name to DateTimeZone object and cache result in
	 * an in-memory location, to allow faster retrievals on repetitive call
	 * with the same name.
	 *
	 * @param string $name Name of timezone to get TZ object for
	 *
	 * @return DateTimeZone Instance of corresponding TZ object
	 *
	 * @throws Ai1ec_Datetime_Exception When timezone name is invalid
	 */
	protected function _get_timezone( $name ) {
		if (
			NULL === $name &&
			! ( $name = $this->get_default_tz() )
		) {
			return false;
		}
		if ( NULL === ( $zone = $this->_timezones->get( $name ) ) ) {
			try {
				$zone = new Ai1ec_Date_Time_Zone_Utility( $name );
			} catch ( Exception $excpt ) {
				$zone = false;
			}
			$this->_timezones->set( $name, $zone );
		}
		if ( false === $zone ) {
			throw new Ai1ec_Datetime_Exception(
				'Invalid timezone ' . var_export( $name, true )
			);
		}
		return $zone;
	}

	/**
	 * normalize_timestamp method
	 *
	 * Interface method to return normalized timestamp value.
	 *
	 * @param int  $timestamp Timestamp to normalize
	 * @param bool $is_gmt    Define, whereas timestamp is expected to be in GMT
	 *
	 * @return int Normalized timestamp
	 */
	static public function normalize_timestamp(
		$timestamp = false,
		$is_gmt    = false
	) {
		return self::instance()->_normalize_timestamp( $timestamp, $is_gmt );
	}

	/**
	 * _current_time method
	 *
	 * Get current time UNIX timestamp.
	 * Uses in-memory value, instead of re-calling `time()` / `gmmktime()`.
	 *
	 * @param bool $is_gmt Set to true to return GMT timestamp
	 *
	 * @return int Current time UNIX timestamp
	 */
	protected function _current_time( $is_gmt = false ) {
		return $this->_current_time[(int)( (bool)$is_gmt )];
	}

	/**
	 * normalize_timestamp method
	 *
	 * Interface method to return normalized timestamp value.
	 *
	 * @param int  $timestamp Timestamp to normalize
	 * @param bool $is_gmt    Define, whereas timestamp is expected to be in GMT
	 *
	 * @return int Normalized timestamp
	 */
	protected function _normalize_timestamp(
		$timestamp = false,
		$is_gmt    = false
	) {
		$timestamp = (int)$timestamp;
		if ( 0 === $timestamp ) {
			$timestamp = $this->_current_time( $is_gmt );
		}
		return $timestamp;
	}

	/**
	 * _date_time_from_timestamp method
	 *
	 * Convert timestamp (UNIX timestamp, string value 'now' or boolean false)
	 * to DateTime object.
	 *
	 * @param int $timestamp Timestamp to convert [optional=false]
	 *
	 * @return DateTime Instance of corresponding DateTime object
	 *
	 * @throws Ai1ec_Datetime_Exception If timestamp is invalid
	 */
	protected function _date_time_from_timestamp( $timestamp = false ) {
		$timestamp = $this->_normalize_timestamp( $timestamp, true );
		$datetime  = NULL;
		try {
			$datetime = new DateTime( '@' . $timestamp );
		} catch ( Exception $excpt ) {
			throw new Ai1ec_Datetime_Exception(
				'Invalid timestamp ' . var_export( $timestamp, true )
			);
		}
		return $datetime;
	}

	/**
	 * Constructor
	 *
	 * Initialize properties to default values, that may result in better
	 * performance, than delaying this until actual usage.
	 *
	 * @return void Constructor does not return
	 */
	protected function __construct() {
		$this->_timezones    = Ai1ec_Memory_Utility::instance(
			__CLASS__ . '/timezones'
		);
		$this->_gmt_offsets  = Ai1ec_Memory_Utility::instance(
			__CLASS__ . '/gmt_offsets'
		);
		$this->_gmtdates     = Ai1ec_Memory_Utility::instance(
			__CLASS__ . '/gmt_dates'
		);
		$this->_current_time = array(
			(int)$_SERVER['REQUEST_TIME'],
			gmmktime(),
		);
		$this->_time_i18n    = new Ai1ec_Time_I18n_Utility();
	}

}
