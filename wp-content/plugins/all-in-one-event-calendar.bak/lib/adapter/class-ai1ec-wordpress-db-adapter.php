<?php


/**
 * @author Timely Network Inc
 */

class Ai1ec_Wordpress_Db_Adapter implements Ai1ec_Db_Adapter {
	private $wpdb;
	function __construct( wpdb $db = null ) {
		if( null === $db ) {
			global $wpdb;
			$this->wpdb = $wpdb;
		} else {
			$this->wpdb = $db;
		}
	}

	/**
	 *
	 * @param $key string
	 *
	 * @see Ai1ec_Db_Adapter::get_data_from_config()
	 *
	 */
	public function get_data_from_config( $key ) {
		return Ai1ec_Meta::get_option( $key );
	}

	/* (non-PHPdoc)
	 * @see Ai1ec_Db_Adapter::write_data_to_config()
	 */
	public function write_data_to_config( $key, $value ) {
		delete_option( $key );
		return add_option( $key, $value);
	}

	/**
	 * (non-PHPdoc)
	 * @see Ai1ec_Db_Adapter::delete_data_from_config()
	 */
	public function delete_data_from_config( $key ) {
		return delete_option( $key );
	}
}
