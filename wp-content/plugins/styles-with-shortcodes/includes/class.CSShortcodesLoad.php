<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class CSShortcodesLoad {
	var $tag_to_id = array();
	var $footer = array();
	var $uid = 0;
	var $shortcode_tags = array();
	var $added_footer = array();
	function CSShortcodesLoad(){
		$this->add_shortcodes();
		//--------
		add_shortcode('sws_debug',array(&$this,'sws_debug'));
		add_filter('the_content',array(&$this,'do_shortcode'),9,1);
	}
	
	function sws_debug($atts,$content=null,$code=""){
		global $wp_filter;
		$out = "<textarea cols=40 rows=5>".$content."</textarea>";
		$out .= "<pre>".print_r($wp_filter['the_content'],true)."</pre>";
		$out .= "<pre>".print_r($wp_filter['wp_footer'],true)."</pre>";
		return $out;
	}
	
	function add_shortcodes(){
		global $wpdb;
		
		$sql = "SELECT P.ID, P.post_title";
		$sql.= ", COALESCE((SELECT M.meta_value FROM `{$wpdb->postmeta}` M WHERE M.post_id=P.ID AND M.meta_key=\"sc_shortcode\" LIMIT 1),'') as sc_shortcode";
		$sql.= ", COALESCE((SELECT M.meta_value FROM `{$wpdb->postmeta}` M WHERE M.post_id=P.ID AND M.meta_key=\"sc_shortcodes\" LIMIT 1),'') as sc_shortcodes";
		$sql.= ", COALESCE((SELECT M.meta_value FROM `{$wpdb->postmeta}` M WHERE M.post_id=P.ID AND M.meta_key=\"sc_priority_shortcode\" LIMIT 1),'') as sc_priority_shortcode";
		$sql.= " FROM `{$wpdb->posts}` P";
		$sql.= " WHERE post_type='csshortcode' AND post_status='publish' ORDER BY menu_order ASC";
		if($wpdb->query($sql)&&$wpdb->num_rows>0){
			foreach($wpdb->last_result as $row){
				$tag = $row->sc_shortcode;
				//$priority_shortcode = intval(get_post_meta($row->ID,'sc_priority_shortcode',true));
				$priority_shortcode = intval($row->sc_priority_shortcode); 
				if(trim($tag)!=''){
					$this->tag_to_id[$tag]=$row->ID;
					if($priority_shortcode){
						$this->add_shortcode($tag, array(&$this,'shortcode_handler'));
					}else{
						add_shortcode($tag, array(&$this,'shortcode_handler'));
					}
				}		
				//---
				//$tags = get_post_meta($row->ID,'sc_shortcodes',true);
				$tags = $row->sc_shortcodes;
				$tags = ''==trim($tags)?false:unserialize($tags);
				if(is_array($tags)&&count($tags)>0){
					foreach($tags as $tag){
						if(trim($tag)!=''){
							$this->tag_to_id[$tag]=$row->ID;
							if($priority_shortcode){
								$this->add_shortcode($tag, array(&$this,'sub_shortcode_handler'));
							}else{
								add_shortcode($tag, array(&$this,'sub_shortcode_handler'));
							}
						}
					}
				}
			}
		}
	}
	
	//this is directly modified from shortcodes.php
	//wpautop is interfering with some shortcodes so we need to do some of our shortcodes before autop
	function add_shortcode($tag, $func) {
		if ( is_callable($func) )
			$this->shortcode_tags[$tag] = $func;
	}	
	
	function get_shortcode_regex() {
		$shortcode_tags =& $this->shortcode_tags;
		$tagnames = array_keys($shortcode_tags);
		$tagregexp = join( '|', array_map('preg_quote', $tagnames) );
	
		// WARNING! Do not change this regex without changing do_shortcode_tag() and strip_shortcodes()
		return '(.?)\[('.$tagregexp.')\b(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?(.?)';
	}
	
	function do_shortcode($content) {
		$shortcode_tags =& $this->shortcode_tags;
	
		if (empty($shortcode_tags) || !is_array($shortcode_tags))
			return $content;
	
		$pattern = $this->get_shortcode_regex();
		return preg_replace_callback('/'.$pattern.'/s', array(&$this,'do_shortcode_tag'), $content);
	}
	
	function do_shortcode_tag( $m ) {
		$shortcode_tags =& $this->shortcode_tags;
	
		// allow [[foo]] syntax for escaping a tag
		if ( $m[1] == '[' && $m[6] == ']' ) {
			return substr($m[0], 1, -1);
		}
	
		$tag = $m[2];
		$attr = shortcode_parse_atts( $m[3] );
	
		if ( isset( $m[5] ) ) {
			// enclosing tag - extra parameter
			return $m[1] . call_user_func( $shortcode_tags[$tag], $attr, $m[5], $tag ) . $m[6];
		} else {
			// self-closing tag
			return $m[1] . call_user_func( $shortcode_tags[$tag], $attr, NULL,  $tag ) . $m[6];
		}
	}	
	//------------------------------------
	function _replace($str,$arr){
		foreach($arr as $key => $repl){
			$str = str_replace($key,$repl,$str);
		}
		return $str;
	}
	
	function sub_shortcode_handler($atts,$content=null,$code=""){
		$sc_id = $this->tag_to_id[$code];	
		$sc_fields = get_post_meta($sc_id,'sc_fields',true);
		$arr = array();
		$sc_template = '';
		if(is_array($sc_fields)&&count($sc_fields)>0){
			while($field=array_shift($sc_fields)){
				if(property_exists($field,'shortcode') && $field->shortcode==$code && in_array($field->type,array('data'))){
					$sc_template = $field->shortcode_template;
					$i=0;
					while( ($i++<$field->field_number) && ($f=array_shift($sc_fields)) ){
						$varname = strtolower($f->name);
						$arr[$varname]=$f->default;
					}
				}
			}
		}
		
		extract(shortcode_atts($arr, $atts));
		
		$content = trim($content)==''?$content:do_shortcode($content);
		
		$replace = array(
			'{id}'=>$sc_id,
			'{shortcode}'=>$code,
			'{pluginurl}'=>WPCSS_URL,
			'{themeurl}'=>get_bloginfo('stylesheet_directory').'/',
			'{siteurl}'=>site_url(),
			'{uid}'=> $this->uid++
		);	
		
		$fields = get_post_meta($sc_id,'sc_fields',true);
		if(is_array($fields)&&count($fields)>0){
			foreach($fields as $f){
				$varname = strtolower($f->name);
				if(@$f->content==1){
					$replace["{".$f->name."}"]=$content;
				}else{
					$replace["{".$f->name."}"]=$this->get_field_value($f,@$$varname,$code);//@$$varname;
				}
			}
		}	
			
		if(''==trim($sc_template)){
			return '';
		}
	
		$out = do_shortcode($sc_template);
		$out = $this->_replace($out,$replace);		
		$out = str_replace('timthumb.php','thumbnail.php',$out);		
		return $out;
	}

	function shortcode_handler($atts,$content=null,$code=""){
		$sc_id = $this->tag_to_id[$code];
		$fields = get_post_meta($sc_id,'sc_fields',true);
		$arr = array();
		if(is_array($fields)&&count($fields)>0){
			foreach($fields as $f){
				$varname = strtolower($f->name);
				$arr[$varname]=$f->default;
				if('ui_theme'==$f->type){
					$ui_theme_var = $f->name;
				}
			}
		}

		extract(shortcode_atts($arr, $atts));
		
		$replace = array(
			'{id}'=>$sc_id,
			'{shortcode}'=>$code,
			'{content}'=>$content,
			'{pluginurl}'=>WPCSS_URL,
			'{themeurl}'=>get_bloginfo('stylesheet_directory').'/',
			'{siteurl}'=>site_url(),
			'{uid}'=> $this->uid++
		);
				
		if(is_array($fields)&&count($fields)>0){
			foreach($fields as $f){
				$varname = strtolower($f->name);//wp shortcode api lowercases attributes. :s
				$replace["{".$f->name."}"]=$this->get_field_value($f,@$$varname,$code);
			}
		}		
	
		$out = get_post_meta($sc_id,'sc_template',true);
		$out = $this->php_handler($sc_id,$atts,$content,$code,$arr,$out);
		if(''==trim($out)){
			return '';
		}
		
		$out = $this->_replace($out,$replace);
		$out = ''==$out?'':do_shortcode($out);

		$this->added_footer[$code] = isset($this->added_footer[$code])?$this->added_footer[$code]:false;
		if(!$this->added_footer[$code]){
			$this->added_footer[$code]=true;
			add_action('wp_footer',array(&$this,'footer'));
			if(is_admin()){
				add_action('admin_footer',array(&$this,'footer'));
			}
			//-----------------------------------------------------------------------------------
			$sc_styles = get_post_meta($sc_id,'sc_styles',true);
			$sc_styles = is_array($sc_styles)&&count($sc_styles)>0?$sc_styles:array();

			if(isset($$ui_theme_var)){
				$sc_styles[]=$$ui_theme_var;
			}

			if(count($sc_styles)>0){
				ob_start();
				foreach($sc_styles as $style){
					wp_print_styles($style);
				}
				$this->footer['styles'.$code] = ob_get_contents();
				ob_end_clean();
			}	
			//-----------------------------------------------------------------------------------
			$sc_scripts = get_post_meta($sc_id,'sc_scripts',true);
			$sc_scripts = is_array($sc_scripts)?$sc_scripts:array();
			//--- provided to support shortcodes from the first release until the user updates the shortcodes.
			if(in_array($code,array('sws_overlay','sws_scrollable_basic','sws_scrollable_preview','sws_overlay_apple','sws_gmap3','sws_tooltip','fstip','accordion','uitabs','sws_code','sws_nivo_zoom','sws_nivo_slider','sws_toggle1','sws_toggle2','sws_toggle3'))){
				array_unshift($sc_scripts,'jquery-ui');
				array_unshift($sc_scripts,'sws-jquery-tools');
			}
			if(in_array($code,array('sws_ui_tabs'))){
				array_unshift($sc_scripts,'jquery-ui');
				array_unshift($sc_scripts,'sws-jquery-ui-tabs');
			}			
			//----
			if(count($sc_scripts)>0){				
				ob_start();
				foreach($sc_scripts as $script){
					wp_print_scripts($script);
				}
				$this->footer['scripts'.$code] = ob_get_contents();
				ob_end_clean();
			}	
			//-----------------------------------------------------------------------------------
			$css = get_post_meta($sc_id,'sc_css',true);
			$this->footer['css'.$code]= $this->_replace($css,$replace);
			
			
			$js = get_post_meta($sc_id,'sc_js',true);
			$this->footer['js'.$code]= $this->_replace($js,$replace);
		}
		$out = str_replace('timthumb.php','thumbnail.php',$out);
		return $out;						
	}
	
	function php_handler($sc_id,$atts,$content,$code,$field_defaults,$shortcode_template){
		$php = get_post_meta($sc_id,'sc_php',true);
		if(trim($php)=='')return $shortcode_template;
		extract(shortcode_atts($field_defaults, $atts));
		$output='';
		try{
			ob_start();
			eval($php);
			$output = ob_get_contents();
			ob_end_clean();			
		}catch(Exception $e){
			
		}
		$output = str_replace('timthumb.php','thumbnail.php',$output);
		return $output;
	}
	
	function footer(){		
		$footer = implode("\n",$this->footer);
		$footer = str_replace("<style>\r\n</style>","",$footer);
		$footer = str_replace("<script>\r\n</script>","",$footer);
		echo $footer;
	}	
	
	function get_field_value($field,$value,$shortcode){
		if(!property_exists($field,'urlencode')&& false!==strpos($shortcode,'sws_picture_frame')){
			$value = urlencode($value);
		}
		return $value;
	}
}

new CSShortcodesLoad();
?>