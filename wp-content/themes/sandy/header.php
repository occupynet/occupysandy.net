<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package WordPress
 * @subpackage Foghorn
 * @since Foghorn 0.1
 */
?><!DOCTYPE html>
<!--[if lt IE 9]>
<html id="ie" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 6) | !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width" />
<title><?php
	/*
	 * Print the <title> tag based on what is being viewed.
	 */
	global $page, $paged;

	wp_title( '|', true, 'right' );

	// Add the blog name.
	bloginfo( 'name' );

	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		echo " | $site_description";

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 )
		echo ' | ' . sprintf( __( 'Page %s', 'foghorn' ), max( $paged, $page ) );

	?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="icon" type="image/png" href="http://interoccupy.net/wp-content/uploads/2012/07/IO-favicon.png" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_directory' ); ?>/css/style.css" />

<!--[if lt IE 9]>
<script src="<?php echo get_template_directory_uri(); ?>/js/html5.js" type="text/javascript"></script>
<![endif]-->
<?php
	/* We add some JavaScript to pages with the comment form
	 * to support sites with threaded comments (when in use).
	 */
	if ( is_singular() && get_option( 'thread_comments' ) )
		wp_enqueue_script( 'comment-reply' );

	/* Always have wp_head() just before the closing </head>
	 * tag of your theme, or you will break many plugins, which
	 * generally use this hook to add elements to <head> such
	 * as styles, scripts, and meta tags.
	 */
	wp_head();
?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="hfeed">
	<header id="branding" role="banner" class="clearfix">
			<nav id="global">
				<ul id="menu-global-menu">
					<li class="globalLogo"><a href="http://interoccupy.net">Inter<span>occupy.net</span></a></li>
					<li class="navAbout"><a href="http://interoccupy.net/about/">About</a></li>
					<li class="navNewsire"><a href="http://interoccupy.net/newswire/">Newswire</a></li>
					<li class="navHubs"><a href="http://interoccupy.net/hubs/">Hubs</a></li>
					<li class="navCalendar"><a href="http://interoccupy.net/calendar/">Calendar</a></li>
					<li class="navServices"><a href="http://interoccupy.net/services/">Services</a></li>
					<li class="navJoin"><a href="http://interoccupy.net/get-involved/">Get Involved</a></li>
				</ul>
			</nav>

			<hgroup>
				<h1 id="site-title"><span><a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home">
				<?php if ( of_get_option('logo', false) ) { ?>
					<img src="<?php echo of_get_option('logo'); ?>" alt="<?php bloginfo( 'name' ) ?>" />
				<?php } else {
					bloginfo( 'name' );
				}?>
				</a></span></h1>
				<div id="carousel">
					<a href="http://statigr.am/viewer.php#/detail/319004559331671253_25813182" target="_blank"><img src="<?php bloginfo('stylesheet_directory'); ?>/images/carousel/occupysandy8.jpg" alt="occupysandy" width="150" height="150"></a>

					<a href="http://occupysandy.net/wp-content/uploads/2013/03/occupysandy105sq.jpg" target="_blank"><img src="http://occupysandy.net/wp-content/uploads/2013/03/occupysandy105sqsm.jpg" alt="occupysandy" width="150" height="150"></a>
					<a href="http://statigr.am/viewer.php#/detail/321866875885911625_7937366" target="_blank"><img src="<?php bloginfo('stylesheet_directory'); ?>/images/carousel/occupysandy13.jpg" alt="occupysandy" width="150" height="150"></a>
					<a href="http://occupysandy.net/wp-content/uploads/2013/03/occupysandy107sq.jpg" target="_blank"><img src="http://occupysandy.net/wp-content/uploads/2013/03/occupysandy107sqsm.jpg" alt="occupysandy" width="150" height="150"></a>

					<a href="http://statigr.am/viewer.php#/detail/319003987153974191_2484027" target="_blank"><img src="<?php bloginfo('stylesheet_directory'); ?>/images/carousel/occupysandy10.jpg" alt="occupysandy" width="150" height="150"></a>
					<a href="http://occupysandy.net/wp-content/uploads/2013/03/occupysandy100sq.jpg" target="_blank"><img src="http://occupysandy.net/wp-content/uploads/2013/03/occupysandy100sqsm.jpg" alt="occupysandy" width="150" height="150"></a>
					<a href="http://statigr.am/viewer.php#/detail/319050062774329015_6968766" target="_blank"><img src="<?php bloginfo('stylesheet_directory'); ?>/images/carousel/occupysandy7.jpg" alt="occupysandy" width="150" height="150"></a>
					<a href="http://occupysandy.net/wp-content/uploads/2013/03/occupysandy114sq.jpg" target="_blank"><img src="http://occupysandy.net/wp-content/uploads/2013/03/occupysandy114sqsm.jpg" alt="occupysandy" width="150" height="150"></a>
					<a href="http://statigr.am/viewer.php#/detail/321708886318182164_30381338" target="_blank"><img src="<?php bloginfo('stylesheet_directory'); ?>/images/carousel/occupysandy11.jpg" alt="occupysandy" width="150" height="150"></a>


					<a href="http://statigr.am/viewer.php#/detail/321792944812413008_244169435" target="_blank"><img src="<?php bloginfo('stylesheet_directory'); ?>/images/carousel/occupysandy5.jpg" alt="occupysandy" width="150" height="150"></a>
					<a href="http://statigr.am/viewer.php#/detail/319006263469863879_2484027" target="_blank"><img src="<?php bloginfo('stylesheet_directory'); ?>/images/carousel/occupysandy9.jpg" alt="occupysandy" width="150" height="150"></a>
					<a href="http://occupysandy.net/wp-content/uploads/2013/03/occupysandy113sq.jpg" target="_blank"><img src="http://occupysandy.net/wp-content/uploads/2013/03/occupysandy113sqsm.jpg" alt="occupysandy" width="150" height="150"></a>
					<a href="http://occupysandy.net/wp-content/uploads/2013/03/occupysandy115sq.jpg" target="_blank"><img src="http://occupysandy.net/wp-content/uploads/2013/03/occupysandy115sqsm.jpg" alt="occupysandy" width="150" height="150"></a>
					<a href="http://statigr.am/viewer.php#/detail/321802059520514427_28563429" target="_blank"><img src="<?php bloginfo('stylesheet_directory'); ?>/images/carousel/occupysandy4.jpg" alt="occupysandy" width="150" height="150"></a>
					<a href="http://statigr.am/viewer.php#/detail/321731426281349892_4626303" target="_blank"><img src="<?php bloginfo('stylesheet_directory'); ?>/images/carousel/occupysandy14.jpg" alt="occupysandy" width="150" height="150"></a>

					<a href="http://occupysandy.net/wp-content/uploads/2013/03/occupysandy108sq.jpg" target="_blank"><img src="http://occupysandy.net/wp-content/uploads/2013/03/occupysandy108sqsm.jpg" alt="occupysandy" width="150" height="150"></a>
				</div>
                <?php if ( of_get_option('tagline',false) ) { ?>
					<h2 id="site-description"><?php bloginfo( 'description' ); ?></h2>
                <?php } ?>
			</hgroup>

			<nav id="access" role="navigation">
				<h1 class="section-heading"><?php _e( 'Main menu', 'foghorn' ); ?></h1>
				<?php /*  Allow screen readers / text browsers to skip the navigation menu and get right to the good stuff. */ ?>
				<div class="skip-link screen-reader-text"><a href="#content" title="<?php esc_attr_e( 'Skip to content', 'foghorn' ); ?>"><?php _e( 'Skip to content', 'foghorn' ); ?></a></div>
				<?php /* Our navigation menu.  If one isn't filled out, wp_nav_menu falls back to wp_page_menu. The menu assiged to the primary position is the one used. If none is assigned, the menu with the lowest ID is used. */ ?>
				<?php wp_nav_menu( array( 'theme_location' => 'primary' ) ); ?>
			</nav><!-- #access -->
	</header><!-- #branding -->


	<div id="main" class="clearfix">
