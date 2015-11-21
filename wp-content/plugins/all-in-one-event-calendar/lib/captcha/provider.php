<?php

/**
 * Ai1ec_Captcha_Provider interface.
 *
 * @author     Time.ly Network Inc.
 * @since      2.2
 *
 * @package    AI1EC
 * @subpackage AI1EC.Captcha
 */
abstract class Ai1ec_Captcha_Provider extends Ai1ec_Base {

	/**
	 * Settings object.
	 *
	 * @var Ai1ec_Settings
	 */
	protected $_settings = null;

	/**
	 * Theme loader object.
	 *
	 * @var Ai1ec_Theme_Loader
	 */
	protected $_theme_loader = null;

	/**
	 * Whether provider is configured or not.
	 *
	 * @var bool
	 */
	protected $_is_configured = null;

	/**
	 * Constructor.
	 *
	 * @param Ai1ec_Registry_Object $registry
	 *
	 * @return Ai1ec_Captcha_Provider
	 *
	 * @throws Ai1ec_Bootstrap_Exception
	 */
	public function __construct( Ai1ec_Registry_Object $registry ) {
		parent::__construct( $registry );
		$this->_settings     = $registry->get( 'model.settings' );
		$this->_theme_loader = $registry->get( 'theme.loader' );
	}

	/**
	 * Returns settings array.
	 *
	 * @param bool $enable_rendering Whether setting HTML will be rendered or not.
	 *
	 * @return array Array of settings.
	 */
	abstract public function get_settings( $enable_rendering = true );

	/**
	 * Returns captcha challenge.
	 *
	 * @return mixed
	 */
	abstract public function get_challenge();

	/**
	 * Validates challenge.
	 *
	 * @param array Challenge response data.
	 *
	 * @return mixed
	 */
	abstract public function validate_challenge( array $data );

	/**
	 * Returns provider name.
	 *
	 * @return string
	 */
	abstract public function get_name();

	/**
	 * Returns whether provider is properly configured or not.
	 *
	 * @return bool
	 */
	public function is_configured() {
		if ( null !== $this->_is_configured ) {
			return $this->_is_configured;
		}
		$this->_is_configured = true;
		foreach ( $this->get_settings() as $key => $setting ) {
			$value = $this->_settings->get( $key );
			if ( empty( $value ) ) {
				$this->_is_configured = false;
				break;
			}
		}

		return $this->_is_configured;
	}
}