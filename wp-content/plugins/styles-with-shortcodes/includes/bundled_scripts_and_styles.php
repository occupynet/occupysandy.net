<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class bundled_scripts_and_styles {
	function bundled_scripts_and_styles(){
		add_action('init',array(&$this,'init'));
	}
	
	function init(){
		global $sws_plugin;
		
		$sws_plugin->add_bundle('starter','Starter',WPCSS_PATH.'includes/bundle.php');
		
		wp_register_style('sws-button',WPCSS_URL.'css/sws_button.css',array(),'1.0.1');	
		$sws_plugin->add_style('sws-button','SWS Button','');
		
		//registered by wp
		//$sws_plugin->add_script('jquery-ui-core','jQuery UI core','http://jqueryui.com');

		wp_register_script('jquery-ui',WPCSS_URL.'js/jquery-ui-1.8.10.custom.min.js',array(),'1.8.10');
		$sws_plugin->add_script('jquery-ui','jQuery UI','http://jqueryui.com');		

		wp_register_script('sws-jquery-ui-tabs',WPCSS_URL.'js/jquery-ui-tabs-1.8.10.custom.min.js',array('jquery-ui'),'1.8.10.1');
		$sws_plugin->add_script('sws-jquery-ui-tabs','jQuery UI Tabs','http://jqueryui.com');		

		wp_register_script('jquery-easing',WPCSS_URL.'js/jquery.easing.1.3.js',array(),'1.3.0');
		$sws_plugin->add_script('jquery-easing','jQuery Easing Plugin 1.3','http://gsgd.co.uk/sandbox/jquery/easing/');		
		
		wp_register_style('ui-smoothness',WPCSS_URL.'css/smoothness/jquery-ui-1.8.7.custom.css',array(),'1.8.7');	
		$sws_plugin->add_style('ui-smoothness','UI Smoothness','',true);	
		
		wp_register_style('start',WPCSS_URL.'css/start/jquery-ui-1.8.7.custom.css',array(),'1.8.7');	
		$sws_plugin->add_style('start','UI Start','',true);		
		
		wp_register_script('lightbox-evolution',WPCSS_URL.'js/lightbox/jquery.lightbox.js',array('jquery'),'1.5.3');		
		$sws_plugin->add_script('lightbox-evolution','Lightbox Evolution','');			
		
		wp_register_script('preloadify',WPCSS_URL.'js/preloadify/jquery.preloadify.js',array('jquery','lightbox-evolution'),'1.0.3');		
		$sws_plugin->add_script('preloadify','Preloadify','');		
		
		wp_register_style('preloadify',WPCSS_URL.'js/preloadify/plugin/css/style.css',array(),'1.0.2');	
		$sws_plugin->add_style('preloadify','Preloadify','');		
		
		wp_register_style('sws-tables',WPCSS_URL.'css/tables.css',array(),'1.0.0');	
		$sws_plugin->add_style('sws-tables','Table templates','');	
		
		wp_register_script('tools-rangeinput',WPCSS_URL.'js/jquery.tools.rangeinput.min.js', array(),'1.2.5');		
		//$sws_plugin->add_script('tools-rangeinput','Rangeinput','');		
		
		wp_register_style('sws-picture-frames',WPCSS_URL.'css/picture_frames.css',array(),'1.0.0');	
		$sws_plugin->add_style('sws-picture-frames','Picture frames','');	

		//jquery-tools have a tabs method, if this is included by the theme or another plugin then our tabs dont work.
		wp_register_script('sws-jquery-tools',WPCSS_URL.'js/jquery.tools.min.js',array('jquery'),'1.2.5');
		$sws_plugin->add_script('sws-jquery-tools','jQuery TOOLS','http://flowplayer.org/tools/index.html');	
	}
}
?>