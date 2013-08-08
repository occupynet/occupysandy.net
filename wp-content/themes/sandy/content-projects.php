<?php
/**
 * The template used for displaying the Resourcs page content in page-custom.php
 *
 * @package WordPress
 * @subpackage Foghorn
 * @since Foghorn 0.1
 */
?>

<article id="post-7099" class="post-7099 page type-page status-publish hentry">
	<header class="entry-header">
		<h1 class="leader"><?php the_title(); ?></h1>
	</header><!-- .entry-header -->

	<div class="entry-content">

		<p class="follower">
	    <!-- subtitle -->
		</p>

	</div><!-- .entry-content -->

	<div class="entry-content">

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
			} ?>

		<?php
		    // Page content is edited in the Projects Page ('pagename=projects')
		    $your_query = new WP_Query( 'pagename=projects' );
		    while ( $your_query->have_posts() ) : $your_query->the_post();
		        the_content();
		    endwhile;
		    wp_reset_postdata();
		?>

      <table  class="pro">
	      <thead>
	        <tr>
	          <th>Title</th>
	          <th>Description</th>
	          <th>Status</th>
	          <th>Profile</th>
	        </tr>
	      </thead>
	    <tbody>

	    <?php $args = array( 'post_type' => 'projects');
		    $loop = new WP_Query( $args );
		    while ( $loop->have_posts() ) : $loop->the_post(); 
			    $status = get_post_meta( get_the_ID(), 'project-status', true ); 
			    $spokescouncil_member = get_post_meta( get_the_ID(), 'project_spokescouncil_member', true ); 

			    //If project not a spokescouncil member, skip
			    // if($spokescouncil_member) {
			    ?>

				<tr>
					<td class="pro-name"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></td>
					<td class="pro-blurb"><?php if( $post->post_excerpt ) { echo $post->post_excerpt; } ?></td>
					<td class="pro-status"><?php 
					if($spokescouncil_member) {
						echo 'Spokescouncil Member';
					} else {
						echo 'Not a Spokescouncil Member';
					} ?></td>
					<td class="pro-link"><a href="<?php the_permalink(); ?>"></a></td>
				</tr>

	    <?php //} //end if
	    endwhile;
	    ?>
	       </tbody>
	   </table>

	</div><!-- .entry-content -->
</article><!-- #post-7099 -->
