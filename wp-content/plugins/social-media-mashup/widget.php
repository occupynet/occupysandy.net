<?php

add_action( 'widgets_init', 'smm_widget' );

function smm_widget() {
	register_widget( 'SMM_Social_Media_Mashup_Widget' );
}

class SMM_Social_Media_Mashup_Widget extends WP_Widget {
	
	function SMM_Social_Media_Mashup_Widget() {
		
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'social-media-mashup', 'description' => __( 'Combined social media & RSS feeds', BNM_LOCALE ) );

		/* Widget control settings. */
		$control_ops = array( 'id_base' => 'smm-widget' );

		/* Create the widget. */
		$this->WP_Widget( 'smm-widget', __( 'Social Media Mashup', BNM_LOCALE ), $widget_ops, $control_ops );
		
	}
	
	function widget( $args, $instance ) {
		extract( $args );
		
		global $wpdb;

		/* Before widget (defined by themes). */
		echo $before_widget;
		
		extract( $instance );
		
		/* Title of widget (before and after defined by themes). */
		if ( $title )
			echo $before_title . $title . $after_title;
		
		social_media_mashup( $count );
		
		/* After widget (defined by themes). */
		echo $after_widget;
	}
	
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		/* Strip tags (if needed) and update the widget settings. */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['count'] = strip_tags( $new_instance['count'] );

		return $instance;
	}
	
	function form( $instance ) {
		
		/* Set up some default widget settings. */
		$defaults = array( 'title' => __( 'Social Stream', BNM_LOCALE ), 'count' => 5 );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', BNM_LOCALE ); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'Number of items to show:', BNM_LOCALE ); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" value="<?php echo $instance['count']; ?>" />
		</p>
		
		<p><?php printf( __( "The rest of this widget's options are set on the %s plugin settings page%s.", BNM_LOCALE ), '<a href="options-general.php?page=smm-options">', '</a>' ); ?></p>
		
	<?php }
	
}

?>