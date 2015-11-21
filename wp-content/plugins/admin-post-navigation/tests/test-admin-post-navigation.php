<?php

class Admin_Post_Navigation_Test extends WP_UnitTestCase {

	function setUp() {
		parent::setUp();

		c2c_AdminPostNavigation::register_post_page_hooks();
	}

	function tearDown() {
		parent::tearDown();

		unset( $GLOBALS['post_ID'] );
		$this->unset_current_user();

		remove_filter( 'c2c_admin_post_navigation_post_statuses', array( $this, 'c2c_admin_post_navigation_post_statuses' ), 10, 3 );
		remove_filter( 'c2c_admin_post_navigation_orderby',       array( $this, 'c2c_admin_post_navigation_orderby' ), 10, 2 );
		remove_filter( 'c2c_admin_post_navigation_orderby',       array( $this, 'c2c_admin_post_navigation_orderby_bad_value' ), 10, 2 );
	}


	/*
	 *
	 * DATA PROVIDERS
	 *
	 */



	/*
	 *
	 * HELPER FUNCTIONS
	 *
	 */


	private function create_user( $role, $set_as_current = true ) {
		$user_id = $this->factory->user->create( array( 'role' => $role ) );
		if ( $set_as_current ) {
			wp_set_current_user( $user_id );
		}
		return $user_id;
	}

	// helper function, unsets current user globally. Taken from post.php test.
	private function unset_current_user() {
		global $current_user, $user_ID;

		$current_user = $user_ID = null;
	}

	private function create_posts( $number = 5, $current_post_index = 2 ) {
		$user_id = $this->create_user( 'administrator' );

		$posts = $this->factory->post->create_many( $number, array( 'post_author' => $user_id ) );

		$GLOBALS['post_ID'] = $posts[ $current_post_index ];
		$current_post = get_post( $posts[ $current_post_index ] );

		c2c_AdminPostNavigation::do_meta_box( $current_post->post_type, 'normal', $current_post );

		return $posts;
	}

	public function c2c_admin_post_navigation_post_statuses( $post_statuses, $post_type, $post ) {
		$this->assertTrue( is_array( $post_statuses ) );
		$this->assertEquals( 'post', $post_type );
		$this->assertTrue( is_a( $post, 'WP_Post' ) );

		// Add a post status.
		$post_statuses[] = 'trash';

		// Remove post status.
		$post_statuses_to_remove = array( 'draft' );
		foreach ( $post_statuses_to_remove as $remove ) {
			if ( false !== $index = array_search( $remove, $post_statuses ) ) {
				unset( $post_statuses[ $index ] );
			}
		}

		return $post_statuses;
	}

	public function c2c_admin_post_navigation_orderby( $orderby, $post_type ) {
		return 'post_date';
	}

	public function c2c_admin_post_navigation_orderby_bad_value( $orderby, $post_type ) {
		return 'gibberish';
	}


	/*
	 *
	 * TESTS
	 *
	 */


	function test_class_exists() {
		$this->assertTrue( class_exists( 'c2c_AdminPostNavigation' ) );
	}

	function test_version() {
		$this->assertEquals( '1.9.2', c2c_AdminPostNavigation::version() );
	}

	/*
	 * c2c_AdminPostNavigation::next_post()
	 */

	function test_navigate_next_to_post() {
		$posts = $this->create_posts();

		$next_post = c2c_AdminPostNavigation::next_post();

		$this->assertEquals( $posts[3], $next_post->ID );
	}

	function test_navigate_next_at_end() {
		$posts = $this->create_posts( 5, 4 );

		$next_post = c2c_AdminPostNavigation::next_post();

		$this->assertEmpty( $next_post );
	}

	function test_navigate_next_skips_unwhitelisted_post_status() {
		$posts = $this->create_posts();

		$post = get_post( $posts[3] );
		$post->post_status = 'trash';
		wp_update_post( $post );

		$next_post = c2c_AdminPostNavigation::next_post();

		$this->assertEquals( $posts[4], $next_post->ID );
	}

	function test_navigate_next_when_no_editable_next() {
		$posts = $this->create_posts();
		$user_id = $this->create_user( 'author' );

		$post = get_post( $posts[2] );
		$post->post_author = $user_id;
		wp_update_post( $post );

		$next_post = c2c_AdminPostNavigation::next_post();

		$this->assertEmpty( $next_post );
	}

	/*
	 * c2c_AdminPostNavigation::previous_post()
	 */

	function test_navigate_previous_to_post() {
		$posts = $this->create_posts();

		$previous_post = c2c_AdminPostNavigation::previous_post();

		$this->assertEquals( $posts[1], $previous_post->ID );
	}

	function test_navigate_previous_at_beginning() {
		$posts = $this->create_posts( 5, 0 );

		$previous_post = c2c_AdminPostNavigation::previous_post();

		$this->assertEmpty( $previous_post );
	}

	function test_navigate_previous_skips_unwhitelisted_post_status() {
		$posts = $this->create_posts();

		$post = get_post( $posts[1] );
		$post->post_status = 'trash';
		wp_update_post( $post );

		$previous_post = c2c_AdminPostNavigation::previous_post();

		$this->assertEquals( $posts[0], $previous_post->ID );
	}

	function test_navigate_previous_when_no_editable_previous() {
		$posts = $this->create_posts();
		$user_id = $this->create_user( 'author' );

		$post = get_post( $posts[2] );
		$post->post_author = $user_id;
		wp_update_post( $post );

		$previous_post = c2c_AdminPostNavigation::previous_post();

		$this->assertEmpty( $previous_post );
	}


	/*
	 * Filters.
	 */


	function test_hooks_action_load_post_php() {
		$this->assertEquals( 10, has_action( 'load-post.php', array( 'c2c_AdminPostNavigation', 'register_post_page_hooks' ) ) );
	}

	function test_hooks_action_admin_enqueue_scripts() {
		$this->assertEquals( 10, has_action( 'admin_enqueue_scripts', array( 'c2c_AdminPostNavigation', 'add_css' ) ) );
	}

	function test_hooks_action_admin_print_footer_scripts() {
		$this->assertEquals( 10, has_action( 'admin_print_footer_scripts', array( 'c2c_AdminPostNavigation', 'add_js' ) ) );
	}

	function test_hooks_action_do_meta_boxes() {
		$this->assertEquals( 10, has_action( 'do_meta_boxes', array( 'c2c_AdminPostNavigation', 'do_meta_box' ) ) );
	}

	function test_filter_c2c_admin_post_navigation_post_statuses_when_adding_post_status() {
		add_filter( 'c2c_admin_post_navigation_post_statuses', array( $this, 'c2c_admin_post_navigation_post_statuses' ), 10, 3 );

		$posts = $this->create_posts();

		$post = get_post( $posts[3] );
		$post->post_status = 'trash';
		wp_update_post( $post );

		$next_post = c2c_AdminPostNavigation::next_post();

		$this->assertEquals( $posts[3], $next_post->ID );
	}

	function test_filter_c2c_admin_post_navigation_post_statuses_when_removing_post_status() {
		add_filter( 'c2c_admin_post_navigation_post_statuses', array( $this, 'c2c_admin_post_navigation_post_statuses' ), 10, 3 );

		$posts = $this->create_posts();

		$post = get_post( $posts[3] );
		$post->post_status = 'draft';
		wp_update_post( $post );
		$post = get_post( $posts[2] );

		$next_post = c2c_AdminPostNavigation::next_post();
		$this->assertEquals( $posts[4], $next_post->ID );
	}

	function test_filter_c2c_admin_post_navigation_orderby() {
		add_filter( 'c2c_admin_post_navigation_orderby', array( $this, 'c2c_admin_post_navigation_orderby' ), 10, 2 );

		$posts = $this->create_posts();

		// Change post dates so post ordering by date is 3, 0, 2, 4, 1
		$new_post_dates = array(
			'2015-06-13 12:30:00',
			'2015-03-13 12:30:00',
			'2015-05-13 12:30:00',
			'2015-07-13 12:30:00',
			'2015-04-13 12:30:00',
		);
		foreach ( $new_post_dates as $i => $date ) {
			$post = get_post( $posts[ $i ] );
			$post->post_date = $date;
			wp_update_post( $post );
		}

		$next_post = c2c_AdminPostNavigation::next_post();

		$this->assertEquals( $posts[0], $next_post->ID );

		$previous_post = c2c_AdminPostNavigation::previous_post();

		$this->assertEquals( $posts[4], $previous_post->ID );
	}

	function test_filter_c2c_admin_post_navigation_orderby_with_bad_value() {
		add_filter( 'c2c_admin_post_navigation_orderby', array( $this, 'c2c_admin_post_navigation_orderby_bad_value' ), 10, 2 );

		// Should function as if never hooked.
		$this->test_navigate_next_to_post();
		$this->test_navigate_previous_to_post();
	}

	/*
	 * TODO tests:
	 * - JS is not enqueued on frontend
	 * - JS is enqueue on appropriate admin page(s)
	 * - JS is not enqueued on inappropriate admin page(s)
	 * - CSS is not enqueued on frontend
	 * - CSS is enqueue on appropriate admin page(s)
	 * - CSS is not enqueued on inappropriate admin page(s)
	 */

}
