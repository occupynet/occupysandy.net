<?php
/**
 * Plugin Name: Social Media Mashup
 * Plugin URI: http://bravenewmedia.net/wordpress-plugins/social-media-mashup/
 * Description: Combine your Twitter, Facebook, Google+, Flickr, YouTube, and any RSS feeds into one stream.
 * Version: 1.1.1
 * Author: Brave New Media, Inc.
 * Author URI: http://bravenewmedia.net/
 * License: GPLv3
 * 
 * Copyright 2011  Brave New Media (email : web@bravenewmedia.net)
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 3, as 
 * published by the Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/* Constants
========================================================*/

if ( ! defined( 'BNM_LOCALE' ) )
	define( 'BNM_LOCALE', '' );

if ( ! defined( 'SMM_URL' ) )
	define( 'SMM_URL', plugins_url( null, __FILE__ ) );

if ( ! defined( 'SMM_DIR' ) )
	define( 'SMM_DIR', WP_PLUGIN_DIR . '/' . basename( dirname( __FILE__ ) ) );
	
/* Make it go
========================================================*/

function social_media_mashup( $count = null, $echo = true ) {
	
	$output = "\n<!-- Social Media Mashup plugin by Brave New Media -->\n";
	
	$output .= '<div class="social-media-mashup icons-' . smm_option( 'show_icons' ) . '">' . "\n";
	
	if ( ! $count )
		$count = 5;
	
	// The $feeds array is what we pass to SimplePie - an array of feed URLs
	$feeds = array();
	$feed_keys = array( 'twitter', 'facebook', 'google', 'flickr', 'youtube', 'rss1', 'rss2', 'rss3' );
	
	// Check which feeds are specified
	foreach ( $feed_keys as $key ) :
		
		if ( smm_option( $key ) != '' )
			$feeds[] = smm_feed_url( $key );
		
	endforeach;
	
	// Include this site's posts if option is set in the admin
	if ( smm_option( 'blog' ) )
		$feeds[] = get_bloginfo( 'rss2_url' );
	
	// SimplePie magic starts here.
	if ( ! class_exists( 'SimplePie' ) )
		require_once( SMM_DIR . '/simplepie.inc' );
	
	$feed = new SimplePie();
	$feed->set_feed_url( $feeds );
	
	// Make sure feeds are getting local timestamps
	if ( get_option( 'timezone_string' ) && strpos( get_option( 'timezone_string' ), 'UTC' ) === false )
		date_default_timezone_set( get_option( 'timezone_string' ) );
	
	// Encode HTML tags instead of stripping them.
	$feed->encode_instead_of_strip( false );
	
	// If a cache time is set in the admin AND the "smm-cache" folder is writeable,
	// set up the cache.
	if ( (int)smm_option( 'cache_time' ) > 0 && is_writable( SMM_DIR . '/smm-cache' ) ) {
		$feed->enable_cache( true );
		$feed->set_cache_duration( (int)smm_option( 'cache_time' ) * 60 );
		$feed->set_cache_location( SMM_DIR . '/smm-cache' );
		$output .= "\t<!-- Social Media Mashup cache is enabled. Duration: " . smm_option( 'cache_time' ) . " minutes -->\n";
	}
	else {
		$feed->enable_cache( false );
		$output .= "\t<!-- Social Media Mashup cache is disabled. -->\n";
	}
	
	// Start SimplePie's engines.
	$feed->init();
	$feed->handle_content_type();
	
	// Loop through the items in the combined feed
	foreach ( $feed->get_items( 0, $count ) as $item ):
		
		// Make links out of URLs in the text
		$final = preg_replace('/\s(http:\/\/[^\s]+)/', ' <a href="$1">$1</a>', $item->get_description() );
		
		// If the description is blank, use the title
		if ( $final == '' ) {
			$final = preg_replace('/\s(http:\/\/[^\s]+)/', ' <a href="$1">$1</a>', $item->get_title() );
		
			// Some items have embeddable media - that usually comes with a thumbnail.
			// In the case of custom YouTube feeds, there's no formatted description
			// like in a "user uploads" YouTube feed.
			$enclosure = $item->get_enclosure();
			if ( $enclosure != '' ) {
				$final .= '<br /><a href="' . $item->get_permalink() . '"><img src="' . $enclosure->get_thumbnail() . '" /></a>';
			}
		}
		
		// Facebook adds <br/> tags up the wazoo. This makes it a little better.
		$final = str_replace( array( '<br/><br/>', '<br><br>', '<br /><br />' ), '</p><p>', $final );
		$final = str_replace( array( '<br/>', '<br>', '<br />' ), '</p><p>', $final );
		
		$item_class = smm_feed_class( $item->get_feed()->get_permalink() );
		if ( $item_class == 'twitter' ) {
			// Remove "Username: " from beginning of tweets
			$final = preg_replace('/([^:]+:\s)/', '', $final, 1 );
			
			// Add links to all @ mentions
			$final = preg_replace('/@([^\s]+)/', '<a href="http://twitter.com/$1">@$1</a>', $final );
			
			// Add links to all hash tags
			$final = preg_replace('/#([^\s]+)/', '<a href="http://twitter.com/search/%23$1">#$1</a>', $final );
			
			// I <3 Regular Expressions.
		}
		
		// Decide whether to show the feed source. If someone enters a YouTube feed in one of
		// the custom RSS fields, it needs to show the YouTube icon ($item_class = 'youtube custom')
		// but still show the feed source if "RSS feeds only" is selected in the admin.
		$source = '';
		if ( smm_option( 'show_source' ) == 'all' || ( smm_option( 'show_source' ) == 'rss' && in_array( $item_class, array( 'rss', 'youtube custom' ) ) ) )
			$source = $item->get_feed()->get_title() . ' <span class="sep">|</span> ';
		
		// Make this entry consistent with the site's text formatting
		$final = apply_filters( 'the_content', $final );
		
		// Engage!
		$output .= "\n\t" . '<div class="smm-item smm-' . $item_class . '">
		' . $final . '
		<p class="entry-meta">' . $source . '<a href="' . $item->get_permalink() . '">' . smm_friendly_date( $item->get_date( 'c' ) ) . ' &rarr;</a></p>
	</div>' . "\n";
	
	endforeach;
	
	$output .= "</div>\n";
	
	$output .= "<!-- End Social Media Mashup plugin -->\n";
	
	if ( $echo )
		echo $output;
	else
		return $output;
	
}

function smm_feed_url( $feed_key ) {
	
	switch ( $feed_key ) {
		
		case 'twitter':
			$feed_url = 'http://api.twitter.com/1/statuses/user_timeline.rss?user_id=' . smm_option( 'twitter' );
			break;
		
		case 'facebook':
			$feed_url = 'http://www.facebook.com/feeds/page.php?format=rss20&id=' . smm_option( 'facebook' );
			break;
		
		// Google Plus is the only one without a native RSS feed - plusfeed.appspot.com to the rescue
		case 'google':
			$feed_url = 'http://plusfeed.appspot.com/' . smm_option( 'google' );
			break;
		
		case 'flickr':
			$feed_url = 'http://api.flickr.com/services/feeds/photos_public.gne?id=' . smm_option( 'flickr' ) . '&lang=en-us&format=rss_200';
			break;
		
		case 'youtube':
			$feed_url = 'http://gdata.youtube.com/feeds/base/users/' . smm_option( 'youtube' ) . '/uploads?alt=rss&v=2&orderby=published&client=ytapi-youtube-profile';
			break;
		
		case 'rss1':
		case 'rss2':
		case 'rss3':
			$feed_url = smm_option( $feed_key );
			break;
		
	}
	
	return $feed_url;
	
}

function smm_feed_class( $feed_url ) {
	
	if ( strpos( $feed_url, 'twitter' ) !== false )
		return 'twitter';
	elseif ( strpos( $feed_url, 'facebook' ) !== false )
		return 'facebook';
	elseif ( strpos( $feed_url, 'google' ) !== false )
		return 'google';
	elseif ( strpos( $feed_url, 'flickr' ) !== false )
		return 'flickr';
	// Only the default YouTube feed (user uploads) gets "youtube"
	elseif ( strpos( $feed_url, 'youtube' ) !== false && strpos( $feed_url, 'uploads' ) !== false && strpos( $feed_url, smm_option( 'youtube' ) ) !== false )
		return 'youtube';
	// All other YouTube feeds (entered in Custom RSS) get "youtube custom"
	elseif ( strpos( $feed_url, 'youtube' ) !== false )
		return 'youtube custom';
	// This is the default/fallback class
	else
		return 'rss';
	
}

/* Widget
========================================================*/

require_once( SMM_DIR . '/widget.php' );

/* Shortcode
========================================================*/

add_shortcode( 'social-media-mashup', 'smm_shortcode' );

/**
 * [social-media-mashup count="5"]
 */
function smm_shortcode( $atts ) {
	
	extract( shortcode_atts( array(
		'count' => 5,
	), $atts ) );
	
	return social_media_mashup( $count, false );
	
}

/* Friendly dates (i.e. "2 days ago")
========================================================*/

function smm_friendly_date( $date ) {
	
	// Make sure the server is using the right time zone
	if ( get_option( 'timezone_string' ) && strpos( get_option( 'timezone_string' ), 'UTC' ) === false )
		date_default_timezone_set( get_option( 'timezone_string' ) );
	
	// Get the time difference in seconds
	$post_time = strtotime( $date );
	$current_time = time();
	$time_difference = $current_time - $post_time;
	
	// Seconds per...
	$minute = 60;
	$hour = 3600;
	$day = 86400;
	$week = $day * 7;
	$month = $day * 31;
	$year = $day * 366;
	
	// if over 3 years
	if ( $time_difference > $year * 3 ) {
		$friendly_date = __( 'a long while ago', BNM_LOCALE );
	}
	
	// if over 2 years
	else if ( $time_difference > $year * 2 ) {
		$friendly_date =__( 'over 2 years ago', BNM_LOCALE );
	}
	
	// if over 1 year
	else if ( $time_difference > $year ) {
		$friendly_date = __( 'over a year ago', BNM_LOCALE );
	}
	
	// if over 11 months
	else if ( $time_difference >= $month * 11 ) {
		$friendly_date = __( 'about a year ago', BNM_LOCALE );
	}
	
	// if over 2 months
	else if ( $time_difference >= $month * 2 ) {
		$months = (int) $time_difference / $month;
		$friendly_date = sprintf( __( '%d months ago', BNM_LOCALE ), $months );
	}
	
	// if over 4 weeks ago
	else if ( $time_difference > $week * 4 ) {
		$friendly_date = __( 'last month', BNM_LOCALE );
	}
	
	// if over 3 weeks ago
	else if ( $time_difference > $week * 3 ) {
		$friendly_date = __( '3 weeks ago', BNM_LOCALE );
	}
	
	// if over 2 weeks ago
	else if ( $time_difference > $week * 2 ) {
		$friendly_date = __( '2 weeks ago', BNM_LOCALE );
	}
	
	// if equal to or more than a week ago
	else if ( $time_difference >= $day * 7 ) {
		$friendly_date = __( 'last week', BNM_LOCALE );
	}
	
	// if equal to or more than 2 days ago
	else if ( $time_difference >= $day * 2 ) {
		$days = (int) $time_difference / $day;
		$friendly_date = sprintf( __( '%d days ago', BNM_LOCALE ), $days );
	}
	
	// if equal to or more than 1 day ago
	else if ( $time_difference >= $day ) {
		$friendly_date = __( 'yesterday', BNM_LOCALE );
	}
	
	// 2 or more hours ago
	else if ( $time_difference >= $hour * 2 ) {
		$hours = (int) $time_difference / $hour;
		$friendly_date = sprintf( __( '%d hours ago', BNM_LOCALE ), $hours );
	}
	
	// 1 hour ago
	else if ( $time_difference >= $hour ) {
		$friendly_date = __( 'an hour ago', BNM_LOCALE );
	}
	
	// 2–59 minutes ago
	else if ( $time_difference >= $minute * 2 ) {
		$minutes = (int) $time_difference / $minute;
		$friendly_date = sprintf( __( '%d minutes ago', BNM_LOCALE ), $minutes );
	}
	
	else {
		$friendly_date = __( 'just now', BNM_LOCALE );
	}
	
	// HTML 5 FTW
	return '<time title="' . $date . '" datetime="' . date( 'c', strtotime( $date ) ) . '" pubdate>' . ucfirst( $friendly_date ) . '</time>';
	
}

/* Stylesheet
========================================================*/

add_action( 'init', 'smm_styles' );

function smm_styles() {
	
	if ( ! is_admin() ) {
		if ( smm_option( 'use_styles' ) ) {
			// Main stylesheet - everything but icons
			wp_register_style( 'social-media-mashup', SMM_URL . '/style.css' );
			wp_enqueue_style( 'social-media-mashup' );
		}
		if ( smm_option( 'show_icons' ) != 'none' ) {
			// Icons only
			wp_register_style( 'social-media-mashup-icons', SMM_URL . '/icons.css' );
			wp_enqueue_style( 'social-media-mashup-icons' );
		}
	}
	
}

/* Settings page
========================================================*/

require_once( SMM_DIR . '/admin.php' );

/* 'Settings' link on Plugins page
========================================================*/

add_filter( 'plugin_action_links', 'smm_plugin_action_links', 10, 2 );

function smm_plugin_action_links( $links, $file ) {
	if ( $file == plugin_basename( dirname( __FILE__ ) . '/social-media-mashup.php' ) ) {
		$links[] = '<a href="options-general.php?page=smm-options">' . __( 'Settings', BNM_LOCALE ) . '</a>';
	}

	return $links;
}


?>