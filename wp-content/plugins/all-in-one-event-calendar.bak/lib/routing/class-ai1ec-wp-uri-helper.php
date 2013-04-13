<?php

/**
 * Class to aid WP URI management
 *
 * @author     Justas Butkus <justas@butkus.lt>
 * @since      2012.12.11
 *
 * @package    AllInOneCalendar
 * @subpackage AllInOneCalendar.Lib.Routing
 */
class Ai1ec_Wp_Uri_Helper
{

	/**
	 * get_pagebase method
	 *
	 * Given URI - extract pagebase value, as used in `index.php?pagebase={arg}`
	 * when matching rewrites.
	 * It may, optionally, provide arguments from URI to append to query string.
	 * This is indicated via setting {@see $qsa} to non-`false` value.
	 *
	 * @param string $uri URI to parse pagebase value from
	 * @param string $qsa Separator to use to append query arguments [optional]
	 *
	 * @return string Parsed URL
	 */
	static public function get_pagebase( $uri, $qsa = false ) {
		$parsed = parse_url( $uri );
		if ( empty( $parsed ) ) {
			return '';
		}
		$output = '';
		if ( isset( $parsed['path'] ) ) {
			$output = basename( $parsed['path'] );
		}
		if ( isset( $parsed['query'] ) && false !== $qsa ) {
			$output .= $qsa . $parsed['query'];
		}
		return $output;
	}

	/**
	 * Get the calendar pagebase with the full url but without the language
	 * 
	 * @param string $uri
	 * @param string $lang
	 * @return string
	 */
	static public function get_pagebase_for_links( $uri, $lang ) {
		if( empty( $lang ) ) {
			return $uri;
		}
		if( false !== strpos( $uri, '&amp;lang=' ) ) {
			return str_replace( '&amp;lang=' . $lang, '' , $uri );
		}
		if( false !== strpos( $uri, '?lang=' ) ) {
			return str_replace( '?lang=' . $lang, '' , $uri );
		}
		return $uri;
	}
}
