<?php
/**
 * Template Name: Full Width
 *
 * The template for full width content.
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

get_header(); ?>

		<div id="primary">
			<div id="content" role="main">

		<div class="content-wrap clearfix">
			<?php the_post(); ?>

			<?php get_template_part( 'content', 'page' ); ?>
                </div>

		<?php //comments_template( '', true ); ?>

			</div><!-- #content -->
		</div><!-- #primary -->

<?php get_footer(); ?>
