<?php
	// Prepare data for use in template.
	$card = get_the_occupy_sandy_card();

if (is_wp_error($card)) : 
?>
<div class="card error <?php print $card->get_error_code(); ?>">
<h5 class="cardType">Back-End Error</h5>
<p><?php print $card->get_error_message(); ?></p>
</div>

<?php
else :
	$classes = array('card', $card->get_card_class());
	$state = $card->get_state();
	$address = $card->get_address();
	$timestamp = $card->get_timestamp('U');
	$status = $card->get_status();
	$times = $card->get_times();
	$contact = $card->get_contact();
	$link = $card->get_link(); // Is this supposed to be a URL or HTML? I'm assuming HTML for now.
	$description = $card->get_description();

	// Pretty-print the date.
	$today = time(); $yesterday = time() - (24*3600);

	if (is_numeric($timestamp)) :
		$datestamp = date('Y-m-d', $timestamp);
		if (date('Y-m-d', $today) == $datestamp) :
			$updated = 'today ';
		elseif (date('Y-m-d', $yesterday) == $datestamp) :
			$updated = 'yesterday ';
		else :
			$updated = date('M j, ', $timestamp);
		endif;
		$updated .= date('ga', $timestamp);
	else :
		$updated = $timestamp;
	endif;

?>
<div class="<?php print implode(" ", $classes); ?> mini">

	<a href="<?php print $link; ?>" class="aCard">
	
		<?php if (strlen($link) > 0) : ?>
		<h5 class="cardLink pictogram">➦</h5>
		<?php endif; ?>
		
		<h2 class="cardName"><?php print $card->get_title(); ?></h2>
		
		<?php if (strlen($status) > 0) : ?>
		<p class="cardStatus"><?php print $status; ?>&hellip;</p>
		<?php endif; ?>

		<?php if (strlen($updated) > 0) : ?>
		<h5 class="cardUpdated">Updated <?php print $updated; ?></h5>
		<?php endif; ?>
	
	</a>

</div>
<?php
endif;

