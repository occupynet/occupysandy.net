<?php

/**
 * Class that handles less related functions.
 *
 * @author     Time.ly Network Inc.
 * @since      2.0
 *
 * @package    AI1EC
 * @subpackage AI1EC.Less
 */
class Ai1ec_Less_Lessphp extends Ai1ec_Base {

	/**
	 *
	 * @var string
	 */
	const DB_KEY_FOR_LESS_VARIABLES = "ai1ec_less_variables";

	/**
	 *
	 * @var lessc
	 */
	private $lessc;

	/**
	 *
	 * @var array
	 */
	private $files = array();

	/**
	 *
	 * @var string
	 */
	private $unparsed_variable_file;

	/**
	 *
	 * @var string
	 */
	private $parsed_css;

	/**
	 *
	 * @var string
	 */
	private $default_theme_url;

	/**
	 *
	 * @var Ai1ec_File_Less
	 */
	private $variable_file;

	/**
	 * Variables used for compilation.
	 *
	 * @var array
	 */
	private $variables;

	public function __construct(
		Ai1ec_Registry_Object $registry,
		$default_theme_url = AI1EC_DEFAULT_THEME_URL
	) {
		parent::__construct( $registry );
		$this->lessc = $this->_registry->get( 'lessc' );
		$this->lessc->setFormatter( 'compressed' );
		$this->default_theme_url = $this->sanitize_default_theme_url( $default_theme_url );
		$this->parsed_css        = '';
		$this->variables         = array();
		$this->files             = array(
			'style.less',
			'event.less',
			'calendar.less',
		);
	}

	/**
	 *
	 * @param Ai1ec_File_Less $file
	 */
	public function set_variable_file( Ai1ec_File_Less $file ) {
		$this->variable_file = $file;
	}

	/**
	 *
	 * @param Ai1ec_File_Less $file
	 */
	public function add_file( $file ) {
		$this->files[] = $file;
	}

	/**
	 * Parse all the less files resolving the dependencies.
	 *
	 * @param array $variables
	 * @param bool  $compile_core If set to true, it forces compilation of core CSS only, suitable for shipping.
	 * @throws Ai1ec_File_Not_Found_Exception|Exception
	 * @throws Exception
	 * @return string
	 */
	public function parse_less_files( array $variables = null, $compile_core = false ) {
		// If no variables are passed, initialize from DB, config file, and
		// extension injections in one call.
		if ( empty( $variables ) ) {
			$variables = $this->get_saved_variables( false );
		}
		// convert the variables to key / value
		$variables   = $this->convert_less_variables_for_parsing( $variables );
		// Inject additional constants from extensions
		$variables   = apply_filters( 'ai1ec_less_constants', $variables );

		// Use this variables for hashmap purposes.
		$this->variables = $variables;

		// Load the static variables defined in the theme's variables.less file.
		$this->load_static_theme_variables();
		$loader      = $this->_registry->get( 'theme.loader' );
		//Allow extensions to add their own LESS files.
		$this->files   = apply_filters( 'ai1ec_less_files', $this->files );
		$this->files[] = 'override.less';

		// Find out the active theme URL.
		$option      = $this->_registry->get( 'model.option' );
		$theme       = $option->get( 'ai1ec_current_theme' );
		$this->lessc->addImportDir(
			$theme['theme_dir'] . DIRECTORY_SEPARATOR . 'less'
		);
		$import_dirs = array();
		foreach ( $this->files as $file ) {
			$file_to_parse = null;
			try {
				// Get the filename following our fallback convention
				$file_to_parse = $loader->get_file( $file );
			} catch ( Ai1ec_Exception $e ) {
				// We let child themes override styles of Vortex.
				// So there is no fallback for override and we can continue.
				if ( $file !== 'override.less' ) {
					throw $e;
				} else {
					// It's an override, skip it.
					continue;
				}
			}
			// We prepend the unparsed variables.less file we got earlier.
			// We do this as we do not import that anymore in the less files.
			$this->unparsed_variable_file .= $file_to_parse->get_content();

			// Set the import directories for the file. Includes current directory of
			// file as well as theme directory in core. This is important for
			// dependencies to be resolved correctly.
			$dir = dirname( $file_to_parse->get_name() );
			if ( ! isset( $import_dirs[$dir] ) ) {
				$import_dirs[$dir] = true;
				$this->lessc->addImportDir( $dir );
			}
		}
		$variables['fontdir'] = '~"' .
			Ai1ec_Http_Response_Helper::remove_protocols(
				$theme['theme_url']
			) . '/font"';
		$variables['fontdir_default'] = '~"' .
			Ai1ec_Http_Response_Helper::remove_protocols(
				$this->default_theme_url
			) . 'font"';
		$variables['imgdir'] = '~"' .
			Ai1ec_Http_Response_Helper::remove_protocols(
				$theme['theme_url']
			) . '/img"';
		$variables['imgdir_default'] = '~"' .
			Ai1ec_Http_Response_Helper::remove_protocols(
				$this->default_theme_url
			) . 'img"';
		if ( true === $compile_core ) {
			$variables['fontdir'] = '~"../font"';
			$variables['fontdir_default'] = '~"../font"';
			$variables['imgdir'] = '~"../img"';
			$variables['imgdir_default'] = '~"../img"';
		}
		try {
			$this->parsed_css = $this->lessc->parse(
				$this->unparsed_variable_file,
				$variables
			);
		} catch ( Exception $e ) {
			throw $e;
		}

		// Replace font placeholders
		$this->parsed_css = preg_replace_callback(
			'/__BASE64_FONT_([a-zA-Z0-9]+)_(\S+)__/m',
			array( $this, 'load_font_base64' ),
			$this->parsed_css
		);

		return $this->parsed_css;
	}

	/**
	 * Check LESS variables are stored in the options table; if not, initialize
	 * with defaults from config file and extensions.
	 */
	public function initialize_less_variables_if_not_set() {
		$variables = $this->_registry->get( 'model.option' )->get(
			self::DB_KEY_FOR_LESS_VARIABLES,
			array()
		);

		if ( empty( $variables ) ) {
			// Initialize variables with defaults from config file and extensions,
			// omitting descriptions.
			$variables = $this->get_saved_variables( false );

			// Save the new/updated variable array back to the database.
			$this->_registry->get( 'model.option' )->set(
				self::DB_KEY_FOR_LESS_VARIABLES,
				$variables
			);
		}
	}

	/**
	 * Invalidates CSS cache if ai1ec_invalidate_css_cache option was flagged.
	 * Deletes flag afterwards.
	 */
	public function invalidate_css_cache_if_requested() {
		$option = $this->_registry->get( 'model.option' );

		if (
			$option->get( 'ai1ec_invalidate_css_cache' ) ||
			Ai1ec_Css_Frontend::PARSE_LESS_FILES_AT_EVERY_REQUEST
		) {
			$css_controller = $this->_registry->get( 'css.frontend' );
			$css_controller->invalidate_cache( null, true );
			$option->delete( 'ai1ec_invalidate_css_cache' );
		}
	}

	/**
	 * After updating core themes, we also need to update the LESS variables with
	 * the new ones as they may have changed. This function assumes that the
	 * user_variables.php file in the active theme and/or parent theme has just
	 * been updated.
	 */
	public function update_less_variables_on_theme_update() {
		// Get old variables from the DB.
		$saved_variables = $this->get_saved_variables( false );
		// Get the new variables from file.
		$new_variables = $this->get_less_variable_data_from_config_file();
		foreach ( $new_variables as $name => $attributes ) {
			// If the variable already exists, keep the old value.
			if ( isset( $saved_variables[$name] ) ) {
				$new_variables[$name]['value'] = $saved_variables[$name]['value'];
			}
		}
		// Save the new variables to the DB.
		$this->_registry->get( 'model.option' )->set(
			self::DB_KEY_FOR_LESS_VARIABLES,
			$new_variables
		);
	}

	/**
	 * Get the theme variables from the theme user_variables.php file; also inject
	 * any other variables provided by extensions.
	 *
	 * @return array
	 */
	public function get_less_variable_data_from_config_file() {
		// Load the file to parse using the theme loader to select the right file.
		$loader = $this->_registry->get( 'theme.loader' );
		$file = $loader->get_file( 'less/user_variables.php', array(), false );

		// This variables are returned by evaluating the PHP file.
		$variables = $file->get_content();
		// Inject extension variables into this array.
		return apply_filters( 'ai1ec_less_variables', $variables );
	}

	/**
	 * Returns compilation specific hashmap.
	 *
	 * @return array Hashmap.
	 */
	public function get_less_hashmap() {
		foreach ( $this->variables as $key => $value ) {
			if ( 'fontdir_' === substr( $key, 0, 8 ) ) {
				unset( $this->variables[$key] );
			}
		}
		$hashmap   = $this->_registry->get(
			'filesystem.misc'
		)->build_current_theme_hashmap();
		$variables = $this->variables;
		ksort( $variables );
		return array(
			'variables' => $variables,
			'files'     => $hashmap,
		);
	}

	/**
	 * Returns whether LESS compilation should be performed or not.
	 *
	 * @param array|null $variables LESS variables.
	 *
	 * @return bool Result.
	 *
	 * @throws Ai1ec_Bootstrap_Exception
	 */
	public function is_compilation_needed( $variables = array() ) {
		if (
			apply_filters( 'ai1ec_always_recompile_less', false ) ||
			(
				defined( 'AI1EC_DEBUG' ) &&
				AI1EC_DEBUG
			)
		) {
			return true;
		}
		if ( null === $variables ) {
			$variables = array();
		}
		/* @var $misc Ai1ec_Filesystem_Misc */
		$misc        = $this->_registry->get( 'filesystem.misc' );
		$cur_hashmap = $misc->get_current_theme_hashmap();
		if ( empty( $variables ) ) {
			$variables = $this->get_saved_variables( false );
		}
		$variables   = $this->convert_less_variables_for_parsing( $variables );
		$variables   = apply_filters( 'ai1ec_less_constants', $variables );
		$variables   = $this->_compilation_check_clear_variables( $variables );
		ksort( $variables );
		if (
			null === $cur_hashmap ||
			$variables !== $cur_hashmap['variables']
		) {
			return true;
		}

		$file_hashmap = $misc->build_current_theme_hashmap();

		return ! $misc->compare_hashmaps( $file_hashmap, $cur_hashmap['files'] );
	}


	/**
	 * Gets the saved variables from the database, and make sure all variables
	 * are set correctly as required by config file and any extensions. Also
	 * adds translations of variable descriptions as required at runtime.
	 *
	 * @param $with_description bool Whether to return variables with translated descriptions
	 * @return array
	 */
	public function get_saved_variables( $with_description = true ) {
		// We don't store description in options table, so find it in current config
		// file. Variables from extensions are already injected during this call.
		$variables_from_config = $this->get_less_variable_data_from_config_file();

		// Fetch current variable settings from options table.
		$variables = $this->_registry->get( 'model.option' )->get(
			self::DB_KEY_FOR_LESS_VARIABLES,
			array()
		);

		// Generate default variable array from the config file, and union these
		// with any saved variables to make sure all required variables are set.
		$variables += $variables_from_config;

		// Add the description at runtime so that it can be translated.
		foreach ( $variables as $name => $attrs ) {
			// Also filter out any legacy variables that are no longer found in
			// current config file (exceptions thrown if this is not handled here).
			if ( ! isset( $variables_from_config[$name] ) ) {
				unset( $variables[$name] );
			}
			else {
				// If description is requested and is available in config file, use it.
				if (
					$with_description &&
					isset( $variables_from_config[$name]['description'] )
				) {
					$variables[$name]['description'] =
						$variables_from_config[$name]['description'];
				} else {
					unset( $variables[$name]['description'] );
				}
			}
		}

		return $variables;
	}

	/**
	 * Tries to fix the double url as of AIOEC-882
	 *
	 * @param string $url
	 * @return string
	 */
	public function sanitize_default_theme_url( $url ) {
		$pos_http = strrpos( $url, 'http://');
		$pos_https = strrpos( $url, 'https://');
		// if there are two http
		if( 0 !== $pos_http ) {
			// cut of the first one
			$url = substr( $url, $pos_http );
		} else if ( 0 !== $pos_https ) {
			$url = substr( $url, $pos_https );
		}
		return $url;
	}

	/**
	 * Drop extraneous attributes from variable array and convert to simple
	 * key-value pairs required by the LESS parser.
	 *
	 * @param array $variables
	 * @return array
	 */
	private function convert_less_variables_for_parsing( array $variables ) {
		$converted_variables = array();
		foreach ( $variables as $variable_name => $variable_params ) {
			$converted_variables[$variable_name] = $variable_params['value'];
		}
		return $converted_variables;
	}


	/**
	 * Different themes need different variables.less files. This uses the theme
	 * loader (searches active theme first, then default) to load it unparsed.
	 */
	private function load_static_theme_variables() {
		$loader = $this->_registry->get( 'theme.loader' );
		$file = $loader->get_file( 'variables.less', array(), false );
		$this->unparsed_variable_file = $file->get_content();
	}

	/**
	 * Load font as base 64 encoded
	 *
	 * @param array $matches
	 * @return string
	 */
	private function load_font_base64( $matches ) {
		// Find out the active theme URL.
		$option = $this->_registry->get( 'model.option' );
		$theme  = $option->get( 'ai1ec_current_theme' );
		$dirs   = apply_filters(
			'ai1ec_font_dirs',
			array(
				'AI1EC'   => array(
					$theme['theme_dir'] . DIRECTORY_SEPARATOR . 'font',
					AI1EC_DEFAULT_THEME_PATH . DIRECTORY_SEPARATOR . 'font',
				)
			)
		);
		$directories = $dirs[$matches[1]];
		foreach ( $directories as $dir ) {
			$font_file = $dir . DIRECTORY_SEPARATOR . $matches[2];
			if ( file_exists( $font_file ) ) {
				return base64_encode( file_get_contents( $font_file ) );
			}
		}
		return '';
	}

	/**
	 * Removes fontdir variables added by add-ons.
	 *
	 * @param array $variables Input variables array.
	 *
	 * @return array Modified variables.
	 */
	protected function _compilation_check_clear_variables( array $variables ) {
		foreach ( $variables as $key => $value ) {
			if ( 'fontdir_' === substr( $key, 0, 8 ) ) {
				unset( $variables[$key] );
			}
		}

		return $variables;
	}
}
