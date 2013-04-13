<?php
//
//  class-ai1ec-view-helper.php
//  all-in-one-event-calendar
//
//  Created by The Seed Studio on 2011-07-13.
//

/**
 * Ai1ec_View_Helper class
 *
 * @package Helpers
 * @author time.ly
 **/
class Ai1ec_View_Helper {
	/**
	 * _instance class variable
	 *
	 * Class instance
	 *
	 * @var null | object
	 **/
	private static $_instance = NULL;

	/**
	 * Constructor
	 *
	 * Default constructor
	 **/
	private function __construct() { }

	/**
	 * get_instance function
	 *
	 * Return singleton instance
	 *
	 * @return object
	 **/
	static function get_instance() {
		if( self::$_instance === NULL ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
   * Enqueue a script from the theme resources directory.
   *
   * @param string $name Unique identifer for the script
   * @param string $file Filename of the script
   * @param array $deps Dependencies of the script
   * @param bool $in_footer Whether to add the script to the footer of the page
   *
	 * @return void
	 */
	function theme_enqueue_script( $name, $file, $deps = array(), $in_footer = FALSE  ) {


		if( ! $file || empty( $file ) ) {
			throw new Ai1ec_File_Not_Provided( "You need to specify a script file." );
		}

		try {
			$file_path = $this->get_path_of_js_file_to_load( $file );
			// Append core themes version to version string to make sure recently
			// updated files are used.
			wp_enqueue_script(
					$name,
					$file_path,
					$deps,
					AI1EC_VERSION . '-' .
						Ai1ec_Meta::get_option( 'ai1ec_themes_version', 1 ),
					$in_footer
			);
		} catch ( Ai1ec_File_Not_Found  $e ) {
			throw $e;
		}
	}

	/**
	 * admin_enqueue_style function
	 *
	 * @return void
	 **/
	function admin_enqueue_style( $name, $file, $deps = array() ) {
		if( ! $file || empty( $file ) ) {
			throw new Ai1ec_File_Not_Provided( "You need to specify a style file." );
		}

		$_file = AI1EC_ADMIN_THEME_CSS_PATH . '/' . $file;

		if( ! file_exists( $_file ) ) {
			throw new Ai1ec_File_Not_Found( "The specified file " . $file . " doesn't exist." );
		} else {
			$file = AI1EC_ADMIN_THEME_CSS_URL . '/' . $file;
			wp_enqueue_style( $name, $file, $deps, AI1EC_VERSION );
		}
	}

	/**
	 * theme_enqueue_style function
	 *
	 * @return void
	 **/
	function theme_enqueue_style( $name, $file, $deps = array() ) {
    global $ai1ec_themes_controller;

		if( ! $file || empty( $file ) ) {
			throw new Ai1ec_File_Not_Provided( "You need to specify a style file." );
		}

		// template path
		$active_template_path = $ai1ec_themes_controller->active_template_path();
		// template url
		$active_template_url = $ai1ec_themes_controller->active_template_url();

		// look for the file in the active theme
		$themes_root = array(
			(object) array(
				'path' => $active_template_path . '/' . AI1EC_CSS_FOLDER,
				'url'  => $active_template_url . '/' . AI1EC_CSS_FOLDER
			),
			(object) array(
				'path' => $active_template_path,
				'url'  => $active_template_url
			),
			(object) array(
				'path' => AI1EC_DEFAULT_THEME_PATH . '/' . AI1EC_CSS_FOLDER,
				'url'  => AI1EC_DEFAULT_THEME_URL . '/' . AI1EC_CSS_FOLDER
			),
			(object) array(
				'path' => AI1EC_DEFAULT_THEME_PATH,
				'url'  => AI1EC_DEFAULT_THEME_URL
			),
		);

		$file_found = false;

		// look for the file in each theme
		foreach( $themes_root as $theme_root ) {
			// $_file is a local var to hold the value of
			// the file we are looking for
			$_file = $theme_root->path . '/' . $file;
			if( file_exists( $_file ) ) {
				// file is found
				$file_found = true;
				// assign the found file
				$file       = $theme_root->url . '/' . $file;
				// exit the loop;
				break;
			}
		}

		if( $file_found === false ) {
			throw new Ai1ec_File_Not_Found( "The specified file '" . $file . "' doesn't exist." );
		}
		else {
			// Append core themes version to version string to make sure recently
			// updated files are used.
			wp_enqueue_style(
				$name,
				$file,
				$deps,
				AI1EC_VERSION . '-' .
					Ai1ec_Meta::get_option( 'ai1ec_themes_version', 1 )
			);
		}
	}

	/**
	 * display_admin function
	 *
	 * Display the view specified by file $file and passed arguments $args.
	 *
	 * @param string $file
	 * @param array $args
	 *
	 * @return void
	 **/
	function display_admin( $file = false, $args = array() ) {
		if( ! $file || empty( $file ) ) {
			throw new Ai1ec_File_Not_Provided( "You need to specify a view file." );
		}

		$file = AI1EC_ADMIN_THEME_PATH . '/' . $file;

		if( ! file_exists( $file ) ) {
			throw new Ai1ec_File_Not_Found( "The specified view file doesn't exist." );
		} else {
			extract( $args );
			require( $file );
		}
	}

	/**
	 * display_theme function
	 *
	 * Display the view specified by file $file and passed arguments $args.
	 *
	 * @param string $file
	 * @param array $args
	 *
	 * @return void
	 **/
	function display_theme( $file = false, $args = array() ) {
    global $ai1ec_themes_controller;

		if( ! $file || empty( $file ) ) {
			throw new Ai1ec_File_Not_Provided( "You need to specify a view file." );
		}

		// look for the file in the selected theme
		$themes_root = array(
			$ai1ec_themes_controller->active_template_path(),
			AI1EC_DEFAULT_THEME_PATH
		);

		// remove duplicates
		$themes_root = array_unique( $themes_root );

		$file_found = false;

		// look for the file in each theme
		foreach( $themes_root as $theme_root ) {
			// $_file is a local var to hold the value of
			// the file we are looking for
			$_file = $theme_root . '/' . $file;
			if( file_exists( $_file ) ) {
				// file is found
				$file_found = true;
				// assign the found file
				$file       = $_file;
				// exit the loop;
				break;
			}
		}

		if( $file_found === false ) {
			throw new Ai1ec_File_Not_Found( "The specified view file '" . $file . "' doesn't exist." );
		} else {
			extract( $args );
			require( $file );
		}
	}

	/**
	 * display_admin_css function
	 *
	 * Renders the given stylesheet inline. If stylesheet has already been
	 * displayed once before with the same set of $args, does not display
	 * it again.
	 *
	 * @param string $file
	 * @param array $args
	 *
	 * @return void
	 **/
	function display_admin_css( $file = false, $args = array() ) {
		static $displayed = array();

		if( ! $file || empty( $file ) ) {
			throw new Ai1ec_File_Not_Provided( 'You need to specify a css file.' );
		}

		$file = AI1EC_ADMIN_THEME_CSS_PATH . '/' . $file;

		if( isset( $displayed[$file] ) && $displayed[$file] === $args )	// Skip if already displayed
			return;

		if( ! file_exists( $file ) ) {
			throw new Ai1ec_File_Not_Found( "The specified css file doesn't exist." );
		} else {
			$displayed[$file] = $args;	// Flag that we've displayed this file with these args

			extract( $args );
			echo '<style type="text/css">';
			require( $file );
			echo '</style>';
		}
	}

	/**
	 * display_theme_css function
	 *
	 * Renders the given stylesheet inline. If stylesheet has already been
	 * displayed once before with the same set of $args, does not display
	 * it again.
	 *
	 * @param string $file
	 * @param array $args
	 *
	 * @return void
	 **/
	function display_theme_css( $file = false, $args = array() ) {
    global $ai1ec_themes_controller;
		static $displayed = array();

		if( ! $file || empty( $file ) ) {
			throw new Ai1ec_File_Not_Provided( 'You need to specify a CSS file.' );
		}

		// look for the file in the selected theme
		$themes_root = array(
			$ai1ec_themes_controller->active_template_path() . '/' . AI1EC_THEME_CSS_FOLDER,
			AI1EC_DEFAULT_THEME_PATH . '/' . AI1EC_THEME_CSS_FOLDER
		);

		// remove duplicates
		$themes_root = array_unique( $themes_root );

		$file_found = false;

		// look for the file in each theme
		foreach( $themes_root as $theme_root ) {
			// $_file is a local var to hold the value of
			// the file we are looking for
			$_file = $theme_root . '/' . $file;
			if( file_exists( $_file ) ) {
				// file is found
				$file_found = true;
				// assign the found file
				$file       = $_file;
				// exit the loop;
				break;
			}
		}

		if( isset( $displayed[$file] ) && $displayed[$file] === $args )	// Skip if already displayed
			return;

		if( ! file_exists( $file ) ) {
			throw new Ai1ec_File_Not_Found( "The specified CSS file doesn't exist." );
		} else {
			$displayed[$file] = $args;	// Flag that we've displayed this file with these args

			extract( $args );
			echo '<style type="text/css">';
			require( $file );
			echo '</style>';
		}
	}

	/**
	 * display_admin_js function
	 *
	 * Renders the given script inline. If script has already been displayed
	 * once before with the same set of $args, does not display it again.
	 *
	 * @param string $file
	 * @param array $args
	 *
	 * @return void
	 **/
	function display_admin_js( $file = false, $args = array() ) {
		static $displayed = array();

		if( ! $file || empty( $file ) ) {
			throw new Ai1ec_File_Not_Provided( "You need to specify a js file." );
		}

		$file = AI1EC_ADMIN_THEME_JS_PATH . '/' . $file;

		if( $displayed[$file] === $args)	// Skip if already displayed
			return;

		if( ! file_exists( $file ) ) {
			throw new Ai1ec_File_Not_Found( "The specified js file doesn't exist." );
		} else {
			$displayed[$file] = $args;	// Flag that we've displayed this file with these args

			extract( $args );
			echo '<script type="text/javascript" charset="utf-8">';
			echo '/* <![CDATA[ */';
			require( $file );
			echo '/* ]]> */';
			echo '</script>';
		}
	}
	/**
	 * display_theme_js function
	 *
	 * Renders the given script inline. If script has already been displayed
	 * once before with the same set of $args, does not display it again.
	 *
	 * @param string $file
	 * @param array $args
	 *
	 * @return void
	 **/
	function display_theme_js( $file = false, $args = array() ) {
    global $ai1ec_themes_controller;
		static $displayed = array();

		if( ! $file || empty( $file ) ) {
			throw new Ai1ec_File_Not_Provided( "You need to specify a JS file." );
		}

    // look for the file in the selected theme
    $themes_root = array(
      $ai1ec_themes_controller->active_template_path() . '/' . AI1EC_THEME_JS_FOLDER,
      AI1EC_DEFAULT_THEME_PATH . '/' . AI1EC_THEME_JS_FOLDER
    );

    // remove duplicates
    $themes_root = array_unique( $themes_root );

    $file_found = false;

    // look for the file in each theme
    foreach( $themes_root as $theme_root ) {
      // $_file is a local var to hold the value of
      // the file we are looking for
      $_file = $theme_root . '/' . $file;
      if( file_exists( $_file ) ) {
        // file is found
        $file_found = true;
        // assign the found file
        $file       = $_file;
        // exit the loop;
        break;
      }
    }

		if( $displayed[$file] === $args)	// Skip if already displayed
			return;

		if( ! file_exists( $file ) ) {
			throw new Ai1ec_File_Not_Found( "The specified JS file doesn't exist." );
		} else {
			$displayed[$file] = $args;	// Flag that we've displayed this file with these args

			extract( $args );
			echo '<script type="text/javascript" charset="utf-8">';
			echo '/* <![CDATA[ */';
			require( $file );
			echo '/* ]]> */';
			echo '</script>';
		}
	}

	/**
	 * get_admin_view function
	 *
	 * Return the output of a view as a string rather than output to response.
	 *
	 * @param string $file
	 * @param array $args
	 *
	 * @return void
	 **/
	function get_admin_view( $file = false, $args = array() ) {
		ob_start();
		$this->display_admin( $file, $args );
		return ob_get_clean();
	}

	/**
	 * get_theme_view function
	 *
	 * Return the output of a view in the theme as a string rather than output to response.
	 *
	 * @param string $file
	 * @param array $args
	 *
	 * @return void
	 **/
	function get_theme_view( $file = false, $args = array() ) {
		ob_start();
		$this->display_theme( $file, $args );
		return ob_get_clean();
	}

	/**
	 * get_admin_img_url function
	 *
	 * @return string
	 **/
	public function get_admin_img_url( $file ) {
		if( ! $file || empty( $file ) ) {
			throw new Ai1ec_File_Not_Provided( "You need to specify an image file." );
		}

		$_file = AI1EC_ADMIN_THEME_IMG_PATH . '/' . $file;

		if( ! file_exists( $_file ) ) {
			throw new Ai1ec_File_Not_Found( "The specified file " . $_file . " doesn't exist." );
		} else {
			$file = AI1EC_ADMIN_THEME_IMG_URL . '/' . $file;
			return $file;
		}
	}

	/**
	 * get_theme_img_url function
	 *
	 * @return string
	 **/
	public function get_theme_img_url( $file ) {
    global $ai1ec_themes_controller;

		if( ! $file || empty( $file ) ) {
			throw new Ai1ec_File_Not_Provided( "You need to specify an image file." );
		}

    // template path
    $active_template_path = $ai1ec_themes_controller->active_template_path();
    // template url
    $active_template_url = $ai1ec_themes_controller->active_template_url();

    // look for the file in the active theme
    $themes_root = array(
      (object) array(
        'path' => $active_template_path . '/' . AI1EC_IMG_FOLDER,
        'url'  => $active_template_url . '/' . AI1EC_IMG_FOLDER
      ),
      (object) array(
        'path' => AI1EC_DEFAULT_THEME_PATH . '/' . AI1EC_IMG_FOLDER,
        'url'  => AI1EC_DEFAULT_THEME_URL . '/' . AI1EC_IMG_FOLDER
      ),
    );

		$file_found = false;

		// look for the file in each theme
		foreach( $themes_root as $theme_root ) {
			// $_file is a local var to hold the value of
			// the file we are looking for
			$_file = $theme_root->path . '/' . $file;
			if( file_exists( $_file ) ) {
				// file is found
				$file_found = true;
				// assign the found file
				$file       = $theme_root->url . '/' . $file;
				// exit the loop;
				break;
			}
		}

		if( $file_found === false ) {
			throw new Ai1ec_File_Not_Found( "The specified file '" . $file . "' doesn't exist." );
		} else {
			return $file;
		}
	}

	/**
	 * Utility for properly outputting JSON data as an AJAX response.
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	function json_response( $data ) {
		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Pragma: no-cache' );
		header( 'Content-Type: application/json; charset=UTF-8' );

		// Output JSON-encoded result and quit
		echo json_encode( ai1ec_utf8( $data ) );
		exit;
	}

	/**
	 * Utility for properly outputting JSONP data as an AJAX response.
	 *
	 * @param string $data
	 *
	 * @param string $callback
	 *
	 * @return void
	 */
	function jsonp_response( $data, $callback ) {
		header( 'Content-Type: application/json; charset=UTF-8' );

		// Output JSONP-encoded result and quit
		echo $callback . '(' . json_encode( ai1ec_utf8( $data ) ) . ')';
		exit;
	}

	/**
	 * Utility for properly outputting XML data as an AJAX response.
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	function xml_response( $data ) {
		header( 'Content-Type: text/xml; charset=UTF-8' );

		// Output JSON-encoded result and quit
		echo Ai1ec_XML_Utility::serialize_to_xml( ai1ec_utf8( $data ) );
		exit;
	}

	/**
	 * url method
	 *
	 * Generate page link given stub (base) and arguments to inject.
	 * Some arguments are treated specially. I.e. 'action', which is
	 * injected using WP add_query_arg method.
	 *
	 * @uses add_query_arg       To inject special query vars
	 * @uses Ai1ec_Router::uri() To generate final URI
	 *
	 * @param string $page Page stub (base) to extend
	 * @param array  $argv Arguments to add to query
	 *
	 * @return string Fully usable URI
	 */
	public function url( $page, array $argv = array() ) {
		global $ai1ec_settings, $ai1ec_router;
		$action = $ai1ec_settings->default_calendar_view;
		extract( $argv, EXTR_IF_EXISTS );
		if ( 0 !== strncmp( $action, 'ai1ec_', 6 ) ) {
			$action = 'ai1ec_' . $action;
		}
		if ( false === strpos( $page, '#' ) ) {
			add_query_arg( compact( 'action' ), $page );
		}
		return $ai1ec_router->uri( $argv, $page );
	}

	/**
	 * disable_autosave method
	 *
	 * Callback to disable autosave script
	 *
	 * @param array $input List of scripts registered
	 *
	 * @return array Modified scripts list
	 */
	public function disable_autosave( array $input ) {
		wp_deregister_script( 'autosave' );
		$autosave_key = array_search( 'autosave', $input );
		if ( false === $autosave_key || ! is_scalar( $autosave_key ) ) {
			unset( $input[$autosave_key] );
		}
		return $input;
	}

	/**
	 * the_title_admin method
	 *
	 * Override title, visible in admin side, to display parent event
	 * title in-line.
	 *
	 * @param string $title	  Title to be displayed
	 * @param int	 $post_id ID of post being displayed
	 *
	 * @return string Modified title
	 */
	public function the_title_admin( $title, $post_id ) {
		global $ai1ec_events_helper;
		remove_filter( 'the_title', 'esc_html' );
		$title			= esc_html( $title );
		$parent_post_id = $ai1ec_events_helper->event_parent( $post_id );
		if ( $parent_post_id ) {
			$parent_post = get_post( $parent_post_id );
			if ( NULL !== $parent_post ) {
				$title .= '</a> <span class="ai1ec-instance-parent">(' .
					__( 'instance of', AI1EC_PLUGIN_NAME ) .
					' <a href="';
				$title .= get_edit_post_link(
					$parent_post_id,
					'display'
				);
				$title .= '">' . $parent_post->post_title;
				$title .= '</a>)</span><a href="#noclick">';
			}
		}
		return $title;
	}


}
// END class
