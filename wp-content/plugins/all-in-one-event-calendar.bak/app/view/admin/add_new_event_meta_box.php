<div class="timely">
	<div class="accordion form-inline" id="ai1ec-add-new-event-accordion">
		<?php foreach ( $boxes as $box ) : ?>
			<div class="accordion-group">
				<?php echo $box; ?>
			</div>
		<?php endforeach; ?>
	</div>
	<?php if ( ! empty( $publish_button ) ) : ?>
		<?php echo $publish_button; ?>
	<?php endif; ?>
</div>
