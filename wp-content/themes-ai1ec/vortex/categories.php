<li class="dropdown ai1ec-category-filter <?php if( ! empty( $selected_cat_ids ) ) echo "active"; ?>">
	<a class="dropdown-toggle" data-toggle="dropdown">
		<i class="icon-folder-open"></i>
		<span class="ai1ec-clear-filter ai1ec-tooltip-trigger"
			data-href="<?php echo $clear_filter; ?>"
			<?php echo $data_type; ?>
			title="<?php _e( 'Clear category filter', AI1EC_PLUGIN_NAME ); ?>">
			<i class="icon-remove-sign"></i>
		</span>
		<?php _e( 'Categories', AI1EC_PLUGIN_NAME ); ?>
		<span class="caret"></span>
	</a>
	<div class="dropdown-menu">
		<?php foreach( $categories as $term ): ?>
			<div data-term="<?php echo $term->term_id; ?>"
				<?php if( in_array( $term->term_id, $selected_cat_ids ) ) : ?>
					class="active"
				<?php endif; ?>
				>
				<a class="ai1ec-load-view ai1ec-category"
					<?php if( $term->description ): ?>
						title="<?php echo esc_attr( $term->description ); ?>"
					<?php endif; ?>
					<?php echo $data_type; ?>
					href="<?php echo $term->href; ?>">
					<?php echo $term->color; ?>
					<?php echo esc_html( $term->name ); ?>
				</a>
			</div>
		<?php endforeach; ?>
	</div>
</li>

