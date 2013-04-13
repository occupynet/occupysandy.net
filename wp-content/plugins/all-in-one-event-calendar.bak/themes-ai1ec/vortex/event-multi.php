<div class="timely ai1ec-multi-event
	ai1ec-event-id-<?php echo $event->post_id; ?>
	<?php if ( $event->get_multiday() ) echo 'ai1ec-multiday'; ?>
	<?php if ( $event->allday ) echo 'ai1ec-allday'; ?>">

<div class="ai1ec-event-details clearfix">

	<?php if ( $event->ticket_url || $event->show_map ) : ?>
		<div class="btn-group pull-right">
			<?php if ( $event->ticket_url ) : ?>
				<a href="<?php esc_attr_e( $event->ticket_url ); ?>" target="_blank"
					class="ai1ec-tickets btn btn-small btn-primary">
					<i class="icon-shopping-cart"></i>
					<?php _e( 'Buy Tickets', AI1EC_PLUGIN_NAME ); ?>
				</a>
			<?php endif; ?>
			<?php if( $event->show_map ): ?>
				<a class="btn btn-small" href="<?php the_permalink() . $event->instance_id; ?>#ai1ec-event">
					<i class="icon-map-marker"></i>
					<?php _e( 'View Map', AI1EC_PLUGIN_NAME ); ?>
				</a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="ai1ec-time">
		<div class="ai1ec-label"><?php _e( 'When:', AI1EC_PLUGIN_NAME ); ?></div>
		<div class="ai1ec-field-value"><?php echo $event->get_timespan_html(); ?></div>
	</div>

	<?php if ( $recurrence ) : ?>
		<div class="ai1ec-recurrence">
			<div class="ai1ec-label"><?php _e( 'Repeats:', AI1EC_PLUGIN_NAME ); ?></div>
			<div class="ai1ec-field-value">
				<?php if ( $edit_instance_url ) : ?>
					<a class="ai1ec-edit-instance-link pull-right"
						href="<?php echo $edit_instance_url; ?>">
						<i class="icon-pencil"></i>
						<?php echo $edit_instance_text; ?>
					</a>
				<?php endif; ?>
				<?php echo $recurrence; ?>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( $location ) : ?>
		<div class="ai1ec-location">
			<div class="ai1ec-label"><?php _e( 'Where:', AI1EC_PLUGIN_NAME ); ?></div>
			<div class="ai1ec-field-value"><?php echo $location; ?></div>
		</div>
	<?php endif; ?>

	<?php if ( $categories ) : ?>
		<div class="ai1ec-categories">
			<div class="ai1ec-label ai1ec-hidden-narrow-phone" title="<?php _e( 'Categories', AI1EC_PLUGIN_NAME ); ?>">
				<i class="icon-folder-open icon-large"></i>
			</div>
			<div class="ai1ec-label ai1ec-visible-narrow-phone">
				<i class="icon-folder-open"></i>
				<?php _e( 'Categories:', AI1EC_PLUGIN_NAME ); ?>
			</div>
			<div class="ai1ec-field-value"><?php echo $categories; ?></div>
		</div>
	<?php endif; ?>

	<?php if ( $tags ) : ?>
		<div class="ai1ec-tags">
			<div class="ai1ec-label ai1ec-hidden-narrow-phone" title="<?php _e( 'Tags', AI1EC_PLUGIN_NAME ); ?>">
				<i class="icon-tags icon-large"></i>
			</div>
			<div class="ai1ec-label ai1ec-visible-narrow-phone">
				<i class="icon-tags"></i>
				<?php _e( 'Tags:', AI1EC_PLUGIN_NAME ); ?>
			</div>
			<div class="ai1ec-field-value"><?php echo $tags; ?></div>
		</div>
	<?php endif; ?>

</div>

</div>
