=== Social Media Mashup ===
Contributors: bravenewmedia
Tags: social media, facebook, twitter, google+, google plus, youtube, flickr, stream
Requires at least: 3.0
Tested up to: 3.2.1
Stable tag: trunk

Combine Twitter, Facebook, Google+, Flickr, YouTube, and custom RSS feeds into one stream.

== Description ==

Social Media Mashup provides a widget and template tag to display a combined social media & RSS stream. A simple options panel and minimal CSS make this plugin easy to install and visually compatible on any WordPress site.

See the [screenshots](http://wordpress.org/extend/plugins/social-media-mashup/screenshots/) for an example of the plugin in action.

**Note:** Twitter stopped directly supporting RSS feeds a short while ago, and as a result Twitter feeds are intermittently disappearing from the mashup. We are currently working on a solution to make the Twitter feeds more reliable. **In the meantime,** use the caching feature of this plugin to increase the reliability of your Twitter feed.

== Installation ==

1. Unzip the file and upload the `social-media-mashup` folder to the `/wp-content/plugins/` directory
1. Make sure the `smm-cache` folder inside the plugin folder is writeable by the server (permissions set to 755, 775, or 777 depending on your web host)
1. Activate the plugin through the **Plugins** menu in WordPress
1. Go to **Settings > Social Media Mashup** to enter social media & feed information
1. See the instructions in the upper-right of the settings page for the shortcode & template tag

== Frequently Asked Questions ==

= Is there a shortcode? =

There is now! To insert a mashup into any post or page, use this shortcode in the content area:

`[social-media-mashup count="5"]`

Change `count` to customize the number of items displayed.

= What's the template tag? =

Insert this code into your theme to display a mashup:

`<?php
if (function_exists('social_media_mashup'))
	social_media_mashup(5);
?>`

Change `5` to customize the number of items displayed.

== Have another question? ==

Check out our support forums or submit a ticket at [Brave New Media Support](http://bnm.zendesk.com/home). You can also contact us at [Brave New Media](http://bravenewmedia.net/contact-us/?plugin_support=Yes+(Social+Media+Mashup)) or on Twitter [@BraveNewTweet](http://twitter.com/#!/bravenewtweet).

== Credits ==

* Icons by [Komodo Media](http://www.komodomedia.com/blog/2008/12/social-media-mini-iconpack/)
* Icons by [FastIcon.com](http://www.fasticon.com/)
* Icons by [Picons.me](http://picons.me/)
* Settings API class by [Aliso the Geek](http://alisothegeek.com/2011/01/wordpress-settings-api-tutorial-1/)
* RSS feed mashing by [SimplePie](http://simplepie.org/)

== Screenshots ==

1. Social stream displaying as a widget
2. Plugin settings

== Upgrade Notice ==

= 1.1.1 =
This update adds links to online support to the settings screen and the README file.

= 1.1 =
This update adds a shortcode for use in your posts and pages.

= 1.0.2 =
This update fixes an incompatibility with other plugins using SimplePie.

= 1.0.1 =
This update fixes display problems on the plugin's settings screen.

== Changelog ==

= 1.1.1 =
* Added links to online support to admin screen & README file
* Updated the short description

= 1.1 =
* Added shortcode functionality
* Added usage instructions to admin screen

= 1.0.2 =
* Added a note about Twitter feed reliability
* Fixed error caused by other plugins loading SimplePie before Social Media Mashup

= 1.0.1 =
* Added warning on settings screen if the `smm-cache` folder is not writable by the server
* Disabled cache duration setting if the `smm-cache` folder is not writable by the server
* Fixed display issue on settings screen: admin footer drawn on top of content
* Fixed display issue on settings screen: low-resolution screens had a horizontal scroll

= 1.0 =
* Initial release