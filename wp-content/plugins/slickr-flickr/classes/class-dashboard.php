<?php
class Slickr_Flickr_Dashboard extends Slickr_Flickr_Admin {
	private $tips = array(
			'flickr_id' => array('heading' => 'Flickr ID', 'tip' => 'The Flickr ID is required for you to be able to access your photos.<br/>You can find your Flickr ID by entering the URL of your Flickr photostream at http://idgettr.com'),
			'flickr_group' => array('heading' => 'Flickr User or Group', 'tip' => 'Typically your default account will be a User account unless your site is supporting a Flickr Group.'),
			'flickr_api_key' => array('heading' => 'Flickr API Key', 'tip' => 'The Flickr API Key is used if you want to be able to get more than 20 photos at a time.<br/>A Flickr API key looks something like this : 5aa7aax73kljlkffkf2348904582b9cc.<br/>You can find your Flickr API Key by logging in to Flickr and clicking the Get Your Flickr API Keys link in the Getting Started section'),
			'flickr_items' => array('heading' => 'Number Of Photos', 'tip' => 'Flickr recommend a maximum of 30 photos per page.'),
			'flickr_type' => array('heading' => 'Type of Display', 'tip' => 'Choose the most common type of display for your photos.'),
			'flickr_size' => array('heading' => 'Photo Size', 'tip' => 'Choose the default display size for your photos.'),
			'flickr_captions' => array('heading' => 'Captions', 'tip' => 'Enable captions if the majority of your photos have titles.'),
			'flickr_autoplay' => array('heading' => 'Autoplay', 'tip' => 'Enable autoplay if you generally want slideshows to play automatically.'),
			'flickr_delay' => array('heading' => 'Delay Between Images', 'tip' => 'Set a default for the delay between slideshow images. This is typically in the range of 3 to 7 seconds.'),
			'flickr_transition' => array('heading' => 'Slideshow Transition', 'tip' => 'Set a default transition period between one slide disappearing and another appearing. This is typically between 0.5 and 2.0 seconds.'),
			'flickr_responsive' => array('heading' => 'Mobile Responsive', 'tip' => 'Click to use the mobile responsive slider.'),
			'flickr_lightbox' => array('heading' => 'Lightbox', 'tip' => 'You can use a default Slickr Flickr LightBox, or Thickbox which comes with WordPress, or use another LightBox you have installed separately in your theme or another plugin.'),
			'flickr_thumbnail_border' => array('heading' => 'Highlight Color', 'tip' => 'The highlight color appears in the photo border when the user moves their cursor over the image.'),
			'flickr_galleria' => array('heading' => 'Galleria Version', 'tip' => 'Choose which version of the galleria you want to use. We recommend you use the latest version of the galleria as this has the most features.'),
			'flickr_galleria_theme' => array('heading' => 'Theme/Skin', 'tip' => 'The default theme is "classic". Only change this value is you have purchased a premium Galleria theme or written one and located it under the themes folder specified below.'),
			'flickr_galleria_theme_loading' => array('heading' => 'Theme Load Method', 'tip' => 'Choose <i>Static</i> if you are using the same Galleria theme thoughout the site otherwise choose <i>Dynamic</i> if you are using different themes on different pages.'),
			'flickr_galleria_themes_folder' => array('heading' => 'Themes Folder', 'tip' => 'The recommended location is "galleria/themes". Prior to WordPress 3.3 you could put the themes under wp-content/plugins/slickr-flickr/galleria but this is no longer possible since WordPress now wipes the plugin folder of any extra files that are not part of the plugin.'),
			'flickr_galleria_options' => array('heading' => 'Galleria Options', 'tip' => 'Here you can set default options for the Galleria.<br/>The correct format is like CSS with colons to separate the parameter name from the value and semi-colons to separate each pair: param1:value1;param2:value2;<br/>For example, transition:slide;transitionSpeed:1000; sets a one second slide transition.'),
			'flickr_scripts_in_footer' => array('heading' => 'Load Script In Footer', 'tip' => 'This option allows you to load Javascript in the footer instead of the header. This can be useful as it may reduce potential jQuery conflicts with other plugins.<br/>However, it will not work for all WordPress themes, specifically those that do not support loading of scripts in the footer using standard WordPress hooks and filters.'),
			'flickr_message' => array('heading' => 'Error Message', 'tip' => 'Any message you enter here will replace the default message that is displayed when no photos are available for whatever reason.'),
			'flickr_silent' => array('heading' => 'Silent Mode', 'tip' => 'Click the checkbox to suppress any response at all when no photos are found.'),

			);

	private $galleria_versions = array(
		'galleria-latest' => 'Galleria latest version',
		'galleria-original' => 'Galleria original version',
		'galleria-none' => 'Galleria not required so do not load the script'
	);

    private $lightboxes  = array(
		'sf-lightbox' => 'Default LightBox (pre-installed with this plugin)',
		'thickbox' => 'Thickbox (pre-installed with Wordpress)',
		'evolution' => 'Evolution LightBox for Wordpress (requires separate installation)',
		'fancybox' => 'FancyBox (requires separate installation)',
		'colorbox' => 'LightBox Plus for Wordpress (requires separate installation)',
		'responsive' => 'Responsive LightBox (requires separate installation)',
		'shutter' => 'Shutter Reloaded for Wordpress (requires separate installation)',
		'slimbox'=> 'SlimBox for Wordpress (requires separate installation)',
		'lightbox' => 'Some Other LightBox(requires separate installation)'
    );

    private $sizes = array('medium' => 'Medium (500px by 375px)',
			'm640' => 'Medium 640 (640px by 480px)',
			'm800' => 'Medium 800 (800px by 600px)',
			'large' => 'Large (1024px by 768px)',
			'original' => 'Original (2400px by 1800px)');

    private $types = array('gallery' => 'a gallery of thumbnail images',
			'galleria' => 'a galleria slideshow with thumbnail images below',
			'slideshow' => 'a slideshow of medium size images');

	function init() {
		add_action('admin_menu',array($this, 'admin_menu'));
		add_filter('plugin_action_links',array($this, 'plugin_action_links'), 10, 2 );
        add_action('admin_enqueue_scripts', array($this ,'register_tooltip_styles'));
        add_action('admin_enqueue_scripts', array($this ,'register_admin_styles'));
	}

	function admin_menu() {
		$this->screen_id = add_options_page(SLICKR_FLICKR_PLUGIN_NAME, SLICKR_FLICKR_PLUGIN_NAME, 'manage_options', $this->get_slug(), array($this, 'page_content'));
		add_action('load-'.$this->get_screen_id(), array($this, 'load_page'));
	}
 
	function page_content() {
 		$this->print_admin_form_with_sidebar($this->admin_heading(), __CLASS__, $this->get_keys());
	} 

	function load_page() {
		$this->set_tooltips($this->tips);
 		if (isset($_POST['options_update'])) $this->save() ;
		if (isset($_GET['cache'])) $this->clear_cache();  		
		add_action('admin_enqueue_scripts',array($this, 'enqueue_admin_styles'));
		add_action('admin_enqueue_scripts',array($this, 'enqueue_metabox_scripts'));
		add_action('admin_enqueue_scripts',array($this, 'enqueue_postbox_scripts'));
		$this->add_meta_box('intro', 'Intro', 'intro_panel');
		$this->add_meta_box('general', 'General Settings', 'general_panel',array ('options' => Slickr_Flickr_Options::get_options()), 'normal');
		$this->add_meta_box('extras', 'Extras', 'extras_panel', null,'advanced');
		$this->add_meta_box('news', 'Slickr Flickr News', 'news_panel', null, 'advanced');
		$this->add_meta_box('help', 'Free Tutorials', 'tutorials_panel', null, 'side');
		$current_screen = get_current_screen();
		if (method_exists($current_screen,'add_help_tab')) {
			$current_screen->add_help_tab( array( 'id' => 'slickr_flickr_overview', 'title' => 'Overview', 		
				'content' => '<p>This admin screen is used to configure your Flickr settings, set display defaults, and choose which LightBox and version of the Galleria /theme you wish to use with Slickr Flickr.</p>'));	
			$current_screen->add_help_tab( array( 'id' => 'slickr_flickr_troubleshooting', 'title' => 'Troubleshooting', 		
				'content' => '<p>Make sure you only have one version of jQuery installed, and have a single LightBox activated otherwise you may have conflicts. For best operation your page should not have any JavaScript errors. Some Javascript conflicts are removed by loading Slickr Flickr in the footer (see Advanced Options)</p>
				<p>For help go to <a href="http://www.slickrflickr.com/slickr-flickr-help/">Slickr Flickr Help</a> or for priority support upgrade to <a href="http://www.slickrflickr.com/upgrade/">Slickr Flickr Pro</a></p>'));	
		}
	}

	function general_panel($post,$metabox) {
      $options = $metabox['args']['options'];
      $this->display_metabox( array(
         'Identity' => $this->id_panel($options),
         'Display' => $this->display_panel($options),
         'LightBox' => $this->lightbox_panel($options),
         'Galleria' => $this->galleria_panel($options),
         'No Photos' => $this->no_photos_panel($options),
         'Advanced' => $this->advanced_panel($options)
		),1);
   }

 	function extras_panel($post, $metabox) {
      $this->display_metabox( array(
         'Getting Started' => $this->starting_panel(),
         'Useful Links' => $this->links_panel(),
         'LightBoxes' => $this->lightboxes_panel(),
         'Clear RSS Cache' => $this->cache_panel()
		),2);
   }	
	
	function id_panel($options) {		
      return 
		$this->fetch_form_field ('flickr_id', $options['id'], 'text', array(), array('maxlength' => 15, 'size' =>15)).
		$this->fetch_form_field ('flickr_group', $options['group'], 'select', array('n' => 'user', 'y' => 'group')).
		$this->fetch_form_field ('flickr_api_key', $options['api_key'], 'text', array(), array('maxlength' => 32,'size' =>32));
	}

	function display_panel($options) {		
	 	return
		$this->fetch_form_field ('flickr_items', $options['items'], 'text', array(), array('maxlength' => 4, 'size' => 4)).
		$this->fetch_form_field ('flickr_type', $options['type'], 'select', $this->types).
		$this->fetch_form_field ('flickr_size', $options['size'], 'select', $this->sizes).
		$this->fetch_form_field ('flickr_captions', $options['captions'], 'radio', array('on' => 'on','off' => 'off')).
		$this->fetch_form_field ('flickr_autoplay', $options['autoplay'], 'radio', array('on' => 'on','off' => 'off')).
		$this->fetch_form_field ('flickr_delay', $options['delay'], 'text', array(), array('maxlength' => 3, 'size' => 3, 'suffix' => 's')).
		$this->fetch_form_field ('flickr_transition', $options['transition'], 'text', array(), array('maxlength' => 3, 'size' => 3, 'suffix' => 's')).
		$this->fetch_form_field ('flickr_responsive', $options['responsive'], 'checkbox');
	}

	function no_photos_panel($options) {		
 		return	
		$this->fetch_form_field ('flickr_silent', $options['silent'], 'checkbox').
		$this->fetch_form_field ('flickr_message', $options['message'], 'text', array(), array( 'size' => 50));
	}

	function advanced_panel($options) {		
 		return	
		$this->fetch_form_field ('flickr_scripts_in_footer', $options['scripts_in_footer'], 'checkbox');
	}

	function galleria_panel($options) {		
      return
		$this->fetch_form_field ('flickr_galleria', $options['galleria'], 'select', $this->galleria_versions).
		$this->fetch_form_field ('flickr_galleria_theme', $options['galleria_theme'], 'text', array(), array('maxlength' => 20, 'size' => 12)).
		$this->fetch_form_field ('flickr_galleria_theme_loading', $options['galleria_theme_loading'], 'select', array('static' => 'Static','dynamic' => 'Dynamic')).
		$this->fetch_form_field ('flickr_galleria_themes_folder', $options['galleria_themes_folder'], 'text', array(), array('maxlength' => 50, 'size' => 30)).
		$this->fetch_form_field ('flickr_galleria_options', $options['galleria_options'], 'textarea', array(), array('cols' => 60, 'rows' => 4));
	}

	function lightbox_panel($options) {		
      return
		$this->fetch_form_field ('flickr_lightbox', $options['lightbox'], 'select', $this->lightboxes).
		$this->fetch_form_field ('flickr_thumbnail_border', $options['thumbnail_border'], 'text', array(), array('size' => 7, 'class' => 'color-picker'));
	}

	function lightboxes_panel() {	 		
		return <<< COMPAT_LIGHTBOX_PANEL
<ul>
<li><a href="http://s3.envato.com/files/1099520/index.html" rel="external" target="_blank">Evolution Lightbox</a></li>
<li><a href="http://wordpress.org/extend/plugins/easy-fancybox/" rel="external" target="_blank">FancyBoxBox</a></li>
<li><a href="http://wordpress.org/extend/plugins/lightbox-plus/" rel="external" target="_blank">Lightbox Plus (ColorBox) for WordPress</a></li>
<li><a href="http://wordpress.org/extend/plugins/responsive-lightbox/" rel="external" target="_blank">Responsive Lightbox</a></li>
<li><a href="http://wordpress.org/extend/plugins/shutter-reloaded/" rel="external" target="_blank">Shutter Lightbox for WordPress</a></li>
<li><a href="http://wordpress.org/extend/plugins/slimbox/" rel="external" target="_blank">SlimBox</a></li>
</ul>
COMPAT_LIGHTBOX_PANEL;
	}

 	function tutorials_panel($post,$metabox) {	
		$images_url = plugins_url('images/',dirname(__FILE__));	
		$home = SLICKR_FLICKR_HOME;
		print <<< HELP_PANEL
<p><img src="{$images_url}free-video-tutorials-banner.png" alt="Slickr Flickr Tutorials Signup" /></p>
<form id="slickr_flickr_signup" method="post" action="{$home}"
onsubmit="return slickr_flickr_validate_form(this)">
<fieldset>
<input type="hidden" name="form_storm" value="submit"/>
<input type="hidden" name="destination" value="slickr-flickr"/>
<label for="firstname">First Name
<input id="firstname" name="firstname" type="text" value="" /></label><br/>
<label for="email">Email
<input id="email" name="email" type="text" /></label><br/>
<label id="lsubject" for="subject">Subject
<input id="subject" name="subject" type="text" /></label>
<input type="submit" value="" />
</fieldset>
</form>
HELP_PANEL;
	}	

 	function starting_panel() {	
		$images_url = plugins_url('images/',dirname(__FILE__));	
		$home = SLICKR_FLICKR_HOME;
		return <<< STARTING_PANEL
<ul>
<li><a href="{$home}/40/how-to-use-slickr-flickr-admin-settings/" rel="external" target="_blank">How To Use Admin Settings</a></li>
<li><a href="http://idgettr.com/" rel="external" target="_blank">Find your Flickr ID</a></li>
<li><a href="http://www.flickr.com/services/api/keys/" rel="external" target="_blank">Get Your Flickr API Keys</a></li>
<li><a href="{$home}/56/how-to-use-slickr-flickr-to-create-a-slideshow-or-gallery/" rel="external" target="_blank">How To Use The Plugin</a></li>
<li><a href="{$home}/slickr-flickr-videos/" rel="external" target="_blank">Get FREE Video Tutorials</a></li>
</ul>
STARTING_PANEL;
	}	

	
	function links_panel() {	 
		$home = SLICKR_FLICKR_HOME;				
      $pro = SLICKR_FLICKR_PRO;
		return <<< LINKS_PANEL
<ul>
<li><a href="{$home}" rel="external" target="_blank">Plugin Home Page</a></li>
<li><a href="{$home}/1717/using-slickr-flickr-with-other-lightboxes" rel="external" target="_blank">Using Slickr Flickr with other lightboxes</a></li>
<li><a href="http://galleria.aino.se/themes/" rel="external" target="_blank">Premium Galleria Themes</a></li>
<li><a href="{$home}/2328/load-javascript-in-footer-for-earlier-page-display/" rel="external" target="_blank">Loading Slickr Flickr scripts in the footer</a></li>
<li><a href="{$home}/slickr-flickr-help/" rel="external" target="_blank">Get Help</a></li>
<li><a href="{$home}/pro/" rel="external" target="_blank">Slickr Flickr Pro Bonus Features</a></li>
</ul>
LINKS_PANEL;
	}	

 	function intro_panel($post,$metabox){	
		$message = $metabox['args']['message'];	 	
		$home = SLICKR_FLICKR_HOME;
		print <<< INTRO_PANEL
	<p>For help on gettting the best from Slickr Flickr visit the <a href="{$home}">Slickr Flickr Plugin Home Page</a></p>
	<p><b>We recommend you fill in your Flickr ID in the Flickr Identity section. All the other fields are optional.</b></p>
{$message}
INTRO_PANEL;
	}
	
	function cache_panel() {
		$url = $_SERVER['REQUEST_URI'];
		if (strpos($url, 'cache') === FALSE) $url .= '&cache=clear';	
		return <<< CACHE_PANEL
<p>If you have a RSS caching issue where your Flickr updates have not yet appeared on Wordpress then click the button below to clear the RSS cache</p>
<a id="slickr_flickr_cache" class="button-primary" href="{$url}" >Clear Cache</a>
CACHE_PANEL;
	}	
 
   function clear_cache() {
        $_SERVER['REQUEST_URI'] = remove_query_arg( 'cache');  
   		Slickr_Flickr_Cache::clear_cache();
   		$this->add_admin_notice(__('WordPress RSS cache',SLICKR_FLICKR_DOMAIN), __('has been cleared successfully',SLICKR_FLICKR_DOMAIN));
   		return true;
   }

	private function check_numeric_range($val, $default, $min, $max) {
		if ($good_val = filter_var ($val, FILTER_VALIDATE_INT, 
			array('options' => array('default' => $default, 'min_range' => $min, 'max_range' => $max))))
			return $good_val;
		else
			return $default;
	}

	function save() {
		check_admin_referer(__CLASS__);
		$updated = false;
		$settings = 'Slickr Flickr Settings ';
  		$options = explode(',', stripslashes($_POST['page_options']));
  		if ($options) {
  			$flickr_options = array();
    		// retrieve option values from POST variables
    		foreach ($options as $option) {
       			$option = trim($option);
       			$key = substr($option,7);
       			$val = array_key_exists($option, $_POST) ? trim(stripslashes($_POST[$option])) : '';
				switch ($option) {
       				case 'flickr_per_page': $flickr_options[$key] = $this->check_numeric_range($val, 50, 50, 500); break;
 					default: $flickr_options[$key] = $val;
	    		}
    		} //end for
			
   			$updates = Slickr_Flickr_Options::save_options($flickr_options) ;
   			if ($updates)  {
            	$updated = true;
				$this->add_admin_notice($settings, __('saved',SLICKR_FLICKR_DOMAIN));
   			} else
      			$this->add_admin_notice($settings, __('are unchanged since last update',SLICKR_FLICKR_DOMAIN), true);
  		} else {
         	$this->add_admin_notice($settings, __('not found',SLICKR_FLICKR_DOMAIN), true);
  		}
  		return $updated ;
	} 
	
}
 