<?php
//
//  global-functions.php
//  all-in-one-event-calendar
//
//  Created by The Seed Studio on 2012-02-28.
//

if ( ! function_exists( 'pr' ) ):
/**
 * pr function
 *
 * Debug output of variable.
 * Print variable information (using var_dump for {@see empty()} values
 * and print_r otherwise) optionally preceeded by {$title}.
 *
 * @param mixed  $arg   Variable to output (print)
 * @param string $title Title to preceed the variable information
 *
 * @return void Method does not return
 */
function pr( $arg, $title = null )
{
	if ( WP_DEBUG ) {
		if ( $title ) {
			echo '<strong style="font-family:fixed;font-size:1.6em">',
				$title, '</strong>';
		}
		echo '<pre>';
		if ( empty( $arg ) ) {
			var_dump( $arg );
		} else {
			print_r( $arg );
		}
		echo '</pre>';
	}
}
endif;

/**
 * url_get_contents function
 *
 * @param string $url URL 
 *
 * @return string
 **/
function url_get_contents( $url ) {
	// holds the output
	$output = "";

	// To make a remote call in wordpress it's better to use the wrapper functions instead
	// of class methods. http://codex.wordpress.org/HTTP_API
	// SSL Verification was disabled in the cUrl call
	$result = wp_remote_get( $url, array( 'sslverify' => false, 'timeout' => 120 ) );
	// The wrapper functions return an WP_error if anything goes wrong.
	if( is_wp_error( $result ) ) {
		// We explicitly return false to notify an error. This is exactly the same behaviour we had before
		// because both curl_exec() and file_get_contents() returned false on error
		return FALSE;
	}

	$output = $result['body'];

	// check if data is utf-8
	if( ! SG_iCal_Parser::_ValidUtf8( $output ) ) {
		// Encode the data in utf-8
		$output = utf8_encode( $output );
	}

	return $output;
}

/**
 * is_curl_available function
 *
 * checks if cURL is enabled on the system
 *
 * @return bool
 **/
function is_curl_available() { 
	
	if( ! function_exists( "curl_init" )   && 
      ! function_exists( "curl_setopt" ) && 
      ! function_exists( "curl_exec" )   && 
      ! function_exists( "curl_close" ) ) {
			
			return false; 
	}
	
	return true;
}

/**
 * ai1ec_utf8 function
 *
 * Encode value as safe UTF8 - discarding unrecognized characters.
 * NOTE: objects will be cast as array.
 *
 * @uses iconv               To change encoding
 * @uses mb_convert_encoding To change encoding if `iconv` is not available
 *
 * @param mixed $input Value to encode
 *
 * @return mixed UTF8 encoded value
 *
 * @throws Exception If no trans-coding method is available
 */
function ai1ec_utf8( $input ) {
	if ( NULL === $input ) {
		return NULL;
	}
	if ( is_scalar( $input ) ) {
		if ( function_exists( 'iconv' ) ) {
			return iconv( 'UTF-8', 'UTF-8//IGNORE', $input );
		}
		if ( function_exists( 'mb_convert_encoding' ) ) {
			return mb_convert_encoding( $input, 'UTF-8' );
		}
		throw new Exception(
			'Either `iconv` or `mb_convert_encoding` must be available.'
		);
	}
	if ( ! is_array( $input ) ) {
		$input = (array)$input;
	}
	return array_map( 'ai1ec_utf8', $input );
}
