<div class="timely ai1ec-single-event
	ai1ec-event-id-<?php echo $event->post_id; ?>
	<?php if ( $event->get_multiday() ) echo 'ai1ec-multiday'; ?>
	<?php if ( $event->allday ) echo 'ai1ec-allday'; ?>">

<a name="ai1ec-event"></a>

<div class="ai1ec-event-details clearfix">

	<div class="clearfix"><?php echo $back_to_calendar; ?></div>

	<div class="ai1ec-time">
		<div class="ai1ec-label"><?php _e( 'When:', AI1EC_PLUGIN_NAME ); ?></div>
		<div class="ai1ec-field-value"><?php echo $event->get_timespan_html(); ?></div>
	</div>

	<?php if ( $recurrence ) : ?>
		<div class="ai1ec-recurrence">
			<div class="ai1ec-label">
				<i class="icon-repeat"></i>
				<?php _e( 'Repeats:', AI1EC_PLUGIN_NAME ); ?>
			</div>
			<div class="ai1ec-field-value">
				<?php if ( $edit_instance_url ) : ?>
					<a class="ai1ec-edit-instance-link pull-right"
						href="<?php echo $edit_instance_url; ?>">
						<i class="icon-pencil"></i>
						<?php echo $edit_instance_text; ?>
					</a>
				<?php endif; ?>
				<?php echo ucfirst( $recurrence ); ?>
				<?php if ( $exclude ) : ?>
					<div class="ai1ec-exclude">
						<?php _e( 'Excluding:', AI1EC_PLUGIN_NAME ); ?>
						<?php echo $exclude; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>


	<?php if ( $map ) : ?>
		<div class="ai1ec-map pull-right"><?php echo $map; ?></div>
	<?php endif; ?>

	<?php if ( $show_subscribe_buttons || $event->ticket_url ) : ?>
		<div class="ai1ec-actions pull-right btn-group">
			<?php if ( $event->ticket_url ) : ?>
				<a href="<?php esc_attr_e( $event->ticket_url ); ?>" target="_blank"
					class="ai1ec-tickets btn btn-small btn-primary">
					<i class="icon-shopping-cart"></i>
					<?php _e( 'Buy Tickets', AI1EC_PLUGIN_NAME ); ?>
				</a>
			<?php endif; ?>
			<?php if ( $show_subscribe_buttons ) : ?>
				<a class="btn btn-small ai1ec-subscribe"
					href="<?php echo esc_attr( $subscribe_url ); ?>"
					title="<?php _e( 'Add this event to your favourite calendar program (iCal, Outlook, etc.)', AI1EC_PLUGIN_NAME ); ?>" >
					<i class="icon-plus"></i>
					<?php _e( 'Add to Calendar', AI1EC_PLUGIN_NAME ); ?></a>
				<a class="btn btn-small ai1ec-subscribe-google" target="_blank"
					href="<?php echo esc_attr( $google_url ); ?>"
					title="<?php _e( 'Add this event to your Google Calendar', AI1EC_PLUGIN_NAME ); ?>" >
					<i class="icon-google-plus icon-large"></i>
					<?php _e( 'Add to Google', AI1EC_PLUGIN_NAME ); ?>
				</a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( $location ) : ?>
		<div class="ai1ec-location">
			<div class="ai1ec-label"><?php _e( 'Where:', AI1EC_PLUGIN_NAME ); ?></div>
			<div class="ai1ec-field-value"><?php echo $location; ?></div>
		</div>
	<?php endif; ?>

	<?php if ( $event->cost ) : ?>
		<div class="ai1ec-cost">
			<div class="ai1ec-label"><?php _e( 'Cost:', AI1EC_PLUGIN_NAME ); ?></div>
			<div class="ai1ec-field-value"><?php echo esc_html( $event->cost ); ?></div>
		</div>
	<?php endif; ?>

	<?php if ( $contact ) : ?>
		<div class="ai1ec-contact">
			<div class="ai1ec-label"><?php _e( 'Contact:', AI1EC_PLUGIN_NAME ); ?></div>
			<div class="ai1ec-field-value"><?php echo $contact; ?></div>
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

<?php
	if ( ! $event->get_content_img_url() ) {
		echo $event->get_event_avatar(
			array( 'post_thumbnail', 'location_avatar', 'category_avatar' ),
			'timely alignleft',
			false
		);
	}
?>

</div>
