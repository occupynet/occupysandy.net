<?php

/**
 * @author Timely Network Inc
 */

class Ai1ec_Wordpress_Template_Adapter implements Ai1ec_Template_Adapter {

	/**
	 *
	 * @param $id string
	 *
	 * @param $title string
	 *
	 * @param $callback callable
	 *
	 * @param $post_type string
	 *
	 * @param $context string
	 *
	 * @see Ai1ec_Template_Adapter::add_meta_box()
	 *
	 */
	public function add_meta_box( $id, $title, $callback, $screen, $context ) {
		return add_meta_box( $id, $title, $callback, $screen, $context );
	}

	/**
	 *
	 * @param $screen string
	 *
	 * @param $context string
	 *
	 * @param $object mixed
	 *
	 * @see Ai1ec_Template_Adapter::display_meta_box()
	 *
	 */
	public function display_meta_box( $screen, $context, $object ) {
		return do_meta_boxes( $screen, $context, $object );
	}

	/**
	 * (non-PHPdoc)
	 * @see Ai1ec_Template_Adapter::escape_attribute()
	 */
	public function escape_attribute( $text ) {
		return esc_attr( $text );
	}

	/**
	 * (non-PHPdoc)
	 * @see Ai1ec_Template_Adapter::enqueue_script()
	 */
	public function enqueue_script( $handle, $src, array $dep = array(), $ver = false, $media = 'all' ) {
		return wp_enqueue_style( $handle, $src, $dep, $ver, $media );
	}

	/**
	 * (non-PHPdoc)
	 * @see Ai1ec_Template_Adapter::get_site_url()
	 */
	public function get_site_url() {
		return get_site_url();
	}
}
