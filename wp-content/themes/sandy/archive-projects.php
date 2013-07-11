<?php
/**
 * Template Name: Projects page
 * The template for displaying the Projects page.
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

          <?php get_template_part( 'content', 'projects' ); ?>
          
        </div>

        <?php //comments_template( '', true ); ?>

      </div><!-- #content -->
    </div><!-- #primary -->

<?php if ( of_get_option('layout','layout-2cr') != 'layout-1c') {
  get_sidebar();
} ?>
<?php get_footer(); ?>