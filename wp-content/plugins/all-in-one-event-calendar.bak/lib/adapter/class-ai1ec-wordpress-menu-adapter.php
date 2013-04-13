<?php


/**
 * @author Timely Network Inc
 */

class Ai1ec_Wordpress_Menu_Adapter implements Ai1ec_Menu_Adapter {
	/**
	 *
	 * @see Ai1ec_Menu_Adapter::add_menu_page()
	 *
	 */
	public function add_menu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function ) {
		return add_submenu_page(
			$parent_slug,
			$page_title,
			$menu_title,
			$capability,
			$menu_slug,
			$function
		);
	}
}
