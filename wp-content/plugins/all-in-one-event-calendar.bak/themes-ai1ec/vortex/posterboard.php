<?php echo $navigation; ?>
<div class="ai1ec-posterboard-view"
	data-ai1ec-tile-min-width="<?php echo $tile_min_width; ?>">
	<?php if( ! $dates ): ?>
		<p class="ai1ec-no-results">
			<?php _e( 'There are no upcoming events to display at this time.', AI1EC_PLUGIN_NAME ) ?>
		</p>
	<?php else: ?>
		<?php foreach ( $dates as $timestamp => $date_info ) : ?>
			<?php foreach ( $date_info['events'] as $category ) : ?>
				<?php foreach ( $category as $event ) : ?>
					<div class="ai1ec-event
						ai1ec-event-id-<?php echo $event->post_id; ?>
						ai1ec-event-instance-id-<?php echo $event->instance_id; ?>
						<?php if( $event->allday ) echo 'ai1ec-allday'; ?>">
						<div class="ai1ec-event-wrap clearfix">
							<div class="ai1ec-date-block-wrap" <?php echo $event->get_category_bg_color(); ?>>
								<a class="ai1ec-load-view" href="<?php echo $date_info['href']; ?>" <?php echo $data_type; ?> >
									<span class="ai1ec-month"><?php echo Ai1ec_Time_Utility::date_i18n( 'M', $timestamp, true ) ?></span>
									<span class="ai1ec-day"><?php echo Ai1ec_Time_Utility::date_i18n( 'j', $timestamp, true ) ?></span>
								</a><!--/.ai1ec-load-view-->
							</div><!--/.ai1ec-date-block-wrap-->
							<?php edit_post_link(
								'<i class="icon-pencil"></i> ' . __( 'Edit', AI1EC_PLUGIN_NAME ),
								'', '', $event->post_id
							); ?>
							<div class="ai1ec-event-title-wrap">
								<span class="ai1ec-event-title">
									<a class="ai1ec-load-event"
									    href="<?php echo esc_attr( get_permalink( $event->post_id ) . $event->instance_id ) ?>"
									   <?php echo $event->get_category_text_color(); ?>
									   <?php echo $data_type_events ?>
									   title="<?php echo esc_attr( apply_filters( 'the_title', $event->post->post_title, $event->post_id ) ) ?>" >
										<?php echo esc_html( apply_filters( 'the_title', $event->post->post_title, $event->post_id ) ) ?>
									</a>
									<?php if ( $show_location_in_title && isset( $event->venue ) && $event->venue != '' ): ?>
										<span class="ai1ec-event-location"><?php echo sprintf( __( '@ %s', AI1EC_PLUGIN_NAME ), $event->venue ); ?></span>
									<?php endif; ?>
								</span><!--/.ai1ec-event-title-->
								<div class="ai1ec-event-time">
									<?php echo $event->get_timespan_html( 'weekday' ); ?>
								</div>
							</div>
							<div class="clearfix">
								<?php
									echo $event->get_event_avatar( array(
										'post_thumbnail',
										'content_img',
										'location_avatar',
										'category_avatar',
									) );
								?>
								<?php if ( $event->get_post_excerpt() ) : ?>
									<div class="ai1ec-event-description">
										<?php echo esc_html( $event->get_post_excerpt() ); ?>
									</div>
								<?php endif; ?>
							</div>
							<?php if ( $event->get_categories_html() || $event->get_tags_html() ) : ?>
								<footer>
									<div>
										<?php if ( $event->get_categories_html() ) : ?>
											<span class="ai1ec-categories">
												<?php echo $event->get_categories_html(); ?>
											</span>
										<?php endif; ?>
										<?php if ( $event->get_tags_html() ) : ?>
											<span class="ai1ec-tags">
												<?php echo $event->get_tags_html(); ?>
											</span>
										<?php endif; ?>
									</div>
								</footer>
							<?php endif; ?>
						</div><!--/.ai1ec-event-wrap-->
					</div><!--/.ai1ec-event-->
				<?php endforeach ?>
			<?php endforeach ?>
		<?php endforeach ?>
	<?php endif ?>
</div><!--/.ai1ec-posterboard-view-->
<div class="pull-right"><?php echo $pagination_links; ?></div>
