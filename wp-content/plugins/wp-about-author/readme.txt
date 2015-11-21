=== WP About Author ===
Contributors: JonBishop
Donate link: http://www.jonbishop.com/donate/
Tags: author, author bio, author box, post, widget, bio, twitter, facebook, about, about author, author biography, avatar, user box, wp about author, guest author, guest post
Requires at least: 3.0
Tested up to: 3.8.1
Stable tag: 1.5

Easily display customizable author bios below your posts

== Description ==

This plugin is the easiest way to add a customizable author bio below your posts. The plugin works right out of the box with WordPress built in profiles.

Customization capabilities include

1. Three border styles to match any theme
1. Change background color with easy to use color picker
1. Display settings allow you to control when to display author bios
1. Display text links or icons to a users social media profiles
1. Change the size and shape of your avatar

This plugin also expands your profile page by adding popular social media fields so it's easier for readers to follow your authors.

== Installation ==

1. Upload the 'wp-about-author' folder to the '/wp-content/plugins/' directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Edit your WordPress user profile
1. Use the settings page to choose when to display bios


== Frequently Asked Questions ==

= Can I change the background color of the box? =

Of course your can. It's one of the options available to you in the settings panel.

= Can I change the layout of the content in the box? =

As of version 1.3 you can. All you have to do is create your own HTML using the following templates tags and use a filter.

Templates tags are: %%bordertype%%, %%borderbg%%, %%authorpic%% and %%content%%

Example:
Add the following to a functionality plugin or your functions.php file
`<?php
function my_wp_about_author_template(){
    return '<div class="wp-about-author-containter-%%bordertype%%" style="background-color:%%borderbg%%;"><div class="wp-about-author-pic">%%authorpic%%</div><div class="wp-about-author-text">%%content%%</div></div>';
}
add_filter('wp_about_author_template', 'my_wp_about_author_template');
?>`

= Can I add additional social media links? =

As of version 1.3 you can. Once again using a filter you can intercept the data that generates these links and add your own.

Example:
Add the following to a functionality plugin or your functions.php file
`<?php
function my_wp_about_author_social($social){
    $social['my_service_key'] = array(
        'link'=>'http://www.myservice.com/%%username%%',
        'title'=>'My Service',
        'icon'=>'http://www.fullpathtoicon.com/icon.png'
    );
    return '<div class="wp-about-author-containter-%%bordertype%%" style="background-color:%%borderbg%%;"><div class="wp-about-author-pic">%%authorpic%%</div><div class="wp-about-author-text">%%content%%</div></div>';
}
add_filter('wp_about_author_get_socials', 'my_wp_about_author_social');
?>`

= Can I filter the author box from additional pages not listed in my settings? =

Sure thing. Now there's a WordPress filter for that.

Example:
Add the following to a functionality plugin or your functions.php file to exclude the author box from a page with the id equal to 100
`<?php
function my_wp_about_author_display($author_content){
    if(is_page(100))
        return "";
    return $author_content;
}
add_filter('wp_about_author_display', 'my_wp_about_author_display');
?>`


= Are there any other filters that let me modify the output? =

The following filters have been added:
* `wp_about_author_name()` - Modify the output of the name in the author box
* `wp_about_author_description()` - Modify the output of the description in the author box
* `wp_about_author_more_posts()` - Modify the "More Posts" text in the author box
* `wp_about_author_website()` - Modify the "Website" text in the author box
* `wp_about_author_follow_me()` - Modify the "Follow Me:" text in the author box
* `wp_about_author_separator()` - Change the separator displayed between text links


== Screenshots ==

1. Author bio below a post
2. WP About Author settings page


== Changelog ==

The current version is 1.5 (2014.4.5)

= 1.5 (2014.4.5) =
* Fixed update
* Fixing title alignment with avatar
* Fixing display of text area next to avatar

= 1.4 (2014.3.29) =
* Fixed conflict with WordPress SEO
* Can change size and shape of Avatar image
* Fixed broken color picker
* Added link to Settings page from Plugins page

= 1.3 (2012.2.23) =
* Added social media icons as an alternative to text links
* Optimized code and added actions and filters for developers to modify output
* Cleaned up settings area

= 1.2 (2011.7.13) =
* Added option to display bio in feed

= 1.1 (2010.11.30) =
* Updated Reame
* Fixed formatting issues

= 1.0 =
* Plugin released