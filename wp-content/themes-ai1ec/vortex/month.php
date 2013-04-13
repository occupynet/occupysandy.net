<?php echo $navigation; ?>
<table class="ai1ec-month-view ai1ec-popover-boundary">
	<thead>
		<tr>
			<?php foreach( $weekdays as $weekday ): ?>
				<th class="ai1ec-weekday"><?php echo $weekday; ?></th>
			<?php endforeach; // weekday ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach( $cell_array as $week ): ?>
			<tr class="ai1ec-week">
				<?php foreach( $week as $day ): ?>
					<?php if( $day['date'] ): ?>
						<td <?php if( $day['today'] ) echo 'class="ai1ec-today"' ?>>

							<?php
								// TODO: This div should not be needed, but with multi-day
								// event bars it is required until a better method of arranging
								// events is contrived:
							if ( empty( $week['added_stretcher'] ) ) : ?>
								<div class="ai1ec-day-stretcher"></div>
								<?php $week['added_stretcher'] = true; ?>
							<?php endif; ?>

							<div class="ai1ec-day">
								<div class="ai1ec-date"><a class="ai1ec-load-view" <?php echo $data_type; ?> href="<?php echo $day['date_link'] ?>"><?php echo $day['date'] ?></a></div>
								<?php foreach ( $day['events'] as $event ): ?>
									<a href="<?php echo esc_attr( get_permalink( $event->post_id ) ) . $event->instance_id ?>"
										<?php echo $data_type_events; ?>
										<?php if ( $event->get_multiday() ) : ?>
											data-end-day="<?php echo $event->get_multiday_end_day(); ?>"
											data-start-truncated="<?php echo $event->start_truncated ? 'true' : 'false'; ?>"
											data-end-truncated="<?php echo $event->end_truncated ? 'true' : 'false'; ?>"
										<?php endif; ?>
										data-instance-id="<?php echo $event->instance_id; ?>"
										class="ai1ec-event-container ai1ec-load-event ai1ec-popup-trigger
											ai1ec-event-id-<?php echo $event->post_id; ?>
											ai1ec-event-instance-id-<?php echo $event->instance_id; ?>
											<?php if ( $event->allday ) echo 'ai1ec-allday'; ?>
											<?php if ( $event->get_multiday() ) echo 'ai1ec-multiday'; ?>"
										>

										<div class="ai1ec-event"
											style="<?php echo $event->get_color_style(); ?>">
											<span class="ai1ec-event-title"><?php echo esc_html( apply_filters( 'the_title', $event->post->post_title, $event->post_id ) ) ?></span>
											<?php if( ! $event->allday ) : ?>
												<span class="ai1ec-event-time"><?php echo esc_html( $event->get_short_start_time() ) ?></span>
											<?php endif ?>
										</div>
									</a>
									<div class="ai1ec-popup hide">
										<?php if( $event->get_category_colors() ) : ?>
											<div class="ai1ec-category-colors"><?php echo $event->get_category_colors(); ?></div>
										<?php endif ?>

										<span class="ai1ec-popup-title popover-title">
											<a href="<?php echo esc_attr( get_permalink( $event->post_id ) ) . $event->instance_id ?>">
												<?php if( function_exists( 'mb_strimwidth' ) ) : ?>
													<?php echo esc_html( apply_filters( 'the_title', mb_strimwidth( $event->post->post_title, 0, 35, '...' ), $event->post_id ) );
												else : ?>
													<?php $read_more = strlen( $event->post->post_title ) > 35 ? '...' : ''; ?>
													<?php echo esc_html( apply_filters( 'the_title', substr( $event->post->post_title, 0, 35 ) . $read_more, $event->post_id ) );
												endif;
											?></a>
											<?php if ( $show_location_in_title && isset( $event->venue ) && $event->venue != '' ): ?>
												<span class="ai1ec-event-location"><?php echo esc_html( sprintf( __( '@ %s', AI1EC_PLUGIN_NAME ), $event->venue ) ); ?></span>
											<?php endif; ?>
										</span><!--/.span.ai1ec-popup-title-->
										<?php edit_post_link(
											'<i class="icon-pencil"></i> ' . __( 'Edit', AI1EC_PLUGIN_NAME ),
											'', '', $event->post_id
										); ?>

										<div class="ai1ec-event-time"><?php echo $event->get_timespan_html( 'short' ); ?></div>

										<?php
											// Event avatar
											echo $event->get_event_avatar(
												array(
													'post_thumbnail',
													'content_img',
													'location_avatar',
													'category_avatar',
												)
											);
										?>

										<?php if ( $event->get_post_excerpt() ) : ?>
											<div class="ai1ec-popup-excerpt"><?php echo esc_html( $event->get_post_excerpt() ); ?></div>
										<?php endif ?>
									</div><!-- .ai1ec-popup.hide -->
								<?php endforeach // events ?>
							</div><!--/.ai1ec-day-->
						</td>
					<?php else: ?>
						<td class="ai1ec-empty"></td>
					<?php endif // date ?>
				<?php endforeach // day ?>
			</tr><!--/tr.ai1ec-week-->
		<?php endforeach // week ?>
	</tbody>
</table>
