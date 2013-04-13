<?php

/**
 * WordPress backed post meta options management
 *
 * @author     Justas Butkus <justas@butkus.lt>
 * @since      2012.10.02
 *
 * @package    AllInOneCalendar
 * @subpackage AllInOneCalendar.Model.Meta
 */
class Ai1ec_Meta_Post extends Ai1ec_Meta
{

	/**
	 * _fetch method
	 *
	 * Fetch actual post meta using WP interface.
	 *
	 * @uses get_post_meta To get actual value
	 *
	 * @param string $post_id  ID of post to fetch meta for
	 * @param NULL   $meta_key Meta name to fetch
	 * @param NULL   $default  Value to return if $name is not found
	 * @param NULL   $single   Whereas to fetch all, or single, matches
	 *
	 * @return mixed Value or $default
	 */
	protected function _fetch(
		$post_id,
		$meta_key = NULL,
		$default  = false,
		$single   = false
	) {
		$value = get_post_meta(
			$post_id,
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
		return add_action( 'update_post_meta', array( $this, 'clean' ) );
	}

}
