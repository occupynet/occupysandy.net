=== Styles with Shortcodes ===
Author: Alberto Lau (RightHere LLC)
Author URL: http://plugins.righthere.com/styles-with-shortcodes/
Tags: WordPress, Shortcodes, Shortcode API, jQuery UI, jQuery TOOLS, Toggle, Tabs, Accordion, Syntax Highlighter, Custom underlined links, Overlay, Buttons, Columns, Google Maps, Blockquotes, Pullquotes, Tables, Dividers, Colored Boxes, Picture frames, Tooltips, Facebook, Twitter, LinkedIN, Flattr, Twitter follow me, Google +1
Requires at least: 3.0
Tested up to: 3.3.1
Stable tag: 1.7.9 rev23212


======== Changelog =======
Version 1.7.9 rev23212 - April 10, 2012
* Update: Updated Thumbnail.php (TimThumb 2.8.10)
* Bug Fixed: Prevent warnings related to registered scripts
* Bug Fixed: Tables where not applying the table width correctly

Version 1.7.9 rev20920 - February 23, 2012
* Update: Updated Thumbnail.php (TimThumb 2.8.9)

Version 1.7.8 rev15258 - January 6, 2012
* Update: Thumbnail.php updated with TimThumb 2.8.5
* Bug Fixed: Custom Post Type column not showing in WP 3.3
* Bug Fixed: Broken Slider
* Update: Imported bundle with column fixes
* Update: Moved JS to init instead of plugins_loaded hook
* Update: Added new icons for menu

Version 1.7.7 rev13259 - December 6, 2011
* Bug Fixed: When adding panel items, sliders where not rendering with the correct min, max and step values
* Bug Fixed: Jerky Chrome Nivo Slider animation
* Bug Fixed: Missing Header breaks Chrome caching
* Update: Thumbnail.php updated to TimThumb 2.8.3 and removed word thumb and old TimThumb files.

Version 1.7.6 rev11833 - November 11, 2011
* New Feature: Added a checkbox field type for Shortcode creation
* New Feature: Added option to selective disable bundle on fronted, backend or both

Version 1.7.6 rev11488 - October 26, 2011
* Bug Fixed: Insert tool top margin increased to accommodate the admin bar
* New Feature: Additional drop downs for the Shortcode creator; registered post types and registered taxonomies.

Version 1.7.6 rev11143 - October 20, 2011
* New Feature: Added lightbox support for CSS Frames
* Update: Increasing bottom padding on buttons
* New Feature: Added resources for new Minimalistic Divider Bar Shortcodes

Version 1.7.5 rev10566 - October 5, 2011
* Bug Fixed: Videos not loading in Lightbox. Force update of preloadify.js
* Updated: Updated Lightbox to 1.5.3
* New Feature: Added support for Lightbox on CSS Frames

Version 1.7.5 rev10020 - September 23, 2011
* Bug Fixed: S icon dialog was hidden by some other plugin that is hiding HTML in the admin footer.
* Bug Fixed: Hardcode fix, url encode the src field of TimThumb urls.
* Improvement: Position the insert tool in the top of the viewport and not top of the page
* Improvement: Option to always include admin sws scripts for plugins and themes that custom add Custom Post Content on screens
* New Feature: Added 18 new Golden Ratio Picture frames.
* New Feature: Added "align" and "width" parameter to the Twitter Follow Button Shortcode.

Version 1.7.4 rev7914 - August 11, 2011
* Bug Fixed: update_post_meta does not behave the same when adding a new meta or updating and existing one in regard to slashes.
* New Feature: added 15 new images for picture frame Shortcodes (DC).

Version 1.7.3 rev7550 - August 5, 2011
* Security Update: Disallow php from being executed on the TimThumb cache
* New Feature: Replace TimThumb with WordThumb (increased security)

Version 1.7.2 rev7110 - July 22, 2011
* New Feature: Added custom capability that makes it possible to display the "S" icon on the editor or metabox for inserting Shortcodes
* Bug Fixed: Compatibility problem with LightBox Plus (Colorbox) plugin, renamed enqueued script name.
* Bug Fixed: Allow external images in picture frames
* Increased memory limit for image resizing. Also added a debugging timthumb for easier support.
* Bug Fixed: Google Map fixes
* Bug Fixed: Blue line in buttons on Firefox 5.0.1
* Update: Updated Options Panel 1.0.3
* Update: Lightbox jQuery script updated

Version 1.7.1 rev6153 - June 23, 2011
* Bug Fixed: removed Options Panel php warning
* Added alternative remote service procedure for sites not allowing fopen URL (this is for entering license key)

Version 1.7.0 rev5972 - June 18, 2011
* New Feature: Added Lightbox option to picture frames
* New Feature: Added Lightbox support for video, updated picture frame Shortcodes to accept video URL.
* New Feature: Social Networks Shortcode (Google +1 button support)
* New Feature: Social Networks Shortcode (Twitter follow me button support)
* New Feature: Social Networks Shortcode (LinkedIn button support)
* New Feature: Social Networks Shortcode (Flattr button support)
* New Feature: CSS Frames Shortcodes (3 pre-defined styles and 1 custom)
* New Feature: URL Shortcode (Create custom underlined hyperlinks)
* New Feature: Highlight Shortcode (custom highlight of text)
* New Feature: Downloadable Content module added
* Bug Fixed: Toogle not working on certain conditions (JavaScript error on line break conversion)
* Bug Fixed: Updated the developer URL in API address.
* Bug Fixed: Update notification link
* Update: increased the TimThumb limit from 1000 px to 2000 px

Version 1.6.1 rev4542 - May 19, 2011
* Bug Fixed: Shortcodes not loaded when a metadata gets duplicated for some reason
* Bug Fixed: Toggle automatically closing when more than one version used.
* Bug Fixed: sws_ul not showing in IE7.
* New Feature: 11 New Picture Frames (Square) added
* New Feature: 1 new Shortcode for displaying WordPress shortcodes (4 different backgrounds)
* New Feature: Button Shortcode updated with 30 colors schemes, glow feature and new X-large button
* New Feature: Added Shortcode preview image on insert tool
* New Feature: Option to show the Shortcode tool in a metabox instead of the standard "S" icon above the visual editor.
* New Feature: Option to enable SWS in Custom Post Types when using the metabox Shortcode insert tool.
* New Feature: Added preview images for some Shortcodes; Picture Frames

Version 1.6.0 rev4005 - April 25, 2011
* Bug Fixed: removed left indent on table shortcode
* Bug Fixed: Left, Center and Right align of buttons
* Bug Fixed: Scrollable Gallery when combined with some overlays, the images are kept hidden.

Version 1.5.9 rev3509 - April 13, 2011
* New Shortcode: URL insert custom underlined links
* Added ALT text support for Picture Frames shortcode

Version 1.5.8 rev3035 - April 6, 2011
* Bug fix, Syntax Highlighter do not apply WP filters to codes.
* Bug fix, Tabs for IE7 (Javascript bug) 

Version 1.5.7 rev2949 - April 4, 2011
* Bug fix, reduce the number of queries done by the plugin.
* Bug fix, / should not be replaced on any of the meta that is not the Shortcode code.
* Bug fix, remove several notices displayed on sites with php notices on
* Bug fix, prevent a warning that is displayed on sites where WP_DEBUG is set to true.
* Changed a function name in timthumb that is in conflict with another plugin.
* Added compatibility with Options Tree menu

Version 1.5.6 rev2634 - March 25, 2011
* Added support for integration into theme (this is a feature for Theme developers)

Version 1.5.5 rev2399 - March 22, 2011
* Bug fix, creating a Shortcode with a slash on the Shortcode code was breaking the the_content filter and not showing any content.
* Bug fix, prevent a php warning that was breaking Shortcode saving on sites with php warning on
* Bug fix, thumbs where not showing on sites with php warnings on
* Add temp directory used by thimthumb

Version 1.5.4 rev2272 - March 13, 2011
* Bug fix Shortcode in widgets
* Bug fix prevent other plugins from hooking into Custom Post Types saved by SWS
* Bug fix conflict with OptionTree plugin
* Bug fix jQuery tools remove tabs plugin
* Bug fix plus image breaking on long titles in the toggle Shortcode
* Bug fix buttons Shortcodes

Version 1.5.2. rev1817 - February 24, 2011
* Fixed broken Gallery Scrollable Basic
* Fixed broken Gallery Scrollable with Preview

Version 1.5.2 rev1780 - February 23, 2011
* Added feature so the rtl (right-to-left) menu usage doesn't break. This is used in WordPress in Hebrew.

Version 1.5.2 rev1697 - February 14, 2011 
* Fixed Shortcode compatibility issue with the following Premium WordPress themes from Themeforest;
    - Awake - Powerful Professional WordPress Theme
    - Striking Premium Corporate & Portfolio WP Theme
    - Doutive 2WO - All in One WordPress Theme

Version 1.5.2 - February 11, 2011
* Added support for using Shortcodes in Custom Dashboard Metaboxes (wp-admin)

Version 1.5.0 - December 19, 2010
* added 24 new Shortcodes 
* improved the Shortcode creator tool; added more info to Shortcode list, added new field types, author info, bundle info
* improved the Shortcode generator tool; added descriptions to fields
* added option to recover original Shortcodes (bundles)
* added loading of scripts and styles in a separate file, from an add-on 
* reorganized core, so all scripts and styles are only loaded when a Shortcode requires it.

Version 1.0.1 - November 30, 2010
* Added support for user Roles with Edit Post and Edit Page capabilities

Version 1.0.0 (November 27, 2010)
* First release.

======== Description ========

This plugin lets you customize content faster and easier than ever before by using Shortcodes. Choose from close to 100 built in shortcodes like; jQuery Toggles and Tabs, Tooltips, Column shortcodes, Gallery and Image shortcodes, Button Styles, Alert Box Styles, Animated Alert Box Styles, Pullquotes, Blockquotes, Tables, Unordered Lists, Twitter buttons, Retweet button, Facebook Like buttons and many more!
You can create your own shortcakes and share them with friends and other people who also uses the Styles with Shortcodes plugin. 

== Installation ==

1. Upload the 'style-with-shortcodes' folder to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Click on 'Shortcodes' in the left admin bar of your dashboard

== Frequently Asked Questions ==

Q: How do I export a shortcode to a friend that also has Styles with Shortcodes?
A: Click on Shortcodes in the menu to the left. Find the shortcode you would like to export (share with your friend). Click on Edit and then scroll down to the bottom and click on "Export this shortcodes settings". When you see the code copy it and simply send it to your friend by email. Your friend will need to have a valid license for the Styles with Shortcodes plugin in order to import your shortcode.

Q: Can other developers create shortcodes for this plugin?
A: Yes, the idea is that anyone can develop shortcakes and then you can import them if you have a licensed copy of Styles with Shortcodes.

Q: How do I insert a shortcode?
A: First you need to create a Post or a Page. Then click on the "S" icon above the visual editor. You will find it in the same line as the default WordPress icons (Upload/Insert). When you click the icon you will get a "Add Styles with Shortcodes" box opening. First you select the Shortcode Category. Right now there are 16 different categories with over 50 different variations of shortcodes. When you have selected the category then you select the shortcode. And then you will get a easy to user interface that tells you exactly what information is needed to insert the chosen shortcode.

Q: How do I create a new shortcode?
A: Click on "Add new Shortcode" in the left menu. The plugin utilizes the Shortcode API that was introduced with WordPress 2.5. It is a simple set of functions for creating macro codes for use in post and page content. The API handles all the tricky parsing, eliminating the need for writing a custom regular expression for each shortcode. Fist define your shortcode fields and then create your shortcode template, style (CSS) and you can even include Javascript if it is needed for your shortcode.

Please notice that you need some knowledge of HTML, PHP, CSS (and Javascript) in order to create your own shortcakes. If you don't know this you can still use all the built in shortcakes and get additional shortcakes. 

Q: How do I import a shortcode from a friend?
A: A shortcode can have many different shortcode fields, template, styles (CSS) and Javascript and it would be a tedious process to manually copy each field and settings to a new shortcode. Therefore we have created a really easy to use export/import feature. A Exported Shortcode will look something like this:

Tzo4OiJzdGRDbGFzcyI6OTp7czoxMDoicG9zdF90aXRsZSI7czoxODoiU3ludGF4IEhpZ2hsaWdodGVyIjtzOjEyOiJzY19zaG9ydGNvZGUiO3M6ODoic3dzX2NvZGUiO3M6MTM6InNjX3Nob3J0Y29kZXMiO2E6MDp7fXM6MTE6InNjX3RlbXBsYXRlIjtzOjYxOiI8cHJlIG5hbWU9ImNvZGUiIGNsYXNzPSJicnVzaDp7bGFuZ3VhZ2V9Ij4NCntjb250ZW50fQ0KPC9wcmU+IjtzOjY6InNjX2NzcyI7czoyOTQ6IjxsaW5rIHR5cGU9InRleHQvY3NzIiByZWw9InN0eWxlc2hlZXQiIGhyZWY9IntwbHVnaW51cmx9anMvc3ludGF4aGlnaGxpZ2h0ZXJfMy4wLjgzL3N0eWxlcy9zaENvcmUuY3NzIj48L2xpbms+PGxpbmsgdHlwZT0idGV4dC9jc3MiIHJlbD0ic3R5bGVzaGVldCIgaHJlZj0ie3BsdWdpbnVybH1qcy9zeW50YXhoaWdobGlnaHRlcl8zLjAuODMvc3R5bGVzL3NoVGhlbWVEZWZhdWx0LmNzcyI+PC9saW5rPg0KPHN0eWxlPg0KLnN5bnRheGhpZ2hsaWdodGVyIGNvZGUgew0KZGlzcGxheTppbmxpbmU7DQp9DQo8L3N0eWxlPiI7czo1OiJzY19qcyI7czoxNzk3OiI8c2NyaXB0IHNyYz0ie3BsdWdpbnVybH1qcy94cmVnZXhwLW1pbi5qcyIgdHlwZT0idGV4dC9qYXZhc2Nyat9fX0=

In order to import a Exported Shortcode click on "Create new Shortcode" and then scroll down to the "Import" field. Copy and paste the code into the field and click "More info". The system will now analyze the shortcode and tell you what type of code it is e.g.

Name:		Syntax Highlighter
Shortcode:	sws_code
Bundle
Categories:	Code

And then just click "Confirm Import shortcode settings". No need to know anything about HTML, PHP, CSS or Javascript!



==Sources, Credits & Licenses ==

I've used the following opensource projects, graphics, fonts, API's or other files as listed. Thanks to the author for the creative work they made.

1) jQuery TOOLS UI library by Tero Piirainen (http://flowplayer.org/tools/)

2) Syntax Highlighter by Alex Gorbatchev (http://alexgorbatchev.com/SyntaxHighlighter/)

3) jQuery UI library (http://jqueryui.com/)

4) TimThumb by Ben Gillbanks (http://www.binarymoon.co. uk/projects/timthumb/)

5) Google Maps API version 3.0 (http://code.google.com/apis/maps/documentation/javascript/)

6) jQuery Color Picker (http://www.eyecon.ro/colorpicker/)

7) Facebook (http://developers.facebook.com/docs/reference/plugins/like)

8) Twitter (http://twitter.com/about/resources/tweetbutton)

9) ReTweet (http://tweetmeme.com/about/retweet_button)

10) Prelodify
License Type: Extended License
URL: http://codecanyon.net/item/preloadify/133636
Item Purchase Code: a780ced4-3ae9-4634-a336-dd9b419df18e
Licensor's Author Username: 5thSenseLabs
Licensee: RightHere LLC

11) Special Drop Shadow Generator
License Type: Extended License
URL: http://graphicriver.net/item/special-drop-shadows-generator/106097
Item Purchase Code: 0e848634-382d-4984-b21f-d1f7878c73dc
Licensor's Author Username: Giallo
Licensee: RightHere LLC

12) jQuery Lightbox Evolution
License Type: Extended License
URL: http://codecanyon.net/item/jquery-lightbox-evolution/115655
Item Purchase Code: 3e36c852-3030-4ed3-a569-da1dc1e806c0
Licensor's Author Name: aeroalquimia
Licensee: RightHere LLC

13) Minimalist Dividers
License Type: Extended License
URL: http://graphicriver.net/item/minimalist-dividers-resizable-/146824
Item Purchase Code: cb35782e-cc52-4aab-9ab0-bd50e981ddce
Licensor's Author Username: 360Degrees
Licensee: RightHere LLC
