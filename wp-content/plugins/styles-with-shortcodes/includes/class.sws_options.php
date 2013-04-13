<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class sws_options {
	var $plugin_id;
	function sws_options($args=array()){
		$defaults = array(
			'plugin_id'=>'styles-with-shortcodes',
			'option_show_in_metabox'=> false
		);
		foreach($defaults as $property => $default){
			$this->$property = isset($args[$property])?$args[$property]:$default;
		}	
		
		add_action("pop_admin_head_{$this->plugin_id}",array(&$this,'pop_admin_head'));
		add_filter("pop-options_{$this->plugin_id}",array(&$this,'options'),10,1);
		
		add_filter("pop-options_{$this->plugin_id}",array(&$this,'accessibility_options'),15,1);
		if($this->option_show_in_metabox){
			add_filter("pop-options_{$this->plugin_id}",array(&$this,'insert_tool_options'),15,1);
		}				
	}
	function options($t){
		$i = count($t);
		//-------------------------		
		$i++;
		$t[$i]->id 			= 'general'; 
		$t[$i]->label 		= __('General Settings','sws');//title on tab
		$t[$i]->right_label	= __('General Settings','sws');//title on tab
		$t[$i]->page_title	= __('General Settings','sws');//title on content
		$t[$i]->theme_option = true;
		$t[$i]->plugin_option = true;
		$t[$i]->options = array(
			(object)array(
				'id'	=> 'disable_autop',
				'type'	=> 'checkbox',
				'label'	=> __('Disable autop','sws'),
				'description'=> __('Some Shortcodes may break when autop is active. By checking this option the plugin will disable the WordPress autop filter.','sws'),
				'save_option'=>true,
				'load_option'=>true
			),
			(object)array(
				'id'	=> 'disable_sws_in_widget',
				'type'	=> 'checkbox',
				'label'	=> __('Disable SWS in widget content ','sws'),
				'description'=> __('Check this to disable shortcodes in widget. Only needed if for some reason it breaks the theme widget content.','sws'),
				'save_option'=>true,
				'load_option'=>true
			),
			(object)array(
				'id'	=> 'allowed_ext_thumb_url',
				'type'	=> 'textarea',
				'label'	=> __('Allowed urls','sws'),
				'description'=> __('By default the Image Resizer (TimThumb) can resize images hosted on flickr.com, picasa.com, blogger.com, wordpress.com, and img.youtube.com. If you want to allow additional sites you can add them to the list to your left. <strong>Use one url per line.</strong>','sws'),
				'save_option'=>true,
				'load_option'=>true
			),
			(object)array(
				'type'	=> 'clear',
			),
			(object)array(
				'type'	=> 'submit',
				'label'	=> __('Save','sws'),
				'class' => 'button-primary',
				'save_option'=>false,
				'load_option'=>false
			)
		);		
		//-------------------------			
		$i++;
		$t[$i]->id 			= 'bundles'; 
		$t[$i]->label 		= __('Bundles','sws');//title on tab
		$t[$i]->right_label	= __('Restore bundles','sws');//title on tab
		$t[$i]->page_title	= __('Bundles','sws');//title on content
		$t[$i]->theme_option = true;
		$t[$i]->plugin_option = true;
		$t[$i]->options = array(
			(object)array(
				'type'	=> 'callback',
				'description'=>__("Bundles can be added by plugin add-ons or SWS updates. Because you can customize Shortcodes to make it perfect for your theme, the plugin does not overwrite existing Shortcodes when activating them, if it was previously installed on the system; instead you need to manually choose a bundle and click on restore to return Shortcodes to their initial configuration.","sws"),
				'callback'=>array(&$this,'restore_bundle')
			),
			(object)array(
				'type'	=> 'clear',
			)
		);
		//--------------------------			
		$i++;
		$t[$i]->id 			= 'troubleshooting'; 
		$t[$i]->label 		= __('Troubleshooting options','sws');//title on tab
		$t[$i]->right_label	= __('','sws');//title on tab
		$t[$i]->page_title	= __('Troubleshooting options','sws');//title on content
		$t[$i]->theme_option = true;
		$t[$i]->plugin_option = true;
		$t[$i]->options = array(
			(object)array(
				'id'	=> 'increase_pcre',
				'type'	=> 'checkbox',
				'label'	=> __('Increase pcre backtrack and recursion limits','css'),
				'description'=> $this->increase_pcre_description(),
				'save_option'=>true,
				'load_option'=>true				
			),
			(object)array(
				'type'	=> 'clear',
			),
			(object)array(
				'type'	=> 'submit',
				'label'	=> __('Save','sws'),
				'class' => 'button-primary',
				'save_option'=>false,
				'load_option'=>false
			)
		);

		return $t;
	}
	
	function accessibility_options($t){
		$i = count($t);
		//-------------------------			
		$i++;
		$t[$i]->id 			= 'accessibility'; 
		$t[$i]->label 		= __('Shortcode Insert Tool Accessibility','sws');//title on tab
		$t[$i]->right_label	= __('Capabilities','sws');//title on tab
		$t[$i]->page_title	= __('Shortcode Insert Tool Accessibility','sws');//title on content
		$t[$i]->theme_option = true;
		$t[$i]->plugin_option = true;
		$t[$i]->options = array(
			(object)array(
				'id'	=> 'editor_capability',
				'type'	=> 'select',
				'options'=> $this->capability_options(),
				'label'	=> 'Capability',
				'description'=> __('If you want to restrict access to the shortcode insert tool (S on top of the editor, or metabox) check the capability that the user needs from the dropdown.','sws'),
				'save_option'=>true,
				'load_option'=>true				
			)
		);
		$t[$i]->options[]=(object)array('type'=>'clear');
		$t[$i]->options[]=(object)array(
				'type'=>'submit',
				'class'=>'button-primary',
				'label'=>__('Save','sws')
			);
		return $t;
	}
	
	function get_all_caps_from_wp_roles($WP_Roles){
		$all_caps = array();
		if(count($WP_Roles->roles)>0){
			foreach($WP_Roles->roles as $role_id => $row){
				foreach($row['capabilities'] as $capability => $allowed){
					$all_caps[$capability]=$capability;
				}
			}
		}
		return $all_caps;	
	}
	
	function capability_options(){
		$WP_Roles = new WP_Roles();
		$all_caps = $this->get_all_caps_from_wp_roles($WP_Roles);
		$options = array(''=>__('--none--','sws'));
		if(is_array($all_caps)&&count($all_caps)>0){
			sort($all_caps);	
			foreach($all_caps as $c){
				$options[$c]=$c;
			}
		}	
		return $options;
	}
	
	function insert_tool_options($t){
		$i = count($t);
		//-------------------------			
		$i++;
		$t[$i]->id 			= 'insert_tool'; 
		$t[$i]->label 		= __('Shortcode Insert Tool settings','sws');//title on tab
		$t[$i]->right_label	= __('UI Options, custom post types','sws');//title on tab
		$t[$i]->page_title	= __('Shortcode Insert Tool settings','sws');//title on content
		$t[$i]->theme_option = true;
		$t[$i]->plugin_option = true;
		$t[$i]->options = array(
			(object)array(
				'id'	=> 'show_in_metabox',
				'type'	=> 'checkbox',
				'label'	=> __('Show shortcode tool in a metabox','css'),
				'description'=> __("Check this option if you want the shortcode insert tool to be displayed in a metabox instead of the standard S icon above the editor.","sws"),
				'save_option'=>true,
				'load_option'=>true				
			),
			(object)array(
				'id'	=> 'editor_head_always',
				'type'	=> 'checkbox',
				'label'	=> __('Always output the sws insert tool head scripts','css'),
				'description'=> __("Check this option if you have a plugin or theme that is adding the sws S icon, but it is not working.","sws"),
				'save_option'=>true,
				'load_option'=>true				
			),
			(object)array(
				'type'	=> 'clear',
			),
			(object)array(
				'type'	=> 'subtitle',
				'label'	=> 'Custom Post Types',
				'description'=> __('Check the custom post types where you want the shortcode insert tool metabox to be displayed. Only applicable when using the shortcode insert tool metabox.','css')
			)
		);		
		//-------
		$custom_post_types = get_post_types(array('_builtin' => false),'objects','and');	
		if(is_array($custom_post_types)&&count($custom_post_types)>0){
			foreach($custom_post_types as $post_type => $pt){
				$name = isset($pt->label)&&trim($pt->label)!=''?$pt->label:$post_type;
				$t[$i]->options[]=(object)array(
						'id'	=> 'meta_box_post_types[]',
						'type'	=> 'checkbox',
						'option_value'	=> $post_type,
						'label'	=> $name,
						'save_option'=>true,
						'load_option'=>true				
					);				
			}
		}else{
			$t[$i]->options[]=(object)array('type'=>'label','value'=>__('There are no additional custom post types.','css'));
		}

		$t[$i]->options[]=(object)array('type'=>'clear');
		$t[$i]->options[]=(object)array(
				'type'=>'submit',
				'class'=>'button-primary',
				'label'=>__('Save','sws')
			);
		return $t;
	}
	
	function increase_pcre_description(){
		ob_start();
?>
<p>If you are writing a long post that contains shortcodes, but it is rendering and empty page, check this option to increase php settings pcre.backtrack_limit and pcre.recursion_limit; depending on your hosting this may not be available.  You can also do this manually on your php.ini settings.</p>
<p>
<ul>
<li>php.ini pcre.backtrace_limit=<?php echo @ini_get('pcre.backtrack_limit')?></li>
<li>php.ini pcre.recursion_limit=<?php echo @ini_get('pcre.recursion_limit')?></li>
</ul></p>
<?php	
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	
	function pop_admin_head(){
?>
<script type='text/javascript'>
 jQuery(document).ready(function($){ 
	$("#btn_restore_bundle").click(function(){restore_bundle( $('#bundle_dropdown').val() );});
 });	
 
 function restore_bundle(bundle){
 	 jQuery(document).ready(function($){ 
	 	$('#restore_status').addClass('left-loading').html('');
		var _url = '<?php echo WPCSS_URL?>api/admin.restore_bundle.php';
		$.post(_url,{'bundle':bundle},function(data){
			if(data.R=='OK'){
				$('#restore_status').html('Operation completed');
			}else if(data.R=='ERR'){
				$('#restore_status').html(data.MSG);
			}else{
				$('#restore_status').html('Unknown error while processing. Please reload and try again.');
			}
			$('#restore_status').removeClass('left-loading');
		},'json');
	 });	 
 }
 </script>
<?php	
	}
	function restore_bundle(){
?>
			<div class="pt-option">
				<label for="restore-bundle">Restore bundle:</label>
				<?php echo $this->bundles_dropdown();?>			
			</div>
			<div class="pt-option">
				<p><input type="button" id="btn_restore_bundle" class="button-secondary" value="Click to restore bundle" /></p>
				<p><div id="restore_status"></div></p>
			</div>		
<?php
	}
	function bundles_dropdown($id='bundle_dropdown',$name='bundle_dropdown',$extra='',$value=''){
		global $sws_plugin;
		$str = sprintf("<select id=\"%s\" name=\"%s\" %s>",$id,$name,$extra);
		if(is_array($sws_plugin->bundles)&&count($sws_plugin->bundles)>0){
			foreach($sws_plugin->bundles as $id => $b ){
				$str.=sprintf("<option %s value=\"%s\">%s</option>", ($b->id==$value?'selected':''),$b->id,$b->label);
			}
		}
		$str.="</select>";
		return $str;
	}	
}
?>