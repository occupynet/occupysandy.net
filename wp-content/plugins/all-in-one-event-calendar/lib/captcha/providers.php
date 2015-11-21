<?php

/**
 * Captcha providers handler class.
 *
 * @author     Time.ly Network Inc.
 * @since      2.2
 *
 * @package    AI1EC
 * @subpackage AI1EC.Captcha
 */
class Ai1ec_Captcha_Providers extends Ai1ec_Base {

	/**
	 * List of available captcha providers.
	 *
	 * @var array
	 */
	protected $_providers = null;

	/**
	 * Returns list of available providers.
	 *
	 * @return array List of providers.
	 */
	public function get_providers() {
		if ( null !== $this->_providers ) {
			return $this->_providers;
		}
		$built_in      = array(
			'Ai1ec_Captcha_Recaptcha_Provider',
			'Ai1ec_Captcha_Nocaptcha_Provider',
		);
		$all_providers = apply_filters( 'ai1ec_captcha_providers', $built_in );
		if ( empty( $all_providers ) ) {
			return array();
		}
		$providers = array();
		foreach ( $all_providers as $provider_class ) {
			$provider = new $provider_class( $this->_registry );
			if ( ! $provider instanceof Ai1ec_Captcha_Provider ) {
				continue;
			}
			$providers[] = $provider;
		}

		return $providers;
	}

	/**
	 * Returns providers settings.
	 *
	 * @return array Providers settings.
	 */
	public function get_providers_as_settings() {
		$all_providers = $this->get_providers();
		$settings      = array();
		foreach ( $all_providers as $provider ) {
			$settings[] = array(
				'text'     => $provider->get_name(),
				'value'    => get_class( $provider ),
				'settings' => $provider->get_settings(),
			);
		}

		return $settings;
	}
}