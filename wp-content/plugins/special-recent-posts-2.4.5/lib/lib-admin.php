<?php
/*
| --------------------------------------------------------
| File        : lib-admin.php
| Project     : Special Recent Posts PRO plugin for Wordpress
| Version     : 2.4.5
| Description : This file contains several functions
|               for SRP initialization and admin panel building.
| Author      : Luca Grandicelli
| Author URL  : http://www.lucagrandicelli.com
| Plugin URL  : http://codecanyon.net/item/special-recent-posts-pro/552356
| Copyright (C) 2011-2012  Luca Grandicelli
| --------------------------------------------------------
*/

/*
| ---------------------------------------------
| PLUGIN INIT FUNCTIONS
| ---------------------------------------------
*/

/*
| ---------------------------------------------
| This is the main initializing function.
| ---------------------------------------------
*/
function srp_init() {

	// Doing a global database options check.
	SpecialRecentPosts::srp_dboptions_check();
}
/*
| ---------------------------------------------
| This is the main admin initializing function.
| ---------------------------------------------
*/
function srp_admin_init() {
	
	// Registering Plugin admin stylesheet.
	wp_register_style('srp-admin-stylesheet' , SRP_PLUGIN_URL . SRP_ADMIN_CSS);
	
	// Registering Custom Js Init Script.
	wp_register_script('srp-custom-js-init'  , SRP_PLUGIN_URL . SRP_JS_INIT,  false, '2.4.4', true);
	
	// Enqueuing plugin admin widget stylesheet.
	wp_enqueue_style('srp-admin-stylesheet');
	
	// Forcing Loading jQuery.
	wp_enqueue_script('jquery');
	
	// Enqueuing Custom Js Init Script.
	wp_enqueue_script('srp-custom-js-init');
	
	// Adding a new action link.
	add_filter('plugin_action_links', 'srp_plugin_action_links', 10, 2);
}


/*
| -------------------------------------------------------
| This function adds new action links on plugin's page.
| -------------------------------------------------------
*/
function srp_plugin_action_links($links, $file) {

	// Checking if we're on the correct plugin file.
	if ($file == SRP_PLUGIN_MAINFILE) {
		$links[] = '<a href="options-general.php?page=special-recent-posts/lib/lib-admin.php">'.__('Settings').'</a>';
	}

	// Return new embedded link.
	return $links;
}

/*
| -------------------------------------------------------
| This function handles the plugin widget registration.
| -------------------------------------------------------
*/
function srp_install_widgets() {

	// Registering widget.
	register_widget("WDG_SpecialRecentPosts");
}

/*
| ---------------------------------------------
| PLUGIN COMPATIBILITY CHECK
| ---------------------------------------------
*/

/*
| -----------------------------------------------------------
| These functions display several error messages from the
| compatibility check process.
| -----------------------------------------------------------
*/
function srpcheck_phpver_error() {

	// Setting up new Error.
	$error = new WP_Error('broke', __("<strong>Special Recent Posts PRO Error!</strong> You're running an old version of PHP. In order for this plugin to work, you must enable your server with PHP support version 5.0.0+. Please contact your hosting/housing company support, and check how to enable it.</a>", SRP_TRANSLATION_ID));
	if (is_wp_error($error)) {
		echo "<div id=\"message\" class=\"error\"><p>" . $error->get_error_message() . "</p></div>";
	}
}

function srpcheck_gd_error() {
	
	// Setting up new Error.
	$error = new WP_Error('broke', __("<strong>Special Recent Posts PRO Error!</strong> GD libraries are not supported by your server. Please contact your hosting/housing company support, and check how to enable it. Without these libraries, thumbnails can't be properly resized and displayed.", SRP_TRANSLATION_ID));
	if (is_wp_error($error)) {
	   echo "<div id=\"message\" class=\"error\"><p>" . $error->get_error_message() . "</p></div>";
	}
}

function srpcheck_thumbnailsupport_error() {

	// Setting up new Error.
	$error = new WP_Error('broke', __("<strong>Special Recent Posts PRO Warning!</strong> Your theme doesn't support post thumbnail. The plugin will keep on working with first post images only. To enable post thumbnail support, please check the <a href='http://codex.wordpress.org/Post_Thumbnails'> Wordpress Documentation</a>", SRP_TRANSLATION_ID));
	if (is_wp_error($error)) {
	   echo "<div id=\"message\" class=\"updated\"><p>" . $error->get_error_message() . "</p></div>";
	}
}

function srpcheck_cache_exists_error() {
	
	// Setting up new Error.
	$error = new WP_Error('broke', __("<strong>Special Recent Posts PRO Warning!</strong> The Cache folder does not exist!. In order to use caching functionality you have to manually create a folder names 'cache' under the special-recent-posts/ folder.", SRP_TRANSLATION_ID));
	if (is_wp_error($error)) echo "<div id=\"message\" class=\"error\"><p>" . $error->get_error_message() . "</p></div>";
}

function srpcheck_cache_writable_error() {
	
	// Setting up new Error.
	$error = new WP_Error('broke', __("<strong>Special Recent Posts PRO Warning!</strong> The Cache folder is not writable. In order to use caching functionality you have to set the correct writing permissions on special-recent-posts/cache/ folder. E.G: 0755 or 0775", SRP_TRANSLATION_ID));
	if (is_wp_error($error)) echo "<div id=\"message\" class=\"error\"><p>" . $error->get_error_message() . "</p></div>";
}


/*
| ------------------------------------------------------------------
| This is the main function that performs the compatibility check.
| ------------------------------------------------------------------
*/
function check_plugin_compatibility() {

	// Checking for PHP version.
	$current_ver = phpversion();
	
	// Switching through version compare results.
    switch(version_compare($current_ver, SRP_REQUIRED_PHPVER)) {
		case -1:
			add_action('admin_notices', 'srpcheck_phpver_error'); 
		break;
			
        case 0:
        case 1:
		break;
    }
	
	// Checking for GD support. (required for the PHP Thumbnailer Class to work)
	if (!function_exists("gd_info")) {
		srpcheck_gd_error();
	}
	
	// Checking if the current wordpress theme support featured thumbnails.
	if (!current_theme_supports('post-thumbnails')) {
		srpcheck_thumbnailsupport_error(); 
	}
	
	// Checking if cache folder exixts and it's writable.
	if (!file_exists(SRP_PLUGIN_DIR . SRP_CACHE_DIR)) {
		srpcheck_cache_exists_error();
		
	} else if (!is_writable(SRP_PLUGIN_DIR . SRP_CACHE_DIR)) {
		srpcheck_cache_writable_error();
	}
}

/*
| ---------------------------------------------
| AMIN MENUS PAGE AND STYLESHEETS
| ---------------------------------------------
*/

/*
| -----------------------------------------
| This is the main Admin setup function.
| -----------------------------------------
*/
function srp_admin_setup() {
	
	// Adding SubMenu Page.
	$page = add_submenu_page('options-general.php', __('Special Recent Posts PRO - Settings Page', 'Special Recent Posts PRO - Settings Page'), __('Special Recent Posts PRO', 'Special Recent Posts PRO'), 'administrator', __FILE__, 'srp_admin_menu_options');
	
    // Using registered $page handle to hook stylesheet loading.
    add_action('admin_print_styles-' . $page, 'srp_admin_plugin_add_style');
}


/*
| ----------------------------------------------------
| This is the main function to add admin stylesheet.
| ----------------------------------------------------
*/
function srp_admin_plugin_add_style() {
	
	// Enqueuing plugin admin stylesheet.
	wp_enqueue_style('srp-admin-stylesheet');
}


/*
| ----------------------------------------------------
| This is the main function to add widget stylesheet
| into the current theme.
| ----------------------------------------------------
*/
function srp_front_head() {
	
	// Doing a global database options check.
	SpecialRecentPosts::srp_dboptions_check();
	
	// Importing global default options array.
	$srp_current_options = get_option('srp_plugin_options');
	
	// Checking for SRP Stylesheet enabled.
	if ($srp_current_options["srp_disable_theme_css"] != "yes") {
		
		// Registering Front End CSS.
		wp_register_style('srp-front-stylesheet' , SRP_PLUGIN_URL . SRP_FRONT_CSS);
		
		// Enqueuing Front End CSS.
		wp_enqueue_style('srp-front-stylesheet');
	
		// Adding IE7 Fix.
		echo "<!--[if IE 7]>";
		echo "<link rel='stylesheet' id='css-ie-fix' href='" . SRP_PLUGIN_URL . SRP_IEFIX_CSS . "' type='text/css' media='all' /> ";
		echo "<![endif]-->";
	}
}

/*
| ---------------------------------------------
| BUILDING PLUGIN OPTION PAGE
| ---------------------------------------------
*/

/*
| --------------------------------------------------------------
| This is the main function that builds the plugin admin page.
| --------------------------------------------------------------
*/
function srp_admin_menu_options() {

	// Checking if we have the manage option permission enabled.
	if (!current_user_can('manage_options'))  {
		wp_die(__('You do not have sufficient permissions to access this page.', SRP_TRANSLATION_ID));
	}
	
	// For first, let's check if there is some kind of compatibility error.
	check_plugin_compatibility();
?>
	<!-- Generating Option Page HTML. -->
	<div class="wrap">
		<div id="srp-admin-container">
			<?php
			
				// Updating and validating data/POST Check.
				srp_update_data($_POST, get_option('srp_plugin_options'));
				
				// Importing global default options array.
				$srp_current_options = get_option('srp_plugin_options');
			?>
			
			<!-- BOF Title and Description section. -->
			<h2 class="srp_admin_headertitle"><?php _e('Special Recent Posts PRO (version ' . SRP_PLUGIN_VERSION . ') - Settings Page', SRP_TRANSLATION_ID); ?></h2>
			<div class="srp_option_header_l1">
			<?php _e('<strong>Welcome to the Special Recent Posts PRO Admin Panel.</strong><br /> In this page you can configure the main settings for the Special Recent Posts PRO plugin. Keep in mind that these are basic options. 
			Special options apply for each widget instance, shortcode or PHP code to ensure an high level of customization.<br />
			Go to Widget Page and drag the Special Recent Posts PRO widget to see additional options available.', SRP_TRANSLATION_ID); ?>
			</div>
			<!-- EOF Title and Description section. -->
				
			<!-- BOF Admin Tabs -->
			<ul id="srp_widget_tabs">
				<li>
					<a onClick="javascript:srpTabsSwitcher(1);" class="srp_tab_1 active" title="<?php _e('General Settings', SRP_TRANSLATION_ID); ?>" href="#"><?php _e('General Settings', SRP_TRANSLATION_ID); ?></a>
				</li>
				
				<li>
					<a onClick="javascript:srpTabsSwitcher(2);" class="srp_tab_2" title="<?php _e('Cache Settings', SRP_TRANSLATION_ID); ?>" href="#"><?php _e('Cache Settings', SRP_TRANSLATION_ID); ?></a>
				</li>
			</ul>
			<!-- EOF Admin Tabs -->
				
			
			<div class="metabox-holder" id="srp_tab1">
				<!--  Open Form. -->
				<form id="srp_admin_form" name="srp_admin_form" action="" method="POST">
					<input type="hidden" value="yes" name="srp_dataform">
					<input type="hidden" value="<?php echo $srp_current_options["srp_version"]; ?>" name="srp_version">
					<input type="hidden" value="<?php echo $srp_current_options["srp_global_post_limit"]; ?>" name="srp_global_post_limit">
					<div class="postbox">
						
						<h3><?php _e('General Settings', SRP_TRANSLATION_ID);?></h3>

						<!-- BOF Left Box. -->
						<div id="srp-admin-leftcontent">
							<p>
							<?php _e('This is the General Settings page. Here you can customize all of the settings that globally apply to the plugin.', SRP_TRANSLATION_ID); ?>
							</p>
							
							<dl>
								<dt>
									<strong><?php _e('Enable Compatibility Mode', SRP_TRANSLATION_ID); ?></strong>
								</dt>
								<dd>
									<?php _e('This option enables some compatibility features that change the behaviour of the SRP plugin, in order to work seamlessly with other plugins.
									If you are experiencing problems with Special Recent Posts PRO and other plugins, you might want to disable this option.', SRP_TRANSLATION_ID); ?>
								</dd>
								
								<dt>
									<strong><?php _e('Log Errors on Screen', SRP_TRANSLATION_ID); ?></strong>
								</dt>
								<dd>
									<?php _e('This option enables the error logging on screen. It\'s useful to understand where something has gone wrong during the image generation process.', SRP_TRANSLATION_ID); ?>
								</dd>
								
								<dt>
									<strong><?php _e('No-Posts Image Placeholder', SRP_TRANSLATION_ID); ?></strong>
								</dt>
								<dd>
									<?php _e('This is the default image that appears when no other images are available inside a post. 
									You can use the one you prefer, by simply inserting the full URL of the image. 
									If you leave this field empty, the default no-image placeholder will be loaded.', SRP_TRANSLATION_ID); ?>
								</dd>
								
								<dt>
									<strong><?php _e('Disable Plugin CSS?', SRP_TRANSLATION_ID); ?></strong>
								</dt>
								<dd>
									<?php _e('This option enables/disables the built-in widget stylesheet. Set this option to "Yes" if you wish to use your own style.', SRP_TRANSLATION_ID); ?>
								</dd>
								
								<dt>
									<strong><?php _e('Theme CSS', SRP_TRANSLATION_ID); ?></strong>
								</dt>
								<dd>
									<?php _e('This is the global Stylesheet. All layout changements must be done here. 
									Consider that in some cases, your custom theme CSS might override these settings. 
									In this case, edit this stylesheet using the <i>"<strong>!important"</strong></i> attribute beside each rule to override your theme css rules.', SRP_TRANSLATION_ID); ?>
								</dd>
							</dl>
						</div>
						<!-- EOF Left Box. -->
						
						<!-- BOF Right Box. -->
						<div id="srp-admin-rightcontent">
							<ul>
								<li>
									<!--BOF Compatibility Mode -->
									<label for="srp_compatibility_mode"><?php _e('Enable Compatibility Mode', SRP_TRANSLATION_ID); ?></label>
									<span class="srp-smalltext"><?php _e('Switch this to No if you\'re experiencing visualization problems or other kind of incompatibility with other plugins.', SRP_TRANSLATION_ID); ?></span><br />
									<select id="srp_compatibility_mode" name="srp_compatibility_mode">
										<option value="yes" <?php selected($srp_current_options["srp_compatibility_mode"], 'yes'); ?>><?php _e('Yes', SRP_TRANSLATION_ID); ?></option>
										<option value="no" <?php selected($srp_current_options["srp_compatibility_mode"], 'no'); ?>><?php _e('No', SRP_TRANSLATION_ID); ?></option>
									</select>
									<!--EOF Compatibility Mode -->
								</li>
								
								<li>
									<!--BOF Log Errors on Screen -->
									<label for="srp_log_errors_screen"><?php _e('Log Errors on Screen?', SRP_TRANSLATION_ID); ?></label>
									<span class="srp-smalltext"><?php _e('Switch this to Yes if you want to log potential errors or warnings on screen.', SRP_TRANSLATION_ID); ?></span><br />
									<select id="srp_log_errors_screen" name="srp_log_errors_screen">
										<option value="yes" <?php selected($srp_current_options["srp_log_errors_screen"], 'yes'); ?>><?php _e('Yes', SRP_TRANSLATION_ID); ?></option>
										<option value="no" <?php selected($srp_current_options["srp_log_errors_screen"], 'no'); ?>><?php _e('No', SRP_TRANSLATION_ID); ?></option>
									</select>
									<!--EOF Log Errors on Screen -->
								</li>
								
								
								<!--BOF Thumbnail Custom URL -->
								<li>
									<label for="srp_noimage_url"><?php _e('No-Posts Image Placeholder', SRP_TRANSLATION_ID); ?></label>
									<input type="text" id="srp_noimage_url" name="srp_noimage_url" value="<?php echo stripslashes($srp_current_options['srp_noimage_url']); ?>" size="90" /><br />
									<span class="srp-smalltext"><?php _e('Enter the absolute url of the image placeholder. Default size: 100px x 100px.', SRP_TRANSLATION_ID); ?></span>
								</li>
								<!--EOF Thumbnail Custom URL -->
								
								<!--BOF Disable Theme CSS -->
								<li>
									<label for="srp_disable_theme_css"><?php _e('Disable Plugin CSS?', SRP_TRANSLATION_ID); ?></label>
									<select id="srp_disable_theme_css" name="srp_disable_theme_css">
										<option value="yes" <?php selected($srp_current_options["srp_disable_theme_css"], 'yes'); ?>><?php _e('Yes', SRP_TRANSLATION_ID); ?></option>
										<option value="no" <?php selected($srp_current_options["srp_disable_theme_css"], 'no'); ?>><?php _e('No', SRP_TRANSLATION_ID); ?></option>
									</select>
								</li>
								<!--EOF Disable Theme CSS -->								
							</ul>
						</div>
						<!-- EOF Right Box. -->
						
						<div class="clearer"></div>
						
					</div><!-- EOF postbox. -->
					<input type="submit" name="submit" class="button-primary" value="<?php _e('Save Options', SRP_TRANSLATION_ID); ?>" />
				</form> <!--EOF Form. -->
			</div><!-- EOF metabox-holder. -->
		
			<div class="metabox-holder" id="srp_tab2">
				<form id="srp-cache-flush-form" action="" method="POST">
					<div class="postbox">
						
						<h3><?php _e('Cache Settings', SRP_TRANSLATION_ID);?></h3>
						<!-- BOF Left Box. -->
						<div id="srp-admin-leftcontent">
							<dl>
								<dt>
									<strong><?php _e('Empty Cache Folder', SRP_TRANSLATION_ID); ?></strong>
								</dt>
								<dd>
									<?php _e('Click this button to empty the thumbnails cache folder, which is located at special-recent-posts/cache/', SRP_TRANSLATION_ID); ?>
								</dd>
						</div>
						<!-- EOF Left Box. -->
						
						<!-- BOF Right Box. -->
						<div id="srp-admin-rightcontent">
							
							<input type="hidden" value="yes" name="cache_flush">
							<input type="submit" value="<?php _e('Empty Cache Folder', SRP_TRANSLATION_ID); ?>" class="button-primary">
							
							<!-- EOF Cache Flush Section -->
						</div>

						<div class="clearer"></div>		
					</div><!-- EOF postbox. -->
				</form>
			</div><!-- EOF metabox-holder. -->
		</div> <!-- EOF srp_adm_container -->
	</div> <!-- EOF Wrap. -->
<?php
}

/*
| -------------------------------------------------------
| This is the main function to update form option data.
| -------------------------------------------------------
*/
function srp_update_data($data, $srp_current_options) {

	// Checking if form has been submitted.
	if (isset($_POST['srp_dataform'])) {
	
		// Loading global default plugin values.
		global $srp_default_plugin_values;
	
		// Removing the "submit" $_POST entry.
		unset($data['srp_dataform']);
		
		// Removing the "submit" $_POST entry.
		unset($data['submit']);
		
		// Validating text fields.		
		foreach ($data as $k => $v) {
			
			// Assigning global default value to noimage placeholder field, if this is empty.
			if ((empty($v)) && ($k == "srp_noimage_url")) $data[$k] = $srp_default_plugin_values[$k];
		}
		
		// Updating WP Option with new $_POST data.
		update_option('srp_plugin_options', $data);

		// Displaying "save settings" message.
		echo "<div id=\"message\" class=\"updated\"><p><strong>" . __('Settings Saved', SRP_TRANSLATION_ID) . "</strong></p></div>";
	}
	
	// Checking for Cache Flush Option.
	if (isset($_POST["cache_flush"]) && $_POST["cache_flush"] == "yes") {
		
		// Setting up cache folder path.
		$mydir = SRP_PLUGIN_DIR . SRP_CACHE_DIR;
		
		// Initializing directory class.
		$d = dir($mydir); 
		
		// Reading cache folder content.
		while($entry = $d->read()) { 
			
			// Checking if the directory is empty.
			if ($entry!= "." && $entry!= "..") { 
				
				// Deleting files.
				unlink(SRP_PLUGIN_DIR  . SRP_CACHE_DIR . $entry);
			} 
		}
		
		// Closing fodler class connection.
		$d->close();
		
		// Displaying status message.
		echo "<div id=\"message\" class=\"updated\"><p><strong>" . __('Cache Folder Cleaned', SRP_TRANSLATION_ID) . "</strong></p></div>";
	}
}