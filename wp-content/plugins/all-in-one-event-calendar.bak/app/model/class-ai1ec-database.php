<?php
//
//  class-ai1ec-database.php
//  all-in-one-event-calendar
//

/**
 * Ai1ec_Database class
 *
 * Class responsible for generic database operations
 *
 * @package Models
 * @author  Timely Network Inc
 **/
class Ai1ec_Database
{

	/**
	 * @staticvar Ai1ec_Database Singletonian instance of self
	 */
	static protected $_instance = NULL;

	/**
	 * @var array Map of tables and their parsed definitions
	 */
	protected $_schema_delta = array();

	/**
	 * instance method
	 *
	 * Get singleton instance of self (Ai1ec_Database).
	 *
	 * @return Ai1ec_Database Initialized instance of self
	 */
	static public function instance() {
		if ( ! ( self::$_instance instanceof Ai1ec_Database ) ) {
			self::$_instance = new Ai1ec_Database();
		}
		return self::$_instance;
	}

	/**
	 * apply_delta method
	 *
	 * Attempt to parse and apply given database tables definition, as a delta.
	 * Some validation is made prior to calling DB, and fields/indexes are also
	 * checked for consistency after sending queries to DB.
	 *
	 * NOTICE: only "CREATE TABLE" statements are handled. Others will, likely,
	 * be ignored, if passed through this method.
	 *
	 * @param string|array $query Single or multiple queries to perform on DB
	 *
	 * @return bool Success
	 *
	 * @throws Ai1ec_Database_Error In case of any error
	 */
	public function apply_delta( $query ) {
		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin' . DIRECTORY_SEPARATOR .
				'includes' . DIRECTORY_SEPARATOR . 'upgrade.php';
		}
		$success = false;
		try {
			$this->_schema_delta = array();
			$queries = $this->_prepare_delta( $query );
			$result  = dbDelta( $queries );
			$success = $this->_check_delta();
		} catch ( Ai1ec_Database_Error $failure ) {
			$message = Ai1ec_Helper_Factory::create_admin_message_instance(
				'<p>' . __(
					'Database update has failed. Please make sure, that database user, defined in <em>wp-config.php</em> has permissions, to make changes (<strong>ALTER TABLE</strong>) to the database.',
					AI1EC_PLUGIN_NAME
				) . '</p>',
				__(
					'Plug-in disabled due to unrecoverable database update error',
					AI1EC_PLUGIN_NAME
				)
			);
			$this->get_notices_helper()->add_renderable_children( $message );
			if ( ! function_exists( 'deactivate_plugins' ) ) {
				require ABSPATH . 'wp-admin/includes/plugin.php';
			}
			deactivate_plugins( AI1EC_PLUGIN_BASENAME, true );
		}
		return $success;
	}

	/**
	 * get_notices_helper method
	 *
	 * DIP implementing method, to give access to Ai1ec_Admin_Notices_Helper.
	 *
	 * @param Ai1ec_Admin_Notices_Helper $replacement Notices implementor
	 *
	 * @return Ai1ec_Admin_Notices_Helper Instance of notices implementor
	 */
	public function get_notices_helper(
		Ai1ec_Admin_Notices_Helper $replacement = NULL
	) {
		static $helper = NULL;
		if ( NULL !== $replacement ) {
			$helper = $replacement;
		}
		if ( NULL === $helper ) {
			$helper = Ai1ec_Admin_Notices_Helper::get_instance();
		}
		return $helper;
	}

	/**
	 * _prepare_delta method
	 *
	 * Prepare statements for execution.
	 * Attempt to parse various SQL definitions and compose the one, that is
	 * most likely to be accepted by delta engine.
	 *
	 * @param string|array $queries Single or multiple queries to perform on DB
	 *
	 * @return bool Success
	 *
	 * @throws Ai1ec_Database_Error In case of any error
	 */
	protected function _prepare_delta( $queries ) {
		if ( ! is_array( $queries ) ) {
			$queries = explode( ';', $queries );
			$queries = array_filter( $queries );
		}
		$current_table = NULL;
		$ctable_regexp = '#
			\s*CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?([^ ]+)`?\s*
			\((.+)\)
			([^()]*)
			#six';
		foreach ( $queries as $query ) {
			if ( preg_match( $ctable_regexp, $query, $matches ) ) {
				$this->_schema_delta[$matches[1]] = array(
					'tblname' => $matches[1],
					'cryptic'  => NULL,
					'creator'  => '',
					'columns' => array(),
					'indexes' => array(),
					'content' => preg_replace( '#`#', '', $matches[2] ),
					'clauses' => $matches[3],
				);
			}
		}
		$this->_parse_delta();
		$sane_queries = array();
		foreach ( $this->_schema_delta as $table => $definition ) {
			$create = 'CREATE TABLE ' . $table . " (\n";
			foreach ( $definition['columns'] as $column ) {
				$create .= '    ' . $column['create'] . ",\n";
			}
			foreach ( $definition['indexes'] as $index ) {
				$create .= '    ' . $index['create'] . ",\n";
			}
			$create = substr( $create, 0, -2 ) . "\n";
			$create .= ')' . $definition['clauses'];
			$this->_schema_delta[$table]['creator'] = $create;
			$this->_schema_delta[$table]['cryptic'] = md5( $create );
			$sane_queries[] = $create;
		}
		return $sane_queries;
	}

	/**
	 * _parse_delta method
	 *
	 * Parse table application (creation) statements into atomical particles.
	 * Here "atomical particles" stands for either columns, or indexes.
	 *
	 * @return void Method does not return
	 *
	 * @throws Ai1ec_Database_Error In case of any error
	 */
	protected function _parse_delta() {
		foreach ( $this->_schema_delta as $table => $definitions ) {
			$listing = explode( "\n", $definitions['content'] );
			$listing = array_filter( $listing, array( $this, '_is_not_empty_line' ) );
			$lines   = count( $listing );
			$lineno  = 0;
			foreach ( $listing as $line ) {
				++$lineno;
				$line = trim( preg_replace( '#\s+#', ' ', $line ) );
				$line_new = rtrim( $line, ',' );
				if (
					$lineno < $lines && $line === $line_new ||
					$lineno == $lines && $line !== $line_new
				) {
					throw new Ai1ec_Database_Error(
						'Missing comma in line \'' . $line . '\''
					);
				}
				$line = $line_new;
				unset( $line_new );
				$type = 'indexes';
				if ( false === ( $record = $this->_parse_index( $line ) ) ) {
					$type   = 'columns';
					$record = $this->_parse_column( $line );
				}
				if ( isset(
						$this->_schema_delta[$table][$type][$record['name']]
				) ) {
					throw new Ai1ec_Database_Error(
						'For table `' . $table . '` entry ' . $type .
						' named `' . $record['name'] . '` was declared twice' .
						' in ' . $definitions
					);
				}
				$this->_schema_delta[$table][$type][$record['name']] = $record;
			}
		}
	}

	/**
	 * _parse_index method
	 *
	 * Given string attempts to detect, if it is an index, and if yes - parse
	 * it to more navigable index definition for future validations.
	 * Creates modified index create line, for delta application.
	 *
	 * @param string $description Single "line" of CREATE TABLE statement body
	 *
	 * @return array|bool Index definition, or false if input does not look like index
	 *
	 * @throws Ai1ec_Database_Error In case of any error
	 */
	protected function _parse_index( $description ) {
		$description = preg_replace(
			'#^CONSTRAINT(\s+`?[^ ]+`?)?\s+#six',
			'',
			$description
		);
		$details     = explode( ' ', $description );
		$index       = array(
			'name'    => NULL,
			'content' => array(),
			'create'  => '',
		);
		$details[0]  = strtoupper( $details[0] );
		switch ( $details[0] ) {
			case 'PRIMARY':
				$index['name']   = 'PRIMARY';
				$index['create'] = 'PRIMARY KEY ';
				break;

			case 'UNIQUE':
				$name = $details[1];
				if (
					0 === strcasecmp( 'KEY',   $name ) ||
					0 === strcasecmp( 'INDEX', $name )
				) {
					$name = $details[2];
				}
				$index['name']   = $name;
				$index['create'] = 'UNIQUE KEY ' . $name;
				break;

			case 'KEY':
			case 'INDEX':
				$index['name']   = $details[1];
				$index['create'] = 'KEY ' . $index['name'];
				break;

			default:
				return false;
		}
		$index['content'] = $this->_parse_index_content( $description );
		$index['create'] .= ' (';
		foreach ( $index['content'] as $column => $length ) {
			$index['create'] .= $column;
			if ( NULL !== $length ) {
				$index['create'] .= '(' . $length . ')';
			}
			$index['create'] .= ',';
		}
		$index['create'] = substr( $index['create'], 0, -1 );
		$index['create'] .= ')';
		return $index;
	}

	/**
	 * _parse_column method
	 *
	 * Parse column to parseable definition.
	 * Some valid definitions may still be not recognizes (namely SET and ENUM)
	 * thus one shall beware, when attempting to create such.
	 * Create alternative create table entry line for delta application.
	 *
	 * @param string $description Single "line" of CREATE TABLE statement body
	 *
	 * @return array Column definition
	 *
	 * @throws Ai1ec_Database_Error In case of any error
	 */
	protected function _parse_column( $description ) {
		$column_regexp = '#^
			([a-z][a-z_]+)\s+
			(
				[A-Z]+
				(?:\s*\(\s*\d+(?:\s*,\s*\d+\s*)?\s*\))?
				(?:\s+UNSIGNED)?
				(?:\s+ZEROFILL)?
				(?:\s+BINARY)?
				(?:
					\s+CHARACTER\s+SET\s+[a-z][a-z_]+
					(?:\s+COLLATE\s+[a-z][a-z0-9_]+)?
				)?
			)
			(
				\s+(?:NOT\s+)?NULL
			)?
			(
				\s+DEFAULT\s+[^\s]+
			)?
			(\s+ON\s+UPDATE\s+CURRENT_(?:TIMESTAMP|DATE))?
			(\s+AUTO_INCREMENT)?
			\s*,?\s*
		$#six';
		if ( ! preg_match( $column_regexp, $description, $matches ) ) {
			throw new Ai1ec_Database_Error(
				'Invalid column description ' . $description
			);
		}
		$column = array(
			'name'    => $matches[1],
			'content' => array(),
			'create'  => '',
		);
		if ( 0 === strcasecmp( 'boolean', $matches[2] ) ) {
			$matches[2] = 'tinyint(1)';
		}
		$column['content']['type'] = $matches[2];
		$column['content']['null'] = (
			! isset( $matches[3] ) ||
			0 !== strcasecmp( 'NOT NULL', trim( $matches[3] ) )
		);
		$column['create'] = $column['name'] . ' ' . $column['content']['type'];
		if ( isset( $matches[3] ) ) {
			$column['create'] .= ' ' .
				implode(
					' ',
					array_map(
						'trim',
						array_slice( $matches, 3 )
					)
				);
		}
		return $column;
	}

	/**
	 * _parse_index_content method
	 *
	 * Parse index content, to a map of columns and their length.
	 * All index (content) cases shall be covered, although it is only tested.
	 *
	 * @param string Single line of CREATE TABLE statement, containing index definition
	 *
	 * @return array Map of columns and their length, as per index definition
	 *
	 * @throws Ai1ec_Database_Error In case of any error
	 */
	protected function _parse_index_content( $description ) {
		if ( ! preg_match( '#^[^(]+\((.+)\)$#', $description, $matches ) ) {
			throw new Ai1ec_Database_Error(
				'Invalid index description ' . $description
			);
		}
		$columns = array();
		$textual = explode( ',', $matches[1] );
		$column_regexp = '#\s*([^(]+)(?:\s*\(\s*(\d+)\s*\))?\s*#sx';
		foreach ( $textual as $column ) {
			if (
				! preg_match( $column_regexp, $column, $matches ) || (
					  isset( $matches[2] ) &&
					  (string)$matches[2] !== (string)intval( $matches[2] )
				)
			) {
				throw new Ai1ec_Database_Error(
					'Invalid index (columns) description ' . $description .
					' as per \'' . $column . '\''
				);
			}
			$matches[1] = trim( $matches[1] );
			$columns[$matches[1]] = NULL;
			if ( isset( $matches[2] ) ) {
				$columns[$matches[1]] = (int)$matches[2];
			}
		}
		return $columns;
	}

	/**
	 * _check_delta method
	 *
	 * Given parsed schema definitions (in {@see self::$_schema_delta} map) this
	 * method performs checks, to ensure that table exists, columns are of
	 * expected type, and indexes match their definition in original query.
	 *
	 * @return bool Success
	 *
	 * @throws Ai1ec_Database_Error In case of any error
	 */
	protected function _check_delta() {
		global $wpdb;
		if ( empty( $this->_schema_delta ) ) {
			return true;
		}
		foreach ( $this->_schema_delta as $table => $description ) {

			$columns = $wpdb->get_results( 'SHOW FULL COLUMNS FROM ' . $table );
			if ( empty( $columns ) ) {
				throw new Ai1ec_Database_Error(
					'Required table `' . $table . '` was not created'
				);
			}
			$db_column_names = array();
			foreach ( $columns as $column ) {
				if ( ! isset( $description['columns'][$column->Field] ) ) {
					trigger_error(
						'Unknown column `' . $column->Field .
						'` is present in table `' . $table . '`'
					);
					continue; // TODO: this is ignored, so far
					throw new Ai1ec_Database_Error(
						'Unknown column `' . $column->Field .
						'` is present in table `' . $table . '`'
					);
				}
				$db_column_names[$column->Field] = $column->Field;
				$type_db = $column->Type;
				$collation = '';
				if ( $column->Collation ) {
					$collation = ' CHARACTER SET ' .
						substr(
							$column->Collation,
							0,
							strpos( $column->Collation, '_' )
						) . ' COLLATE ' . $column->Collation;
				}
				$type_req = $description['columns'][$column->Field]
					['content']['type'];
				if (
					false !== stripos(
						$type_req,
						' COLLATE '
					)
				) {
					// suspend collation checking
					//$type_db .= $collation;
					$type_req = preg_replace(
						'#^
							(.+)
							\s+CHARACTER\s+SET\s+[a-z0-9_]+
							\s+COLLATE\s+[a-z0-9_]+
							(.+)?\s*
						$#six',
						'$1$2',
						$type_req
					);
				}
				$type_db  = strtolower(
					preg_replace( '#\s+#', '', $type_db )
				);
				$type_req = strtolower(
					preg_replace( '#\s+#', '', $type_req )
				);
				if ( 0 !== strcmp( $type_db, $type_req ) ) {
					throw new Ai1ec_Database_Error(
						'Field `' . $table . '`.`' . $column->Field .
						'` is of incompatible type'
					);
				}
				if (
					'YES' === $column->Null &&
					false === $description['columns'][$column->Field]
						['content']['null'] ||
					'NO' === $column->Null &&
					true === $description['columns'][$column->Field]
						['content']['null']
				) {
					throw new Ai1ec_Database_Error(
						'Field `' . $table . '`.`' . $column->Field .
						'` NULLability is flipped'
					);
				}
			}
			if (
				$missing = array_diff(
					array_keys( $description['columns'] ),
					$db_column_names
				)
			) {
					throw new Ai1ec_Database_Error(
						'In table `' . $table . '` fields are missing: ' .
						implode( ', ', $missing )
					);
			}

			$index_list = $wpdb->get_results( 'SHOW INDEXES FROM ' . $table );
			$indexes = array();
			foreach ( $index_list as $index_def ) {
				$name = $index_def->Key_name;
				if ( ! isset( $indexes[$name] ) ) {
					$indexes[$name] = array(
						'columns' => array(),
						'unique'  => ( 0 !== $index_def->Non_unique ),
					);
				}
				$indexes[$name]['columns'][$index_def->Column_name] =
					$index_def->Sub_part;
			}

			foreach ( $indexes as $name => $definition ) {
				if ( ! isset( $description['indexes'][$name] ) ) {
					throw new Ai1ec_Database_Error(
						'Unknown index `' . $name .
						'` is defined for table `' . $table . '`'
					);
				}
				if (
					$missed = array_diff_assoc(
						$description['indexes'][$name]['content'],
						$definition['columns']
					)
				) {
					throw new Ai1ec_Database_Error(
						'Index `' . $name .
						'` definition for table `' . $table . '` has invalid ' .
						' fields: ' . implode( ', ', array_keys( $missed ) )
					);
				}
			}

			if (
				$missing = array_diff(
					array_keys( $description['indexes'] ),
					array_keys( $indexes )
				)
			) {
					throw new Ai1ec_Database_Error(
						'In table `' . $table . '` indexes are missing: ' .
						implode( ', ', $missing )
					);
			}

		}
		return true;
	}

	/**
	 * _is_not_empty_line method
	 *
	 * Helper method, to check that any given line is not empty.
	 * Aids array_filter in detecting empty SQL query lines.
	 *
	 * @param string $line Single line of DB query statement
	 *
	 * @return bool True if line is not empty, false otherwise
	 */
	protected function _is_not_empty_line( $line ) {
		$line = trim( $line );
		return ! empty( $line );
	}

}
