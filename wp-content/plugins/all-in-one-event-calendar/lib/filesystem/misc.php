<?php

/**
 * Miscellaneous file system related functions.
 *
 * @author     Time.ly Network Inc.
 * @since      2.2
 *
 * @package    AI1EC
 * @subpackage AI1EC.Lib.Filesystem
 */
class Ai1ec_Filesystem_Misc extends Ai1ec_Base {

	/**
	 * Builds directory hashmap.
	 *
	 * @param array|string $paths      Paths for hashmap generation. It accepts
	 *                                 string or array of paths. Elements in
	 *                                 hashmaps are not overwritten.
	 * @param array        $exclusions List of excluded file names.
	 *
	 * @return array Hashmap.
	 */
	public function build_dirs_hashmap( $paths, $exclusions = array() ) {
		if ( ! is_array( $paths ) ) {
			$paths = array( $paths );
		}
		$hashmap = array();
		foreach ( $paths as $path ) {
			if ( file_exists( $path ) ) {
				$hashmap += $this->build_dir_hashmap( $path, $exclusions );
			}
		}

		ksort( $hashmap );

		return $hashmap;
	}

	/**
	 * Builds hashmap for given directory.
	 *
	 * @param string $directory  Directory for hashmap creation.
	 * @param array  $exclusions List of excluded file names.
	 *
	 * @return array Hashmap.
	 */
	public function build_dir_hashmap( $directory, $exclusions = array() ) {
		$directory_iterator = new RecursiveDirectoryIterator(
			$directory,
			RecursiveDirectoryIterator::SKIP_DOTS
		);
		$recursive_iterator = new RecursiveIteratorIterator(
			$directory_iterator
		);
		$files              = new RegexIterator(
			$recursive_iterator,
			'/^.+\.(less|css|php)$/i',
			RegexIterator::GET_MATCH
		);
		$hashmap            = array();
		foreach ( $files as $file ) {
			$file_info = new SplFileInfo( $file[0] );
			$file_path = $file_info->getPathname();
			if ( in_array( $file_info->getFilename(), $exclusions ) ) {
				continue;
			}
			$key = str_replace(
				array( $directory, '/' ),
				array( '', '\\' ),
				$file_path
			);

			$hashmap[ $key ] = array(
				'size' => $file_info->getSize(),
				'sha1' => sha1_file( $file_path ),
			);
		}
		ksort( $hashmap );

		return $hashmap;
	}

	/**
	 * Returns hashmap for current theme.
	 *
	 * @return mixed|null Hashmap or null if none.
	 *
	 * @throws Ai1ec_Bootstrap_Exception
	 */
	public function get_current_theme_hashmap() {
		$cur_theme     = $this->_registry->get( 'model.option' )->get(
			'ai1ec_current_theme'
		);
		$file_location = $cur_theme['theme_dir'] . DIRECTORY_SEPARATOR .
		                 'less.sha1.map.php';
		if ( ! file_exists( $file_location ) ) {
			return null;
		}

		return require $file_location;
	}

	/**
	 * Builds file hashmap for current theme.
	 *
	 * @return array Hashmap.
	 *
	 * @throws Ai1ec_Bootstrap_Exception
	 * @throws Ai1ec_Invalid_Argument_Exception
	 */
	public function build_current_theme_hashmap() {
		$paths = $this->_registry->get( 'theme.loader' )->get_paths();

		return $this->build_dirs_hashmap(
			array_keys(
				$paths['theme']
			),
			array(
				'ai1ec_parsed_css.css',
				'less.sha1.map.php',
				'index.php',
			)
		);
	}

	/**
	 * Returns theme structrure for one of core themes.
	 *
	 * @param string $stylesheet Theme stylesheet. Expected one of
	 *                           ['plana','vortex','umbra','gamma'].
	 *
	 * @return array Theme structure
	 *
	 * @throws Ai1ec_Invalid_Argument_Exception
	 */
	public function build_theme_structure( $stylesheet ) {
		$themes = array( 'plana', 'vortex', 'umbra', 'gamma' );
		if ( ! in_array( $stylesheet, $themes ) ) {
			throw new Ai1ec_Invalid_Argument_Exception(
				'Theme ' . $stylesheet . ' compilation is not supported.'
			);
		}
		$root = AI1EC_PATH . DIRECTORY_SEPARATOR . 'public' .
		        DIRECTORY_SEPARATOR . AI1EC_THEME_FOLDER;

		return array(
			'theme_root' => $root,
			'theme_dir'  => $root . DIRECTORY_SEPARATOR . $stylesheet,
			'theme_url'  => AI1EC_URL . '/public/' . AI1EC_THEME_FOLDER . '/' . $stylesheet,
			'stylesheet' => $stylesheet,
			'legacy'     => false,
		);
	}

	/**
	 * Compares files hashmaps. If $src key doesn't exist in $dst, it's just
	 * ommited. This is intended for LESS compilation check. Current theme
	 * may contain more LESS files than base one, what does not matter as
	 * other files should be changed accordingly.
	 *
	 * @param array $src Source hashmap. Should be computed from current
	 *                   theme contents.
	 * @param array $dst Base hashmap. Should be taken from less.sha1.map.php
	 *                   file.
	 *
	 * @return bool Comparision result. True if they are equal.
	 */
	public function compare_hashmaps( array $src, array $dst ) {
		foreach ( $src as $key => $value ) {
			if ( ! isset( $dst[ $key ] ) ) {
				continue;
			}
			$dst_value = $dst[ $key ];
			if ( $dst_value !== $value ) {
				return false;
			}
		}

		return true;
	}
}
