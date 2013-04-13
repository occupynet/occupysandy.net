<?php
/**
 * @author Timely Network Inc
 *
 * Concrete implementation for getting data from APC.
 */
class Ai1ec_Apc_Cache implements Ai1ec_Cache_Strategy {

	/**
	 *
	 * @see Ai1ec_Get_Data_From_Cache::get_data()
	 *
	 */
	public function get_data( $dist_key ) {
		$key  = $this->_key( $dist_key );
		$data = apc_fetch( $key );
		if ( false === $data ) {
			throw new Ai1ec_Cache_Not_Set_Exception( "$dist_key not set" );
		}
		return $data;
	}

	/**
	 *
	 * @see Ai1ec_Write_Data_To_Cache::write_data()
	 *
	 */
	public function write_data( $dist_key, $value ) {
		$key          = $this->_key( $dist_key );
		$store_method = 'apc_add';
		if ( false !== ( $existing = apc_fetch( $key ) ) ) {
			if ( $value === $existing ) {
				return true;
			}
			$store_method = 'apc_store';
		} elseif ( false === function_exists( $store_method ) ) {
			$store_method = 'apc_store';
		}
		if ( false === $store_method( $key, $value ) ) {
			try {
				if ( $value !== $this->get_data( $key ) ) {
					throw new Ai1ec_Cache_Not_Set_Exception( 'Data mis-match' );
				}
			} catch ( Ai1ec_Cache_Not_Set_Exception $excpt ) {
				throw new Ai1ec_Cache_Not_Set_Exception(
					'Failed to write ' . $dist_key . ' to APC cache'
				);
			}
		}
		return true;
	}

	/**
	 * (non-PHPdoc)
	 * @see Ai1ec_Write_Data_To_Cache::delete_data()
	 */
	public function delete_data( $key ) {
		return apc_delete( $this->_key( $key ) );
	}

	/**
	 * _key method
	 *
	 * Make sure we are on the safe side - in case of multi-instances
	 * environment some prefix is required.
	 *
	 * @param string $key Key to be used against APC cache
	 *
	 * @return string Key with prefix prepended
	 */
	protected function _key( $key ) {
		static $prefix = NULL;
		if ( NULL === $prefix ) {
			$prefix = substr( md5( site_url() ), 0, 8 );
		}
		if ( 0 !== strncmp( $key, $prefix, 8 ) ) {
			$key = $prefix . $key;
		}
		return $key;
	}

}
