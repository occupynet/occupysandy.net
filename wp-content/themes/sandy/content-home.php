<?php
/**
 * The template used for displaying homepage content
 *
 * @package WordPress
 * @subpackage Foghorn
 * @since Foghorn 0.1
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
  <header class="entry-header">
    <h1 class="leader">
      <?php the_title(); ?>
    </h1>
    <?php edit_post_link( __( 'Edit', 'foghorn' ), '<span class="edit-link">', '</span>' ); ?>
  </header>
  <!-- .entry-header -->
  
  <div class="entry-content">
    <p class="follower"> <?php echo get_post_meta($post->ID, 'custom_tagline', true); ?> </p>
    
    <?php
	/* Get all sticky posts */
	$sticky = get_option( 'sticky_posts' );

	/* Sort the stickies with the newest ones at the top */
	rsort( $sticky );

	/* Get the 5 newest stickies (change 5 for a different number) */
	$sticky = array_slice( $sticky, 0, 5 );

	/* Query sticky posts */
	query_posts( array( 'post__in' => $sticky, 'caller_get_posts' => 1 ) );
	?>

    <div style="clear:both;"  class="feature home-feeds" id="feature">
		<div class="feature-box">
			<h2 class="feature-title">Newswire</h2>
			<nav class="feature-nav">
				<button class="feature-next">▸</button>
				<button class="feature-back">◂</button>
				<br>
			</nav>
			<div class="feature_container">
			<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
				<div class="feature-slide">
					<h3><a href="<?php the_permalink(); ?>" title="<?php the_title() ?>" rel="bookmark"><?php echo get_interocc_excerpt(25); ?></a></h3>
				</div>
			<?php endwhile; // end of the loop. ?>
			</div>
		</div>
	</div>
    <!-- .featured-posts -->

     <?php wp_reset_query(); ?> 

	  <?php the_content(); ?>

  </div>
  <!-- .entry-content --> 
</article>
<!-- #post-<?php the_ID(); ?> --> 
