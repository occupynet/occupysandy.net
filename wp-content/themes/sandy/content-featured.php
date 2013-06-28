<?php
/**
 * The template used for displaying homepage content
 *
 * @package WordPress
 * @subpackage Foghorn
 * @since Foghorn 0.1
 */
?>

<?php $feed_heading =  get_post_meta($post->ID, 'home-feed-heading', true); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
  
  <header class="entry-header">
    <h1 class="leader">
      <?php the_title(); ?>
    </h1>
  </header>
  
  <!-- .entry-header -->
  
  <div class="entry-content">
    <?php 
	$temp = $wp_query; $wp_query= null;
	$wp_query = new WP_Query(); 
	$wp_query->query(array('post__in'=>get_option('sticky_posts'), 'posts_per_page' => 1, 'ignore_sticky_posts' => 1));

	while ($wp_query->have_posts()) : $wp_query->the_post(); 
	?>
    <?php if( has_post_thumbnail() ) { ?>
    <div class="post-thumbnail"> <a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>">
      <?php the_post_thumbnail('multiple-thumb'); ?>
      </a> </div>
    <?php } ?>
    <h1 style="line-height: 1em; margin: -8px 0 0 0;"> <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
      <?php the_title(); ?>
      </a> </h1>
    <footer class="entry-meta">
      <time datetime="<?php echo the_time('Y-m-j'); ?>" pubdate>
        <?php the_time('F jS, Y'); ?>
      </time>
    </footer>
    <p class="follower"> <?php the_excerpt('300'); ?> </p>
    <?php endwhile; ?>
    <?php
	query_posts( array( 
		'post__not_in' => get_option( 'sticky_posts' ),
		'posts_per_page' => 5,
		'offset' => 1,
		 ) );
	?>
    <div style="clear:both;"  class="feature home-feeds" id="feature">
      <h2 class="promos-title"><?php echo $feed_heading; ?></h2>
      <?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
      <article id="post-<?php the_ID(); ?>" <?php post_class(' clearfix'); ?>>
        <header class="entry-header"><a href="<?php the_permalink(); ?>" title="Read more">
          <?php the_title(); ?>
          </a>
          <?php if( has_post_thumbnail() ) { ?>
          <div class="post-thumbnail"> <a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>">
            <?php the_post_thumbnail('multiple-thumb'); ?>
            </a> </div>
          <?php } ?>
        </header>
        <footer class="entry-meta">
          <time datetime="<?php echo the_time('Y-m-j'); ?>" pubdate>
            <?php the_time('F jS, Y'); ?>
          </time>
        </footer>
        <div class="entry-summary"> <?php the_excerpt(); ?> </div>
      </article>
      <?php endwhile; // end of the loop. ?>
    </div>
    <!-- .featured-posts -->
    
    <div>
      <?php wp_reset_query(); ?>

		<?php the_content(); ?>
  </div>
  <!-- .entry-content --> 
</article>
<footer>
  <?php edit_post_link( __( 'Edit', 'foghorn' ), '<span class="edit-link">', '</span>' ); ?>
</footer>
<!-- #post-<?php the_ID(); ?> --> 
