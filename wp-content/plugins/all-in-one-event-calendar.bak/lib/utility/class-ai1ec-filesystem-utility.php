<?php

/** 
 * @author Timely Network Inc
 * 
 * 
 */

class Ai1ec_Filesystem_Utility {

	/**
	 * check if the path is writable. To make the check .
	 *
	 * @param string $path
	 * @return boolean
	 */
	public static function is_writable( $path ) {
		global $wp_filesystem;
		include_once ABSPATH . 'wp-admin/includes/file.php';
		// If for some reason the include doesn't work as expected just return false.
		if( ! function_exists( 'WP_Filesystem' ) ) {
			return false;
		}
		$writable = WP_Filesystem( false, $path );
		// We consider the directory as writable if it uses the direct transport,
		// otherwise credentials would be needed
		return $writable && $wp_filesystem->method === 'direct';
	}
}