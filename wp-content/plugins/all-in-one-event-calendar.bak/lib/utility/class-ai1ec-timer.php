<?php

/**
 * This is a simple class that can be used to benchmark functions.
 *
 * @author Timely Network Inc
 * @since  1.9
 *
 * @package    AllInOneEventCalendar
 * @subpackage AllInOneEventCalendar.Lib.Utility
 */
class Ai1ec_Timer {
	var $start;
	var $pause_time;

	/*  start the timer  */
	function __construct( $start = 0 ) {
		if( $start ) {
			$this->start();
		}
	}

	/*  start the timer  */
	function start() {
		$this->start = $this->get_time();
		$this->pause_time = 0;
	}

	/*  pause the timer  */
	function pause() {
		$this->pause_time = $this->get_time();
	}

	/*  unpause the timer  */
	function unpause() {
		$this->start += ($this->get_time() - $this->pause_time);
		$this->pause_time = 0;
	}

	/*  get the current timer value  */
	function get($decimals = 8) {
		return round(($this->get_time() - $this->start),$decimals);
	}

	/*  format the time in seconds  */
	function get_time() {
		list($usec,$sec) = explode(' ', microtime());
		return ((float)$usec + (float)$sec);
	}
}
