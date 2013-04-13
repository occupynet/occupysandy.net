<?php
//
//  class-ai1ec-duplicate-controller.php
//  all-in-one-event-calendar
//
//  Created by The Seed Studio on 2012-04-22.
//

/**
 * Ai1ec_Duplicate_Controller class
 *
 * @package Controllers
 * @author time.ly
 **/
class Ai1ec_Duplicate_Controller {
	/**
	 * _instance class variable
	 *
	 * Class instance
	 *
	 * @var null | object
	 **/
	private static $_instance = NULL;

	/**
	 * 
	 * @var Ai1ec_Admin_Notices_Helper
	 */
	private $admin_notice_helper;

	/**
	 * @param field_type $admin_notice_helper
	 */
	public function set_admin_notice_helper( Ai1ec_Admin_Notices_Helper &$admin_notice_helper ) {
		$this->admin_notice_helper = $admin_notice_helper;
	}

	/**
	 * Constructor
	 *
	 * Default constructor
	 **/
	private function __construct() {
		// ===========
		// = ACTIONS =
		// ===========
		add_action( 'admin_action_duplicate_post_save_as_new_post' , array( $this , 'duplicate_post_save_as_new_post'));
		add_action( 'admin_action_duplicate_post_save_as_new_post_draft' , array( $this , 'duplicate_post_save_as_new_post_draft'));

		// Using our action hooks to copy taxonomies
		add_action( 'dp_duplicate_page' , array( $this , 'duplicate_post_copy_post_taxonomies' ), 10 , 2 );

		// Using our action hooks to copy meta fields
		add_action( 'dp_duplicate_page' , array( $this , 'duplicate_post_copy_post_meta_info' ), 10 , 2 );

		// Using our action hooks to copy attachments
		add_action( 'dp_duplicate_page' , array( $this , 'duplicate_post_copy_attachments' ), 10 , 2 );

		// Using our action hooks to copy events meta
		add_action( 'dp_duplicate_page' , array( $this , 'duplicate_event_meta' ), 10 , 2 );

		// Using  action hooks to for custom duplicate bulk action
		add_action( 'admin_footer-edit.php' , array( $this , 'duplicate_custom_bulk_admin_footer' ));

		// Using  action hooks to for custom duplicate bulk action
		add_action( 'load-edit.php' , array( $this , 'duplicate_custom_bulk_action' ));

		// ===========
		// = FILTERS =
		// ===========
		add_filter(
			'page_row_actions',
			array( $this , 'duplicate_post_make_duplicate_link_row' ),
			10,
			2
		);

	}

	/**
	 * get_instance function
	 *
	 * Return singleton instance
	 *
	 * @return Ai1ec_Duplicate_Controller
	 **/
	static function get_instance() {
		if( self::$_instance === NULL ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	* Add the link to action list for post_row_actions
	*/
	function duplicate_post_make_duplicate_link_row($actions, $post) {

		if( $post->post_type == "ai1ec_event" ) {
			$actions['clone'] = '<a href="'.$this->duplicate_post_get_clone_post_link( $post->ID , 'display', false).'" title="'
			. esc_attr(__("Make new copy of event", AI1EC_PLUGIN_NAME))
			. '">' .  __( 'Clone', AI1EC_PLUGIN_NAME ) . '</a>';
			$actions['edit_as_new_draft'] = '<a href="' . $this->duplicate_post_get_clone_post_link( $post->ID ) . '" title="'
			. esc_attr(__( 'Copy to a new draft' , AI1EC_PLUGIN_NAME ))
			. '">' .  __( 'Clone to Draft' , AI1EC_PLUGIN_NAME ) . '</a>';
		}
		return $actions;
	}

	/**
	 * Retrieve duplicate post link for post.
	 *
	 *
	 * @param int $id Optional. Post ID.
	 * @param string $context Optional, default to display. How to write the '&', defaults to '&amp;'.
	 * @param string $draft Optional, default to true
	 * @return string
	 */
	function duplicate_post_get_clone_post_link( $id = 0, $context = 'display', $draft = true ) {

		if ( !$post = &get_post( $id ) )
			return;

		if ( $draft )
			$action_name = "duplicate_post_save_as_new_post_draft";
		else
			$action_name = "duplicate_post_save_as_new_post";

		if ( 'display' == $context )
			$action = '?action=' . $action_name . '&amp;post=' . $post->ID;
		else
			$action = '?action=' . $action_name . '&post=' . $post->ID;

		$post_type_object = get_post_type_object( $post->post_type );
		if ( !$post_type_object )
			return;

		return apply_filters( 'duplicate_post_get_clone_post_link', admin_url( "admin.php". $action ), $post->ID , $context );
	}

	/**
	 * Display duplicate post link for post.
	 *
	 * @param string $link Optional. Anchor text.
	 * @param string $before Optional. Display before edit link.
	 * @param string $after Optional. Display after edit link.
	 * @param int $id Optional. Post ID.
	 */
	function duplicate_post_clone_post_link( $link = null, $before = '', $after = '', $id = 0 ) {
		if ( !$post = &get_post( $id ) )
			return;

		if ( !$url = duplicate_post_get_clone_post_link( $post->ID ) )
			return;

		if ( null === $link )
			$link = __( 'Copy to a new draft' , AI1EC_PLUGIN_NAME );

		$post_type_obj = get_post_type_object( $post->post_type );
		$link = '<a class="post-clone-link" href="' . $url . '" title="'
		. esc_attr(__( "Copy to a new draft" , AI1EC_PLUGIN_NAME ))
		.'">' . $link . '</a>';

		echo $before . apply_filters( 'duplicate_post_clone_post_link', $link, $post->ID ) . $after;
	}
	/**
	 * Get original post .
	 *
	 * @param int $id Optional. Post ID.
	 * @param string $output Optional, default is Object. Either OBJECT, ARRAY_A, or ARRAY_N.
	 * @return mixed Post data
	 */
	function duplicate_post_get_original( $id = 0 , $output = OBJECT ) {
		if ( ! ( $post = &get_post( $id ) ) ) {
			return;
		}

		$original_ID = Ai1ec_Meta::instance( 'Post' )
			->get( $post->ID , '_dp_original', '', true );
		if ( empty( $original_ID ) ) {
			return NULL;
		}

		$original_post = &get_post( $original_ID[0], $output );
		return $original_post;
	}

	/**
	 * Connect actions to functions
	 */

	/*
	 * This function calls the creation of a new copy of the selected post (as a draft)
	 * then redirects to the edit post screen
	 */
	function duplicate_post_save_as_new_post_draft() {
		$this->duplicate_post_save_as_new_post( 'draft' );
	}

	/*
	 * This function calls the creation of a new copy of the selected post (by default preserving the original publish status)
	 * then redirects to the post list
	 */
	function duplicate_post_save_as_new_post( $status = '' ) {
		if (! ( isset( $_GET['post'] ) || isset( $_POST['post'] )  || ( isset( $_REQUEST['action'] ) && 'duplicate_post_save_as_new_post' == $_REQUEST['action'] ) ) ) {
			wp_die(__('No post to duplicate has been supplied!', AI1EC_PLUGIN_NAME ) );
		}

		// Get the original post
		$id = ( isset( $_GET['post'] ) ? $_GET['post'] : $_POST['post'] );
		$post = get_post( $id );

		// Copy the post and insert it
		if ( isset( $post ) && $post != null ) {
			$new_id = $this->duplicate_post_create_duplicate( $post , $status );

			if ( $status == '') {
				// Redirect to the post list screen
				wp_redirect( admin_url( 'edit.php?post_type='.$post->post_type ) );
			} else {
				// Redirect to the edit screen for the new draft post
				wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_id ) );
			}
			exit;

		} else {
			$post_type_obj = get_post_type_object( $post->post_type );
			wp_die( esc_attr( __( 'Copy creation failed, could not find original:' , AI1EC_PLUGIN_NAME ) ) . ' ' . $id );
		}
	}

	/**
	 * Get the currently registered user
	 */
	function duplicate_post_get_current_user() {
		if ( function_exists( 'wp_get_current_user' ) ) {
			return wp_get_current_user();
		} else if ( function_exists( 'get_currentuserinfo' ) ) {
			global $userdata;
			get_currentuserinfo();
			return $userdata;
		} else {
			global $wpdb;
			$query        = $wpdb->prepare(
				'SELECT * FROM ' . $wpdb->users . ' WHERE user_login = %s',
				$_COOKIE[ USER_COOKIE ]
			);
			$current_user = $wpdb->get_results( $query );
			return $current_user;
		}
	}

	/**
	 * Copy the taxonomies of a post to another post
	 */
	function duplicate_post_copy_post_taxonomies( $new_id , $post ) {
		global $wpdb;
		if ( isset( $wpdb->terms ) ) {
			// Clear default category (added by wp_insert_post)
			wp_set_object_terms( $new_id , NULL, 'category' );

			$post_taxonomies = get_object_taxonomies( $post->post_type );
			//$taxonomies_blacklist = get_option('duplicate_post_taxonomies_blacklist');
			//if ( $taxonomies_blacklist == "" )
			$taxonomies_blacklist = array();
			$taxonomies = array_diff( $post_taxonomies , $taxonomies_blacklist );
			foreach ( $taxonomies as $taxonomy ) {
				$post_terms = wp_get_object_terms( $post->ID , $taxonomy , array( 'orderby' => 'term_order' ) );
				$terms = array();
				for ( $i=0; $i<count( $post_terms ); $i++ ) {
					$terms[] = $post_terms[ $i ]->slug;
				}
				wp_set_object_terms ($new_id , $terms , $taxonomy );
			}
		}
	}


	/**
	 * Copy the meta information of a post to another post
	 */
	function duplicate_post_copy_post_meta_info( $new_id , $post ) {
		global $ai1ec_events_controller;
		$ai1ec_events_controller->save_post( $new_id , $post );

		$post_meta_keys = get_post_custom_keys( $post->ID );
		if ( empty( $post_meta_keys ) ) return;
		//$meta_blacklist = explode(",",get_option('duplicate_post_blacklist'));
		//if ( $meta_blacklist == "" )
		$meta_blacklist = array();
		$meta_keys = array_diff( $post_meta_keys, $meta_blacklist );

		foreach ( $meta_keys as $meta_key ) {
			$meta_values = get_post_custom_values( $meta_key, $post->ID );
			foreach ( $meta_values as $meta_value ) {
				$meta_value = maybe_unserialize( $meta_value );
				add_post_meta( $new_id , $meta_key , $meta_value );
			}
		}
	}

	/**
	 * duplicate all event meta values
	 * */

	function duplicate_event_meta( $new_id , $post ) {
		global $ai1ec_events_helper;
		try {
			// get the event
			$event = new Ai1ec_Event( $post->ID );
			// if we are here the event exists
			$cloned_event = new Ai1ec_Event();
			// clone events
			$cloned_event = clone $event;
			// Set new post id
			$cloned_event->post_id = $new_id;
			// Save new event
			$cloned_event->save();
			// Create instances
			$ai1ec_events_helper->cache_event( $cloned_event );
		}
		catch( Ai1ec_Event_Not_Found $e ) {
			// Event does not exist.
			// Leave form fields undefined (= zero-length strings)
			$event = null;
		}

	}

	/**
	 * Copy the attachments
	 * It simply copies the table entries, actual file won't be duplicated
	 */
	function duplicate_post_copy_attachments( $new_id , $post ) {
		//if (get_option('duplicate_post_copyattachments') == 0) return;

		// get old attachments
		$attachments = get_posts( array( 'post_type' => 'attachment' , 'numberposts' => -1 , 'post_status' => null , 'post_parent' => $post->ID ) );
		// clone old attachments
		foreach ( $attachments as $att ) {
			$new_att_author = $this->duplicate_post_get_current_user();

			$new_att = array (
				'menu_order'     => $att->menu_order,
				'comment_status' => $att->comment_status,
				'guid'           => $att->guid,
				'ping_status'    => $att->ping_status,
				'pinged'         => $att->pinged,
				'post_author'    => $new_att_author->ID,
				'post_content'   => $att->post_content,
				'post_date'      => $att->post_date ,
				'post_date_gmt'  => get_gmt_from_date( $att->post_date ),
				'post_excerpt'   => $att->post_excerpt,
				'post_mime_type' => $att->post_mime_type,
				'post_parent'    => $new_id,
				'post_password'  => $att->post_password,
				'post_status'    => $att->post_status,
				'post_title'     => $att->post_title,
				'post_type'      => $att->post_type,
				'to_ping'        => $att->to_ping
			);

			$new_att_id = wp_insert_post( $new_att );

			// get and apply a unique slug
			$att_name = wp_unique_post_slug( $att->post_name , $new_att_id , $att->post_status , $att->post_type , $new_id );
			$new_att = array();
			$new_att['ID']        = $new_att_id;
			$new_att['post_name'] = $att_name;

			wp_update_post( $new_att );

			// call hooks to copy attachement metadata
			do_action( 'dp_duplicate_page', $new_att_id, $att );
		}
	}


	/**
	 * Create a duplicate from a posts' instance
	 */
	function duplicate_post_create_duplicate( $post , $status = '' ) {
		$new_post_author = $this->duplicate_post_get_current_user();
		$new_post_status = $status;
		if ( empty( $new_post_status ) ) {
			$new_post_status = $post->post_status;
		}

		$new_post = array(
			'menu_order'     => $post->menu_order,
			'comment_status' => $post->comment_status,
			'ping_status'    => $post->ping_status,
			'pinged'         => $post->pinged,
			'post_author'    => $new_post_author->ID,
			'post_content'   => $post->post_content,
			'post_date'      => $post->post_date ,
			'post_date_gmt'  => get_gmt_from_date( $post->post_date  ),
			'post_excerpt'   => $post->post_excerpt,
			'post_parent'    => $post->post_parent,
			'post_password'  => $post->post_password,
			'post_status'    => $new_post_status,
			'post_title'     => $post->post_title,
			'post_type'      => $post->post_type,
			'to_ping'        => $post->to_ping,
		);

		$new_post_id = wp_insert_post( $new_post );
		$edit_event_url = esc_attr(
			admin_url( "post.php?post={$new_post_id}&action=edit" )
		);
		$message = Ai1ec_Helper_Factory::create_admin_message_instance(
			sprintf(
				__( '<p>The event <strong>%s</strong> was cloned succesfully. <a href="%s">Edit cloned event</a></p>', AI1EC_PLUGIN_NAME ),
				$post->post_title,
				$edit_event_url
			)
		);
		$message->set_message_type( 'updated' );
		$this->admin_notice_helper->add_renderable_children( $message );
		// If you have written a plugin which uses non-WP database tables to save
		// information about a post you can hook this action to dupe that data.
		if (
			$post->post_type == 'page' || (
				function_exists( 'is_post_type_hierarchical' ) &&
				is_post_type_hierarchical( $post->post_type )
			)
		) {
			do_action( 'dp_duplicate_page', $new_post_id, $post );
		} else {
			do_action( 'dp_duplicate_post', $new_post_id, $post );
		}

		delete_post_meta( $new_post_id, '_dp_original' );
		add_post_meta(    $new_post_id, '_dp_original', $post->ID );

		// If the copy gets immediately published, we have to set a proper slug.
		if (
			$new_post_status == 'publish' ||
			$new_post_status == 'future'
		) {
			$post_name = wp_unique_post_slug(
				$post->post_name,
				$new_post_id,
				$new_post_status,
				$post->post_type,
				$post->post_parent
			);

			$new_post = array();
			$new_post['ID']        = $new_post_id;
			$new_post['post_name'] = $post_name;

			// Update the post into the database
			wp_update_post( $new_post );
		}

		return $new_post_id;
	}


	// =========================================
	// = Bulk Duplicate Custom Action For Events
	// =========================================

	/**
	 * add clone bluk action in the dropdown
	 * */
	function duplicate_custom_bulk_admin_footer() {
		global $post_type;
		if( $post_type == 'ai1ec_event' ) {
		?>
			<script type="text/javascript">
				jQuery(document).ready(function() {

					jQuery('<option>').val('clone').text('<?php _e( 'Clone' , AI1EC_PLUGIN_NAME )?>').appendTo("select[name='action']");
					jQuery('<option>').val('clone').text('<?php _e( 'Clone' , AI1EC_PLUGIN_NAME )?>').appendTo("select[name='action2']");

				});
			</script>
		<?php
		}
	}

	/**
	 * duplicate all selected post
	 *
	 */
	public function duplicate_custom_bulk_action() {

		// duplicate all selected post by top dropdown
		if (
			isset( $_REQUEST['action'] ) &&
			$_REQUEST['action'] == 'clone' &&
			!empty( $_REQUEST['post'] )
		) {
			foreach ( $_REQUEST['post'] as $row ) {
				$this->duplicate_post_create_duplicate(
					get_post( $row )
				);
			}
		}

		// duplicate all selected post by bottom dropdown
		if (
			isset( $_REQUEST['action2'] ) &&
			$_REQUEST['action2'] == 'clone' &&
			!empty( $_REQUEST['post'] )
		) {
			foreach ( $_REQUEST['post'] as $row ) {
				$this->duplicate_post_create_duplicate(
					get_post( $row )
				);
			}
		}

	}
}
// END class
