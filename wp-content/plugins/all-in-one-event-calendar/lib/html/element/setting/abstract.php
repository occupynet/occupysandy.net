<?php

/**
 * Abstract class to accelerate settings page snippets development.
 *
 * @author     Time.ly Network, Inc.
 * @since      2.0
 * @package    Ai1EC
 * @subpackage Ai1EC.Html
 */
abstract class Ai1ec_Html_Element_Settings extends Ai1ec_Base
    implements Ai1ec_Html_Element_Interface {

	/**
	 * @var Ai1ec_Html_Helper Instance of HTML helper.
	 */
	protected $_html = NULL;

	/**
	 * @var array Map of arbitrary arguments passed to an element.
	 */
	protected $_args = array();

	/**
	 * Constructor accepts system as injectable and requests HTML helper.
	 *
	 * @param Ai1ec_Registry_Object $system Injected system argument.
	 *
	 * @return void Constructor does not return.
	 */
	public function __construct(
		Ai1ec_Registry_Object $registry,
		array $args
	) {
		parent::__construct( $registry );
		$this->_args = $args;
		$this->_html = $registry->get( 'html.helper' );
	}

	/**
	 * Set value within current object scope
	 *
	 * Value name is formed as {$attribute} with underscore ('_') prefixed.
	 *
	 * @param string $attribute Name of attribute to set.
	 * @param mixed  $value     Value to set for attribute.
	 *
	 * @return Ai1ec_Html_Element_Settings Instance of self.
	 */
	public function set( $attribute, $value ) {
		$this->{'_' . $attribute} = $value;
		return $this;
	}

	/**
	 * Override to include any initialization logics.
	 *
	 * @return void Method output is ignored.
	 */
	protected function _initialize() {
	}

	/**
	 * Generate settings output line.
	 *
	 * @param string $output Generated output to finalize.
	 * @param bool   $wrap   Whether content should be wrapped with div or not.
	 *
	 * @return string Finalized HTML snippet.
	 */
	public function render( $output = '', $wrap = true ) {
		if ( isset( $this->_args['renderer']['condition'] ) ) {
			$condition = $this->_args['renderer']['condition'];
			if ( is_bool( $condition ) ) {
				$render = $condition;
			} else {
				$callback = explode( ':', $this->_args['renderer']['condition'] );
				try {
					$render = $this->_registry->dispatch(
						$callback[0],
						$callback[1]
					);
				} catch (Ai1ec_Bootstrap_Exception $exception) {
					$render = '';
				}
			}
			if ( ! $render ) {
				return '';
			}
		}
		if ( ! $wrap ) {
			return $output;
		}
		return '<div class="ai1ec-form-group">' . $output . '</div>';
	}

}
