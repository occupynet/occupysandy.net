<?php

/**
 * The concrete command that renders the event.
 *
 * @author     Time.ly Network Inc.
 * @since      2.0
 *
 * @package    AI1EC
 * @subpackage AI1EC.Command
 */
class Ai1ec_Command_Render_Event extends Ai1ec_Command_Render_Calendar {

	/* (non-PHPdoc)
	 * @see Ai1ec_Command::is_this_to_execute()
	 */
	public function is_this_to_execute() {
		global $post;
		if (
			! isset( $post ) ||
			! is_object( $post ) ||
			(int)$post->ID <= 0 ||
			post_password_required( $post->ID )
		) {
			return false;
		}
		return $this->_registry->get( 'acl.aco' )->is_our_post_type();
	}

	/* (non-PHPdoc)
	 * @see Ai1ec_Command::do_execute()
	 */
	public function do_execute() {
		// If not on the single event page, return nothing.
		if ( ! is_single() ) {
			return array(
				'data'     => '',
				'is_event' => true,
			);
		}

		// Else proceed with rendering valid event. Fetch all relevant details.
		$instance      = -1;
		if ( isset( $_REQUEST['instance_id'] ) ) {
			$instance    = (int)$_REQUEST['instance_id'];
		}
		$event         = $this->_registry->get(
			'model.event',
			get_the_ID(),
			$instance
		);
		$event_page    = $this->_registry->get( 'view.event.single' );
		$footer_html   = $event_page->get_footer( $event );
		$css = $this->_registry->get( 'css.frontend' )
			->add_link_to_html_for_frontend();
		$js  = $this->_registry->get( 'controller.javascript' )
			->load_frontend_js( false );

		// If requesting event by JSON (remotely), return fully rendered event.
		if ( 'html' !== $this->_request_type ) {
			return array(
				'data'     => array(
					'html'   => $event_page->get_full_article( $event, $footer_html )
				),
				'callback' => Ai1ec_Request_Parser::get_param( 'callback', null ),
			);
		}
		// Else return event details as components.
		return array(
			'data'     => $event_page->get_content( $event ),
			'is_event' => true,
			'footer'   => $footer_html,
		);
	}
}
