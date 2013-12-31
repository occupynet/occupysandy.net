<?php echo $navigation; ?>
<div class="ai1ec-agenda-view">
	<?php if( ! $dates ): ?>
		<p class="ai1ec-no-results">
			<?php _e( 'There are no upcoming events to display at this time.', AI1EC_PLUGIN_NAME ) ?>
		</p>
	<?php else: ?>
		<?php foreach( $dates as $timestamp => $date_info ): ?>
			<div class="ai1ec-date
				<?php if ( ! empty( $date_info['today'] ) ) echo 'ai1ec-today'; ?>">
				<a class="ai1ec-date-title ai1ec-load-view" href="<?php echo $date_info['href']; ?>" <?php echo $data_type; ?>>
					<div class="ai1ec-month"><?php echo Ai1ec_Time_Utility::date_i18n( 'M', $timestamp, true ) ?></div>
					<div class="ai1ec-day"><?php echo Ai1ec_Time_Utility::date_i18n( 'j', $timestamp, true ) ?></div>
					<div class="ai1ec-weekday"><?php echo Ai1ec_Time_Utility::date_i18n( 'D', $timestamp, true ) ?></div>
					<?php if ( $show_year_in_agenda_dates ) : ?>
						<div class="ai1ec-year"><?php echo Ai1ec_Time_Utility::date_i18n( 'Y', $timestamp, true ) ?></div>
					<?php endif; ?>
				</a><!--/.ai1ec-date-title-->
				<div class="ai1ec-date-events">
					<?php foreach( $date_info['events'] as $category ): ?>
						<?php foreach( $category as $event ): ?>
							<div class="ai1ec-event
								ai1ec-event-id-<?php echo $event->post_id ?>
								ai1ec-event-instance-id-<?php echo $event->instance_id ?>
								<?php if( $event->allday ) echo 'ai1ec-allday' ?>
								<?php if( $expanded ) echo 'ai1ec-expanded' ?>">

								<div class="ai1ec-event-header">
									<div class="ai1ec-event-toggle">
										<i class="icon-minus-sign icon-large"></i>
										<i class="icon-plus-sign icon-large"></i>
									</div><!--/.ai1ec-event-toggle-->
									<span class="ai1ec-event-title">
										<?php echo esc_html( apply_filters( 'the_title', $event->post->post_title, $event->post_id ) ) ?>
										<?php if ( $show_location_in_title && isset( $event->venue ) && $event->venue != '' ): ?>
											<span class="ai1ec-event-location"><?php echo sprintf( __( '@ %s', AI1EC_PLUGIN_NAME ), esc_html( $event->venue ) ); ?></span>
										<?php endif; ?>
									</span><!--/.ai1ec-event-title-->
									<?php edit_post_link(
										'<i class="icon-pencil"></i> ' . __( 'Edit', AI1EC_PLUGIN_NAME ),
										'', '', $event->post_id
									); ?>
									<div class="ai1ec-event-time">
										<?php echo $event->get_timespan_html( 'short' ); ?>
									</div><!--/.ai1ec-event-time-->
								</div><!--/.ai1ec-event-header-->

								<?php // Hidden summary, until clicked ?>
								<div class="ai1ec-event-summary<?php if( $expanded ) echo ' ai1ec-expanded'; ?>">

									<div class="ai1ec-event-description">
										<?php
										if ( ! $event->get_content_img_url() ) {
											echo $event->get_event_avatar(
												array(
													'post_thumbnail',
													'location_avatar',
													'category_avatar'
												),
												'alignleft'
											);
										}
										?>
										<?php echo apply_filters( 'the_content', $event->post->post_content ) ?>
									</div><!--/.ai1ec-event-description-->

									<div class="ai1ec-event-summary-footer">
										<div class="btn-group ai1ec-actions">
											<?php if ( $is_ticket_button_enabled && ! empty( $event->ticket_url ) ) : ?>
												<a class="btn btn-primary ai1ec-buy-tickets" target="_blank" href='<?php echo $event->ticket_url; ?>'><?php echo $event->get_tickets_url_label(); ?></a>
											<?php endif; ?>
											<a <?php echo $data_type_events; ?> class="ai1ec-read-more btn ai1ec-load-event"
												href="<?php echo esc_attr( get_permalink( $event->post_id ) . $event->instance_id ); ?>">
												<?php _e( 'Read more', AI1EC_PLUGIN_NAME ); ?> <i class="icon-arrow-right"></i>
											</a><!--/.ai1ec-read-more-->
										</div>
										<?php if ( $event->get_categories_html() ) : ?>
											<span class="ai1ec-categories">
												<span class="ai1ec-label">
													<i class="icon-folder-open"></i>
													<?php _e( 'Categories:', AI1EC_PLUGIN_NAME ); ?>
												</span>
												<?php echo $event->get_categories_html(); ?>
											</span><!--/.ai1ec-event-categories-->
										<?php endif ?>
										<?php if ( $event->get_tags_html() ) : ?>
											<span class="ai1ec-tags">
												<span class="ai1ec-label">
													<i class="icon-tags"></i>
													<?php _e( 'Tags:', AI1EC_PLUGIN_NAME ); ?>
												</span>
												<?php echo $event->get_tags_html(); ?>
											</span>
										<?php endif ?>
									</div>
								</div><!--/.ai1ec-event-summary-->

							</div><!--/.ai1ec-event-->
						<?php endforeach ?>
					<?php endforeach ?>
				</div><!--/.ai1ec-date-events-->
			</div><!--/.ai1ec-date-->
		<?php endforeach ?>
	<?php endif ?>
</div><!--/.ai1ec-agenda-view-->
<div class="pull-right"><?php echo $pagination_links; ?></div>
