<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/
if(!class_exists('PluginOptionsPanel')):

class PluginOptionsPanel {
	var $id;
	var $capability;
	var $page_title;
	var $menu_text;
	var $option_menu_parent;
	var $notification;
	var $description_rowspan=0;
	var $version = '1.0.5';
	var $rangeinput;
	var $colorpicker;
	function PluginOptionsPanel($args=array()){
		$defaults = array(
			'id'					=> 'plugin-options-panel',
			'capability'			=> 'manage_pop',
			'options_varname'		=> 'pop_options',
			'menu_id'				=> 'pop-options',
			'page_title'			=> __('Plugin Options Panel','pop'),
			'menu_text'				=> __('Plugin Options Panel','pop'),
			'option_menu_parent'	=> 'options-general.php',
			'notification'			=> (object)array(
				'plugin_version'=> '1.0.0',
				'plugin_code' 	=> 'POP',
				'message'		=> __('Plugin update %s is available! <a href=\"%s\">Please update now</a>','pop')
			),
			'theme'			=> false,
			'stylesheet'	=> 'pop-options',
			'rangeinput'	=> 'tools-rangeinput',
			'colorpicker'	=> 'jquery-colorpicker'/*,
			'rangeinput'	=> false,
			'colorpicker'	=> false*/
		);
		foreach($defaults as $property => $default){
			$this->$property = isset($args[$property])?$args[$property]:$default;
		}
		//---	
		add_action('admin_menu',array(&$this,'admin_menu'));
		
		add_action('init',array(&$this,'pop_handle_save'));
		
		add_action('wp_ajax_notifications-'.$this->id, array(&$this,'pop_notifications'));
	}
	
	function pop_handle_save(){
		if(!isset($_POST[$this->id.'_options']))
			return;
		
		if(!current_user_can($this->capability))
			return;
			
		$options = explode(',', stripslashes( $_POST[ 'page_'.$this->id ] ));
		if ( $options ) {
			$existing_options = $this->get_options();
			foreach ( $options as $option ) {
				$option = trim($option);
				$value = null;
				if ( isset($_POST[$option]) )
					$value = $_POST[$option];
				if ( !is_array($value) ) $value = trim($value);
				$value = stripslashes_deep($value);
				$existing_options[$option]=$value;
			}		
			update_option($this->options_varname, $existing_options);
		}

		do_action('pop_handle_save',$this);
		//------------------------------	
		$goback = add_query_arg( 'updated', 'true', wp_get_referer() );
		if(isset($_REQUEST['tabs_selected_tab'])&&$_REQUEST['tabs_selected_tab']!=''){
			$goback = add_query_arg( 'tabs_selected_tab', $_REQUEST['tabs_selected_tab'], $goback );
		}
		wp_redirect( $goback );
	}
	
	
	function admin_menu(){
		$page_id = add_submenu_page( $this->option_menu_parent,$this->page_title ,$this->menu_text,$this->capability,$this->menu_id,array(&$this,'body'));
		add_action( 'admin_head-'. $page_id, array(&$this,'head') );
	}
	//admin_enqueue_scripts
	function head(){
		wp_print_styles( $this->stylesheet );
		wp_print_scripts( 'jquery-ui-tabs' );
		if($this->rangeinput)wp_print_scripts( $this->rangeinput );
		if($this->colorpicker){
			wp_print_scripts( $this->colorpicker );	
			wp_print_styles( $this->colorpicker );
		}
?>
  <script>
 jQuery(document).ready(function($){ 
 	$("#pop-options-cont .option-title").unbind('click').click(function(e){
		$(this).toggleClass('open').next().slideToggle();
	});
 
 	$("#btn-open-all").click(function(){
		$('#pop-options-cont .option-title').addClass('open').next().slideDown();
	});
	
	if(location.hash){
		var sel = '#'+location.hash.slice(1)+'.toggle-option .option-title';
		if($(sel).length>0){
			$(sel).click();
		}
	}
		
	var args = {
		action: 'notifications-<?php echo $this->id ?>'
	};	
	$.post(ajaxurl,args,function(data){
		if(data.R=='OK'){
			$('#notifications-<?php echo $this->id ?>').html(data.MSG);	
		}
	},'json');		
<?php if($this->rangeinput): ?>	
	/* range input fields */
	$(':range').rangeinput();
<?php endif; ?>
<?php if($this->colorpicker):?>	 
	function get_contrast_font_color(hex,color1,color2){
		function giveHex(s){
			s=s.toUpperCase();
			return parseInt(s,16);
		}	
		if(hex.length<6||hex.length>6){
			return color1;
		}
		r=giveHex(hex.slice(0,2));
		g=giveHex(hex.slice(2,4));
		b=giveHex(hex.slice(4,6));
		if ( ((r*299 + g*587 + b*114) / 1000) > 125) {
		    return color1;
		} else {
		    return color2;
		}
	}

	$('.pop-colorpicker').ColorPicker({
		onSubmit: function(hsb, hex, rgb, el) {
			$(el).val(hex).change();
			$(el).ColorPickerHide();
			//$(el).css('background-color', '#'+hex );
			//$(el).css('color', '#'+get_contrast_font_color(hex,'000000','ffffff') );
		},
		onBeforeShow: function () {
			$(this).ColorPickerSetColor(this.value);
		}
	}).bind('keyup', function(e){
		$(this).ColorPickerSetColor(this.value);
		 if (e.keyCode == 27) { $(this).ColorPickerHide(); }
	}).bind('change',function(e){
		$(this).css('background-color', '#'+this.value );
		$(this).css('color', '#'+get_contrast_font_color(this.value,'000000','ffffff') );	
	}).change();
<?php endif;?>	
 });	
 
 </script>
<?php

		add_action( 'admin_notices', array(&$this,'pop_update_notice') );	
		do_action('pop_admin_head_'.$this->id);		
	}
	
	function pop_update_notice(){
		echo sprintf("<div id=\"notifications-%s\"></div>",$this->id);
	}
	
	function pop_notifications(){
		$url = sprintf('http://plugins.righthere.com/?rh_latest_version=%s&site_url=%s&license_key=%s',$this->notification->plugin_code,urlencode(site_url('/')),urlencode($this->get_license_key()));
		if($this->theme){$url.="&theme=1";}
		
		if(!class_exists('righthere_service'))require_once 'class.righthere_service.php';
		$rh = new righthere_service();
		$r = $rh->rh_service($url);		
		
		if(false!==$r){
			if(!$this->theme){
				if(is_object($r)&&property_exists($r,'version')){
					if($r->version>$this->notification->plugin_version){
						$message = sprintf("<div class=\"updated fade\"><p><strong>%s</strong></p></div>",$this->notification->message);
						$response = (object)array(
							'R'		=> 'OK',
							'MSG'	=> sprintf($message,$r->version,$r->url)
						);
						die(json_encode($response));
					}else{
						die(json_encode((object)array('R'=>'ERR','MSG'=>'Plugin is latest version.')));
					}
				}else{
					die(json_encode((object)array('R'=>'ERR','MSG'=>'Invalid response format.')));
				}			
			}
		}
		die(json_encode((object)array('R'=>'ERR','MSG'=>'Notification service is not available.')));
	}
	
	function body(){
		$options = apply_filters('pop-options_'.$this->id,array());
		$existing_options = $this->get_options();
		$existing_options = is_array($existing_options)?$existing_options:array();			
?>
<div class="wrap">
<?php screen_icon('options-general'); ?>
<h2><?Php echo $this->menu_text?></h2>
<?php echo isset($_REQUEST['updated'])?'<div class="updated">'.__('Options updated.','pop').'</div>':'' ?>
<div id="sys_msg"></div>
<div id="pop-options-cont">
<?php		
		if(count($options)>0){
			$save_fields = array();
			echo "<form method=\"post\" action=\"\">";
			echo '<input type="hidden" name="'.$this->id.'_options" value="1" />';
			echo '<input type="hidden" id="tabs_selected_tab" name="tabs_selected_tab" value="" />';
			wp_nonce_field($this->id);
			foreach($options as $tab){
				$tab->theme_option = property_exists($tab,'theme_option')? $tab->theme_option : true;
				$tab->plugin_option = property_exists($tab,'plugin_option')? $tab->plugin_option : true;
				if($this->theme&&!$tab->theme_option){
					continue;
				}else if(!$tab->plugin_option){
					continue;
				}
				echo sprintf("<div id=\"%s\" class=\"toggle-option\">",$tab->id);
				echo sprintf("<h3 class=\"option-title\">%s<span>%s</span></h3>", $tab->label, @$tab->right_label );
				echo "<div class=\"option-content\">";
				if(count($tab->options)>0){
					foreach($tab->options as $i => $o){			
						$o->theme_option = property_exists($o,'theme_option')? $o->theme_option : true;
						$o->plugin_option = property_exists($o,'plugin_option')? $o->plugin_option : true;					
						if($this->theme&&!$o->theme_option){
							continue;
						}else if(!$o->plugin_option){
							continue;
						}
						//----------
						$method = "_".$o->type;
						if(!method_exists($this,$method))
							continue;
							
						if(true===@$o->load_option){
							$el_id = $this->get_el_id($tab,$i,$o);		
							$el_id = str_replace("[]","",$el_id);
							$o->value = isset($existing_options[$el_id])?$existing_options[$el_id]:(property_exists($o,'default')?$o->default:'');
						}
						
						echo trim(@$o->description)==''?'':"<div class=\"pt-clear\"></div><div class=\"description\">".@$o->description."</div>";
						
						if($o->type=='callback'){
							$o->existing_options = $existing_options;
							echo $this->$method($tab,$i,$o,$save_fields);
						}else{	
							$class = property_exists($o,'ptclass')?$o->ptclass:'';
							echo sprintf("<div class=\"pt-option pt-option-%s %s\">",$o->type,$class);
							if(in_array($o->type,array('checkbox'))){
								echo sprintf("%s",$this->$method($tab,$i,$o,$save_fields));	
								echo sprintf("<span class=\"pt-checkbox-label\">%s</span>",$o->label);
							}else{
								if(property_exists($o,'label')&&!in_array($o->type,array('label','subtitle','hr','submit','range','textarea'))){
									echo sprintf("<span class=\"pt-label pt-type-%s\">%s</span>",$o->type,$o->label);	
								}
								echo sprintf("%s",$this->$method($tab,$i,$o,$save_fields));						
							}
							//------------
							//echo "<div class=\"pt-clear\"></div>";
							echo "</div>";//close pt-option						
						}
					}
				}
				echo "</div>";//close option-content
				echo "</div>";//close toggle-option
			}
			
			echo "<div class=\"bottom-controls\">";
			echo "<input id=\"btn-open-all\" class=\"button-secondary\" type=\"button\" value=\"".__('Open all','pop')."\" />";
			echo $this->_submit(null,null,(object)array('class'=>'button-primary','label'=>__('Save all','pop')));
			echo "</div>";
			
			echo '<input type="hidden" name="action" value="update" />';
			echo sprintf('<input type="hidden" name="page_%s" value="%s" />',$this->id,$this->get_save_fields($save_fields));
			echo "</form>";
		}
?>
</div>
</div>
<?php	
		echo "<div class=\"pop-version\"><i>Options panel version $this->version</i></div>";
	}
	
	function get_save_fields($save_fields){
		if(is_array($save_fields)&&count($save_fields)>0){
			$tmp = array();
			foreach($save_fields as $field){
				if(in_array($field,$tmp))continue;
				$tmp[]=str_replace("[]",'',$field);
			}
			return implode(",",$tmp);
		}else{
			return '';
		}
	}
	
	function get_options(){
		$options = get_option($this->options_varname);
		return is_array($options)?$options:array();
	}
	
	function get_option($name){
		$options = $this->get_options();
		return isset($options[$name])?$options[$name]:'';
	}
	
	function get_license_key(){
		$licenses = $this->get_option('license_keys');
		if(is_array($licenses)&&count($licenses)>0){
			foreach($licenses as $license){
				if(in_array($license->item_type,array('plugin','theme'))){
					return $license->license_key;
				}
			}
		}
		$license_key = $this->get_option('license_key');
		if(trim($license_key)!=''){
			return $license_key;
		}
		return '';
	}
	
	function get_el_id($tab,$i,$o){
		return property_exists($o,'name')?$o->name:$o->id;
		//return sprintf("%s_%s",$this->id,$o->id);
	}
	
	function get_el_properties($tab,$i,$o){
		$elp = array();
		if(count(@$o->el_properties)>0){
			foreach($o->el_properties as $prop => $val){
				$elp[] = sprintf("%s=\"%s\"",$prop,$val);
			}
		}
		return implode(' ',$elp);
	}
	
	function _subtitle($tab,$i,$o,&$save_fields){
		return sprintf("<h3 class=\"option-panel-subtitle\">%s</h3>",@$o->label);
	}
	
	function _description($tab,$i,$o){
		return trim(@$o->description)==''?'':"<div class=\"pt-clear\"></div><div class=\"description\">".@$o->description."</div>";
	}
	
	function _hr(){
		return sprintf("<hr class=\"hr\" />");
	}
	
	function _textarea($tab,$i,$o,&$save_fields){
		$id = $this->get_el_id($tab,$i,$o);
		$properties = $this->get_el_properties($tab,$i,$o);
		
		if(true===$o->save_option){
			$save_fields[]=$id;	
		}
		$str = '';
		if(!@$o->nolabel){
			$str.=sprintf("<div class=\"slider-label\">%s</div>",@$o->label);
		}
		$str .= sprintf("<textarea id=\"%s\" name=\"%s\" %s>%s</textarea>",$id,$id,$properties,$o->value);
		return $str;
	}
	
	function _label($tab,$i,$o,&$save_fields){
		$label = property_exists($o,'value')?$o->value:(property_exists($o,'label')?$o->label:false);
		return $label?sprintf('<label>%s</label>',$label ):'';
	}
	
	function _colorpicker($tab,$i,$o,&$save_fields){
		$o->el_properties = property_exists($o,'el_properties')&&is_array($o->el_properties)?$o->el_properties:array();
		$o->el_properties['class'] = isset($o->el_properties['class'])?$o->el_properties['class'].' pop-colorpicker':'pop-colorpicker';
		return $this->_text($tab,$i,$o,$save_fields);
	}
	function _text($tab,$i,$o,&$save_fields){
		$id = $this->get_el_id($tab,$i,$o);
		$str = sprintf('<input type="text" id="%s" name="%s" value="%s" %s />',$id,$id,$o->value, $this->get_el_properties($tab, $i, $o) );		
		if(true===$o->save_option){
			$save_fields[]=$id;	
		}
		
		return $str;
	}
	
	function _range($tab,$i,$o,&$save_fields){
		$id = $this->get_el_id($tab,$i,$o);
		foreach(array('step'=>1,'min'=>0,'max'=>1,'nolabel'=>false) as $field => $default){
			$o->$field = property_exists($o,$field)?$o->$field:$default;
		}
		$str = '';
		if(!$o->nolabel){
			$str.=sprintf("<div class=\"slider-label\">%s</div>",@$o->label);
		}
		$str .= sprintf('<input type="range" id="%s" name="%s" value="%s"  min="%s" max="%s" step="%s" %s />',$id,$id,$o->value , $o->min, $o->max, $o->step, $this->get_el_properties($tab, $i, $o) );		
		if(true===$o->save_option){
			$save_fields[]=$id;	
		}
		
		return $str;
	}
	
	function hidden($tab,$i,$o,&$save_fields){
		$id = $this->get_el_id($tab,$i,$o);
		$str = sprintf('<input type="hidden" id="%s" name="%s" value="%s" %s />',$id,$id,$o->value, $this->get_el_properties($tab, $i, $o) );		
		if(true===$o->save_option){
			$save_fields[]=$id;	
		}
		return $str;
	}
	
	function _checkbox($tab,$i,$o,&$save_fields){
		$id = $this->get_el_id($tab,$i,$o);
		$name = property_exists($o,'name')?$o->name:$id;
		$o->option_value=(property_exists($o,'option_value'))?$o->option_value:1;
		//$checked = $o->value==$o->option_value?'checked':'';
		if(is_array($o->value)){
			$checked = in_array($o->option_value,$o->value)?'checked="checked"':'';
		}else{	
			$checked = $o->value==$o->option_value?'checked="checked"':'';
		}
		$str = sprintf('<input type="checkbox" id="%s" name="%s" value="%s" %s %s />',$id,$name,$o->option_value, $this->get_el_properties($tab, $i, $o) , $checked);
				
		if(true===$o->save_option){
			$save_fields[]=$name;	
		}
		
		return $str;
	}
	
	function _select($tab,$i,$o,&$save_fields){
		$id = $this->get_el_id($tab,$i,$o);
		$name = property_exists($o,'name')?$o->name:$id;
		$str = sprintf('<select id="%s" name="%s"  %s />',$id,$name, $this->get_el_properties($tab, $i, $o) );
		if(!empty($o->options)){
			foreach($o->options as $value => $label){
				$selected = $o->value==$value?'selected="selected"':'';
				$str.=sprintf("<option %s value=\"%s\">%s</option>", $selected, $value, $label);
			}
		}
		$str.="</select>";
		
		if(true===$o->save_option){
			$save_fields[]=$name;	
		}
		
		return $str;
	}
	
	function _yesno($tab,$i,$o,&$save_fields){
		$o->options = array(
			'1'=>__('Yes','pop'),
			'0'=>__('No','pop')
		);
		return $this->_radio($tab,$i,$o,$save_fields);
	}
	
	function _radio($tab,$i,$o,&$save_fields){
		$str = '';
		if(!empty($o->options)){
			$k=0;
			foreach($o->options as $value => $label){
				$id = $this->get_el_id($tab,$i,$o).'_'.($k++);
				$name = $this->get_el_id($tab,$i,$o);
				$selected = $o->value==$value?'checked':'';
				$str.=sprintf("<input %s id=\"%s\" name=\"%s\" type=\"radio\" %s value=\"%s\" />&nbsp;<label>%s</label>&nbsp;&nbsp;", $this->get_el_properties($tab, $i, $o),$id, $name, $selected, $value, $label);
			}
			if(true===$o->save_option){
				$save_fields[]=$name;	
			}
		}
		return $str;
	}

	function _clear($tab,$i,$o){
		return "<div class=\"pt-clear\"></div>";
	}
	
	function _submit($tab,$i,$o){
		return sprintf("<input class=\"%s\" type=\"submit\" name=\"theme_options_submit\" value=\"%s\" />",$o->class, $o->label);
	}
	
	function _button($tab,$i,$o){
		$id = $this->get_el_id($tab,$i,$o);
		return sprintf("<input class=\"%s\" type=\"button\" id=\"%s\" name=\"%s\" value=\"%s\" %s />",$o->class, $id, $id, $o->label, $this->get_el_properties($tab,$i,$o) );
	}	
	
	function _callback($tab,$i,$o,&$save_fields){
		if(is_callable($o->callback)){
			return call_user_func($o->callback,$tab,$i,$o,$save_fields);
		}
		return '';
	}
}
endif;
?>