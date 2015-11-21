<?php

/**
 * Missing class setting-renderer description.
 *
 * @author     Time.ly Network Inc.
 * @since      2.2
 *
 * @package    AI1EC
 * @subpackage AI1EC.
 */

class Ai1ec_Html_Setting_Renderer extends Ai1ec_Base {

	/**
	 * Renders single setting.
	 *
	 * @param array $setting Setting structure.
	 *
	 * @return string Rendered content.
	 *
	 * @throws Ai1ec_Bootstrap_Exception
	 */
	public function render( array $setting ) {
		$renderer_name = $setting['renderer']['class'];
		$renderer      = null;
		try {
			$renderer = $this->_registry->get(
				'html.element.setting.' . $renderer_name,
				$setting
			);
		} catch ( Ai1ec_Bootstrap_Exception $exception ) {
			$renderer = $this->_registry->get(
				'html.element.setting.input',
				$setting
			);
		}

		return $renderer->render();
	}
}