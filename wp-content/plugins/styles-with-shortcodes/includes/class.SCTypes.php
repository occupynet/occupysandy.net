<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class SCTypes {
	var $uid = 0;
	var $scripts = array();
	var $styles = array();
	var $allowed_types = array(
		'text',
		'textarea',
		'rtf',
		'colorpicker',
		'dropdown',
		'slider',
		'data',
		'label',
		'hidden',
		'ui_theme',
		'checkbox'
	);
	
	function SCTypes(){
	
	}
	
	function get_fields($shortcode, &$fields, $q=1000){
		echo "<input type=\"hidden\" class=\"mce-item mce-scopentag\" value=\"$shortcode\" />";
		if(is_array($fields)&&count($fields)==0){
			echo "<input type=\"hidden\" class=\"mce-item mce-scclose\" value=\"$shortcode\" />";
			return;
		}
		$r = 0;
		$last_field = (object)array('content'=>0);
		while( ($r++<$q) && ($field=array_shift($fields)) ){		
			echo $this->get_field($field,$fields,$last_field);
			$last_field = $field;
		}
		if( isset($last_field->content) && $last_field->content!=1 && $last_field->type!='data' ){
			echo "<input type=\"hidden\" class=\"mce-item mce-scclose\" value=\"$shortcode\" />";
		}
		echo "<input type=\"hidden\" class=\"mce-item mce-scclosetag\" value=\"$shortcode\" />";
	}
	
	function get_field($field,&$fields,$last_field){
		if(!in_array($field->type,$this->allowed_types)){return '';}
		$method = $field->type;
		return $this->$method($field,$fields,$last_field);	
	}

	function get_classes($field){
		$tmp = array();
		if( isset($field->content) && $field->content==1 ){
			$tmp[]='mce-content';
		}else{
			$tmp[]='mce-property';
		}
		if( isset($field->classes) && trim($field->classes)!=''){
			$tmp[]=$field->classes;
		}
		if(property_exists($field,'jsfunc') && ''!=trim($field->jsfunc)){
			$tmp[]='parse-with-rel';
		}
		return implode(' ',$tmp);
	}
	
	function data($field,&$fields,$last_field){
		if( isset($last_field->content) && $last_field->content!=1 ){
			echo "<input type=\"hidden\" class=\"mce-item mce-scclose\" value=\"".@$shortcode."\" />";
		}	
?>
<div class="mce-data">
	<h3 class="mce-data-label"><?php echo $field->label; ?></h3>
	<div class="mce-data-rows">
		<div class="mce-data-row">
			<div class="mce-data-row-content">
				<span class="mce-row-delete">&nbsp;</span>
				<?php $this->get_fields($field->shortcode,$fields,$field->field_number)?>
			</div>
		</div>
	</div>
	
	<div class="mce-data-control">
		<input type="button" class="button-secondary add-mce-data-row" value="<?php echo (isset($field->button_label)&&trim($field->button_label)!='')?$field->button_label:'Add data'?>" />
	</div>
</div>
<?php
	}
	
	function get_jsfunc($field){
		if(property_exists($field,'jsfunc') && ''!=trim($field->jsfunc)){
			return str_replace('"','\"',str_replace('{val}',"_val", sprintf("_val = %s ;",$field->jsfunc)));
		}
		return '';
	}
	
	function checkbox($field){
?>
		<div class="fieldset">
			<?php if(!empty($field->description)) :?>
			<div class="description"><?php echo $field->description?></div>
			<?php endif;?>		
			<label class="css-mce-label"><?php echo $field->label?></label>
			<div class="css-mce-input">
				<?php echo sprintf("<input type=\"checkbox\" id=\"mce-checkbox-%s\" name=\"sws_%s\" class=\"mce-item %s\" rel=\"%s\" value=\"%s\" %s>",
					$this->uid++,
					$field->name,
					$this->get_classes($field),
					$this->get_jsfunc($field),
					$field->checkbox_value,
					$field->default==$field->checkbox_value?'checked="checked"':''
					); ?>
			</div>
			<div class="clearer"></div>
		</div>
<?php
	}
	
	function text($field){
		$field->id = sprintf( "mce-text-%s", $this->uid++ );
?>
		<div class="fieldset">
			<?php if(!empty($field->description)) :?>
			<div class="description"><?php echo $field->description?></div>
			<?php endif;?>
			<label class="css-mce-label"><?php echo $field->label; ?></label>
			<div class="css-mce-input">
				<?php echo sprintf("<input id=\"%s\" name=\"sws_%s\" type=\"text\" class=\"mce-item %s\" value=\"%s\" rel=\"%s\" />",$field->id,$field->name,$this->get_classes($field),$field->default,$this->get_jsfunc($field)); ?>
				<?php $this->get_helper($field) ?>
			</div>
			<div class="clearer"></div>
		</div>
<?php
	}
	
	function hidden($field){
		echo sprintf("<input id=\"mce-text-%s\" name=\"sws_%s\" type=\"hidden\" class=\"mce-item %s\" value=\"%s\" />",$this->uid++,$field->name,$this->get_classes($field),$field->default); 
	}
	
	function label($field){
?>
		<div class="fieldset">
			<label class="css-mce-field-label <?php echo $this->get_classes($field)?>"><?php echo $field->label; ?></label>
			<div class="clearer"></div>
		</div>
<?php
	}
	
	function textarea($field){
?>
		<div class="fieldset">
			<?php if(!empty($field->description)) :?>
			<div class="description"><?php echo $field->description?></div>
			<?php endif;?>		
			<label class="css-mce-label"><?php echo $field->label?></label>
			<div class="css-mce-input">
				<?php echo sprintf("<textarea id=\"mce-textarea-%s\" name=\"sws_%s\" class=\"mce-item %s\" rel=\"%s\" >%s</textarea>",$this->uid++,$field->name,$this->get_classes($field),$this->get_jsfunc($field),$field->default); ?>
			</div>
			<div class="clearer"></div>
		</div>
<?php
	}
	
	function rtf($field){
		//this is just for future release
		$uid = $this->uid++;
		
?>
		<div class="fieldset">
			<?php if(!empty($field->description)) :?>
			<div class="description"><?php echo $field->description?></div>
			<?php endif;?>		
			<label class="css-mce-label"><?php echo $field->label?></label>
			<div class="css-mce-input">
				<?php echo sprintf("<textarea id=\"mce-textarea-%s\" name=\"sws_%s\" class=\"mce-item %s\" rel=\"%s\">%s</textarea>",$uid,$field->name,$this->get_classes($field),$this->get_jsfunc($field),$field->default); ?>
			</div>
			<div class="clearer"></div>
		</div>
<?php
	}
	
	
	function colorpicker($field){
?>
		<div class="fieldset">
			<?php if(!empty($field->description)) :?>
			<div class="description"><?php echo $field->description?></div>
			<?php endif;?>		
			<label class="css-mce-label"><?php echo $field->label; ?></label>
			<div class="css-mce-input">
				<?php echo sprintf("<input id=\"mce-colorpicker-%s\" name=\"sws_%s\" type=\"text\" class=\"mce-item sws-colorpicker %s\" value=\"%s\" maxlength=\"6\" size=\"6\" />",$this->uid++,$field->name,$this->get_classes($field),$field->default);?>
			</div>
			<div class="clearer"></div>
		</div>
<?php
	}
	
	function ui_theme($field){
		global $sws_plugin;
		$field->options = '';
		foreach($sws_plugin->sws_styles as $s){
			if($s->ui_theme){
				$field->options.=sprintf("%s|%s\n",$s->id,$s->label);
			}
		}
		if($this->have_class('admin-load-ui-theme',$field)){
			$field->extra.=' OnChange="javascript:load_ui_theme(this,\''.WPCSS_URL.'api/wp_print_styles.php\');"';
		}
		$this->dropdown($field);
	}
	
	function have_class($class,$field){
		if( property_exists($field,'classes') && trim($field->classes)!=''){
			$classes = explode(' ',$field->classes);
			return in_array($class,$classes);
		}	
		return false;
	}
		
	function dropdown($field){
		if(property_exists($field,'dropdown_callback')&&trim($field->dropdown_callback)!=''){
			return $this->dropdown_from_predifined_source($field);
		}
		$options = explode("\n", (isset($field->options)?$field->options:'|no options') );
		
		$str = sprintf("<select id=\"mce-dropdown-%s\" name=\"sws_%s\" class=\"mce-item %s\" %s>",$this->uid++,$field->name,$this->get_classes($field),(property_exists($field,'extra')?$field->extra:''));
		if(is_array($options)&&count($options)>0){
			foreach($options as $row){
				$pair = explode('|',$row);
				if(count($pair)>=2){
					//$str.= sprintf("<option %s value=\"%s\">%s</option>",(isset($pair[2])?$pair[2]:''),$pair[0],$pair[1]);
					$str.= sprintf("<option %s value=\"%s\">%s</option>",(isset($pair[2])?$pair[2]:($pair[0]==$field->default?'selected':'')),$pair[0],$pair[1]);
				}
			}
		}
		$str.="</select>";
?>
		<div class="fieldset">
			<?php if(!empty($field->description)) :?>
			<div class="description"><?php echo $field->description?></div>
			<?php endif;?>		
			<label class="css-mce-label"><?php echo $field->label?></label>
			<div class="css-mce-input">
				<?php echo $str?>
			</div>
			<div class="clearer"></div>
		</div>
<?php
	}
	
	function dropdown_from_predifined_source($field){
		$options_callback = $field->dropdown_callback;
		if(!is_callable($options_callback)){
			return $this->text($field);
		}
		
		$options = $options_callback($field);
		
		$str = sprintf("<select id=\"mce-dropdown-%s\" name=\"sws_%s\" class=\"mce-item %s\" %s>",$this->uid++,$field->name,$this->get_classes($field),(property_exists($field,'extra')?$field->extra:''));
		if(is_array($options)&&count($options)>0){
			foreach($options as $value => $label){
				$str.= sprintf("<option %s value=\"%s\">%s</option>",($value==$field->default?'selected':''),$value,$label);
			}
		}
		$str.="</select>";
?>
		<div class="fieldset">
			<?php if(!empty($field->description)) :?>
			<div class="description"><?php echo $field->description?></div>
			<?php endif;?>		
			<label class="css-mce-label"><?php echo $field->label?></label>
			<div class="css-mce-input">
				<?php echo $str?>
			</div>
			<div class="clearer"></div>
		</div>
<?php	
	}
	
	function slider($field){
		$step = property_exists($field,'step')?$field->step:0;
?>
		<div class="fieldset">
			<?php if(!empty($field->description)) :?>
			<div class="description"><?php echo $field->description?></div>
			<?php endif;?>		
			<label class="css-mce-label"><?php echo $field->label ?></label>
			<div class="css-mce-input">
				<?php echo sprintf("<input name=\"sws_%s\" class=\"mce-item sws-rangeinput %s\" type=\"range\" min=\"%s\" max=\"%s\" value=\"%s\" step=\"%s\" />",$field->name,$this->get_classes($field),((isset($field->min)?$field->min:0)),((isset($field->max)?$field->max:100)),$field->default,$step);?>
			</div>
			<div class="clearer"></div>
		</div>
<?php
	}
	
	function scripts(){
		echo "<script type=\"text/javascript\">".implode(" ",$this->scripts)."</script>";
	}
	
	function get_helper($field){
		if($this->have_class('helper-ui-icon', $field )){
			$this->helper_ui_icon($field);
		}
	}
	
	function helper_ui_icon($field){
		$ui_icons = array('ui-icon-carat-1-n','ui-icon-carat-1-ne','ui-icon-carat-1-e','ui-icon-carat-1-se','ui-icon-carat-1-s','ui-icon-carat-1-sw','ui-icon-carat-1-w','ui-icon-carat-1-nw','ui-icon-carat-2-n-s','ui-icon-carat-2-e-w','ui-icon-triangle-1-n','ui-icon-triangle-1-ne','ui-icon-triangle-1-e','ui-icon-triangle-1-se','ui-icon-triangle-1-s','ui-icon-triangle-1-sw','ui-icon-triangle-1-w','ui-icon-triangle-1-nw','ui-icon-triangle-2-n-s','ui-icon-triangle-2-e-w','ui-icon-arrow-1-n','ui-icon-arrow-1-ne','ui-icon-arrow-1-e','ui-icon-arrow-1-se','ui-icon-arrow-1-s','ui-icon-arrow-1-sw','ui-icon-arrow-1-w','ui-icon-arrow-1-nw','ui-icon-arrow-2-n-s','ui-icon-arrow-2-ne-sw','ui-icon-arrow-2-e-w','ui-icon-arrow-2-se-nw','ui-icon-arrowstop-1-n','ui-icon-arrowstop-1-e','ui-icon-arrowstop-1-s','ui-icon-arrowstop-1-w','ui-icon-arrowthick-1-n','ui-icon-arrowthick-1-ne','ui-icon-arrowthick-1-e','ui-icon-arrowthick-1-se','ui-icon-arrowthick-1-s','ui-icon-arrowthick-1-sw','ui-icon-arrowthick-1-w','ui-icon-arrowthick-1-nw','ui-icon-arrowthick-2-n-s','ui-icon-arrowthick-2-ne-sw','ui-icon-arrowthick-2-e-w','ui-icon-arrowthick-2-se-nw','ui-icon-arrowthickstop-1-n','ui-icon-arrowthickstop-1-e','ui-icon-arrowthickstop-1-s','ui-icon-arrowthickstop-1-w','ui-icon-arrowreturnthick-1-w','ui-icon-arrowreturnthick-1-n','ui-icon-arrowreturnthick-1-e','ui-icon-arrowreturnthick-1-s','ui-icon-arrowreturn-1-w','ui-icon-arrowreturn-1-n','ui-icon-arrowreturn-1-e','ui-icon-arrowreturn-1-s','ui-icon-arrowrefresh-1-w','ui-icon-arrowrefresh-1-n','ui-icon-arrowrefresh-1-e','ui-icon-arrowrefresh-1-s','ui-icon-arrow-4','ui-icon-arrow-4-diag','ui-icon-extlink','ui-icon-newwin','ui-icon-refresh','ui-icon-shuffle','ui-icon-transfer-e-w','ui-icon-transferthick-e-w','ui-icon-folder-collapsed','ui-icon-folder-open','ui-icon-document','ui-icon-document-b','ui-icon-note','ui-icon-mail-closed','ui-icon-mail-open','ui-icon-suitcase','ui-icon-comment','ui-icon-person','ui-icon-print','ui-icon-trash','ui-icon-locked','ui-icon-unlocked','ui-icon-bookmark','ui-icon-tag','ui-icon-home','ui-icon-flag','ui-icon-calendar','ui-icon-cart','ui-icon-pencil','ui-icon-clock','ui-icon-disk','ui-icon-calculator','ui-icon-zoomin','ui-icon-zoomout','ui-icon-search','ui-icon-wrench','ui-icon-gear','ui-icon-heart','ui-icon-star','ui-icon-link','ui-icon-cancel','ui-icon-plus','ui-icon-plusthick','ui-icon-minus','ui-icon-minusthick','ui-icon-close','ui-icon-closethick','ui-icon-key','ui-icon-lightbulb','ui-icon-scissors','ui-icon-clipboard','ui-icon-copy','ui-icon-contact','ui-icon-image','ui-icon-video','ui-icon-script','ui-icon-alert','ui-icon-info','ui-icon-notice','ui-icon-help','ui-icon-check','ui-icon-bullet','ui-icon-radio-off','ui-icon-radio-on','ui-icon-pin-w','ui-icon-pin-s','ui-icon-play','ui-icon-pause','ui-icon-seek-next','ui-icon-seek-prev','ui-icon-seek-end','ui-icon-seek-start','ui-icon-seek-first','ui-icon-stop','ui-icon-eject','ui-icon-volume-off','ui-icon-volume-on','ui-icon-power','ui-icon-signal-diag','ui-icon-signal','ui-icon-battery-0','ui-icon-battery-1','ui-icon-battery-2','ui-icon-battery-3','ui-icon-circle-plus','ui-icon-circle-minus','ui-icon-circle-close','ui-icon-circle-triangle-e','ui-icon-circle-triangle-s','ui-icon-circle-triangle-w','ui-icon-circle-triangle-n','ui-icon-circle-arrow-e','ui-icon-circle-arrow-s','ui-icon-circle-arrow-w','ui-icon-circle-arrow-n','ui-icon-circle-zoomin','ui-icon-circle-zoomout','ui-icon-circle-check','ui-icon-circlesmall-plus','ui-icon-circlesmall-minus','ui-icon-circlesmall-close','ui-icon-squaresmall-plus','ui-icon-squaresmall-minus','ui-icon-squaresmall-close','ui-icon-grip-dotted-vertical','ui-icon-grip-dotted-horizontal','ui-icon-grip-solid-vertical','ui-icon-grip-solid-horizontal','ui-icon-gripsmall-diagonal-se','ui-icon-grip-diagonal-se');
	
?>
<div class="helper-ui-icon">
	<ul class="ui-widget ui-helper-clearfix">
<?php foreach($ui_icons as $icon): ?>
	<li class="ui-state-default ui-corner-all" rel="#<?php echo $field->id?>" title="<?php echo $icon ?>">
		<span style="float: left; margin-right: 0.3em;" class="ui-icon-helper ui-icon <?php echo $icon ?>" rel="<?php echo $icon ?>"></span>
	</li>
<?php endforeach; ?>
	</ul>
</div>
<?php		
	}
}
?>