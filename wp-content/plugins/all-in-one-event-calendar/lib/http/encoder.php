<?php

/**
 * Calendar Http_Encoder wrapper.
 *
 * @author     Time.ly Network Inc.
 * @since      2.2
 *
 * @package    AI1EC
 * @subpackage AI1EC.Lib
 */

class Ai1ec_HTTP_Encoder extends HTTP_Encoder {

	/**
	 * Overrides parent function and removed Content-Length header to avoid
	 * some problems if our JavaScript is somehow prepended by 3rd party code.
	 *
	 * @return void Method does not return.
	 */
	public function sendHeaders() {
		unset( $this->_headers['Content-Length'] );
		parent::sendHeaders();
	}
}