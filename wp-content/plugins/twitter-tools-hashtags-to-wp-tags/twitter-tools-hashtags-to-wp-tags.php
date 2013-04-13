<?php
/*
Plugin Name: Twitter Tools - Hashtags to WP tags
Plugin URI: http://www.feedmeastraycat.net/
Description: Hashtags to WP tags is a small extension for the Twitter Tools plugin. Use it to create WordPress tags for each hashtag when you enable Twitter Tools to automatically create posts from each of your tweets. Just activate it and all hashtags are automatically added as post tags when the tweets are added.
Version: 0.1.0
Author: David M&aring;rtensson, Mark Ellis
Author URI: http://www.feedmeastraycat.net/
*/


// Add action for Twitter tools do_tweet_post
add_action('aktt_do_tweet_post', array('TwitterToolsHashtagsToWPTags', 'aktt_do_tweet_post'), 10, 2);

// Add action for set object terms
add_action('set_object_terms', array('TwitterToolsHashtagsToWPTags', 'set_object_terms'), 10, 6);



/**
 * Twitter Tools Hashtags to WP Tags singelton class
 */
class TwitterToolsHashtagsToWPTags {

	/**
	 * Store the tweet ids of each tweets that has been checked during this page load
	 * @var array
	 */
	public static $HashtaggedTweetIds = array();
	
	/**
	 * Store the hashtags for each tweet that has been checked during this page load
	 * @var array
	 */
	public static $TweetIdsHashtags = array();
	
	
	/**
	 * Twitter Tools action - aktt_do_tweet_post
	 * This function runs when twitter tools creates the post data for each found tweet. It searches
	 * the tweet for hashtags and store them to be able to create WP tags later (in set_object_terms())
	 * @param array $data Post data (which is sent into wp_insert_post() by Twitter Tools)
	 * @param object $tweet Twitter Tool tweet object
	 * @return array Returns overwritten array
	 */
	public static function aktt_do_tweet_post($data, $tweet) {
		$matches = array();
		preg_match_all('/\#([\w\pL]{1,})/u', $tweet->tw_text, $matches);
		if (!empty($matches[1])) {
			TwitterToolsHashtagsToWPTags::$HashtaggedTweetIds[] = $tweet->tw_id;
			TwitterToolsHashtagsToWPTags::$TweetIdsHashtags[$tweet->tw_id] = $matches[1];
		}
		return $data;
	}
	
	
	/**
	 * WordPress action - set_object_terms
	 * Due to how Twitter Tools saves each post (when importing tweets) and how it adds tags, we must
	 * hook into this action to save our hashtags as tags after Twitter Tools has added its tags.
	 * If not, they are overwritten and removed by Twitter Tools. (See twitter-tools.php class 
	 * twitter_tools, function do_tweet_post, where it runs wp_set_post_tags() without append).
	 * @param int $object_id The object to relate to.
	 * @param array|int|string $terms The slug or id of the term, will replace all existing related terms in this taxonomy.
	 * @param array|string $taxonomy The context in which to relate the term to the object.
	 * @param bool $append If false will delete difference of terms.
	 * @see wp_set_object_terms()
	 * @return void
	 */
	public static function set_object_terms($object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids) {
		if (!empty(TwitterToolsHashtagsToWPTags::$HashtaggedTweetIds)) {
			foreach (TwitterToolsHashtagsToWPTags::$HashtaggedTweetIds AS $index => $twitter_id) {
				$post_id = TwitterToolsHashtagsToWPTags::get_post_id_by_meta_key_and_value('aktt_twitter_id', $twitter_id);
				if ($post_id == $object_id) {
					// Unset so when we call wp_set_post_tags we don't end up with an infinite loop loop loop loop...
					unset(TwitterToolsHashtagsToWPTags::$HashtaggedTweetIds[$index]);
					wp_set_post_tags($post_id, TwitterToolsHashtagsToWPTags::$TweetIdsHashtags[$twitter_id], true);
				}
			}
			
		}
	}
	
	
	/**
	 * Get post id from meta key and value
	 * @param string $key
	 * @param mixed $value
	 * @return int|bool
	 */
	public static function get_post_id_by_meta_key_and_value($key, $value) {
		global $wpdb;
		$meta = $wpdb->get_results("SELECT * FROM `".$wpdb->postmeta."` WHERE meta_key='".$wpdb->escape($key)."' AND meta_value='".$wpdb->escape($value)."' ORDER BY meta_id DESC LIMIT 1");
		if (is_array($meta) && !empty($meta) && isset($meta[0])) {
			$meta = $meta[0];
		}		
		if (is_object($meta)) {
			return $meta->post_id;
		}
		else {
			return false;
		}
	}
	

}


