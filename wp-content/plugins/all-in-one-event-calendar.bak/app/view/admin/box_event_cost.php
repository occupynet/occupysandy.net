<div class="accordion-heading">
	<a class="accordion-toggle" data-toggle="ai1ec_collapse"
		data-parent="#ai1ec-add-new-event-accordion"
		href="#ai1ec-event-cost-box">
		<i class="icon-shopping-cart"></i> <?php _e( 'Event cost and Tickets', AI1EC_PLUGIN_NAME ); ?>
	</a>
</div>
<div id="ai1ec-event-cost-box" class="accordion-body collapse">
	<div class="accordion-inner">
		<table class="ai1ec-form">
			<tbody>
				<tr>
					<td class="ai1ec-first">
						<label for="ai1ec_cost">
							<?php _e( 'Cost', AI1EC_PLUGIN_NAME ); ?>:
						</label>
					</td>
					<td>
						<input type="text" name="ai1ec_cost" id="ai1ec_cost" value="<?php echo $cost; ?>" />
					</td>
				</tr>
				<tr>
					<td>
						<label for="ai1ec_ticket_url">
							<?php _e( 'Buy Tickets URL', AI1EC_PLUGIN_NAME ); ?>:
						</label>
					</td>
					<td>
						<input type="text" name="ai1ec_ticket_url" id="ai1ec_ticket_url" value="<?php echo $ticket_url; ?>" />
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
