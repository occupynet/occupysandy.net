<?php

/**
 *
 * @author Timely Network Inc
 *
 * Concrete implementation for file cache.
 */
class Ai1ec_File_Cache implements Ai1ec_Cache_Strategy {

	/**
	 * @var string
	 */
	private $cache_dir;

	public function __construct( $cache_dir ) {
		$this->cache_dir = $cache_dir;
	}
	/**
	 *
	 * @see Ai1ec_Get_Data_From_Cache::get_data()
	 *
	 */
	public function get_data( $file ) {
		if( ! file_exists( $this->cache_dir . $file ) ) {
			throw new Ai1ec_Cache_Not_Set_Exception( "File $file does not exist" );
		}
		return file_get_contents( $this->cache_dir . $file );
	}

	/**
	 *
	 * @see Ai1ec_Write_Data_To_Cache::write_data()
	 *
	 */
	public function write_data( $filename, $value ) {
		global $wp_filesystem;
		$result = $wp_filesystem->put_contents( $this->cache_dir . $filename, $value );
		if( false === $result ) {
			throw new Ai1ec_Cache_Write_Exception( "An error occured while saving data to {$this->cache_dir}$filename" );
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see Ai1ec_Write_Data_To_Cache::delete_data()
	 */
	public function delete_data( $filename ) {
		// Check if file exists. It might not exists if you switch themes twice without never rendering the css
		if( file_exists( $this->cache_dir . $filename ) ) {
			return unlink( $this->cache_dir . $filename );
		}
		return true;
	}
}
