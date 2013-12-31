<li class="dropdown ai1ec-author-filter <?php
	if ( ! empty( $selected_auth_ids ) ) {
		echo 'active';
	}
?>">
	<a class="dropdown-toggle" data-toggle="dropdown">
		<i class="icon-user"></i>
		<span class="ai1ec-clear-filter ai1ec-tooltip-trigger"
			data-href="<?php echo $clear_filter; ?>"
			<?php echo $data_type; ?>
			title="<?php _e( 'Clear author filter', AI1EC_PLUGIN_NAME ); ?>">
			<i class="icon-remove-sign"></i>
		</span>
		<?php _e( 'Authors', AI1EC_PLUGIN_NAME ); ?>
		<span class="caret"></span>
	</a>
	<div class="dropdown-menu">
		<?php foreach( $authors as $author ): ?>
			<div data-term="<?php echo $author->user_id; ?>"
				<?php if (
					in_array( $author->user_id, $selected_auth_ids )
				) : ?>
					class="active"
				<?php endif; ?>
				>
				<a class="ai1ec-load-view ai1ec-author"
					<?php if ( $author->display_name ): ?>
						title="<?php echo esc_attr( $author->display_name ); ?>"
					<?php endif; ?>
					<?php echo $data_type; ?>
					href="<?php echo $author->href; ?>">
					<?php echo esc_html( $author->display_name ); ?>
				</a>
			</div>
		<?php endforeach; ?>
	</div>
</li>

