<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class sws_admin_ajax {
	function sws_admin_ajax($method=false){
		if(false!==$method){
			$this->call_ajax_method($method);		
		}
	}

	function call_ajax_method($method){
		if(method_exists($this,$method)){
			$this->$method();
		}else{
			$this->send_error_die('Unknown ajax method.');
		}
	}
	
	function send_error_die($msg){
		die(json_encode(array('R'=>'ERR','MSG'=>$msg)));
	}
	
	function sws_list_fields(){
		$post_id = intval($_REQUEST['ID']);
		$this->verify_access($post_id);
		$fields = get_post_meta($post_id,'sc_fields',true);
		$shortcode = get_post_meta($post_id,'sc_shortcode',true);
		if(is_array($fields) && count($fields)>0){			
		?>
		<div class="css-fields-cont">
		<table class="widefat">
		<thead>
			<tr>
				<th>&nbsp;</th>
				<th>Label</th>
				<th>Property name</th>
				<th>Default value</th>
				<th>Input type</th>
				<th>Shortcode template tag</th>
			</tr>
		</thead>
		<tbody>
		<?php $i=0;foreach($fields as $name => $field): ?>
			<tr>
				<td>
					<span OnClick="javascript:edit_css_field('<?php echo $name?>')" class="css-field-edit">&nbsp;</span>
					<span OnClick="javascript:delete_css_field('<?php echo $name?>')" class="css-field-delete">&nbsp;</span>
					<?php if($i++>0): ?>
					<span OnClick="javascript:moveup_css_field('<?php echo $name?>')" class="css-field-moveup" title="Move up">&nbsp;</span>
					<?php endif; ?>
				</td>
				<td><?php echo $field->label?></td>
				<td><?php echo $field->name?></td>
				<td><?php echo $field->default?></td>
				<td><?php echo ($field->type=='data')?$field->type.'('.$field->field_number.' fields)':$field->type?></td>
				<td>{<?php echo $field->name?>}</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
		</table>
		</div>
		<?php	
		}else{
			echo "There are no configured fields.";
		}	
	}
	
	function sws_moveup_field(){
		$post_id = intval($_REQUEST['ID']);
		if($post_id==0){$this->send_error_die('Post ID is not valid.');}
		//-----------
		$this->verify_nonce_and_access($post_id);
		//-------------
		$name = $_REQUEST['name'];
		if(trim($name)==''){
			$this->send_error_die('Property name is not valid');
		}
		
		$fields = get_post_meta($post_id,'sc_fields',true);
		$fields = is_array($fields)?$fields:array();
		if(!isset($fields[$name])){
			$this->send_error_die('Field not found.');
		}
		
		$last_field = false;
		if(count($fields)>0){
			$tmp = array();
			foreach($fields as $iname => $field){
				if(false!==$last_field){
					if($iname==$name){
						$tmp[$name]=$field;
						continue;
					}
					$tmp[$last_field->name]=$last_field;
				}
				$last_field = $field;
			}
			$tmp[$last_field->name]=$last_field;
			update_post_meta($post_id,'sc_fields',$tmp);
		}
		
		$ret = array(
			'R'=>'OK',
			'MSG'=>''
		);
		die(json_encode($ret));
	}
	
	function sws_remove_field(){		
		$post_id = intval($_REQUEST['ID']);
		if($post_id==0){$this->send_error_die('Post ID is not valid.');}
		//-----------
		$this->verify_nonce_and_access($post_id);
		//-------------
		$name = $_REQUEST['name'];
		if(trim($name)==''){
			$this->send_error_die('Property name is not valid');
		}
		
		$fields = get_post_meta($post_id,'sc_fields',true);
		$fields = is_array($fields)?$fields:array();
		
		if(isset($fields[$name])){
			unset($fields[$name]);
		}
		
		update_post_meta($post_id,'sc_fields',$fields);
		
		$ret = array(
			'R'=>'OK',
			'MSG'=>''
		);
		die(json_encode($ret));	
	}
	
	function sws_add_field(){
		$post_id = intval($_REQUEST['ID']);
		if($post_id==0){$this->send_error_die('Post ID is not valid.');}
		//-----------
		$this->verify_nonce_and_access($post_id);
		//-------------
		$name = $_REQUEST['name'];
		if(trim($name)==''){
			$this->send_error_die('Property name is not valid');
		}
		
		$new_field = array();
		foreach(array('name','label','default','classes','jsfunc','description','content','type','dropdown_callback','options','min','max','step','shortcode','shortcode_template','field_number','button_label','checkbox_value') as $field){
			$new_field[$field] = $_POST[$field];
		}
		
		$new_field['name']=str_replace(' ','_',$new_field['name']);
		
		$fields = get_post_meta($post_id,'sc_fields',true);
		$fields = is_array($fields)?$fields:array();
		
		$fields[$name] = (object)$new_field;
		
		update_post_meta($post_id,'sc_fields',$fields);
		//-- additional shortcodes----------------------------------------
		$shortcodes = array();
		foreach($fields as $field){
			if(trim(@$field->shortcode)!=''){
				$shortcodes[]=$field->shortcode;
			}
		}
		update_post_meta($post_id,'sc_shortcodes',$shortcodes);
		//----------------------------------------------------------------
		
		$ret = array(
			'R'=>'OK',
			'MSG'=>''
		);
		die(json_encode($ret));
	}
	
	function sws_get_field(){		
		$post_id = intval($_REQUEST['ID']);
		
		if($post_id==0){
			$this->send_error_die('Post ID is not valid.');
		}
		
		$this->verify_nonce_and_access($post_id);
		
		$name = $_REQUEST['name'];
		if(trim($name)==''){
			$this->send_error_die('Property name is not valid');
		}
		
		$fields = get_post_meta($post_id,'sc_fields',true);
		$fields = is_array($fields)?$fields:array();
		if(!isset($fields[$name])){
			$this->send_error_die('Field not found.');
		}
		
		$response = $fields[$name];
		$response->default_value = $response->default;
		$response->content = intval(@$response->content);
		
		$ret = array(
			'R'=>'OK',
			'MSG'=>'',
			'DATA'=> $response
		);
		die(json_encode($ret));
	}
	
	function sws_sc_export(){
		$post_id = intval($_REQUEST['ID']);
		if($post_id==0){$this->send_error_die('Post ID is not valid.');}
		//-----------
		$this->verify_nonce_and_access($post_id);
		//-------------
		$sco = new ImportExport();
		$data = $sco->get_shortcode_from_post_id($post_id,$error,true);
		if(false===$data){
			$this->send_error_die('Error generating export data: '.$error);
		}
		$ret = array(
			'R'=>'OK',
			'MSG'=>'',
			'DATA'=>$data
		);
		die(json_encode($ret));
	}
	
	function sws_sc_import(){		
		$post_id = intval($_REQUEST['ID']);
		if($post_id==0){$this->send_error_die('Post ID is not valid.');}
		//-----------
		$this->verify_nonce_and_access($post_id);
		//-------------
		$code = $_REQUEST['code'];
		$import_terms = isset($_REQUEST['import_terms'])?$_REQUEST['import_terms']:false;
		
		$sco = new ImportExport();
		$res = $sco->restore_from_string($post_id,$code,$error,$import_terms);
		if(false===$res){
			$this->send_error_die($error);
		}
		$ret = array(
			'R'=>'OK',
			'MSG'=>'',
			'URL'=> html_entity_decode(get_edit_post_link( $post_id ))
		);
		die(json_encode($ret));
	}
	
	function sws_sc_moreinfo(){		
		$post_id = intval($_REQUEST['ID']);
		if($post_id==0){$this->send_error_die('Post ID is not valid.');}
		//-----------
		$this->verify_nonce_and_access($post_id);
		//-------------
		global $wpdb;
		$code = $_REQUEST['code'];
		$sco = new ImportExport();
		$obj = $sco->string_to_obj($code);
		if(false===$obj){$this->send_error_die('Error reading code. (2)');}
		if(false=== $sco->check_shortcode_obj($obj,$error)){$this->send_error_die($error.print_r($obj,true));}
	
		$categories = array();
		if(isset($obj->sc_terms) && is_array($obj->sc_terms) && count($obj->sc_terms)>0){
			foreach($obj->sc_terms as $t){
				$categories[] = $t->name;
			}
		}
		
		$data = (object)array();
		$data->name = $obj->post_title;
		$data->shortcode = $obj->sc_shortcode;
		$data->category = $categories;
		$data->bundle = $obj->sc_bundle;
		$data->info = (property_exists($obj,'sc_info'))?$obj->sc_info:array('author'=>'','url'=>'');
		$data->warnings = array();
		
		$sql = "SELECT P.ID FROM `$wpdb->postmeta` M INNER JOIN `$wpdb->posts` P ON P.ID = M.post_id WHERE P.ID!={$post_id} AND M.meta_key = \"sc_shortcode\" AND M.meta_value=\"{$obj->sc_shortcode}\" AND P.post_status IN ('publish', 'draft')";
		$data->duplicate_posts = $wpdb->get_col($sql,0);
		$data->duplicate_links = array();
		if(is_array($data->duplicate_posts)&&count($data->duplicate_posts)>0){
			foreach($data->duplicate_posts as $duplicate_id){
				$data->duplicate_links[] = sprintf("<a href=\"%s\">%s</a>",html_entity_decode(get_edit_post_link( $duplicate_id )),$duplicate_id);
			}
		}
		//---check that a required script is present
		if(property_exists($obj,'sc_scripts') && is_array($obj->sc_scripts) && count($obj->sc_scripts)>0 ){
			global $wp_scripts;
			foreach($obj->sc_scripts as $handle){
				if(!array_key_exists($handle,$wp_scripts->registered)){
					$data->warnings[]=sprintf("Shortcode requires a registered javascript library (%s), but it is not active in the system.",$handle);
				}
			}
		}
		if(property_exists($obj,'sc_styles') && is_array($obj->sc_styles) && count($obj->sc_styles)>0 ){
			global $wp_styles;
			foreach($obj->sc_styles as $handle){
				if(!array_key_exists($handle,$wp_styles->registered)){
					$data->warnings[]=sprintf("Shortcode requires a registered stylesheet (%s), but it is not active in the system.",$handle);
				}
			}
		}
		
		$ret = array(
			'R'=>'OK',
			'MSG'=>'',
			'DATA'=>$data
		);
		die(json_encode($ret));	
	}
	
	function verify_nonce_and_access($post_id){
		$this->verify_nonce();
		$this->verify_access($post_id);		
	}
	
	function verify_nonce(){
		if ( !wp_verify_nonce( $_REQUEST['nonce'], 'csshortcode-css-nonce' )) {
			$this->send_error_die('Settings error, no access.');
		}
	}
	
	function verify_access($post_id){
		if ( !current_user_can( 'edit_post', $post_id ) ){
			$this->send_error_die('No access.');
		} 
	}	
}
?>