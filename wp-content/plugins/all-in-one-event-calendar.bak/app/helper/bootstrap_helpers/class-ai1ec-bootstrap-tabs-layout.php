<?php
/**
 *
 * @author Timely Network Inc
 *
 * This class is responsible for rendering multiple Bootstrap tabs with the
 * desired layout.
 */
class Ai1ec_Bootstrap_Tabs_Layout implements Ai1ec_Renderable {
	/**
	 * @var Ai1ec_View_Helper
	 */
	private $view_helper;
	/**
	 * @var string
	 */
	private $layout = "up";
	/**
	 * @var string
	 */
	private $class = "";
	/**
	 * @var array
	 */
	private $renderables;

	public function __construct( Ai1ec_View_Helper $view_helper ) {
		$this->view_helper = $view_helper;
	}

	/**
	 *
	 * @param string $layout
	 */
	public function set_layout( $layout ) {
		$this->layout = $layout;
		switch( $layout ) {
			case "right" : $this->class = "tab-right";
				break;
			case "up"    : $this->class = "";
				break;
			case "below" : $this->class = "tabs-below";
				break;
			default      : $this->class = "tabs-left";
				break;
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see Ai1ec_Renderable::render()
	 */
	public function render() {
		$args = array(
			'component' => $this,
			'class'     => $this->class,
		);
		$this->view_helper->display_admin( 'base_tabs.php', $args );
	}

	/**
	 * This overrides standard add_children method so that it accepts
	 * Ai1ec_Bootstrap_Tab objects. The composite is safe as the
	 * Ai1ec_Bootstrap_Tab objects implements Ai1ec_Renderable.
	 *
	 * @see Ai1ec_Parent::add_renderable_children()
	 */
	public function add_renderable_children( Ai1ec_Bootstrap_Tab $tab ) {
		$this->renderables[] = $tab;
	}

	/**
	 * Render the headers of the tabs
	 */
	public function render_headers() {
		foreach( $this->renderables as $renderable ) {
			$renderable->render_tab_header();
		}
	}

	/**
	 * Render the bodies of the tabs.
	 */
	public function render_bodies() {
		foreach( $this->renderables as $renderable ) {
			$renderable->render();
		}
	}
}
