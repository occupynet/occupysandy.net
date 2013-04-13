<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class CSSOptions {
	var $capability = 'manage_sws';
	//--menu parameters:
	var $page_title;
	var $menu_text;	
	var $option_menu_parent;
	function CSSOptions($args=array()){
		$defaults = array(
			'capability'			=> 'manage_sws',
			'page_title'			=> __('Options','sws'),
			'menu_text'				=> __('Options','sws'),
			'option_menu_parent'	=> 'edit.php?post_type=csshortcode',
			'option_show_in_metabox'=> false
		);
		foreach($defaults as $property => $default){
			$this->$property = isset($args[$property])?$args[$property]:$default;
		}
		add_action('admin_menu',array(&$this,'admin_menu'),100);
	}
	function admin_menu(){
		if(current_user_can('manage_options')||current_user_can($this->capability)){
			$plugin_page = add_submenu_page($this->option_menu_parent, $this->page_title, $this->menu_text, 'read', 'shortcode-options', array(&$this, 'shortcode_options') );
			add_action( 'admin_head-'. $plugin_page, array(&$this,'options_head') );	
		}			
	}
			
	function shortcode_options(){
		$sys_msg='';
		if(isset($_POST['f_save'])){
			$options = array(
				'show_in_metabox' => isset($_POST['show_in_metabox'])?1:0,
				'disable_autop' => isset($_POST['disable_autop'])?1:0,
				'disable_sws_in_widget' => isset($_POST['disable_sws_in_widget'])?1:0,
				'skip_shortcode_priority'=>isset($_POST['skip_shortcode_priority'])?1:0,
				'allowed_ext_thumb_url'=>'',
				'increase_pcre'=>isset($_POST['increase_pcre'])?1:0,
				'license_key'=>isset($_POST['license_key'])?$_POST['license_key']:'',
				'meta_box_post_types'=>isset($_POST['meta_box_post_types'])&&is_array($_POST['meta_box_post_types'])?$_POST['meta_box_post_types']:array()
			);
			$options['allowed_ext_thumb_url'] = isset($_POST['allowed_ext_thumb_url'])?$_POST['allowed_ext_thumb_url']:'';
			
			update_option('sws_options',$options);
			$sys_msg='<div id="message" class="updated below-h2">Options saved.</div>';
		}
		
		$options = get_option('sws_options');
		$options = empty($options)?array():$options;
		//----------		
?>
<div class="css-options-main wrap">
<h2>Styles with Shortcodes Options</h2>
<?php echo $sys_msg ?>
<form name="sform" method="post" action="">

<div id="css-options-cont">
	<div id="css-defaults" class="toggle-option">
		<h3 class="option-title">General Settings<span>General settings</span></h3>
		<div class="option-content">			

			<div class="description">Some Shortcodes may break when autop is active.  By checking this option the plugin will disable the WordPress autop filter.</div>	
			<div class="pt-option">
				<input type="checkbox" <?php echo isset($options['disable_autop'])&&$options['disable_autop']==1?'checked="checked"':''?> name="disable_autop" value="1">&nbsp;<?php _e('Disable autop','css')?>		
			</div>
			<div class="clear"></div>
			<div class="description">Check this to disable shortcodes in widget.  Only needed if for some reason it breaks the theme widget content.</div>	
			<div class="pt-option">
				<input type="checkbox" <?php echo isset($options['disable_sws_in_widget'])&&$options['disable_sws_in_widget']==1?'checked="checked"':''?> name="disable_sws_in_widget" value="1">&nbsp;<?php _e('Disable SWS in widget content','css')?>		
			</div>
			<div class="clear"></div>
			<div class="description">By default the Image Resizer (TimThumb) can resize images hosted on flickr.com, picasa.com, blogger.com, wordpress.com, and img.youtube.com. If you want to allow additional sites you can add them to the list to your left.  <strong>Use one url per line</strong>.</div>	
			<div class="pt-option">
				<label for="allowed_ext_thumb_url">Allowed urls:</label>&nbsp;
				<textarea name="allowed_ext_thumb_url" cols=40><?php echo isset($options['allowed_ext_thumb_url'])?$options['allowed_ext_thumb_url']:''?></textarea>	
			</div>
			<div class="clear"></div>
			
		
		</div>
	</div>	
	
	<div id="css-defaults" class="toggle-option">
		<h3 class="option-title">Bundles<span>Restore bundles</span></h3>
		<div class="option-content">			
			<div class="description">Bundles can be added by plugin add-ons or SWS updates. Because you can customize Shortcodes to make it perfect for your theme, the plugin does not overwrite existing Shortcodes when activating them, if it was previously installed on the system; instead you need to manually choose a bundle and click on restore to return Shortcodes to their initial configuration.</div>	
			<div class="pt-option">
				<label for="restore-bundle">Restore bundle:</label>
				<?php echo $this->bundles_dropdown();?>		
			</div>
			<br />
			<div class="pt-option">
				<p><input type="button" id="btn_restore_bundle" class="button-secondary" value="Click to restore bundle" /></p>
				<p><div id="restore_status"></div></p>
			</div>
			<div class="clear"></div>
		</div>
	</div>	
	
	<div id="css-defaults" class="toggle-option">
		<h3 class="option-title">Troubleshooting options<span></span></h3>
		<div class="option-content">	
			<div class="description"><p>If you are writing a long post that contains shortcodes, but it is rendering and empty page, check this option to increase php settings pcre.backtrack_limit and pcre.recursion_limit; depending on your hosting this may not be available.  You can also do this manually on your php.ini settings.</p>
<p>
<ul>
<li>php.ini pcre.backtrace_limit=<?php echo @ini_get('pcre.backtrack_limit')?></li>
<li>php.ini pcre.recursion_limit=<?php echo @ini_get('pcre.recursion_limit')?></li>
</ul></p>
			</div>	
			<div class="pt-option">
				<input type="checkbox" <?php echo isset($options['increase_pcre'])&&$options['increase_pcre']==1?'checked="checked"':''?> name="increase_pcre" value="1">&nbsp;<?php _e('Increase pcre backtrack and recursion limits','css')?>		
			</div>
			<div class="clear"></div>	

		</div>
	</div>
	
<?php if($this->option_show_in_metabox): ?>
	<div id="css-editor-options" class="toggle-option">
		<h3 class="option-title">Shortcode Insert Tool settings<span>UI Options, custom post types</span></h3>
		<div class="option-content">
			<div class="description">check this option if you want the shortcode insert tool to be displayed in a metabox instead of the standard S icon above the editor.</div>	
			<div class="pt-option">
				<input type="checkbox" <?php echo isset($options['show_in_metabox'])&&$options['show_in_metabox']==1?'checked="checked"':''?> name="show_in_metabox" value="1">&nbsp;<?php _e('Show shortcode tool in a metabox','css')?>		
			</div>
			<div class="clear"></div>
<?PHP $custom_post_types = get_post_types(array('_builtin' => false),'objects','and');?>
<?php if(is_array($custom_post_types)&&count($custom_post_types)>0):?>
			<div class="description">Check the custom post types where you want the shortcode insert tool metabox to be displayed.  Only applicable when using the shortcode insert tool metabox.</div>
<?php foreach($custom_post_types as $post_type => $pt):$name = isset($pt->label)&&trim($pt->label)!=''?$pt->label:$post_type;?>
			<div class="pt-option">
				<input type="checkbox" <?php echo isset($options['meta_box_post_types'])&&is_array($options['meta_box_post_types'])&&in_array($post_type,$options['meta_box_post_types'])?'checked="checked"':''?> name="meta_box_post_types[]" value="<?php echo $post_type?>">&nbsp;<?php echo $name?>		
			</div>			
<?php endforeach;else:?>
			<p>&nbsp;</p>
<?php endif;?>			
		</div>	
	</div>
<?PHP endif;?>

<?php if(!defined('WPCSS_THEME')):?>	
	<div id="css-defaults" class="toggle-option">
		<h3 class="option-title">License<span></span></h3>
		<div class="option-content">	
			<div class="description">
<p>Your purchase code can be found in your license Certificate file.</p>
<p>Go to Codecanyon and click My Account at the top, then click Downloads, and then click the <strong>License Certificate link</strong>.
You will find the code in there and it will look something like this:</p>
<p>Item Purchase Code:<br />bek72585-d6a6-4724-c8c4-9d32f85734g3</p>
<p>This allows us to verify your purchase and provide support to those who have paid. We will also automatically notify you when updates are available. Updates are free to download if you have purchased this once. If you have questions about this, please contact us at <a href="mailto:support@righthere.com">support@righthere.com</a>.</p>			
			</div>	
			<div class="pt-option">
				<label>Lincense key:</label>
				<input type="text" size="50"name="license_key" value="<?php echo isset($options['license_key'])?$options['license_key']:''?>" />
			<div class="clear"></div>	

		</div>
	</div>
<?php endif;?>	
</div>

<input type="submit" class="button-primary save-button" name="f_save" value="Save" />
</form>
</div>
<?php	
	}

	function options_head(){
?>
<script>
 jQuery(document).ready(function($){ 
 	$(".option-title").click(function(){$(this).toggleClass('open').next().toggle();});
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