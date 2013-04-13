<?php

/**
 * Query adapter interface
 *
 * @author     Justas Butkus <justas@butkus.lt>
 * @since      2012.07.20
 *
 * @package    AllInOneCalendar
 * @subpackage AllInOneCalendar.Lib.Adapter
 */
interface Ai1ec_Adapter_Query_Interface
{

	/**
	 * Check if rewrite module is enabled
	 */
	public function rewrite_enabled();

	/**
	 * Register rewrite rule
	 *
	 * @param string $regexp   Matching expression
	 * @param string $landing  Landing point for queries matching regexp
	 * @param int    $priority Rule priority (match list) [optional=NULL]
	 *
	 * @return bool
	 */
	public function register_rule( $regexp, $landing, $priority = NULL );

}
