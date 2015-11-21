<?php

/**
 * Abstract class for extensions which are sold.
 *
 * @author     Time.ly Network Inc.
 * @since      2.0
 *
 * @package    AI1EC
 * @subpackage AI1EC.Controller
 */
abstract class Ai1ec_Base_License_Controller extends Ai1ec_Base_Extension_Controller {

	/**
	 * @var string Settings entry name for license key.
	 */
	protected $_licence;

	/**
	 * @var string Settings entry name for license status output.
	 */
	protected $_licence_status;

	/**
	 * @var string Licensing API endpoint URI.
	 */
	protected $_store = 'http://time.ly/';

	/**
	 * Get label to be used for license input field.
	 *
	 * @return string Localized label field.
	 */
	abstract public function get_license_label();

	/**
	 * @param Ai1ec_Registry_Object $registry
	 */
	public function initialize_licence_actions() {
		$this->_register_licence_actions();
		$this->_register_licence_fields();
	}

	/**
	 * Add the extension tab if not present
	 *
	 * @param array $tabs
	 * @return array
	 */
	public function add_tabs( array $tabs ) {
		if ( ! isset( $tabs['extensions'] ) ) {
			$tabs['extensions'] = array(
				'name'  => Ai1ec_I18n::__( 'Add-ons' ),
				'items' => array(),
			);
		}

		return $tabs;
	}

	/**
	 * Register action for licences.
	 */
	protected function _register_licence_actions() {
		$dispatcher = $this->_registry->get( 'event.dispatcher' );
		// we need the super class so we use get_class()
		$class      = explode( '_', get_class( $this ) );
		$controller = strtolower( end( $class ) );
		$dispatcher->register_filter(
			'ai1ec_add_setting_tabs',
			array( 'controller.' . $controller, 'add_tabs' )
		);
	}

	/**
	 * Register fields for licence
	 */
	protected function _register_licence_fields() {
		$plugin_id             = $this->get_machine_name();
		$this->_licence        = 'ai1ec_licence_' . $plugin_id;
		$this->_licence_status = 'ai1ec_licence_status_' . $plugin_id;
		$options               = array(
			$this->_licence => array(
				'type' => 'string',
				'version'  => $this->get_version(),
				'renderer' => array(
					'class'       => 'input',
					'group-class' => 'ai1ec-col-sm-7',
					'tab'         => 'extensions',
					'item'        => 'licenses',
					'type'        => 'normal',
					'label'       => $this->get_license_label(),
					'status'      => $this->_licence_status,
				),
				'default'  => '',
			),
			$this->_licence_status => array(
				'type'     => 'string',
				'version'  => $this->get_version(),
				'default'  => 'invalid',
			),
		);
		$settings = $this->_registry->get( 'model.settings' );
		foreach ( $options as $key => $option ) {
			$renderer = null;
			if ( isset( $option['renderer'] ) ) {
				$renderer = $option['renderer'];
			}
			$settings->register(
				$key,
				$option['default'],
				$option['type'],
				$renderer,
				$option['version']
			);
		}
	}

}