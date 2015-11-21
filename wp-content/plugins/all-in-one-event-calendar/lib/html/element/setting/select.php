<?php

/**
 * Renderer of settings page select option.
 *
 * @author       Time.ly Network, Inc.
 * @instantiator new
 * @since        2.0
 * @package      Ai1EC
 * @subpackage   Ai1EC.Html
 */
class Ai1ec_Html_Setting_Select extends Ai1ec_Html_Element_Settings {

	/* (non-PHPdoc)
	 * @see Ai1ec_Html_Element_Settings::render()
	 */
	public function render( $output = '' ) {
		$options = $this->_args['renderer']['options'];
		if ( ! is_array( $options ) ) {
			$callback = explode( ':', $options );
			if ( ! isset( $callback[1] ) ) {
				$options = $this->{$options}();
			} else {
				$value = $this->_args['value'];
				if( false === is_array( $this->_args['value'] ) ){
					$value = array( $this->_args['value'] );
				}
				try {
					$options = $this->_registry->dispatch(
						$callback[0],
						$callback[1]
					);
				} catch (Ai1ec_Bootstrap_Exception $exc) {
					return '';
				}
			}
		}
		$options   = apply_filters( 'ai1ec_settings_select_options' , $options, $this->_args['id'] );
		$fieldsets = array();
		foreach ( $options as $key => &$option ) {
			// if the key is a string, it's an optgroup
			if ( is_string( $key ) ) {
				foreach ( $option as &$opt ) {
					$opt = $this->_set_selected_value( $opt );
				}
			} else {
				$option = $this->_set_selected_value( $option );
				if ( isset( $option['settings'] ) ) {
					$fieldsets[] = $this->_render_fieldset(
						$option['settings'],
						$option['value'],
						$this->_args['id'],
						isset( $option['args']['selected'] )
					);
				}
			}
		}
		$select_args = array();
		$args = array(
			'id'         => $this->_args['id'],
			'label'      => $this->_args['renderer']['label'],
			'attributes' => $select_args,
			'options'    => $options,
			'fieldsets'  => $fieldsets,
		);
		$loader = $this->_registry->get( 'theme.loader' );
		$file   = $loader->get_file( 'setting/select.twig', $args, true );
		return parent::render( $file->get_content() );
	}

	/**
	 * Toggle `selected` attribute according to current selection.
	 *
	 * @param array $option Option being checked.
	 *
	 * @return array Optionally modified option entry.
	 */
	protected function _set_selected_value( array $option ) {
		if ( $option['value'] === $this->_args['value'] ) {
			$option['args'] = array(
				'selected' => 'selected',
			);
		}
		return $option;
	}

	/**
	 * Gets the options for the "Starting day of week" select.
	 *
	 * @return array
	 */
	protected function get_weekdays() {
		$locale  = $this->_registry->get( 'p28n.wpml' );
		$options = array();
		for ( $day_index = 0; $day_index <= 6; $day_index++ ) {
			$option = array(
				'text'  => $locale->get_weekday( $day_index ),
				'value' => $day_index,
			);
			$options[] = $option;
		}
		return $options;
	}

	/**
	 * Renders fieldset with options for selected item.
	 *
	 * @param array  $settings  Settings structure.
	 * @param string $parent_id Option value from parent Html select element.
	 * @param string $select_id Html Select element id.
	 * @param bool   $visible   Whether fieldset is visible or not.
	 *
	 * @return string Html content.
	 *
	 * @throws Ai1ec_Bootstrap_Exception
	 */
	protected function _render_fieldset(
		array $settings,
		$parent_id,
		$select_id,
		$visible = false
	) {
		$setting_renderer = $this->_registry->get(
			'html.element.setting-renderer'
		);
		$global_settings  = $this->_registry->get(
			'model.settings'
		);
		$content = '';
		foreach ( $settings as $id => $setting ) {
			$setting['id']    = $id;
			// fetch value from real setting as this one is some kind of
			// mockup.
			$setting['value'] = $global_settings->get( $id );
			$content .= $setting_renderer->render( $setting );
		}
		$args   = array(
			'parent_id' => $parent_id,
			'contents'  => $content,
			'select_id' => $select_id,
			'visible'   => $visible,
		);
		$loader = $this->_registry->get( 'theme.loader' );
		$file   = $loader->get_file( 'setting/select-fieldsets.twig', $args, true );
		return parent::render( $file->get_content(), false );
	}

}
