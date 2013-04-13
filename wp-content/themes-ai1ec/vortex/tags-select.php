<input type="text" class="ai1ec-tags-selector"
	id="<?php echo $id; ?>" name="<?php echo $name; ?>"
	data-placeholder="<?php esc_attr_e( 'Tags (optional)', AI1EC_PLUGIN_NAME ); ?>"
	data-ai1ec-tags='<?php echo $tags_json; ?>'
	value="<?php echo $selected_tag_ids; ?>" />
