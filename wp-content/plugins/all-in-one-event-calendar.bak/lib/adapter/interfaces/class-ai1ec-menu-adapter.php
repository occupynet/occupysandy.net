<?php

/**
*
*/

interface Ai1ec_Menu_Adapter {
	public function add_menu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
}