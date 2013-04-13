<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class lightbox_options {
	var $plugin_id;
	function lightbox_options($args=array()){
		$defaults = array(
			'plugin_id'				=> ''
		);
		foreach($defaults as $property => $default){
			$this->$property = isset($args[$property])?$args[$property]:$default;
		}		
		add_filter( "pop-options_{$this->plugin_id}",array(&$this,'options'),10,1);
	}
	function options($t){
		$i = count($t);
		//-------------------------		
		$i++;
		$t[$i]->id 			= 'lightbox'; 
		$t[$i]->label 		= __('Lightbox Settings','sws');//title on tab
		$t[$i]->right_label	= __('Lightbox Settings','sws');//title on tab
		$t[$i]->page_title	= __('Lightbox Settings','sws');//title on content
		$t[$i]->theme_option = true;
		$t[$i]->plugin_option = true;
		$t[$i]->options = array(
			(object)array(
				'id'	=> 'le_theme',
				'type'	=> 'select',
				'label'	=> __('Choose a theme','sws'),
				'description'=>__('<p>If you are using a theme or plugin that already includes a Lightbox Evolution theme, you can choose "--Disable&nbsp;Bundled&nbsp;Theme--" to prevent SWS from including its bundled theme.</p>','sws'),
				'options'=>array(
					''=>'--choose theme--',
					'default'=>'Default',
					'classic'=>'Classic',
					'classic-dark'=>'Classic Dark',
					'evolution'=>'Evolution',
					'evolution-dark'=>'Evolution Dark',
					'minimalist'=>'Minimalist',
					'white-green'=>'White Green',
					'disable'=>'--Disable Bundled Theme--',
					'disable_front'=>'--Disable Bundled Theme on frontend only--',
					'disable_admin'=>'--Disable Bundled Theme on wp-admin only--'
				),
				'save_option'=>true,
				'load_option'=>true
			),
			(object)array(
				'type'=>'subtitle',
				'label'=>'Background Overlay'
			),
			(object)array(
				'id'=>'le_modal',
				'type'=>'yesno',
				'label'=>'Modal',
				'save_option'=>true,
				'load_option'=>true
			),
			(object)array(
				'id'=>'le_opacity',
				'type'=>'range',
				'label'=>'Opacity',
				'default'=>'0.6',
				'step'=>'0.01',
				'min'=>'0',
				'max'=>'1',
				'save_option'=>true,
				'load_option'=>true
			),
			(object)array(
				'id'=>'le_background_color_custom',
				'type'=>'colorpicker',
				'label'=>'Custom background color',
				'save_option'=>true,
				'load_option'=>true
			),
			(object)array(
				'type'=>'subtitle',
				'label'=>'Display Options'
			),
			(object)array(
				'id'	=> 'le_emerge_from',
				'type'	=> 'select',
				'label'	=> __('Animation starts from','sws'),
				'default'=>'top',
				'options'=>array(
					'top'=>'Top(default)',
					'bottom'=>'bottom'
				),
				'save_option'=>true,
				'load_option'=>true
			),
			(object)array(
				'id'	=> 'le_resize_images',
				'type'	=> 'yesno',
				'label'	=> __('Resize images','sws'),
				'save_option'=>true,
				'load_option'=>true
			),			
			(object)array(
				'id'=>'le_show_duration',
				'type'=>'range',
				'label'=> __('Opening Duration (milliseconds)','sws'),
				'default'=>'400',
				'step'=>'100',
				'min'=>'0',
				'max'=>'5000',
				'el_properties'=>array('style'=>'width:65px;'),
				'save_option'=>true,
				'load_option'=>true
			),			
			(object)array(
				'id'=>'le_close_duration',
				'type'=>'range',
				'label'=> __('Closing Duration (milliseconds)','sws'),
				'default'=>'200',
				'step'=>'100',
				'min'=>'0',
				'max'=>'5000',
				'el_properties'=>array('style'=>'width:65px;'),
				'save_option'=>true,
				'load_option'=>true
			),	
			/* this are defined on the le js but dont seem to do anything.		
			(object)array(
				'id'=>'le_move_duration',
				'type'=>'range',
				'label'=> __('Move Duration (milliseconds)','sws'),
				'default'=>'1000',
				'step'=>'100',
				'min'=>'0',
				'max'=>'5000',
				'el_properties'=>array('style'=>'width:65px;'),
				'save_option'=>true,
				'load_option'=>true
			),			
			(object)array(
				'id'=>'le_resize_duration',
				'type'=>'range',
				'label'=> __('Resize Duration (milliseconds)','sws'),
				'default'=>'1000',
				'step'=>'100',
				'min'=>'0',
				'max'=>'5000',
				'el_properties'=>array('style'=>'width:65px;'),
				'save_option'=>true,
				'load_option'=>true
			),		
			*/	
			(object)array(
				'type'=>'clear'
			),			
			(object)array(
				'type'=>'submit',
				'class'=>'button-primary',
				'label'=>__('Save','sws')
			)
		);		
		//-------------------------			
		return $t;
	}
	
	function select_theme($tab,$i,$o,&$save_fields){
?>
TODO SELECT THEME
<?php	
	}
}
?>