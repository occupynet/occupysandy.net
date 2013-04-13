<?php
/**
 *
 * @author Timely Network Inc
 *
 * This class is responsible for handling admin notices.
 *
 * It will handle two types of notices; as it iss now it will just handle
 * "request" notices, which are set in various parts of the app and just need
 * to be displayed.
 *
 * Later on we will add support for DB notices which must be displayed on every
 * page view until dismissed. I really don't like singletons but this is one of
 * the rare case in which it makes a lot of sense.
 */
class Ai1ec_Admin_Notices_Helper implements Ai1ec_Renderable {

	const TRANSIENT_ADMIN_MESSAGES = 'ai1ec_transient_admin_messages';

	/**
	 * _instance class variable
	 *
	 * Class instance
	 *
	 * @var null | object
	 **/
	private static $_instance = NULL;


	private function __construct() {
	}

	/**
	 * Adds a renderable child to the element
	 *
	 * @param Ai1ec_Renderable $renderable
	 */
	public function add_renderable_children( Ai1ec_Renderable $renderable ) {
		$messages = get_transient( self::$_instance );
		if( false === $messages ) {
			$messages = array();
		}
		$messages[] = $renderable;
		set_transient( self::TRANSIENT_ADMIN_MESSAGES, $messages, 7200 );
	}


	/**
	 * get_instance function
	 *
	 * Return singleton instance
	 *
	 * @return object
	 **/
	public static function get_instance() {
		if( self::$_instance === NULL ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * This is needed becauseget_transient() requires it
	 * 
	 * @return string
	 */
	public function __toString() {
		return '';
	}

	/* (non-PHPdoc)
	 * @see Ai1ec_Renderable::render()
	 */
	public function render() {
		$messages = get_transient( self::TRANSIENT_ADMIN_MESSAGES );
		if ( false !== $messages ) {
			foreach( $messages as $renderable ) {
				$renderable->render();
			}
		}
		delete_transient( self::TRANSIENT_ADMIN_MESSAGES );
	}
}
