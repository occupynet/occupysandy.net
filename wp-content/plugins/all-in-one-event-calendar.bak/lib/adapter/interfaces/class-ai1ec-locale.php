<?php
/**
 * This cinterface provides Locale related function for our plugin
 * 
 * @author Then.ly
 *
 */


interface Ai1ec_Locale {
	/**
	 * get an array of the month names for the current locale
	 * 
	 * @return array
	 */
	public function get_localized_month_names();
	/**
	 * get an array of the week names for the current locale
	 *
	 * @return array
	 */
	public function get_localized_week_names();
}