<?php

/**
 * This class renders the html for the event taxonomy.
 *
 * @author     Time.ly Network Inc.
 * @since      2.0
 *
 * @package    AI1EC
 * @subpackage AI1EC.View.Event
 */
class Ai1ec_View_Event_Taxonomy extends Ai1ec_Base {

	/**
	 * @var Ai1ec_Taxonomy Taxonomy abstraction layer.
	 */
	protected $_taxonomy_model = null;

	/**
	 * @var array Caches the color evaluated for each event.
	 */
	protected $_event_color_map = array();

	/**
	 * @var array Caches the color squares HTML evaluated for each event.
	 */
	protected $_event_color_squares_map = array();

	/**
	 * Returns style attribute for events rendered in Month, Week, or Day view.
	 *
	 * @param  Ai1ec_Event $event Event object.
	 *
	 * @return string             Color style attribute.
	 */
	public function get_color_style( Ai1ec_Event $event ) {
		$color = $this->get_color_for_event( $event );

		// Convert to style attribute.
		if ( $color ) {
			$color = $event->is_allday() || $event->is_multiday()
				? 'background-color: ' . $color . ';'
				: 'color: ' . $color . ' !important;';
		} else {
			$color = '';
		}

		return $color;
	}

	/**
	 * Returns HTML of category color swatches for this event.
	 *
	 * @param  Ai1ec_Event $event Event object.
	 *
	 * @return string             HTML of the event's category color swatches.
	 */
	public function get_category_colors( Ai1ec_Event $event ) {
		$post_id = $event->get( 'post_id' );

		if ( ! isset( $this->_event_color_squares_map[$post_id] ) ) {
			$squares = '';
			$categories = $this->_taxonomy_model->get_post_categories( $post_id );

			if ( false !== $categories ) {
				$squares = $this->get_event_category_colors( $categories );
			}

			// Allow add-ons to modify/add to category color swatch HTML.
			$squares = apply_filters(
				'ai1ec_event_color_squares',
				$squares,
				$event
			);

			$this->_event_color_squares_map[$post_id] = $squares;
		}

		return $this->_event_color_squares_map[$post_id];
	}

	/**
	 * Returns the HTML markup for the category color square.
	 *
	 * @param int $term_id The term ID of event category
	 *
	 * @return string
	 */
	public function get_category_color_square( $term_id ) {
		$color = $this->_taxonomy_model->get_category_color( $term_id );
		$event_taxonomy = $this->_registry->get( 'model.event.taxonomy' );
		if ( null !== $color ) {
			$taxonomy = $event_taxonomy->get_taxonomy_for_term_id( $term_id );
			$cat = get_term( $term_id, $taxonomy->taxonomy );
			return '<span class="ai1ec-color-swatch ai1ec-tooltip-trigger" ' .
				'style="background:' . $color . '" title="' .
				esc_attr( $cat->name ) . '"></span>';
		}
		return '';
	}

	/**
	 * Returns the HTML markup for the category image square.
	 *
	 * @param int $term_id The term ID of event category.
	 *
	 * @return string HTML snippet to use for category image.
	 */
	public function get_category_image_square( $term_id ) {
		$image = $this->_taxonomy_model->get_category_image( $term_id );
		if ( null !== $image ) {
			return '<img src="' . $image . '" alt="' .
				Ai1ec_I18n::__( 'Category image' ) .
				'" class="ai1ec_category_small_image_preview" />';
		}
		return '';
	}

	/**
	 * Returns category color squares for the list of Event Category objects.
	 *
	 * @param array $cats The Event Category objects as returned by get_terms()
	 *
	 * @return string
	 */
	public function get_event_category_colors( array $cats ) {
		$sqrs = '';
		foreach ( $cats as $cat ) {
			$tmp = $this->get_category_color_square( $cat->term_id );
			if ( ! empty( $tmp ) ) {
				$sqrs .= $tmp;
			}
		}
		return $sqrs;
	}

	/**
	 * Style attribute for event background color.
	 *
	 * @param  Ai1ec_Event $event Event object.
	 *
	 * @return string             Color to assign to event background.
	 */
	public function get_category_bg_color( Ai1ec_Event $event ) {
		$color = $this->get_color_for_event( $event );

		// Convert to HTML attribute.
		if ( $color ) {
			$color = 'style="background-color: ' . $color . ';"';
		} else {
			$color = '';
		}

		return $color;
	}

	/**
	 * Style attribute for event multi-date divider color.
	 *
	 * @param  Ai1ec_Event $event Event object.
	 *
	 * @return string Color to assign to event background.
	 */
	public function get_category_divider_color( Ai1ec_Event $event ) {
		$color = $this->get_color_for_event( $event );

		// Convert to HTML attribute.
		if ( $color ) {
			$color = 'style="border-color: ' . $color . ' transparent transparent transparent;"';
		} else {
			$color = '';
		}

		return $color;
	}

	/**
	 * Style attribute for event text color.
	 *
	 * @param  Ai1ec_Event $event Event object.
	 *
	 * @return string Color to assign to event text (foreground).
	 */
	public function get_category_text_color( Ai1ec_Event $event ) {
		$color = $this->get_color_for_event( $event );

		// Convert to HTML attribute.
		if ( $color ) {
			$color = 'style="color: ' . $color . ';"';
		} else {
			$color = '';
		}

		return $color;
	}

	/**
	 * Caches color for event having the given post ID.
	 *
	 * @param  int    $post_id Event's post ID.
	 *
	 * @return string Color associated with event.
	 */
	public function get_color_for_event( $event ) {
		$post_id = $event->get( 'post_id' );

		// If color for this event is uncached, populate cache.
		if ( ! isset( $this->_event_color_map[$post_id] ) ) {
			// Find out if an add-on has provided its own color for the event.
			$color = apply_filters( 'ai1ec_event_color', '', $event );

			// If none provided, fall back to event categories.
			if ( empty( $color ) ) {
				$categories = $this->_taxonomy_model->get_post_categories( $post_id );
				// Find the first category of this post that defines a color.
				foreach ( $categories as $category ) {
					$color = $this->_taxonomy_model->get_category_color(
						$category->term_id
					);
					if ( $color ) {
						break;
					}
				}
			}
			$this->_event_color_map[$post_id] = $color;
		}

		return $this->_event_color_map[$post_id];
	}

	/**
	 * Categories as HTML, either as blocks or inline.
	 *
	 * @param Ai1ec_Event $event  Rendered Event.
	 * @param string      $format Return 'blocks' or 'inline' formatted result.
	 *
	 * @return string String of HTML for category blocks.
	 */
	public function get_categories_html(
		Ai1ec_Event $event,
		$format = 'blocks'
	) {
		$categories = $this->_taxonomy_model->get_post_categories(
			$event->get( 'post_id' )
		);
		foreach ( $categories as &$category ) {
			$href = $this->_registry->get(
				'html.element.href',
				array( 'cat_ids' => $category->term_id )
			);

			$class = $data_type = $title = '';
			if ( $category->description ) {
				$title = 'title="' .
					esc_attr( $category->description ) . '" ';
			}

			$html        = '';
			$class      .= ' ai1ec-category';
			$color_style = '';
			if ( $format === 'inline' ) {
				$taxonomy = $this->_registry->get( 'model.taxonomy' );
				$color_style = $taxonomy->get_category_color(
					$category->term_id
				);
				if ( $color_style !== '' ) {
					$color_style = 'style="color: ' . $color_style . ';" ';
				}
				$class .= '-inline';
			}

			$html .= '<a ' . $data_type . ' class="' . $class .
			' ai1ec-term-id-' . $category->term_id . ' p-category" ' .
			$title . $color_style . 'href="' . $href->generate_href() . '">';

			if ( $format === 'blocks' ) {
				$html .= $this->get_category_color_square(
					$category->term_id
				) . ' ';
			} else {
				$html .=
				'<i ' . $color_style .
					'class="ai1ec-fa ai1ec-fa-folder-open"></i>';
			}

			$html .= esc_html( $category->name ) . '</a>';
			$category = $html;
		}
		return implode( ' ', $categories );
	}

	/**
	 * Tags as HTML
	 */
	public function get_tags_html( Ai1ec_Event $event ) {
		$tags = $this->_taxonomy_model->get_post_tags(
			$event->get( 'post_id' )
		);
		if ( ! $tags ) {
			$tags = array();
		}
		foreach ( $tags as &$tag ) {
			$href = $this->_registry->get(
				'html.element.href',
				array( 'tag_ids' => $tag->term_id )
			);
			$class = '';
			$data_type = '';
			$title = '';
			if ( $tag->description ) {
				$title = 'title="' . esc_attr( $tag->description ) . '" ';
			}
			$tag = '<a ' . $data_type . ' class="ai1ec-tag ' . $class .
				' ai1ec-term-id-' . $tag->term_id . '" ' . $title .
				'href="' . $href->generate_href() . '">' .
				'<i class="ai1ec-fa ai1ec-fa-tag"></i>' .
				esc_html( $tag->name ) . '</a>';
		}
		return implode( ' ', $tags );
	}

	public function __construct( Ai1ec_Registry_Object $registry ) {
		parent::__construct( $registry );
		$this->_taxonomy_model = $this->_registry->get( 'model.taxonomy' );
	}

}
