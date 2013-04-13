<?php echo $navigation; ?>
<table class="ai1ec-oneday-view-original">
	<thead>
		<tr>
			<?php foreach( $cell_array as $date => $day ): ?>
				<th class="ai1ec-weekday <?php if( $day['today'] ) echo 'ai1ec-today' ?>">
					<span class="ai1ec-weekday-date"><?php
					echo Ai1ec_Time_Utility::date_i18n( 'l', $date, true );
					?> </span>
				</th>
			<?php endforeach; // weekday ?>
		</tr>
		<tr>
			<?php foreach( $cell_array as $day ) : ?>
				<td class="ai1ec-allday-events <?php if( $day['today'] ) echo 'ai1ec-today' ?>">

					<?php if( ! $done_allday_label ) : ?>
						<div class="ai1ec-allday-label"><?php _e( 'All-day', AI1EC_PLUGIN_NAME ) ?></div>
						<?php $done_allday_label = true ?>
					<?php endif ?>

					<?php foreach( $day['allday'] as $event ) : ?>
						<a href="<?php echo esc_attr( get_permalink( $event->post_id ) ) . $event->instance_id ?>"
							<?php echo $data_type_events; ?>
							data-instance-id="<?php echo $event->instance_id; ?>"
							class="ai1ec-event-container ai1ec-load-event ai1ec-popup-trigger
								ai1ec-event-id-<?php echo $event->post_id ?>
								ai1ec-event-instance-id-<?php echo $event->instance_id ?>
								ai1ec-allday
								<?php if ( $event->_orig->get_multiday() ) echo 'ai1ec-multiday'; ?>"
							>
							<div class="ai1ec-event"
								style="<?php echo $event->get_color_style() ?>">
								<span class="ai1ec-event-title">
									<?php echo esc_html( apply_filters( 'the_title', $event->post->post_title, $event->post_id ) ) ?>
									<?php if ( $show_location_in_title && isset( $event->venue ) && $event->venue != '' ): ?>
										<span class="ai1ec-event-location"><?php echo sprintf( __( '@ %s', AI1EC_PLUGIN_NAME ), $event->venue ); ?></span>
									<?php endif; ?>
								</span>
							</div>
						</a>

						<div class="ai1ec-popup hide ai1ec-popup-in-oneday-view">
							<?php if ( $event->get_category_colors() ): ?>
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
							</span>
							<?php edit_post_link(
								'<i class="icon-pencil"></i> ' . __( 'Edit', AI1EC_PLUGIN_NAME ),
								'', '', $event->post_id
							); ?>

							<div class="ai1ec-event-time">
								<?php echo $event->_orig->get_timespan_html( 'short' ); ?>
							</div>

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

							<?php if ( $event->get_post_excerpt() ): ?>
								<div class="ai1ec-popup-excerpt"><?php echo esc_html( $event->get_post_excerpt() ) ?></div>
							<?php endif ?>
						</div><!-- .ai1ec-popup -->

					<?php endforeach; // allday ?>

				</td>
			<?php endforeach; // weekday ?>
		</tr>
	</thead>
	<tbody>
		<tr class="ai1ec-oneday">
			<td <?php if( $day['today'] ) echo 'class="ai1ec-today"'; ?>>

				<div class="ai1ec-grid-container">
					<?php for( $hour = 0; $hour < 24; $hour++ ) : ?>
						<div class="ai1ec-hour-marker <?php if( $hour >= 8 && $hour < 18 ) echo 'ai1ec-business-hour'; ?>" style="top: <?php echo $hour * 60; ?>px;">
							<div><?php
							echo esc_html(
								Ai1ec_Time_Utility::date_i18n(
									$time_format,
									gmmktime( $hour, 0 ),
									true
								)
							);
							?></div>
						</div>
						<?php for( $quarter = 1; $quarter < 4; $quarter++ ) : ?>
							<div class="ai1ec-quarter-marker" style="top: <?php echo $hour * 60 + $quarter * 15 ?>px;"></div>
						<?php endfor; ?>
					<?php endfor; ?>
					<?php if ( $day['today'] ) : ?>
						<div class="ai1ec-now-marker"
							style="top: <?php echo $now_top ?>px;">
							<div>
								<?php _e( 'Now:', AI1EC_PLUGIN_NAME ); echo " $now_text"; ?>
							</div>
						</div>
					<?php endif; ?>
				</div>

				<div class="ai1ec-day">
					<?php foreach ( $day['notallday'] as $notallday ) : ?>
						<?php extract( $notallday ); ?>
						<a href="<?php echo esc_attr( get_permalink( $event->post_id ) ) . $event->instance_id; ?>"
							<?php echo $data_type_events; ?>
							data-instance-id="<?php echo $event->instance_id; ?>"
							class="ai1ec-event-container ai1ec-load-event ai1ec-popup-trigger
								ai1ec-event-id-<?php echo $event->post_id; ?>
								ai1ec-event-instance-id-<?php echo $event->instance_id; ?>
								<?php if ( $event->start_truncated ) echo 'ai1ec-start-truncated'; ?>
								<?php if ( $event->end_truncated ) echo 'ai1ec-end-truncated'; ?>
								<?php if ( $event->_orig->get_multiday() ) echo 'ai1ec-multiday'; ?>"
							style="top: <?php echo $top; ?>px;
								height: <?php echo $height; ?>px;
								left: <?php echo $indent * 16 + 54; ?>px;
								<?php echo $event->get_color_style(); ?>
								<?php if ( $event->get_faded_color() ) :
									$rgba = $event->get_rgba_color();
									$rgba1 = sprintf( $rgba, '0.05' );
									$rgba3 = sprintf( $rgba, '0.3' ); ?>
									border: 1px solid <?php echo $event->get_faded_color(); ?>;
									border-color: <?php printf( $rgba, '0.5' ); ?>;
									background-image: -webkit-linear-gradient( top, <?php echo $rgba1; ?>, <?php echo $rgba3; ?> 50px );
									background-image: -moz-linear-gradient( top, <?php echo $rgba1; ?>, <?php echo $rgba3; ?> 50px );
									background-image: -ms-linear-gradient( top, <?php echo $rgba1; ?>, <?php echo $rgba3; ?> 50px );
									background-image: -o-linear-gradient( top, <?php echo $rgba1; ?>, <?php echo $rgba3; ?> 50px );
									background-image: linear-gradient( top, <?php echo $rgba1; ?>, <?php echo $rgba3; ?> 50px );
								<?php endif; ?>
								">

							<?php if ( $event->start_truncated ) : ?>
								<div class="ai1ec-start-truncator">◤</div>
							<?php endif; ?>
							<?php if ( $event->end_truncated ) : ?>
								<div class="ai1ec-end-truncator">◢</div>
							<?php endif; ?>

							<div class="ai1ec-event">
								<span class="ai1ec-event-time"><?php echo esc_html( $event->get_short_start_time() ); ?></span>
								<span class="ai1ec-event-title">
									<?php echo esc_html( apply_filters( 'the_title', $event->post->post_title, $event->post_id ) ); ?>
									<?php if ( $show_location_in_title && isset( $event->venue ) && $event->venue != '' ): ?>
										<span class="ai1ec-event-location"><?php echo sprintf( __( '@ %s', AI1EC_PLUGIN_NAME ), $event->venue ); ?></span>
									<?php endif; ?>
								</span>
							</div>

						</a>

						<div class="ai1ec-popup hide ai1ec-popup-in-oneday-view">
							<?php if ( $event->get_category_colors() ): ?>
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
							</span>
							<?php edit_post_link(
								'<i class="icon-pencil"></i> ' . __( 'Edit', AI1EC_PLUGIN_NAME ),
								'', '', $event->post_id
							); ?>

							<div class="ai1ec-event-time">
								<?php echo $event->_orig->get_timespan_html( 'short' ); ?>
							</div>

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

							<?php if ( $event->get_post_excerpt() ): ?>
								<div class="ai1ec-popup-excerpt"><?php echo esc_html( $event->get_post_excerpt() ) ?></div>
							<?php endif ?>
						</div><!-- .ai1ec-popup -->
					<?php endforeach; // events ?>
				</div>
			</td>
		</tr>
	</tbody>
</table>
<div class="pull-right"><?php echo $pagination_links; ?></div>
