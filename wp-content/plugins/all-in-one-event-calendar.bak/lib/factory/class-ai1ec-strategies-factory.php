<?php

/** 
 * @author Timely Network Inc
 * 
 * 
 */

class Ai1ec_Strategies_Factory {

	/**
	 * @var array
	 */
	private static $cache_directories = array();

	/**
	 * @param string $cache_directory
	 * @return Ai1ec_Cache_Strategy
	 */
	public static function create_cache_startegy_instance( $cache_directory = null ) {
		$is_cache_directory_writable = false;
		if ( null !== $cache_directory ) {
			if ( ! isset( self::$cache_directories[$cache_directory] ) ) {
				self::$cache_directories[$cache_directory] = Ai1ec_Filesystem_Utility::is_writable( 
					$cache_directory
				);
			}
			$is_cache_directory_writable = self::$cache_directories[$cache_directory];
		}
		$is_apc_installed = function_exists( 'apc_store' )
		                  && function_exists( 'apc_fetch' )
		                  && ini_get( 'apc.enabled' );
		$sapi_type = php_sapi_name();
		if (
			substr( $sapi_type, 0, 3 ) === 'cgi' ||
			substr( $sapi_type, -3, 3 ) === 'cgi'
		) {
			$is_apc_installed = false;
		}
		if ( $is_apc_installed ) {
			return new Ai1ec_Apc_Cache();
		} else if ( $is_cache_directory_writable ) {
			return new Ai1ec_File_Cache( $cache_directory );
		} else {
			return new Ai1ec_Db_Cache( Ai1ec_Adapters_Factory::create_db_adapter_instance() );
		}

	}

	/**
	 * @param string $key_for_persistance
	 * @param Ai1ec_Cache_Strategy $cache_strategy
	 * @param string $cache_directory
	 * @return Ai1ec_Persistence_Context
	 */
	public static function create_persistence_context( 
		$key_for_persistance,
		$cache_directory = null
	) {
		return new Ai1ec_Persistence_Context( 
			$key_for_persistance, 
			self::create_cache_startegy_instance( $cache_directory )
		);
	}
}