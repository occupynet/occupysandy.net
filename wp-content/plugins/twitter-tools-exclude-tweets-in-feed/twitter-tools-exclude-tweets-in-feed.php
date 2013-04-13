<?php
/*
Plugin Name: Twitter Tools - Exclude Tweets in Feed
Plugin URI: http://www.feedmeastraycat.net/projects/random-wordpress-plugins/twitter-tools-exclude-in-feed/
Description: Small extension for the Twitter Tools plugin to exlude imported tweets in the feed.
Version: 0.1.0
Author: David M&aring;rtensson
Author URI: http://www.feedmeastraycat.net/
*/


/**
 * When the feed is fetched, add query vars to ignore the twitter tools twitter blog post categories.
 * @param WP_Query $wp_query
 */
function aktt_excltwinfeed__pre_get_posts($wp_query) {
	if ($wp_query->is_feed) {
		$wp_query->query_vars['category__not_in'][] = get_option('aktt_blog_post_category');
	}
}
add_action('pre_get_posts', 'aktt_excltwinfeed__pre_get_posts');


?>