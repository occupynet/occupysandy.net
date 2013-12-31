<?php if ( $show_add_your_calendar || $show_post_your_event ) : ?>
	<div class="btn-group">
		<?php if ( $show_add_your_calendar ) : ?>
			<a href="#ai1ec-submit-ics-modal" class="btn btn-primary btn-mini"
				data-toggle="ai1ec_modal">
				<i class="icon-plus"></i>
				<?php _e( 'Add Your Calendar Feed', AI1EC_PLUGIN_NAME ); ?>
			</a>
		<?php endif; ?>
		<?php if ( $show_post_your_event ) : ?>
			<?php if ( $show_front_end_create_form ) : ?>
			<a href="#ai1ec-create-event-modal" class="btn btn-primary btn-mini"
				data-toggle="ai1ec_modal">
			<?php else : ?>
			<a href="<?php echo $create_event_url; ?>" class="btn btn-primary">
			<?php endif; ?>
				<i class="icon-plus"></i>
				<?php _e( 'Post Your Event', AI1EC_PLUGIN_NAME ); ?>
			</a>
		<?php endif; ?>
	</div>
<?php endif; ?>

<?php echo $modals; ?>
