<?php

/**
 * WordPress backed meta management
 *
 * @author     Justas Butkus <justas@butkus.lt>
 * @since      2012.10.02
 *
 * @package    AllInOneCalendar
 * @subpackage AllInOneCalendar.Model.Meta
 */
abstract class Ai1ec_Meta
{

	/**
	 * @constant int Number of entries to keep in cache
	 */
	const CACHE_LENGTH           = 200;

	/**
	 * @staticvar Ai1ec_Meta Singleton self instance
	 */
	static protected $_instances = array();

	/**
	 * @var array Map of cached entries
	 */
	protected $_cache            = array();

	/**
	 * instance method
	 *
	 * Get single instance of class.
	 *
	 * @param string $type Meta value corresponding class
	 *
	 * @return Ai1ec_Meta Self instance
	 */
	static public function instance( $type ) {
		$class = 'Ai1ec_Meta_' . $type;
		if (
			! isset( self::$_instances[$class] ) ||
			! ( self::$_instances[$class] instanceof Ai1ec_Meta )
		) {
			self::$_instances[$class] = new $class();
		}
		return self::$_instances[$class];
	}

	/**
	 * get_option method
	 *
	 * Method to simplify call to option retrieval.
	 * Options are commonly retrieved, thus a helper method.
	 *
	 * @param string $name     Option to fetch
	 * @param NULL   $default  Value to return if $name is not found
	 *
	 * @return mixed Found option or {$default}
	 */
	static public function get_option( $name, $default = false ) {
		return self::instance( 'Option' )->get( $name, NULL, $default, true );
	}

	/**
	 * get method
	 *
	 * Method to get cached meta value (skipping callbacks and all) with
	 * cache guard, to avoid blowing in memory.
	 *
	 * @param string $base_key Major key identifying option or opt. group
	 * @param string $sub_key  Secondary key to identify value within base group
	 * @param mixed  $default  Default value, if nothing is found
	 * @param mixed  $options  Additional fetch options to instruct WP
	 *
	 * @return mixed Found value or {$default}
	 */
	public function get(
		$base_key,
		$sub_key = NULL,
		$default = false,
		$options = false
	) {
		static $zero_byte = "\0";
		$cache_key = $base_key . $zero_byte . $sub_key;
		if ( ! isset( $this->_cache[$cache_key] ) ) {
			if ( count( $this->_cache ) > self::CACHE_LENGTH ) {
				array_shift( $this->_cache ); // discard
			}
			$value = $this->_fetch( $base_key, $sub_key, $default, $options );
			$this->_cache[$cache_key] = compact(
				'base_key',
				'sub_key',
				'value'
			);
		}
		return $this->_cache[$cache_key]['value'];
	}

	/**
	 * clean method
	 *
	 * Purge all cached entries.
	 *
	 * @return void Method does not return
	 */
	public function clean() {
		$this->_cache = array();
	}

	/**
	 * __clone method
	 *
	 * Magic method to be triggered on object cloning.
	 * Prohibits actual cloning via throwing exception.
	 */
	public function __clone() {
		throw new Ai1ec_Singleton_Restriction( 'Cloning is prohibited' );
	}

	/**
	 * _fetch method
	 *
	 * Method to retrieve actual value using WP interface.
	 *
	 * @param string $base_key Major key identifying option or opt. group
	 * @param string $sub_key  Secondary key to identify value within base group
	 * @param mixed  $default  Default value, if nothing is found
	 * @param mixed  $options  Additional fetch options to instruct WP
	 *
	 * @return mixed Found value or {$default}
	 */
	abstract protected function _fetch(
		$base_key,
		$sub_key = NULL,
		$default = false,
		$options = false
	);

	/**
	 * _after_initialize method
	 *
	 * Callback method, to avoid overloading constructor
	 *
	 * @return NULL Return value is not used
	 */
	protected function _after_initialize() {
		return NULL;
	}

	/**
	 * Constructor
	 *
	 * Initialize cache entries, if any are common.
	 *
	 * @return void Constructor does not return
	 */
	protected function __construct() {
		$this->_cache = array(); // extract common values
		$this->_after_initialize();
	}

}
