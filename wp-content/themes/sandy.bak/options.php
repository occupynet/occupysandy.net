<?php
/**
 * A unique identifier is defined to store the options in the database and reference them from the theme.
 * By default it uses 'foghorn'.  If the identifier changes, it'll appear as if the options have been reset.
 * 
 */

function optionsframework_option_name() {
	
	$optionsframework_settings = get_option('optionsframework');
	$optionsframework_settings['id'] = 'foghorn';
	update_option('optionsframework', $optionsframework_settings);
	
}

/**
 * Defines an array of options that will be used to generate the settings page and be saved in the database.
 * When creating the "id" fields, make sure to use all lowercase and no spaces.
 *  
 */

function optionsframework_options() {
		
	// If using image radio buttons, define a directory path
	$imagepath =  get_bloginfo('template_url') . '/images/';
	
	// Options array	
	$options = array();
		
	$options[] = array( "name" => __('General Settings','foghorn'),
                    	"type" => "heading");
						
	$options[] = array( "name" => __('Custom Logo','foghorn'),
						"desc" => __('Upload a logo for your site.','foghorn'),
						"id" => "logo",
						"type" => "upload");
						
	$options[] = array( "name" => __('Display Site Tagline','foghorn'),
						"desc" => __('Display the site tagline under the site title.','foghorn'),
						"id" => "tagline",
						"std" => "0",
						"type" => "checkbox");
	
	$options[] = array( "name" => __('Menu Position','foghorn'),
						"desc" => __('Check to display the menu underneath the logo and floated left.  Good for long menus.','foghorn'),
						"id" => "menu_position",
						"std" => "0",
						"type" => "checkbox");
						
	$options[] = array( "name" => __('Layout','foghorn'),
						"desc" => __('Select a site layout: sidebar right, sidebar left, or no sidebar.','foghorn'),
						"id" => "layout",
						"std" => "layout-2cr",
						"type" => "images",
						"options" => array(
						'layout-2cr' => $imagepath . '2cr.png',
						'layout-2cl' => $imagepath . '2cl.png',
						'layout-1c' => $imagepath . '1col.png',)
						);
						
	$options[] = array( "name" => __('Custom Footer Text','foghorn'),
						"desc" => __('Custom text for the footer of your theme.','foghorn'),
						"id" => "footer_text",
						"std" => __( 'Powered by ', 'foghorn' ) . '<a href="http://www.wordpress.org">WordPress</a> ' . __( 'and ', 'foghorn' ) . '<a href="https://github.com/devinsays/foghorn">' . __( 'Foghorn', 'foghorn' ) . '</a>',
						"type" => "textarea");
/////////// ADDED //////////////

	$options[] = array(
		'name' => __('Hub Settings', 'options_check'),
		'type' => 'heading');

	$options[] = array(
		'name' => __('Hub Title', 'options_check'),
		'desc' => __('Enter title for Hub, full name', 'options_check'),
		'id' => 'hub-title',
		'std' => '',
		'type' => 'text');

	$options[] = array(
		'name' => __('Hub Handle', 'options_check'),
		'desc' => __('Enter the handle for the hub: no spaces. ex. bankjustice', 'options_check'),
		'id' => 'hub-handle',
		'std' => '',
		'type' => 'text');

	$options[] = array(
		'name' => __('Hub Mailing List Link', 'options_check'),
		'desc' => __('Enter URL for mailing list signup', 'options_check'),
		'id' => 'hub-list',
		'std' => '',
		'type' => 'text');

	$options[] = array(
		'name' => __('Contact Person', 'options_check'),
		'desc' => __('Point person for Hub', 'options_check'),
		'id' => 'contact-person',
		'std' => '',
		'type' => 'text');

	$options[] = array(
		'name' => __('Admin Email', 'options_check'),
		'desc' => __('Admin Email', 'options_check'),
		'id' => 'contact-email',
		'std' => '',
		'type' => 'text');
		
	$options[] = array(
		'name' => __('Public Phone #', 'options_check'),
		'desc' => __('Phone number that is publically shared', 'options_check'),
		'id' => 'contact-phone',
		'std' => '',
		'type' => 'text');
		
	$options[] = array(
		'name' => __('Short Description', 'options_check'),
		'desc' => __('In 30 words or less...', 'options_check'),
		'id' => 'short-desc',
		'std' => '',
		'type' => 'textarea');

	$options[] = array(
		'name' => __('Hub Image', 'options_check'),
		'desc' => __('Upload an image for your hub', 'options_check'),
		'id' => 'hub-image',
		'type' => 'upload');

	/**
	 * For $settings options see:
	 * http://codex.wordpress.org/Function_Reference/wp_editor
	 *
	 * 'media_buttons' are not supported as there is no post to attach items to
	 * 'textarea_name' is set by the 'id' you choose
	 */

	$wp_editor_settings = array(
		'wpautop' => true, // Default
		'textarea_rows' => 5,
		'tinymce' => array( 'plugins' => 'wordpress' )
	);

	$options[] = array(
		'name' => __('Full hub description', 'options_check'),
		'desc' => __( '', 'options_check' ),
		'id' => 'full-desc',
		'type' => 'editor',
		'settings' => $wp_editor_settings );

	$options[] = array(
		'name' => __('Colorpicker', 'options_check'),
		'desc' => __('Color for your hub', 'options_check'),
		'id' => 'hub-color',
		'std' => '',
		'type' => 'color' );

	$options[] = array(
		'name' => __('Input Checkbox', 'options_check'),
		'desc' => __('Regular call?', 'options_check'),
		'id' => 'regular-call',
		'std' => '1',
		'type' => 'checkbox');

	$options[] = array(
		'name' => __('Hub Tools', 'options_check'),
		'type' => 'heading');

	$options[] = array(
		'name' => __('Call Information', 'options_check'),
		'desc' => __('Basics about call dates and times', 'options_check'),
		'id' => 'call-info',
		'std' => '',
		'type' => 'text');

	$options[] = array(
		'name' => __('Hub Web Page', 'options_check'),
		'desc' => __('Enter full URL, including http://', 'options_check'),
		'id' => 'hub-website',
		'std' => '',
		'type' => 'text');

	$options[] = array(
		'name' => __('Hub Facebook Page', 'options_check'),
		'desc' => __('Enter full URL, including http://', 'options_check'),
		'id' => 'hub-facebook',
		'std' => '',
		'type' => 'text');

	$options[] = array(
		'name' => __('Hub Facebook Group', 'options_check'),
		'desc' => __('Enter full URL, including http://', 'options_check'),
		'id' => 'hub-facebook-group',
		'std' => '',
		'type' => 'text');

	$options[] = array(
		'name' => __('Hub Twitter', 'options_check'),
		'desc' => __('Enter Twitter handle ONLY', 'options_check'),
		'id' => 'hub-twitter',
		'std' => '',
		'type' => 'text');

	$options[] = array(
		'name' => __('Remove Minutes Tab?', 'options_check'),
		'desc' => __('Check to remove the minutes tab', 'options_check'),
		'id' => 'minutes-tab',
		'std' => '0',
		'type' => 'checkbox');

	$options[] = array(
		'name' => __('Social Tab?', 'options_check'),
		'desc' => __('Works best with Facebook Page (rather than group) and twitter handle.', 'options_check'),
		'id' => 'social-tab',
		'std' => '0',
		'type' => 'checkbox');

	$options[] = array(
		'name' => __('O.NET Forum', 'options_check'),
		'desc' => __('Enter full URL, including http://', 'options_check'),
		'id' => 'hub-forum',
		'std' => '',
		'type' => 'text');

	$options[] = array(
		'name' => __('O.NET Wiki', 'options_check'),
		'desc' => __('Enter full URL, including http://', 'options_check'),
		'id' => 'hub-wiki',
		'std' => '',
		'type' => 'text');

	$options[] = array(
		'name' => __('O.NET Classifieds', 'options_check'),
		'desc' => __('Enter full URL, including http://', 'options_check'),
		'id' => 'hub-classifieds',
		'std' => '',
		'type' => 'text');
	return $options;
}