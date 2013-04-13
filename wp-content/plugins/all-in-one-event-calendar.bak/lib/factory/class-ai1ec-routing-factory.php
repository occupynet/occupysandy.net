<?php

/**
 * @author Timely Network Inc
 */

class Ai1ec_Routing_Factory {

	/**
	 * @var Ai1ec_Settings
	 */
	private static $ai1ec_settings;

	/**
	 * @param Ai1ec_Settings $ai1ec_settings
	 */
	public static function set_ai1ec_settings( $ai1ec_settings ) {
		Ai1ec_Routing_Factory::$ai1ec_settings = $ai1ec_settings;
	}

	/**
	 * @param array $arguments
	 * @return Ai1ec_Arguments_Parser
	 */
	public static function create_argument_parser_instance( array $arguments = NULL ) {
		$request = new Ai1ec_Arguments_Parser(
			$arguments,
			self::$ai1ec_settings->default_calendar_view
		);
		$request->parse();
		return $request;
	}
}
