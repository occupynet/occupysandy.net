<?php

// include_once('functions-projects.php');
// include_once('functions-resources.php');
// include_once('functions-library.php');

/**
 * Tell WordPress to run foghorn_setup() when the 'after_setup_theme' hook is run.
 */
 
add_action( 'after_setup_theme', 'foghorn_setup' );

if ( ! function_exists( 'foghorn_setup' ) ):

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which runs
 * before the init hook. The init hook is too late for some features, such as indicating
 * support post thumbnails.
 *
 * To override foghorn_setup() in a child theme, add your own foghorn_setup to your child theme's
 * functions.php file.
 *
 * @uses load_theme_textdomain() For translation/localization support.
 * @uses add_editor_style() To style the visual editor.
 * @uses add_theme_support() To add support for post thumbnails and automatic feed links.
 * @uses register_nav_menus() To add support for navigation menus.
 * @uses add_custom_background() To add support for a custom background.
 * @uses set_post_thumbnail_size() To set a custom post thumbnail size.
 *
 * @since Foghorn 0.1
 */
 
function foghorn_setup() {

	/**
 	* Set the content width based on the theme's design and stylesheet.
 	*/
	if ( ! isset( $content_width ) )
		$content_width = 560;

	// Make Foghorn translatable
	load_theme_textdomain( 'foghorn', TEMPLATEPATH . '/languages' );

	$locale = get_locale();
	$locale_file = TEMPLATEPATH . "/languages/$locale.php";
	if ( is_readable( $locale_file ) )
		require_once( $locale_file );

	// Styles the visual editor with editor-style.css to match the theme style
	add_editor_style();

	// Add default posts and comments RSS feed links to <head>.
	add_theme_support( 'automatic-feed-links' );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menu( 'primary', __( 'Primary Menu', 'foghorn' ) );

	// Adds support for custom backgrounds
	add_custom_background();

	// Adds theme support for thumbnails
	add_theme_support( 'post-thumbnails' );
	
	// Creates an image thumbnail size for multiple displays
	add_image_size( 'multiple-thumb', 325, 205, true );
	
	// Sets up the option panel functions
	require_once(TEMPLATEPATH . '/extensions/options-functions.php');

}
endif; // foghorn_setup

/**
 * Sets the post excerpt length to 40 characters.
 */
 
function foghorn_excerpt_length( $length ) {
	return 60;
}
add_filter( 'excerpt_length', 'foghorn_excerpt_length' );

/**
 * Returns a "Continue Reading" link for excerpts
 */
 
function foghorn_continue_reading_link() {
	return ' <a href="'. get_permalink() . '">' . __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'foghorn' ) . '</a>';
}

/**
 * Replaces "[...]" (appended to automatically generated excerpts) with an ellipsis and foghorn_continue_reading_link().
 *
 */
 
function foghorn_auto_excerpt_more( $more ) {
	return ' &hellip;' . foghorn_continue_reading_link();
}
add_filter( 'excerpt_more', 'foghorn_auto_excerpt_more' );

/**
 * Adds a pretty "Continue Reading" link to custom post excerpts.
 *
 * To override this link in a child theme, remove the filter and add your own
 * function tied to the get_the_excerpt filter hook.
 */
 
function foghorn_custom_excerpt_more( $output ) {
	if ( has_excerpt() && ! is_attachment() ) {
		$output .= foghorn_continue_reading_link();
	}
	return $output;
}
add_filter( 'get_the_excerpt', 'foghorn_custom_excerpt_more' );


/**
 * Registers the sidebars and widgetized areas.
 *
 * @since Foghorn 0.1
 */
 
function foghorn_widgets_init() {

	register_sidebar( array(
		'name' => __( 'Sidebar', 'foghorn' ),
		'id' => 'sidebar',
		'description' => __( 'The right sidebar for posts and pages.', 'foghorn' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => "</aside>",
		'before_title' => '<h1 class="widget-title">',
		'after_title' => '</h1>',
	) );
	
	register_sidebar( array(
		'name' => __( 'Footer-left', 'foghorn' ),
		'id' => 'footer-left',
		'description' => __( 'The left footer widgets posts and pages.', 'foghorn' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => "</aside>",
		'before_title' => '<h3 class="footer-title">',
		'after_title' => '</h3>',
	) );
	
	register_sidebar( array(
		'name' => __( 'Footer-middle', 'foghorn' ),
		'id' => 'footer-middle',
		'description' => __( 'The middle footer widgets for posts and pages.', 'foghorn' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => "</aside>",
		'before_title' => '<h3 class="footer-title">',
		'after_title' => '</h3>',
	) );
	
	register_sidebar( array(
		'name' => __( 'Footer-right', 'foghorn' ),
		'id' => 'footer-right',
		'description' => __( 'The right footer widgets for posts and pages.', 'foghorn' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => "</aside>",
		'before_title' => '<h3 class="footer-title">',
		'after_title' => '</h3>',
	) );	
}
add_action( 'widgets_init', 'foghorn_widgets_init' );

register_nav_menu( 'global', 'Global Menu' );

/**
 * Display navigation to next/previous pages when applicable
 */
 
function foghorn_content_nav( $nav_id ) {
	global $wp_query;

	if ( $wp_query->max_num_pages > 1 ) : ?>
		<nav id="<?php echo $nav_id; ?>">
			<h1 class="section-heading"><?php _e( 'Post navigation', 'foghorn' ); ?></h1>
			<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'foghorn' ) ); ?></div>
			<div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'foghorn' ) ); ?></div>
		</nav><!-- #nav-above -->
	<?php endif;
}

/**
 * Comments
 */
 
if ( ! function_exists( 'foghorn_comment' ) ) :

/**
 * Template for comments and pingbacks.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 *
 * @since Foghorn 0.1
 */
 
function foghorn_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case 'pingback' :
		case 'trackback' :
	?>
	<li class="post pingback">
		<p><?php _e( 'Pingback:', 'foghorn' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __( '(Edit)', 'foghorn' ), ' ' ); ?></p>
	<?php
			break;
		default :
	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
		<article id="comment-<?php comment_ID(); ?>" class="comment">
			<footer class="comment-meta">
				<div class="comment-author vcard">
					<?php
						$avatar_size = 60;
						if ( '0' != $comment->comment_parent )
							$avatar_size = 40;

						echo get_avatar( $comment, $avatar_size );

						printf( __( '%1$s on %2$s%3$s at %4$s%5$s <span class="says">said:</span>', 'foghorn' ),
							sprintf( '<cite class="fn">%s</cite>', get_comment_author_link() ),
							'<a href="' . esc_url( get_comment_link( $comment->comment_ID ) ) . '"><time pubdate datetime="' . get_comment_time( 'c' ) . '">',
							get_comment_date(),
							get_comment_time(),
							'</time></a>'
						);
					?>

					<?php edit_comment_link( __( '[Edit]', 'foghorn' ), ' ' ); ?>
				</div><!-- .comment-author .vcard -->

				<?php if ( $comment->comment_approved == '0' ) : ?>
					<em class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'foghorn' ); ?></em>
					<br />
				<?php endif; ?>

			</footer>

			<div class="comment-content"><?php comment_text(); ?></div>

			<div class="reply">
				<?php comment_reply_link( array_merge( $args, array( 'reply_text' => __( 'Reply &darr;', 'foghorn' ), 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
			</div><!-- .reply -->
		</article><!-- #comment-## -->

	<?php
			break;
	endswitch;
}
endif; // ends check for foghorn_comment()


/// ADDED ///
global $os_regionToState;

$os_regionToState = array(
'rockaways' => 'NY',
'lower east side' => 'NY',
);

require_once('extensions/fusion/occupysandybackend.php');
require_once('extensions/fusion/occupysandyfrontend.php');
/*

function theme_enqueue_less() {
wp_enqueue_style('myCss', get_bloginfo('template_directory').'/style.less', array(), '0.7', 'screen, projection');
}
add_action('wp','theme_enqueue_less');

*/
function add_fonts() {
?>
	<!-- webfonts -->
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,400,700' rel='stylesheet' type='text/css'>
	<?php 
}
add_action('wp_head','add_fonts'); 

/*Resources required to display featured posts in a slider on the home page*/

function add_slider_resources() {
	?>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
	<script src="<?php bloginfo('template_directory');?>/js/slides.min.jquery.js"></script>

	<script>
		$(function(){
			$('#feature').slides({
				preload: true,
				container: 'feature_container',
				generateNextPrev: false,
				pagination: false,
				next: 'feature-next',
				prev: 'feature-back',
				currentClass: 'current',
				play: 5000
			});
		});
	</script>
 
<?php }

add_action('wp_head','add_slider_resources');

/*Add shortcode for adding the big image buttons on the homepage*/
/*Usage: [location-button url="http://yourwebsite.org" image="image.jpg"]Text[/location-button]*/

 function location_button($atts, $content = null) {  
     extract(shortcode_atts(array(  
         "url" => '',
         "image" => ''
     ), $atts));  
     return '<div class="promo" style="background-image:url(' .$image. '); background-repeat: no-repeat;">
     <a href="'.$url.'" class="home-location-button"><span class="home-location">'.$content.' &rsaquo;</span></a>
     </div>';
     
 }  
    
     add_shortcode("location-button", "location_button");
     
/*Add a filter to allow display of different excerpt lengths
**Usage: within a template, 
**Inside the loop -
**interocc_excerpt(); // regular excerpt (55 words)
**interocc_excerpt(30); // 30 words with formatting (<p>this is an excerpt ... </p>)
**get_interocc_excerpt(30); // 30 words without formatting (this is an excerpt ... )

**Outside the loop -
**pass a Post ID to the function (required outside the loop)
**interocc_excerpt(30, 22); // 30 word excerpt of Post with ID 22
**get_interocc_excerpt(30, 22); // 30 word excerpt of Post with ID 22
*/
 function interocc_excerpt($excerpt_length = 55, $id = false, $echo = true) {
          
     $text = '';
    
           if($id) {
                 $the_post = & get_post( $my_id = $id );
                 $text = ($the_post->post_excerpt) ? $the_post->post_excerpt : $the_post->post_content;
           } else {
                 global $post;
                 $text = ($post->post_excerpt) ? $post->post_excerpt : get_the_content('');
     }

                 $text = strip_shortcodes( $text );
                 $text = apply_filters('the_content', $text);
                 $text = str_replace(']]>', ']]&gt;', $text);
	              $text = strip_tags($text);
                 $excerpt_more = ' <a href="' .get_permalink(). '" class="continue-reading">Continue reading <span class="meta-nav">&rarr;</span>';
                 //$excerpt_more = ' ' . '[...]';
                 $words = preg_split("/[\n\r\t ]+/", $text, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY);
                 if ( count($words) > $excerpt_length ) {
                         array_pop($words);
                         $text = implode(' ', $words);
                         $text = $text . $excerpt_more;
                 } else {
                         $text = implode(' ', $words);
                 }
         if($echo)
   echo apply_filters('the_content', $text);
         else
         return $text;
 }
  
	function get_interocc_excerpt($excerpt_length = 55, $id = false, $echo = false) {
		return interocc_excerpt($excerpt_length, $id, $echo);
 }

//List terms in a given taxonomy
//Used to generate TOC for custom post types like Resources and Library

function get_custom_taxonomy_list($taxonomy) {
	$terms = get_terms($taxonomy);
	echo '<ul class="list-wrap">';
	foreach ( $terms as $term ) {
		echo '<li><a href="#' . $term->slug . '" title="' . $term->name . '" ' . '>' . $term->name.'</a></li>';
	}
	echo '</ul>';
} 

//Custom post types don't make good use of descriptions...
//Used to display intro/descriptive text for custom post types like Resources and Library
function get_content_for_tax_archive($pagename) {
	$args = array(
		'pagename' => $pagename
	);
	$taxpage = new WP_Query( $args );
	while ( $taxpage->have_posts() ) : $taxpage->the_post();
		the_content();
	endwhile;
	wp_reset_postdata();
}


?>