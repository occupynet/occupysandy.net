<?php

/**
 * @author Timely Network Inc
 *
 * This class is responsible of Handling CSS generation functions
 */

class Ai1ec_Css_Controller {

	const GET_VARIBALE_NAME                 = 'ai1ec_render_css';
	// This is for testing purpose, set it to false when committing for production
	const PARSE_LESS_FILES_AT_EVERY_REQUEST = false;

	const KEY_FOR_PERSISTANCE = 'ai1ec_parsed_css';
	/**
	 * @var Ai1ec_Css_Persistence_Helper
	 */
	private $persistance_context;

	/**
	 * @var Ai1ec_Lessphp_Controller
	 */
	private $lessphp_controller;

	/**
	 * @var Ai1ec_Wordpress_Db_Adapter
	 */
	private $db_adapter;

	/**
	 * @var boolean
	 */
	private $preview_mode;

	/**
	 * @var Ai1ec_Template_Adapter
	 */
	private $template_adapter;

	/**
	 * @var Ai1ec_Admin_Notices_Helper
	 */
	private $admin_notices_helper;

	/**
	 * @param Ai1ec_Admin_Notices_Helper $admin_notices_helper
	 */
	public function set_admin_notices_helper( &$admin_notices_helper ) {
		$this->admin_notices_helper = $admin_notices_helper;
	}

	public function __construct(
		Ai1ec_Persistence_Context $persistance_context,
		Ai1ec_Lessphp_Controller $lessphp_controller,
		Ai1ec_Wordpress_Db_Adapter $db_adapter,
		$preview_mode,
		Ai1ec_Template_Adapter $template_adapter
	) {
		$this->persistance_context = $persistance_context;
		$this->lessphp_controller  = $lessphp_controller;
		$this->db_adapter          = $db_adapter;
		$this->preview_mode        = $preview_mode;
		$this->template_adapter    = $template_adapter;
	}

	/**
	 * Renders the css for our frontend.
	 *
	 * Sets etags to avoid sending not needed data
	 */
	public function render_css() {
		header( 'Content-Type: text/css' );
		// Aggressive caching to save future requests from the same client.
		$etag = '"' . md5( __FILE__ . $_GET[self::GET_VARIBALE_NAME] ) . '"';
		header( 'ETag: ' . $etag );
		$max_age = 31536000;
		header(
			'Expires: ' .
			gmdate(
				'D, d M Y H:i:s',
				Ai1ec_Time_Utility::current_time() + $max_age
			) .
			' GMT'
		);
		header( 'Cache-Control: public, max-age=' . $max_age );
		if (
			empty( $_SERVER['HTTP_IF_NONE_MATCH'] ) ||
			$etag !== stripslashes( $_SERVER['HTTP_IF_NONE_MATCH'] )
		) {
			// compress data if possible
			if ( extension_loaded( 'zlib' ) ) {
				ob_start( 'ob_gzhandler' );
			}
			$content = $this->get_compiled_css();
			echo $content;
			ob_end_flush();
		} else {
			// Not modified!
			status_header( 304 );
		}
		// We're done!
		exit( 0 );
	}

	/**
	 *
	 * @param string $css
	 * @throws Ai1ec_Cache_Write_Exception
	 */
	public function update_persistence_layer( $css ) {
		try {
			$this->persistance_context->write_data_to_persistence( $css );
			$this->save_less_parse_time();
		}
		catch ( Ai1ec_Cache_Write_Exception $e ) {
			throw $e;
		}
	}


	/**
	 * Get the url to retrieve the css
	 *
	 * @return string
	 */
	public function get_css_url() {
		$time = $this->db_adapter->get_data_from_config( self::GET_VARIBALE_NAME );
		return $this->template_adapter->get_site_url() . "/?" . self::GET_VARIBALE_NAME . "=$time";
	}

	/**
	 * Create the link that will be added to the frontend
	 */
	public function add_link_to_html_for_frontend() {
		$preview = '';
		if( true === $this->preview_mode ) {
			// bypass browser caching of the css
			$now = strtotime( 'now' );
			$preview = "&preview=1&nocache={$now}&ai1ec_stylesheet=" . $_GET['ai1ec_stylesheet'];
		}
		$url = $this->get_css_url() . $preview;
		$this->template_adapter->enqueue_script( 'ai1ec_style', $url );
	}

	/**
	 * Invalidate the persistence layer only after a succesful compile of the
	 * LESS files.
	 *
	 * @param array $variables
	 * @param boolean $update_persistence
	 * @return boolean
	 */
	public function invalidate_cache( array $variables = null, $update_persistence = false ) {
		// Reset the parse time to force a browser reload of the CSS, whether we are
		// updating persistence or not.
		$this->save_less_parse_time();
		try {
			// Try to parse the css
			$css = $this->lessphp_controller->parse_less_files( $variables );
			if ( $update_persistence ) {
				$this->update_persistence_layer( $css );
			} else {
				$this->persistance_context->delete_data_from_persistence();
			}
		} catch ( Ai1ec_Cache_Write_Exception $e ) {
			$message = Ai1ec_Helper_Factory::create_admin_message_instance(
				'<p>' . __( "The LESS file compiled correctly but there was an error while saving the generated CSS to persistence.", AI1EC_PLUGIN_NAME ) . '</p>',
				__( "An error ocurred while updating CSS", AI1EC_PLUGIN_NAME )
			);
			$this->admin_notices_helper->add_renderable_children( $message );
			// this means a correct parsing but an error in saving to persistance
			return false;
		} catch ( Exception $e ) {
			$message = Ai1ec_Helper_Factory::create_admin_message_instance(
				sprintf(
					__( '<p>The message returned was: <em>%s</em></p>', AI1EC_PLUGIN_NAME ),
					$e->getMessage()
				),
				__( "An error occurred while compiling LESS files", AI1EC_PLUGIN_NAME )
			);
			$this->admin_notices_helper->add_renderable_children( $message );
			return false;
		}
		return true;
	}

	/**
	 * update the variables array on the db
	 *
	 * @param Ai1ec_Db_Adapter $db_adapter
	 *
	 * @return void|array $variables the variables array
	 *
	 */
	public function handle_less_variables_page_form_post() {
		// It's really early in the WP lifecycle, so we have to include manually.
		require_once( ABSPATH . 'wp-includes/pluggable.php' );
		if ( ! wp_verify_nonce( $_POST['simple_page_nonce'], 'update' ) ) {
			die( "Security check failed" );
		}
		$variables = array();
		// Handle updating of variables
		if ( isset( $_POST[Ai1ec_Less_Variables_Editing_Page::FORM_SUBMIT_NAME] ) ) {
			$variables = $this->db_adapter->get_data_from_config(
				Ai1ec_Lessphp_Controller::DB_KEY_FOR_LESS_VARIABLES
			);
			foreach ( $variables as $variable_name => $variable_params ) {
				if ( isset( $_POST[$variable_name] ) ) {
					// Avoid problems for those who are foolish enough to leave php.ini
					// settings at their defaults, which has magic quotes enabled.
					if ( get_magic_quotes_gpc() ) {
						$_POST[$variable_name] = stripslashes( $_POST[$variable_name] );
					}
					// update the original array
					$variables[$variable_name]['value'] = $_POST[$variable_name];
				}
			}
		}
		// Handle reset of theme variables.
		if ( isset( $_POST[Ai1ec_Less_Variables_Editing_Page::FORM_SUBMIT_RESET_THEME] ) ) {
			$variables = $this->lessphp_controller->get_less_variable_data_from_config_file(
				Ai1ec_Less_Factory::create_less_file_instance(
					Ai1ec_Less_File::USER_VARIABLES_FILE
				)
			);
		}
		$this->update_variables_and_compile_css(
			$variables,
			isset(
				$_POST[Ai1ec_Less_Variables_Editing_Page::FORM_SUBMIT_RESET_THEME]
			)
		);
	}

	/**
	 * Update the less variables on the DB and recompile the CSS
	 *
	 * @param array $variables
	 * @param boolean $resetting are we resetting or updating variables?
	 */
	private function update_variables_and_compile_css( array $variables, $resetting ) {
		$no_parse_errors = $this->invalidate_cache( $variables, true );
		if ( $no_parse_errors ) {
			$this->db_adapter->write_data_to_config(
				Ai1ec_Lessphp_Controller::DB_KEY_FOR_LESS_VARIABLES,
				$variables
			);
			$message = Ai1ec_Helper_Factory::create_admin_message_instance(
				sprintf(
					'<p>' . __(
						"Theme options were updated successfully. <a href='%s'>Visit site</a>",
						AI1EC_PLUGIN_NAME
					) . '</p>',
					get_site_url()
				)
			);
			if ( true === $resetting ) {
				$message = Ai1ec_Helper_Factory::create_admin_message_instance(
					sprintf(
						'<p>' . __(
							"Theme options were successfully reset to their default values. <a href='%s'>Visit site</a>",
							AI1EC_PLUGIN_NAME
						) . '</p>',
						get_site_url()
					)
				);
			}
			$message->set_message_type( 'updated' );
			$this->admin_notices_helper->add_renderable_children( $message );
		}
	}
	/**
	 * Try to get the CSS from cache.
	 * If it's not there re-generate it and save it to cache
	 * If we are in preview mode, recompile the css using the theme present in the url.
	 *
	 */
	private function get_compiled_css() {
		try {
			// If we want to force a recompile, we throw an exception.
			if( $this->preview_mode === true || self::PARSE_LESS_FILES_AT_EVERY_REQUEST === true ) {
				throw new Ai1ec_Cache_Not_Set_Exception();
			}else {
				// This throws an exception if the key is not set
				$css = $this->persistance_context->get_data_from_persistence();
				return $css;
			}
		} catch ( Ai1ec_Cache_Not_Set_Exception $e ) {
			// If we are in preview mode we force a recompile and we pass the variables.
			if( $this->preview_mode ) {
				return $this->lessphp_controller->parse_less_files(
					$this->lessphp_controller->get_less_variable_data_from_config_file(
						Ai1ec_Less_Factory::create_less_file_instance(
							Ai1ec_Less_File::USER_VARIABLES_FILE
						)
					)
				);
			} else {
				$css = $this->lessphp_controller->parse_less_files();
			}
			try {
				$this->update_persistence_layer( $css );
				return $css;
			} catch ( Ai1ec_Cache_Write_Exception $e ) {
				// If something is really broken, still return the css.
				// This means we parse it every time. This should never happen.
				return $css;
			}
		}
	}

	/**
	 * Save the compile time to the db so that we can use it to build the link
	 */
	private function save_less_parse_time() {
		$this->db_adapter->write_data_to_config(
			self::GET_VARIBALE_NAME,
			Ai1ec_Time_Utility::current_time()
		);
	}
}
