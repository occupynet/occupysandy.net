<?php

/**
 * @author Timely Network Inc
 *
 * This class handles parsing less variables
 */

class Ai1ec_Lessphp_Controller {
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
	 * @var Ai1ec_Db_Adapter
	 */
	private $db_adapter;
	/**
	 *
	 * @var Ai1ec_Less_File
	 */
	private $variable_file;

	public function __construct(
			lessc $lessc,
			$default_theme_url,
			Ai1ec_Db_Adapter $db_adapter
		) {
		$this->lessc = $lessc;
		$this->default_theme_url = self::sanitize_default_theme_url( $default_theme_url );
		$this->db_adapter = $db_adapter;
		$this->parsed_css = '';
	}

	/**
	 *
	 * @param Ai1ec_Less_File $file
	 */
	public function set_variable_file( Ai1ec_Less_File $file ) {
		$this->variable_file = $file;
	}

	/**
	 *
	 * @param Ai1ec_Less_File $file
	 */
	public function add_file( Ai1ec_Less_File $file ) {
		$this->files[] = $file;
	}

	/**
	 * Parse all the less files resolving the dependencies.
	 *
	 * @return string
	 */
	public function parse_less_files( array $variables = null ) {
		global $ai1ec_themes_controller;

		// If no variables are passed i get them from the db
		if( null === $variables ) {
			$variables = $this->db_adapter->get_data_from_config(
				self::DB_KEY_FOR_LESS_VARIABLES
			);
			// If they are not set in the db, get them from file.
			// this happen when the user switched the theme and triggered a new parse.
			if( false === $variables ) {
				$variables = $this->get_less_variable_data_from_config_file(
					Ai1ec_Less_Factory::create_less_file_instance( Ai1ec_Less_File::USER_VARIABLES_FILE )
				);
			}
		}
		// convert the variables to key / value
		$variables = $this->convert_less_variables_for_parsing( $variables );
		// Load the variable.less file to use
		$this->load_less_variables_from_file();

		foreach ( $this->files as $file ) {
			$filename = '';
			/*
			 * @var $file Ai1ec_Less_File
			 */
			try {
				// Get the filename following our fallback convention
				$filename = $file->locate_exact_file_to_load_in_theme_folders();

			} catch ( Ai1ec_File_Not_Found $e ) {
				// We let child themes ovverride properties of vortex.
				// So there is no fallback for override and we can continue.
				if( $file->get_name() !== 'override' ) {
					throw $e;
				} else {
					// it's override, skip it.
					continue;
				}
			}
			// if the file is a css file, no need to parse it, just serve it as usual.
			if( substr_compare( $filename, '.css', -strlen( '.css' ), strlen( '.css' ) ) === 0 ) {
				$this->parsed_css .= file_get_contents( $filename );
				continue;
			}
			// We prepend the unparsed variables.less file we got earlier.
			// We do this as we do not import that anymore in the less files.
			$css_to_parse = $this->unparsed_variable_file . file_get_contents( $filename );

			// Set the import dir for the file. This is important as
			// dependencies will be resolved correctly
			$this->lessc->importDir = dirname( $filename );

			// Set the font & img dirs
			$active_theme_url = AI1EC_THEMES_URL . '/' .
				$ai1ec_themes_controller->active_template_url();
			$variables['fontdir'] = '~"' . $active_theme_url . "/font\"";
			$variables['imgdir'] = '~"' . $active_theme_url . "/img\"";
			$variables['fontdir_default'] = '~"' . $this->default_theme_url . "/font\"";
			$variables['imgdir_default'] = '~"' . $this->default_theme_url . "/img\"";
			try {
				$this->parsed_css .= $this->lessc->parse(
					$css_to_parse,
					$variables
				);
			} catch ( Exception $e ) {
				throw $e;
			}
		}
		return $this->parsed_css;
	}

	/**
	 * Check if the option that stores the less variables is set, otherwise create it
	 *
	 * @param Ai1ec_Wordpress_Db_Adapter $db_adapter
	 * @param string $theme_path
	 */
	public function initialize_less_variables_if_not_set(
		Ai1ec_Less_File $file
	) {
		$saved_variables = $this->db_adapter->get_data_from_config(
			self::DB_KEY_FOR_LESS_VARIABLES
		);

		// If the key is not set, we create the variables
		if ( false === $saved_variables ) {
			$variables_to_save = $this->get_less_variable_data_from_config_file(
				$file
			);
			// do not store the description
			foreach( $variables_to_save as $name => $attributes ) {
				unset( $variables_to_save[$name]['description'] );
			}
			$this->db_adapter->write_data_to_config(
				self::DB_KEY_FOR_LESS_VARIABLES,
				$variables_to_save
			);
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
		$saved_variables = self::get_saved_variables( $this->db_adapter );
		// Get the new variables from file.
		$new_variables = $this->get_less_variable_data_from_config_file(
			Ai1ec_Less_Factory::create_less_file_instance(
				Ai1ec_Less_File::USER_VARIABLES_FILE
			)
		);
		foreach ( $new_variables as $variable_name => $variable_data ) {
			unset( $variable_data['description'] );
			// If the variable already exists, keep the old value.
			if ( isset( $saved_variables[$variable_name] ) ) {
				$variable_data['value'] = $saved_variables[$variable_name]['value'];
			}
			$new_variables[$variable_name] = $variable_data;
		}
		// Wave the new variables to the DB.
		$this->db_adapter->write_data_to_config(
			self::DB_KEY_FOR_LESS_VARIABLES,
			$new_variables
		);
	}

	/**
	 * Get the data for the config from the parsed file.
	 *
	 * @param Ai1ec_Less_File $file
	 * @return array
	 */
	public function get_less_variable_data_from_config_file(
		Ai1ec_Less_File $file
	) {
		// load the file to parse using the usal convention
		require( $file->locate_exact_file_to_load_in_theme_folders() );
		// This variable is locate in the required file
		return $less_user_variables;
	}


	/**
	 * Convert the variables coming from the db to key value pairs used by the less parser
	 *
	 * @param array $variables
	 * @return array
	 */
	private function convert_less_variables_for_parsing( array $variables ) {
		$converted_variables = array();
		foreach( $variables as $variable_name => $variable_params ) {
			$converted_variables[$variable_name] = $variable_params['value'];
		}
		return $converted_variables;
	}


	/**
	 * Different themes need different variable.less files.
	 * Here i use the usual fallback ( active theme first then vortex ) to load it unparsed
	 *
	 */
	private function load_less_variables_from_file() {
		$filename = $this->variable_file->locate_exact_file_to_load_in_theme_folders();
		$this->unparsed_variable_file = file_get_contents( $filename );
	}

	/**
	 * a static method to get variables
	 *
	 * @param Ai1ec_Db_Adapter $db_adapter
	 * @return array
	 */
	static public function get_saved_variables( Ai1ec_Db_Adapter $db_adapter ) {
		$variables = $db_adapter->get_data_from_config(
				self::DB_KEY_FOR_LESS_VARIABLES
		);
		$variables_with_description = self::get_less_variable_data_from_config_file(
			Ai1ec_Less_Factory::create_less_file_instance( Ai1ec_Less_File::USER_VARIABLES_FILE )
		);
		// Add the description at runtime so that it can get the translation
		foreach( $variables as $name => $attrs ) {
			if( isset( $variables_with_description[$name]['description'] ) ) {
				$variables[$name]['description'] = $variables_with_description[$name]['description'];
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
	public static function sanitize_default_theme_url( $url ) {
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
}
