<?php

/**
 * @author Timely Network Inc
 *
 * This class is an adapter between Wordpress functions and our interfaces.
 */
class Ai1ec_Locale_Wordpress_Adapter implements Ai1ec_Locale {
	/**
	 *
	 * @return array
	 *
	 * @see Ai1ec_Locale::get_localized_week_names()
	 *
	 */
	public function get_localized_week_names() {
		global $wp_locale;
		return implode( ',', $wp_locale->weekday_initial );
	}
	/**
	 *
	 * @return array
	 *
	 * @see Ai1ec_Locale::get_localized_month_names()
	 *
	 */
	public function get_localized_month_names() {
		global $wp_locale;
		return implode( ',', $wp_locale->month );
	}
}
