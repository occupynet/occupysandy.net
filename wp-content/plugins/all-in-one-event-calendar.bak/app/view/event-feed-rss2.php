<?php
/**
 * RSS2 Feed Template for displaying RSS2 Posts feed.
 *
 * @package WordPress
 */

header('Content-Type: ' . feed_content_type('rss-http') . '; charset=' . Ai1ec_Meta::get_option( 'blog_charset' ), true);

echo '<?xml version="1.0" encoding="',
	Ai1ec_Meta::get_option( 'blog_charset' ),
	'"?'.'>'; ?>

<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	<?php do_action('rss2_ns'); ?>
>

<channel>
	<title><?php bloginfo_rss('name'); wp_title_rss(); ?></title>
	<atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
	<link><?php bloginfo_rss('url') ?></link>
	<description><?php bloginfo_rss("description") ?></description>
	<lastBuildDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_lastpostmodified('GMT'), false); ?></lastBuildDate>
	<language><?php bloginfo_rss( 'language' ); ?></language>
	<sy:updatePeriod><?php echo apply_filters( 'rss_update_period', 'hourly' ); ?></sy:updatePeriod>
	<sy:updateFrequency><?php echo apply_filters( 'rss_update_frequency', '1' ); ?></sy:updateFrequency>
<?php


foreach ( $event_results['events'] as $event ){
	$title       = htmlspecialchars(
		apply_filters( 'the_title', $event->post->post_title, $event->post_id )
	);
	$permalink   = htmlspecialchars( get_permalink( $event->post_id ) );
	$date        = date('d M Y H:i:s', $event->start );
	$user_info   = get_userdata( $event->post->post_author );
	$location    = str_replace( "\n", ', ', rtrim( $event->get_location() ) );
	$use_excerpt = Ai1ec_Meta::get_option( 'rss_use_excerpt' );
	$description = apply_filters( 'the_content', $event->post->post_content );
	if ( $use_excerpt ) {
		$description = Ai1ec_String_Utility::truncate_string_if_longer_than_x_words(
			$description,
			50,
			" <a href='$permalink' >" . __( 'Read more...', AI1EC_PLUGIN_NAME ) . "</a>"
		);
	}
	$args = array(
		'timespan'    => $event->get_timespan_html(),
		'location'    => $location,
		'permalink'   => $permalink,
		'description' => wpautop( $description ),
	);
	// Load the RSS specific template
	ob_start();
	$ai1ec_view_helper->display_theme( 'event-feed-description.php', $args );
	$content = ob_get_contents();
	ob_end_clean();
	$user            = $user_info->user_login;
	$guid            = htmlspecialchars( get_the_guid( $event->post_id ) );
	$comments        = esc_url(
		get_post_comments_feed_link( $event->post_id, 'rss2' )
	);
	$comments_number = get_comments_number( $event->post_id );
	echo <<<FEED
<item>
	<title>$title</title>
	<link>{$permalink}{$event->instance_id}</link>
	<pubDate>$date</pubDate>
	<dc:creator>$user</dc:creator>
	<guid isPermaLink="false">$guid</guid>
	<description><![CDATA[$content]]></description>
	<wfw:commentRss>$comments</wfw:commentRss>
	<slash:comments>$comments_number</slash:comments>
</item>
FEED;
	} 
		
		
		 ?>

</channel>
</rss>
