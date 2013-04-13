<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class CSShortcodes {
	var $capability = 'manage_sws';
	var $post;
	var $show_ui = true;
	function CSShortcodes($arg=array()){
		$this->show_ui = isset($arg['show_ui'])?$arg['show_ui']:true;
		//-----
		add_action('init',array(&$this,'init_taxonomy'));//we need it defined outside for editors.
		add_filter('widget_display_callback',array(&$this,'widget_display_callback'));	
		if(current_user_can('manage_sws')||current_user_can($this->capability)){
			add_action('init',array(&$this,'init_post_type'));
			add_action('admin_menu', array(&$this, 'post_meta_box') );
			add_action('save_post', array(&$this,'save_post'), 1, 1 );	
			add_action('admin_init',array(&$this,'admin_init'));	
			add_action('restrict_manage_posts',array(&$this,'restrict_manage_posts'));
			add_filter( 'pre_get_posts', array(&$this,'meta_filter_posts') );			
		}
		add_action('admin_head-edit.php', array(&$this,'admin_head'));
		add_action('wp_ajax_sws_notifications', array(&$this,'sws_notifications'));
		//ajax
		add_action('wp_ajax_sws_get_field', array(&$this,'wp_ajax_sws_admin'));
		add_action('wp_ajax_sws_list_fields', array(&$this,'wp_ajax_sws_admin'));
		add_action('wp_ajax_sws_moveup_field', array(&$this,'wp_ajax_sws_admin'));
		add_action('wp_ajax_sws_remove_field', array(&$this,'wp_ajax_sws_admin'));
		add_action('wp_ajax_sws_add_field', array(&$this,'wp_ajax_sws_admin'));
		add_action('wp_ajax_sws_sc_export', array(&$this,'wp_ajax_sws_admin'));
		add_action('wp_ajax_sws_sc_import', array(&$this,'wp_ajax_sws_admin'));
		add_action('wp_ajax_sws_sc_moreinfo', array(&$this,'wp_ajax_sws_admin'));
		add_action('admin_head', array(&$this,'icon32_style'));	
	}
	
	function icon32_style(){
?>
<style>
.icon32-posts-csshortcode{
	background: url("<?php echo WPCSS_URL.'images/sws32.png'?>") no-repeat scroll 0 1px transparent !important;
}
</style>
<?php	
	}
	
	function wp_ajax_sws_admin(){
		require_once("class.sws_admin_ajax.php");
		$ajax = new sws_admin_ajax($_REQUEST['action']);
		die();
	}
	
	function widget_display_callback($str){
		global $sws_plugin;
		if(isset($sws_plugin->options['disable_sws_in_widget'])&&$sws_plugin->options['disable_sws_in_widget']==1){
			return $str;
		}else{
			if(is_array($str)&&count($str)>0){
				foreach($str as $i => $v){
					if(is_string($v)){
						$str[$i]=do_shortcode($v);
					}
				}
				return $str;
			}else if(is_string($str)){
				return do_shortcode($str);
			}
		}
		return $str;
	}
	function restrict_manage_posts($arg){
		if(isset($_REQUEST['post_type'])&&$_REQUEST['post_type']=='csshortcode'){
?>
<div class="sws_list_filter"><label class="sws_filter_label">Category</label>:&nbsp;<?php wp_dropdown_categories( array('selected'=>(isset($_REQUEST['f_sws_category'])?$_REQUEST['f_sws_category']:0), 'name'=>'f_sws_category','id'=>'f_sws_category', 'show_option_all'=> sprintf('--%s--',__('show all','wpcss')),'taxonomy'=>'csscategory') );?></div>
<div class="sws_list_filter"><label class="sws_filter_label">Shortcode</label>:&nbsp;<input type="text" id="f_sws_shortcode" name="f_sws_shortcode" value="" /></div>
<div class="sws_list_filter"><label class="sws_filter_label">Bundle</label>:&nbsp;<input type="text" id="f_sws_bundle" name="f_sws_bundle" value="<?php echo isset($_REQUEST['f_sws_bundle'])?$_REQUEST['f_sws_bundle']:''?>" /></div>
<script>jQuery('select[name=m]').hide();</script>
<?php		
		}
//		if(isset($_REQUEST['f_sws_category'])){
//			print_r($_REQUEST['f_sws_category']);
//		}
	}

	function meta_filter_posts($query){
		if(is_admin()&&$query->query['post_type']=='csshortcode'){
			if(isset($_REQUEST['f_sws_shortcode'])&&trim($_REQUEST['f_sws_shortcode'])!=''){
				$query->set( 'meta_key', 	'sc_shortcode' );
				$query->set( 'meta_value', 	$_REQUEST['f_sws_shortcode']);			
			}
			if(isset($_REQUEST['f_sws_bundle'])&&trim($_REQUEST['f_sws_bundle'])!=''){
				$query->set( 'meta_key', 	'sc_bundle' );
				$query->set( 'meta_value', 	$_REQUEST['f_sws_bundle'] );			
			}
			if(isset($_REQUEST['f_sws_category'])&&$_REQUEST['f_sws_category']>0){
				$term = get_term($_REQUEST['f_sws_category'],'csscategory');
				$query->set( 'csscategory' , $term->slug );
				$query->is_tax = true ;	// Doesnt seems that edit.php follows a standard wp_query get_posts							
			}
		}
	}
	
	function admin_init(){
		global $wp_version;
		if($wp_version<3.3){
			add_filter( 'manage_edit-csshortcode_columns', array(&$this,'admin_columns')  );
			add_action('manage_posts_custom_column', array(&$this,'custom_column'),10,2);					
		}else{
			add_filter( 'manage_csshortcode_posts_columns', array(&$this,'admin_columns')  );
			add_action('manage_csshortcode_posts_custom_column', array(&$this,'custom_column'),10,2);				
		}
	}

	function sws_notifications(){
		$options = get_option('sws_options');
		$options = is_array($options)?$options:array();
		$url = sprintf('http://plugins.righthere.com/?rh_latest_version=SWS&site_url=%s&license_key=%s',urlencode(site_url('/')),urlencode(@$options['license_key']));
		//$url = sprintf('http://dev.lawley.com/?rh_latest_version=SWS&site_url=%s&license_key=%s',urlencode(site_url('/')),urlencode(@$options['license_key']));
		if(defined('WPCSS_THEME')){$url.="&theme=1";}

		$handle=@fopen($url,'r');
		if($handle){
			$contents = '';
			while (!feof($handle)) {
			  $contents .= fread($handle, 8192);
			}
			fclose($handle);	
			if(!defined('WPCSS_THEME')){
				$r = json_decode($contents);
				if(is_object($r)&&property_exists($r,'version')){
					if($r->version>WPCSS){
						$response = (object)array(
							'R'		=> 'OK',
							'MSG'	=> sprintf("<div class=\"updated fade\"><p><strong>Styles With Shortcodes update %s is available! <a href=\"%s\">Please update now</a></strong></p></div>",$r->version,$r->url)
						);
						die(json_encode($response));
					}
				}			
			}
		}
		die(json_encode((object)array('R'=>'ERR','MSG'=>'Notification service is not available.')));		
	}
	
	function admin_head(){
		global $current_screen;
		if('csshortcode'==$current_screen->post_type){
			function sws_update_notice(){
				echo sprintf("<div id=\"sws-notifications\"></div>");
			}
			add_action( 'admin_notices', 'sws_update_notice' );		
?>
<script>
jQuery(document).ready(function($) {
	var args = {
		action: 'sws_notifications'
	};	
	$.post(ajaxurl,args,function(data){
		if(data.R=='OK'){
			$('#sws-notifications').html(data.MSG);	
		}
	},'json');	
});
</script>
<?php
		}
	}
	
	function admin_columns($defaults){
		$new = array();
		foreach($defaults as $key => $title){
			$new[$key]=$title;
			if($key=='title'){
				$new['cscateg']=__("Category",'wpcss');
				$new['csshortcode']=__("Shortcode",'wpcss');
				$new['csusage']=__("SC Usage",'wpcss');
				$new['csbundle']=__("Bundle",'wpcss');
			}
		}
	
		return $new;
	}
	
	function custom_column($field, $post_id=null){
		global $post;
		$post_id = $post_id==null?$post->ID:$post_id;
		if($field=='cscateg'){
			$groups = get_the_terms($post_id, 'csscategory');
			$tmp = array();
			if(is_array($groups)&&count($groups)>0){
				foreach($groups as $group){
					$tmp[]=$group->name;
				}
			}
			echo implode(",",$tmp);
		}else if($field=='csshortcode'){
			echo get_post_meta($post_id,'sc_shortcode',true);
		}else if($field=='csusage'){
			$tag = get_post_meta($post_id,'sc_shortcode',true);
			if(trim($tag)!=''){
				echo implode(', ',$this->posts_ids_to_edit_link_array($this->get_posts_ids_containing_shortcode($tag, 10)));
			}
		}else if($field=='csbundle'){
			echo get_post_meta($post_id,'sc_bundle',true);
		}
	}
	
	function get_posts_ids_containing_shortcode($tag,$limit=1){
		global $wpdb;
		$sql = "SELECT ID FROM `{$wpdb->posts}` WHERE post_content LIKE \"%[$tag%\" LIMIT $limit";
		$sql = "SELECT DISTINCT(ID) FROM `{$wpdb->posts}` WHERE post_content LIKE \"%[$tag%\" AND post_status IN ('publish') LIMIT $limit";
		$ids = $wpdb->get_col($sql,0);
		return (is_array($ids)&&count($ids)>0)?$ids:array();
	}
	
	function posts_ids_to_edit_link_array($ids){
		$r = array();
		while($id=array_shift($ids)){
			$tmp = get_edit_post_link($id);
			if(!empty($tmp))$r[]=sprintf("<a href=\"%s\">%s</a>",$tmp,$id);
		}
		return $r;
	}
	
	function init_taxonomy(){
		  $labels = array(
		    'name' => _x( 'Categories', 'taxonomy general name' ),
		    'singular_name' => _x( 'Category', 'taxonomy singular name' ),
		    'search_items' =>  __( 'Search shortcode categories' ),
		    'popular_items' => __( 'Popular shortcode categories' ),
		    'all_items' => __( 'All Shortcode categories' ),
		    'parent_item' => null,
		    'parent_item_colon' => null,
		    'edit_item' => __( 'Edit shortcode category' ), 
		    'update_item' => __( 'Update shortcode category' ),
		    'add_new_item' => __( 'Add shortcode category' ),
		    'new_item_name' => __( 'New shortcode category name' ),
		  ); 
		
		  register_taxonomy(
		  	'csscategory',
			array('csshortcode'),
			array(
		    	'hierarchical' => true,
		    	'labels' => $labels,
		    	'show_ui' => true,
		    	'query_var' => true,
		    	'rewrite' => array( 'slug' => 'csscategory' ),
		  ));			
	}
	
	function init_post_type($install=false){
		$labels = array(
			'name' 				=> __('Shortcodes'),
			'singular_name' 	=> __('Shortcodes'),
			'add_new' 			=> __('Add new shortcode'),
			'edit_item' 		=> __('Edit shortcode'),
			'new_item' 			=> __('New shortcode'),
			'view_item'			=> __('View shortcode'),
			'search_items'		=> __('Search shortcode'),
			'not_found'			=> __('No shortcodes found'),
			'not_found_in_trash'=> __('No shortcodes found in trash')
		);
		
		register_post_type('csshortcode', array(
			'label' => __('Shortcodes','wpcss'),
			'labels' => $labels,
			'public' => false,
			'show_ui' => ($install)?true:($this->show_ui?true:false),
			'capability_type' => 'post',
			'hierarchical' => false,
			'rewrite' => false,
			'query_var' => false,
			'supports' => array('title','revisions'),
			'exclude_from_search' => true,
			'menu_position' => 100,
			'show_in_nav_menus' => false,
			'taxonomies' => array('csscategory'),
			'menu_icon'=> WPCSS_URL.'images/sws.png'
		));		  	
	}

	function post_meta_box(){
		add_meta_box( 'csshortcode-fields', __('Shortcode fields','wpcss'),	array( &$this, 'csshortcode_fields' ), 'csshortcode', 'normal', 'high');
		add_meta_box( 'csshortcode-settings', __('Shortcode settings','wpcss'),	array( &$this, 'csshortcode_settings' ), 'csshortcode', 'normal', 'high');
		add_meta_box( 'csshortcode-scripts', __('Javascript files','wpcss'),	array( &$this, 'csshortcode_scripts' ), 'csshortcode', 'normal', 'high');
		add_meta_box( 'csshortcode-styles', __('Stylesheet files','wpcss'),	array( &$this, 'csshortcode_styles' ), 'csshortcode', 'normal', 'high');
		add_meta_box( 'csshortcode-import', __('Shortcode Import/Export','wpcss'),	array( &$this, 'csshortcode_import' ), 'csshortcode', 'normal', 'high');
		add_meta_box( 'csshortcode-info', __('Additional info','wpcss'),	array( &$this, 'csshortcode_info' ), 'csshortcode', 'side', 'default');
	}	
		
	function save_post($post_id){
		
		if ( !wp_verify_nonce( @$_POST['csshortcode-css-nonce'], 'csshortcode-css-nonce' )) {
			return $post_id;
		}
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
			return $post_id;
		// Check permissions
     
		//add_post_meta
		if ( 'csshortcode' == $_POST['post_type'] ) {
			remove_all_actions('save_post');
			//--remove_all_actions run inside the same hook is generating a warning
			global $wp_filter;
			$wp_filter['save_post']=array();
			//-------------------------------------
		  if ( !current_user_can( 'edit_post', $post_id ) ){
		  	return $post_id;
		  } 
		} else {
		    return $post_id;
		}
		
		foreach(array('sc_shortcode','sc_priority_shortcode','sc_template','sc_php','sc_css','sc_js','sc_bundle','sc_preview') as $field){
			$value = isset($_POST[$field])?$_POST[$field]:'';
			if($field=='sc_shortcode'){
				//this character on a shortcode crashes the system.
				$value = str_replace("/","_",$value);
			}
			update_post_meta($post_id,$field,$value);
		}
		
		foreach(array('sc_scripts','sc_styles','sc_info') as $field){
			$value = isset($_POST[$field])?$_POST[$field]:array();
			update_post_meta($post_id,$field,$value);
		}
	}
	
	function csshortcode_settings($post){
		echo '<input type="hidden" name="csshortcode-css-nonce" id="csshortcode-css-nonce" value="' . wp_create_nonce( 'csshortcode-css-nonce' ) . '" />';

		$sc_css = get_post_meta($post->ID,'sc_css',true);
		$sc_css = trim($sc_css)==''?"<style>\r\n</style>":$sc_css;
		
		$sc_js = get_post_meta($post->ID,'sc_js',true);
		$sc_js = trim($sc_js)==''?"<script>\r\n</script>":$sc_js;
?>
<div class="wpcss-settings">
	<div class="right">
		<div class="description">
			<p><strong>Shortcode</strong>: The main shortcode.  Most be unique, letters and avoid names with hyphens (example-shortcode), instead use underscore (example_shortcode).</p>
			<p><strong>Bundle</strong>: The bundle is a group of Shortcodes that usually come as plugin add-ons.  SWS comes with a starter bundle.</p>
			<p><strong>Priority Shortocde</strong>: Only choose priority Shortcode if autop is incorrectly formatting the Shortcode output.  In most cases this not needed.  Technically speaking, this makes the Shortcode processed before the autop filter.</p>
		</div>
	</div>
	<div class="left">
		<div class="fieldset">
			<label class="heading inline-label" for="css-shortcode">Shortcode:</label>&nbsp;<input type="text" id="sc_shortcode" class="inline-input" name="sc_shortcode" value="<?php echo get_post_meta($post->ID,'sc_shortcode',true)?>" />
		</div>
		<div class="fieldset">
			<label class="heading inline-label" for="css-bundle">Bundle:</label>&nbsp;<input type="text" id="sc_bundle" class="inline-input" name="sc_bundle" value="<?php echo get_post_meta($post->ID,'sc_bundle',true)?>" />
		</div>
		<div class="fieldset">
			<label class="heading inline-label" for="css-preview">Preview image url:</label>&nbsp;<input type="text" id="sc_preview" class="inline-input" name="sc_preview" value="<?php echo get_post_meta($post->ID,'sc_preview',true)?>" />
		</div>
		<div class="fieldset">
			<label class="heading inline-label" for="css-priority_shortcode">Priority shortcode:</label>
			<select id="sc_priority_shortcode" name="sc_priority_shortcode" class="inline-input">
				<option <?php echo intval(get_post_meta($post->ID,'sc_priority_shortcode',true))==0?'selected':'' ?> value="">No</option>
				<option <?php echo intval(get_post_meta($post->ID,'sc_priority_shortcode',true))==1?'selected':'' ?> value="1">Yes</option>
			</select>
		</div>
	</div>
	<div class="clearer"></div>
	
	<div class="right">
		<div class="description">
			<p><strong>Shortcode template:</strong>This is what the Shortcode will be replaced with.  You can use some template tags here:</p>
			<ul>
				<li><strong>{content}</strong> : Will be replaced with the tag content, for example [tag]{content}[/tag], it can also contain other shortcodes.</li>
			</ul>
		</div>	
	</div>
	<div class="left">
		<div class="fieldset">
			<label class="heading"  for="css-shortcode">Shortcode template:</label><br />
			<textarea name="sc_template" rows="10" cols="40"><?php echo get_post_meta($post->ID,'sc_template',true)?></textarea>
		</div>	
	</div>
	<div class="clearer"></div>
	
	<div class="right">
		<div class="description">
			<p><strong>PHP code:</strong>You can write valid PHP code to generate the shortcode output.  The code most output the desired content.  Example:<br /> echo  "Hello world!";</p>
			<p>The code will be eval()'d inside a try{eval($myphpcode)}catch{} block.</p>
			<h5>Available php variables:</h5>
			<p>All fields are ready to use by your custom php.  For example if you defined a field called width,it is ready to use as $width</p>
			<ul>
				<li><strong>$content</strong> : This variable contains the content section of the shortcode.  For example, for [test]content of shortcode[/test] $content will be assigned "content of shortcode"</li>
				<li><strong>$shortcode_template</strong> : This is assigned the content of the "Shortcode template field" </li>
			</ul>
		</div>	
	</div>
	<div class="left">
		<div class="fieldset">
			<label class="heading"  for="css-php">PHP code:</label><br />
			<textarea name="sc_php" rows="10" cols="40"><?php echo htmlspecialchars(get_post_meta($post->ID,'sc_php',true))?></textarea>			
		</div>	
	</div>
	<div class="clearer"></div>

	<div class="clearer"></div>
	
	<div class="right">
		<div class="description"><p><strong>Styles</strong>: If your Shortcode have stylesheet classes defined, you can add style sheet definitions here.  This will be output in the page footer.</p></div>
	</div>
	<div class="left">
		<div class="fieldset">
			<label class="heading"  for="css-style">Styles:</label><br />
			<textarea name="sc_css" rows="10" cols="40"><?php echo $sc_css?></textarea>
		</div>	
	</div>
	<div class="clearer"></div>
	
	<div class="right">
		<div class="description"><p><strong>Javascript:</strong>: If your Shortcode requires javascript, you can add the code here.  The code is only added once per page even if you insert the same Shortcode multiple times.  This will be output in the page footer.</p></div>
	</div>
	<div class="left">
		<div class="fieldset">
			<label class="heading"  for="css-js">Javascript:</label><br />
			<textarea name="sc_js" rows="10" cols="40"><?php echo $sc_js?></textarea>
		</div>	
	</div>	
	

	<div class="clearer"></div>
</div>
<?php
		add_action('admin_footer',array(&$this,'csshortcode_settings_footer'));
	}
	
	function csshortcode_settings_footer(){
?>
<script>
jQuery(document).ready(function($){
	$('#title').change(function(){
		if(''==$('#sc_shortcode').val() && ''!=this.value){
			var _sc = 'sws_'+this.value.toLowerCase();
			_sc = _sc.replace(' ','_');
			$('#sc_shortcode').val(_sc);
		}
	});
});
</script>
<?php	
	}
	
	function csshortcode_fields($post){
		$this->post = $post;
		
		$dropdown_callback_options = array(
			''=>'--none--'
		);
		$dropdown_callback_options = apply_filters('sws_dropdown_callback_options',$dropdown_callback_options);
?>
<div class="wpcss-fields">
	<h4>Configured shortcode fields</h4>
	<div id="css-fields"></div>
	<hr />
	<h4>Shortcode field details</h4>
	<div id="css-field">
		<table>
			<tr>
				<td class="col1"><label class="css-field-label">Name:</label></td>
				<td class="col2"><input id="css_field_name" name="css_field_name" class="css-field-name" type="text" value="" /></td>
				<td class="col3"><div class="description">Write a new name and you will save a new Shortcode field.</div></td>
			</tr>
			<tr class="not-type not-hidden">
				<td class="col1"><label class="css-field-label">Label:</label></td>
				<td><input id="css_field_label" name="css_field_label" class="css-field-label" type="text" value="" /></td>
				<td>&nbsp;</td>
			</tr>
			<tr class="not-type not-label not-data not-hidden">
				<td class="col1"><label class="css-field-description">Description:</label></td>
				<td class="col2"><textarea id="css_field_description" name="css_field_description" class="css-field-description"></textarea></td>
				<td class="col3"><div class="description">A help text to be displayed next to the field, just like this one.</div></td>
			</tr>
			<tr class="not-type not-label">
				<td class="col1"><label class="css-field-label">Default value:</label></td>
				<td class="col2"><input id="css_field_default" name="css_field_default" class="css-field-default" type="text" value="" /></td>
				<td class="col3">&nbsp;</td>
			</tr>
			<tr class="not-type not-hidden">
				<td class="col1"><label class="css-field-label">Additional classes:</label></td>
				<td class="col2"><input id="css_field_classes" name="css_field_classes" class="css-field-classes" type="text" value="" /></td>
				<td class="col3">&nbsp;</td>
			</tr>	
			<tr class="css-type-fields css-type-text css-type-textarea">
				<td class="col1"><label class="css-field-label">JS function:</label></td>
				<td class="col2"><input id="css_field_jsfunc" name="css_field_jsfunc" class="css-field-jsfunc" type="text" value="" /></td>
				<td class="col3">
					<div class="description">
					<p><strong>JS Function</strong>: Example,  you want to format a number to 2 decimal positions; you can use this template <br /><strong>({val}).toFixed(2)</strong>, where {val} will be replaced with the value that the shortcode user inputs into the field.</p>
					</div>				
				</td>
			</tr>			
			<tr class="not-type not-label not-ui_theme">
				<td class="col1"><label class="css-field-label">Content:</label></td>
				<td class="col2">
					<select id="css_field_content" name="css_field_content" class="css-field-content" >
						<option value='0'>No</option>
						<option value='1'>Yes</option>
					</select>					
				</td>
				<td class="col3">
					<div class="description">
					<p>Set to yes if this field will generate the content portion of a Shortcode. Example: if the field content should go to "content" in [shortcode]content[/Shortcode]</p>
					<p>NOTE:When building a Shortcode, fields are processed sequentially, therefore: never put a non-content field after a content field or the system will end up generating a bad Shortcode.</p>
					</div>
				</td>
			</tr>

			<tr>
				<td class="col1"><label class="css-field-type">Type:</label></td>
				<td class="col2">
					<select id="css_field_type" name="css_field_type" class="css-field-type" >
						<option value='text'>text</option>
						<option value='colorpicker'>Colorpicker</option>
						<option value='textarea'>Textarea</option>
						<option value='rtf'>rtf editor</option>
						<option value='dropdown'>drop-down</option>
						<option value='checkbox'>checkbox</option>
						<option value='slider'>Slider/Range input</option>
						<option value='data'>Data set/Stackable fields</option>
						<option value='label'>Label</option>
						<option value='hidden'>Hidden field</option>
						<option value='ui_theme'>jQuery UI Theme</option>
					</select>				
				</td>
				<td class="col3">&nbsp;</td>
			</tr>

			<tr class="css-type-checkbox css-type-fields">
				<td class="col1"><label class="css-field-type">Checked value:</label></td>
				<td class="col2">
					<input id="css_checkbox_value" name="css_checkbox_value" class="css_checkbox_value" type="text" value="" />
				</td>
				<td class="col3" rowspan=2>
				<div class="description">Specify the checkbox value.  That is the value sent to the shortcode when the checkbox is checked.  Else a blank string is sent.</div>
				</td>		
			</tr>
			
			<tr class="css-type-dropdown css-type-fields">
				<td class="col1"><label class="css-field-type">Predifined options:</label></td>
				<td class="col2">
					<select id="css_dropdown_callback" name="css_dropdown_callback" >
<?php foreach($dropdown_callback_options as $value=>$label):?>
					<option value="<?php echo $value?>"><?php echo $label?></option>
<?php endforeach; ?>						
					</select>				
				</td>
				<td class="col3" rowspan=2>
				<div class="description">
					<h4>Dropdown options</h4>
					<p>If you do not specify a predefined options source for the dropdown, you can specify them manually witht the "Drop-down values" fields.</p>
					<p>Options to use in the drop-down.  Write pairs of value|label, 1 pair per line.</p>  
					<p>
				The following example settings:<br />
<textarea id="example-dropdown">
|--choose a month--
01|January
02|February
</textarea><br />
				Will generate:<br />
				<select>
					<option value="">--choose a month--</option>
					<option value="01">January</option>
					<option value="02">February</option>
				</select>
					</p>
				</div>			
				</td>
			</tr>
			
			<tr class="css-type-dropdown css-type-fields">
				<td class="col1"><label class="css-field-label">Drop-down values:</label></td>
				<td class="col2"><textarea id="css_field_dropdown_values" name="css_field_dropdown_values" class="css_field_dropdown_values" >|--choose--</textarea></td>
				
			</tr>
			
			<tr class="css-type-slider css-type-fields">
				<td class="col1"><label class="css-field-min-label">Min value:</label></td>
				<td class="col2"><input id="css_field_min" name="css_field_min" class="css-field-min" type="text" value="" /></td>
				<td class="col3" rowspan=3><div class="description"><p><strong>Min/Max</strong>: minimum and maximum values the slider can set</p>
				<p><strong>Step</strong>:The granularity/increments of each step, normally 1.  Or 0.1 for opacity values with 1 decimal.</p>
				</div></td>
			</tr>			
			
			<tr class="css-type-slider css-type-fields">
				<td class="col1"><label class="css-field-max-label">Max value:</label></td>
				<td class="col2"><input id="css_field_max" name="css_field_max" class="css-field-max" type="text" value="" /></td>	
			</tr>	
			
			<tr class="css-type-slider css-type-fields">
				<td class="col1"><label class="css-field-step-label">Steps:</label></td>
				<td class="col2"><input id="css_field_step" name="css_field_step" class="css-field-step" type="text" value="1" /></td>
			</tr>		
			
			<tr class="css-type-data css-type-fields">
				<td class="col1"><label class="css-field-shortcode-label">Shortcode:</label></td>
				<td class="col2"><input id="css_field_shortcode" name="css_field_shortcode" class="css-field-shortcode" type="text" value="" /></td>
				<td class="col3" rowspan=2>
					<div class="description"><p>Specify a Shortcode and the number of fields it contains.  This will generate a nested Shortcode structue, example<br />
<pre>
[shortcode]
	[field_shortcode][/field_shortcode]
	[field_shortcode][/field_shortcode]
[/shortcode]
</pre>
If you specify Number of fields=3, the 3 fields following the data type field will become properties or content of the item subshortcode.
</p></div>
				</td>
			</tr>		
			
			<tr class="css-type-data css-type-fields">
				<td class="col1"><label class="css-field-field_number-label">Number of fields:</label></td>
				<td class="col2"><input id="css_field_field_number" name="css_field_field_number" class="css-field-field_number" type="text" value="" /></td>
			</tr>		
			
			<tr class="css-type-data css-type-fields">
				<td class="col1"><label class="css-field-shortcode_template-label">SC Template:</label></td>
				<td class="col2"><textarea id="css_field_shortcode_template" name="css_field_shortcode_template" class="css-field-shortcode_template"></textarea></td>
			</tr>		
			
			<tr class="css-type-data css-type-fields">
				<td class="col1"><label class="css-field-button_label-label">Add button label:</label></td>
				<td class="col2"><input id="css_field_button_label" name="css_field_button_label" class="css-field-button_label" type="text" value="" /></td>
			</tr>		
			
		</table>
		
		<div class="css-field">
			<input id="button_add_field" type="button" class="button-primary" name="save_field" value="Save shortcode field" />
			<div class="button_add_field_msg">&nbsp;</div>
		</div>
	</div>
	<div class="clearer"></div>
</div>
<?php
		
		add_action('admin_footer',array(&$this,'csshortcode_fields_footer'));
	}
	
	function csshortcode_fields_footer(){
?>
<script>
jQuery(document).ready(function($){
	$('#button_add_field').click(function(){add_css_field();});
	$('#css_field_type').change(function(){
		$('.css-type-fields').hide();
		$('.css-type-'+$(this).val()).show();
		$('.not-type').show();
		$('.not-'+$(this).val()).hide();
	}).trigger('change');
});

function load_css_fields(){
	jQuery(document).ready(function($){
		$('#css-fields').addClass('loading');
		var args = {
			action : 'sws_list_fields',
			ID: $('#post_ID').val()
		};
		$('#css-fields').load(ajaxurl,args,function(){$(this).removeClass('loading');});
	});
}

function moveup_css_field(_name){
	jQuery(document).ready(function($){
		$('.button_add_field_msg ').addClass('loading');
		var args = {
			action : 'sws_moveup_field',
			ID: $('#post_ID').val(),
			'name':_name,
			'nonce':$('#csshortcode-css-nonce').val()
		};
		$.post(ajaxurl,args,function(data){
			if(data.R=='OK'){

			}else if(data.R=='ERR'){
				_alert(data.MSG);	
			}else{
				_alert('Unexpected error.');			
			}
			load_css_fields();
			$('.button_add_field_msg ').removeClass('loading');
		},'json');
	});
}

function edit_css_field(_name){
	jQuery(document).ready(function($){
		$('.button_add_field_msg ').addClass('loading');
		var args = {
			action: 'sws_get_field',
			ID: $('#post_ID').val(),
			name:_name,
			nonce:$('#csshortcode-css-nonce').val()
		};
		$.post(ajaxurl,args,function(data){
			if(data.R=='OK'){
				$('#css_field_label').val(data.DATA.label);
				$('#css_field_name').val(data.DATA.name);
				$('#css_field_description').val(data.DATA.description);
				$('#css_field_default').val(data.DATA.default_value);
				$('#css_field_classes').val(data.DATA.classes);
				$('#css_field_jsfunc').val(data.DATA.jsfunc);
				$('#css_field_content').val(data.DATA.content);
				$('#css_field_type').val(data.DATA.type).change();
				$('#css_dropdown_callback').val( ('undefined'==typeof(data.DATA.dropdown_callback)?'':data.DATA.dropdown_callback ) ).change();
				$('#css_field_dropdown_values').val(data.DATA.options);
				$('#css_field_min').val(data.DATA.min);
				$('#css_field_max').val(data.DATA.max);
				$('#css_field_step').val((undefined==data.DATA.step)?1:data.DATA.step);
				$('#css_field_shortcode').val(data.DATA.shortcode);
				$('#css_field_shortcode_template').val(data.DATA.shortcode_template);
				$('#css_field_field_number').val(data.DATA.field_number);
				$('#css_field_button_label').val(data.DATA.button_label);
				$('#css_checkbox_value').val(data.DATA.checkbox_value);
			}else if(data.R=='ERR'){
				load_css_fields();
				_alert(data.MSG);	
			}else{
				load_css_fields();
				_alert('Unexpected error.');			
			}
			$('.button_add_field_msg ').removeClass('loading');
		},'json');
	});
}

function delete_css_field(_name){
	jQuery(document).ready(function($){
		$('.button_add_field_msg ').addClass('loading');
		var args = {
			action : 'sws_remove_field',
			ID: $('#post_ID').val(),
			name:_name,
			nonce:$('#csshortcode-css-nonce').val()
		};
		$.post(ajaxurl,args,function(data){
			if(data.R=='OK'){
				
			}else if(data.R=='ERR'){
				_alert(data.MSG);	
			}else{
				_alert('Unexpected error.');			
			}
			load_css_fields();
			$('.button_add_field_msg ').removeClass('loading');
		},'json');
	});
}

function add_css_field(){
	jQuery(document).ready(function($){
		$('.button_add_field_msg ').addClass('loading');
		var args = {
			action : 		'sws_add_field',
			ID: 			$('#post_ID').val(),
			name:			$('#css_field_name').val(),
			label:			$('#css_field_label').val(),
			description:	$('#css_field_description').val(),
			'default':		$('#css_field_default').val(),
			classes:		$('#css_field_classes').val(),
			jsfunc:			$('#css_field_jsfunc').val(),
			content:		$('#css_field_content').val(),
			type:			$('#css_field_type').val(),
			dropdown_callback:$('#css_dropdown_callback').val(),
			options:		$('#css_field_dropdown_values').val(),
			'min':			$('#css_field_min').val(),
			'max':			$('#css_field_max').val(),
			step:			$('#css_field_step').val(),
			shortcode:		$('#css_field_shortcode').val(),
			field_number:	$('#css_field_field_number').val(),
			shortcode_template:$('#css_field_shortcode_template').val(),
			button_label:	$('#css_field_button_label').val(),
			nonce:			$('#csshortcode-css-nonce').val(),
			checkbox_value:	$('#css_checkbox_value').val()
		};
		$.post(ajaxurl,args,function(data){
			if(data.R=='OK'){
				load_css_fields();
			}else if(data.R=='ERR'){
				_alert(data.MSG);	
			}else{
				_alert('Unexpected error.');			
			}
			$('.button_add_field_msg ').removeClass('loading');
		},'json');
	});
}

function _alert(msg){
	alert(msg);
}

load_css_fields();
</script>
<?php	
	}
	
	function csshortcode_import(){
?>
<div class="wpcss-import">
	<h4>Export Shortcode Settings</h4>

	<div class="right">
		<div class="description">Share your shortcodes with other users.</div>
	</div>
	<div class="left">
		<div class="fieldset">
			<label class="heading"  for="css-export">Export:</label><br />
			<textarea id="css-export" rows="10" cols="40">Click Export this shortcode settings to generate an importable shortcode settings code.</textarea>
		</div>	
		<input type="button" id="btn-export" class="button-primary" value="Export this shortcode settings" />
	</div>		
	<div class="clearer"></div>
	
	<div class="right">
		<div class="description">
			<h4>Import shortcode settings from other sites.</h4>
			<strong>Conflicts:</strong>
			<p><strong>Duplicate shortcode name</strong> : Shortcode names most be unique. Although you can add duplicate names, if you publish duplicate Shortcode names, only one will be used by WordPress.</p>
			<p><strong>Missing Javascript/Stylesheet</strong> : if you import a code and it complains about missing javascript or stylesheet, then the code is most likely exported from a website with a bundle or add-on that is not installed on this site.</p>
		</div>
	</div>
	<div class="left">
		<div class="fieldset">
			<label class="heading"  for="css-import">Import:</label><br />
			<textarea id="css-import" rows="10" cols="40">Copy paste a valid Shortcode settings code into this box and click on more info to get details on the Shortcode.</textarea>
		</div>	
		<input type="button" id="btn-moreinfo" class="button-primary" value="More info" />
		
	</div>		
	<div class="clearer"></div>
	
	<div class="left shortcode-details">
		<div class="fieldset">
			<label class="heading"  for="css-import">Shortcode details:</label><br />
			<div id="shortcode-details">
				<table class="widefat">
					<tr>
						<td>Name</td>
						<td><div id="import-name"></div></td>
					</tr>
					<tr>
						<td>Shortcode</td>
						<td><div id="import-shortcode"></div></td>
					</tr>
					<tr>
						<td>Bundle</td>
						<td><div id="import-bundle"></div></td>
					</tr>
					<tr>
						<td>Categories</td>
						<td><div id="import-category"></div></td>
					</tr>
					<tr>
						<td>Author</td>
						<td><div id="import-info"></div></td>
					</tr>
					<tr id="import-warning-tr">
						<td><span class="import-conflict">Conflicts(!)</span></td>
						<td><div id="import-warning"></div></td>
					</tr>
				</table>
			</div>
		</div>	
		<input type="checkbox" id="import-terms" value="1" />&nbsp;<label>Import Categories.</label><br />
		<input type="button" id="btn-import" class="button-primary" value="Confirm Import shortcode settings" />
	</div>
			
	<div class="clearer"></div>
		
	<div class="clearer"></div>
</div>
<?php	
		add_action('admin_footer',array(&$this,'csshortcode_import_footer'));
	}
	
	function csshortcode_import_footer(){
?>
<style>
#import-warning-tr {
	display:none;
}
</style>
<script>
jQuery(document).ready(function($){
	$('#btn-export').click(function(){css_export();});
	$('#btn-moreinfo').click(function(){css_moreinfo();});
	$('#btn-import').click(function(){css_import();});
	$('#css-export').hide();
	$('.shortcode-details').hide();
});

function css_export(){
	jQuery(document).ready(function($){
		var args = {
			action: 'sws_sc_export',
			ID:		$('#post_ID').val(),
			nonce:	$('#csshortcode-css-nonce').val()
		};
		$.post(ajaxurl,args,function(data){
			if(data.R=='OK'){
				$('#css-export').val(data.DATA).show();
				_alert('Done.  You can copy paste the generated code into another site with the SWS plugin installed.');
			}else if(data.R=='ERR'){
				_alert(data.MSG);	
			}else{
				_alert('Unexpected error.');			
			}
		},'json');
	});
}

function css_import(){
	jQuery(document).ready(function($){
		var args = {
			action:		'sws_sc_import',
			ID:			$('#post_ID').val(),
			nonce:		$('#csshortcode-css-nonce').val(),
			code:		$('#css-import').val(),
			import_terms:$('#import-terms').is(':checked')
		};
		$.post(ajaxurl,args,function(data){
			if(data.R=='OK'){
				window.location.href = unescape(data.URL);
			}else if(data.R=='ERR'){
				_alert(data.MSG);	
			}else{
				_alert('Unexpected error.');			
			}
		},'json');
	});
}

function css_moreinfo(){
	jQuery(document).ready(function($){
		var args = {
			action:		'sws_sc_moreinfo',
			ID:			$('#post_ID').val(),
			nonce:		$('#csshortcode-css-nonce').val(),
			code:		$('#css-import').val()
		};
		$.post(ajaxurl,args,function(data){
			if(data.R=='OK'){
				$('#import-name').html(data.DATA.name);
				$('#import-shortcode').html(data.DATA.shortcode);
				$('#import-bundle').html(data.DATA.bundle);
				if(undefined!=data.DATA.info.url && ''!=data.DATA.info.url){
					$('#import-info').html( '<a href="'+data.DATA.info.url+'">'+data.DATA.info.author+'</a>');
				}else{
					$('#import-info').html(data.DATA.info.author);
				}
				$('#import-category').html(data.DATA.category.join(','));
				$('.shortcode-details').show();
				$('#import-warning').html('');
				$('#import-warning-tr').hide();
				if(data.DATA.duplicate_posts.length>0){
					var _html = "A custom style shortcode with the same name is already in the system.  Shortcode names most be unique; only one will be used.";
					if(data.DATA.duplicate_links.length>0){
						_html = _html + "<br />Duplicate shortcodes: " + data.DATA.duplicate_links.join(', ');
					}
					$('#import-warning').html(_html);
					$('#import-warning-tr').show();
				}
				if(data.DATA.warnings.length>0){
					$('#import-warning').append( '<br />' + data.DATA.warnings.join('<br />') );
					$('#import-warning-tr').show();
				}				
			}else if(data.R=='ERR'){
				_alert(data.MSG);	
			}else{
				_alert('Unexpected error.');			
			}
		},'json');
	});
}
</script>
<?php	
	}
	
	function csshortcode_scripts($post){
		global $sws_plugin;
		$sc_scripts = get_post_meta($post->ID,'sc_scripts',true);
		$sc_scripts = is_array($sc_scripts)?$sc_scripts:array();
?>
<p>Please check any libraries that this shortcode will need to include.</p>
<?php if(count($sws_plugin->sws_scripts)>0):?>
<div class="sws-script-option-cont">
			<?php foreach($sws_plugin->sws_scripts as $s):?>
<div class="sws-script-option">
	<input type="checkbox"  <?php echo in_array($s->id,$sc_scripts)?'checked':''?> name="sc_scripts[]" value="<?php echo $s->id ?>" />&nbsp;&nbsp;<?php echo $s->label ?>
</div>
			<?php endforeach;?>
	<div class="clear"></div>
</div>
<?php else: ?>
<b>There are no scripts registered with the plugin.</b>
<?php endif; ?>
<?php
	}
	
	function csshortcode_styles($post){
		global $sws_plugin;
		$sc_styles = get_post_meta($post->ID,'sc_styles',true);
		$sc_styles = is_array($sc_styles)?$sc_styles:array();
?>
<p>Please check any stylesheets that this shortcode will need to include.</p>
<?php if(count($sws_plugin->sws_styles)>0):?>
<div class="sws-style-option-cont">
			<?php foreach($sws_plugin->sws_styles as $s):?>
<div class="sws-style-option">
	<input type="checkbox"  <?php echo in_array($s->id,$sc_styles)?'checked':''?> name="sc_styles[]" value="<?php echo $s->id ?>" />&nbsp;&nbsp;<?php echo $s->label ?>
</div>
			<?php endforeach;?>
	<div class="clear"></div>		
</div>
<?php else: ?>
<b>There are no stylesheets registered with the plugin.</b>
<?php endif; ?>
<?php
	}
	
	function csshortcode_info($post){
		$sc_info = get_post_meta($post->ID,'sc_info',true);
		$sc_info = is_array($sc_info)?$sc_info:array();	
?>
<div class="sc_info_field">
	<label for="sc_info_author">Author:</label>
	<input type="text" name="sc_info[author]" value="<?php echo @$sc_info["author"]?>" />
</div>
<div class="sc_info_field">
	<label for="sc_info_url">URL:</label>
	<input type="text" name="sc_info[url]" value="<?php echo @$sc_info['url']?>" />
</div>
<?php	
	}
}

//----- adding more Predifined options source: ----------
add_filter('sws_dropdown_callback_options','sws_dropdown_callback_options',10,1);
function sws_dropdown_callback_options($options){
	$options['sws_category_options_slug']	= __('Category dropdown (value=slug)','sws');
	$options['sws_registered_post_types_with_ui']	= __('Registered post types with UI(value=name)','sws');
	$options['sws_registered_taxonomies']	= __('Registered taxonomies(value=name)','sws');	
	return $options;
}

function sws_category_options_slug(){
	$categories = get_categories( $args );
	$options = array(''=> __('--choose category--','sws') );
	foreach($categories as $c){
		$options[$c->slug]=$c->cat_name;
	}
	return $options;
}

function sws_registered_post_types_with_ui($options){
	$args=array(
	  'show_ui'   => true
	); 
	$options = array(''=> __('--choose post type--','sws') );
	$post_types=get_post_types($args,"objects","and");
	if(is_array($post_types)&&count($post_types)>0){
		foreach($post_types as $p){
			$options[$p->name]=$p->label;
			
		}
	}
	return $options;
}

function sws_registered_taxonomies(){
	$args=array(
	  'public'   => true
	); 
	$options = array(''=> __('--choose post type--','sws') );
	$taxonomies = get_taxonomies($args,'objects','and');
	if(is_array($taxonomies)&&count($taxonomies)>0){
		foreach($taxonomies as $t){
			$options[$t->name]=$t->label;
		}
	}
	return $options;
}
?>