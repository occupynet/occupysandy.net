<?php

/**
 * This class renders the html for the single event page.
 *
 * @author     Time.ly Network Inc.
 * @since      2.0
 *
 * @package    AI1EC
 * @subpackage AI1EC.View.Event
 */
class Ai1ec_View_Event_Single extends Ai1ec_Base {

	/**
	 * Renders the html of the page and returns it.
	 *
	 * @param Ai1ec_Event $event
	 *
	 * @return string the html of the page
	 */
	public function get_content( Ai1ec_Event $event ) {
		$settings = $this->_registry->get( 'model.settings' );
		$rrule    = $this->_registry->get( 'recurrence.rule' );
		$taxonomy = $this->_registry->get( 'view.event.taxonomy' );
		$location = $this->_registry->get( 'view.event.location' );
		$ticket   = $this->_registry->get( 'view.event.ticket' );
		$content  = $this->_registry->get( 'view.event.content' );
		$time     = $this->_registry->get( 'view.event.time' );

		$subscribe_url = AI1EC_EXPORT_URL . '&ai1ec_post_ids=' .
			$event->get( 'post_id' );

		$event->set_runtime(
			'tickets_url_label',
			$ticket->get_tickets_url_label( $event, false )
		);
		$event->set_runtime(
			'content_img_url',
			$content->get_content_img_url( $event )
		);

		$extra_buttons = apply_filters(
			'ai1ec_rendering_single_event_actions',
			'',
			$event
		);

		$venues_html = apply_filters(
			'ai1ec_rendering_single_event_venues',
			nl2br( $location->get_location( $event ) ),
			$event
		);
		$timezone_info = array(
			'show_timezone'       => false,
			'text_timezone_title' => null,
			'event_timezone'      => null,
		);
		$default_tz = $this->_registry->get( 'date.timezone' )
			->get_default_timezone();
		/**
		 * Only display the timezone information if:
		 *     -) local timezone is not enforced -- because if it is enforced
		 *        then site owner knows that it's clear, from event contents,
		 *        where event happens and what time means;
		 *     -) the timezone is different from the site timezone because if
		 *        they do match then it is likely obvious when and wheere the
		 *        event is about to take place.
		 */
		if (
			$this->_registry->get( 'model.settings' )
				->get( 'always_use_calendar_timezone' ) &&
			$event->get( 'timezone_name' ) !== $default_tz
		) {
			$timezone_info = array(
				'show_timezone'       => true,
				'event_timezone'      => $event->get( 'timezone_name' ),
				'text_timezone_title' => sprintf(
					Ai1ec_I18n:: __(
						'Event was created in the %s time zone'
					),
					$event->get( 'start' )->get_gmt_offset_as_text()
				),
			);
		}

		$banner_image_meta = get_post_meta( $event->get( 'post_id' ), 'ai1ec_banner_image' );
		$banner_image = $banner_image_meta ? $banner_image_meta[0] : '';

		// objects are passed by reference so an action is ok
		do_action( 'ai1ec_single_event_page_before_render', $event );

		$args = array(
			'event'                   => $event,
			'recurrence'              => $rrule->rrule_to_text( $event->get( 'recurrence_rules' ) ),
			'exclude'                 => $time->get_exclude_html( $event, $rrule ),
			'categories'              => $taxonomy->get_categories_html( $event ),
			'tags'                    => $taxonomy->get_tags_html( $event ),
			'location'                => $venues_html,
			'map'                     => $location->get_map_view( $event ),
			'contact'                 => $ticket->get_contact_html( $event ),
			'back_to_calendar'        => $content->get_back_to_calendar_button_html(),
			'subscribe_url'           => $subscribe_url,
			'subscribe_url_no_html'   => $subscribe_url . '&no_html=true',
			'edit_instance_url'       => null,
			'edit_instance_text'      => null,
			'google_url'              => 'http://www.google.com/calendar/render?cid=' . urlencode( $subscribe_url ),
			'show_subscribe_buttons'  => ! $settings->get( 'turn_off_subscription_buttons' ),
			'hide_featured_image'     => $settings->get( 'hide_featured_image' ),
			'extra_buttons'           => $extra_buttons,
			'show_get_calendar'       => ! $settings->get( 'disable_get_calendar_button' ),
			'text_add_calendar'       => __( 'Add to Calendar', AI1EC_PLUGIN_NAME ),
			'subscribe_buttons_text'  => $this->_registry
				->get( 'view.calendar.subscribe-button' )
				->get_labels(),
			'text_get_calendar'       => Ai1ec_I18n::__( 'Get a Timely Calendar' ),
			'text_when'               => __( 'When:', AI1EC_PLUGIN_NAME ),
			'text_where'              => __( 'Where:', AI1EC_PLUGIN_NAME ),
			'text_cost'               => __( 'Cost:', AI1EC_PLUGIN_NAME ),
			'text_contact'            => __( 'Contact:', AI1EC_PLUGIN_NAME ),
			'text_free'               => __( 'Free', AI1EC_PLUGIN_NAME ),
			'text_categories'         => __( 'Categories', AI1EC_PLUGIN_NAME ),
			'text_tags'               => __( 'Tags', AI1EC_PLUGIN_NAME ),
			'timezone_info'           => $timezone_info,
			'banner_image'            => $banner_image,
			'content_img_url'         => $event->get_runtime( 'content_img_url' ),
			'post_id'                 => $event->get( 'post_id' ),
			'ticket_url'              => $event->get( 'ticket_url' ),
			'tickets_url_label'       => $event->get_runtime( 'tickets_url_label' ),
			'start'                   => $event->get( 'start' ),
			'end'                     => $event->get( 'end' ),
			'cost'                    => $event->get( 'cost' ),
			'instance_id'             => $event->get( 'instance_id' ),
		);

		if (
			! empty( $args['recurrence'] ) &&
			$event->get( 'instance_id' ) &&
			current_user_can( 'edit_ai1ec_events' )
		) {
			$args['edit_instance_url']  = ai1ec_admin_url(
				'post.php?post=' . $event->get( 'post_id' ) .
				'&action=edit&instance=' . $event->get( 'instance_id' )
			);
			$args['edit_instance_text'] = sprintf(
				Ai1ec_I18n::__( 'Edit this occurrence (%s)' ),
				$event->get( 'start' )->format_i18n( 'M j' )
			);
		}
		$loader = $this->_registry->get( 'theme.loader' );
		return $loader->get_file( 'event-single.twig', $args, false )
			->get_content();
	}

	/**
	 * @param Ai1ec_Event $event
	 *
	 * @return The html of the footer
	 */
	public function get_footer( Ai1ec_Event $event ) {
		$text_calendar_feed = Ai1ec_I18n::__(
			'This post was replicated from another site\'s <a href="%s" title="iCalendar feed"><i class="ai1ec-fa ai1ec-fa-calendar"></i> calendar feed</a>.'
		);
		$loader = $this->_registry->get( 'theme.loader' );
		$text_calendar_feed = sprintf(
			$text_calendar_feed,
			esc_attr( str_replace( 'http://', 'webcal://', $event->get( 'ical_feed_url' ) ) )
		);
		$args   = array(
			'event'              => $event,
			'text_calendar_feed' => $text_calendar_feed,
			'text_view_post'     => Ai1ec_I18n::__( 'View original' ),
		);
		return $loader->get_file( 'event-single-footer.twig', $args, false )
			->get_content();
	}

	/**
	 * Render the full article for the event – title, content, and footer.
	 *
	 * @param Ai1ec_Event $event
	 * @param string      $footer Footer HTML to append to event
	 */
	public function get_full_article( Ai1ec_Event $event, $footer = '' ) {
		$title         = apply_filters(
			'the_title',
			$event->get( 'post' )->post_title,
			$event->get( 'post_id' )
		);
		$event_details = $this->get_content( $event );
		$content       = wpautop(
			apply_filters(
				'ai1ec_the_content',
				apply_filters(
					'the_content',
					$event->get( 'post' )->post_content
				)
			)
		);
		$args = compact( 'title', 'event_details', 'content', 'footer' );
		$loader = $this->_registry->get( 'theme.loader' );
		return $loader->get_file( 'event-single-full.twig', $args, false )
			->get_content();
	}

}
