<?php
/**
 * Theme backward compatibility check.
 *
 * @author     Time.ly Network Inc.
 * @since      2.2
 *
 * @package    AI1EC
 * @subpackage AI1EC.Lib.Compatibility
 */
class Ai1ec_Compatibility_Check extends Ai1ec_Base {

	/**
	 * @var null|bool Calculated response.
	 */
	protected $_use_backward_compatibility = null;

	/**
	 * Returns whether calendar is using backward compatibility or not.
	 *
	 * @return bool|null Result
	 *
	 * @throws Ai1ec_Bootstrap_Exception
	 */
	public function use_backward_compatibility() {
		if ( null === $this->_use_backward_compatibility ) {
			$this->_use_backward_compatibility = (
				AI1EC_THEME_COMPATIBILITY_FER &&
				! $this->_registry->get(
					'model.settings'
				)->get( 'ai1ec_use_frontend_rendering', false )
			);
		}
		return $this->_use_backward_compatibility;
	}

	/**
	 * Observes settings changes. If setting ai1ec_use_frontend_rendering
	 * is changed and set to true perfoms theme check.
	 *
	 * @param array $old_options Old options array.
	 * @param array $new_options New options array.
	 *
	 * @return void Method does not return.
	 *
	 * @throws Ai1ec_Bootstrap_Exception
	 */
	public function ai1ec_settings_observer( $old_options, $new_options ) {
		$old_value = isset( $old_options['ai1ec_use_frontend_rendering'] )
			? (bool)$old_options['ai1ec_use_frontend_rendering']['value']
			: null;
		$new_value = isset( $new_options['ai1ec_use_frontend_rendering'] )
			? (bool)$new_options['ai1ec_use_frontend_rendering']['value']
			: null;
		if (
			$old_value === $new_value ||
			! $new_value
		) {
			return;
		}
		if ( $this->is_current_theme_outside_core() ) {
			$this->_registry->get( 'notification.admin' )->store(
				Ai1ec_I18n::__( 'You have turned on Frontend Rendering and you are using a custom calendar theme. If your theme does not support Frontend Rendering, your calendar may not work correctly.' ),
				'error',
				0,
				array( Ai1ec_Notification_Admin::RCPT_ADMIN ),
				true
			);
		}
	}

	/**
	 * Returns whether current calendar theme is located under core directory
	 * or not.
	 *
	 * @return bool Result
	 *
	 * @throws Ai1ec_Bootstrap_Exception
	 */
	public function is_current_theme_outside_core() {
		$option     = $this->_registry->get( 'model.option' );
		$cur_theme  = $option->get( 'ai1ec_current_theme', array() );
		$theme_root = dirname( AI1EC_DEFAULT_THEME_ROOT );
		return (
			isset( $cur_theme['theme_root'] ) &&
			$theme_root !== dirname( $cur_theme['theme_root'] )
		);
	}
}
