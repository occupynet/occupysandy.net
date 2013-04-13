<?php

/**
 *
 * @author Timely Network Inc
 *        
 *        
 */

class A1iec_Bootstrap_Message extends Ai1ec_Html_Element {
	
	/**
	 * @var string
	 */
	private $message;
	
	/**
	 * @var string
	 */
	private $type;

	public function __construct( $message, $type = 'success' ) {
		parent::__construct();
		$this->message = $message;
		$this->type    = $type;
	}
	
	public function render() {
		$close = Ai1ec_Helper_Factory::create_generic_html_tag( 'a' );
		$close->set_attribute( 'data-dismiss', 'alert' );
		$close->add_class( 'close' );
		$close->set_attribute( 'href', '#' );
		$close->set_text( 'x' );
		$container = Ai1ec_Helper_Factory::create_generic_html_tag( 'div' );
		$container->add_class( 'alert' );
		switch( $this->type ) {
			case "success": 
				$container->add_class( 'alert-success' );
				break;
			case "error":
				$container->add_class( 'alert-error' );
				break;
			default:
				break;
		}
		$container->set_prepend_text( false );
		$container->set_text( $this->message );
		$container->add_renderable_children( $close );
		$container->render();
	}
}