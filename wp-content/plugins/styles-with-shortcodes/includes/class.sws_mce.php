<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class sws_mce {
	function sws_mce(){
		add_filter( 'tiny_mce_version', array(&$this,'refresh_mce'));
		add_action('init', array(&$this,'myplugin_addbuttons'));
		add_action('wp_ajax_sws_code_update_meta', array(&$this,'sws_code_update_meta'));
		add_action('save_post', array(&$this,'save_post') );	
		
		add_filter('the_content',array(&$this,'the_content'),0260000000,1);
		//----------	
	}
		
	function the_content($content){
		global $post;
		if(!is_object($post)||!property_exists($post,'ID'))return $content;
		$sws_code = get_post_meta($post->ID,'sws_code',true);
		$sws_code = is_array($sws_code)?$sws_code:array();	
		if(preg_match_all("/<!--swscode(.*?)-->/",$content,$arr)){
			foreach($arr[0] as $i => $repl){
				$j=intval(trim($arr[1][$i]));
				$code = isset($sws_code[$j])?$sws_code[$j]:'';
				$content = str_replace($repl,$code,$content);
			}
		}
	
		return $content;
	}
	
	function save_post($post_id){
		//cleanup unused meta
		global $wpdb;
		$post_id = intval($post_id);
		$sws_code = get_post_meta($post_id,'sws_code',true);
		$sws_code = is_array($sws_code)?$sws_code:array();	
		if(count($sws_code)>0){
			$content = $wpdb->get_var("SELECT post_content FROM $wpdb->posts WHERE ID=$post_id",0,0);
			if(preg_match_all("/<!--swscode(.*?)-->/",$content,$arr)){
				$tmp = array();
				foreach($arr[1] as $index){
					$tmp[]=trim($index);
				}
				
				$new_sws_code = array();
				foreach($sws_code as $i => $code){
					if(in_array($i,$tmp)){
						$new_sws_code[$i]=$code;
					}
				}
				update_post_meta($post_id,'sws_code',$new_sws_code);
			}			
		}
	}
	
	function sws_code_update_meta(){
		$post_id = isset($_REQUEST['post_id'])?$_REQUEST['post_id']:false;
		$code = isset($_REQUEST['code'])?$_REQUEST['code']:false;
		
		if(false===$post_id||false===$code){
			die(json_encode(array("R"=>"ERR",'MSG'=>'No access')));
		}
		
		if ( current_user_can( 'edit_post', $post_id ) ){
			$sws_code = get_post_meta($post_id,'sws_code',true);
			$sws_code = is_array($sws_code)?$sws_code:array();
			$a = array_keys($sws_code);
			if(count($a)>0){
				$code_id = intval(max($a)) + 1;
			}else{
				$code_id = 0;
			}
			$sws_code[$code_id]=$code;
			
			update_post_meta($post_id,'sws_code',$sws_code);		
			die(json_encode(array("R"=>"OK",'MSG'=>'','ID'=>$code_id)));			
		}

		die(json_encode(array("R"=>"ERR",'MSG'=>'No access')));
	}
	
	function myplugin_addbuttons(){	
		if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
			return;
		if ( get_user_option('rich_editing') == 'true') {
	    	add_filter("mce_external_plugins", array(&$this,"add_myplugin_tinymce_plugin") );
	    	//add_filter('mce_buttons', array(&$this,'register_myplugin_button') );
		 	//add_filter('mce_css', array(&$this,'plugin_mce_css') );
		}	
	}
	
	function add_myplugin_tinymce_plugin($plugin_array) {
	   $plugin_array['sws_code'] = WPCSS_URL.'tinymce/sws_code.js?v=1';
	   return $plugin_array;
	}
	
	function refresh_mce($ver) {
	  	return $ver++;
	}
}
new sws_mce();
?>