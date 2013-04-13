<?php

/**
 * @author Timely Network Inc
 *
 * This class represents a page.
 */

abstract class Ai1ec_Page extends Ai1ec_Base_Container implements Ai1ec_Renderable {
	/**
	 * @var Ai1ec_Menu_Adapter
	 */
	protected $menu_adapter;
	/**
	 * @var Ai1ec_View_Helper
	 */
	protected $view_helper;
	/**
	 * @var Ai1ec_Template_Adapter
	 */
	protected $template_adapter;

	/**
	 * This function is the director of the composite and builds the children
	 * object.
	 */
	abstract public function render_html_for_page();

	/* (non-PHPdoc)
	 * @see Ai1ec_Renderable::render()
	*/
	//abstract public function render();

	public function __construct(
		Ai1ec_Menu_Adapter $menu_adapter,
		Ai1ec_View_Helper $ai1ec_view_helper,
		Ai1ec_Template_Adapter $template_adapter
	) {
		$this->menu_adapter = $menu_adapter;
		$this->view_helper = $ai1ec_view_helper;
		$this->template_adapter = $template_adapter;
	}

	/**
	 * Adds the page to a menu.
	 *
	 * @param string $parent_slug
	 * @param string $page_title
	 * @param string $menu_title
	 * @param string $capability
	 * @param string $menu_slug
	 */
	public function add_page_to_menu( $parent_slug, $page_title, $menu_title, $capability, $menu_slug ) {
		return $this->menu_adapter->add_menu_page(
			$parent_slug,
			$page_title,
			$menu_title,
			$capability,
			$menu_slug,
			array( $this, 'render_html_for_page' )
		);
	}
}
