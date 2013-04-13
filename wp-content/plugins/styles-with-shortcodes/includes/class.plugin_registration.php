<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/
if(!class_exists('plugin_registration')):
class plugin_registration {
	var $plugin_id;
	var $plugin_code;
	var $label;
	var $right_label;
	var $page_title;
	var $tdom;
	var $options_varname;
	function plugin_registration($args=array()){
		$defaults = array(
			'plugin_id'				=> '',
			'plugin_code'			=> 'POP',
			'tdom'					=> 'righthere',
			'options_varname'		=> 'pop_options',
			'capability'			=> 'manage_options'
		);
		foreach($defaults as $property => $default){
			$this->$property = isset($args[$property])?$args[$property]:$default;
		}		
		add_filter( "pop-options_{$this->plugin_id}",array(&$this,'options'),100,1);
		add_action("pop_admin_head_{$this->plugin_id}",array(&$this,'pop_admin_head'));
		add_action('wp_ajax_registered-licenses-'.$this->plugin_id, array(&$this,'registered_licenses'));
		add_action('wp_ajax_add-license-'.$this->plugin_id, array(&$this,'add_license'));
	}
	
	function options($t){
		$i = count($t);
		//--Default backgrounds -----------------------		
		$i++;
		$t[$i]->id 			= 'license'; 
		$t[$i]->label 		= __('License',$this->tdom);
		$t[$i]->right_label	= __('Item Purcahse Key',$this->tdom);
		$t[$i]->page_title	= __('Product License',$this->tdom);
		$t[$i]->theme_option = false;
		$t[$i]->plugin_option = true;
		$t[$i]->options = array(
			(object)array(
				'id'		=> 'license_key_callback',
				'type'		=> 'callback',
				'callback'	=> array(&$this,'login_options')	
			)			
		);
		return $t;
	}
	
	function pop_admin_head(){
?>
<script>
jQuery('document').ready(function($){
	$('#submit_license').unbind('click').click(function(e){
		$('#add-license-msg').html('Adding license').addClass('add-license-message').removeClass('add-license-error').fadeIn();
		var url = 'dev.lawley.com';
		var args = {
			'action':'add-license-<?php echo $this->plugin_id ?>',
			'license_key':$('#add_license_key').val()
		};
		$.post(ajaxurl,args,function(data){
			if(data.R=='OK'){
				$('#add-license-msg').html('Done, reloading license keys').show().addClass('add-license-message').removeClass('add-license-error').fadeOut('slow');
				load_registered_licenses();
			}else if(data.R=='ERR'){
				$('#add-license-msg').html(data.MSG).removeClass('add-license-message').addClass('add-license-error').show();
			}else{
				$('#add-license-msg').html('Service not available.').removeClass('add-license-message').addClass('add-license-error').show();
			}
		},'json');
	});
	load_registered_licenses();
});

function load_registered_licenses(){
	jQuery('document').ready(function($){
		var ts = new Date();
		var args = {
			'action':'registered-licenses-<?php echo $this->plugin_id ?>',
			'ts':escape(ts)
		};
		$('.registered-license-cont').load(ajaxurl,args,function(){
		
		});
	});
}
</script>
<?php	
	}
	
	function add_license(){
		if(!$this->check_ajax()){
			die(json_encode((object)array('R'=>'ERR','MSG'=>'Service not available.')));
		}	
		$license_key = isset($_REQUEST['license_key'])&&trim($_REQUEST['license_key'])!=''?$_REQUEST['license_key']:false;
		if(false===$license_key){
			die(json_encode((object)array('R'=>'ERR','MSG'=>'Missing parameter')));
		}
		
		$options = $this->get_options();
		$options['license_keys'] =  is_array($options['license_keys'])&&count($options['license_keys'])>0?$options['license_keys']:array();
		//--check existing
		if(count($options['license_keys'])>0){
			foreach($options['license_keys'] as $l){
				if(@$l->license_key==$license_key){
					die(json_encode((object)array('R'=>'ERR','MSG'=>'License already added.')));
				}
			}
		}

		$url = sprintf('http://plugins.righthere.com/?content_service=verify_license_key&license_key=%s&plugin_code=%s',urlencode($license_key),urlencode($this->plugin_code));

		if(!class_exists('righthere_service'))require_once 'class.righthere_service.php';
		$rh = new righthere_service();
		$r = $rh->rh_service($url);		

		if(false!==$r){
			if($r->R=='OK'){
				if(!in_array($r->LICENSE->item_type,array('plugin','theme'))){
					if(count($options['license_keys'])==0){
						die(json_encode((object)array('R'=>'ERR','MSG'=>'Please add a main license key before adding an addon license key.')));
					}	
				}
				$options['license_keys'][]=$r->LICENSE;
				update_option($this->options_varname,$options);
				die(json_encode($r));
			}else if($r->R=='ERR'){
				die(json_encode($r));
			}else{
				die(json_encode((object)array('R'=>'ERR','MSG'=>'Service not available.(1)')));
			}			
		}
		die(json_encode((object)array('R'=>'ERR','MSG'=>'Service not available.(2)')));
	}
	
	function registered_licenses(){
		if(!$this->check_ajax()){
			die('.');
		}	
		$options = $this->get_options();
		if(isset($options['license_keys'])&&count($options['license_keys'])>0){
			foreach($options['license_keys'] as $i => $l){
?>
<div class="license-key-desc">
	<label><?php echo @$l->item_name?> (<?php echo @$l->license?>)</label><br />
	<i><?php echo @$l->license_key?></i>
</div>
<?php							
			}
		}
		
		die();
	}
	function login_options($tab,$i,$o,&$save_fields){
		foreach(array('license_key','license_item_name') as $option_name){
			$$option_name = isset($o->existing_options['license_key'])?$o->existing_options['license_key']:'';
		}
		foreach(array('extra_license_key','extra_license_item_name') as $option_name){
			$$option_name = isset($o->existing_options['license_key'])&&is_array($o->existing_options['license_key'])?$o->existing_options['license_key']:array();
		}
		
?>
		<div class="description"><p>Your purchase code can be found in your license Certificate file.</p>
		<p>Go to Codecanyon and click My Account at the top, then click Downloads, and then click the <strong>License Certificate link</strong>.
		You will find the code in there and it will look something like this:</p>
		<p>Item Purchase Code:<br>bek72585-d6a6-4724-c8c4-9d32f85734g3</p>
		<p>This allows us to verify your purchase and provide support to those who have paid. We will also automatically notify you when updates are available. Updates are free to download if you have purchased this once. If you have questions about this, please contact us at <a href="mailto:support@righthere.com">support@righthere.com</a>.</p>
		</div>
		
		<div class="pt-option">
			<div class="add-license-key">
				<h4>License key</h4>
				<input type="text" id="add_license_key" class="add_license_key" name="add_license_key" value="" />&nbsp;
				<input class="button-secondary" type="button" id="submit_license" value="Add license" />
			</div>
			<div id="add-license-msg" class="">TODO</div>	
			<div class="registered-license-cont">
			...
			</div>		
		</div>


		<div class="pt-clear"></div>

<?php		
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
	function get_option($name){
		$options = $this->get_options();
		return isset($options[$name])?$options[$name]:'';
	}	
	function get_options(){
		$options = get_option($this->options_varname);
		return is_array($options)?$options:array();
	}	
	function check_ajax(){
		if(current_user_can('manage_options')||current_user_can($this->capability)){
			return true;
		}else{
			return false;
		}
	}	
}
endif;

if(!class_exists('righthere_license')):
class righthere_license {
	var $license_key;
	var $item_id;
	var $item_name;
	var $created_at;
	var $license;
	var $item_type;
	function righthere_license($args=array()){
		if(count($args)>0){
			foreach($args as $field => $value){
				$this->$field = $value;
			}		
		}
	}
}
endif;
?>