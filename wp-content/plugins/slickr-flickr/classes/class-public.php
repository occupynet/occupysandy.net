<?php
class Slickr_Flickr_Public {

	static private $jquery_data; //write jQuery in one chunk	
	static private $galleria_themes; //galleria themes
	static private $is_present = false; //is there a galleria, gallery or slideshow on this page? 	

	static function init() {
		self::$galleria_themes=array(); //initialize galleria themes
		self::$jquery_data=array(); //initialize jquery config
		add_shortcode('slickr-flickr', array(__CLASS__,'display'));
		add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'));
		add_filter('widget_text', 'do_shortcode', 11);			
	}

	static function display($attr) {
		$disp = new Slickr_Flickr_Display();
		return $disp->show($attr);
	}

	static function enqueue_scripts() {
	    $path = SLICKR_FLICKR_PLUGIN_URL;
	    $options = Slickr_Flickr_Options::get_options();
	    $footer_scripts =  $options['scripts_in_footer'] ;

    	wp_enqueue_style('slickr-flickr', $path.'/styles/public.css', array(), SLICKR_FLICKR_VERSION);
    	$deps = array('jquery');
    	switch ($options['lightbox']) {
    		 case 'sf-lightbox':  {
		        wp_enqueue_style('slickr-flickr-lightbox', $path."/styles/lightGallery.css", array(),"1.0");
		        wp_enqueue_script('slickr-flickr-lightbox', $path."/scripts/lightGallery.min.js", array('jquery'),"1.0",$footer_scripts);
		        $deps[] = 'slickr-flickr-lightbox';
        	}
    		case 'thickbox': { //preinstalled by wordpress but needs to be activated
    		   wp_enqueue_style('thickbox');
    		   wp_enqueue_script('thickbox');
    		   $deps[] = 'thickbox';
 			   break;
    		}
    		default: { break; } //use another lightbox plugin
    	}
		//TODO wp_enqueue_script('jqpagination', $path."/scripts/jquery.jqpagination.min.js", array('jquery'),"1.3",$footer_scripts);     

		$gname = 'galleria';
    	$galleria = array_key_exists('galleria',$options) ? $options['galleria'] : 'galleria-latest';
    	$gfolder = $path . "/galleria/";    
	    switch ($galleria) {
		    case 'galleria-none': { break; }
		    case 'galleria-original':
		    case 'galleria-1.0': {
    			wp_enqueue_style($gname, $gfolder.'galleria-1.0.css',array(),'1.0');
    			wp_enqueue_script($gname, $gfolder.'galleria-1.0.noconflict.js', array('jquery'), SLICKR_FLICKR_VERSION, $footer_scripts);
        		break;
			}
		    default: {
				$gversion = '1.4.2';
				$gscript = $gfolder . 'galleria-'.$gversion.'.min.js';
				$gtheme = $options['galleria_theme'];
				$gloading = $options['galleria_theme_loading'];
		    	wp_enqueue_script($gname, $gscript, array('jquery'), $gversion, $footer_scripts); //enqueue loading of core galleria script
				if ('static' == $gloading) {
			    	wp_enqueue_script($gname.'-'.$gtheme, self::get_galleria_theme_path($gtheme), array('jquery',$gname), $gversion, $footer_scripts); //enqueue loading of core galleria script
	    			wp_enqueue_style($gname.'-'.$gtheme, self::get_galleria_theme_path($gtheme,true), array(), $gversion);
				}
    		    break;
    		}
		}
    	wp_enqueue_script('rslides', $path.'/scripts/responsiveslides.min.js', 'jquery', '1.54', $footer_scripts);
    	wp_enqueue_script('slickr-flickr', $path.'/scripts/public.js', $deps, SLICKR_FLICKR_VERSION, $footer_scripts);
    	add_filter('print_footer_scripts', array(__CLASS__,'print_scripts'),100); //start slickr flickr last
		if ($footer_scripts) add_action('wp_footer', array(__CLASS__,'dequeue_redundant_scripts'),1);
	}

	static function dequeue_redundant_scripts() {
		if (count(self::$galleria_themes)==0) wp_dequeue_script('galleria'); 
		if (! self::$is_present) {
			wp_dequeue_script('slickr-flickr'); 
			wp_dequeue_script('slickr-flickr-lightbox');
		}
	}

	static function print_scripts() {
		if (self::$is_present) {
			self::print_jquery_data(); //setup of the image data
			self::load_galleria_theme(); //lazy loading and possible change of Galleria theme
 			self::start_show(); //start the gallerias, galleries and slideshows
 		}
	}

	static function note_active() {
		self::$is_present = true;
	}
		
	static function add_galleria_theme($theme) {
		if (! in_array($theme, self::$galleria_themes)) self::$galleria_themes[] = $theme;
	}

	static function get_galleria_theme_path($theme, $css = false) {
		if (empty($theme)) $theme = 'classic';
		if ('classic'==$theme) 
    	    $themepath = SLICKR_FLICKR_PLUGIN_URL. '/galleria/themes/classic/galleria.classic';
		else  //premium themes are located outside the plugin folder
    	    $themepath = site_url( Slickr_Flickr_Options::get_option('galleria_themes_folder'). '/' . $theme .'/galleria.'. $theme);
		return $themepath . ($css ? '.css' : '.min.js');
	}	

	static function load_galleria_theme() {
	    $options = Slickr_Flickr_Options::get_options();
		if (('galleria-latest' != $options['galleria']) 
		|| ('dynamic' != $options['galleria_theme_loading'])
		|| (count(self::$galleria_themes) == 0)) return;

		print <<< LOAD_THEME_START
<script type="text/javascript">
//<![CDATA[
jQuery(document).ready(function() { 
LOAD_THEME_START;
		foreach (self::$galleria_themes as $theme) 
			printf( 'Galleria.loadTheme("%1$s");', self::get_galleria_theme_path($theme)) ;
		print <<< LOAD_THEME_END
})
//]]>
</script>

LOAD_THEME_END;
	}

	static function add_jquery($line) {
		self::$jquery_data[]= $line;
	}

	static function print_jquery_data() {
		if (count(self::$jquery_data) == 0) return;
		$output = '';
    	foreach (self::$jquery_data as $data) $output .= $data."\r\n";
		print <<< JQUERY_DATA
<script type="text/javascript">
//<![CDATA[
jQuery(document).ready(function() {
{$output}
});
//]]>
</script>
JQUERY_DATA;
	}

	static function start_show() {
		print <<< START_SLIDESHOWS
<script type="text/javascript">
//<![CDATA[
jQuery.noConflict(); 
jQuery(document).ready(function() { slickr_flickr_start(); });
//]]>
</script>
START_SLIDESHOWS;
	}

}
