<?php

/**
 * @author Timely Network Inc
 *
 * This class is responsible for handling LESS files.
 */

class Ai1ec_Less_File {

	const THEME_CSS_FOLDER = 'css';
	const THEME_LESS_FOLDER = 'less';
	const USER_VARIABLES_FILE = 'user_variables';
	/**
	 * @var string
	 */
	private $name;
	/**
	 * @var string
	 */
	private $active_theme_folder;
	/**
	 * @var string
	 */
	private $default_theme_folder;

	/**
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	public function __construct(
		$name,
		$active_theme_folder,
		$default_theme_folder
		) {
		$this->name = $name;
		$this->active_theme_folder = $active_theme_folder;
		$this->default_theme_folder = $default_theme_folder;
	}

	/**
	 * Locate the file to load.
	 * First it looks if there is a css file in the directory of the current theme.
	 * Then it looks for a less version in the directory of the current theme
	 * Then it looks for a less file into the default theme folder
	 *
	 * @throws Ai1ec_File_Not_Found
	 * @return string
	 */
	public function locate_exact_file_to_load_in_theme_folders() {
		$active_css_folder    = $this->active_theme_folder . DIRECTORY_SEPARATOR . self::THEME_CSS_FOLDER;
		$active_less_folder   = $this->active_theme_folder . DIRECTORY_SEPARATOR . self::THEME_LESS_FOLDER;
		$standard_less_folder = $this->default_theme_folder . DIRECTORY_SEPARATOR . self::THEME_LESS_FOLDER;
		$name = $this->name;

		// Detect parent directory access and handle specially.
		$path_components = explode( '/', $name );
		if ( count( $path_components ) > 1 && $path_components[0] === '..' ) {
			$paths_to_check = array(
				$this->active_theme_folder,
				$this->active_theme_folder,
				$this->default_theme_folder,
			);
			$name = $path_components[1];
		}
		else {
			$paths_to_check = array(
				$active_css_folder,
				$active_less_folder,
				$standard_less_folder,
			);
		}

		$css_file  = "{$name}.css";
		$less_file = "{$name}.less";
		if( $this->name === self::USER_VARIABLES_FILE ) {
			$less_file = "{$name}.php";
			$css_file = $less_file;
		}


		// Look up file. Start with CSS & LESS files in selected theme, then resort
		// to default theme's LESS file.
		$files_to_check = array(
			$paths_to_check[0] . DIRECTORY_SEPARATOR . $css_file,
			$paths_to_check[1] . DIRECTORY_SEPARATOR . $less_file,
			$paths_to_check[2] . DIRECTORY_SEPARATOR . $less_file,
		);
		foreach( $files_to_check as $file_to_check ) {
			if( file_exists( $file_to_check ) ) {
				return $file_to_check;
			}
		}

		throw new Ai1ec_File_Not_Found( "Could not find a version of \"$name\" to parse." );
	}
}
