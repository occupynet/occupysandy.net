<div class="ai1ec-modal-header">
	<button type="button" class="close" data-dismiss="ai1ec_modal">×</button>
	<h2><?php _e( 'Post Your Event', AI1EC_PLUGIN_NAME ); ?></h2>
</div>

<form class="ai1ec-create-event-form" method="POST"
	action="<?php echo esc_attr( $form_action ); ?>">

<?php wp_nonce_field( 'ai1ec_front_end_form', AI1EC_POST_TYPE ); ?>
<input type="hidden" name="ai1ec_start_time" id="ai1ec-start-time" />
<input type="hidden" name="ai1ec_end_time" id="ai1ec-end-time" />
<input type="hidden" name="ai1ec_all_day_event" id="ai1ec-all-day-event" />
<input type="hidden" name="ai1ec_instant_event" id="ai1ec-instant-event" />

<div class="ai1ec-modal-body">

	<?php // Alerts (hidden by default). ?>
	<div class="alert alert-error hide ai1ec-missing-field">
		<?php printf( __( 'The %s field is required.', AI1EC_PLUGIN_NAME ),
			'<em></em>' ); ?>
	</div>
	<?php if( $require_disclaimer ) : ?>
		<div class="alert alert-error hide ai1ec-required-disclaimer">
			<?php _e( 'You must check the checkbox stating you agree to the posting rules to submit the event.', AI1EC_PLUGIN_NAME ) ?>
		</div>
	<?php endif; ?>
	<div class="alert alert-error hide ai1ec-submit-error"></div>

	<?php if( $require_disclaimer ) : ?>
	<div class="row-fluid">
		<label for="require_disclaimer" class="ai1ec-checkbox-label">
			<input type="checkbox" id="require_disclaimer" value="1" />
			<?php printf(
				__(
					'I have read and agreed that this event conforms to the <a %s>posting rules</a>',
					AI1EC_PLUGIN_NAME
				),
				'data-toggle="ai1ec_collapse" data-target="#show_disclaimer" class="ai1ec-collapsible-toggle" id="open_require_disclaimer"'
				); ?>
		</label>
	</div>
	<div class="ai1ec-row collapse"
			id="show_disclaimer">
				<div class="well well-small">
					<?php echo $disclaimer ?>
				</div>
			</div>
	<?php endif; ?>
	<?php // Event title. ?>
	<div class="row-fluid">
		<input type="text" id="ai1ec-event-title" name="post_title"
			placeholder="<?php esc_attr_e( 'Event Title', AI1EC_PLUGIN_NAME ); ?>"
			class="span12" />
	</div>

	<div class="row-fluid">
		<?php // Start date & time. ?>
		<div class="span3">
			<input data-date-format="<?php echo esc_attr( $date_format_pattern ); ?>"
				data-date-weekstart="<?php echo esc_attr( $week_start_day ); ?>"
				id="ai1ec-start-date-input" type="text"
				readonly="readonly"
				class="span12 ai1ec-datepicker"
				placeholder="<?php esc_attr_e( 'Date', AI1EC_PLUGIN_NAME ); ?>" />
		</div>

		<div class="span3">
			<div id="ai1ec-start-time-input-wrap"
				class="collapse bootstrap-timepicker">
				<input id="ai1ec-start-time-input" type="text"
					title="<?php echo $timezone_expr; ?>"
					data-show-meridian="<?php echo $input_24h_time ? 'false' : 'true'; ?>"
					readonly="readonly" disabled="disabled"
					class="span12 ai1ec-timepicker ai1ec-tooltip-trigger"
					placeholder="<?php esc_attr_e( 'Time', AI1EC_PLUGIN_NAME ); ?>"
					/>
			</div>

			<?php // Has time checkbox. ?>
			<label for="ai1ec-has-time" class="ai1ec-checkbox-label">
				<input type="checkbox" id="ai1ec-has-time"
					value="1" disabled="disabled" data-toggle="ai1ec_collapse"
					data-target="#ai1ec-start-time-input-wrap" />
				<?php _e( 'Add time', AI1EC_PLUGIN_NAME ); ?>
			</label>
		</div>

		<?php // End date & time. ?>
		<div class="span6">
			<div id="ai1ec-end-time-wrap" class="collapse row-fluid">
				<div class="span6">
					<input
						data-date-format="<?php echo esc_attr( $date_format_pattern ); ?>"
						data-date-weekstart="<?php echo esc_attr( $week_start_day ); ?>"
						id="ai1ec-end-date-input" type="text"
						readonly="readonly" disabled="disabled"
						class="span12 ai1ec-datepicker"
						placeholder="<?php esc_attr_e( 'End date', AI1EC_PLUGIN_NAME ); ?>"
						/>
				</div>

				<div class="span6">
					<div id="ai1ec-end-time-input-wrap"
						class="collapse bootstrap-timepicker">
						<input id="ai1ec-end-time-input" type="text"
							title="<?php echo $timezone_expr; ?>"
							data-show-meridian="<?php echo $input_24h_time ? 'false' : 'true'; ?>"
							data-alignment="right"
							readonly="readonly" disabled="disabled"
							class="span12 ai1ec-timepicker ai1ec-tooltip-trigger"
							placeholder="<?php esc_attr_e( 'End time', AI1EC_PLUGIN_NAME ); ?>"
							/>
					</div>
				</div>
			</div>

			<?php // Has end time checkbox. ?>
			<label for="ai1ec-has-end-time" class="ai1ec-checkbox-label">
				<input type="checkbox" id="ai1ec-has-end-time"
					value="1" disabled="disabled" data-toggle="ai1ec_collapse"
					data-target="#ai1ec-end-time-wrap" />
				<span class="ai1ec-without-time">
					<?php _e( 'Add end date', AI1EC_PLUGIN_NAME ); ?>
				</span>
				<span class="ai1ec-with-time">
					<?php _e( 'Add end date/time', AI1EC_PLUGIN_NAME ); ?>
				</span>
			</label>
		</div>
	</div>

	<?php /* Venue name */ ?>
	<div class="row-fluid">
		<input type="text" id="ai1ec_venue" name="ai1ec_venue"
			placeholder="<?php esc_attr_e( 'Venue name (optional)', AI1EC_PLUGIN_NAME ); ?>"
			class="span12" />
	</div>

	<?php // Address & show map checkbox. ?>
	<div class="row-fluid">
		<div class="span9">
			<input type="text" id="ai1ec_address" name="ai1ec_address"
				placeholder="<?php esc_attr_e( 'Address (optional)', AI1EC_PLUGIN_NAME ); ?>"
				class="span12" />
		</div>
		<div class="span3">
			<label for="ai1ec-google-map" class="ai1ec-checkbox-label">
				<input type="checkbox" id="ai1ec-google-map" name="ai1ec_google_map"
					<?php if ( $interactive_gmaps ) : ?>
						data-toggle="ai1ec_collapse" data-target="#ai1ec-map-wrap"
					<?php endif; ?>
					disabled="disabled" />
				<?php _e( 'Include map', AI1EC_PLUGIN_NAME ); ?>
			</label>
		</div>
	</div>

	<?php // Map preview. ?>
	<?php if ( $interactive_gmaps ) : ?>
		<div id="ai1ec-map-wrap" class="collapse">
			<div id="ai1ec_map_canvas"></div>
		</div>
	<?php endif; ?>

	<?php // Categories & tags. ?>
	<div class="row-fluid">

		<?php if ( $cat_select !== '' ) : ?>

		<div class="span6">
			<?php echo $cat_select; ?>
		</div>
		<div class="span6">

		<?php else : ?>

		<div class="span12">

		<?php endif; ?>

			<?php echo $tag_select; ?>
		</div>
	</div>

	<?php // Description. ?>
	<div class="row-fluid">
		<textarea id="ai1ec-description" name="post_content" class="span12" rows="4"
			placeholder="<?php esc_attr_e( 'Description (optional)', AI1EC_PLUGIN_NAME ); ?>"
			></textarea>
	</div>

	<?php /* Event organizer name and email */ ?>
	<div class="row-fluid">
		<div class="span6">
			<input type="text" id="ai1ec_contact_name" name="ai1ec_contact_name"
				placeholder="<?php esc_attr_e( 'Organizer name (optional)', AI1EC_PLUGIN_NAME ); ?>"
				class="span12" />
		</div>
		<div class="span6">
			<input type="text" id="ai1ec_contact_email" name="ai1ec_contact_email"
				placeholder="<?php esc_attr_e( 'Organizer e-mail (optional)', AI1EC_PLUGIN_NAME ); ?>"
				class="span12" />
		</div>
	</div>

	<div class="row-fluid">
		<label for="ai1ec-extra-checkbox" class="ai1ec-checkbox-label">
			<input type="checkbox" id="ai1ec-extra-checkbox"
				data-toggle="ai1ec_collapse" data-target="#ai1ec-extra-fields" />
			<?php _e( 'Add additional details (cost, website URLs, etc.)',
				AI1EC_PLUGIN_NAME ); ?>
		</label>
	</div>

	<div id="ai1ec-extra-fields" class="collapse">
		<?php /* Event cost and tickets URL */ ?>
		<div class="row-fluid">
			<div class="span4">
				<div id="ai1ec_cost_wrap" class="collapse">
					<input type="text" id="ai1ec_cost" name="ai1ec_cost"
						placeholder="<?php esc_attr_e( 'Cost', AI1EC_PLUGIN_NAME ); ?>"
						class="span12" />
				</div>
				<label for="ai1ec_is_free">
					<input type="checkbox"
					       checked="checked"
					       name="ai1ec_is_free"
					       data-toggle="ai1ec_collapse"
					       data-target="#ai1ec_cost_wrap"
					       id="ai1ec_is_free"
					       value="1" />
					<?php _e( 'Free', AI1EC_PLUGIN_NAME ); ?>
				</label>
			</div>
			<div class="span8">
				<input type="text" id="ai1ec_ticket_url" name="ai1ec_ticket_url"
					placeholder="<?php esc_attr_e( 'Registration URL (optional)', AI1EC_PLUGIN_NAME ); ?>"
					class="span12" />
			</div>
		</div>

		<?php /* Event phone and contact URL */ ?>
		<div class="row-fluid">
			<div class="span4">
				<input type="text" id="ai1ec_contact_phone" name="ai1ec_contact_phone"
					placeholder="<?php esc_attr_e( 'Phone number (optional)', AI1EC_PLUGIN_NAME ); ?>"
					class="span12" />
			</div>
			<div class="span8">
				<input type="text" id="ai1ec_contact_url" name="ai1ec_contact_url"
					placeholder="<?php esc_attr_e( 'External website URL (optional)', AI1EC_PLUGIN_NAME ); ?>"
					class="span12" />
			</div>
		</div>
	</div>

	<?php // Image upload. ?>
	<?php if ( $allow_uploads ) : ?>
		<div class="row-fluid">
			<div class="span3">
				<label for="ai1ec-image" class="ai1ec-file-upload-label">
					<?php _e( 'Image (optional):', AI1EC_PLUGIN_NAME ); ?>
				</label>
			</div>
			<div class="span9">
				<div class="fileupload fileupload-new" data-provides="fileupload">
					<div class="fileupload-new thumbnail">
						<img src="<?php echo esc_attr( $default_image ); ?>" />
					</div>
					<div class="fileupload-preview fileupload-exists thumbnail"></div>
					<span class="btn btn-large btn-file">
						<i class="icon-picture"></i>&nbsp;
						<span class="fileupload-new">
							<?php _e( 'Select image', AI1EC_PLUGIN_NAME ); ?>
						</span>
						<span class="fileupload-exists">
							<?php _e( 'Change', AI1EC_PLUGIN_NAME ); ?>
						</span>
						<input type="file" name="ai1ec_image" />
					</span>
					<a href="#" class="btn btn-large fileupload-exists" data-dismiss="fileupload">
						<i class="icon-remove"></i>
						<?php _e( 'Remove', AI1EC_PLUGIN_NAME ); ?>
					</a>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<?php // reCAPTCHA. ?>
	<?php if ( $recaptcha_key ) : ?>
		<div class="ai1ec-recaptcha"
			data-placeholder="<?php _e( 'Verification words' ); ?>"
			data-recaptcha-key="<?php echo esc_attr( $recaptcha_key ); ?>">
			<div class="ai1ec-initializing-message">
				<?php _e( 'Loading reCAPTCHA...', AI1EC_PLUGIN_NAME ); ?>
			</div>
		</div>
	<?php endif; ?>

</div><!-- /.ai1ec-modal-body -->

<div class="ai1ec-modal-footer">
	<a href="#" class="btn btn-large btn-primary ai1ec-submit">
		<?php _e( 'Submit Event', AI1EC_PLUGIN_NAME ); ?>
		<i class="icon-chevron-right"></i>
	</a>
</div>

</form>
