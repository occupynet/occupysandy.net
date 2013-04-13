<?php

class custom_shortcode_styling {
	var $id = 'styles-with-shortcodes';
	var $options;
	var $sws_scripts = array();
	var $sws_styles = array();
	var $bundles = array();
	var $show_ui = true;
	var $options_parameters = array();
	var $editor_parameters = array();
	function custom_shortcode_styling($args=array()){
		$option_overwrite = isset($args['options'])&&is_array($args['options'])?$args['options']:array();
		$this->options = get_option('sws_options');
		if(is_array($option_overwrite)&&count($option_overwrite)>0){
			foreach($option_overwrite as $field => $value){
				$this->options[$field]=$value;
			}
		}		
		//------------
		$defaults = array(
			'show_ui'			=> true,
			'options_parameters'=> array(),
			'editor_parameters'	=> $this->get_editor_parameters()
		);
		foreach($defaults as $property => $default){
			$this->$property = isset($args[$property])?$args[$property]:$default;
		}
		//-----------
		$this->options_parameters = $this->get_options_parameters();
		//-----------
		add_action('plugins_loaded',array(&$this,'plugins_loaded'));
		add_action('init',array(&$this,'init'));
		
		if(isset($this->options['disable_autop'])&&$this->options['disable_autop']==1){
			remove_filter ('the_content', 'wpautop');
		}
		
		if(isset($this->options['increase_pcre'])&&$this->options['increase_pcre']==1){
			try {
				if(@ini_get('pcre.backtrack_limit')<10000001){
					@ini_set('pcre.backtrack_limit',10000001);
				}
				if(@ini_get('pcre.recursion_limit')<20000002){
					@ini_set('pcre.recursion_limit',20000002);
				}
			}catch(Exception $e){
				
			}
		}
	}
	function get_options_parameters(){
		$r = array();
		foreach(array('option_show_in_metabox') as $field){
			if(isset($this->options[$field])){
				$r[$field]=$this->options[$field];
			}
		}
		$defaults = array(				
			'id'					=> $this->id,
			'plugin_id'				=> $this->id,
			'capability'			=> 'manage_options',
			'options_varname'		=> 'sws_options',
			'menu_id'				=> 'sws-options',
			'page_title'			=> __('Options','sws'),
			'menu_text'				=> __('Options','sws'),
			'option_menu_parent'	=> 'edit.php?post_type=csshortcode',
			'notification'			=> (object)array(
				'plugin_version'=> WPCSS,
				'plugin_code' 	=> 'SWS',
				'message'		=> __('Styles With Shortcodes update %s is available! <a href="%s">Please update now</a>','sws')
			),
			'theme'					=> false,
			'stylesheet'			=> 'sws-options',
			'option_show_in_metabox'=> true					
		);
		
		foreach($defaults as $field => $value){
			$r[$field] = isset($r[$field])?$r[$field]:$value;
		}
				
		return $r;
	}
	function get_editor_parameters(){
		$r = array();
		foreach(array('show_in_metabox','metabox_title','meta_box_post_types','editor_head_always') as $field){
			if(isset($this->options[$field])){
				$r[$field]=$this->options[$field];
			}
		}
		return $r;
	}
	
	function init(){
		wp_enqueue_script('sws_frontend',WPCSS_URL.'js/sws_frontend.js',array('jquery'),'1.0.0');
		if(is_admin()):
			if(@$_REQUEST['post_type']!='slider')wp_enqueue_style('wpcss-toggle',WPCSS_URL.'css/toggle.css',array(),'1.0.3');
			wp_enqueue_style('jquery-colorpicker',WPCSS_URL.'colorpicker/css/colorpicker.css',array(),'1.0.0');
			wp_register_style( 'sws-insert-tool', WPCSS_URL.'css/insert_tool.css', array(),'1.0.0');
			wp_register_style( 'sws-options', WPCSS_URL.'css/pop.css', array(),'1.0.0');
			
			wp_enqueue_script('wpsws',WPCSS_URL.'js/sws.js',array(),'1.0.2');
			wp_enqueue_script('jquery-colorpicker',WPCSS_URL.'colorpicker/js/colorpicker.js',array('jquery'),'1.0.0');				
			wp_register_script( 'sws-insert-tool', WPCSS_URL.'js/insert_tool.js', array(),'1.0.0');			
		endif;
	}
	
	function plugins_loaded(){			
		//-- register scripts ----
		require_once WPCSS_PATH.'includes/bundled_scripts_and_styles.php';	
		new bundled_scripts_and_styles();//
		
		require_once WPCSS_PATH.'includes/class.CSShortcodes.php';//defines the csshortcode custom post type.
		new CSShortcodes(array('show_ui'=>$this->show_ui));
		
		require_once WPCSS_PATH.'includes/class.ImportExport.php';//api for importing exporting		
		require_once WPCSS_PATH.'includes/class.CSShortcodesLoad.php';//load shortcodes to wp
		require_once WPCSS_PATH.'includes/class.sws_mce.php';
		require_once WPCSS_PATH.'includes/class.sws_lightbox.php';
		new sws_lightbox($this->options);
		
		if(is_admin()):
			if(!isset($this->options['editor_capability']) || ''==$this->options['editor_capability'] || current_user_can($this->options['editor_capability']) ){				
				require_once WPCSS_PATH.'includes/class.CSSEditor.php';
				new CSSEditor($this->editor_parameters);
			}
			
			require_once WPCSS_PATH.'includes/class.plugin_registration.php';
			new plugin_registration(array('plugin_id'=>$this->id,'tdom'=>'sws','plugin_code'=>'SWS','options_varname'=>'sws_options'));			
			
			require_once WPCSS_PATH.'includes/class.PluginOptionsPanel.php';		
			new PluginOptionsPanel($this->options_parameters);		
			require_once WPCSS_PATH.'includes/class.sws_options.php';
			new sws_options($this->options_parameters);		
			require_once WPCSS_PATH.'includes/class.lightbox_options.php';
			new lightbox_options($this->options_parameters);
			
			$this->options['license_keys'] =  is_array($this->options['license_keys'])&&count($this->options['license_keys'])>0?$this->options['license_keys']:array();			
			if(!defined('rh_downloadable_content'))require_once WPCSS_PATH.'includes/class.rh_downloadable_content.php';

			new rh_downloadable_content(array(
				'id'			=> 'sws_downloads',
				'parent_id'		=> $this->options_parameters['option_menu_parent'],
				'menu_text'		=> __('Downloads','sws'),
				'page_title'	=> __('Downloadable content - Styles With Shortcodes for WordPress','sws'),
				'license_keys'	=> $this->options['license_keys'],
				'api_url'		=> 'http://plugins.righthere.com/',
				//'api_url'		=> 'http://dev.lawley.com/',
				'product_name'	=> __('Styles With Shortcodes','sws')
			));							
		endif;		
	}
	
	function add_script($id,$label,$url=''){
		$this->sws_scripts[] = (object)array('id'=>$id,'label'=>$label,'url'=>$url);
	}
	
	function add_style($id,$label,$url='',$ui_theme=false){
		$this->sws_styles[] = (object)array('id'=>$id,'label'=>$label,'url'=>$url,'ui_theme'=>$ui_theme);
	}
	
	function add_bundle($id,$label,$path){
		$this->bundles[$id]=(object)array('id'=>$id,'label'=>$label,'path'=>$path);
	}
}  


?>