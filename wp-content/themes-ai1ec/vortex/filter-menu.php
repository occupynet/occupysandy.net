	<div class="timely">
		<div class="ai1ec-calendar-toolbar clearfix">
			<?php if ( $contribution_buttons ) : ?>
				<div class="pull-right">
					<?php echo $contribution_buttons; ?>
				</div>
			<?php endif; ?>
			<ul class="nav nav-pills pull-left"><?php echo $views_dropdown; ?></ul>
			<?php if ( ( $categories || $tags || $authors ) && $show_dropdowns ) : ?>
				<ul class="nav nav-pills pull-left ai1ec-filters">
						<?php echo $categories; ?>
						<?php echo $tags; ?>
						<?php echo $authors; ?>
				</ul>

			<?php endif; ?>
				<div class='pull-left'>
					<?php $save_view_btngroup->render(); ?>
				</div>
			<?php if ( $show_select2 ) : ?>
				<div class="row-fluid ai1ec-select2-filters">
					<?php foreach ( array( $categories, $tags, $authors ) as $select2 ) : ?>
						<?php
							if ( empty( $select2 ) ) {
								continue;
							}
						?>
						<div class="<?php echo $span_for_select2; ?>">
							<?php echo $select2; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div><!-- /.ai1ec-calendar-toolbar -->
	</div>
