<?php

/**
 * Routing (management) base class
 *
 * @author     Justas Butkus <justas@butkus.lt>
 * @since      2012.07.20
 *
 * @package    AllInOneCalendar
 * @subpackage AllInOneCalendar.Lib.Routing
 */
class Ai1ec_Router
{

	/**
	 * @var Ai1ec_Router Instance of self
	 */
	static private $_instance = null;

	/**
	 * @var string Calendar base url
	 */
	protected $_calendar_base = null;

	/**
	 * @var string Base URL of WP installation
	 */
	protected $_site_url = NULL;

	/**
	 * @var Ai1ec_Adapter_Query_Interface Query manager object
	 */
	protected $_query_manager = null;

	/**
	 * Singleton access method
	 *
	 * @return Ai1ec_Router Instance of self
	 */
	static public function instance() {
		if (
			! isset( self::$_instance ) ||
			! ( self::$_instance instanceof Ai1ec_Router )
		) {
			self::$_instance = new Ai1ec_Router();
		}
		return self::$_instance;
	}

	/**
	 * Set base (AI1EC) URI
	 *
	 * @param string $uri Base URI (i.e. http://www.example.com/calendar)
	 *
	 * @return Ai1ec_Router Object itself
	 */
	public function asset_base( $url ) {
		$this->_calendar_base = $url;
		return $this;
	}

	/**
	 * Get base URL of WP installation
	 *
	 * @return string URL where WP is installed
	 */
	public function get_site_url() {
		if ( NULL === $this->_site_url ) {
			$this->_site_url = site_url();
		}
		return $this->_site_url;
	}

	/**
	 * Generate (update) URI
	 *
	 * @param array	 $arguments List of arguments to inject into AI1EC group
	 * @param string $page		Page URI to modify
	 *
	 * @return string Generated URI
	 */
	public function uri( array $arguments, $page = NULL ) {
		if ( NULL === $page ) {
			$page = $this->_calendar_base;
		}
		$uri_parser = new Ai1ec_Uri();
		$parsed_url = $uri_parser->parse( $page );
		$parsed_url['ai1ec'] = array_merge(
			$parsed_url['ai1ec'],
			$arguments
		);
		$result_uri = $uri_parser->write( $parsed_url );

		return $result_uri;
	}

	/**
	 * Register rewrite rule to enable work with pretty URIs
	 */
	public function register_rewrite( $rewrite_to ) {
		if (
			! $this->_calendar_base &&
			! $this->_query_manager->rewrite_enabled()
		) {
			return $this;
		}
		$base = basename( $this->_calendar_base );
		if ( false !== strpos( $base, '?' ) ) {
			return $this;
		}
		$base   = '(?:.+/)?' . $base;
		$regexp = $base . '(\/[a-z0-9\-_:\/]+|\/?$)';
		$clean_base = trim( $this->_calendar_base, '/' );
		$clean_site = trim( $this->get_site_url(), '/' );
		if ( 0 === strcmp( $clean_base, $clean_site ) ) {
			$regexp = '([a-z][a-z0-9\-_:\/]*:[a-z0-9\-_:\/])';
			$rewrite_to = remove_query_arg( 'pagename', $rewrite_to );
		}
		$this->_query_manager->register_rule(
			$regexp,
			$rewrite_to
		);
		return $this;
	}

	/**
	 * Initiate internal variables
	 */
	protected function __construct() {
		$this->_query_manager = Ai1ec_Adapter::query_manager();
	}

}
