<div class="ai1ec-pagination btn-group">
	<?php foreach ( $links as $link ) : ?>
		<?php if ( is_array( $link ) ) : ?>
			<a class="<?php echo $link['class']; ?> ai1ec-load-view btn"
				<?php echo $data_type; ?>
				href="<?php echo esc_attr( $link['href'] ); ?>"
				<?php if ( ! $link['enabled'] ): ?>disabled="disabled"<?php endif; ?>>
				<?php echo $link['text']; ?>
			</a>
		<?php else : ?>
			<?php $link->render(); ?>
		<?php endif; ?>
	<?php endforeach; ?>
</div><!--/.ai1ec-pagination-->
