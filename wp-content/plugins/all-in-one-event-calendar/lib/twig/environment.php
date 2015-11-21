<?php

/**
 * Wrapper for Twig_Environment
 *
 * @author     Time.ly Network Inc.
 * @since      2.0
 *
 * @package    AI1EC
 * @subpackage AI1EC.Twig
 */
class Ai1ec_Twig_Environment extends Twig_Environment {

	/**
	 * @var Ai1ec_Registry_Object The registry Object.
	 */
	protected $_registry = null;

	/**
	 * Loads a template by name.
	 *
	 * @param string  $name	 The template name
	 * @param integer $index The index if it is an embedded template
	 *
	 * @return Twig_TemplateInterface A template instance representing the given template name
	 */
	public function loadTemplate( $name, $index = null ) {
		try {
			return parent::loadTemplate( $name, $index );
		} catch ( RuntimeException $excpt ) {
			/*
			 * We should not rely on is_writable - WP Engine case.
			 * I've made twig directory read-only and is_writable was returning
			 * true.
			 */
			$this->_registry->get(
				'twig.cache'
			)->set_unavailable( $this->cache );
			/*
			 * Some copy paste from original Twig method. Just to avoid first
			 * error during rendering.
			 */
			$cls = $this->getTemplateClass( $name, $index );
			eval(
				'?>' .
				$this->compileSource(
					$this->getLoader()->getSource( $name ),
					$name
				)
			);
			return $this->loadedTemplates[$cls] = new $cls($this);
		}
	}

	/**
	 * Set Ai1ec_Registry_Object
	 *
	 * @param Ai1ec_Registry_Object $registry
	 *
	 * @return void
	 */
	public function set_registry( Ai1ec_Registry_Object $registry ) {
		$this->_registry = $registry;
	}

	/**
	 * Renders a template.
	 *
	 * @param string $name    The template name
	 * @param array  $context An array of parameters to pass to the template
	 *
	 * @return string The rendered template
	 *
	 * @throws Twig_Error_Loader  When the template cannot be found
	 * @throws Twig_Error_Syntax  When an error occurred during compilation
	 * @throws Twig_Error_Runtime When an error occurred during rendering
	 */
	public function render( $name, array $context = array() ) {
		try {
			return parent::render( $name, $context );
		} catch ( Exception $excpt ) {
			if (
				! defined( 'AI1EC_DEBUG' ) ||
				! AI1EC_DEBUG
			) {
				return $this->_handle_render_exception( $name, $context );
			}
			throw $excpt;
		}
	}

	/**
	 * Switches calendar theme to vortex.
	 *
	 * @return void Method does not return.
	 *
	 * @throws Ai1ec_Bootstrap_Exception
	 */
	public function switch_to_vortex() {
		$this->_registry->get( 'theme.loader' )->switch_to_vortex();
	}

	/**
	 * Handles rendering exception. Switches to Vortex theme and tries to
	 * re-render view. If it doesn't help it returns default warning.
	 *
	 * @param string $name    Template name.
	 * @param array  $context An array of parameters to pass to the template.
	 *
	 * @return string Rendered or default error string.
	 */
	protected function _handle_render_exception( $name, array $context ) {
		register_shutdown_function( array( $this, 'switch_to_vortex' ) );
		return '<div class="ai1ec-alert ai1ec-alert-danger">' .
			Ai1ec_I18n::__( 'The calendar is temporarily disabled due to a rendering error. Please <a href="javascript:location.reload();">reload the page</a>.' ) .
			'</div>';
	}

}
