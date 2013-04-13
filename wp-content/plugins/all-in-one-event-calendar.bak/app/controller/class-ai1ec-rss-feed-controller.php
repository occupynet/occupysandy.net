<?php
class Ai1ec_Rss_Feed_Controller {

	/**
	 * add the feed. This is called dureing the init phase to work correctly
	 * 
	 */
	public function add_feed() {
		add_feed( 'ai1ec_event', array( $this, 'create_feed_output' ) );
	}

	/**
	 * Creates the output of the RSS feed.
	 * 
	 * @param boolean $comment ( ignored )
	 */
	public function create_feed_output( $comment ) {
		global $ai1ec_calendar_helper,
		       $ai1ec_view_helper,
		       $ai1ec_events_helper,
		       $ai1ec_settings;

		$number_of_posts = Ai1ec_Meta::get_option( 'posts_per_rss' );
		// Get the request parser
		$request = new Ai1ec_Arguments_Parser(
			NULL,
			'ai1ec_' . $ai1ec_settings->default_calendar_view
		);
		$request->parse();
		// Create the filter
		$filter = array( 
			'cat_ids' => $request->get( 'cat_ids' ),
			'tag_ids' => $request->get( 'tag_ids' ),
			'post_ids' => $request->get( 'post_ids' ),
		);

		$event_results = $ai1ec_calendar_helper->get_events_relative_to(
			$ai1ec_events_helper->gmt_to_local(
				Ai1ec_Time_Utility::current_time()
			),
			$number_of_posts,
			0,
			$filter,
			0
		);
		require_once AI1EC_VIEW_PATH . '/event-feed-rss2.php';
	}
}
