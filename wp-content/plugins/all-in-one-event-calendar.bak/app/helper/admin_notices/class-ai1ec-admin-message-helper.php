<?php

/** 
 * @author Nicola
 * 
 * 
 */

class Ai1ec_Admin_Message_Helper extends Ai1ec_Html_Element {

	/**
	 * @var string
	 */
	private $label;

	/**
	 * @var string
	 */
	private $message;

	/**
	 * An array with two keys:
	 *   class: the class to give to the button
	 *   value: the value of the button
	 * @var array
	 */
	private $button;

	/**
	 * @var string
	 */
	private $message_type = "error";

	/**
	 * @var Ai1ec_View_Helper
	 */
	private $ai1ec_view_helper;

	/**
	 * @param string $label
	 */
	public function set_label( $label ) {
		$this->label = $label;
	}

	/**
	 * @param string $message
	 */
	public function set_message( $message ) {
		$this->message = $message;
	}

	/**
	 * @param string $button
	 */
	public function set_button( $button ) {
		$this->button = $button;
	}

	/**
	 * @param string $message_type
	 */
	public function set_message_type( $message_type ) {
		$this->message_type = $message_type;
	}

	public function __construct( $message, Ai1ec_View_Helper $ai1ec_view_helper ) {
		parent::__construct();
		$this->message = $message;
		$this->ai1ec_view_helper = $ai1ec_view_helper;
	}
	
	public function render() {
		$args = array(
			'label'        => $this->label,
			'msg'          => $this->message,
		);
		if( isset( $this->button ) ) {
			$args['button'] = (object) $this->button;
		}
		if( isset( $this->message_type ) ) {
			$args['message_type'] = $this->message_type;
		}
		$this->ai1ec_view_helper->display_admin( 'admin_notices.php', $args );
	}
}