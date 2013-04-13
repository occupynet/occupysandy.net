<div class="accordion-heading">
	<a class="accordion-toggle" data-toggle="ai1ec_collapse"
		data-parent="#ai1ec-add-new-event-accordion"
		href="#ai1ec-event-contact-box">
		<i class="icon-phone"></i> <?php _e( 'Organizer contact info', AI1EC_PLUGIN_NAME ); ?>
	</a>
</div>
<div id="ai1ec-event-contact-box" class="accordion-body collapse">
	<div class="accordion-inner">
		<table class="ai1ec-form">
			<tbody>
				<tr>
					<td class="ai1ec-first">
						<label for="ai1ec_contact_name">
							<?php _e( 'Contact name:', AI1EC_PLUGIN_NAME ); ?>
						</label>
					</td>
					<td>
						<input type="text" name="ai1ec_contact_name" id="ai1ec_contact_name" value="<?php echo $contact_name; ?>" />
					</td>
				</tr>
				<tr>
					<td>
						<label for="ai1ec_contact_phone">
							<?php _e( 'Phone:', AI1EC_PLUGIN_NAME ); ?>
						</label>
					</td>
					<td>
						<input type="text" name="ai1ec_contact_phone" id="ai1ec_contact_phone" value="<?php echo $contact_phone; ?>" />
					</td>
				</tr>
				<tr>
					<td>
						<label for="ai1ec_contact_email">
							<?php _e( 'E-mail:', AI1EC_PLUGIN_NAME ); ?>
						</label>
					</td>
					<td>
						<input type="text" name="ai1ec_contact_email" id="ai1ec_contact_email" value="<?php echo $contact_email; ?>" />
					</td>
				</tr>
				<tr>
					<td>
						<label for="ai1ec_contact_url">
							<?php _e( 'External URL:', AI1EC_PLUGIN_NAME ); ?>
						</label>
					</td>
					<td>
						<input type="text" name="ai1ec_contact_url" id="ai1ec_contact_url" value="<?php echo $contact_url; ?>" />
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
