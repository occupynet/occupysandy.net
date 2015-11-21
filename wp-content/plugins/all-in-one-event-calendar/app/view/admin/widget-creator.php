<?php

/**
 * The SuperWidget creator page.
 *
 * @author     Time.ly Network Inc.
 * @since      2.1
 *
 * @package    AI1ECSW
 * @subpackage AI1ECSW.View
 */
class Ai1ec_View_Widget_Creator extends Ai1ec_View_Admin_Abstract {

	/**
	 * Adds page to the menu.
	 *
	 * @wp_hook admin_menu
	 *
	 * @return void
	 */
	public function add_page() {
		add_submenu_page(
			AI1EC_ADMIN_BASE_URL,
			__( 'Widget Creator', AI1EC_PLUGIN_NAME ),
			__( 'Widget Creator', AI1EC_PLUGIN_NAME ),
			'manage_ai1ec_feeds',
			AI1EC_PLUGIN_NAME . '-widget-creator',
			array( $this, 'display_page' )
		);
	}

	/**
	 * Display this plugin's feeds page in the admin.
	 *
	 * @return void
	 */
	public function display_page() {
		$this->_registry->get( 'css.admin' )->admin_enqueue_scripts(
			'ai1ec_event_page_all-in-one-event-calendar-settings'
		);
		$this->_registry->get( 'css.admin' )->process_enqueue(
			array(
				array( 'style', 'super-widget.css', ),
			)
		);
		$args = array(
			'title' => __(
				'Widget Creator',
				AI1EC_PLUGIN_NAME
			),
			'metabox' => array(
				'screen' => 'ai1ec-super-widget',
				'action' => 'left',
				'object' => null
			),
		);
		$loader = $this->_registry->get( 'theme.loader' );
		$file   = $loader->get_file( 'widget-creator/page.twig', $args, true );
		$file->render();
	}

	/* (non-PHPdoc)
	 * @see Ai1ec_View_Admin_Settings::handle_post()
	 */
	public function handle_post() {
	}

	/* (non-PHPdoc)
	 * @see Ai1ec_View_Admin_Settings::add_meta_box()
	 */
	public function add_meta_box() {
		add_meta_box(
			'ai1ec-widget-creator',
			_x( 'Widget Creator', 'meta box', AI1EC_PLUGIN_NAME ),
			array( $this, 'display_meta_box' ),
			'ai1ec-super-widget',
			'left',
			'default'
		);
	}

	/**
	 * Renders the settings
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public function get_html_from_settings( array $settings ) {
		$named_elements = array();
		foreach ( $settings as $id => $setting ) {
			$named_elements[$id] = $this->_registry->get(
				'html.element.setting.' . $setting['renderer']['class'],
				array(
					'id'       => $id,
					'value'    => $setting['value'],
					'renderer' => $setting['renderer'],
				)
			)->render();
		}
		return $named_elements;
	}

	/* (non-PHPdoc)
	 * @see Ai1ec_View_Admin_Settings::display_meta_box()
	 */
	public function display_meta_box( $object, $box )  {
		$widgets = $this->_registry->get( 'controller.javascript-widget' )
			->get_widgets();
		// this is just for the Super Widget which doesn't fully implement Ai1ec_Embeddable
		$widgets = apply_filters( 'ai1ec_widget_creators_widgets', $widgets );
		$tabs = array();
		foreach ( $widgets as $widget_id => $widget_class ) {
			$widget           = $this->_registry->get( $widget_class );
			$tabs[$widget_id] = array(
				'name'         => $widget->get_name(),
				'icon'         => $widget->get_icon(),
				'requirements' => $widget->check_requirements(),
				'elements'     => $this->get_html_from_settings(
					$widget->get_configurable_for_widget_creation()
				)
			);
		}

		$loader = $this->_registry->get( 'theme.loader' );
		$file   = $loader->get_file(
			'widget-creator/super-widget-contents.twig',
			array(
				'tabs'              => $tabs,
				'siteurl'           => trailingslashit( ai1ec_get_site_url() ),
				'text_common_info'  => Ai1ec_I18n::__( 'Use this tool to generate code snippets you can add to <strong>an external website</strong> to embed new calendars and widgets.' ),
				'text_alert'        => Ai1ec_I18n::__( '<h4>Attention!</h4><p>These widgets are designed to be embedded in <strong>external sites only</strong> and may cause conflicts if used within the same WordPress site.</p>' ),
				'text_alternatives' => sprintf(
					Ai1ec_I18n::__( '<p>Use <a href="%s"><strong>Appearance</strong> &gt; <strong>Widgets</strong></a> to add event widgets to your WordPress site as you would any other widget, or use <a href="%s" target="_blank">shortcodes</a> to embed the full calendar.</strong></p>' ),
					admin_url( 'widgets.php' ),
					'http://time.ly/document/user-guide/using-calendar/display-multiple-calendars-site/'
				),
				'display_alert'     => apply_filters( 'ai1ec_display_widget_creator_warning', true ),
				'text_preview'      => Ai1ec_I18n::__( 'Preview:' ),
				'text_paste'        => Ai1ec_I18n::__( 'Paste this code onto your site:' ),
				'text_updated_code' => Ai1ec_I18n::__( 'This code will update to reflect changes made to the settings. Changing settings will not affect previously embedded widgets.' ),
			),
			true
		);
		$file->render();
	}


}
