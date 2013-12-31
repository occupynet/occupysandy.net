<div class="ai1ec-modal hide fade" id="ai1ec-submit-ics-modal">
	<div class="ai1ec-loading"></div>
	<div class="ai1ec-modal-header">
		<button type="button" class="close" data-dismiss="ai1ec_modal">×</button>
		<h2><?php _e( 'Add Your Calendar Feed', AI1EC_PLUGIN_NAME ); ?></h2>
	</div>
	<form class="ai1ec-submit-ics-form">
		<span class="ai1ec-nonce-fields">
			<?php wp_nonce_field( 'ai1ec_submit_ics_form', AI1EC_POST_TYPE ); ?>
		</span>
		<div class="ai1ec-modal-body">
			<div class="ai1ec-pane collapse in">
				<div class="ai1ec-alerts"></div>
				<div class="ai1ec-prose">
					<p><?php echo __( "If you know of an event feed you think belongs in this calendar, paste its iCalendar (.ics) feed’s URL below. If approved, its events will be added to this calendar.", AI1EC_PLUGIN_NAME ); ?></p>
				</div>
				<div class="row-fluid">
					<input class="span12" type="text" value=""
						id="ai1ec_calendar_url" name="ai1ec_calendar_url"
						placeholder="<?php _e( 'Paste your calendar’s iCalendar (.ics) feed URL', AI1EC_PLUGIN_NAME ); ?>">
				</div>
				<div class="row-fluid">
					<div class="span6">
						<?php echo $categories; ?>
					</div>
					<div class="span6">
						<input class="span12" type="text" value=""
							id="ai1ec_submitter_email" name="ai1ec_submitter_email"
							placeholder="<?php _e( "Enter your e-mail address", AI1EC_PLUGIN_NAME ); ?>">
					</div>
				</div>
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
			</div>

			<p class="ai1ec-centered">
				<a href=".ai1ec-submit-ics-form .ai1ec-pane" id="ai1ec-download-toggle"
					class="btn btn-info btn-large" data-toggle="ai1ec_collapse">
					<i class="icon-download icon-large"></i>
					<?php _e( 'Get Your Own Calendar', AI1EC_PLUGIN_NAME ); ?>
					<i class="icon-chevron-down"></i>
					<i class="icon-chevron-up hide"></i>
				</a>
			</p>

			<div class="ai1ec-pane collapse ai1ec-prose">
				<p>
					<?php _e( 'If you already have your own <a href="http://wordpress.org/" target="_blank">WordPress</a> website, it’s easy to install your own <strong>All-in-One Event Calendar from Timely</strong>:', AI1EC_PLUGIN_NAME ); ?>
				</p>
				<ol>
					<li><?php _e( 'Browse to <a href="http://time.ly/get-your-own-calendar" target="_blank">time.ly/get-your-own-calendar</a>.', AI1EC_PLUGIN_NAME ); ?></li>
					<li><?php _e( 'Download the <em>free</em> <strong>Standard Calendar</strong> plugin.', AI1EC_PLUGIN_NAME ); ?></li>
					<li><?php _e( 'Log into your WordPress dashboard.', AI1EC_PLUGIN_NAME ); ?></li>
					<li><?php _e( 'Browse to <strong>Plugins</strong> &gt; <strong>Add New</strong> &gt; <strong>Upload</strong>.', AI1EC_PLUGIN_NAME ); ?></li>
					<li><?php _e( 'Choose the .zip file you just downloaded, and click <strong>Install Now</strong>.', AI1EC_PLUGIN_NAME ); ?></li>
				</ol>
				<p>
					<?php _e( 'That’s it! You’ll have your own <strong>All-in-One Calendar</strong> up and running within minutes.', AI1EC_PLUGIN_NAME ); ?>
				</p>
				<p>
					<?php _e( 'For help or for more information, please visit our <a href="http://help.time.ly/" target="_blank">Help Desk</a>.', AI1EC_PLUGIN_NAME ); ?>
				</p>
			</div>
		</div>
		<div class="ai1ec-modal-footer">
			<div class="collapse in ai1ec-pane">
				<button type="submit"
					class="btn btn-large btn-primary ai1ec-submit">
					<?php _e( 'Submit for review', AI1EC_PLUGIN_NAME ); ?>
					<i class="icon-chevron-right"></i>
				</button>
			</div>
		</div>
	</form>
</div>
