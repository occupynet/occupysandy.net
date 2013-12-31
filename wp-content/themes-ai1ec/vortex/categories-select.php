<select class="ai1ec-categories-selector ai1ec-select2-multiselect-selector"
	id="<?php echo $id; ?>" name="<?php echo $name; ?>"
	placeholder="<?php esc_attr_e( 'Categories (optional)', AI1EC_PLUGIN_NAME ); ?>"
	data-placeholder="<?php esc_attr_e( 'Categories (optional)', AI1EC_PLUGIN_NAME ); ?>"
	multiple="multiple">
	<?php foreach ( $categories as $term ) : ?>
		<option value="<?php echo $term->term_id; ?>"
      <?php if ( in_array( $term->term_id, $selected_cat_ids ) ) : ?>
      	selected="selected"
      <?php endif; ?>
			<?php if ( isset( $term->description ) ) : ?>
				data-description="<?php echo esc_attr( $term->description ); ?>"
			<?php endif; ?>
			<?php if ( isset( $term->color ) ) : ?>
				data-color="<?php echo esc_attr( $term->color ); ?>"
			<?php endif; ?>
			>
			<?php echo esc_html( $term->name ); ?>
		</option>
	<?php endforeach; ?>
</select>
