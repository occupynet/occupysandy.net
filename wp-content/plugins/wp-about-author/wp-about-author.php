<?php
/*
Plugin Name: WP About Author
Plugin URI: http://www.jonbishop.com/downloads/wordpress-plugins/wp-about-author/
Description: Easily display customizable author bios below your posts
Version: 1.5
Author: Jon Bishop
Author URI: http://www.jonbishop.com
License: GPL2
*/

if (!defined('WPAUTHORURL_URL')) {
	define('WPAUTHORURL_URL', plugin_dir_url(__FILE__));
}
if (!defined('WPAUTHORURL_PATH')) {
	define('WPAUTHORURL_PATH', plugin_dir_path(__FILE__));
}
if (!defined('WPAUTHORURL_BASENAME')) {
    define('WPAUTHORURL_BASENAME', plugin_basename(__FILE__));
}
if (!defined('WPAUTHORURL_VER')) {
    define('WPAUTHORURL_VER', '17');
}

require_once(WPAUTHORURL_PATH."/wp-about-author-admin.php");
//require_once(WPAUTHORURL_PATH."/wp-about-author-services.php");

// Add box below post with share buttons and subscribe/comment text
function wp_about_author_display($for_feed = false){
	global $post;

  	$wp_about_author_settings=array();
	$wp_about_author_settings=get_option('wp_about_author_settings');
	
	$wp_about_author_content = "";
    $wp_about_author_links = "";
    $wp_about_author_social = "";
	$wp_about_author_author_pic =  "";
	$wp_about_author_author = array();
	$wp_about_author_author['name'] = get_the_author();
	$wp_about_author_author['description'] = get_the_author_meta('description');
	$wp_about_author_author['website'] = get_the_author_meta('url');
	$wp_about_author_author['posts'] = (int)get_the_author_posts();
  	$wp_about_author_author['posts_url'] = get_author_posts_url(get_the_author_meta('ID'));
	$wp_about_author_author_pic = get_avatar(get_the_author_meta('email'), $wp_about_author_settings['wp_author_avatar_size']);
        
        // About Author Title
	$wp_about_author_content .= "<h3><a href='" . $wp_about_author_author['posts_url']. "' title='". $wp_about_author_author['name'] ."'>". apply_filters( 'wp_about_author_name', $wp_about_author_author['name'] ) ."</a></h3>";
	
        // About Author Description
        $wp_about_author_content .= "<p>"  .apply_filters( 'wp_about_author_description', $wp_about_author_author['description']) . "</p>";
        
        // About Author Links
        if(!empty($wp_about_author_author['posts_url'])){
            $wp_about_author_links .= "<a href='" . $wp_about_author_author['posts_url']. "' title='More posts by ". $wp_about_author_author['name'] ."'>".apply_filters( 'wp_about_author_more_posts', "More Posts")."</a> ";
        }
        if(!empty($wp_about_author_author['website'])){
            if($wp_about_author_links!=""){$wp_about_author_links.=apply_filters( 'wp_about_author_separator', " - ");}
            $wp_about_author_links .= "<a href='" . $wp_about_author_author['website']. "' title='". $wp_about_author_author['name'] ."'>".apply_filters( 'wp_about_author_website', "Website")."</a> ";
        }
        
        // About Author Social
        $wp_about_author_social .= wp_about_author_get_social_links($wp_about_author_settings);
        if(isset($wp_about_author_settings['wp_author_social_images']) && $wp_about_author_settings['wp_author_social_images']){
            $wp_about_author_content .= "<p>"  .$wp_about_author_links . "</p>";
            if($wp_about_author_social != ""){
                $wp_about_author_content .= '<p class="wpa-nomargin">'.apply_filters( 'wp_about_author_follow_me', "Follow Me:").'<br />' . $wp_about_author_social.'</p>';
            }
        } else {
            $wp_about_author_content .= "<p class='wpa-nomargin'>";
            $wp_about_author_content .= $wp_about_author_links;
            if($wp_about_author_social != ""){
                $wp_about_author_content .= apply_filters( 'wp_about_author_separator', " - ") . $wp_about_author_social;
            }
            $wp_about_author_content .= "</p>";
        }

        // Avatar size and shape
		$wp_about_author_avatar_class = 'wp-about-author-pic';
		if($wp_about_author_settings['wp_author_avatar_shape'] === "on"){
			$wp_about_author_avatar_class .= ' wp-about-author-circle';
		}
		$wp_about_author_text_margin = ($wp_about_author_settings['wp_author_avatar_size'] + 40) . 'px';

        // Create output
        $return_content = '';
        // Allow filters to create new templates for output
        if (!$for_feed){
            $return_content = apply_filters( 'wp_about_author_template','<div class="wp-about-author-containter-%%bordertype%%" style="background-color:%%borderbg%%;"><div class="'.$wp_about_author_avatar_class.'">%%authorpic%%</div><div class="wp-about-author-text" style="margin-left:'.$wp_about_author_text_margin.'">%%content%%</div></div>');
        } else {
            $return_content = apply_filters( 'wp_about_author_feed_template','<p><div style="float:left; text-align:left;>%%authorpic%%</div>%%content%%</p>');
        }
        $replace_array = array(
            '%%bordertype%%'=>$wp_about_author_settings['wp_author_alert_border'],
            '%%borderbg%%'=>$wp_about_author_settings['wp_author_alert_bg'],
            '%%authorpic%%'=>$wp_about_author_author_pic,
            '%%content%%'=>$wp_about_author_content
        );
        foreach($replace_array as $search=>$replace){
            $return_content = str_replace($search, $replace, $return_content);
        }

        return apply_filters( 'wp_about_author_display', $return_content );
}


// Add buttons to page
function insert_wp_about_author($content) {
	$wp_about_author_settings = wp_about_author_get_options();
    // Make sure we have defaults
    add_defaults_wp_about_author($wp_about_author_settings);
	
    if(is_front_page() && isset($wp_about_author_settings['wp_author_display_front']) && $wp_about_author_settings['wp_author_display_front']){
		$content.=wp_about_author_display();
	} else if(is_archive() && isset($wp_about_author_settings['wp_author_display_archives']) && $wp_about_author_settings['wp_author_display_archives']){
		$content.=wp_about_author_display();
	} else if(is_search() && isset($wp_about_author_settings['wp_author_display_search']) && $wp_about_author_settings['wp_author_display_search']){
		$content.=wp_about_author_display();
	} else if(is_page() && isset($wp_about_author_settings['wp_author_display_pages']) && $wp_about_author_settings['wp_author_display_pages']){
		$content.=wp_about_author_display();
	} else if(is_single() && isset($wp_about_author_settings['wp_author_display_posts']) && $wp_about_author_settings['wp_author_display_posts']){
		$content.=wp_about_author_display();
        } else if(is_feed() && isset($wp_about_author_settings['wp_author_display_feed']) && $wp_about_author_settings['wp_author_display_feed']){
		$content.=wp_about_author_display(true);
	} else {
		$content=$content;	
	}
	return $content;
}

// Generate social icons
function wp_about_author_get_social_links($wp_about_author_settings){
        $content="";
        $socials = wp_about_author_get_socials();
        foreach($socials as $social_key=>$social){
            if (get_the_author_meta($social_key)){
                if(isset($wp_about_author_settings['wp_author_social_images']) && $wp_about_author_settings['wp_author_social_images']){
                    $content .= "<a class='wpa-social-icons' href='".str_replace('%%username%%', get_the_author_meta($social_key), $social['link'])."'><img src='". $social['icon']."' alt='".$social['title']."'/></a>";
                } else {
                    if($content != "")
                        $content .= apply_filters( 'wp_about_author_separator', " - ");
                    $content .= "<a href='".str_replace('%%username%%', get_the_author_meta($social_key), $social['link'])."'>".$social['title']."</a>";
                }
            }
        }
        return $content;
}

function wp_about_author_get_socials() {
        $socials = array();
    	$socials['twitter'] = array('title'=>'Twitter', 'link'=>'http://www.twitter.com/%%username%%', 'icon'=> WPAUTHORURL_URL .'images/twitter.png');
        $socials['facebook'] = array('title'=>'Facebook', 'link'=>'http://www.facebook.com/%%username%%', 'icon'=> WPAUTHORURL_URL .'images/facebook.png');
        $socials['linkedin'] = array('title'=>'LinkedIn', 'link'=>'http://www.linkedin.com/in/%%username%%', 'icon'=> WPAUTHORURL_URL .'images/linkedin.png');
        $socials['pinterest'] = array('title'=>'Pinterest', 'link'=>'http://www.pinterest.com/%%username%%', 'icon'=> WPAUTHORURL_URL .'images/pinterest.png');
        if(defined( 'WPSEO_VERSION')){
       		$socials['googleplus'] = array('title'=>'Google Plus', 'link'=>'%%username%%', 'icon'=> WPAUTHORURL_URL .'images/googleplus.png');
        } else {
             $socials['googleplus'] = array('title'=>'Google Plus', 'link'=>'https://plus.google.com/%%username%%', 'icon'=> WPAUTHORURL_URL .'images/googleplus.png');
        }
        $socials['digg'] = array('title'=>'Digg', 'link'=>'http://www.digg.com/%%username%%', 'icon'=> WPAUTHORURL_URL .'images/digg.png');
        $socials['flickr'] = array('title'=>'Flickr', 'link'=>'http://www.flickr.com/people/%%username%%', 'icon'=> WPAUTHORURL_URL .'images/flickr.png');
        $socials['stumbleupon'] = array('title'=>'StumbleUpon', 'link'=>'http://www.stumbleupon.com/stumbler/%%username%%', 'icon'=> WPAUTHORURL_URL .'images/stumbleupon.png');
        $socials['youtube'] = array('title'=>'YouTube', 'link'=>'http://www.youtube.com/user/%%username%%', 'icon'=> WPAUTHORURL_URL .'images/youtube.png');
        $socials['yelp'] = array('title'=>'Yelp', 'link'=>'http://www.yelp.com/user_details?userid=%%username%%', 'icon'=> WPAUTHORURL_URL .'images/yelp.png');
        $socials['reddit'] = array('title'=>'Reddit', 'link'=>'http://www.reddit.com/user/%%username%%', 'icon'=> WPAUTHORURL_URL .'images/reddit.png');
        $socials['delicious'] = array('title'=>'Delicious', 'link'=>'http://www.delicious.com/%%username%%', 'icon'=> WPAUTHORURL_URL .'images/delicious.png');
        return apply_filters( 'wp_about_author_get_socials', $socials );
}

// Add css to header
function wp_about_author_style() {
	wp_enqueue_style('wp-author-bio', WPAUTHORURL_URL . 'wp-about-author.css');	
}

function wp_about_author_filter_contact($contactmethods) {
	unset($contactmethods['yim']);
	unset($contactmethods['aim']);
	unset($contactmethods['jabber']);
	$socials = wp_about_author_get_socials();
        foreach($socials as $social_key=>$social){
            $contactmethods[$social_key] = $social['title'];
        }
	$contactmethods['yim'] = 'Yahoo IM';
	$contactmethods['aim'] = 'AIM';
	$contactmethods['jabber'] = 'Jabber / Google Talk';

	return $contactmethods;
}

register_activation_hook(__FILE__, 'add_defaults_wp_about_author');
// Define default option settings
function add_defaults_wp_about_author($tmp = "") {
    if(empty($tmp)){
        $tmp = get_option('wp_about_author_settings');
    }

    // Check to see if we're up to date
    if(intval($tmp['wp_author_version']) >= WPAUTHORURL_VER){
        return false;
    }

    if(!is_array($tmp)) {
		$tmp = array(
    		"wp_author_installed"=>"on",
    		"wp_author_version"=>"16",
    		"wp_author_alert_bg"=>"#FFEAA8",
    		"wp_author_display_front"=>"on",
    		"wp_author_display_archives"=>"on",
    		"wp_author_display_search"=>"",
    		"wp_author_display_posts"=>"on",
    		"wp_author_display_pages"=>"on",
            "wp_author_display_feed"=>"",
    		"wp_author_alert_border"=>"top",
            "wp_author_social_images"=>"on",
            "wp_author_avatar_size"=>"100",
            "wp_author_avatar_shape"=>""
		);
		update_option('wp_about_author_settings', $tmp);
	}
    if (!$tmp['wp_author_social_images']){
            $tmp['wp_author_version'] = "14";
            $tmp['wp_author_display_feed'] = "";
            update_option('wp_about_author_settings', $tmp);
    }
    if (!$tmp['wp_author_display_feed']){
            $tmp['wp_author_version'] = "15";
            $tmp['wp_author_display_feed'] = "";
            update_option('wp_about_author_settings', $tmp);
    }
    if (intval($tmp['wp_author_version']) < 17){
            $tmp['wp_author_version'] = "17";
            if(!isset($tmp['wp_author_avatar_size']))
                $tmp['wp_author_avatar_size'] = "100";
            if(!isset($tmp['wp_author_avatar_shape']))
                $tmp['wp_author_avatar_shape'] = "";
            update_option('wp_about_author_settings', $tmp);
    }
}

function wp_about_author_shortcode( ){
	wp_about_author_display();
}

add_shortcode( 'wp_about_author', 'wp_about_author_shortcode' );

add_action('admin_menu','add_wp_about_author_options_subpanel');
add_action('admin_print_scripts', 'add_wp_about_author_admin_scripts');
add_action('admin_print_styles', 'add_wp_about_author_admin_styles');
add_filter('plugin_action_links_' . WPAUTHORURL_BASENAME, 'wp_about_author_plugin_settings_link');

add_action('wp_print_styles', 'wp_about_author_style' );

add_filter('user_contactmethods', 'wp_about_author_filter_contact');
add_filter('the_content', 'insert_wp_about_author');

?>