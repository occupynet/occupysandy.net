<?php

/**
*	This interface defines the basic cache operations
*/

interface Ai1ec_Cache_Strategy {

	/**
	 * Retrieves the data store for the passed key
	 *
	 * @param string $key
	 * @throws Ai1ec_Cache_Not_Set_Exception if the key wasn't set
	 */
	public function get_data( $key );

	/**
	 * Write the data to the persistence Layer
	 *
	 * @throws Ai1ec_Cache_Write_Exception
	 * @param string $key
	 * @param string $value
	 */
	public function write_data( $key, $value );

	/**
	 * Deletes the data associated with the key from the persistence layer.
	 *
	 * @param string $key
	 */
	public function delete_data( $key );
}