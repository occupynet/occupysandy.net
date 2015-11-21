<?php

/**
 * DBI utils.
 *
 * @author     Time.ly Network Inc.
 * @since      2.2
 *
 * @package    AI1EC
 * @subpackage AI1EC.Dbi
 */

class Ai1ec_Dbi_Utils extends Ai1ec_Base {

	/**
	 * Returns SQL string for INSERT statement.
	 *
	 * @param array $value Array of values.
	 *
	 * @return string SQL statement.
	 */
	public function array_value_to_sql_value( array $value ) {
		return '(' . implode( ',', array_values( $value ) ) . ')';
	}
}
