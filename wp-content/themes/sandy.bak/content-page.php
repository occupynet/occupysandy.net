<?php
/**
 * The template used for displaying page content in page.php
 *
 * @package WordPress
 * @subpackage Foghorn
 * @since Foghorn 0.1
 */
?>



<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<h1 class="leader"><?php the_title(); ?></h1>
        <?php edit_post_link( __( 'Edit', 'foghorn' ), '<span class="edit-link">', '</span>' ); ?>

	</header><!-- .entry-header -->

	<div class="entry-content">

		<p class="follower">
			<?php echo get_post_meta($post->ID, 'home-page-blurb', true); ?>
		</p>

	</div><!-- .entry-content -->


	<!-- If it's the front page, show any sticky posts -->
	<?php if ( is_front_page() ) { ?>

        <?php // Proceed only if sticky posts exist.
        $sticky = get_option('sticky_posts');
        if ( ! empty( $sticky ) ) { ?>

		<?php query_posts(array('post__in'=>get_option('sticky_posts'))); ?>

		<?php echo get_option('sticky_posts'); ?>

			<div class="feature">
				<h2 class="feature-title">Newswire:</h2>
				<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
						<p><button class="feature-next">▸</button><button class="feature-back disabled">◂</button></p>
						<h3 class="feature-headline"><?php the_excerpt(); ?></h3>
				<?php endwhile; // end of the loop. ?>
			</div>

		<?php wp_reset_query(); // reset the query to get back to regularly schedule content ?>

	<?php } ?>

	<?php } ?>

	<div class="entry-content">

		<?php get_template_part( 'content', 'feature' ); ?>

		<?php the_content(); ?>

	</div><!-- .entry-content -->
</article><!-- #post-<?php the_ID(); ?> -->
