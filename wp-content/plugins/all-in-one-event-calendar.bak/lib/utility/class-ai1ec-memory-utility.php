<?php

/**
 * In-memory storage utility, to aid in development
 *
 * @author     Justas Butkus <justas@butkus.lt>
 * @since      2012.10.05
 *
 * @package    AllInOneCalendar
 * @subpackage AllInOneCalendar.Lib.Utilities
 */
class Ai1ec_Memory_Utility
{

	/**
	 * @var array Map of self instances for each named memory location
	 */
	static protected $_instances = array();

	/**
	 * @var array Map of memory entries
	 */
	protected $_entries          = array();

	/**
	 * @var int Number of entries to hold in map
	 */
	protected $_limit            = 0;

	/**
	 * instance method
	 *
	 * Singleton instance to aid in development. Usefull where reference is
	 * more conveniently passed by name, instead of by reference.
	 *
	 * @param string $name  Name for memory location
	 *
	 * @return Ai1ec_Memory_Utility Instance of self for given name
	 */
	static public function instance( $name ) {
		if ( ! isset( self::$_instances[$name] ) ) {
			self::$_instances[$name] = new self();
		}
		return self::$_instances[$name];
	}

	/**
	 * Constructor
	 *
	 * @param int $limit Number of entries specific to this location
	 *
	 * @return void Constructor does not return
	 */
	public function __construct( $limit = 50 ) {
		$limit = (int)$limit;
		if ( $limit < 10 ) {
			$limit = 10;
		}
		$this->_limit = $limit;
	}

	/**
	 * set method
	 *
	 * Write data to memory under given key.
	 *
	 * @param string $key   Key under which value must be written
	 * @param mixed  $value Value to associate with given key
	 *
	 * @return bool Success
	 */
	public function set( $key, $value ) {
		if ( count( $this->_entries ) > $this->_limit ) {
			array_shift( $this->_entries ); // discard
		}
		$this->_entries[$key] = $value;
		return true;
	}

	/**
	 * add method
	 *
	 * Add data to memory under given key, if it does not exist.
	 *
	 * @param string $key   Key under which value must be added
	 * @param mixed  $value Value to associate with given key
	 *
	 * @return bool Success
	 */
	public function add( $key, $value ) {
		if ( isset( $this->_entries[$key] ) ) {
			return false;
		}
		return $this->set( $key, $value );
	}

	/**
	 * get method
	 *
	 * Retrieve data from memory, stored under specified key.
	 *
	 * @param string $key     Key under which value is expected to be
	 * @param mixed  $default Value to return if nothing is found
	 *
	 * @return mixed Found value or {$default}
	 */
	public function get( $key, $default = NULL ) {
		if ( ! isset( $this->_entries[$key] ) ) {
			return $default;
		}
		return $this->_entries[$key];
	}

}
