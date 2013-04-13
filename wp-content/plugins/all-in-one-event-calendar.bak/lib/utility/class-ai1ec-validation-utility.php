<?php

/**
 * Validation utility library
 *
 * @author     Timely Network Inc
 * @since      2012.08.21
 *
 * @package    AllInOneCalendar
 * @subpackage AllInOneCalendar.Lib.Utility
 */
class Ai1ec_Validation_Utility {

	/**
	 * Check if the date supplied is valid. It validates $date in the format given
	 * by $pattern, which matches one of the supported date patterns.
	 * @see  Ai1ec_Time_Utility::get_date_patterns()
	 *
	 * @param  string  $date    Date string to validate
	 * @param  string  $pattern Key of date pattern (@see
	 *                          Ai1ec_Time_Utility::get_date_patterns()) to
	 *                          match date string against
	 * @return boolean
	 */
	static public function validate_date( $date, $pattern = 'def' ) {
		$result = self::validate_date_and_return_parsed_date( $date, $pattern );
		if( $result === false ) {
			return false;
		}
		return true;
	}

	/**
	 * Check if the date supplied is valid. It validates date in the format given
	 * by $pattern, which matches one of the supported date patterns.
	 * @see  Ai1ec_Time_Utility::get_date_patterns()
	 *
	 * @param  string  $date    Date string to parse
	 * @param  string  $pattern Key of date pattern (@see
	 *                          Ai1ec_Time_Utility::get_date_patterns()) to
	 *                          match date string against
	 * @return array|boolean		An array with the parsed date or false if the date
	 *                          is not valid
	 */
	static public function validate_date_and_return_parsed_date(
		$date, $pattern = 'def'
	) {
		// Convert pattern to regex.
		$pattern = Ai1ec_Time_Utility::get_date_pattern_by_key( $pattern );
		$pattern = preg_quote( $pattern, '/' );
		$pattern = str_replace(
			array( 'dd',           'd',              'mm',           'm',              'yyyy',         'yy' ),
			array( '(?P<d>\d{2})', '(?P<d>\d{1,2})', '(?P<m>\d{2})', '(?P<m>\d{1,2})', '(?P<y>\d{4})', '(?P<y>\d{2})' ),
			$pattern
		);
		// Accept hyphens and dots in place of forward slashes (for URLs).
		$pattern = str_replace( '\/', '[\/\-\.]', $pattern );
		$pattern = "/^$pattern$/";

		if( preg_match( $pattern, $date, $matches ) ) {
			if( checkdate( $matches['m'], $matches['d'], $matches['y'] ) ) {
				return array(
					"month" => $matches['m'],
					"day"   => $matches['d'],
					"year"  => $matches['y'],
				);
			}
		}
		return false;
	}

	/**
	 * Check if the string or integer is a valid timestamp.
	 *
	 * @see http://stackoverflow.com/questions/2524680/check-whether-the-string-is-a-unix-timestamp
	 * @param string|int $timestamp
	 * @return boolean
	 */
	static public function is_valid_time_stamp( $timestamp ) {
		return
			( is_int( $timestamp ) || ( string ) ( int ) $timestamp === $timestamp )
			&& ( $timestamp <= PHP_INT_MAX )
			&& ( $timestamp >= ~ PHP_INT_MAX );
	}
}
