<div class="ai1ec-form-group">
	<label class="ai1ec-control-label ai1ec-col-sm-3">
		<?php _e( 'Custom dates:', AI1EC_PLUGIN_NAME ) ;?>
	</label>
	<div class="ai1ec-col-sm-8">
		<div id="ai1ec_recurrence_calendar" data-date="<?php echo $selected_dates; ?>"></div>
	</div>
</div>
<div class="ai1ec-form-group">
	<div class="ai1ec-col-sm-9 ai1ec-col-sm-offset-3">
		<div id="ai1ec_rec_dates_list"></div>
		<input type="hidden" name="ai1ec_rec_custom_dates"
			id="ai1ec_rec_custom_dates" value="">
	</div>
</div>
