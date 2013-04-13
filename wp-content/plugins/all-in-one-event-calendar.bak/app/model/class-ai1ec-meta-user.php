<?php

/**
 * WordPress backed user meta options management
 *
 * @author     Justas Butkus <justas@butkus.lt>
 * @since      2012.10.02
 *
 * @package    AllInOneCalendar
 * @subpackage AllInOneCalendar.Model.Meta
 */
class Ai1ec_Meta_User extends Ai1ec_Meta
{

	/**
	 * _fetch method
	 *
	 * Fetch actual user meta using WP interface.
	 *
	 * @uses get_user_meta To get actual value
	 *
	 * @param string $post_id  ID of user to fetch meta for
	 * @param NULL   $meta_key Meta name to fetch
	 * @param NULL   $default  Value to return if $name is not found
	 * @param NULL   $single   Whereas to fetch all, or single, matches
	 *
	 * @return mixed Value or $default
	 */
	protected function _fetch(
		$user_id,
		$meta_key = NULL,
		$default  = false,
		$single   = false
	) {
		$value = get_user_meta(
			$user_id,
			$meta_key,
			false
		);
		if ( empty( $value ) ) {
			return $default;
		}
		if ( ! $single ) {
			return $value;
		}
		return $value[0];
	}

	protected function _after_initialize() {
		return add_action( 'update_user_meta', array( $this, 'clean' ) );
	}

}
