<div class="ai1ec-feed-container">
	<h4 class="ai1ec_feed_h4">
		<?php _e( 'iCalendar/.ics Feed URL:', AI1EC_PLUGIN_NAME ); ?>
	</h4>
	<div class="ai1ec-feed-url"><input type="text" class="ai1ec-feed-url" readonly="readonly" value="<?php echo esc_attr( $feed_url ) ?>" /></div>
	<input type="hidden" name="feed_id" class="ai1ec_feed_id" value="<?php echo $feed_id;?>" />
	<?php if( $event_category ): ?>
		<div class="ai1ec-feed-category">
			<?php _e( 'Event category:', AI1EC_PLUGIN_NAME ); ?>
			<strong><?php echo $event_category; ?></strong>
		</div>
	<?php endif ?>
	<?php if( $tags ): ?>
		<div class="ai1ec-feed-tags">
			<?php _e( 'Tag with', AI1EC_PLUGIN_NAME ); ?>:
			<strong><?php echo $tags; ?></strong>
		</div>
	<?php endif ?>
	<div class="ai1ec-feed-comments-enabled">
		<?php _e( 'Allow comments', AI1EC_PLUGIN_NAME ); ?>:
		<strong><?php
		if ( $comments_enabled ) {
			_e( 'Yes', AI1EC_PLUGIN_NAME );
		} else {
			_e( 'No',  AI1EC_PLUGIN_NAME );
		}
		?></strong>
	</div>
	<div class="ai1ec-feed-map-display-enabled">
		<?php _e( 'Show map', AI1EC_PLUGIN_NAME ); ?>:
		<strong><?php
		if ( $map_display_enabled ) {
			_e( 'Yes', AI1EC_PLUGIN_NAME );
		} else {
			_e( 'No',  AI1EC_PLUGIN_NAME );
		}
		?></strong>
	</div>
	<input type="button" class="button ai1ec_delete_ics" value="<?php _e( '× Remove', AI1EC_PLUGIN_NAME ); ?>" />
	<input type="button" class="button ai1ec_update_ics" value="<?php _e( 'Refresh', AI1EC_PLUGIN_NAME ); ?>" />
	<?php if( $events ): ?>
		<input type="hidden" class="ai1ec_flush_ics" value="<?php echo $events ?>" />
	<?php endif ?>
	<img src="images/wpspin_light.gif" class="ajax-loading" alt="" />
</div>
