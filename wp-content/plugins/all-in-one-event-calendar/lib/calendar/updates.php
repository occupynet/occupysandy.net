<?php

/**
 * Calendar state container.
 *
 * @author     Time.ly Network Inc.
 * @since      2.3
 *
 * @package    AI1EC
 * @subpackage AI1EC.Lib.Calendar
 */
class Ai1ec_Calendar_Updates extends Ai1ec_Base {

	/**
	 * Primary update endpoint.
	 *
	 * @const string
	 */
	const PRIMARY_END_POINT = 'https://update.time.ly/update';

	/**
	 * Alternative update endpoint.
	 *
	 * @const string
	 */
	const SECONDARY_END_POINT = 'http://cdn.update.time.ly/updates/updates.json';

	/**
	 * Check updates and return additional info.
	 *
	 * @param mixed $transient_data Current transient data.
	 *
	 * @return mixed Modified transient data.
	 */
	public function check_updates( $transient_data ) {
		if ( empty( $transient_data ) ) {
			return $transient_data;
		}
		$updates = $this->_download_updates();
		if ( empty( $updates ) ) {
			return $transient_data;
		}
		$plugins = get_plugins();
		foreach ( $updates as $plugin => $update_data ) {
			/** @var $plugin_data array */
			$plugin_data = isset( $plugins[$plugin] ) ? $plugins[$plugin] : null;
			if (
				empty( $plugin_data['Version'] ) ||
				version_compare( $plugin_data['Version'], $update_data['new_version'], '>=' )
			) {
				continue;
			}
			$transient_data->response[$plugin] = (object) $update_data;
		}

		return $transient_data;
	}

	/**
	 * Get plugin data from retrieved and cached data.
	 *
	 * @param array      $data   Current data.
	 * @param string     $action Action name.
	 * @param array|null $args   Query arguments.
	 *
	 * @return mixed Plugin data.
	 */
	public function plugins_api_filter( $data, $action = '', $args = null ) {
		if (
			'plugin_information' !== $action ||
			empty( $args->slug ) ||
			'all-in-one-event-calendar' !== substr( $args->slug, 0, 25 )
		) {
			return $data;
		}
		$update_data       = get_site_transient( 'update_plugins' );
		$plugin_identifier = $args->slug . '/' . $args->slug . '.php';
		if ( empty( $update_data->response[$plugin_identifier] ) ) {
			return $data;
		}

		return $update_data->response[$plugin_identifier];
	}

	/**
	 * Clear updates related transients.
	 *
	 * @return void Method does not return.
	 */
	public function clear_transients() {
		delete_site_transient( 'ai1ec_update_plugins' );
		delete_site_transient( 'update_plugins' );
	}

	/**
	 * Download update info. Check local transient for cached data.
	 *
	 * @return array|mixed|null|object Update data.
	 */
	protected function _download_updates() {
		$cached_updates = get_site_transient( 'ai1ec_update_plugins' );
		if ( $cached_updates ) {
			return $cached_updates;
		}
		// try first endpoint
		$response = $this->_get_data_from_endpoint( self::PRIMARY_END_POINT );
		if ( is_wp_error( $response ) ) {
			$response = $this->_get_data_from_endpoint( self::SECONDARY_END_POINT );
		}
		if ( is_wp_error( $response ) ) {
			return null;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		set_site_transient( 'ai1ec_update_plugins', $data, 30 * MINUTE_IN_SECONDS );

		return $data;
	}

	/**
	 * Get update data from given endpoint.
	 *
	 * @param string $endpoint Endpoint URI.
	 *
	 * @return array|WP_Error Request result.
	 */
	protected function _get_data_from_endpoint( $endpoint ) {
		return wp_remote_get(
			$endpoint,
			array (
				'timeout'   => 15,
				'sslverify' => false,
			)
		);
	}
}