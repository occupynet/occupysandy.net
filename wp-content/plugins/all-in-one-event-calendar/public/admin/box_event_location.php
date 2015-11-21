<div class="ai1ec-panel-heading">
	<a data-toggle="ai1ec-collapse"
		data-parent="#ai1ec-add-new-event-accordion"
		href="#ai1ec-event-location-box">
		<i class="ai1ec-fa ai1ec-fa-map-marker ai1ec-fa-fw"></i>
		<?php _e( 'Event location details', AI1EC_PLUGIN_NAME ); ?>
	</a>
</div>
<div id="ai1ec-event-location-box" class="ai1ec-panel-collapse ai1ec-collapse">
	<div class="ai1ec-panel-body">
		<div class="ai1ec-row">
			<div class="ai1ec-col-md-8 ai1ec-col-lg-6">
				<table class="ai1ec-form ai1ec-location-form">
					<tbody>
						<?php echo $select_venue; ?>
						<tr>
							<td class="ai1ec-first">
								<label for="ai1ec_venue">
									<?php _e( 'Venue name:', AI1EC_PLUGIN_NAME ); ?>
								</label>
							</td>
							<td>
								<input type="text" name="ai1ec_venue" id="ai1ec_venue"
									class="ai1ec-form-control"
									value="<?php echo esc_attr( $venue ); ?>">
							</td>
						</tr>
						<tr>
							<td>
								<label for="ai1ec_address">
									<?php _e( 'Address:', AI1EC_PLUGIN_NAME ); ?>
								</label>
							</td>
							<td>
								<input type="text" name="ai1ec_address" id="ai1ec_address"
									class="ai1ec-form-control"
									value="<?php echo esc_attr( $address ); ?>">
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<label for="ai1ec_input_coordinates">
									<input type="checkbox" value="1" name="ai1ec_input_coordinates"
										id="ai1ec_input_coordinates" <?php echo $coordinates; ?>>
									<?php _e( 'Input Coordinates', AI1EC_PLUGIN_NAME ); ?>
								</label>
							</td>
						</tr>
						<?php echo $save_venue; ?>
					</tbody>
				</table>
				<table id="ai1ec_table_coordinates" class="ai1ec-form ai1ec-location-form">
					<tbody>
						<tr>
							<td class="ai1ec-first">
								<label for="ai1ec_latitude">
									<?php _e( 'Latitude:', AI1EC_PLUGIN_NAME ); ?>
								</label>
							</td>
							<td>
								<input type="text" name="ai1ec_latitude" id="ai1ec_latitude"
									class="ai1ec-coordinates ai1ec-form-control"
									value="<?php echo $latitude; ?>">
							</td>
						</tr>
						<tr>
							<td>
								<label for="ai1ec_longitude">
									<?php _e( 'Longitude:', AI1EC_PLUGIN_NAME ); ?>
								</label>
							</td>
							<td>
								<input type="text" name="ai1ec_longitude" id="ai1ec_longitude"
									class="ai1ec-coordinates ai1ec-form-control"
									value="<?php echo $longitude; ?>">
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="ai1ec-col-md-4 ai1ec-col-lg-6">
				<label for="ai1ec_google_map">
					<input type="checkbox" value="1" name="ai1ec_google_map"
						id="ai1ec_google_map" <?php echo $google_map; ?>>
					<?php _e( 'Show Map', AI1EC_PLUGIN_NAME ); ?>
				</label>
				<div class="ai1ec-map-preview
					<?php echo $show_map ? 'ai1ec-map-visible' : ''; ?>">
					<div id="ai1ec_map_canvas"></div>
				</div>
			</div>
		</div>
		<input type="hidden" name="ai1ec_city" id="ai1ec_city" value="<?php echo esc_attr( $city ); ?>">
		<input type="hidden" name="ai1ec_province" id="ai1ec_province" value="<?php echo esc_attr( $province ); ?>">
		<input type="hidden" name="ai1ec_postal_code" id="ai1ec_postal_code" value="<?php echo esc_attr( $postal_code ); ?>">
		<input type="hidden" name="ai1ec_country" id="ai1ec_country" value="<?php echo esc_attr( $country ); ?>">
		<input type="hidden" name="ai1ec_country_short" id="ai1ec_country_short" value="">
	</div>
</div>
