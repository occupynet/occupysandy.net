<?php
/**
 *
 * @author Timely Network Inc
 *
 * This class is responsible for creating instances of adapters.
 */
class Ai1ec_Adapters_Factory {

	/**
	 * @return Ai1ec_Wordpress_Menu_Adapter
	 */
	public static function create_menu_adapter_instance() {
		return new Ai1ec_Wordpress_Menu_Adapter();
	}

	/**
	 * @return Ai1ec_Wordpress_Db_Adapter
	 */
	public static function create_db_adapter_instance() {
		return new Ai1ec_Wordpress_Db_Adapter();
	}

	/**
	 * @return Ai1ec_Wordpress_Template_Adapter
	 */
	public static function create_template_adapter_instance() {
		return new Ai1ec_Wordpress_Template_Adapter();
	}

	/**
	 *
	 * @return Ai1ec_Adapter_Query_Wordpress
	 */
	public static function create_query_adapter_instance() {
		return new Ai1ec_Adapter_Query_Wordpress();
	}
}
