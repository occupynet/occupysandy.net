<?php

/**
 * @author Timely Network Inc
 *
 * This class is responsible for creating helpers.
 */

class Ai1ec_Helper_Factory {

	/**
	 * @return Ai1ec_Bootstrap_Tabs
	 */
	public static function create_bootstrap_tabs_layout_instance() {
		return new Ai1ec_Bootstrap_Tabs_Layout( self::create_view_helper_instance() );
	}

	/**
	 * @return Ai1ec_Bootstrap_Tab
	 */
	public static function create_bootstrap_tab_instance( $id, $title ) {
		return new Ai1ec_Bootstrap_Tab( $id, $title );
	}

	/**
	 * @return Ai1ec_View_Helper
	 */
	public static function create_view_helper_instance() {
		return Ai1ec_View_Helper::get_instance();
	}

	/**
	 * @param string $color
	 * @param string $id
	 * @return Ai1ec_Bootstrap_Colorpicker
	 */
	public static function create_bootstrap_colorpicker_instance( $color, $id ) {
		return new Ai1ec_Bootstrap_Colorpicker( $color, $id );
	}

	/**
	 * @return Ai1ec_Generic_Html_Tag
	 */
	public static function create_generic_html_tag( $type ) {
		return new Ai1ec_Generic_Html_Tag( $type );
	}

	/**
	 * @return Ai1ec_Select
	 */
	public static function create_select_instance( $id ) {

		return new Ai1ec_Select( $id );
	}

	/**
	 * @return Ai1ec_Input
	 */
	public static function create_input_instance() {
		return new Ai1ec_Input();
	}

	/**
	 * @param string $label
	 * @param string $message
	 * @return Ai1ec_Admin_Message_Helper
	 */
	public static function create_admin_message_instance( $message, $label = null ) {
		global $ai1ec_view_helper;
		$admin_message = new Ai1ec_Admin_Message_Helper( $message, $ai1ec_view_helper );
		if( null !== $label ) {
			$admin_message->set_label( $label );
		}
		return $admin_message;
	}

	/**
	 * @param string $message
	 * @param string $type
	 * @return A1iec_Bootstrap_Message
	 */
	public static function create_bootstrap_message_instance( $message, $type = 'success' ) {
		return new A1iec_Bootstrap_Message( $message, $type );
	}
}
