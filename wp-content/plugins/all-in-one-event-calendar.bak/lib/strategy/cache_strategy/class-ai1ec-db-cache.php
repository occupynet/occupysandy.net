<?php

/**
 *
 * @author Timely Network Inc
 *
 * Concrete implementation for db cache.
 */

class Ai1ec_Db_Cache implements Ai1ec_Cache_Strategy {

	/**
	 * @var Ai1ec_Db_Adapter
	 */
	private $db_adapter;

	public function __construct( Ai1ec_Db_Adapter $db_adapter ) {
		$this->db_adapter = $db_adapter;
	}

	/**
	 *
	 * @see Ai1ec_Get_Data_From_Cache::get_data()
	 *
	 */
	public function get_data( $key ) {
		$data = $this->db_adapter->get_data_from_config( $key );
		if( false === $data ) {
			throw new Ai1ec_Cache_Not_Set_Exception();
		}
		return $data;
	}

	/**
	 *
	 * @see Ai1ec_Write_Data_To_Cache::write_data()
	 *
	 */
	public function write_data( $key, $value ) {
		$result = $this->db_adapter->write_data_to_config( $key, $value );
		if( false === $result ) {
			throw new Ai1ec_Cache_Write_Exception( "An error occured while saving data to $key" );
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see Ai1ec_Write_Data_To_Cache::delete_data()
	 */
	public function delete_data( $key ) {
		return $this->db_adapter->delete_data_from_config( $key );
	}
}
