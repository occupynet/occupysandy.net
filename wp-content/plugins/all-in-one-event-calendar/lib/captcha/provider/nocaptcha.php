<?php

/**
 * Nocaptcha provider.
 *
 * @author     Time.ly Network Inc.
 * @since      2.2
 *
 * @package    AI1EC
 * @subpackage AI1EC.
 */
class Ai1ec_Captcha_Nocaptcha_Provider extends Ai1ec_Captcha_Provider {

	/**
	 * Returns settings array.
	 *
	 * @param bool $enable_rendering Whether setting HTML will be rendered or not.
	 *
	 * @return array Array of settings.
	 */
	public function get_settings( $enable_rendering = true ) {
		return array(
			'google_nocaptcha_public_key'  => array(
				'type'     => 'string',
				'version'  => AI1ECFS_PLUGIN_NAME,
				'renderer' => array(
					'class'     => 'input',
					'tab'       => 'extensions',
					'item'      => 'interactive',
					'type'      => 'normal',
					'label'     => __(
						'noCAPTCHA public key:',
						AI1ECFS_PLUGIN_NAME
					),
					'condition' => $enable_rendering,
				),
				'value'    => '',
			),
			'google_nocaptcha_private_key' => array(
				'type'     => 'string',
				'version'  => AI1ECFS_PLUGIN_NAME,
				'renderer' => array(
					'class'     => 'input',
					'tab'       => 'extensions',
					'item'      => 'interactive',
					'type'      => 'normal',
					'label'     => __(
						'noCAPTCHA private key:',
						AI1ECFS_PLUGIN_NAME
					),
					'condition' => $enable_rendering,
				),
				'value'    => '',
			),
		);
	}

	/**
	 * Returns captcha challenge.
	 *
	 * @return mixed
	 */
	public function get_challenge() {
		$args = array(
			'nocaptcha_key' => $this->_settings->get(
				'google_nocaptcha_public_key'
			),
		);

		return $this->_theme_loader->get_file(
			'captcha/nocaptcha/challenge.twig',
			$args,
			false
		)->get_content();
	}

	/**
	 * Validates challenge.
	 *
	 * @param array Challenge response data.
	 *
	 * @return mixed
	 */
	public function validate_challenge( array $data ) {

		$response['message'] = Ai1ec_I18n::__(
			'Please try verifying you are human again.'
		);
		$response['success'] = false;

		if ( empty( $data['g-recaptcha-response'] ) ) {
			$response['message'] = Ai1ec_I18n::_(
				'There was an error reading the human verification data. Please try again.'
			);
			$response['success'] = false;
		}
		$url       = add_query_arg(
			array(
				'secret'   => $this->_settings->get(
					'google_nocaptcha_private_key'
				),
				'response' => $data['g-recaptcha-response'],
			),
			'https://www.google.com/recaptcha/api/siteverify'
		);
		$json_resp = wp_remote_get( $url );
		if ( is_wp_error( $json_resp ) ) {
			return $response;
		}
		$resp = json_decode( $json_resp['body'], true );
		if (
			isset( $resp['success'] ) &&
			$resp['success']
		) {
			$response = array(
				'success' => true,
			);
		}

		return $response;

	}

	/**
	 * Returns provider name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'Google No CAPTCHA';
	}
}
