<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/
require_once("../api.php");

$post_id = intval($_REQUEST['ID']);
//-----------
//No need to control access here. this does not writes.
//-------------

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