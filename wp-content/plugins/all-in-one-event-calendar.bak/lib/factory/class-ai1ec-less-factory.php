<?php

/**
 * @author Timely Network Inc
 *
 * This class is responsible for creating LESS-related objects.
 */

class Ai1ec_Less_Factory {
	/**
	 * @var array
	 */
	private static $files = array( 'style', 'event', 'calendar', 'override', '../style' );
	/**
	 * @var string
	 */
	private static $active_theme_path;
	/**
	 * @var string
	 */
	private static $default_theme_path;
	/**
	 * @var string
	 */
	private static $default_theme_url;
	/**
	 *
	 * @var boolean
	 */
	private static $preview_mode = false;

	/**
	 * @param string $active_theme_path
	 */
	public static function set_active_theme_path( $active_theme_path ) {
		Ai1ec_Less_Factory::$active_theme_path = $active_theme_path;
	}

	/**
	 * @param string $default_theme_path
	 */
	public static function set_default_theme_path( $default_theme_path ) {
		Ai1ec_Less_Factory::$default_theme_path = $default_theme_path;
	}

	/**
	 * @param boolean $preview_mode
	 */
	public static function set_preview_mode( $preview_mode ) {
		Ai1ec_Less_Factory::$preview_mode = $preview_mode;
	}
	/**
	 * @param string $default_theme_url
	 */
	public static function set_default_theme_url( $default_theme_url ) {
		Ai1ec_Less_Factory::$default_theme_url = $default_theme_url;
	}

	/**
	 * @return lessc
	 */
	public static function create_lessc_instance() {
		require_once AI1EC_LIB_PATH . '/lessphp/lessc.inc.php';
		$less = new lessc();
		// check if the method exists ( it was introduced in 0.3.5 ) if an older version was loaded before ours.
		if( method_exists( $less, 'setFormatter' ) ) {
			$less->setFormatter( "compressed" );
		}
		return $less;
	}

	/**
	 * @param string $active_theme_path
	 * @param string $default_theme_path
	 * @param string $default_theme_path
	 * @return Ai1ec_Lessphp_Controller
	 */
	public static function create_lessphp_controller() {
		$lessc = self::create_lessc_instance();
		$lessphp_controller = new Ai1ec_Lessphp_Controller(
			$lessc,
			self::$default_theme_url,
			Ai1ec_Adapters_Factory::create_db_adapter_instance()
		);
		foreach( self::$files as $file ) {
			$file = self::create_less_file_instance(
				$file,
				self::$active_theme_path,
				self::$default_theme_path
			);
			$lessphp_controller->add_file( $file );
		}
		// Set the variable.less file
		$variable_file = self::create_less_file_instance(
			'variables',
			self::$active_theme_path,
			self::$default_theme_path
		);
		$lessphp_controller->set_variable_file( $variable_file );
		return $lessphp_controller;
	}

	/**
	 * @param string $active_theme_path
	 * @param string $default_theme_path
	 * @return Ai1ec_Css_Controller
	 */
	public static function create_css_controller_instance() {
		$aie1c_admin_notices_helper = Ai1ec_Admin_Notices_Helper::get_instance();
		$db_adapter = Ai1ec_Adapters_Factory::create_db_adapter_instance();
		$persistence_context = Ai1ec_Strategies_Factory::create_persistence_context( 
			Ai1ec_Css_Controller::KEY_FOR_PERSISTANCE,
			AI1EC_CACHE_PATH
		);
		$lessphp_controller = self::create_lessphp_controller();
		$controller = new Ai1ec_Css_Controller(
			$persistence_context,
			$lessphp_controller,
			$db_adapter,
			self::$preview_mode,
			Ai1ec_Adapters_Factory::create_template_adapter_instance()
		);
		$controller->set_admin_notices_helper( $aie1c_admin_notices_helper );
		return $controller;
	}

	/**
	 * @param string $name
	 * @return Ai1ec_Less_File
	 */
	public static function create_less_file_instance( $name ) {
		return new Ai1ec_Less_File( $name, self::$active_theme_path, self::$default_theme_path );
	}

	/**
	 * @param string $type
	 * @param array $params
	 * @return Ai1ec_Less_Variable
	 */
	public static function create_less_variable( $type, array $params ) {
		switch( $type ) {
			case 'color':
				$bootstrap_colorpicker = Ai1ec_Helper_Factory::create_bootstrap_colorpicker_instance(
					$params['value'],
					$params['id']
				);
				return new Ai1ec_Less_Variable_Color(
					$params,
					$bootstrap_colorpicker
				);
				break;
			case 'font':
				$select = Ai1ec_Helper_Factory::create_select_instance(
					$params['id']
				);
				return new Ai1ec_Less_Variable_Font(
					$params,
					$select
				);
				break;
		}
	}
}
