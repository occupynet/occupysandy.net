<?php
//
//  class-ai1ec-arguments-parser.php
//  all-in-one-event-calendar
//
//  Created by Timely Network Inc
//

/**
 * Ai1ec_Arguments_Parser class
 *
 * @package Models
 * @author  Timely Network Inc
 **/
class Ai1ec_Arguments_Parser extends Ai1ec_Abstract_Query {

	/**
	 * Initiate default filters for arguments parser
	 */
	public function __construct(
		array $argv     = null,
		$default_action = null
	) {
		parent::__construct( $argv );
		$action_list = array(
			'posterboard',
			'stream',
			'month',
			'oneday',
			'week',
			'agenda',
		);
		foreach ( $action_list as $action ) {
			$action_list[] = 'ai1ec_' . $action;
		}
		if ( ! in_array( $default_action, $action_list ) ) {
			$default_action = current( $action_list );
		}
		$this->add_rule(
			'action',
			false,
			'string',
			$default_action,
			$action_list
		);
		$this->add_rule( 'page_offset',   false, 'int', 0,    false );
		$this->add_rule( 'month_offset',  false, 'int', 0,    false );
		$this->add_rule( 'oneday_offset', false, 'int', 0,    false );
		$this->add_rule( 'week_offset',   false, 'int', 0,    false );
		$this->add_rule( 'time_limit',    false, 'int', 0,    false );
		$this->add_rule( 'cat_ids',       false, 'int', null, ',' );
		$this->add_rule( 'tag_ids',       false, 'int', null, ',' );
		$this->add_rule( 'post_ids',      false, 'int', null, ',' );
		$this->add_rule( 'term_ids',      false, 'int', null, ',' );
		$this->add_rule( 'exact_date',    false, 'string', null, false );
		// This is the type of the request: Standard, json or jsonp
		$this->add_rule( 'request_type',  false, 'string', 'standard', false );
		// This is the format of the request. For now it's html but if we implement templates it could be json
		$this->add_rule( 'request_format',false, 'string', 'html', false );
		// The callback function for jsonp calls
		$this->add_rule( 'callback'      ,false, 'string', false, false );
		// Whether to include navigation controls
		$this->add_rule( 'no_navigation' ,false, 'string', false, false );
		$this->add_rule( 'applying_filters' ,false, 'string', false, false );
		$this->add_rule( 'shortcode' ,false, 'string', false, false );
	}

	/**
	 * Get query argument name prefix.
	 *
	 * Inherited from parent class. Method is used to detect query name
	 * prefix, that is used to "namespace" own (private) query variables.
	 *
	 * @return string Query prefix 'ai1ec_'
	 */
	protected function _get_prefix() {
		return 'ai1ec_';
	}

}
