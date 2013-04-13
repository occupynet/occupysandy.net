<?php

/**
 *
 * @author then.ly
 *        
 *        
 */
class Ai1ec_Script_Wordpress_Adapter implements Ai1ec_Scripts {

	/**
	 * The scripts that must be echoed in the footer for the admin pages
	 *
	 * @var string
	 */
	private $scripts_in_footer = '';

	/**
	 * The scripts that must be echoed in the footer for the frontend
	 *
	 * @var string
	 */
	private $scripts_in_footer_frontend = '';

	/**
	 * echo the scripts in the footer for the admin. Since this action is enqueued with priority 10
	 * this will happen before the script that requires javascript for the page is loaded as
	 * scripts are loaded with priority = 20 and i need this script before that ( because that script use this as a dependency )
	 */
	public function print_admin_script_footer_for_wordpress_32() {
		echo $this->scripts_in_footer;
	}

	/**
	 * echo the scripts in the footer for the frontend. Since this action is enqueued with priority 10
	 * this will happen before the script that requires javascript for the page is loaded as
	 * scripts are loaded with priority = 20 and i need this script before that ( because that script use this as a dependency )
	 */
	public function print_frontend_script_footer_for_wordpress_32() {
		echo $this->scripts_in_footer_frontend;
	}

	/**
	 *
	 * @param $name string
	 *        Unique identifer for the script
	 *       
	 * @param $file string
	 *        Filename of the script
	 *       
	 * @param $deps array
	 *        Dependencies of the script
	 *       
	 * @param $in_footer bool
	 *        Whether to add the script to the footer of the page
	 *       
	 *       
	 * @return void
	 *
	 * @see Ai1ec_Scripts::enqueue_admin_script()
	 *
	 */
	public function enqueue_admin_script( $name, $file, $deps = array(), $in_footer = FALSE ) {
		if( ! $file || empty( $file ) ) {
			throw new Ai1ec_File_Not_Provided( "You need to specify a script file." );
		}
		
		$_file = AI1EC_ADMIN_THEME_JS_PATH . '/' . $file;
		
		if( ! file_exists( $_file ) ) {
			throw new Ai1ec_File_Not_Found( "The specified file " . $_file . " doesn't exist." );
		} else {
			$file = AI1EC_ADMIN_THEME_JS_URL . '/' . $file;
			wp_enqueue_script( $name, $file, $deps, AI1EC_VERSION, $in_footer );
		}
	}

	/**
	 * Defines a simple module that can be later imported by require js. Useful for translations and so on.
	 *
	 * @param string $handle The script handle that was registered or used in script-loader
	 * @param string $object_name Name for the created requirejs module. This is passed directly so it should be qualified JS variable /[a-zA-Z0-9_]+/
	 * @param array $l10n Associative PHP array containing the translated strings. HTML entities will be converted and the array will be JSON encoded.
	 * @param boolean $frontend Whether the localization is for frontend scripts or backend. Used only in wordpress < 3.3
	 * @return bool Whether the localization was added successfully.
	 */
	public function localize_script_for_requirejs( $handle, $object_name, $l10n, $frontend = false ) {
		global $wp_scripts;
		if ( ! is_a( $wp_scripts, 'WP_Scripts' ) ) {
			if ( ! did_action( 'init' ) )
				_doing_it_wrong( __FUNCTION__, sprintf( __( 'Scripts and styles should not be registered or enqueued until the %1$s, %2$s, or %3$s hooks.' ),
						'<code>wp_enqueue_scripts</code>', '<code>admin_enqueue_scripts</code>', '<code>init</code>' ), '3.3' );
			return false;
		}
		foreach ( (array) $l10n as $key => $value ) {
			if ( !is_scalar($value) )
				continue;
			$l10n[$key] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8');
		}
		$json_data = json_encode( $l10n );
		$prefix = Ai1ec_Requirejs_Controller::REQUIRE_NAMESPACE !== '' ? Ai1ec_Requirejs_Controller::REQUIRE_NAMESPACE . '.' : '';
		$script = "{$prefix}define( '$object_name', $json_data );";
		// Check if the get_data method exist
		if( method_exists( $wp_scripts, 'get_data' ) ) {
			// we are >= 3.3
			$data = $wp_scripts->get_data( $handle, 'data' );
			
			if ( !empty( $data ) )
				$script = "$data\n$script";
			return $wp_scripts->add_data( $handle, 'data', $script );
		} else {
			// we are < 3.3
			$script_to_print = '';
			$script_to_print .= "<script type='text/javascript'>\n";
			$script_to_print .= "/* <![CDATA[ */\n";
			$script_to_print .= $script;
			$script_to_print .= "/* ]]> */\n";
			$script_to_print .= "</script>\n";
			if( $frontend === true ) {
				$this->scripts_in_footer_frontend .= $script_to_print;
			} else {
				$this->scripts_in_footer .= $script_to_print;
			}
			return true;
		}
	}

	/**
	 * 
	 * @throws Ai1ec_Too_Early_For_Wp_Script_Exception
	 * 
	 * @return WP_Scripts
	 */
	private function get_wp_scripts_instance() {
		global $wp_scripts;
		if ( ! is_a( $wp_scripts, 'WP_Scripts' ) ) {
			if ( ! did_action( 'init' ) )
				_doing_it_wrong( __FUNCTION__, sprintf( __( 'Scripts and styles should not be registered or enqueued until the %1$s, %2$s, or %3$s hooks.' ),
						'<code>wp_enqueue_scripts</code>', '<code>admin_enqueue_scripts</code>', '<code>init</code>' ), '3.3' );
			throw new Ai1ec_Too_Early_For_Wp_Script_Exception();
		}
		return $wp_scripts;
	}
}