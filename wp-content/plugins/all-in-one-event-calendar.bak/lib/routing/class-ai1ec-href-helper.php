<?php

/**
 * @author Timely Network Inc
 */

class Ai1ec_Href_Helper {
	/**
	 * @var array the parameters that are used in the urls
	 */
	private $used_paramaters = array(
			'page_offset',
			'month_offset',
			'action',
			'time_limit',
			'exact_date',
			'cat_ids',
			'post_ids',
			'tag_ids',
			'oneday_offset',
			'week_offset',
	);

	/**
	 * @var boolean
	 */
	private $is_category;

	/**
	 * @var boolean
	 */
	private $is_tag;

	/**
	 * @var array the arguments to parse
	 */
	private $args;

	/**
	 * @var int
	 */
	private $term_id;

	/**
	 * @var string
	 */
	private $calendar_page;

	/**
	 * @var boolean
	 */
	private $pretty_permalinks_enabled;

	/**
	 * @param boolean $pretty_permalinks_enabled
	 */
	public function set_pretty_permalinks_enabled( $pretty_permalinks_enabled ) {
		$this->pretty_permalinks_enabled = $pretty_permalinks_enabled;
	}

	/**
	 * @param number $term_id
	 */
	public function set_term_id( $term_id ) {
		$this->term_id = $term_id;
	}

	public function __construct( array $args, $calendar ) {
		$this->args = $args;
		$this->calendar_page = $calendar;
	}
	/**
	 * @param boolean $is_category
	 */
	public function set_is_category( $is_category ) {
		$this->is_category = $is_category;
	}

	/**
	 * @param boolean $is_tag
	 */
	public function set_is_tag( $is_tag ) {
		$this->is_tag = $is_tag;
	}

	/**
	 * Generate the correct href for the view.
	 * This takes into account special filters for categories and tags
	 *
	 * @return string
	 */
	public function generate_href() {
		$href = '';
		$to_implode = array();
		foreach ( $this->used_paramaters as $key ) {
			if( ! empty( $this->args[$key] ) ) {
				$value = $this->args[$key];
				if( is_array( $this->args[$key] ) ) {
					$value = implode( ',', $this->args[$key] );
				}
				$to_implode[$key] = "$key:$value";
			}
		}
		if( $this->is_category || $this->is_tag ) {
			$to_implode = $this->add_or_remove_category_from_href( $to_implode, $this->is_category );
		}
		if( $this->pretty_permalinks_enabled ) {
			$href .= implode( '/', $to_implode );
			if( ! empty( $href ) ) {
				$href .=  '/';
			}
		} else {
			$href .= self::get_param_delimiter_char( $this->calendar_page );
			$href .= 'ai1ec=' . implode( '|', $to_implode );
		}
		$full_url = $this->calendar_page . $href;
		// persiste the lang parameter if present
		if( isset( $_REQUEST['lang'] ) ) {
			$lang = 'lang=' . $_REQUEST['lang'];
			$separator = ( false === strpos(  $full_url, '?') ) ? '?' : '&';
			$full_url .= $separator . $lang;
		}
		return $full_url;
	}

	/**
	 * Perform some extra manipulation for filter href. Basically if the current
	 * category is part of the filter, the href will not contain it (because
	 * clicking on it will actually mean "remove that one from the filter")
	 * otherwise it will be preserved.
	 *
	 * @param array $to_implode
	 * @param boolean $is_category
	 * @return array
	 */
	private function add_or_remove_category_from_href(
		array $to_implode,
		$is_category
	) {
		$array_key = $is_category ? 'cat_ids' : 'tag_ids';
		// Let's copy the origina cat_ids or tag_ids so we do not affect it
		$copy = $this->args[$array_key];
		$key = array_search( $this->term_id, $copy );
		// Let's check if we are already filtering for tags / categorys
		if( isset( $to_implode[$array_key] ) ) {
			if( $key !== false ) {
				unset( $copy[$key] );
			} else {
				$copy[] = $this->term_id;
			}
			if( empty( $copy ) ) {
				unset( $to_implode[$array_key] );
			} else {
				$to_implode[$array_key] = "$array_key:" . implode( ',', $copy );
			}
		} else {
			$to_implode[$array_key] = "$array_key:" . $this->term_id;
		}
		return $to_implode;
	}

	/**
	 * Returns the delimiter character to use if a new query string parameter is
	 * going to be appended to the URL.
	 *
	 * @param string $url URL to parse
	 *
	 * @return string
	 */
	public static function get_param_delimiter_char( $url ) {
		return strpos( $url, '?' ) === false ? '?' : '&';
	}
}
