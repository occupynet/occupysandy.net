<p>
<?php _e(
    'Configure which other calendars your own calendar subscribes to.
    You can add any calendar that provides an iCalendar (.ics) feed.
    Enter the feed URL(s) below and the events from those feeds will be
    imported periodically.',
    AI1EC_PLUGIN_NAME ); ?>
</p>
<div id="ics-alerts"></div>
<div class="ai1ec-form-horizontal">
	<div class="ai1ec-form-group">
		<div class="ai1ec-col-md-12">
			<label class="ai1ec-control-label ai1ec-pull-left" for="cron_freq">
			  <?php _e( 'Check for new events', AI1EC_PLUGIN_NAME ) ?>:
			</label>
			<div class="ai1ec-col-sm-6">
				<?php echo $cron_freq ?>
			</div>
		</div>
	</div>
</div>

<div id="ai1ec-feeds-after"
	class="ai1ec-feed-container ai1ec-well ai1ec-well-sm ai1ec-clearfix">
	<div class="ai1ec-form-group">
		<label for="ai1ec_feed_url">
			<?php _e( 'iCalendar/.ics Feed URL:', AI1EC_PLUGIN_NAME ) ?>
		</label>
		<input type="text" name="ai1ec_feed_url" id="ai1ec_feed_url"
			class="ai1ec-form-control">
	</div>
	<div class="ai1ec-row">
		<div class="ai1ec-col-sm-6">
			<?php $event_categories->render(); ?>
		</div>
		<div class="ai1ec-col-sm-6">
			<?php $event_tags->render(); ?>
		</div>
	</div>
	<?php do_action( 'ai1ec_ics_row_after_categories_tags', null ); ?>
	<div class="ai1ec-feed-comments-enabled">
		<label for="ai1ec_comments_enabled">
			<input type="checkbox" name="ai1ec_comments_enabled"
				id="ai1ec_comments_enabled" value="1">
			<?php _e( 'Allow comments on imported events', AI1EC_PLUGIN_NAME ); ?>
		</label>
	</div>
	<div class="ai1ec-feed-map-display-enabled">
		<label for="ai1ec_map_display_enabled">
			<input type="checkbox" name="ai1ec_map_display_enabled"
				id="ai1ec_map_display_enabled" value="1">
			<?php _e( 'Show map on imported events', AI1EC_PLUGIN_NAME ); ?>
		</label>
	</div>
	<div class="ai1ec-feed-add-tags-categories">
		<label for="ai1ec_add_tag_categories">
			<input type="checkbox" name="ai1ec_add_tag_categories"
				id="ai1ec_add_tag_categories" value="1">
			<?php _e( 'Import any tags/categories provided by feed, in addition those selected above', AI1EC_PLUGIN_NAME ); ?>
		</label>
	</div>
	<?php do_action( 'ai1ec_ics_row_after_keep_categories_tags', null ); ?>
	<div class="ai1ec-feed-keep-old-events">
		<label for="ai1ec_keep_old_events">
			<input type="checkbox" name="ai1ec_keep_old_events"
				id="ai1ec_keep_old_events" value="1">
			<?php _e( 'On refresh, preserve previously imported events that are missing from the feed', AI1EC_PLUGIN_NAME ); ?>
		</label>
	</div>
	<div class="ai1ec-feed-import-timezone">
		<label for="ai1ec_feed_import_timezone">
			<input type="checkbox" name="ai1ec_feed_import_timezones"
				   id="ai1ec_feed_import_timezone" value="1">
			<span class="ai1ec-tooltip-toggle" title="<?php _e( 'Guesses the time zone of events that have none specified; recommended for Google Calendar feeds', AI1EC_PLUGIN_NAME ); ?>">
				<?php _e( 'Assign default time zone to events in UTC', AI1EC_PLUGIN_NAME ); ?>
			</span>
		</label>
	</div>
	<?php do_action( 'ai1ec_ics_row_after_settings', null ); ?>
	<div class="ai1ec-pull-right">
    	<button type="button" id="ai1ec_cancel_ics"
			class="ai1ec-btn ai1ec-btn-primary ai1ec-btn-sm">
			<i class="ai1ec-fa ai1ec-fa-cancel"></i>
			<?php _e( 'Cancel', AI1EC_PLUGIN_NAME ); ?>
		</button>
		<button type="button" id="ai1ec_add_new_ics"
			class="ai1ec-btn ai1ec-btn-primary ai1ec-btn-sm"
			data-loading-text="<?php echo esc_attr(
				'<i class="ai1ec-fa ai1ec-fa-spinner ai1ec-fa-spin ai1ec-fa-fw"></i> ' .
				__( 'Please wait&#8230;', AI1EC_PLUGIN_NAME ) ); ?>">
			<i class="ai1ec-fa ai1ec-fa-plus"></i>
			<span id="ai1ec_ics_add_new">
				<?php _e( 'Add new subscription', AI1EC_PLUGIN_NAME ); ?>
			</span>
			<span id="ai1ec_ics_update" class="ai1ec-hidden">
				<?php _e( 'Update subscription', AI1EC_PLUGIN_NAME ); ?>
			</span>
		</button>
	</div>
</div>

<div class="timely ai1ec-form-inline ai1ec-panel-group" id="ai1ec-feeds-accordion">
	<?php echo $feed_rows; ?>
</div>
<?php echo $modal->render(); ?>
