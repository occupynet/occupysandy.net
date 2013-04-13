<?php
//
//  class-ai1ec-settings-helper.php
//  all-in-one-event-calendar
//
//  Created by The Seed Studio on 2011-07-13.
//

/**
 * Ai1ec_Settings_Helper class
 *
 * @package Helpers
 * @author time.ly
 **/
class Ai1ec_Settings_Helper {
	/**
	 * _instance class variable
	 *
	 * Class instance
	 *
	 * @var null | object
	 **/
	private static $_instance = NULL;

	/**
	 * get_instance function
	 *
	 * Return singleton instance
	 *
	 * @return object
	 **/
	static function get_instance() {
		if( self::$_instance === NULL ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Constructor
	 *
	 * Default constructor
	 **/
	private function __construct() { }

	/**
	 * wp_pages_dropdown function
	 *
	 * Display drop-down list selector of pages, including an "Auto-Create New Page"
	 * option which causes the plugin to generate a new page on user's behalf.
	 *
	 * @param string $field_name
	 * @param int  $selected_page_id
	 * @param string $auto_page
	 * @param bool $include_disabled
	 *
	 * @return string
	 **/
	function wp_pages_dropdown( $field_name, $selected_page_id = 0, $auto_page = '', $include_disabled = false ) {
		global $wpdb;
		ob_start();
		$query = "SELECT
								*
							FROM
								{$wpdb->posts}
							WHERE
								post_status = %s
								AND
								post_type = %s";

		$query = $wpdb->prepare( $query, 'publish', 'page' );
		$results = $wpdb->get_results( $query );
		$pages = array();
		if( $results ) {
			$pages = $results;
		}
		$selected_title = '';
		?>
		<select class="inputwidth" name="<?php echo $field_name; ?>"
						id="<?php echo $field_name; ?>"
						class="wafp-dropdown wafp-pages-dropdown">
			<?php if( ! empty( $auto_page ) ) { ?>
				<option value="__auto_page:<?php echo $auto_page; ?>">
					<?php _e( '- Auto-Create New Page -', AI1EC_PLUGIN_NAME ); ?>
				</option>
			<?php }
			foreach( $pages as $page ) {
				if( $selected_page_id == $page->ID ) {
					$selected = ' selected="selected"';
					$selected_title = $page->post_title;
				} else {
					$selected = '';
				}
				?>
				<option value="<?php echo $page->ID ?>" <?php echo $selected; ?>>
					<?php echo $page->post_title ?>
				</option>
			<?php } ?>
			</select>
		<?php
		if( is_numeric( $selected_page_id ) && $selected_page_id > 0 ) {
			$permalink = get_permalink( $selected_page_id );
			?>
			<p><a href="<?php echo $permalink ?>" target="_blank">
				<?php printf( __( 'View "%s"', AI1EC_PLUGIN_NAME ), $selected_title ) ?>
				<i class="icon-arrow-right"></i>
			</a></p>
			<?php
		}
		return ob_get_clean();
	}

	/**
	 * get_week_dropdown function
	 *
	 * Creates the dropdown element for selecting start of the week
	 *
	 * @param int $week_start_day Selected start day
	 *
	 * @return String dropdown element
	 **/
	function get_week_dropdown( $week_start_day ) {
		global $wp_locale;
		ob_start();
		?>
		<select class="inputwidth" name="week_start_day" id="week_start_day">
		<?php
		for( $day_index = 0; $day_index <= 6; $day_index++ ) :
			$selected = ( $week_start_day == $day_index ) ? 'selected="selected"' : '';
			echo "\n\t<option value='" . esc_attr($day_index) . "' $selected>" . $wp_locale->get_weekday($day_index) . '</option>';
		endfor;
		?>
		</select>
		<?php
		return ob_get_clean();
	}

	/**
	 * get_view_options function
	 *
	 * @return void
	 **/
	function get_view_options( $view = null ) {
		global $ai1ec_settings,
		       $ai1ec_app_helper;

		ob_start();
		?>
		<div>
			<table>
				<tbody>
					<tr class="ai1ec-admin-view-head">
						<td></td>
						<td>Enabled</td>
						<td>Default</td>
					</tr>
					<?php foreach ( $ai1ec_app_helper->view_names() as $key => $name ) {
						$this_view_bool = 'view_' . $key . '_enabled';
						$is_view_enabled = $ai1ec_settings->$this_view_bool;
						?>
						<tr>
							<td>
								<?php _e( $name ) ?>
							</td>
							<td class="ai1ec-control-table-column">
								<input class="checkbox toggle-view" type="checkbox" name="<?php echo $this_view_bool ?>" value="1"
									<?php echo $is_view_enabled ? 'checked="checked"' : ''; ?> />
							</td>
							<td class="ai1ec-control-table-column">
								<input class="toggle-default-view" type="radio" name="default_calendar_view" value="<?php echo $key ?>"
									<?php if ( $ai1ec_settings->default_calendar_view == $key ) : echo 'checked="checked"'; endif; ?>>
							</td>
						</tr>
					<?php } ?>

				</tbody>
			</table>

		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * get_timezone_dropdown function
	 *
	 *
	 *
	 * @return void
	 **/
	function get_timezone_dropdown( $timezone = null ) {
		$timezone_identifiers = DateTimeZone::listIdentifiers();
		ob_start();
		?>
		<select id="timezone" name="timezone">
			<?php foreach( $timezone_identifiers as $value ) : ?>
				<?php if( preg_match( '/^(Africa|America|Antartica|Arctic|Asia|Atlantic|Australia|Europe|Indian|Pacific)\//', $value ) ) : ?>
					<?php $ex = explode( "/", $value );  //obtain continent,city ?>
					<?php if( isset( $continent ) && $continent != $ex[0] ) : ?>
						<?php if( ! empty( $continent ) ) : ?>
							</optgroup>
						<?php endif ?>
						<optgroup label="<?php echo $ex[0] ?>">
					<?php endif ?>

					<?php $city = isset( $ex[2] ) ? $ex[2] : $ex[1]; $continent = $ex[0]; ?>
					<option value="<?php echo $value ?>" <?php echo $value == $timezone ? 'selected' : '' ?>><?php echo $city ?></option>
				<?php endif ?>
			<?php endforeach ?>
			</optgroup>
		</select>
		<?php
		return ob_get_clean();
	}

	/**
	* get_date_format_dropdown function
	*
	* @return string
	**/
	function get_date_format_dropdown( $view = null ) {
		$formats = array(
			'def' => __( 'Default (d/m/yyyy)', AI1EC_PLUGIN_NAME ),
			'us' => __( 'US (m/d/yyyy)', AI1EC_PLUGIN_NAME ),
			'iso' => __( 'ISO 8601 (yyyy-m-d)', AI1EC_PLUGIN_NAME ),
			'dot' => __( 'Dotted (m.d.yyyy)', AI1EC_PLUGIN_NAME ),
		);
		$patterns = Ai1ec_Time_Utility::get_date_patterns();

		$html = '<select name="input_date_format" id="input_date_format">';
		foreach ( $formats as $key => $label ) {
			$html .= '<option value="' . $key . '"';
			$html .= ' data-pattern="' . $patterns[$key] . '"';
			if ( $view == $key ) {
				$html .= ' selected="selected"';
			}
			$html .= '>' . $label . '</option>';
		}
		$html .= '</select>';

		return $html;
	}

	/**
	 * get_cron_freq_dropdown function
	 *
	 * @return void
	 **/
	function get_cron_freq_dropdown( $cron_freq = null ) {
		ob_start();
		?>
		<select name="cron_freq">
			<option value="hourly" <?php echo $cron_freq == 'hourly' ? 'selected' : ''; ?>>
				<?php _e( 'Hourly', AI1EC_PLUGIN_NAME ) ?>
			</option>
			<option value="twicedaily" <?php echo $cron_freq == 'twicedaily' ? 'selected' : '' ?>>
				<?php _e( 'Twice Daily', AI1EC_PLUGIN_NAME ) ?>
			</option>
			<option value="daily" <?php echo $cron_freq == 'daily' ? 'selected' : '' ?>>
				<?php _e( 'Daily', AI1EC_PLUGIN_NAME ) ?>
			</option>
		</select>
		<?php
		return ob_get_clean();
	}

	/**
	 * get_feed_rows function
	 *
	 * Creates feed rows to display on settings page
	 *
	 * @return String feed rows
	 **/
	function get_feed_rows() {
		global $wpdb,
		       $ai1ec_view_helper;

		// Select all added feeds
		$rows = $wpdb->get_results(
			'SELECT * FROM ' . $wpdb->prefix . 'ai1ec_event_feeds'
		);

		ob_start();
		$sql = 'SELECT COUNT(*) FROM ' . $wpdb->prefix .
		       'ai1ec_events WHERE ical_feed_url = %s';
		foreach ( $rows as $row ) {
			$feed_category = get_term( $row->feed_category, 'events_categories' );
			$events        = $wpdb->get_var( $wpdb->prepare( $sql, $row->feed_url ) );
			$args          = array(
				'feed_url'            => $row->feed_url,
				'event_category'      => $feed_category->name,
				'tags'                => stripslashes( $row->feed_tags ),
				'feed_id'             => $row->feed_id,
				'comments_enabled'    => (bool)intval(
					$row->comments_enabled
				),
				'map_display_enabled' => (bool)intval(
					$row->map_display_enabled
				),
				'events'              => $events,
			);
			$ai1ec_view_helper->display_admin( 'feed_row.php', $args );
		}

		return ob_get_clean();
	}

	/**
	 * get_event_categories_select function
	 *
	 * Creates the dropdown element for selecting feed category
	 *
	 * @param int|null $selected The selected category or null
	 *
	 * @return String dropdown element
	 **/
	function get_event_categories_select( $selected = null) {
		ob_start();
		?>
		<select name="ai1ec_feed_category" id="ai1ec_feed_category">
		<?php
		foreach( get_terms( 'events_categories', array( 'hide_empty' => false ) ) as $term ) :
		?>
			<option value="<?php echo $term->term_id; ?>" <?php echo ( $selected === $term->term_id ) ? 'selected' : '' ?>>
				<?php echo $term->name; ?>
			</option>
		<?php
		endforeach;
		?>
		</select>
		<?php
		return ob_get_clean();
	}


	/**
	 * Displays the General Settings meta box.
	 *
	 * @return void
	 */
	public function general_settings_meta_box( $object, $box ) {
		global $ai1ec_view_helper,
					 $ai1ec_settings;

		$calendar_page                  = $this->wp_pages_dropdown(
			'calendar_page_id',
			$ai1ec_settings->calendar_page_id,
			__( 'Calendar', AI1EC_PLUGIN_NAME )
		);
		$week_start_day_val             = Ai1ec_Meta::get_option( 'start_of_week' );
		$week_start_day                 = $this->get_week_dropdown( $week_start_day_val );
		$exact_date                     = $ai1ec_settings->exact_date;
		$posterboard_events_per_page    = $ai1ec_settings->posterboard_events_per_page;
		$posterboard_tile_min_width     = $ai1ec_settings->posterboard_tile_min_width;
		$stream_events_per_page         = $ai1ec_settings->stream_events_per_page;
		$agenda_events_per_page         = $ai1ec_settings->agenda_events_per_page;
		$agenda_include_entire_last_day = $ai1ec_settings->agenda_include_entire_last_day
			? 'checked="checked"'
			: '';
		$include_events_in_rss          =
			'<input type="checkbox" name="include_events_in_rss"' .
			' id="include_events_in_rss" value="1"' .
			(
				$ai1ec_settings->include_events_in_rss
				? ' checked="checked"'
				: ''
			) .
			'/>';
		$exclude_from_search            = $ai1ec_settings->exclude_from_search ? 'checked="checked"' : '';
		$show_publish_button            = $ai1ec_settings->show_publish_button ? 'checked="checked"' : '';
		$hide_maps_until_clicked        = $ai1ec_settings->hide_maps_until_clicked ? 'checked="checked"' : '';
		$agenda_events_expanded         = $ai1ec_settings->agenda_events_expanded ? 'checked="checked"' : '';
		$turn_off_subscription_buttons  = $ai1ec_settings->turn_off_subscription_buttons ? 'checked="checked"' : '';
		$show_create_event_button       = $ai1ec_settings->show_create_event_button ? 'checked="checked"' : '';
		$show_front_end_create_form     = $ai1ec_settings->show_front_end_create_form ? 'checked="checked"' : '';
		$allow_anonymous_submissions    = $ai1ec_settings->allow_anonymous_submissions ? 'checked="checked"' : '';
		$allow_anonymous_uploads        = $ai1ec_settings->allow_anonymous_uploads ? 'checked="checked"' : '';
		$show_add_calendar_button       = $ai1ec_settings->show_add_calendar_button ? 'checked="checked"' : '';
		$recaptcha_public_key           = $ai1ec_settings->recaptcha_public_key;
		$recaptcha_private_key          = $ai1ec_settings->recaptcha_private_key;
		$inject_categories              = $ai1ec_settings->inject_categories ? 'checked="checked"' : '';
		$geo_region_biasing             = $ai1ec_settings->geo_region_biasing ? 'checked="checked"' : '';
		$input_date_format              = $this->get_date_format_dropdown( $ai1ec_settings->input_date_format );
		$input_24h_time                 = $ai1ec_settings->input_24h_time ? 'checked="checked"' : '';
		$default_calendar_view          = $this->get_view_options( $ai1ec_settings->default_calendar_view );
		$timezone_control               = $this->get_timezone_dropdown( $ai1ec_settings->timezone );
		$disable_autocompletion         = $ai1ec_settings->disable_autocompletion ? 'checked="checked"' : '';
		$show_location_in_title         = $ai1ec_settings->show_location_in_title ? 'checked="checked"' : '';
		$show_year_in_agenda_dates      = $ai1ec_settings->show_year_in_agenda_dates ? 'checked="checked"' : '';

		$tax_options         = array(
			'type'     => 'categories',
			'selected' => $ai1ec_settings->default_categories,
		);
		$default_categories  = $this->taxonomy_selector( $tax_options );
		$tax_options         = array(
			'type'     => 'tags',
			'selected' => $ai1ec_settings->default_tags,
		);
		$default_tags        = $this->taxonomy_selector( $tax_options );

		$show_timezone       = $ai1ec_settings->is_timezone_open_for_change();

		$date_format_pattern = Ai1ec_Time_Utility::get_date_pattern_by_key(
			$ai1ec_settings->input_date_format
		);

		$skip_in_the_loop_check         = $ai1ec_settings->skip_in_the_loop_check ? 'checked="checked"' : '';
		$ajaxify_events_in_web_widget   = $ai1ec_settings->ajaxify_events_in_web_widget ? 'checked="checked"' : '';
		$event_platform                 = $ai1ec_settings->event_platform_active ? 'checked="checked"' : '';
		$event_platform_disabled        = AI1EC_EVENT_PLATFORM ? 'disabled="disabled"' : '';
		$event_platform_strict          = $ai1ec_settings->event_platform_strict ? 'checked="checked"' : '';

		$args = array(
			'calendar_page'                  => $calendar_page,
			'default_calendar_view'          => $default_calendar_view,
			'week_start_day_val'             => $week_start_day_val,
			'week_start_day'                 => $week_start_day,
			'default_categories'             => $default_categories,
			'default_tags'                   => $default_tags,
			'exact_date'                     => $exact_date,
			'posterboard_events_per_page'    => $posterboard_events_per_page,
			'posterboard_tile_min_width'     => $posterboard_tile_min_width,
			'stream_events_per_page'         => $stream_events_per_page,
			'agenda_events_per_page'         => $agenda_events_per_page,
			'agenda_include_entire_last_day' => $agenda_include_entire_last_day,
			'exclude_from_search'            => $exclude_from_search,
			'show_publish_button'            => $show_publish_button,
			'hide_maps_until_clicked'        => $hide_maps_until_clicked,
			'agenda_events_expanded'         => $agenda_events_expanded,
			'turn_off_subscription_buttons'  => $turn_off_subscription_buttons,
			'show_create_event_button'       => $show_create_event_button,
			'show_front_end_create_form'     => $show_front_end_create_form,
			'allow_anonymous_submissions'    => $allow_anonymous_submissions,
			'allow_anonymous_uploads'        => $allow_anonymous_uploads,
			'show_add_calendar_button'       => $show_add_calendar_button,
			'recaptcha_public_key'           => $recaptcha_public_key,
			'recaptcha_private_key'          => $recaptcha_private_key,
			'inject_categories'              => $inject_categories,
			'input_date_format'              => $input_date_format,
			'input_24h_time'                 => $input_24h_time,
			'show_timezone'                  => $show_timezone,
			'timezone_control'               => $timezone_control,
			'geo_region_biasing'             => $geo_region_biasing,
			'disable_autocompletion'         => $disable_autocompletion,
			'show_location_in_title'         => $show_location_in_title,
			'show_year_in_agenda_dates'      => $show_year_in_agenda_dates,
			'date_format_pattern'            => $date_format_pattern,
			'calendar_css_selector'          => $ai1ec_settings->calendar_css_selector,
			'skip_in_the_loop_check'         => $skip_in_the_loop_check,
			'ajaxify_events_in_web_widget'   => $ajaxify_events_in_web_widget,
			'event_platform'                 => $event_platform,
			'event_platform_disabled'        => $event_platform_disabled,
			'event_platform_strict'          => $event_platform_strict,
			'display_event_platform'         => is_super_admin(),
			'user_mail_subject'              => $ai1ec_settings->user_mail_subject,
			'user_mail_body'                 => $ai1ec_settings->user_mail_body,
			'admin_mail_subject'             => $ai1ec_settings->admin_mail_subject,
			'admin_mail_body'                => $ai1ec_settings->admin_mail_body,
			'license_key'                    => $ai1ec_settings->license_key,
		);
		$ai1ec_view_helper->display_admin( 'box_general_settings.php', $args );
	}


	/**
	 * taxonomy_selector method
	 *
	 * Get HTML selector for AI1EC custom taxonomy object.
	 *
	 * @param string|array $options Name of taxonomy (categories/tags) or list
	 *     of options to customize selector.
	 *
	 * @return string HTML to use for selection
	 */
	public function taxonomy_selector( $options = array() ) {
		$type = 'categories';
		if ( ! is_array( $options ) ) {
			$type    = (string)$options;
			$options = array();
		} elseif ( isset( $options['type'] ) ) {
			$type    = $options['type'];
		}
		$options = array_merge( array(
			'taxonomy'     => 'events_' . $type,
			'hierarchical' => true,
			'id'           => 'default_' . $type,
			'name'         => 'default_' . $type,
			'class'        => 'inputwidth',
			'selected'     => array(),
			'show_count'   => true,
			'multiple'     => true,
		), $options );
		if (
			$options['multiple'] &&
			'[]' !== substr( $options['name'], -2 )
		) {
			$options['name'] .= '[]';
		}
		if ( ! is_array( $options['selected'] ) ) {
			$options['selected'] = array( (int)$options['selected'] );
		}
		$taxonomy_items = get_categories( $options );
		if ( empty( $taxonomy_items ) ) {
			return NULL;
		}
		$result_html	= "\t" . '<select name="' . $options['name'] . '"' .
			( ($options['multiple']) ? ' multiple="multiple"' : '') .
			' id="' . $options['id'] . '" class="' .
			$options['class'] . '">' . "\n";
		$option_format  = "\t\t" . '<option value="%s"%s>%s</option>' . "\n";
		foreach ( $taxonomy_items as $taxonomy ) {
			$selected = '';
			if ( in_array( $taxonomy->term_id, $options['selected'] ) ) {
				$selected = ' selected="selected"';
			}
			$display = $taxonomy->name;
			if ( $options['show_count'] ) {
				$display .= '&nbsp;&nbsp;(' . $taxonomy->count . ')';
			}
			$result_html .= sprintf(
				$option_format,
				$taxonomy->term_id,
				$selected,
				$display
			);
		}
		$result_html .= "\t" . '</select>';
		return $result_html;
	}

	/**
	 * Renders the contents of the Calendar Feeds meta box.
	 *
	 * @return void
	 */
	function feeds_meta_box( $object, $box )
	{
		global $ai1ec_view_helper;

		$ai1ec_view_helper->display_admin( 'box_feeds.php' );
	}
	/**
	 * Renders the contents of the Support meta box.
	 *
	 * @return void
	 */
	function support_meta_box( $object, $box ) {
		global $ai1ec_view_helper, $ai1ec_settings;
		include_once( ABSPATH . WPINC . '/feed.php' );
		// Initialize new feed
		$newsItems = array();
		$feed      = fetch_feed( AI1EC_RSS_FEED );
		$newsItems = is_wp_error( $feed ) ? array() : $feed->get_items( 0, 5 );
		$ai1ec_view_helper->display_admin(
			'box_support.php',
			array(
				'news'               => $newsItems,
				'license_status_url' => AI1EC_LICENSE_STATUS_JS . $ai1ec_settings->license_key,
			)
		);
	}

	/**
	 * This is called when the settings page is loaded, so that any additional
	 * custom meta boxes can be added by other plugins, themes, etc.
	 *
	 * @return void
	 */
	function add_settings_meta_boxes(){
		global $ai1ec_settings;
		do_action( 'add_meta_boxes', $ai1ec_settings->settings_page );
	}

	/**
	 * This is called when the feeds page is loaded, so that any additional
	 * custom meta boxes can be added by other plugins, themes, etc.
	 *
	 * @return void
	 */
	function add_feeds_meta_boxes(){
		global $ai1ec_settings;
		do_action( 'add_meta_boxes', $ai1ec_settings->feeds_page );
	}
}
// END class
