<?php echo $args['before_widget'] ?>

<?php if( $title ): ?>
	<?php echo $before_title . $title . $after_title; ?>
<?php endif; ?>

<div class="timely ai1ec-agenda-widget-view">
<div class="clearfix">

	<?php if( ! $dates ): ?>
		<p class="ai1ec-no-results">
			<?php _e( 'There are no upcoming events.', AI1EC_PLUGIN_NAME ); ?>
		</p>
	<?php else: ?>
		<div>
			<?php foreach( $dates as $timestamp => $date_info ): ?>
				<div class="ai1ec-date
					<?php if ( ! empty( $date_info['today'] ) ) echo 'ai1ec-today'; ?>">
					<a class="ai1ec-date-title ai1ec-load-view" href="<?php echo $date_info['href']; ?>">
						<div class="ai1ec-month"><?php echo Ai1ec_Time_Utility::date_i18n( 'M', $timestamp, true ); ?></div>
						<div class="ai1ec-day"><?php echo Ai1ec_Time_Utility::date_i18n( 'j', $timestamp, true ); ?></div>
						<div class="ai1ec-weekday"><?php echo Ai1ec_Time_Utility::date_i18n( 'D', $timestamp, true ); ?></div>
						<?php if ( $show_year_in_agenda_dates ) : ?>
							<div class="ai1ec-year"><?php echo Ai1ec_Time_Utility::date_i18n( 'Y', $timestamp, true ) ?></div>
						<?php endif; ?>
					</a><!--/.ai1ec-date-title-->
					<div class="ai1ec-date-events">
						<?php foreach( $date_info['events'] as $category ): ?>
							<?php
							foreach ( $category as $event ):
								$full_link = esc_attr(
									get_permalink( $event->post_id ) .
									$event->instance_id
								);
							?>
								<div class="ai1ec-event
									ai1ec-event-id-<?php echo $event->post_id; ?>
									ai1ec-event-instance-id-<?php echo $event->instance_id; ?>
									<?php if( $event->allday ) echo 'ai1ec-allday'; ?>">

									<a href="<?php echo $full_link; ?>"
										class="ai1ec-popup-trigger">
										<?php if ( $event->allday ): ?>
											<span class="ai1ec-allday-badge">
												<?php _e( 'all-day', AI1EC_PLUGIN_NAME ) ?>
											</span>
										<?php else : ?>
											<span class="ai1ec-event-time">
												<?php echo esc_html( $event->get_start_time() ); ?>
											</span>
										<?php endif; ?>
										<span class="ai1ec-event-title">
											<?php echo esc_html( apply_filters( 'the_title', $event->post->post_title, $event->post_id ) ); ?>
											<?php if ( $show_location_in_title && isset( $event->venue ) && $event->venue != '' ): ?>
												<span class="ai1ec-event-location"><?php echo sprintf( __( '@ %s', AI1EC_PLUGIN_NAME ), esc_html( $event->venue ) ); ?></span>
											<?php endif; ?>
										</span>

									</a><!--/.ai1ec-popup-trigger-->
									<div class="ai1ec-popup hide">
										<?php if( $event->get_category_colors() ): ?>
											<div class="ai1ec-color-swatches"><?php echo $event->get_category_colors(); ?></div>
										<?php endif ?>

										<span class="ai1ec-popup-title popover-title">
											<a href="<?php echo $full_link; ?>">
												<?php if ( function_exists( 'mb_strimwidth' ) ) : ?>
													<?php echo esc_html( apply_filters( 'the_title', mb_strimwidth( $event->post->post_title, 0, 35, '...' ), $event->post_id ) );
												else : ?>
													<?php $read_more = strlen( $event->post->post_title ) > 35 ? '...' : ''; ?>
													<?php echo esc_html( apply_filters( 'the_title', substr( $event->post->post_title, 0, 35 ) . $read_more, $event->post_id ) );
												endif;
											?></a>
											<?php if ( $show_location_in_title && isset( $event->venue ) && $event->venue != '' ): ?>
												<span class="ai1ec-event-location"><?php echo esc_html( sprintf( __( '@ %s', AI1EC_PLUGIN_NAME ), $event->venue ) ); ?></span>
											<?php endif; ?>
											<?php if ( $is_ticket_button_enabled && ! empty( $event->ticket_url ) ) : ?>
												<a class="pull-right btn btn-primary btn-mini ai1ec-buy-tickets" target="_blank" href="<?php echo $event->ticket_url; ?>"><?php echo $event->get_tickets_url_label( false ); ?></a>
											<?php endif; ?>
										</span><!--/.span.ai1ec-popup-title-->
										<?php edit_post_link(
											'<i class="icon-pencil"></i> ' . __( 'Edit', AI1EC_PLUGIN_NAME ),
											'', '', $event->post_id
										); ?>

										<div class="ai1ec-event-time"><?php echo $event->get_timespan_html( 'short' ); ?></div>

										<?php // Event avatar ?>
										<?php
											echo $event->get_event_avatar(
												array(
													'post_thumbnail',
													'content_img',
													'location_avatar',
													'category_avatar',
												)
											);
										?>

										<?php if ( $event->get_post_excerpt() ): ?>
											<div class="ai1ec-popup-excerpt"><?php echo esc_html( $event->get_post_excerpt() ) ?></div>
										<?php endif ?>
									</div><!-- .ai1ec-popup -->

								</div>
							<?php endforeach; ?>
						<?php endforeach; ?>
					</div>
				</div><!--/.ai1ec-date-->
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if( $show_calendar_button || $show_subscribe_buttons ): ?>
		<p>
			<?php if( $show_calendar_button ): ?>
				<a class="btn btn-mini pull-right ai1ec-calendar-link" href="<?php echo $calendar_url; ?>">
					<?php _e( 'View Calendar', AI1EC_PLUGIN_NAME ); ?>
					<i class="icon-arrow-right"></i>
				</a>
			<?php endif; ?>

			<?php if( $show_subscribe_buttons ): ?>
				<span class="ai1ec-subscribe-buttons pull-left btn-group">
					<a class="btn btn-mini ai1ec-subscribe ai1ec-tooltip-trigger"
						href="<?php echo $subscribe_url; ?>" data-placement="bottom"
						title="<?php _e( 'Subscribe to this calendar in your personal calendar (iCal, Outlook, etc.)', AI1EC_PLUGIN_NAME ); ?>" />
						<i class="icon-plus"></i>
						<?php _e( 'Add', AI1EC_PLUGIN_NAME ); ?>
					</a>
					<a class="btn btn-mini ai1ec-subscribe-google ai1ec-tooltip-trigger"
						target="_blank" data-placement="bottom"
						href="http://www.google.com/calendar/render?cid=<?php echo urlencode( str_replace( 'webcal://', 'http://', $subscribe_url ) ); ?>"
						title="<?php _e( 'Subscribe to this calendar in your Google Calendar', AI1EC_PLUGIN_NAME ); ?>" />
						<i class="icon-google-plus icon-large"></i>
						<?php _e( 'Add', AI1EC_PLUGIN_NAME ); ?>
					</a>
				</span>
			<?php endif; ?>
		</p>
	<?php endif; ?>

</div><!--/.clearfix-->
</div><!--/.ai1ec-agenda-widget-view-->

<?php echo $args['after_widget']; ?>
