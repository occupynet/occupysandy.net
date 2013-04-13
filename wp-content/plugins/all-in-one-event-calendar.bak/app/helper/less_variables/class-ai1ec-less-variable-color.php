<?php
/**
 *
 * @author Timely Network Inc
 *
 * This class represents a LESS variable of type color. It supports hex, rgb
 * and rgba formats.
 */

class Ai1ec_Less_Variable_Color extends Ai1ec_Less_Variable {

	/**
	 * (non-PHPdoc)
	 * Set up the color picker
	 * @see Ai1ec_Less_Variable::set_up_renderable()
	 */
	public function set_up_renderable( Ai1ec_Renderable $renderable ) {
		$renderable->set_label( $this->description );
		if( substr($this->value, 0, 3) === 'rgb' ) {
			if( substr($this->value, 0, 4) === 'rgba' ) {
				$renderable->set_format( 'rgba' );
			} else {
				$renderable->set_format( 'rgb' );
			}
		}
		return $renderable;
	}
}
