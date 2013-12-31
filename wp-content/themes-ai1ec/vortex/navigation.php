<div class="clearfix">
	<h2 class="ai1ec-calendar-title"><?php echo esc_html( $title ); ?></h2>
	<div class="ai1ec-title-buttons btn-toolbar pull-right">
		<?php
			if ( ! empty( $before_pagination ) ) {
				echo $before_pagination;
			}
			echo $pagination_links;
		?>
	</div>
</div>
