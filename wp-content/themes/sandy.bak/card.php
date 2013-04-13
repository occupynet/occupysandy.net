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
<div class="<?php print implode(" ", $classes); ?>">
<h5 class="cardType"><?php print $card->get_card_heading(); ?><?php if (strlen($state) > 0) :
?> <span class="stateface stateface-replace stateface-<?php print strtolower($state); ?>"><?php print strtolower($state); ?></span><?php
endif; ?></h5>
<h2 class="cardName"><?php print $card->get_title(); ?></h2>

<?php if (strlen($address) > 0) : ?>
<h5 class="cardAddress"><?php print $address; ?></h5>
<?php endif; ?>

<?php if (strlen($updated) > 0) : ?>
<h5 class="cardUpdated">Updated <?php print $updated; ?></h5>
<?php endif; ?>

<?php if (strlen($status) > 0) : ?>
<p class="cardStatus"><span>Status:</span> <?php print $status; ?></p>
<?php endif; ?>

<?php if (strlen($description) > 0) : ?>
<p class="cardDetails"><span>Details:</span> <?php print $description; ?></p>
<?php endif; ?>

<?php if (strlen($times) > 0) : ?>
<h5 class="cardTimes"><span>Times:</span> <?php print $times; ?></h5>
<?php endif; ?>

<?php if (strlen($contact) > 0) : ?>
<h5 class="cardContact"><span>Contact:</span> <?php print $contact; ?></h5>
<?php endif; ?>

<?php if (strlen($link) > 0) : ?>
<h5 class="cardLink"><a href="<?php print $link; ?>">More info</a></h5>
<?php endif; ?>

</div>
<?php
endif;

