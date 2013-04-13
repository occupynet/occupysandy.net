<?php
/**
 *
 * @author Timely Network Inc
 *
 * This class handles reading/writing of the CSS to the persistence layer.
 */

class Ai1ec_Persistence_Context {

	/**
	 * @var string
	 */
	private $key_for_persistance;

	/**
	 *
	 * @var Ai1ec_Cache_Strategy
	 */
	private $cache_strategy;

	/**
	 *
	 * @param string $key_for_peristance
	 * @param Ai1ec_Cache_Strategy $cache_strategy
	 */
	public function __construct(
		$key_for_persistance,
		Ai1ec_Cache_Strategy $cache_strategy
	) {
		$this->cache_strategy = $cache_strategy;
		$this->key_for_persistance = $key_for_persistance;
	}

	/**
	 * @throws Ai1ec_Cache_Not_Set_Exception
	 * @return string
	 */
	public function get_data_from_persistence() {
		try {
			$data = $this->cache_strategy->get_data( $this->key_for_persistance );
		}
		catch ( Ai1ec_Cache_Not_Set_Exception $e ) {
			throw $e;
		}
		return $data;
	}

	/**
	 * write_data_to_persistence method
	 *
	 * Write data to persistance layer. If that fails - false is returned.
	 * Exceptions are suspended, as cache write is not a fatal error by no
	 * mean, thus shall not be escalated further. If you want exception to
	 * be escalated - use lower layer method directly.
	 *
	 * @param string $data
	 *
	 * @return boll Success
	 */
	public function write_data_to_persistence( $data ) {
		$success = true;
		try {
			if ( ! $this->cache_strategy->write_data(
					$this->key_for_persistance,
					$data
			) ) {
                $success = false;
            }
		} catch ( Ai1ec_Cache_Write_Exception $e ) {
			$success = false;
		}
		return $success;
	}

	/**
	 * Deletes the data stored in cache.
	 */
	public function delete_data_from_persistence() {
		$this->cache_strategy->delete_data( $this->key_for_persistance );
	}

}
