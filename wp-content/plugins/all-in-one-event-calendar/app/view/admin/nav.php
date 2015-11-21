<?php

/**
 * Admin-side navigation elements rendering.
 *
 * @author       Time.ly Network, Inc.
 * @since        2.0
 * @package      Ai1EC
 * @subpackage   Ai1EC.View
 */
class Ai1ec_View_Admin_Navigation extends Ai1ec_Base {

	/**
	 * Adds a link to Settings page in plugin list page.
	 *
	 * @param  array $links List of available links.
	 *
	 * @return array Modified links list.
	 */
	public function plugin_action_links( $links ) {
		$settings_link = sprintf(
			Ai1ec_I18n::__( '<a href="%s">Settings</a>' ),
			ai1ec_admin_url( AI1EC_SETTINGS_BASE_URL )
		);
		array_unshift( $links, $settings_link );
		if ( current_user_can( 'activate_plugins' ) ) {
			$updates_link = sprintf(
				Ai1ec_I18n::__( '<a href="%s">Check for updates</a>' ),
				ai1ec_admin_url( AI1EC_FORCE_UPDATES_URL )
			);
			array_push( $links, $updates_link );
		}
		return $links;
	}

}