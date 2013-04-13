<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal">×</button>
	<h2><?php _e( 'Post Your Event', AI1EC_PLUGIN_NAME ); ?></h2>
</div>

<div class="modal-body">
	<?php if ( $message_type !== 'success' ) : ?>
		<div class="alert alert-<?php echo $message_type; ?>">
			<?php echo $message; ?>
		</div>
	<?php else : ?>
		<?php echo $message; ?>
	<?php endif; ?>
</div>

<div class="modal-footer">
	<a href="#" class="btn btn-large btn-primary pull-left ai1ec-post-another">
		<i class="icon-plus"></i>
		<?php _e( 'Post Another', AI1EC_PLUGIN_NAME ); ?>
	</a>
	<a href="<?php echo esc_attr( $link_url ); ?>"
		class="btn btn-large btn-primary">
		<?php echo $link_text; ?>
		<i class="icon-arrow-right"></i>
	</a>
</div>
