<?php echo $navigation; ?>
<div class="ai1ec-stream-view">
	<?php if( ! $dates ): ?>
		<p class="ai1ec-no-results">
			<?php _e( 'There are no upcoming events to display at this time.', AI1EC_PLUGIN_NAME ) ?>
		</p>
	<?php else: ?>
		<?php foreach( $dates as $timestamp => $date_info ): ?>
			<div class="ai1ec-date <?php
				if( isset( $date_info['today'] ) && $date_info['today'] ) :
					?>ai1ec-today<?php
				endif; ?>">
				<div class="ai1ec-date-title">
					<a class="ai1ec-load-view" href="<?php echo $date_info['href']; ?>" <?php echo $data_type; ?>>
						<?php if ( $show_year_in_agenda_dates ) : ?>
							<?php echo Ai1ec_Time_Utility::date_i18n(
								'F j, Y (l)',
								$timestamp
							); ?>
						<?php else : ?>
							<?php echo Ai1ec_Time_Utility::date_i18n(
								'F j (l)',
								$timestamp
							); ?>
						<?php endif; ?>
					</a><!--/.ai1ec-load-view-->
				</div><!--/.ai1ec-date-title-->
				<div class="ai1ec-date-events">
					<?php foreach( $date_info['events'] as $category ): ?>
					<?php foreach( $category as $event ): ?>
						<div class="clearfix ai1ec-event
							ai1ec-event-id-<?php echo $event->post_id ?>
							ai1ec-event-instance-id-<?php echo $event->instance_id ?>
							<?php if( $event->allday ) echo 'ai1ec-allday' ?>">
								<div class="ai1ec-event-inner clearfix">
									<?php edit_post_link(
										'<i class="icon-pencil"></i> ' . __( 'Edit', AI1EC_PLUGIN_NAME ),
										'', '', $event->post_id
									); ?>
									<div class="ai1ec-event-title">
										<a class="ai1ec-load-event"
												href="<?php echo esc_attr( get_permalink( $event->post_id ) . $event->instance_id ) ?>"
											 <?php echo $event->get_category_text_color(); ?>
											 <?php echo $data_type_events ?>
											 title="<?php echo esc_attr( apply_filters( 'the_title', $event->post->post_title ) ) ?>" >
											<?php echo esc_html( apply_filters( 'the_title', $event->post->post_title ) ) ?>
										</a>
										<?php if ( $show_location_in_title && isset( $event->venue ) && $event->venue != '' ): ?>
											<span class="ai1ec-event-location"><?php echo sprintf( __( '@ %s', AI1EC_PLUGIN_NAME ), esc_html( $event->venue ) ); ?></span>
										<?php endif; ?>
									</div><!--/.ai1ec-event-title-->

									<div class="ai1ec-event-avatar-wrap pull-left">
										<?php echo $event->get_event_avatar(
											array(
												'post_thumbnail',
												'content_img',
												'category_avatar',
												'default_avatar'
											)
										); ?>
									</div>
									<div class="ai1ec-event-meta">
										<span class="ai1ec-event-time">
											<i class="icon-calendar"></i>
											<?php echo $event->get_timespan_html( 'short' ); ?>
										</span><!--/.ai1ec-event-time-->
										<?php if ( $event->get_categories_html( 'inline' ) ) : ?>
											<span class="ai1ec-categories ai1ec-meta-divide">
												<?php echo $event->get_categories_html( 'inline' ); ?>
											</span>
										<?php endif ?>
										<?php if( $event->get_tags_html() ) : ?>
											<span class="ai1ec-tags ai1ec-meta-divide">
												<?php echo $event->get_tags_html(); ?>
											</span>
										<?php endif ?>
									</div>
									<div class="ai1ec-event-description">
										<?php echo esc_html( $event->get_post_excerpt() ); ?>
									</div>
								</div><!--/.ai1ec-event-inner-->
							</div><!--/.ai1ec-event-->
					<?php endforeach ?>
				<?php endforeach ?>
				</div><!--/.ai1ec-date-events-->
			</div><!--/.ai1ec-date-->
		<?php endforeach ?>
	<?php endif ?>
</div><!--/.ai1ec-stream-view-->
<div class="pull-right"><?php echo $pagination_links; ?></div>
