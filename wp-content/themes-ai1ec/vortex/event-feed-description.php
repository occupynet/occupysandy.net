<div>
	<div>
		<span><?php _e( 'When:', AI1EC_PLUGIN_NAME ); ?></span>
		<b><?php echo $timespan; ?></b>
	</div>
	<?php if ( $location ) : ?>
		<div>
		  <span><?php _e( 'Where:', AI1EC_PLUGIN_NAME ); ?></span>
		  <b><?php echo $location; ?></b>
		</div>
	<?php endif; ?>
</div>
<?php if ( $description ) : ?>
<div>
	<?php echo $description; ?>
</div>
<?php endif; ?>
