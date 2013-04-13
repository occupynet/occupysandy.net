=== Special Recent Posts PRO===
Contributors: lgrandicelli
Tags: recent, post, wordpress, plugin, thumbnails, widget, recent posts, latest
Requires at least: 3.0
Tested up to: 3.3
Stable tag: 2.4.5


Special Recent Posts PRO is a very powerful plugin/widget for WordPress which displays your recent posts with thumbnails.

== Description ==

<p>Special Recent Posts PRO is a very powerful plugin/widget for WordPress which displays your recent posts with thumbnails. 
It's the perfect solution for online magazines or simple blogs and it comes with more than 60+ customization options available. 
You can dynamically re-size thumbnails to any desired dimension, drag multiple widget instances and configure each one with its specific settings. 
You can also use auto-generated PHP code/shortcodes to insert the widget in any part of your theme.</p>


<p>Special Recent Posts is an high engineered, flexible and fully configurable plugin that lets you administrate everything about your blog’s recent posts.</p>

<strong>Some of the Special features</strong>:
<ul>
	<li>Beautiful dynamic widget interface</li>
	<li>More than 60+ customization settings</li>
	<li>Thumbnails dynamic adaptive resize</li>
	<li>Thumbnails Rotation Effects</li>
	<li>Cache Support</li>
	<li>Multiple widgets configurations</li>
	<li>Advanced post content display</li>
	<li>Advanced post filtering techniques</li>
	<li>Post Meta display</li>
	<li>Auto Generated PHP Code/Shortcodes</li>
	<li>Custom configurable CSS</li>
	<li>Multi Layout Mode</li>
</ul>

<strong>Plugin's homepage</strong><br />
http://codecanyon.net/item/special-recent-posts-pro/552356

<strong>Credits</strong>
Thumbnail generation is handled by the brilliant PHP Thumb Class
http://phpthumb.gxdlabs.com/

== Installation ==

Manual installation is easy and takes fewer than five minutes.

1. Download the plugin, unpack it and upload the '<em>special-recent-posts</em>' folder to your wp-content/plugins directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Check for correct permissions (0775 or 0777) on cache folder under special-recent-posts/cache
4. Go to Settings -&gt; Special Recent Posts to configure the basic options.
5. On the widgets panel, drag the Special Recent Posts widget onto one of your sidebars and configure its specific settings.
6. You're done. Enjoy.

== Upgrade Notice ==

If you're upgrading from the free version, many of your old settings might be overwritten. 
Anyway, the plugin will try to do its best to preserve your old widget settings.
SO PLEASE MAKE SURE YOU MAKE A BACKUP OF YOUR OLD CUSTOM CSS AND WIDGET SETTINGS. 
If the upgrade process fails or if you're experiencing troubles with the plugin behaviour, please consider to completely uninstall the free version and then re-install SRP PRO from scratch.

<strong>NOTES FOR MANUAL UPGRADE</strong>
If you wish to manually upgrade from a free version to PRO, please read the following steps:

	<ol>
		<li>Deactivate the old version in the Wordpress Plugin panel</li>
		<li>Delete the special-recent-posts folder on your server, under wp-content/plugins/</li>
		<li>Upload the new special-recent-posts folder from version 2.0 to the Wordpress plugin directory.</li>
		<li>Refresh the Wordpress plugin page</li>
		<li>Activate the new version.</li>
	</ol>
	
If you wish to use the Special Recent Posts PRO in another part of your theme which is not widget-handled, you can choose between PHP code or Shortcodes.
Both are auto-generated on every widget instance by the Code Generator section. Just copy the code and paste it wherever you want.
<strong>Please read the included documentation for further details.</strong>

== Changelog ==
= 2.4.5 =
* Added support for NextGen Gallery. Now if you set a post featured image by using the NextGen panel, it will show up instead of the no-image placeholder.
* Main CSS now included via link in the header section. No more plain css text in the <head> tag. CSS now must be edited opening css-front.css via a text editor.
* Fixed wrong link in plugin description.
* Fixed wrong title when using category title filtering.

= 2.4.4 =
* Fixed wrong position of widget title.
* Fixed duplicated ID on single posts instances.
* Added option to filter posts that belong exclusively to both 2 or more categories.
* Added option to sort posts in alphabetical order.
* Fixed wrong floating clearer. Using <div> instead of <br> tag.
* Fixed wring avatar image dimensions.

= 2.4.3 =
* Fixed Bug that prevented correct saving of international filenames with special characters.
* Fixed multi-column view mode.
* Fixed unwanted white space before category/tag separator.

= 2.4.2 =
* Fixed Bug that prevented correct visualization when using PHP external calls or Shortcodes.

= 2.4.1 =
* Fixed Wrong Layout HTML.

= 2.4 =
* Fixes for Wordpress 3.3
* Added a new option to automatically switch the recent posts list according to the current viewed category page. (Under Filtering Panel)
* Fixed a bug that prevented correct thumbnails visualization on Chrome and Safari.
* All SRP warnings and notices have now been moved within the SRP Control Panel.
* Added a text string break for post titles.
* Added a new option to display author's avatars as post thumbnails
* Added a new date format option: 'Time Ago'.
* Added new option: Visualization Filter.
  Now you can choose where the SRP widgets should appear.
  Available Options: Home Page, all Posts, All Pages, All Categories, All Archives, Everything

= 2.3 =
* Resolved Encoding Characters bug.

= 2.2 =
* Minor bugs fixed.

= 2.1 =
* Added WP Multi-Site Support.

= 2.0 =
* Added new options to order posts/pages by last updated and most commented.
* Added new section for shortcode and php code generation directly from widget panel.
* Added new option to filter posts by tags.
* Added new option to include sub-pages when in filtering mode.
* Added new option to assign different css classes and Ids for each widget instance.
* Added new option to link the entire excerpt to post.
* Added new option to filter posts by Custom Field Meta Key and Meta Value.
* Added layout section: now you can switch between single column mode, single row mode, and multiple columns mode.
* Added new option to retrieve thumbnails from custom fields.
* Added new option to skip posts without images.
* Added compatibility Mode with WPML Translator Plugin.
* Added category exclusion Filter.
* Added two more options to enable/disable Author links and Category Title Links.
* Added post tags visualization with optional PREFIX and Separator.
* Added post category visualization with optional PREFIX and Separator.
* Added post author visualization with optional PREFIX.
* Added new option to disable plugin stylesheet.
* Added new option to display post titles above the thumbnails.
* Added cache support. Now thumbnails are stored in a special cache folder for better performance and less load on server.
* Added new option to link the widget title to a custom URL.
* Added a new option to display post titles without link.
* Improved tag rebuilding when allowed tags option is on.
* XAMPP compatibility issue fixed.
* Improved image retrievement process.
* Brand new dynamic widget interface.
* Many bugs fixed.

== Frequently Asked Questions ==

= Plugin shows no thumbnails =

This issue might be caused by several problems. Check the following list.
<ol>
<li>Set the correct permissions on the cache folder. In order to generate the thumbnails, the SRP PRO plugin needs to write the cache folder, located under special-recent-posts/cache/
Be sure this folder is set to 0775 or 0777. Please ask to your system administrator if you're not sure what you are doing.</li>
<li>Thumbnails are rendered using the PHP GD libraries. These should be enabled on your server. Do a phpinfo() on your host to check if they're installed properly. Contact your hosting support to know how to enable them.</li>
<li>Another problem could be that you're hosting the plugin on a MS Windows based machine. This will probably change the encoding data inside the files and could lead to several malfunctions. Better to host on a Unix/Linux based environment.</li>
<li>External images are not allowed. This means that if you're trying to generate a thumbnail from an image hosted on a different domain, it won't work.</li>
</ol>

= Category/Post filtering isn't working =

In order to properly filter posts by categories, you must provide a numeric value which is the Category ID.
Every Wordpress category has an unique Identification number, and this can be found doing the following steps:
<ol>
<li>Go in the  Posts->Categories panel</li>
<li>Mouse over a category name.</li>
<li>Look at the status bar at the very bottom of your browser window. There you will find a long string containing a parameter called <strong>tag_ID</strong> and its following value.</li>
<li>Take note of that number, which is the relative Category ID to insert in the SRP PRO filtering panel.</li>
</ol>

NOTE: Please remember that this procedure is also valid for post filtering.

<p><strong>Please read the included PDF manual for further F.A.Q</strong></p>

== Requirements ==

In order to work, Special Recent Posts PRO plugin needs the following settings:

1. PHP version 5+
2. GD libraries installed and enabled on your server.
3. Correct permissions (0775 or 0777) on cache folder under special-recent-posts/cache