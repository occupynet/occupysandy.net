<?php

/**
*
* This interface defines template related methods
*/

interface Ai1ec_Template_Adapter {
	/**
	 * @param string $id
	 * @param string $title
	 * @param callable $callback
	 * @param string $screen
	 * @param string $context
	 */
	public function add_meta_box( $id, $title, $callback, $screen, $context );
	/**
	 * @param string $screen
	 * @param string $context
	 * @param mixed $object
	 */
	public function display_meta_box( $screen, $context, $object );
	/**
	 * Escape a string so that it can be used as an html attribute
	 * 
	 * @param string $text
	 */
	public function escape_attribute( $text );
	
	/**
	 * Return the site url
	 */
	public function get_site_url();
	
	/**
	 * Enqueue a script for the template
	 * 
	 * @param string $handle
	 * @param string $src
	 * @param array $dep
	 * @param bool|string $ver
	 * @param string $media
	 */
	public function enqueue_script( $handle, $src, array $dep = array(), $ver = false, $media = 'all' );
}