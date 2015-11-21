<?php

/**
 * Content filtering.
 *
 * Guards process execution for multiple runs at the same moment of time.
 *
 * @author     Time.ly Network, Inc.
 * @since      2.1
 * @package    Ai1EC
 * @subpackage Ai1EC.Content
 */
class Ai1ec_Content_Filters extends Ai1ec_Base {

	/**
	 * Stored original the_content filters.
	 * @var array
	 */
	protected $_filters_the_content = array();

	/**
	 * Flag if filters are cleared.
	 * @var bool
	 */
	protected $_filters_the_content_cleared = false;

	/**
	 * Clears all the_content filters excluding few defaults.
	 *
	 * @global array $wp_filter
	 *
	 * @return Ai1ec_Content_Filters This class.
	 */
	public function clear_the_content_filters() {
		global $wp_filter;
		if ( $this->_filters_the_content_cleared ) {
			return $this;
		}
		if ( isset( $wp_filter['the_content'] ) ) {
			$this->_filters_the_content = $wp_filter['the_content'];
		}
		remove_all_filters( 'the_content' );
		add_filter( 'the_content', 'wptexturize' );
		add_filter( 'the_content', 'convert_smilies' );
		add_filter( 'the_content', 'convert_chars' );
		add_filter( 'the_content', 'wpautop' );
		$this->_filters_the_content_cleared = true;
		return $this;
	}

	/**
	 * Restores the_content filters.
	 *
	 * @global array $wp_filter
	 *
	 * @return Ai1ec_Content_Filters This class.
	 */
	public function restore_the_content_filters() {
		global $wp_filter;
		if (
			! $this->_filters_the_content_cleared ||
			empty( $this->_filters_the_content )
		) {
			return $this;
		}
		$wp_filter['the_content'] = $this->_filters_the_content;
		return $this;
	}

	/**
	 * Check if event edit page should display "Move to Trash" button.
	 *
	 * @param array $allcaps An array of all the user's capabilities.
	 * @param array $caps    Actual capabilities for meta capability.
	 * @param array $args    Optional parameters passed to has_cap(), typically object ID.
	 * @param \WP_User $user The user object.
	 *
	 * @return array Capabilities or empty array.
	 */
	public function display_trash_link( $allcaps, $caps, $args, WP_User $user ) {
		if (
			isset( $_GET['instance'] ) &&
			in_array( 'delete_published_ai1ec_events', $caps )
		) {
			return array();
		}
		return $allcaps;
	}
}
