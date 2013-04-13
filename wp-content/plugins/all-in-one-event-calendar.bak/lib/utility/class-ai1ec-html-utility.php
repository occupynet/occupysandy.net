<?php

/**
 * HTML enhancement utility library
 *
 * @author     Justas Butkus <justas@butkus.lt>
 * @since      2012.10.22
 *
 * @package    AllInOneCalendar
 * @subpackage AllInOneCalendar.Lib.Utilities
 */
class Ai1ec_Html_Utility
{

	/**
	 * nbsp method
	 *
	 * Convert spaces to {{&nbsp;}} HTML entities.
	 *
	 * @param string $input Text to modify
	 *
	 * @param string HTML with spaces replaced with {{&nbsp;}} entities
	 */
	static public function nbsp( $input ) {
		return str_replace( ' ', '&nbsp;', $input );
	}

	/**
	 * escape method
	 *
	 * Static method, which is an interface to `esc_html` so far, which
	 * intention is to sanitize HTML before outputting to screen.
	 *
	 * @param string $input HTML to sanitize
	 *
	 * @param string Sane HTML to use on screen
	 */
	static public function escape( $input ) {
		return esc_html( $input );
	}

}
