<?php

/**
 * Array manipulation utility library
 *
 * @author     Justas Butkus <justas@butkus.lt>
 * @since      2012.07.20
 *
 * @package    AllInOneCalendar
 * @subpackage AllInOneCalendar.Lib.Utilities
 */
class Ai1ec_Utility_Array
{

	/**
	 * Merge two arrays recursively maintaining key type as long as possible
	 *
	 * Method similar to array_merge_recursive, although it does not cast non
	 * array value to array, unless one of arguments is an array.
	 * Merge product is produced only on two arrays, not unlimited many.
	 *
	 * @param array $arr1 First (base) array to merge
	 * @param array $arr2 Second (ammendment) array to merge
	 *
	 * @return array Merge product
	 */
	static public function deep_merge( array $arr1, array $arr2 ) {
		$result = array();
		foreach ( $arr1 as $key => $value ) {
			self::_merge_value( $result, $key, $value );
			if ( isset( $arr2[$key] ) ) {
				if ( is_array( $result[$key] ) || is_array( $arr2[$key] ) ) {
					$result[$key] = (array)$result[$key];
					$arr2[$key]	  = (array)$arr2[$key];
					$result[$key] = self::deep_merge(
					  $result[$key],
					  $arr2[$key]
					);
				} else {
					self::_merge_value( $result, $key, $arr2[$key] );
				}
			}
			unset( $arr2[$key] );
		}
		foreach ( $arr2 as $key => $value ) {
			self::_merge_value( $result, $key, $value );
		}
		return $result;
	}

	/**
	 * Inject value into merge array
	 *
	 * If key is numeric (appears to be integer) - value is pushed
	 * into array, otherwise added under given key.
	 *
	 * @param array		 $result Reference to merge array
	 * @param string|int $key	 Key to use for merge
	 * @param mixed		 $value	 Value to add under key
	 *
	 * @return bool Success If it is not true - something wrong happened
	 */
	static protected function _merge_value( array& $result, $key, $value ) {
		if ( is_int( $key ) || ctype_digit( $key ) ) {
			$result[] = $value;
			return true;
		}
		$result[$key] = $value;
		return true;
	}

}
