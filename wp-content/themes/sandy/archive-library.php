<?php
/**
 * Template Name: Library page
 * The template for displaying the Library page.
 *
 *
 * @package WordPress
 * @subpackage Foghorn
 * @since Foghorn 0.1
 */

get_header(); ?>

		<div id="primary">
			<div id="content" role="main">
            
				<div class="content-wrap clearfix">
					<?php the_post(); ?>

					<?php get_template_part( 'content', 'library' ); ?>
                </div>

				<?php //comments_template( '', true ); ?>

			</div><!-- #content -->
		</div><!-- #primary -->

<?php if ( of_get_option('layout','layout-2cr') != 'layout-1c') {
	get_sidebar();
} ?>
<?php get_footer(); ?>