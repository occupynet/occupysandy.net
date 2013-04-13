<?php
/**
 * The template used for displaying the Library page content in page-custom.php
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

		<?php the_content(); ?>

		<h2>Topics</h2>

		<!-- From functions.php - accepts the taxonomy name and outputs TOC-type nav -->
		<?php get_custom_taxonomy_list ('library_categories'); ?>

		<?php
		//Specify taxomony to be used
		$taxonomy = 'library_categories';
		//Get array of taxonomy terms
		$taxonomy_query = get_terms($taxonomy);


		foreach ($taxonomy_query as $taxonomies => $taxonomy) {
			//Display the taxonomy name as a heading with IDanchor
			echo '<a id="' . $taxonomy->slug .  '"></a><h2 class="category-title">' . $taxonomy->name . '</h2>';
			echo '<ul class="library"><li class="entry-item">';

			//Get array of posts for taxonomy term
			$args = array(
				'post_type' => 'library_items',
				'taxonomy' => 'library_categories',
				'term' => $taxonomy->name,
				'orderby' => 'title',
				'order' => 'ASC'
			);
			$post_query = new WP_Query($args);

			if( $post_query->have_posts() ) {
			  while ($post_query->have_posts()) : $post_query->the_post(); ?>
				<h3 class="item-title">
					<?php
					//If the item links to an external source, link there
					if (get_post_custom_values('wpcf-resource-url')) {

						echo '<a href="';
						echo get_post_meta($post->ID, 'wpcf-library-link', true);
						echo '">';

					} else { //Or else link to the post ?>

						<a href="<?php the_permalink() ?>" title="<?php the_title(); ?>">

					<? } ?>
					<?php the_title(); ?></a></h3>
					<?php
					//If there is a source listing, display it
					if (get_post_custom_values('wpcf-library-source')) {
						echo '<cite rel="source">' . get_post_meta($post->ID, 'wpcf-library-source', true) . '</cite>';
					}
					?>
					<?php the_content(); ?>
			    <?php
			  endwhile;
			}
			echo '</li></ul>';
		}
		wp_reset_query();  // Restore global post data stomped by the_post().
		?>



	</div><!-- .entry-content -->

</article><!-- #post-<?php the_ID(); ?> -->
