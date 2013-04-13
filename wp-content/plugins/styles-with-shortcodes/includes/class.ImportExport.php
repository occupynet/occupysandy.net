<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class ImportExport {
	
	function ImportExport(){
	
	}
	
	function get_shortcode_from_post_id($post_id,&$error,$serialized=true){
		global $wpdb;
		
		$sco = (object)array();
		$sco->post_title = $wpdb->get_var("SELECT post_title FROM `{$wpdb->posts}` WHERE ID=$post_id AND post_type=\"csshortcode\" AND post_status=\"publish\"",0,0);
		foreach(array('sc_shortcode','sc_shortcodes','sc_template','sc_php','sc_css','sc_js','sc_fields','sc_bundle','sc_preview','sc_priority_shortcode','sc_scripts','sc_styles','sc_info') as $meta_name){
			$sco->$meta_name = get_post_meta($post_id,$meta_name,true);
		}
		$sco->sc_fields = is_array($sco->sc_fields)?$sco->sc_fields:array();

		$groups = get_the_terms($post_id, 'csscategory');
		$tmp = array();
		if(is_array($groups)&&count($groups)>0){
			foreach($groups as $group){
				$tmp[]= (object)array(
					'name'=>$group->name,
					'slug'=>$group->slug,
					'description'=>$group->description
				); 
			}
		}
		$sco->sc_terms = $tmp;
		
		if(!$this->check_shortcode_obj($sco, $error)){
			return false;
		}
		
		return $serialized?base64_encode(serialize($sco)):$sco;
	}
	
	function string_to_obj($str){
		return unserialize(base64_decode($str));
	}
	
	function restore_from_string($post_id,$str,&$error,$import_terms=false){
		global $wpdb;
		
		$obj = unserialize(base64_decode($str));
		if(!$this->check_shortcode_obj($obj, $error)){
			return false;
		}
		
		$response = (object)array(
			'new_post'=>false
		);
		
		$status = $wpdb->get_var("SELECT post_status FROM `$wpdb->posts` WHERE ID=$post_id",0,0);
		if('auto-draft'==$status){
			$response->new_post = true;
			$status = 'draft';
		}
		
		$my_post = array();
		$my_post['ID'] = $post_id;
		$my_post['post_title'] = $obj->post_title;
		$my_post['post_type']='csshortcode';
		$my_post['post_status']=$status;
		
		$post_id = wp_update_post( $my_post );
		if(0==$post_id){
			$error = "Error updating shortcode record.";
			return false;
		}
		
		foreach(array('sc_shortcode','sc_shortcodes','sc_template','sc_php','sc_css','sc_js','sc_fields','sc_bundle','sc_preview','sc_priority_shortcode','sc_scripts','sc_styles','sc_info') as $field){
			update_post_meta($post_id,$field,$obj->$field);		
		}
		if($import_terms){
			if(isset($obj->sc_terms)&&is_array($obj->sc_terms)&&count($obj->sc_terms)>0){
				foreach($obj->sc_terms as $term){
					$existing_term = get_term_by('slug',$term->slug,'csscategory');
					if(false===$existing_term){
						$new_term = wp_insert_term($term->name, 'csscategory', array('description'=> $term->description, 'slug' => $term->slug, 'parent'=> 0  ));
						if(!is_wp_error($new_term)){
							wp_set_object_terms($post_id,array(intval($new_term['term_id'])),'csscategory',true);
						}
					}else{
						wp_set_object_terms($post_id,array(intval($existing_term->term_id)),'csscategory',true);				
					}
				}
			}
		}	
		return $response;
	}
	
	function check_shortcode_obj($o,&$error){
		$error = '';
		foreach(array('post_title','sc_shortcode','sc_template','sc_css','sc_js','sc_fields') as $field){
			if(!isset($o->$field)){
				$error = sprintf( __("Field not set (%s)",'wpcss') ,$field);
				return false;
			}
		}
		
		if(!is_array($o->sc_fields)){
			$error = __("Shortcode fields property is not an array.","wpcss");
			return false;
		}
		
		foreach(array('post_title','sc_shortcode','sc_template') as $field){
			if(trim($o->$field)==''){
				$error = sprintf( __("Field is empty (%s)",'wpcss') ,$field);
				return false;	
			}
		}
		return true;
	}
	
	function shortcode_exist($shortcode){
		global $wpdb;
		$sql = "SELECT P.ID FROM `$wpdb->postmeta` M INNER JOIN `$wpdb->posts` P ON P.ID = M.post_id WHERE M.meta_key = \"sc_shortcode\" AND M.meta_value=\"$shortcode\" AND P.post_status IN ('publish', 'draft') LIMIT 1";
		$ID = $wpdb->get_var($sql,0,0);
		return $ID>0?true:false;
	}
	//-- bundle
	function import_bundle($code,&$error,$import_terms=true){	
		$bundle = $this->string_to_obj(trim($code));
		if(count($bundle->shortcodes)>0){
			foreach($bundle->shortcodes as $obj){
				if($this->shortcode_exist($obj->sc_shortcode)){
					continue;//skip existing shortcodes.
				}
				$res = $this->add_shortcode($obj,$error,$import_terms);
			}
		}
	}

	function add_shortcode($obj,&$error,$import_terms=true){
		global $wpdb,$userdata;
		
		if(!$this->check_shortcode_obj($obj, $error)){
			return false;
		}
				
		$my_post = array();
		$my_post['post_title'] 	= $obj->post_title;
		$my_post['post_type']	= 'csshortcode';
		$my_post['post_status']	= 'publish';
		$my_post['post_author']	= $userdata->ID;
		
		$this->remove_actions();
		$post_id = wp_insert_post( $my_post , false );
		if(0==$post_id){
			$error = "Error adding shortcode record.";
			return false;
		}
		
		foreach(array('sc_shortcode','sc_shortcodes','sc_template','sc_php','sc_css','sc_js','sc_fields','sc_bundle','sc_preview','sc_priority_shortcode','sc_scripts','sc_styles','sc_info') as $field){
			if(property_exists($obj,$field)){
				update_post_meta($post_id,$field,$obj->$field);		
			}
		}
		if($import_terms){
			if(isset($obj->sc_terms)&&is_array($obj->sc_terms)&&count($obj->sc_terms)>0){
				foreach($obj->sc_terms as $term){
					$existing_term = get_term_by('slug',$term->slug,'csscategory');
					if(false===$existing_term){
						$new_term = wp_insert_term($term->name, 'csscategory', array('description'=> $term->description, 'slug' => $term->slug, 'parent'=> 0  ));
						if(!is_wp_error($new_term)){
							wp_set_object_terms($post_id,array(intval($new_term['term_id'])),'csscategory',true);
						}
					}else{
						wp_set_object_terms($post_id,array(intval($existing_term->term_id)),'csscategory',true);				
					}
				}
			}
		}	
		return $post_id;
	}	

	function update_shortcode($obj,&$error,$import_terms=true){
		global $wpdb,$userdata;
		
		if(!$this->check_shortcode_obj($obj, $error)){
			return false;
		}
				
		$my_post = array();
		$my_post['ID'] 	= $obj->post_ID;
		$my_post['post_title'] 	= $obj->post_title;
		$my_post['post_type']	= 'csshortcode';
		$my_post['post_status']	= 'publish';
		$my_post['post_author']	= $userdata->ID;
		
		$this->remove_actions();
		$post_id = wp_update_post( $my_post , false );
		if(0==$post_id){
			$error = "Error adding shortcode record.";
			return false;
		}
		
		foreach(array('sc_shortcode','sc_shortcodes','sc_template','sc_php','sc_css','sc_js','sc_fields','sc_bundle','sc_preview','sc_priority_shortcode','sc_scripts','sc_styles','sc_info') as $field){
			if(property_exists($obj,$field)){
				update_post_meta($post_id,$field,$obj->$field);		
			}
		}
		if($import_terms){
			if(isset($obj->sc_terms)&&is_array($obj->sc_terms)&&count($obj->sc_terms)>0){
				foreach($obj->sc_terms as $term){
					$existing_term = get_term_by('slug',$term->slug,'csscategory');
					if(false===$existing_term){
						$new_term = wp_insert_term($term->name, 'csscategory', array('description'=> $term->description, 'slug' => $term->slug, 'parent'=> 0  ));
						if(!is_wp_error($new_term)){
							wp_set_object_terms($post_id,array(intval($new_term['term_id'])),'csscategory',true);
						}
					}else{
						wp_set_object_terms($post_id,array(intval($existing_term->term_id)),'csscategory',true);				
					}
				}
			}
		}	
		return $post_id;
	}	
	
	function remove_actions(){
		remove_all_actions('save_post');
		remove_all_actions('new_to_publish');
	}
	
	function restore_bundle_from_name($bundle_name,&$error,$import_terms=true){
		global $sws_plugin;
		if(!isset($sws_plugin->bundles[$bundle_name])){
			$error = 'Bundle is registered with the SWS plugin, or the plugin addon is deactivated.';
			return false;
		}else{
			$b =& $sws_plugin->bundles[$bundle_name];
		}
		
		global $bundle;
		$bundle=false;
		@include($b->path);
		if(false===$bundle){
			$error = sprintf("Bundle file not found (%s)",$b->path);
			return false;
		}else{
			return $this->restore_bundle($bundle,$error,$import_terms);
		}
	}
	
	function restore_bundle($bundle,&$error,$import_terms=true){	
		$bundle = $this->string_to_obj(trim($bundle));
		if(count($bundle->shortcodes)>0){
			$res = false;
			foreach($bundle->shortcodes as $obj){
				$post_id = $this->get_post_id_from_shortcode($obj->sc_shortcode);
				if(false===$post_id){
					$res = $this->add_shortcode($obj,$error,$import_terms);
				}else{
					$obj->post_ID = $post_id;
					$res = $this->update_shortcode($obj,$error,$import_terms);
				}
				//for now, if one fails return:
				if(false===$res){
					return false;
				}
			}
			return false===$res?false:true;
		}else{
			$error = "Bundle does not contain shortcodes.";
			return false;
		}
	}	

	function get_post_id_from_shortcode($shortcode){
		global $wpdb;
		//althought there can be many posts with the same shortcode plugin will only update the first one
		$sql = "SELECT P.ID FROM `$wpdb->postmeta` M INNER JOIN `$wpdb->posts` P ON P.ID = M.post_id WHERE M.meta_key = \"sc_shortcode\" AND M.meta_value=\"$shortcode\" AND P.post_status IN ('publish', 'draft') ORDER BY P.ID ASC LIMIT 1";
		$ID = $wpdb->get_var($sql,0,0);
		return $ID>0?$ID:false;
	}	
}

?>