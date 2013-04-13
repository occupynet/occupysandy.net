<?php

interface Ai1ec_Db_Adapter {
	/**
	 * Get the data from the config object
	 * 
	 * @param string $key
	 * 
	 * @return mixed The value stored in the db or false if the key was not set
	 */
	public function get_data_from_config( $key );
	/**
	 * Write the data to the config object
	 * 
	 * @param string $key
	 * @param string $value
	 * @return boolean TRUE if save ok, false otherwise
	 */
	public function write_data_to_config( $key, $value );
	/**
	 * Deletes the data from the config object
	 * 
	 * @param string $key
	 */
	public function delete_data_from_config( $key );
}