<div class="timely ai1ec-excerpt">
	<div class="ai1ec-time">
		<strong><?php _e( 'When:', AI1EC_PLUGIN_NAME ); ?></strong>
		<?php echo $event->get_timespan_html(); ?>
	</div>
	<?php if ( $location ) : ?>
		<div class="ai1ec-location">
			<strong><?php _e( 'Where:', AI1EC_PLUGIN_NAME ); ?></strong>
			<?php echo $location; ?>
		</div>
	<?php endif; ?>
</div>
