<!-- START All-in-One Event Calendar Plugin - Version <?php echo AI1EC_VERSION; ?> -->
<div id="ai1ec-container" class="ai1ec-container">
	<!-- AI1EC_PAGE_CONTENT_PLACEHOLDER -->
	<div class="timely">
		<div class="ai1ec-calendar-toolbar clearfix">
			<?php if ( $contribution_buttons ) : ?>
				<div class="pull-right">
					<?php echo $contribution_buttons; ?>
				</div>
			<?php endif; ?>
			<ul class="nav nav-pills pull-left"><?php echo $views_dropdown; ?></ul>
			<?php if ( $categories || $tags ) : ?>
				<ul class="nav nav-pills pull-left">
					<?php echo $categories;?>
					<?php echo $tags;?>
				</ul>
			<?php endif; // $categories || $tags ?>
		</div><!-- /.ai1ec-calendar-toolbar -->

		<div id="ai1ec-calendar-view-container">
			<div id="ai1ec-calendar-view-loading" class="ai1ec-loading"></div>
			<div id="ai1ec-calendar-view">
				<?php echo $view; ?>
			</div>
		</div>

		<?php echo $subscribe_buttons; ?>
	</div><!-- /.timely -->
</div>
<!-- END All-in-One Event Calendar Plugin -->
