<?php

/**
 * @author Timely Network Inc
 *
 * This class handles creation of pages.
 */

class Ai1ec_Page_Factory {

	/**
	 * @param string $page_name
	 * @return Ai1ec_Page
	 */
	public static function create_page( $page_name ) {
		switch ( $page_name ) {
			case 'less_variable_editing':
				return self::create_less_variables_editing_page_instance( );
			break;
		}
	}

	/**
	 * Create the page
	 *
	 * @return Ai1ec_Less_Variables_Editing_Page
	 */
	public static function create_less_variables_editing_page_instance() {
		$less_variable_page = new Ai1ec_Less_Variables_Editing_Page(
			Ai1ec_Adapters_Factory::create_menu_adapter_instance(),
			Ai1ec_Helper_Factory::create_view_helper_instance(),
			Ai1ec_Adapters_Factory::create_template_adapter_instance()
		);
		return $less_variable_page;
	}
}
