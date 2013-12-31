<!-- START All-in-One Event Calendar Plugin - Version <?php echo AI1EC_VERSION; ?> -->
<div id="ai1ec-container" class="ai1ec-container">
	<!-- AI1EC_PAGE_CONTENT_PLACEHOLDER -->
	<div id="ai1ec-calendar" class="timely">
		<?php echo $filter_menu->get_content(
			$disable_standard_filter_menu
		); ?>
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
