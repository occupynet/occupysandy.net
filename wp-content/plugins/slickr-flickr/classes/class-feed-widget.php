<?php
if (!class_exists('Slickr_Flickr_Feed_Widget')) {
 class Slickr_Flickr_Feed_Widget extends WP_Widget_RSS {

	function __construct() {
		$widget_ops = array( 'description' => __('Displays Featured image in place of title in any RSS or Atom feed.') );
		$control_ops = array( 'width' => 400, 'height' => 200 );
		parent::__construct( 'slickr-flickr-feed', __('Slickr Flickr Feed'), $widget_ops, $control_ops );
	}

	function widget($args, $instance) {

		if ( isset($instance['error']) && $instance['error'] )
			return;

		$title = $instance['title'];
		$desc = '';
		$link = '';

		/** This filter is documented in wp-includes/default-widgets.php */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		echo $args['before_widget'];
		
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		self::rss_output($instance );
		
		echo $args['after_widget'];
	}

	static function rss_output( $instance ) {

		$url = ! empty( $instance['url'] ) ? $instance['url'] : '';

		while ( stristr($url, 'http') != $url )
			$url = substr($url, 1);

		if ( empty($url) )
			return;

		if ( in_array( untrailingslashit( $url ), array( site_url(), home_url() ) ) )
			return;

		$rss = fetch_feed($url);

		if ( is_wp_error($rss) ) {
			if ( is_admin() || current_user_can('manage_options') ) 
				echo '<div>' . sprintf( __('<strong>RSS Error</strong>: %s'), $rss->get_error_message() ) . '</div>';
			return;
		}

		$default_args = array( 'show_featured' => 0, 'show_author' => 0, 'show_date' => 0, 'show_summary' => 0, 'items' => 0 );

		if (($parsed_url = parse_url($url))
 		&& ($query = isset($parsed_url['query']) ? $parsed_url['query'] : '')) {
 			 $instance = wp_parse_args($query, $default_args);
		}

		$args = wp_parse_args( $instance, $default_args );

		$items = (int) $args['items'];
		if ( $items < 1 || 20 < $items ) $items = 10;

		$show_featured  = (int) $args['show_featured'];
		$show_summary  = (int) $args['show_summary'];
		$show_author   = (int) $args['show_author'];
		$show_date     = (int) $args['show_date'];
	
		if ( !$rss->get_item_quantity() ) {
			echo '<div>' . __( 'An error has occurred, which probably means the feed is down. Try again later.' ) . '</div>';
			return;
		}

		foreach ( $rss->get_items( 0, $items ) as $item ) {
			$link = $item->get_link();
			while ( stristr( $link, 'http' ) != $link ) {
				$link = substr( $link, 1 );
			}
			$link = esc_url( strip_tags( $link ) );
			$title = esc_html( trim( strip_tags( $item->get_title() ) ) );
			$link_title = '';		
			$desc = @html_entity_decode( $item->get_description(), ENT_QUOTES, get_option( 'blog_charset' ) );
	
			if (substr($desc,0,5) == '<img ') { //use image in place of title if supplied
				$end_image = strpos($desc,'>');
				$link_title = sprintf(' title="%1$s"', $title);
				$title = substr($desc,0, $end_image+1);
				$desc = substr($desc, $end_image+1);
			} else {
				if ($show_featured)
					continue; //skip items with missing featured images
			} 
	
			$desc = esc_attr( wp_trim_words( $desc, 55, ' [&hellip;]' ) );

			$summary = '';
			if ( $show_summary ) {
				$summary = $desc;

				// Change existing [...] to [&hellip;].
				if ( '[...]' == substr( $summary, -5 ) ) {
					$summary = substr( $summary, 0, -5 ) . '[&hellip;]';
				}

				$summary = '<div class="rssSummary">' . esc_html( $summary ) . '</div>';
			}

			$date = '';
			if ( $show_date ) {
				$date = $item->get_date( 'U' );

				if ( $date ) {
					$date = ' <span class="rss-date">' . date_i18n( get_option( 'date_format' ), $date ) . '</span>';
				}
			}

			$author = '';
			if ( $show_author ) {
				$author = $item->get_author();
				if ( is_object($author) ) {
					$author = $author->get_name();
					$author = ' <cite>' . esc_html( strip_tags( $author ) ) . '</cite>';
				}
			}

			if ($link) $title = sprintf('<a target="_blank" class="rsswidget" href="%1$s"%2$s>%3$s</a>', $link, $link_title, $title);
			printf('<span class="diy-image-feed-widget-item"><span>%1$s</span>%2$s%3$s%4$s</span>', $title, $date, $summary, $author );
		}

		if ( ! is_wp_error($rss) )
			$rss->__destruct();
		unset($rss);		
	}

	static function display_feeds($feeds = false) {
		if (is_array($feeds) && (count($feeds) > 0)) {
			echo '<div class="diy-image-feed-widget">';
			foreach( $feeds as $url ) {
				$args = array('url' => $url, 'show_summary' => true, 'show_featured' => true);
				self::rss_output( $args );
			}
			echo "</div>";
		}
	}

  }
}
