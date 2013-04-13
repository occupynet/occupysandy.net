<?php

/** 
 * @author Timely Network Inc
 * 
 * 
 */

class Ai1ec_String_Utility {

	/**
	 * classify method
	 *
	 * Attempt to guess class name given file name
	 *
	 * @param string $file Full path to file being checked
	 *
	 * @return string Guessed name of class
	 */
	static public function classify( $file ) {
		$class_name = basename( $file, '.php' );
		$class_name = strtr( $class_name, array( '-' => '_', ' ' => '_' ) );
		return $class_name;
	}

	/**
	 * Truncate a string after $number_of_words words
	 *
	 * @param string $input
	 * @param int $number_of_words
	 * @return string
	 */
	static public function truncate_string_if_longer_than_x_words(
		$input,
		$number_of_words,
		$ellipsis = '...'
	) {
		$words = explode( ' ', $input );
		if ( count( $words ) > $number_of_words ) {
			$short = self::restore_truncated_html_tags(
				implode( ' ', array_slice( $words, 0, $number_of_words ) )
			);
			return $short . $ellipsis;
		} else {
			return $input;
		}
	}

	/**
	 * Closes opened html tags which are not closed because they are truncated
	 *
	 * @param string $input
	 * @return string
	 */
	static private function restore_truncated_html_tags( $input ) {
		$opened = array();
	
		// loop through opened and closed tags in order
		if( preg_match_all( "/<(\/?[a-z]+)>?/i", $input, $matches ) ) {
			foreach( $matches[1] as $tag ) {
				if( preg_match( "/^[a-z]+$/i", $tag, $regs ) ) {
					// a tag has been opened
					if( strtolower( $regs[0] ) != 'br' ) {
						$opened[] = $regs[0];
					}
				} elseif( preg_match( "/^\/([a-z]+)$/i", $tag, $regs ) ) {
					// a tag has been closed
					unset( $opened[array_pop( array_keys( $opened, $regs[1] ) )] );
				}
			}
		}
	
		// close tags that are still open
		if(  $opened) {
			$tagstoclose = array_reverse( $opened );
			foreach( $tagstoclose as $tag ) {
				$input .= "</$tag>";
			}
		}
	
		return $input;
	}
}
