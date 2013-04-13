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
if ( !current_user_can( 'edit_post', $post_id ) ){
	die('No access');
} 
//-------------

class SCTypes {
	var $js_scripts = array();
	var $uid = 0;
	var $allowed_types = array(
		'text',
		'textarea',
		'colorpicker',
		'dropdown',
		'slider'
	);
	function SCTypes(){
	
	}
	
	function get_input($field){
		if(!in_array($field->type,$this->allowed_types)){return '';}
		$method = $field->type;
		return $this->$method($field);
	}
	
	function text($field){
		$str = sprintf("<input id=\"mce-text-%s\" name=\"%s\" type=\"text\" class=\"css-mce-property\" value=\"%s\" />",$this->uid++,$field->name,$field->default);
		return $str;
	}
	function textarea($field){
		$str = sprintf("<textarea id=\"mce-textarea-%s\" name=\"%s\" class=\"css-mce-property\">%s</textarea>",$this->uid++,$field->name,$field->default);
		return $str;	
	}
	
	
	function colorpicker($field){
		$str = sprintf("<input id=\"mce-colorpicker-%s\" name=\"%s\" type=\"text\" class=\"css-mce-property sws-colorpicker\" value=\"%s\" maxlength=\"6\" size=\"6\" />",$this->uid++,$field->name,$field->default);
		return $str;
	}
	
	function dropdown($field){
		$options = explode("\n", (isset($field->options)?$field->options:'|no options') );
		
		$str = sprintf("<select id=\"mce-dropdown-%s\" name=\"%s\" class=\"css-mce-property\">",$this->uid++,$field->name);
		if(is_array($options)&&count($options)>0){
			foreach($options as $row){
				$pair = explode('|',$row);
				if(count($pair)>=2){
					$str.= sprintf("<option %s value=\"%s\">%s</option>",(isset($pair[2])?$pair[2]:''),$pair[0],$pair[1]);
				}
			}
		}
		$str.="</select>";
		return $str;
	}
	
	function slider($field){
		$str = sprintf("<input id=\"mce-slider-%s\" name=\"%s\" class=\"css-mce-property sws-rangeinput\" type=\"range\" min=\"%s\" max=\"%s\" value=\"%s\" />",$this->uid++,$field->name,((isset($field->min)?$field->min:0)),((isset($field->max)?$field->max:100)),$field->default);
		$this->js_scripts[]='jQuery( ".sws-rangeinput" ).rangeinput();';
		return $str;
	}
	
	function scripts(){
		echo "<script type=\"text/javascript\">".implode(" ",$this->js_scripts)."</script>";
	}
}

$SCTypes = new SCTypes();

$fields = get_post_meta($post_id,'sc_fields',true);
$shortcode = get_post_meta($post_id,'sc_shortcode',true);

$template = get_post_meta($post_id,'sc_template',true);

$con = (object)array(
	'label' => 'Content',
	'name' => 'content',
	'default'=> ' ',
	'type'=>'textarea'
);

if(false!==strpos($template,'{content}') && !isset($fields['content'])){
	$fields['content']=$con;
}

if(is_array($fields) && count($fields)>0):foreach($fields as $name => $field): ?>
		<div class="fieldset">
			<label class="css-mce-label"><?php echo $field->label?></label>
			<div class="css-mce-input">
				<?php echo $SCTypes->get_input($field)?>
			</div>
			<div class="clearer"></div>
		</div>
		
<?php endforeach;endif; ?>

		<input type="hidden" id="sc_shortcode" name="sc_shortcode" value="<?php echo $shortcode?>" />
		<div class="fieldset-buttons">
			<input type="button" OnClick="javascript:insert_csshortcode();" class="button-primary" value="Insert shortcode" />
		</div>
		<div class="clearer"></div>
<?php $SCTypes->scripts(); ?>
<script>
jQuery(document).ready(function($){
		$('.sws-colorpicker').ColorPicker({
			onSubmit: function(hsb, hex, rgb, el) {
				$(el).val(hex);
				$(el).ColorPickerHide();
			},
			onBeforeShow: function () {
				$(this).ColorPickerSetColor(this.value);
			}
		})
		.bind('keyup', function(e){
			$(this).ColorPickerSetColor(this.value);
			 if (e.keyCode == 27) { $(this).ColorPickerHide(); }
		});
		
		$('#TB_ajaxContent').css('height','90%');

});
</script>