<li class="dropdown ai1ec-tag-filter <?php if( ! empty( $selected_tag_ids ) ) echo "active"; ?>">
	<a class="dropdown-toggle" data-toggle="dropdown">
		<i class="icon-tags"></i>
		<span class="ai1ec-clear-filter ai1ec-tooltip-trigger"
			data-href="<?php echo $clear_filter; ?>"
			<?php echo $data_type; ?>
			title="<?php _e( 'Clear tag filter', AI1EC_PLUGIN_NAME ); ?>">
			<i class="icon-remove-sign"></i>
		</span>
		<?php _e( 'Tags', AI1EC_PLUGIN_NAME ); ?>
		<span class="caret"></span>
	</a>
	<div class="dropdown-menu">
		<?php foreach( $tags as $term ): ?>
			<span data-term="<?php echo $term->term_id; ?>"
				<?php if( in_array( $term->term_id, $selected_tag_ids ) ) : ?>
					class="active";
				<?php endif; ?>
				<?php if( $term->description ): ?>
					title="<?php echo esc_attr( $term->description ); ?>"
				<?php endif; ?>
				>
				<a class="ai1ec-load-view ai1ec-tag" <?php echo $data_type; ?>
					<?php if( $term->count > 1 ): ?>
						style="font-weight: bold;"
					<?php else: ?>
						style="font-size: 11px;"
					<?php endif; ?>
					href="<?php echo $term->href; ?>" >
					<?php echo esc_html( $term->name ) . " ($term->count)"; ?>
				</a>
			</span><!--/.ai1ec-tag-->
		<?php endforeach; ?>
	</div><!--/.ai1ec-filter-selector-->
</li><!--/.ai1ec-filter-selector-container-->
