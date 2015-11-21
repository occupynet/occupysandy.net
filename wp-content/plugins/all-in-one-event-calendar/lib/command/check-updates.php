<?php

/**
 * The concrete command that compiles CSS.
 *
 * @author     Time.ly Network Inc.
 * @since      2.3
 *
 * @package    AI1EC
 * @subpackage AI1EC.Command
 */
class Ai1ec_Command_Check_Updates extends Ai1ec_Command {

	/*
	 * (non-PHPdoc) @see Ai1ec_Command::is_this_to_execute()
	 */
	public function is_this_to_execute() {
		return isset( $_GET['ai1ec_force_updates'] );
	}

	/* (non-PHPdoc)
	 * @see Ai1ec_Command::set_render_strategy()
	*/
	public function set_render_strategy( Ai1ec_Request_Parser $request ) {
		$this->_render_strategy = $this->_registry->get(
			'http.response.render.strategy.redirect'
		);
	}

	/* (non-PHPdoc)
	 * @see Ai1ec_Command::do_execute()
	*/
	public function do_execute() {
		$this->_registry->get( 'calendar.updates' )->clear_transients();

		return array (
			'url'        => ai1ec_admin_url( 'plugins.php' ),
			'query_args' => array ()
		);
	}
}