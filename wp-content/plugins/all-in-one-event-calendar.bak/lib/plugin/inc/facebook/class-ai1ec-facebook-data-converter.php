<?php

/**
 * This class converts Facebook data so that it can be used by our plugin.
 *
 * @author The Seed Network
 * @since  1.8
 *
 */
class Ai1ec_Facebook_Data_Converter {
	/**
	 * Check if a key in an array is set and then returns the value or if none
	 * found, an empty string instead.
	 *
	 * @param  array  $array The array whose key must be checked
	 * @param  string $key   The key that must be checked
	 *
	 * @return string        The value at $key if set, else an empty string
	 */
	public static function return_empty_or_value_if_set( array $array, $key ) {
		return isset( $array[$key] ) ? $array[$key] : '';
	}
}
