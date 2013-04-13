<?php
/**
 * Template Name: Show Yer Cards
 *
 * The template for displaying externally-fetched cards.
 *
 * @package WordPress
 * @subpackage Foghorn
 * @since Foghorn 0.1
 */

//////////////////////////////////////////////////////////////////////
// LAYOUT: This kind of page will be forced into a 1 column layout. //
//////////////////////////////////////////////////////////////////////

function show_cards_body_class($classes) {
	$ret = array();
	$classFiltered = false;
	foreach ($classes as $idx => $class) :
		if (preg_match('/^layout-/i', $class)) :
			$class = 'layout-1c';
			$classFiltered = true;
		endif;
		$ret[] = $class;
	endforeach;

	if (!$classFiltered) :
		$ret[] = 'layout-1c';
	endif;

	return $ret;
}

add_filter('body_class','show_cards_body_class', 1000);

/////////////////////////////////////////////////////////////////////
// CARD MARKUP: Set up a bit of clean-up for ugly database values. //
/////////////////////////////////////////////////////////////////////

// Here is a way to clean up card titles that you don't like.
function betterCardTitles ($title, $card) {
	if ($title == 'Unknown') {
		$title = 'Other';
	}
	return $title;
}

// Here is a way to add or subtract classes on the fly.
function betterCardClasses ($classes, $card) {
	if ($card->has_type('Food\s*Not\s*Bombs')) :
		$classes[] = 'food';
	endif;
	return $classes;
}
add_filter('occupysandy_card_title', 'betterCardTitles', 2, /*priority=*/ 200);
add_filter('occupysandy_card_classes', 'betterCardClasses', 2, /*priority=*/ 200);

/////////////////////////////////////////////////////
// CARD MARKUP: Automate repetitive tasks.         //
/////////////////////////////////////////////////////

function options_filter_ul ($filters, $axis) {
	$prefix = $filters['prefix'];
	$lf = $filters['label-filter'];

	$allLabelText = 'All';
	if (isset($filters['label-text']) and isset($filters['label-text']['all'])) :
		$allLabelText = $filters['label-text']['all'];
	endif;
?>
<ul class="options-container options-for-<?php print $axis; ?><?php if (isset($filters['container-classes'])) : print ' '.$filters['container-classes']; endif; ?>">
<li><a href="#" id="filter-class-<?php print $axis; ?>-ALL" class="filter-class show-when-ALL selected"><?php print $allLabelText; ?></a></li>
<?php
foreach ($filters['values'] as $vv => $N) :
	$pair = array_map('urldecode', split("/", $vv, 2));
	$label = reset($pair);
	$value = end($pair);
	
	if (is_array($N) and isset($N['label'])) :
		$label = $N['label'];
	endif;
	
	$aClasses = array('filter-class');
	if (is_array($N) and isset($N['values'])) :
		$aClasses = array_merge($aClasses, array_map(function ($v) { return 'show-when-'.$v; }, $N['values']));
	endif;
	
?>
<li><a href="#" id="filter-class-<?php print $prefix . $value; ?>" class="<?php print implode(" ", $aClasses); ?>"><?php
if (strlen(trim($label)) > 0) :
	if (is_callable($lf)) :
		$label = $lf($label);
	endif;

	if (is_array($filters['label-text']) and isset($filters['label-text'][$value])) :
		$label = $filters['label-text'][$value];
	endif;
else :
	if (isset($filters['default'])) :
		$label = $filters['default'];
	else :
		$label = 'Other'; // A sensible default?
	endif;
endif;
print $label;
?></a></li>
<?php endforeach; ?>
</ul>
<?php
}

/////////////////////////////////////////////////////
// FILTERING: PREPARE OPTIONS FOR FILTERING LISTS. //
/////////////////////////////////////////////////////

$filters['type'] = array(
	'values' => get_occupy_sandy_possible_values_for('get_type_classes'),
	'prefix' => '',
	'label-filter' => 'ucfirst',
	'label-text' => array('all' => 'All Types', 'hub' => 'Distribution Centers', 'unknown' => 'Other'),
);
$filters['state'] = array(
	"values" => get_occupy_sandy_possible_values_for('get_state_classes'),
	"prefix" => '',
	'label-text' => array('all' => 'All States'),
	"label-filter" => 'strtoupper',
	'default' => 'Unlisted',
);
$filters['region'] = array(
	'values' => get_mapped_occupy_sandy_possible_values_for('get_region_classes', 'get_state_classes'),
	'prefix' => '',
	'default' => 'Other',
	'label-text' => array('all' => 'All Locales'),
	'container-classes' => "affected-by-state",
);

// Here is a way to clean up label text that you do not like.
$filterMap['type']['text'] = array('unknown' => 'Other');

// Sort these by # of cards, with blanks sorted down to the end.
if (isset($filters['state']['values'][''])) :
	$filters['state']['values'][''] = 0;
endif;
arsort($filters['state']['values']);

$filters['type']['values']['unknown'] = 0;
arsort($filters['type']['values']);

// Sort A-Z, with "Other" forced down to the end.
uksort($filters['region']['values'], function ($left, $right) {
		// Force "Other" to end of alpha order
		$a = (('region-other'==$left) ? '{|}' : $left);
		$b = (('region-other'==$right) ? '{|}' : $right);
		return strcmp($a, $b);
});

//////////////////////////////////////////////////////
// HEADER: This kind of page requires some scripts. //
//////////////////////////////////////////////////////

function isotope_scriptage () {
?>
<script type="text/javascript">
	isotopeContainer = '.container';
	isotopeFilter = { };

	jQuery(document).ready ( function () {
		/* Initialize Isotope. */
		jQuery(isotopeContainer).isotope({
			itemSelector: '.card', masonry: { columnWidth: 1}
		})
		jQuery('.filter-class').click ( function ( e ) {
			var filterAxisClasses = jQuery(this).closest('.options-container').attr('class').split(/\s+/);
			var filterAxis;
			var showWhenID;
			var filterHideInterval = 1000;
			
			for (var i = 0; i < filterAxisClasses.length; i++) {
				if (r = filterAxisClasses[i].match(/^options-for-(.*)$/)) {
					filterAxis = r[1];
				}				
			}

			var linkClasses = jQuery(this).attr('class').split(/\s+/);
			
			for (var i = 0; i < linkClasses.length; i++) {
				if (r = linkClasses[i].match(/^show-when-(.*)$/)) {
					showWhenID = r[1];
				}
			}
			
			var filtersOne2Many = jQuery('.options-container')
				.filter('.affected-by-'+filterAxis)
				.find('.filter-class');
			
			var filterClass = (jQuery(this).attr('id').replace(/^filter-class-/, ''));

			// Rewrite the value for this specific axis in the filtering
			if (filterClass.match(new RegExp('^' + filterAxis + '-ALL$')) ) {
				isotopeFilter[filterAxis] = null;
				filtersOne2Many.closest('li').removeClass('zapped').show(filterHideInterval);
				
				// Make sure that all sub-classes are dispalyed.
				jQuery(this).closest('.options-container').find('.filter-class').closest('li').filter('.subset-limited').show(filterHideInterval).removeClass('subset-limited');
				//.closest('li').show();
			} else {
				isotopeFilter[filterAxis] = '.' + filterClass;
				
				if (showWhenID && (jQuery('#filter-class-' + showWhenID).length > 0)) {
					var filtersMany2One = jQuery('#filter-class-' + showWhenID)
						.closest('.options-container')
						.find('.filter-class');
						
					var possibleFilters = '.show-when-ALL, #filter-class-' + showWhenID;
					filtersMany2One.filter(possibleFilters).closest('li').removeClass('subset-limited').show(filterHideInterval);
					filtersMany2One.filter(':not('+possibleFilters+')').closest('li').addClass('subset-limited').hide(filterHideInterval);
					jQuery('#filter-class-' + showWhenID).click();
				}
				
				// Hide irrelevant filters.
				if (filtersOne2Many.length > 0) {
					var selector = '.show-when-ALL, .show-when-'+filterClass;
					
					filtersOne2Many.filter(selector).closest('li')
						.removeClass('zapped').show(filterHideInterval);
					filtersOne2Many.filter(':not('+selector+')').closest('li')
						.addClass('zapped').hide(filterHideInterval);
						
					// Did we just hide the current filter? If so, reset to ALL.
					if (filtersOne2Many.filter('.selected').closest('li').filter(':not(.zapped)').length == 0) {
						filtersOne2Many.filter('.show-when-ALL').click();
					}
				}
			}

			// sigh
			var combinedFilters = filterSelector(isotopeFilter);
			if (combinedFilters.length > 0) {
				// If there is a filter on here, we should also add in the always-displayed tile.
				combinedFilters += ", .tile-always-display";
			} /* if */

			jQuery(isotopeContainer).isotope({ filter: combinedFilters }).isotope('reLayout');

			// Clear SELECTED class from all except this one.
			jQuery(this).closest('.options-container').find('.filter-class').removeClass('selected');
			jQuery(this).addClass('selected');

			// Avoid anchor behavior
			e.preventDefault();
			return false;
		} );
	} );

	function filterSelector (filter) {
		var ret = '';
		for (var x in filter) {
			if (typeof(filter[x])=='string') {
				ret = ret + filter[x]
			}
		} /* for */
		return ret;
	}

</script>
<?php
} /* isotope_scriptage() */

wp_enqueue_script('jquery');
wp_enqueue_script('isotope', get_stylesheet_directory_uri().'/extensions/isotope/jquery.isotope.min.js', array('jquery'));

add_action('wp_head', 'isotope_scriptage');

////////////////////////////
// TEMPLATE: OK, let's go //
////////////////////////////

get_header(); ?>

		<div id="primary">
			<div id="content" role="main">

		<div class="content-wrap clearfix">
			<?php the_post(); ?>

			<?php get_template_part( 'content', 'page' ); ?>
                </div>

		<?php comments_template( '', true ); ?>
		
		<?php
		foreach ($filters as $axis => $ff) :
			options_filter_ul($ff, $axis);
		endforeach;
		?>

		<div class="container">
		<?php the_occupy_sandy_cards(); ?>

		<div class="card tile-always-display">
			<h5 class="cardType">Start Your Own</h5>
			<p class="cardDetails">If there is an urgent need in the area you were searching in, please let us know at <a href="mailto:OccupySandy@interoccupy.net">OccupySandy@interoccupy.net</a></p>
		</div>
		</div>

			</div><!-- #content -->
		</div><!-- #primary -->

<?php get_footer(); ?>
