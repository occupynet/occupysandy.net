<?php

/**
 * Model representing an event or an event instance.
 *
 * @author       Time.ly Network, Inc.
 * @since        2.1
 * @instantiator Ai1ec_Factory_Event.create_event_instance
 * @package      Ai1EC
 * @subpackage   Ai1EC.Model
 */
class Ai1ec_Event_Compatibility extends Ai1ec_Event {

	/**
	 * Getter.
	 *
	 * @param string $name Property name.
	 *
	 * @return mixed Property value.
	 */
	public function __get( $name ) {
		$value = $this->get( $name );
		if ( null !== $value ) {
			return $value;
		}
		return $this->get_runtime( $name );
	}

	/**
	 * Isset magic function.
	 *
	 * @param string $name Property name.
	 *
	 * @return bool True of false.
	 */
	public function __isset( $name ) {
		$method_name = 'get' . $name;
		if ( method_exists( $this, $method_name ) ) {
			return false;
		}
		return ( null !== $this->$name );
	}

	/**
	 * Twig timespan short method.
	 *
	 * @return string Value.
	 */
	public function gettimespan_short() {
		return $this->_registry->get( 'view.event.time' )
			->get_timespan_html( $this, 'short' );
	}

	/**
	 * Twig is_allday method.
	 *
	 * @return bool Value.
	 */
	public function getis_allday() {
		return $this->is_allday();
	}

	/**
	 * Twig is_multiday method.
	 *
	 * @return bool Value.
	 */
	public function getis_multiday() {
		return $this->is_multiday();
	}

	/**
	 * Returns Event instance permalink for FER compatibility.
	 *
	 * @return string Event instance permalink.
	 */
	public function getpermalink() {
		return $this->get_runtime( 'instance_permalink' );
	}

	/**
	 * Returns Event timespan for popup.
	 *
	 * @return string
	 */
	public function getpopup_timespan() {
		return $this->_registry->get( 'twig.ai1ec-extension' )
			->timespan( $this, 'short' );
	}

	/**
	 * Returns Avatar not wrapped in <a> tag.
	 *
	 * @return string
	 */
	public function getavatar_not_wrapped() {
		 return $this->getavatar( false );
	}

	/**
	 * Returns enddate specific info.
	 *
	 * @return array Date info structure.
	 */
	public function getenddate_info() {
		return array(
			'month'     => $this->get( 'end' )->format( 'M' ),
			'day'       => $this->get( 'end' )->format( 'j' ),
			'weekday'   => $this->get( 'end' )->format( 'D' ),
			'year'      => $this->get( 'end' )->format( 'Y' ),
		);
	}

	/**
	 * Returns Event avatar URL.
	 *
	 * @return string Event avatar URL.
	 * @throws Ai1ec_Bootstrap_Exception
	 */
	public function getavatar_url() {
		return $this->_registry->get(
			'view.event.avatar'
		)->get_event_avatar_url( $this );
	}
}
