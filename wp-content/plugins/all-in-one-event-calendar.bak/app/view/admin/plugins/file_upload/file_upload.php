<div class="row-fluid ai1ec_submit_wrapper">
	<?php if( isset( $message ) ) $message->render() ?>
	<div class="pull-left">
		<label for="ai1ec_file_input">
			<?php esc_html_e( 'Import events from a CSV file or iCalendar (.ics) feed:', AI1EC_PLUGIN_NAME ); ?>
		</label>
		<?php $file_input->render() ?>
	</div>
</div>
<div class="row-fluid ai1ec_submit_wrapper">
	<div class="pull-left ai1ec_textarea_wrapper">
		<label for="ai1ec_upload_textarea">
			<?php esc_html_e( 'Or paste your CSV data or iCalendar (.ics) feed here:', AI1EC_PLUGIN_NAME ); ?>
		</label>
		<?php $textarea->render() ?>
	</div>
</div>
<div class="row-fluid ai1ec_submit_wrapper">
	<div class="ai1ec-feed-category span6">
		<label for="ai1ec_file_upload_feed_category">
			<?php esc_html_e( 'Event category', AI1EC_PLUGIN_NAME ); ?>:
		</label>
		<?php echo $category_select; ?>
	</div>
	<div class="ai1ec-feed-tags span6">
		<label for="ai1ec_file_upload_feed_tags">
			<?php esc_html_e( 'Tag with', AI1EC_PLUGIN_NAME ); ?>:
		</label>
		<input type="text" class="span12" name="ai1ec_file_upload_feed_tags"
			id="ai1ec_file_upload_feed_tags" />
	</div>
</div>
<div class="row-fluid">
	<label for="ai1ec_file_upload_comments_enabled">
		<input type="checkbox" name="ai1ec_file_upload_comments_enabled"
		       id="ai1ec_file_upload_comments_enabled" value="1" />
		<?php esc_html_e( 'Allow comments on imported events', AI1EC_PLUGIN_NAME ); ?>
	</label>
</div>
<div class="row-fluid">
	<label for="ai1ec_file_upload_map_display_enabled">
		<input type="checkbox" name="ai1ec_file_upload_map_display_enabled"
		       id="ai1ec_file_upload_map_display_enabled" value="1" />
		<?php esc_html_e( 'Show map on imported events', AI1EC_PLUGIN_NAME ); ?>
	</label>
</div>
<div class="row-fluid ai1ec_submit_wrapper">
	<div class="span12">
		<?php $submit->render(); ?>
	</div>
</div>
