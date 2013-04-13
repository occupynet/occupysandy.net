<?php
class sws_lightbox {
	var $options;
	function sws_lightbox($options){
		$this->options = $options;
	
		if(intval($this->get_option('le_load_theme'))==0){
			if(is_admin()){
				add_action('admin_head',array(&$this,'head'));
			}else{
				add_action('wp_head',array(&$this,'head'));
			}
		}
	}
	
	function head(){
		$this->load_theme();
		$this->lightbox_settings();
	}
	function load_theme(){
		$theme = $this->get_option('le_theme');
		if($theme=='disable')return;
		$theme = trim($theme)==''?'default':$theme;	
?>
<link rel="stylesheet" type="text/css" href="<?php echo WPCSS_URL?>js/lightbox/themes/<?php echo $theme?>/jquery.lightbox.css" />
<!--[if IE 6]><link rel="stylesheet" type="text/css" href="<?php echo WPCSS_URL?>js/lightbox/themes/<?php echo $theme?>/jquery.lightbox.ie6.css" /><![endif]-->
<?php	
	}	
	function lightbox_settings(){
		$options = array(
			'le_modal'			=>'modal',
			'le_resize_images'	=>'autoresize',
			'le_emerge_from'	=>'emergefrom',
			'le_show_duration'	=>'showDuration',
			'le_close_duration'	=>'closeDuration',
			//'le_move_duration'	=>'moveDuration',
			//'le_resize_duration'=>'resizeDuration',
			'le_opacity'		=>'opacity'	
		);
		$le=array();
		foreach($options as $option => $name){
			if(isset($this->options[$option])){
				$le[$name]=$this->options[$option];
			}
		}		
		$le['modal']=$le['modal']?true:false;
		$json = json_encode((object)$le);
?>
<?php	if(isset($this->options['le_background_color_custom'])&&''!=$this->options['le_background_color_custom']):?>
<style>.jquery-lightbox-overlay {background: <?php echo "#".$this->options['le_background_color_custom']?>;}</style>
<?php endif;?>
<script type='text/javascript'>
var sws_lightbox = <?PHP echo ($json)?$json:'{}';?>;
</script>

<?php

	}
	function get_option($name){
		return isset($this->options[$name])?$this->options[$name]:'';
	}
}


?>