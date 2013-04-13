<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class CSSEditor {
	var $post;
	
	var $show_in_metabox = false;
	var $meta_box_post_types = array();
	var $metabox_title = '';
	function CSSEditor($args=array()){
		$defaults = array(
			'show_in_metabox' 		=> false,
			'metabox_title' 		=> __('Styles with shortcodes','css'),
			'meta_box_post_types'	=> array(),
			'editor_head_always'	=> 0
		);
		foreach($defaults as $property => $default){
			$this->$property = isset($args[$property])?$args[$property]:$default;
		}
		
		if(1==$this->editor_head_always){
			add_action("admin_head",array(&$this,'insert_tool_head'));
		}else{
			add_action("admin_head-post.php",array(&$this,'insert_tool_head'));
			add_action("admin_head-post-new.php",array(&$this,'insert_tool_head'));				
		}
		
		if($this->show_in_metabox){
			add_action('admin_menu', array(&$this, 'post_meta_box') );
		}else{
			add_action('media_buttons_context',array(&$this,'media_buttons_context'));
		}
		
		add_action('wp_ajax_mce_list_fields', array(&$this,'mce_list_fields'));
	}
	
	function post_meta_box(){
		add_meta_box( 'sws-insert-tool-meta', $this->metabox_title,	array( &$this, 'add_mce_popup' ), 'page', 'normal', 'high');
		add_meta_box( 'sws-insert-tool-meta', $this->metabox_title,	array( &$this, 'add_mce_popup' ), 'post', 'normal', 'high');
		if(is_array($this->meta_box_post_types)&&count($this->meta_box_post_types)>0){
			foreach($this->meta_box_post_types as $post_type){
				add_meta_box( 'sws-insert-tool-meta', $this->metabox_title,	array( &$this, 'add_mce_popup' ), $post_type, 'normal', 'high');
			}
		}
	}
	
	function insert_tool_head(){
		wp_print_styles('sws-insert-tool');
		wp_print_scripts('sws-insert-tool');
		if(!$this->show_in_metabox){	
			add_action('admin_footer',array(&$this,'shortcode_dialog'),1);
		}
	}
	
	function shortcode_dialog(){

?>
<div id="sws-insert-tool" class="sws-dialog-cont">
	<div class="sws-dialog-overlay"></div>
	<div class="sws-dialog">
		<div class="sws-dialog-head">
			<div class="sws-dialog-head-text"><?php _e("Add Styles with Shortcodes", 'wpcss')?></div>
			<div class="sws-close-icon">
				<a class="sws-close-icon-a" title="Close" href="javascript:void(0);" alt="Close"><img src="<?php echo WPCSS_URL?>css/images/tb-close.png" /></a>			
			</div>
		</div>	
		<div class="sws-dialog-body">
<?php $this->add_mce_popup_body(); ?>		
		</div>
	</div>
</div>
<?php	
	}
		
	function media_buttons_context($context){
        $out = '<a id="sws-insert-tool-trigger" href="javascript:void(0);" title="'. __("Add Styles with Shortcodes", 'wpcss').'"><img src="'.WPCSS_URL."css/images/icon_shortcodes.png".'" alt="'. __("Add Styles with Shortcodes", 'wpcss') . '" /></a>';
        return $context . $out;
	}
	
	function old_media_buttons_context($context){
        $out = '<a href="&TB_inline?width=920&inlineId=insert_csshortcode" class="thickbox" title="'. __("Add Styles with Shortcodes", 'wpcss').'"><img src="'.WPCSS_URL."css/images/icon_shortcodes.png".'" alt="'. __("Add Styles with Shortcodes", 'wpcss') . '" /></a>';
		add_action('admin_footer',array(&$this,'add_mce_popup'));
        return $context . $out;
	}

    function add_mce_popup(){
		$this->add_mce_popup_head();
		$this->add_mce_popup_body();
    }
	
	function add_mce_popup_head(){
		/* moved to insert_tool.css and insert_tool.js*/
	}
	
	function install_bundle_if_not_installed(){
		global $wpdb;
		$installed_shortcodes_count = intval($wpdb->get_var("SELECT count(ID) FROM `{$wpdb->posts}` WHERE post_type='csshortcode' AND post_status='publish'",0,0));
		if(0==$installed_shortcodes_count){
			sws_install();
		}
	}
	
	function add_mce_popup_body(){
		$this->install_bundle_if_not_installed();
?>
        <div id="insert_csshortcode" style="<?php echo $this->show_in_metabox?'':''/*'display:none;'*/?>">
            <div id="css-mce-form" class="wrap">
			   <a id="css-mce-form-anchor"></a>
			   <div class="shortcode-nav">
				   <div class="fieldset" style="display:block;clear:both;">
					<label class="css-mce-label">Shortcode Category</label>
					<div class="css-mce-input">
						<?php $this->category_dropdown()?>
					</div>
					
				   </div>
				   
				   <div class="fieldset" style="display:block;clear:both;">
				   	<label class="css-mce-label">Shortcode</label>
					<div class="css-mce-input">
						<?php $this->shortcode_dropdown()?>
					</div>
				   	
				   </div>
				   			   
			   </div>
				
			
			<div class="clearer"></div>
			<div id="shortcode-preview" style="display:block;float:left;margin:10px;"></div>
			<div class="clearer"></div>
			
			   <div id="css-mce-fields-cont">
				   <div id="css-fields"></div>
				   
			   </div>
            </div>

        </div>
<?php	
	}
	
	function category_dropdown($id='cs_category'){
		$categories = get_terms('csscategory');
		echo "<select id=\"$id\">";
		echo "<option value=\"\">--any--</option>";
		if(is_array($categories)&&count($categories)>0){
			foreach($categories as $c){
				echo "<option value=\"cat".$c->term_id."\">".$c->name."</option>";
			}
		}
		echo "</select>";			
	}
	
	function shortcode_dropdown($id='cs_shortcode',$echo=true){
		global $wpdb;
		
		$str = "<select id=\"$id\">";
		$str.="<option value=\"0\">--choose a shortcode--</option>";
		
		$sql = "SELECT ID as value, post_title as label FROM `{$wpdb->posts}` WHERE post_type='csshortcode' AND post_status='publish'";
		if($wpdb->query($sql)&&$wpdb->num_rows>0){
			foreach($wpdb->last_result as $row){
				$post_terms = wp_get_post_terms( $row->value, 'csscategory' );
				if(is_array($post_terms)&&count($post_terms)>0){
					$c=array();
					foreach($post_terms as $term){
						$c[]='cat'.$term->term_id;
					}
					$class = "class=\"".implode(' ',$c)."\"";
				}else{
					$class = '';
				}
				
				$preview = get_post_meta($row->value,'sc_preview',true);
				$preview = ''==trim($preview)?'':str_replace('{pluginurl}',WPCSS_URL,$preview);
				$str.= sprintf("<option %s value=\"%s\" rel=\"%s\">%s</option>",$class,$row->value,$preview,htmlentities($row->label));		
			}
		}else{
			$str.="<option value=\"\">".__("--no options--",'wpcss')."</option>";
		}
		$str.= "</select>";
		if($echo)
			echo $str;
		
		return $str;	
	}
	
	function mce_list_fields(){		
		$post_id = intval($_REQUEST['ID']);
		
		require_once WPCSS_PATH.'includes/class.SCTypes.php';		
		$SCTypes = new SCTypes();
		$fields = get_post_meta($post_id,'sc_fields',true);
		
		if(is_array($fields)&&count($fields)>0){
			foreach($fields as $index => $f){
				if(!isset($f->content)){
					$fields[$index]->content = 0;
				}
			}
		}
		
		$shortcode = get_post_meta($post_id,'sc_shortcode',true);		
		$template = get_post_meta($post_id,'sc_template',true);
		
		$con = (object)array(
			'label' => 'Content',
			'name' => 'content',
			'default'=> ' ',
			'content'=> 1,
			'type'=>'textarea'
		);
		
		if(false!==strpos($template,'{content}') && !isset($fields['content'])){
			$have_content=false;
			if(is_array($fields) && count($fields)>0){
				foreach($fields as $f){
					if($f->content==1 || $f->type=='data'){
						$have_content=true;
						break;
					}
				}	
			}
		
			if(!$have_content){
				$fields['content']=$con;
			}
		}
		
		if(is_array($fields) && count($fields)>0):
			$SCTypes->get_fields($shortcode, $fields);
		else:
		?>
		<input type="hidden" class="mce-item mce-scopentag" value="<?php echo $shortcode?>" />
		<input type="hidden" class="mce-item mce-scclose" value="<?php echo $shortcode?>" />
		<?php 
		endif; ?>

		<input type="hidden" id="sc_shortcode" name="sc_shortcode" value="<?php echo $shortcode?>" />
		<div class="fieldset-buttons">
			<input type="button" OnClick="javascript:insert_csshortcode();" class="button-primary" value="Insert shortcode" />
		</div>
		<div class="clearer"></div>
<?php $SCTypes->scripts(); ?>
<script type='text/javascript' src='<?php echo WPCSS_URL?>js/jquery.tools.rangeinput.min.js'></script>
<script type='text/javascript' src='<?php echo WPCSS_URL?>colorpicker/js/colorpicker.js'></script>
<script type="text/javascript">
jQuery.noConflict();
jQuery(document).ready(function($){	
		$('#TB_ajaxContent').css('height','90%');
		$('#TB_ajaxContent').css('width','96%');
		
		$('.mce-row-delete').click(function(){
			$(this).parent().parent().remove();
		});
		$('.add-mce-data-row').click(function(){
			$(this).parent().parent().find('.mce-data-row:first').clone()
				.find('.mce-row-delete').click(function(){
					$(this).parent().parent().slideUp(function(){
						$(this).remove();
					})
				})
				.show().parent().parent()
				.hide().appendTo( $(this).parent().parent().find('.mce-data-rows') ).slideDown('fast',function(){
					set_helpers();
				});
		});
		$('.admin-load-ui-theme').change();
		
		//--
		set_helpers();
});
</script>
<?php
		die();
	}
}
?>