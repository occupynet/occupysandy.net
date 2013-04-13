<?php

/**
 * Controller class for calendar themes.
 *
 * @author     Timely Network Inc
 * @since      2012.04.05
 *
 * @package    AllInOneEventCalendar
 * @subpackage AllInOneEventCalendar.App.Controller
 */
class Ai1ec_Themes_Controller {
	/**
	 * _instance class variable
	 *
	 * Class instance
	 *
	 * @var null | object
	 **/
	private static $_instance = NULL;
	/**
	 * Cache variable storing whether themes are outdated.
	 *
	 * @var null|boolean
	 */
	protected $_are_themes_outdated = null;

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
	 * Constructor
	 *
	 * Default constructor
	 **/
	private function __construct() { }

	/**
	 * view function
	 *
	 * @return void
	 **/
	public function view() {
		global $ai1ec_view_helper,
		       $ai1ec_settings,
		       $ct;
		// defaults
		$activated = false;
		$deleted   = false;

		// check if action is set
		if( isset( $_GET['action'] ) && ! empty( $_GET['action'] ) ) {
			// action can activate or delete a theme
			switch( $_GET['action'] ) {
				// activate theme
				case 'activate':
					$activated = $this->activate_theme();
					break;
				// delete theme
				case 'delete':
					$deleted = $this->delete_theme();
					break;
			}
		}

		if( $activated ) {
			Ai1ec_Less_Factory::set_active_theme_path( AI1EC_THEMES_ROOT . DIRECTORY_SEPARATOR . $_GET['ai1ec_template'] );
			$this->generate_notice_if_legacy_theme_installed( true );
		}


		$_list_table = new Ai1ec_Themes_List_Table();
		$_list_table->prepare_items();

		$args = array(
			'activated'     => $activated,
			'deleted'       => $deleted,
			'ct'            => $ct,
			'wp_list_table' => $_list_table,
			'page_title'    =>
				__( 'All-in-One Event Calendar: Themes', AI1EC_PLUGIN_NAME ),
		);

		add_thickbox();
		wp_enqueue_script( 'theme-preview' );

		$ai1ec_view_helper->display_admin( 'themes.php', $args );
	}

	/**
	 * view_install function
	 *
	 * @return void
	 **/
	public function view_install() {
		global $ai1ec_view_helper;
		$_list_table = new Ai1ec_Themes_List_Table();
		$_list_table->prepare_items();
		ob_start();
		$_list_table->display();
		$html = ob_get_clean();
		$args = array(
			'html' => $html
		);

		$ai1ec_view_helper->display_admin( 'themes-install.php', $args );
	}

	/**
	 * If it is detected that current theme is in legacy format, generate a
	 * dashboard notice.
	 */
	public function generate_notice_if_legacy_theme_installed(
		$display_now = false
	) {
		$theme = Ai1ec_Meta::get_option(
			'ai1ec_template', AI1EC_DEFAULT_THEME_NAME
		);
		// Save directory separator to more concise variable name.
		$slash = DIRECTORY_SEPARATOR;

		$errors = array();

		// ==========================
		// = Wrong style.css format =
		// ==========================
		// Check if a theme has a style.css file containing something besides a CSS
		// comment.
		try {
			$style = Ai1ec_Less_Factory::create_less_file_instance( '..' . $slash . 'style' );
			$css = file_get_contents( $style->locate_exact_file_to_load_in_theme_folders() );
		}
		catch ( Ai1ec_File_Not_Found $e ) {
			// File not found; no legacy theme code available to detect, so return.
			return;
		}
		$css = $this->remove_comments_and_space( $css );
		if ( $css ) {
			$errors[] =
				sprintf(
					__( 'In your theme folder, <code>%s</code>, the file <code>%sstyle.css</code> should now be used only for metadata information (name, author, etc.).', AI1EC_PLUGIN_NAME ) . ' ' .
					__( 'We detected custom CSS rules in that file. Those should be removed from <code>%sstyle.css</code> and placed in the file <code>%scss%soverride.css</code>.', AI1EC_PLUGIN_NAME ),
					$theme, $slash, $slash, $slash, $slash
				);
		}

		// ==========================
		// = Required files missing =
		// ==========================
		// Check if the theme is missing any of the files that are in Gamma (which
		// includes the minimum files).
		$gamma_files = $this->_get_file_listing(
			AI1EC_THEMES_ROOT . $slash . 'gamma',
			'#' . $slash . '\\.|^' . $slash . 'functions\\.php$#i'
		);
		$theme_files = $this->_get_file_listing(
			$this->active_template_path()
		);
		$diff = array_diff( $gamma_files, $theme_files );
		if ( $diff ) {
			$errors[] =
				sprintf(
					__( 'Your theme folder <code>%s</code> is missing one or more required files: <code>%s</code>. Please copy these files from the skeleton theme folder, <code>gamma</code>, into the same relative location under <code>%s</code>.', AI1EC_PLUGIN_NAME ),
					$theme, implode( '</code>, <code>', $diff ), $theme
				);
		}

		if ( $errors ) {
			// ===============================================================
			// = Additional checks for modified page templates and other CSS =
			// ===============================================================
			// Now that we've established that the theme is outdated, make certain
			// recommendations to the user based on the below checks.

			// If they also have overridden page templates. These *must* be updated.
			$vortex_php = $this->_get_file_listing(
				AI1EC_THEMES_ROOT . '/' . AI1EC_DEFAULT_THEME_NAME,
				'#^' . $slash . '(functions|index)\\.php$#i',
				'#\\.php$#i'
			);
			$theme_php = $this->_get_file_listing(
				$this->active_template_path(),
				'#^' . $slash . '(functions|index)\\.php$#i',
				'#\\.php$#i'
			);
			$php_in_common = array_intersect( $vortex_php, $theme_php );
			if ( $php_in_common ) {
				$errors[] =
					sprintf(
						__( 'We detected one or more custom templates files in your theme folder, <code>%s</code>:', AI1EC_PLUGIN_NAME ) . ' ' .
						'<blockquote><code>' . implode( '</code>, <code>', $php_in_common )
						. '</code></blockquote>' .
						__( 'These templates’ originals have changed significantly since these were copied from <code>%s</code>, and the new versions include numerous enhancements. <strong>Your theme’s outdated templates will likely cause your calendar to malfunction.</strong>', AI1EC_PLUGIN_NAME ) . ' ' .
						__( 'We recommend you back up your templates and remove them from <code>%s</code>. Try using your calendar, and if changes are needed, copy the latest version of the template from <code>%s</code> to <code>%s</code> and then make your revisions.', AI1EC_PLUGIN_NAME ),
						$theme, AI1EC_DEFAULT_THEME_NAME, $theme, AI1EC_DEFAULT_THEME_NAME, $theme
					);
			}
			// Check for overridden CSS. These must also be updated.
			$vortex_css = array(
				$slash . 'less' . $slash . 'style.less',
				$slash . 'less' . $slash . 'calendar.less',
				$slash . 'less' . $slash . 'event.less',
				$slash . 'css' . $slash . 'style.css',
				$slash . 'css' . $slash . 'calendar.css',
				$slash . 'css' . $slash . 'event.css',
			);
			$theme_css = $this->_get_file_listing(
				$this->active_template_path(),
				'#^' . $slash . 'style\\.css$#i',
				'#\\.(css|less)$#i'
			);
			$css_in_common = array_intersect( $vortex_css, $theme_css );
			if ( $css_in_common ) {
				$errors[] =
					sprintf(
						__( 'We detected one or more custom CSS or LESS files in your theme folder, <code>%s</code>, that will override the corresponding original in %s:', AI1EC_PLUGIN_NAME ) .
						'<blockquote><code>' . implode( '</code>, <code>', $css_in_common )
						. '</code></blockquote>' .
						__( 'The originals have changed significantly since these were copied from <code>%s</code>, and the new versions include numerous enhancements. <strong>Your theme’s outdated CSS/LESS files will likely cause your calendar to be displayed incorrectly.</strong>', AI1EC_PLUGIN_NAME ) . ' ' .
						__( 'We recommend you back up the affected CSS/LESS files and remove them from <code>%s</code>. Try using your calendar and if changes are needed, place your custom CSS rules in <code>%scss%soverride.css</code> (or custom LESS rules in <code>%sless%soverride.less</code>).', AI1EC_PLUGIN_NAME ),
						$theme, AI1EC_DEFAULT_THEME_NAME, AI1EC_DEFAULT_THEME_NAME, $theme,
						$slash, $slash, $slash, $slash
					);
			}

			// Display final report.
			$message = Ai1ec_Helper_Factory::create_admin_message_instance(
				'<p>' . __( "You are using a calendar theme that should be updated to the new conventions:", AI1EC_PLUGIN_NAME ) . '</p><ol><li>' .
				implode( '</li><li>', $errors ) .
				'</li></ol><p>' .
				sprintf(
					__( '<strong>Note:</strong> After modifying any of your theme’s CSS or LESS files, you <em>must</em> click <strong>Events</strong> &gt; <strong>Theme Options</strong> &gt; <strong>Save Options</strong> to refresh the compiled CSS used in the front-end.', AI1EC_PLUGIN_NAME ) . '</p><p>' .
					__( 'You can learn more from our <a href="%s" target="_blank">Help Desk article</a>.', AI1EC_PLUGIN_NAME ),
					'http://help.time.ly/customer/portal/articles/803082'
				) . '</p>',
				__( "All-in-One Event Calendar Warning: Calendar theme is in legacy format", AI1EC_PLUGIN_NAME )
			);
			if ( true === $display_now ) {
				$message->render();
			} else {
				$aie1c_admin_notices_helper = Ai1ec_Admin_Notices_Helper::get_instance();
				$aie1c_admin_notices_helper->add_renderable_children( $message );
			}
		}
	}

	/**
	 * Remove all spaces and comments from css
	 *
	 * @param string $css
	 * @return string
	 */
	private function remove_comments_and_space( $css ) {
		// remove comments
		$css = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css );
		// remove tabs, spaces, newlines, etc.
		$css = str_replace( array( "\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css );
		return $css;
	}

	/**
	 * activate_theme function
	 *
	 * @return bool
	 **/
	public function activate_theme() {
		check_admin_referer( 'switch-ai1ec_theme_' . $_GET['ai1ec_template'] );
		$this->switch_theme( $_GET['ai1ec_template'], $_GET['ai1ec_stylesheet'] );
		return true;
	}

	/**
	 * Switch to the theme having template $template and stylesheet $stylesheet.
	 *
	 * @return void
	 */
	public function switch_theme( $template, $stylesheet ) {
		// Delete the saved variables
		delete_option( 'ai1ec_less_variables' );
		// Invalidate the cached data so that the next request recompiles the css
		$css_controller = Ai1ec_Less_Factory::create_css_controller_instance();
		update_option( 'ai1ec_template', $template );
		update_option( 'ai1ec_stylesheet', $stylesheet );
		delete_option( 'ai1ec_current_theme' );
		$css_controller->invalidate_cache( null, false );
	}

	/**
	 * delete_theme function
	 *
	 * @return bool
	 **/
	public function delete_theme() {
		check_admin_referer( 'delete-ai1ec_theme_' . $_GET['ai1ec_template'] );
		if( ! current_user_can( 'delete_themes' ) )
			wp_die( __( 'Cheatin&#8217; uh?' ) );

		$this->remove_theme( $_GET['ai1ec_template'] );
		return true;
	}

	/**
	 * remove_theme function
	 *
	 * @return void
	 **/
	public function remove_theme( $template ) {
		global $wp_filesystem;

		if ( empty($template) )
			return false;

		ob_start();
		if ( empty( $redirect ) )
			$redirect = wp_nonce_url(
				admin_url( AI1EC_THEME_SELECTION_BASE_URL ) .
				"&amp;action=delete&amp;ai1ec_template=$template", 'delete-ai1ec_theme_' . $template
			);
		if ( false === ($credentials = request_filesystem_credentials($redirect)) ) {
			$data = ob_get_contents();
			ob_end_clean();
			if ( ! empty($data) ){
				include_once( ABSPATH . 'wp-admin/admin-header.php');
				echo $data;
				include( ABSPATH . 'wp-admin/admin-footer.php');
				exit;
			}
			return;
		}

		if ( ! WP_Filesystem($credentials) ) {
			request_filesystem_credentials($redirect, '', true); // Failed to connect, Error and request again
			$data = ob_get_contents();
			ob_end_clean();
			if ( ! empty($data) ) {
				include_once( ABSPATH . 'wp-admin/admin-header.php');
				echo $data;
				include( ABSPATH . 'wp-admin/admin-footer.php');
				exit;
			}
			return;
		}

		if ( ! is_object($wp_filesystem) )
			return new WP_Error('fs_unavailable', __('Could not access filesystem.'));

		if ( is_wp_error($wp_filesystem->errors) && $wp_filesystem->errors->get_error_code() )
			return new WP_Error('fs_error', __('Filesystem error.'), $wp_filesystem->errors);

		// Get the base plugin folder
		$themes_dir = $wp_filesystem->wp_content_dir() . AI1EC_THEMES_FOLDER . '/';
		if ( empty($themes_dir) )
			return new WP_Error('fs_no_themes_dir', __('Unable to locate WordPress theme directory.'));

		$themes_dir = trailingslashit( $themes_dir );
		$theme_dir = trailingslashit( $themes_dir . $template );

		$deleted = $wp_filesystem->delete($theme_dir, true);

		if ( ! $deleted )
			return new WP_Error('could_not_remove_theme', sprintf(__('Could not fully remove the theme %s.'), $template) );

		return true;
	}

	/**
	 * preview_theme function
	 *
	 * @return void
	 **/
	public function preview_theme() {
		if( ! ( isset( $_GET['ai1ec_template'] ) && isset( $_GET['preview'] ) ) )
			return;

		if( ! current_user_can( 'switch_themes' ) )
			return;

		// Admin Thickbox requests
		if( isset( $_GET['preview_iframe'] ) )
			show_admin_bar( false );

		$_GET['ai1ec_template'] = preg_replace( '|[^a-z0-9_./-]|i', '', $_GET['ai1ec_template'] );

		if( validate_file( $_GET['ai1ec_template'] ) )
			return;

		add_filter( 'ai1ec_template', array( &$this, '_preview_theme_template_filter' ) );

		if( isset( $_GET['ai1ec_stylesheet'] ) ) {
			$_GET['ai1ec_stylesheet'] = preg_replace( '|[^a-z0-9_./-]|i', '', $_GET['ai1ec_stylesheet'] );
			if( validate_file( $_GET['ai1ec_stylesheet'] ) )
				return;
			add_filter( 'ai1ec_stylesheet', array( &$this, '_preview_theme_stylesheet_filter' ) );
		}

		// Prevent theme mods to current theme being used on theme being previewed
		add_filter( 'pre_option_mods_' . get_current_theme(), '__return_empty_array' );

		ob_start( array( &$this, 'preview_theme_ob_filter' ) );
	}

	/**
	 * preview_theme_ob_filter function
	 *
	 * Callback function for ob_start() to capture all links in the theme.
	 *
	 * @param string $content
	 * @return string
	 */
	function preview_theme_ob_filter( $content ) {
		return preg_replace_callback( "|(<a.*?href=([\"']))(.*?)([\"'].*?>)|",
		                              array( &$this, 'preview_theme_ob_filter_callback' ),
		                              $content );
	}

	/**
	 * preview_theme_ob_filter_callback function
	 *
	 * Manipulates preview theme links in order to control and maintain location.
	 *
	 * Callback function for preg_replace_callback() to accept and filter matches.
	 *
	 * @param array $matches
	 * @return string
	 */
	function preview_theme_ob_filter_callback( $matches ) {
		if( strpos( $matches[4], 'onclick' ) !== false )
			$matches[4] = preg_replace( '#onclick=([\'"]).*?(?<!\\\)\\1#i', '', $matches[4] );

		if( ( false !== strpos( $matches[3], '/wp-admin/' ) ) ||
		    ( false !== strpos( $matches[3], '://' ) && 0 !== strpos( $matches[3], home_url() ) ) ||
		    ( false !== strpos( $matches[3], '/feed/' ) ) ||
		    ( false !== strpos( $matches[3], '/trackback/' ) )
		)
			return $matches[1] . "#$matches[2] onclick=$matches[2]return false;" . $matches[4];

		$query_arg = array(
			'preview'          => 1,
			'ai1ec_template'   => $_GET['ai1ec_template'],
			'ai1ec_stylesheet' => @$_GET['ai1ec_stylesheet']
		);

		if( isset( $_GET['preview_iframe'] ) )
			$query_arg['preview_iframe'] = (int) $_GET['preview_iframe'];

		$link = add_query_arg( $query_arg, $matches[3] );

		if( 0 === strpos( $link, 'preview=1' ) )
			$link = "?$link";

		return $matches[1] . esc_attr( $link ) . $matches[4];
	}

	/**
	 * _preview_theme_template_filter function
	 *
	 * Private function to modify the current template when previewing a theme
	 *
	 * @return string
	 */
	public function _preview_theme_template_filter() {
		return isset( $_GET['ai1ec_template'] ) ? $_GET['ai1ec_template'] : '';
	}

	/**
	 * _preview_theme_stylesheet_filter function
	 *
	 * Private function to modify the current stylesheet when previewing a theme
	 *
	 * @return string
	 */
	public function _preview_theme_stylesheet_filter() {
		return isset( $_GET['ai1ec_stylesheet'] ) ? $_GET['ai1ec_stylesheet'] : '';
	}

	/**
   * Returns the root path of ai1ec-themes.
	 *
	 * @return string
	 **/
	public function template_root_path( $template ) {
		return AI1EC_THEMES_ROOT . '/' . $template;
	}

	/**
	 * Returns the root URL of ai1ec-themes.
	 *
	 * @return string
	 **/
	public function template_root_url( $template ) {
		return AI1EC_THEMES_URL . '/' . $template;
	}

	/**
	 * Returns the path to the active calendar theme.
	 *
	 * @return string
	 */
	public function active_template_path() {
		return apply_filters(
			'ai1ec_template_root_path',
			apply_filters(
				'ai1ec_template',
				Ai1ec_Meta::get_option(
					'ai1ec_template',
					AI1EC_DEFAULT_THEME_NAME
				)
			)
		);
	}

	/**
	 * Returns the URL to the active calendar theme.
	 *
	 * @return string
	 */
	public function active_template_url() {
		return apply_filters(
			'ai1ec_template_root_url',
			apply_filters(
				'ai1ec_template',
				Ai1ec_Meta::get_option(
					'ai1ec_template',
					AI1EC_DEFAULT_THEME_NAME
				)
			)
		);
	}

	/**
	 * are_themes_available function
	 *
	 * Checks if core calendar theme folder is present in wp-content.
	 *
	 * @return bool
	 **/
	public function are_themes_available() {
		//  Are calendar themes folder and Vortex theme present under wp-content ?
		if( @is_dir( AI1EC_THEMES_ROOT ) === true && @is_dir( AI1EC_DEFAULT_THEME_PATH ) === true )
			return true;

		return false;
	}

	/**
	 * Get list of modified theme files.
	 *
	 * @return array List of files that differ on destination
	 *
	 * @throws Ai1ec_File_Not_Found If theme files are not available
	 */
	public function list_modified_files() {
		if ( ! $this->are_themes_available() ) {
			throw new Ai1ec_File_Not_Found( 'Themes unavailable' );
		}
		$themes_root       = AI1EC_THEMES_ROOT;
		$plugin_themes_dir = AI1EC_PATH . DIRECTORY_SEPARATOR . AI1EC_THEMES_FOLDER;

		$theme_files       = $this->_sha1_files(
			$themes_root,
			strlen( $themes_root ) + 1
		);
		$plugin_files      = $this->_sha1_files(
			$plugin_themes_dir,
			strlen( $plugin_themes_dir ) + 1
		);
		$unmatched = array_diff_assoc( $plugin_files, $theme_files );

		return array_keys( $unmatched );
	}

	/**
	 * are_themes_outdated method
	 *
	 * Performs multiple checks to find out if theme files need to be updated.
	 *
	 * If theme files do not exist - they need to be updated, thus this method
	 * returns *true*.
	 *
	 * Else:
	 *     -] If theme files version does match shipped version - update version
	 *        number in database, as there is nothing to update, and return false;
	 *     -] If theme files version does NOT match shipped version - they need to
	 *        be updated, thus return *true*.
	 *
	 * Else themes are in sync, and method returns *false*.
	 *
	 * @return bool True, if themes are out of sync
	 */
	public function are_themes_outdated() {
		if ( NULL === $this->_are_themes_outdated ) {
			if ( ! $this->are_themes_available() ) {
				$this->_are_themes_outdated = true;
			}
			else {
				$ai1ec_themes_version =
					Ai1ec_Meta::get_option( 'ai1ec_themes_version', -1 );
				if ( (string) $ai1ec_themes_version !== (string) AI1EC_THEMES_VERSION ) {
					try {
						$modified = $this->list_modified_files();
						if ( 0 === count( $modified ) ) {
							update_option(
								'ai1ec_themes_version',
								AI1EC_THEMES_VERSION
							);
							return false;
						}
					} catch ( Ai1ec_File_Not_Found $exception ) {
						// silently discard - themes are more than *outdated*
					}
					$this->_are_themes_outdated = true;
				}
				else {
					$this->_are_themes_outdated = false;
				}
			}
		}
		return $this->_are_themes_outdated;
	}

	/**
	 * Returns a notice informing admin to update the core theme files if core
	 * theme files are determined to be out of date. Else returns '' (evaluates
	 * to false). If $echo is true, outputs the notice. Else outputs nothing.
	 *
	 * @see  Ai1ec_Themes_Controller::are_themes_outdated()
	 *
	 * @param  boolean $echo Whether to output the message or just return it.
	 * @return boolean       Whether core theme files are out of date.
	 */
	public function frontend_outdated_themes_notice( $echo = true ) {
		$output = '';
		if ( $this->are_themes_outdated() ) {
			$output .= '<p><em>';
			$output .= __( 'The All-in-One Event Calendar core theme files are out of date and the calendar has been temporarily disabled.', AI1EC_PLUGIN_NAME );
			$output .= ' ';
			if ( current_user_can( 'install_themes' ) ) {
				$output .= sprintf(
					__( 'To enable the calendar, please <a href="%s">log into the WordPress dashboard</a> and follow the instructions.', AI1EC_PLUGIN_NAME ),
					esc_attr( admin_url() )
				);
			}
			else {
				$output .= __( 'To enable the calendar, an administrator must log into the WordPress dashboard and follow the instructions.', AI1EC_PLUGIN_NAME );
			}
			$output .= '</em></p>';
		}

		if ( $echo ) {
			echo $output;
		}

		return $output;
	}

	/**
	 * Checks if themes are installed.
	 *
	 * @return void
	 */
	function check_themes() {
		global $wp_filesystem;

		if ( empty( $template ) ) {
			return false;
		}

		ob_start();
		if ( empty( $redirect ) ) {
			$redirect = wp_nonce_url(
				admin_url( AI1EC_THEME_SELECTION_BASE_URL ) .
				"&amp;action=delete&amp;ai1ec_template=$template", 'delete-ai1ec_theme_' . $template
			);
		}
		if ( false === ( $credentials = request_filesystem_credentials( $redirect ) ) ) {
			$data = ob_get_contents();
			ob_end_clean();
			if ( ! empty($data) ){
				include_once( ABSPATH . 'wp-admin/admin-header.php' );
				echo $data;
				include( ABSPATH . 'wp-admin/admin-footer.php' );
				exit;
			}
			return;
		}

		if ( ! WP_Filesystem( $credentials ) ) {
			request_filesystem_credentials( $redirect, '', true ); // Failed to connect, Error and request again
			$data = ob_get_contents();
			ob_end_clean();
			if ( ! empty( $data ) ) {
				include_once( ABSPATH . 'wp-admin/admin-header.php' );
				echo $data;
				include( ABSPATH . 'wp-admin/admin-footer.php' );
				exit;
			}
			return;
		}

		if ( ! is_object( $wp_filesystem ) ) {
			return new WP_Error( 'fs_unavailable', __( 'Could not access filesystem.' ) );
		}

		if ( is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) {
			return new WP_Error( 'fs_error', __( 'Filesystem error.' ), $wp_filesystem->errors);
		}

		// Get the base plugin folder
		$themes_dir = $wp_filesystem->wp_content_dir() . AI1EC_THEMES_FOLDER . '/';
		if ( empty( $themes_dir ) ) {
			return new WP_Error( 'fs_no_themes_dir', __( 'Unable to locate WordPress theme directory.' ) );
		}

		$themes_dir = trailingslashit( $themes_dir );
		$theme_dir = trailingslashit( $themes_dir . $template );

		$deleted = $wp_filesystem->delete( $theme_dir, true );

		if ( ! $deleted ) {
			return new WP_Error( 'could_not_remove_theme', sprintf( __( 'Could not fully remove the theme %s.' ), $template ) );
		}

		return true;
	}

	/**
	 * Register Install Calendar Themes page in wp-admin.
	 */
	function register_theme_installer() {
		global $ai1ec_settings;

		// Add menu item for theme install page, but remove it using
		// remove_submenu_page to keep the page callback and hide it from the menu.
		add_submenu_page(
			'themes.php',
			__( 'Install Calendar Themes', AI1EC_PLUGIN_NAME ),
			__( 'Install Calendar Themes', AI1EC_PLUGIN_NAME ),
			'install_themes',
			AI1EC_PLUGIN_NAME . '-install-themes',
			array( &$this, 'install_themes' )
		);
		remove_submenu_page(
			'themes.php',
			AI1EC_PLUGIN_NAME . '-install-themes'
		);
	}

	/**
	 * install_themes function
	 *
	 * @return void
	 **/
	function install_themes() {

		// WP_Filesystem figures it out by itself, but the filesystem method may be overriden here
		$method = '';
		$url = wp_nonce_url( AI1EC_INSTALL_THEMES_BASE_URL, AI1EC_PLUGIN_NAME . '-theme-installer' );
		if( false === ( $creds = request_filesystem_credentials( $url, $method, false, false ) ) ) {
			// if we get here, then we don't have credentials yet,
			// but have just produced a form for the user to fill in,
			// so stop processing for now
			return false; // stop the normal page form from displaying
		}

		// now we have some credentials, try to get the wp_filesystem running
		if( ! WP_Filesystem( $creds ) ) {
			// our credentials were no good, ask the user for them again
			request_filesystem_credentials( $url, $method, true, false );
			return false;
		}

		?>
		<div class="wrap">
			<?php
			screen_icon();
			?>
			<h2><?php _e( 'Install Calendar Themes', AI1EC_PLUGIN_NAME ) ?></h2>
		<?php

		global $wp_filesystem;

		$themes_root = $wp_filesystem->wp_content_dir() . AI1EC_THEMES_FOLDER;
		if ( ! is_dir( $themes_root ) ) {
			$result = $wp_filesystem->mkdir( $themes_root );

			if ( $result === false ) {
				?>
				<div id="message" class="error">
					<h3>
						<?php _e( 'Errors occurred while we tried to install your core Calendar Themes', AI1EC_PLUGIN_NAME ) ?>.
					</h3>
					<p>
						<?php printf(
							__( 'Unable to create the Calendar Themes folder: <code>%s</code>', AI1EC_PLUGIN_NAME ),
							AI1EC_THEMES_ROOT
						); ?>
					</p>
					<p>
						<?php printf(
							__( 'Try to create this folder manually, then try again.', AI1EC_PLUGIN_NAME ),
							AI1EC_THEMES_ROOT
						); ?>
					</p>
					<p>
						<a class="button" href="<?php echo AI1EC_INSTALL_THEMES_BASE_URL; ?>">
							<?php _e( 'Try again »', AI1EC_PLUGIN_NAME ); ?>
						</a>
					</p>
				</div>
				<?php
				return false;
			}
		}

		$plugin_themes_dir = AI1EC_PATH . DIRECTORY_SEPARATOR .
			AI1EC_THEMES_FOLDER;
		$result = copy_dir( $plugin_themes_dir, $themes_root );

		if ( is_wp_error( $result ) ) {
			?>
			<div id="message" class="error">
				<h3>
					<?php _e( 'Errors occurred while we tried to install your core Calendar Themes', AI1EC_PLUGIN_NAME ) ?>
				</h3>
				<p>
					<?php _e( 'The following error occurred while copying files:', AI1EC_PLUGIN_NAME ); ?>
					<em><?php echo $result->get_error_message(); ?></em>
				</p>
				<p>
					<?php printf(
						__( 'Try changing permissions on the directory <code>%s</code> so that WordPress can write to this directory, then try again.', AI1EC_PLUGIN_NAME ),
						$themes_root
					); ?>
				</p>
			</div>
			<p>
				<a class="button" href="<?php echo AI1EC_INSTALL_THEMES_BASE_URL; ?>">
					<?php _e( 'Try again »', AI1EC_PLUGIN_NAME ); ?>
				</a>
			</p>
			<?php
		}
		else {
			update_option( 'ai1ec_themes_version', AI1EC_THEMES_VERSION );
			?>
			<div id="message" class="updated"><h3><?php _e( 'Calendar themes were installed successfully', AI1EC_PLUGIN_NAME ) ?>.</h3></div>
			<p>
				<a class="button" href="<?php echo AI1EC_SETTINGS_BASE_URL; ?>">
					<?php _e( 'All-in-One Event Calendar Settings »', AI1EC_PLUGIN_NAME ); ?>
				</a>
			</p>
			<?php
		}
		?>
		</div>
		<?php
	}

	/**
	 * Register Update Calendar Themes page in wp-admin.
	 */
	function register_theme_updater() {
		global $ai1ec_settings;

		// Add menu item for theme update page, but without the actual menu item
		// by removing it again right away.
		add_submenu_page(
			'index.php',
			__( 'Update Calendar Themes', AI1EC_PLUGIN_NAME ),
			__( 'Update Calendar Themes', AI1EC_PLUGIN_NAME ),
			'install_themes',
			AI1EC_PLUGIN_NAME . '-update-themes',
			array( &$this, 'update_core_themes' )
		);

		remove_submenu_page(
			'index.php',
			AI1EC_PLUGIN_NAME . '-update-themes'
 		);
	}

	/**
	 * Called by the Update Calendar Themes page. Removes the core theme files
	 * under wp-content/themes-ai1ec and replaces them with fresh versions.
	 */
	function update_core_themes() {
		global $ai1ec_view_helper;

		$src_dir = trailingslashit( AI1EC_PATH . DIRECTORY_SEPARATOR . AI1EC_THEMES_FOLDER );
		$dest_dir = trailingslashit( AI1EC_THEMES_ROOT );

		// List of core themes.
		$folders = array(
			'gamma',
			'plana',
			'umbra',
			'vortex',
		);

		// Array to hold error notifications to the user while updating the themes.
		$delete_errors = array();
		$copy_errors = array();

		// WP_Filesystem figures it out by itself, but the filesystem method may be
		// overriden here.
		$method = '';
		$url = wp_nonce_url(
			AI1EC_UPDATE_THEMES_BASE_URL,
			AI1EC_PLUGIN_NAME . '-theme-updater'
		);
		$creds = request_filesystem_credentials( $url, $method, false, false );
		if ( false === $creds ) {
			// If we get here, then we don't have credentials yet,
			// but have just produced a form for the user to fill in,
			// so stop processing for now.
			return false; // Stop the normal page form from displaying.
		}

		// Now we have some credentials, try to get the wp_filesystem running.
		if ( ! WP_Filesystem( $creds ) ) {
			// Our credentials were no good, ask the user for them again.
			request_filesystem_credentials( $url, $method, true, false );
			return false;
		}

		global $wp_filesystem;

		// 1. Remove old folders.
		foreach ( $folders as $folder ) {
			$folder = $dest_dir . $folder;
			// Check if folder exists.
			if ( $wp_filesystem->is_dir( $folder ) ) {
				// Try to delete it recusively.
				if ( false === $wp_filesystem->delete( $folder, true ) ) {
					// If delete failed, chmod folder recursively to 0644 and try again.
					$wp_filesystem->chmod( $folder, 0644, $recursive );
					if ( false === $wp_filesystem->delete( $folder, true ) ) {
						// We were not able to remove the folder; notify the user.
						$delete_errors[] = $folder;
					}
				}
			}
		}

		// 2. Copy fresh versions of folders.
		foreach ( $folders as $folder ) {
			$src_folder = $src_dir . $folder;
			$dest_folder = $dest_dir . $folder;
			// Try to copy the folder.
			$result = copy_dir( $src_dir, $dest_dir );
			if ( is_wp_error( $result ) ) {
				// We were not able to copy the folder; notify the user.
				$copy_errors[] = $src_folder;
			}
		}

		$errors = array();

		if ( $delete_errors ) {
			$error = '<div class="error"><p><strong>';
			$error .= __( 'There was an error while removing outdated core themes from your themes folder.', AI1EC_PLUGIN_NAME );
			$error .= '</strong> ';
			$error .= __( 'Please FTP to your web server and manually delete:', AI1EC_PLUGIN_NAME );
			$error .= '</p><blockquote><div><code>';
			$error .= implode( '</code></div><div><code>', $delete_errors );
			$error .= '</code></div></blockquote></div>';
			$errors[] = $error;
		}

		if ( $copy_errors ) {
			$error = '<div class="error"><p><strong>';
			$error .= __( 'There was an error while copying core themes from the plugin into the themes folder.', AI1EC_PLUGIN_NAME );
			$error .= '</strong> ';
			$error .= __( 'Please FTP to your web server and manually copy the folders:', AI1EC_PLUGIN_NAME );
			$error .= '</p><blockquote><div><code>';
			$error .= implode( '</code></div><div><code>', $copy_errors );
			$error .= '</code></div></blockquote>';
			$error .= '<p>' . __( 'to', AI1EC_PLUGIN_NAME ) . ' <code>' . $dest_dir .
				'</code></p></div>';
			$errors[] = $error;
		}

		// We have updated the files, now let's update LESS variables.
		$lessphp_controller = Ai1ec_Less_Factory::create_lessphp_controller();
		$lessphp_controller->update_less_variables_on_theme_update();
		// After the update we invalidate the cache and recompile
		$css_controller = Ai1ec_Less_Factory::create_css_controller_instance();
		if ( false === $css_controller->invalidate_cache( null, true ) ) {
			$errors[] = '<div class="error"><p>' .
				__( '<strong>An error occurred while compiling the theme’s CSS after updating files.</strong> Please visit <strong>Events</strong> &gt; <strong>Theme Options</strong> and click <strong>Save Options</strong> for more detail about the error.', AI1EC_PLUGIN_NAME ) .
				'</p></div>';
		}

		// Unsuccessful core theme file update.
		if ( $errors ) {
			array_unshift(
				$errors,
				__( '<div id="message" class="error"><h3>Errors occurred while we tried to update your core Calendar Themes</h3><p><strong>Please follow any instructions listed below or your calendar may malfunction:</strong></p></div>', AI1EC_PLUGIN_NAME )
			);
		}
		// Successful core theme file update.
		else {
			// Update theme version
			update_option( 'ai1ec_themes_version', AI1EC_THEMES_VERSION );

			$msg = '<div id="message" class="updated"><h3>';
			$msg .= __( 'Your core Calendar Themes were updated successfully', AI1EC_PLUGIN_NAME );
			$msg .= '</h3><p>';
			$msg .= __( 'Be sure to <strong>reload your browser</strong> when viewing your site to make sure the most current scripts are used.', AI1EC_PLUGIN_NAME );
			$msg .= '</p></div>';
		}

		$args = array(
			'msg' => $msg,
			'errors' => $errors,
		);

		$ai1ec_view_helper->display_admin( 'themes-updated.php', $args );
	}

  /**
   * Called immediately after WP theme's functions.php is loaded. Load our own
   * theme's functions.php at this time, and the default theme's functions.php.
   */
  function setup_theme() {
    $functions_files = array(
      $this->active_template_path() . DIRECTORY_SEPARATOR . 'functions.php',
      AI1EC_DEFAULT_THEME_PATH . DIRECTORY_SEPARATOR . 'functions.php',
    );

    $functions_files = array_unique( $functions_files );

    foreach( $functions_files as $file ) {
      if ( file_exists( $file ) ) {
        include( $file );
      }
    }
  }

	/**
	 * _sha1_files method
	 *
	 * Calculate SHA1 checksums on files in given directories.
	 *
	 * @param string $path           Path to directory to be examined
	 * @param int    $discard_length Number of characters to strip from resulting file key
	 *
	 * @return array Map of files and their checksums
	 *
	 * @throws Ai1ec_File_Not_Found If given path is not found/not readable
	 */
	protected function _sha1_files( $path, $discard_length ) {
		if ( ! file_exists( $path ) || ! ( $directory = opendir( $path ) ) ) {
			throw new Ai1ec_File_Not_Found(
				sprintf( __( 'Folder %s not found', AI1EC_PLUGIN_NAME ), $path )
			);
		}
		$matched = array();
		while ( false !== ( $read = readdir( $directory ) ) ) {
			if ( '.' === $read || '..' === $read || '.DS_Store' === $read ) {
				continue;
			}
			$new_path = $path . DIRECTORY_SEPARATOR . $read;
			if ( is_dir( $new_path ) ) {
				$matched = array_merge(
					$matched,
					$this->_sha1_files( $new_path, $discard_length )
				);
			} else {
				$matched[substr( $new_path, $discard_length )] = sha1_file( $new_path );
			}
		}
		closedir( $directory );
		return $matched;
	}

	/**
	 * Return a list of files in the given directory and its subdirectories, with
	 * filenames relative to the $relative_root value (which defaults to the
	 * path searched if not supplied). Optionally restrict listing using exclude
	 * and include regex patterns.
	 *
	 * $path can be an absolute path or a path relative to AI1EC_THEMES_ROOT.
	 *
	 * @param  string $path          Path to directory to be examined
	 * @param  string $exclude       Do not return files matching this regex
	 * @param  string $include       Only return files matching this regex
	 * @param  string $rel_root      Return files relative to this directory
	 *
	 * @return array                 List of files
	 *
	 * @throws Ai1ec_File_Not_Found If given path is not found/not readable
	 */
	protected function _get_file_listing(
		$path, $exclude = null, $include = null, $rel_root = null
	) {
		if ( ! file_exists( $path ) || ! ( $directory = opendir( $path ) ) ) {
			// Assume $path is relative to AI1EC_THEMES_ROOT.
			$path = AI1EC_THEMES_ROOT . DIRECTORY_SEPARATOR . $path;
			if ( ! file_exists( $path ) || ! ( $directory = opendir( $path ) ) ) {
				throw new Ai1ec_File_Not_Found(
					sprintf( __( 'Folder %s not found', AI1EC_PLUGIN_NAME ), $path )
				);
			}
		}
		if ( null === $rel_root ) {
			$rel_root = $path;
		}

		$entries = array();
		while ( false !== ( $entry = readdir( $directory ) ) ) {
			if ( '.' === $entry || '..' === $entry ) {
				continue;
			}
			$new_path = $path . DIRECTORY_SEPARATOR . $entry;
			if ( is_dir( $new_path ) ) {
				$entries = array_merge(
					$entries,
					$this->_get_file_listing( $new_path, $exclude, $include, $rel_root )
				);
			} else {
				// Get new path relative to $rel_root (inclusion/exclusion patterns are
				// matched based on this form).
				$new_path = substr( $new_path, strlen( $rel_root ) );
				if ( $exclude && preg_match( $exclude, $new_path ) ) {
					continue;
				}
				if ( $include && ! preg_match( $include, $new_path ) ) {
					continue;
				}
				$entries[] = $new_path;
			}
		}
		closedir( $directory );
		return $entries;
	}

}
// END class
