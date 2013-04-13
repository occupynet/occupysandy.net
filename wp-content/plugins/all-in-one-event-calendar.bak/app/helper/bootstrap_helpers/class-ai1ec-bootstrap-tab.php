<?php

/**
 *
 * @author Timely Network Inc
 *
 * This class is responsible for rendering a Bootstrap tab.
 * It requires a Bootstrap layout to coordinate rendering multiple tabs.
 */

class Ai1ec_Bootstrap_Tab extends Ai1ec_Html_Element_Can_Have_Children {
	/**
	 * @var string
	 */
	private $title;

	public function __construct( $id, $title ) {
		parent::__construct();
		$this->id    = $this->template_adapter->escape_attribute( $id );
		$this->title = $title;
		$this->add_class( 'tab-pane' );
	}

	/**
	 * Render the the header of the tab
	 *
	 */
	public function render_tab_header() {
		echo "<li><a href='#{$this->id}' data-toggle='tab'>{$this->title}</a></li>";
	}

	/**
	 *
	 * @see Ai1ec_Renderable::render()
	 *
	 */
	public function render() {
		echo '<div ' . $this->create_class_markup() . ' id="' . $this->id . '">';
		foreach( $this->container->renderables as $renderable ) {
			$renderable->render();
		}
		echo "</div>";
	}
}
