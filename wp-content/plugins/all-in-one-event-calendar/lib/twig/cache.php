<?php

/**
 * The class which handles Twig cache rescan process.
 *
 * @author     Time.ly Network Inc.
 * @since      2.0
 *
 * @package    AI1EC
 * @subpackage AI1EC.Twig
 */
class Ai1ec_Twig_Cache extends Ai1ec_Base {

	/**
	 * Rescan cache for writable directory.
	 *
	 * @return void
	 */
	public function rescan() {
		$cache_dir = $this->_registry->get( 'theme.loader' )
				->get_cache_dir( true );
		$render_json    = $this->_registry->get(
				'http.response.render.strategy.json'
		);
		$output['data'] = array(
			'state' => (int)(false !== $cache_dir),
		);
		$render_json->render( $output );
	}

	/**
	 * Sets Twig cache as unavailable and notifies admin.
	 *
	 * @param string $cache_dir Cache dir.
	 *
	 * @throws Ai1ec_Bootstrap_Exception
	 */
	public function set_unavailable( $cache_dir = AI1EC_TWIG_CACHE_PATH ) {
		$this->_registry->get( 'model.settings' )
			->set( 'twig_cache', AI1EC_CACHE_UNAVAILABLE );
	}

}
