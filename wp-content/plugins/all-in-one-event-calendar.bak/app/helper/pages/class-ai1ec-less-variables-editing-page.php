<?php
/**
 *
 * @author Timely Network Inc
 *
 * This class is responsible for rendering the Theme Tptions page (customizing
 * LESS variables).
 */

class Ai1ec_Less_Variables_Editing_Page extends Ai1ec_Page {

	const FORM_SUBMIT_NAME = "ai1ec-less-variables-editing";

	const FORM_SUBMIT_RESET_THEME = "ai1ec-less-variables-reset-theme";

	/**
	 * @var string
	 */
	public $title;

	/**
	 * @var string
	 */
	public $meta_box_id;

	/**
	 *
	 * @see Ai1ec_Page::render_html_for_page()
	 *
	 */
	public function render_html_for_page() {
		// Add content to the page
		$this->add_renderables_to_page();
		$this->meta_box_id = 'ai1ec-less-variables-tabs';
		$this->title = __( 'Calendar Theme Options', AI1EC_PLUGIN_NAME );
		// Create a metabox that calls the render method
		$this->template_adapter->add_meta_box(
			$this->meta_box_id,
			__( 'Calendar Theme Options', AI1EC_PLUGIN_NAME ),
			array( $this, 'render' ),
			$this->meta_box_id,
			'left-side'
		);
		$args = array(
			'page'             => $this,
			'template_adapter' => $this->template_adapter,
		);
		$this->view_helper->display_admin( 'base_page.php', $args );
	}

	/**
	 * (non-PHPdoc)
	 * @see Ai1ec_Page::render()
	 */
	public function render() {
		foreach( $this->renderables as $renderable ) {
			$renderable->render();
		}
	}

	/**
	 * Adds al the elements to the page.
	 * I took this out of the factory so that it's done only if the page is clicked
	 */
	private function add_renderables_to_page() {
		// These are the tabs
		$tabs_ids_description = array(
			'general'     => __( 'General', AI1EC_PLUGIN_NAME ),
			'table'       => __( 'Tables', AI1EC_PLUGIN_NAME ),
			'buttons'     => __( 'Buttons', AI1EC_PLUGIN_NAME ),
			'forms'       => __( 'Forms', AI1EC_PLUGIN_NAME ),
			'calendar'    => __( 'Calendar general', AI1EC_PLUGIN_NAME ),
			'posterboard' => __( 'Posterboard view', AI1EC_PLUGIN_NAME ),
			'stream'      => __( 'Stream view', AI1EC_PLUGIN_NAME ),
			'month'       => __( 'Month/week/day view', AI1EC_PLUGIN_NAME ),
			'agenda'      => __( 'Agenda view', AI1EC_PLUGIN_NAME ),
		);
		// Create the tab layout
		$bootstrap_tabs_layout = Ai1ec_Helper_Factory::create_bootstrap_tabs_layout_instance();
		$bootstrap_tabs_layout->set_layout( 'left' );
		$less_variables = Ai1ec_Lessphp_Controller::get_saved_variables( Ai1ec_Adapters_Factory::create_db_adapter_instance() );
		// Inizialize the array of tabs that will be added to the layout
		$bootstrap_tabs_to_add = array();
		// initialize the array of tab bodyes that will be added to the tabs
		$tabs_bodies = array();
		foreach( $tabs_ids_description as $id => $description ) {
			$bootstrap_tabs_to_add["ai1ec-$id"] = Ai1ec_Helper_Factory::create_bootstrap_tab_instance( $id, $description );
			$bootstrap_tabs_to_add["ai1ec-$id"]->add_class( 'form-horizontal' );
			// create the main div that will hold all the variables
			$div = Ai1ec_Helper_Factory::create_generic_html_tag( 'div' );
			$tabs_bodies["ai1ec-$id"] = $div;
		}
		foreach( $less_variables as $variable_id => $variable_attributes ) {
			$variable_attributes['id'] = $variable_id;
			$less_variable = Ai1ec_Less_Factory::create_less_variable(
				$variable_attributes['type'],
				$variable_attributes
			);
			$tabs_bodies["ai1ec-$variable_attributes[tab]"]->add_renderable_children( $less_variable );
		}
		foreach( $tabs_bodies as $tab => $div ) {
			$bootstrap_tabs_to_add[$tab]->add_renderable_children( $div );
		}
		foreach( $bootstrap_tabs_to_add as $tab ) {
			$bootstrap_tabs_layout->add_renderable_children( $tab );
		}
		$this->add_renderable_children( $bootstrap_tabs_layout );

		$input = Ai1ec_Helper_Factory::create_input_instance();
		$input->set_type( 'submit' );
		$input->set_value( __( 'Save Options', AI1EC_PLUGIN_NAME ) );
		$input->set_name( Ai1ec_Less_Variables_Editing_Page::FORM_SUBMIT_NAME );
		$input->add_class( "button-primary" );
		$reset_theme = Ai1ec_Helper_Factory::create_input_instance();
		$reset_theme->set_type( 'submit' );
		$reset_theme->set_value( __( 'Reset to defaults', AI1EC_PLUGIN_NAME ) );
		$reset_theme->set_name( Ai1ec_Less_Variables_Editing_Page::FORM_SUBMIT_RESET_THEME );
		$reset_theme->add_class( "button" );
		$reset_theme->set_id( 'ai1ec-reset-variables' );
		$this->add_renderable_children( $input );
		$this->add_renderable_children( $reset_theme );
	}
}
