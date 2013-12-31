<div class="ai1ec-subscribe-container btn-group pull-left">
	<a class="btn ai1ec-subscribe ai1ec-tooltip-trigger" data-placement="bottom"
		href="<?php echo AI1EC_EXPORT_URL . $url_args ?>"
		title="<?php _e( 'Subscribe to this calendar in your personal calendar (iCal, Outlook, etc.)', AI1EC_PLUGIN_NAME ) ?>" >
		<i class="icon-plus"></i>
		<?php if ( $is_filtered ) : ?>
			<?php _e( 'Subscribe to filtered calendar', AI1EC_PLUGIN_NAME ); ?>
		<?php else : ?>
			<?php _e( 'Subscribe', AI1EC_PLUGIN_NAME ); ?>
		<?php endif; ?>
	</a>
	<a class="btn ai1ec-subscribe-google ai1ec-tooltip-trigger" target="_blank"
		data-placement="bottom"
		href="http://www.google.com/calendar/render?cid=<?php echo urlencode( str_replace( 'webcal://', 'http://', AI1EC_EXPORT_URL ) . $url_args ) ?>"
		title="<?php _e( 'Subscribe to this calendar in your Google Calendar', AI1EC_PLUGIN_NAME ) ?>" >
		<i class="icon-google-plus icon-large"></i>
		<?php _e( 'Add to Google', AI1EC_PLUGIN_NAME ) ?>
	</a>
</div>
