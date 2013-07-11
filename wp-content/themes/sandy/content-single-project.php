<?php
/**
 * The template for displaying content in the single.php template
 *
 * @package WordPress
 * @subpackage Foghorn
 * @since Foghorn 0.1
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('clearfix'); ?>>
	<header class="entry-header">
		<h1 class="entry-title"><?php the_title(); ?></h1>
		<div class="entry-meta">
			<?php
			// 	printf( __( '<span class="sep">Posted </span><a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s" pubdate>%4$s</time></a>', 'foghorn' ),
			// 	esc_url( get_permalink() ),
			// 	esc_attr( get_the_time() ),
			// 	esc_attr( get_the_date( 'c' ) ),
			// 	esc_html( get_the_date() )
			// );
			?>
			<?php $meta = get_post_meta( get_the_ID() ); 
			function display_post_meta($meta) {
				$postmeta = get_post_meta(get_the_ID(), $meta, true);
				echo get_post_meta(get_the_ID(), $meta, true);
			}
			function has_meta_value($meta) {
				$postmeta = get_post_meta(get_the_ID(), $meta, true);
				if (!empty($postmeta)) {
					return 'true';
				}
			}
			?>
			<p><?php if(has_meta_value('project-facebook-link')) { ?>
				<a href="<?php echo get_post_meta( get_the_ID(), 'project-facebook-link', true ); ?>" target="_blank" class="button btnSmall">Facebook</a> <?php } ?>
				<?php if(has_meta_value('project-twitter-handle')) { ?>
				<a href="http://<?php echo get_post_meta( get_the_ID(), 'project-twitter-handle', true ); ?>" target="_blank" class="button btnSmall">Twitter</a><?php } ?>
			</p>

		</div><!-- .entry-meta -->
	</header><!-- .entry-header -->
	<div class="entry-content">
		<?php if( $post->post_excerpt ) { ?>
			<p><?php echo $post->post_excerpt; ?></p>
		<?php } ?>
	    
	    <div class="project-content">
	    	<?php if( has_post_thumbnail() ) { ?><div class="project-image"><?php the_post_thumbnail('multiple-thumb'); ?></div>
			<?php } ?>
		    <?php the_content(); ?>
	    	<div class="project-overview"></div>
	    	<?php if(has_meta_value('project-status')) { ?>
	    	<?php if(has_meta_value('help-needed')) { ?>
	    	<h2 class="project-section-title">Help needed</h2>
	    	<p><?php display_post_meta('help-needed') ?></p>
	    	<?php } ?>
	    	<div class="project-buttons">
	    		<?php if(has_meta_value('project-donate-link')) { ?>
				<a class="button" title="Donate" href="<?php display_post_meta('project-donate-link'); ?>">Donate</a>
				<?php } ?>
				<?php if(has_meta_value('project-volunteer-link')) { ?>
				<a class="button" title="Volunteer" href="<?php display_post_meta('project-volunteer-link'); ?>">Volunteer</a>
				<?php } ?>
			</div>
			<?php } ?>

			<?php if(has_meta_value('project-background')) { ?>
			<h2 class="project-section-title">Background</h2>
			<p><?php display_post_meta('project-background'); ?></p>
			<?php } ?>

			<?php if(has_meta_value('project-partners')) { ?>
			<h2 class="project-section-title">Partners</h2>
			<p><?php display_post_meta('project-partners') ?></p>
			<?php } ?>

			<h2 class="project-section-title">Contact</h2>
			<ul>
				<li><?php display_post_meta('project-contact-name-external') ?></li>
				<li><a href="mailto:<?php display_post_meta('project-website') ?>" target="_blank"><?php display_post_meta('project-contact-email-external') ?></a></li>
				<?php if(has_meta_value('project-contact-phone-external')) { ?><li><span>Phone:</span> <?php display_post_meta('project-contact-phone-external') ?></li><?php } ?>
				<?php if(has_meta_value('project-website')) { ?><li><span>Website:</span> <a href="<?php display_post_meta('project-website') ?>" target="_blank"><?php display_post_meta('project-website') ?></a></li><?php } ?>
				<?php if(has_meta_value('project-facebook-link')) { ?><li><span>Facebook:</span> <a href="<?php display_post_meta('project-facebook-link') ?>" target="_blank"><?php display_post_meta('project-facebook-link') ?></a></li><?php } ?>
				<?php if(has_meta_value('project-twitter-handle')) { ?><li><span>Twitter:</span> <a href="<?php display_post_meta('project-twitter-handle') ?>" target="_blank"><?php display_post_meta('project-twitter-handle') ?></a></li><?php } ?>
				<?php if(has_meta_value('project-flickr')) { ?><li><span>Flickr:</span> <a href="<?php display_post_meta('project-flickr') ?>" target="_blank"><?php display_post_meta('project-flickr') ?></a></li><?php } ?>
			</ul>
	    </div>

	    <p>
			<a class="button btn" href="/projects/"> View All Projects </a>
		</p>
		
        <?php edit_post_link( __( 'Edit', 'foghorn' ), '<span class="edit-link">', '</span>' ); ?>
	</div><!-- .entry-content -->
 </article><!-- #post-<?php the_ID(); ?> -->

<footer class="entry-meta">
</footer><!-- .entry-meta -->