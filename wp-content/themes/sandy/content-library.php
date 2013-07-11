<?php
/**
 * The template used for displaying the Resourcs page content in page-custom.php
 *
 * @package WordPress
 * @subpackage Foghorn
 * @since Foghorn 0.1
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	
	<header class="entry-header">
		<h1 class="leader"><?php $post_type = get_post_type_object( get_post_type($post) );
		echo $post_type->labels->name ; ?></h1>
        <?php edit_post_link( __( 'Edit', 'foghorn' ), '<span class="edit-link">', '</span>' ); ?>

	</header><!-- .entry-header -->

	<div class="entry-content">
		<?php get_content_for_tax_archive('library'); ?>

		<h2>Topics</h2>
		<?php get_custom_taxonomy_list ('library-categories'); ?>

		<?php
		//The custom taxonomy that will be queried
		$taxonomy = 'library-categories';
		$custom_post_type = 'library';
		$custom_terms = get_terms($taxonomy);
		foreach($custom_terms as $custom_term) {
		    wp_reset_query();
		    $args = array('post_type' => $custom_post_type,
		        'tax_query' => array(
		            array(
		                'taxonomy' => $taxonomy,
		                'field' => 'slug',
		                'terms' => $custom_term->slug,
		            ),
		        ),
		     );
		     $loop = new WP_Query($args);
		     if($loop->have_posts()) {
		        //The topic heading
		        echo '<h2 class="category-title" id="' . $custom_term->slug. '">' . $custom_term->name . '</h2>';
				//The resource listings for the topic
				echo '<ul class="resources">';
		        while($loop->have_posts()) : $loop->the_post();
		        $source = get_post_meta( get_the_ID(), 'library-source', true ); 
		            echo '<li>';
		            echo '<h3 class="item-title"><a href="'.get_permalink().'">'.get_the_title().'</a></h3>';
		            echo '<cite rel="source">' . $source . '</cite>';
		            if (the_content()) {
		            	the_content( '', true );
		            }
		            echo '</li>';
		        endwhile;
		        echo '</ul>';
		     }
		}

		?>

	</div><!-- .entry-content -->

</article><!-- #post-<?php the_ID(); ?> -->
