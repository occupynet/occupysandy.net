<?php if ( count( $available_views ) > 1 ) : ?>
<li class="ai1ec-views-dropdown dropdown">
	<a class="dropdown-toggle" data-toggle="dropdown">
		<img src="<?php echo $this->get_theme_img_url( "$current_view-view.png" ); ?>" alt="<?php echo $view_names[$current_view]; ?>" />
		<?php echo $view_names[$current_view]; ?>
		<span class="caret"></span>
	</a>
	<div class="dropdown-menu">
		<?php foreach ( $available_views as $key => $values ) : ?>
			<div class="<?php if ( $key === $current_view ) echo 'active'; ?>">
				<a id="ai1ec-view-<?php echo $key; ?>" <?php echo $data_type; ?>
					class="ai1ec-load-view <?php echo $key; ?>"
					href="<?php echo $values['href']; ?>">
					<img src="<?php echo $this->get_theme_img_url( $key . "-view.png" ); ?>" alt="<?php echo $values['desc']; ?>" />
					<?php echo $values['desc']; ?>
				</a>
			</div>
		<?php endforeach; ?>
	</div>
</li>
<?php endif; ?>
