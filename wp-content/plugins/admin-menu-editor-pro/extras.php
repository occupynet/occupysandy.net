<?php

class wsMenuEditorExtras {
	/** @var WPMenuEditor */
	private $wp_menu_editor;

	private $framed_pages;
	private $ozhs_new_window_menus;
	protected $export_settings;
	
	private $slug = 'admin-menu-editor-pro';
	private $secret = '1kuh432KwnufZ891432uhg32';

	private $disable_virtual_caps = false;

	private $username_cache = array();

	/**
	 * Class constructor.
	 *
	 * @param WPMenuEditor $wp_menu_editor
	 * @return void
	 */
	function wsMenuEditorExtras($wp_menu_editor){
		$this->wp_menu_editor = $wp_menu_editor;

		//Allow the usage of shortcodes in the admin menu
		add_filter('custom_admin_menu', array(&$this, 'do_shortcodes'));
		add_filter('custom_admin_submenu', array(&$this, 'do_shortcodes'));
		
		//Add some extra shortcodes of our own
		$shortcode_callback = array(&$this, 'handle_shortcode');
		$info_shortcodes = array(
			'wp-wpurl',    //WordPress address (URI), as returned by get_bloginfo()
			'wp-siteurl',  //Blog address (URI)
			'wp-admin',    //Admin area URL (with a trailing slash)
			'wp-name',     //Weblog title
			'wp-version',  //Current WP version
		);
		foreach($info_shortcodes as $tag){
			add_shortcode($tag, $shortcode_callback);
		}
		
		//Flag menus that are set to open in a new window so that we can later find
		//and modify them with JS. This is necessary because there is no practical 
		//way to intercept and modify the menu HTML with PHP alone. 
		add_filter('custom_admin_menu', array(&$this, 'flag_new_window_menus'));
		add_filter('custom_admin_submenu', array(&$this, 'flag_new_window_menus'));
		//Output the menu-modification JS after the menu has been generated.
		//'admin_notices' is, AFAIK, the action that fires the soonest after menu
		//output has been completed, so we use that.
		add_action('admin_notices', array(&$this, 'fix_flagged_menus'));
		//A list of IDs for menu items output by Ozh's Admin Drop Down Menu
		//(those can't be modified the usual way because Ozh's plugin strips tags
		//from submenu titles).
		$this->ozhs_new_window_menus = array();
		
		//Handle pages that need to be displayed in a frame.
		$this->framed_pages = array();		
		add_filter('custom_admin_menu', array(&$this, 'create_framed_menu'));
		add_filter('custom_admin_submenu', array(&$this, 'create_framed_item'), 10, 2);

		//Handle submenu separators.
		add_filter('custom_admin_submenu', array($this, 'create_submenu_separator'));
		
		//Import/export settings
		$this->export_settings = array(
		 	'max_file_size' => 1024*512,
		    'file_extension' => 'dat',
		    'old_format_string' => 'wsMenuEditor_ExportFile',
		);
		
		//Insert the import and export dialog HTML into the editor's page
		add_action('admin_menu_editor_footer', array(&$this, 'menu_editor_footer'));
		//Handle menu downloads and uploads
		add_action('admin_menu_editor_header', array(&$this, 'menu_editor_header'));
		//Handle export requests
		add_action( 'wp_ajax_export_custom_menu', array(&$this,'ajax_export_custom_menu') );
		//Add the "Import" and "Export" buttons
		add_action('admin_menu_editor_sidebar', array(&$this, 'add_extra_buttons'));
		
		add_filter('admin_menu_editor-self_page_title', array(&$this, 'pro_page_title'));
		add_filter('admin_menu_editor-self_menu_title', array(&$this, 'pro_menu_title'));
		
		//Tack on some extra validation data when querying our custom update API
		//add_filter('custom_plugins_api_options', array(&$this, 'custom_api_options'), 10, 3);

		//Let other components know we're Pro.
		add_filter('admin_menu_editor_is_pro', array(&$this, 'is_pro_version'));

		//Add menu item drop zones to the top-level and sub-menu containers.
		add_action('admin_menu_editor_container', array($this, 'output_menu_dropzone'), 10, 1);

		/**
		 * Access management extensions.
		 */

		//Allow usernames to be used in capability checks. Syntax : "user:user_login"
		add_filter('user_has_cap', array(&$this, 'hook_user_has_cap'), 10, 3);

		//Enable advanced capability operations (OR, AND, NOT) for internal use.
		add_filter('admin_menu_editor-current_user_can', array($this, 'grant_computed_caps_to_current_user'), 10, 2);

		//Custom per-role and per-user access settings (distinct from the "extra capability" field.
		add_filter('custom_admin_menu_capability', array($this, 'apply_custom_access'), 10, 2);

		//Role access: Grant virtual capabilities to roles/users that need them to access certain menus.
		add_filter('user_has_cap', array($this, 'grant_virtual_caps_to_user'), 10, 3);
		add_filter('role_has_cap', array($this, 'grant_virtual_caps_to_role'), 10, 3);

		//License management
		add_filter('wslm_license_ui_title-admin-menu-editor-pro', array($this, 'license_ui_title'));
		add_action('wslm_license_ui_logo-admin-menu-editor-pro', array($this, 'license_ui_logo'));
	}
	
  /**
   * Process shortcodes in menu fields
   *
   * @param array $item
   * @return array
   */
	function do_shortcodes($item){
		foreach($item as $field => $value){
			if ( is_string($value) && (strpos($value, '[') !== false) ){
				$item[$field] = do_shortcode($value);
			}
		}
		return $item;
	}

  /**
   * Get the value of one of our extra shortcodes
   *
   * @param array $atts Shortcode attributes (ignored)
   * @param string $content Content enclosed by the shortcode (ignored)
   * @param string $code 
   * @return string Shortcode will be replaced with this value
   */
	function handle_shortcode($atts, $content = null, $code = ''){
		//The shortcode tag can be either $code or the zeroth member of the $atts array.
		if ( empty($code) ){
			$code = isset($atts[0]) ? $atts[0] : '';
		}
		
		$info = '['.$code.']'; //Default value
		switch($code){
			case 'wp-wpurl':
				$info = get_bloginfo('wpurl');
				break;
				
			case 'wp-siteurl':
				$info = get_bloginfo('url');
				break;
				
			case 'wp-admin':
				$info = admin_url();
				break;
				
			case 'wp-name':
				$info = get_bloginfo('name');
				break;
				
			case 'wp-version':
				$info = get_bloginfo('version');
				break;
		}
		
		return $info;
	}
	
	/**
	 * Flag menus (and menu items) that are set to open in a new window
	 * so that they can be identified later. 
	 * 
	 * Adds a <span class="ws-new-window-please"></span> element to the title
	 * of each detected menu.  
	 * 
	 * @param array $item
	 * @return array
	 */
	function flag_new_window_menus($item){
		$open_in = ameMenuItem::get($item, 'open_in', 'same_window');
		if ( $open_in == 'new_window' ){
			$old_title = ameMenuItem::get($item, 'menu_title', '');
			$item['menu_title'] = $old_title . '<span class="ws-new-window-please" style="display:none;"></span>';
			
			//For compatibility with Ozh's Admin Drop Down menu, record the link ID that will be
			//assigned to this item. This lets us modify it later.
			if ( function_exists('wp_ozh_adminmenu_sanitize_id') ){
				$subid = 'oamsub_'.wp_ozh_adminmenu_sanitize_id(
					ameMenuItem::get($item, 'file', '')
				); 
				$this->ozhs_new_window_menus[] = '#' . str_replace(
					array(':', '&'), 
					array('\\\\:', '\\\\&'), 
					$subid
				);
			}
		}
				
		return $item;	
	}
	
	/**
	 * Output a piece of JS that will find flagged menu links and make them 
	 * open in a new window. 
	 * 
	 * @return void
	 */
	function fix_flagged_menus(){
		?>
		<script type="text/javascript">
		(function($){
			$('#adminmenu span.ws-new-window-please, #ozhmenu span.ws-new-window-please').each(function(index){
				var marker = $(this);
				//Add target="_blank" to the enclosing link
				marker.parents('a').first().attr('target', '_blank');
				//And to the menu image link, too (only for top-level menus)
				marker.parent().parent().find('> .wp-menu-image a').attr('target', '_blank');
				//Get rid of the marker
				marker.remove();
			});
			
			<?php if ( !empty($this->ozhs_new_window_menus) ): ?>
			
			$('<?php echo implode(', ', $this->ozhs_new_window_menus); ?>').each(function(index){
				//Add target="_blank" to the link
				$(this).find('a').attr('target', '_blank');
			});
											
			<?php endif; ?>
		})(jQuery);
		</script>
		<?php
	}  
	
	/**
	 * Intercept menus that need to be displayed in an IFrame.
	 * 
	 * Here's how this works : each item that needs to be displayed in an IFrame 
	 * gets added as a new menu (or submenu) using the standard WP plugin API. 
	 * This ensures that the myriad undocumented data structures that WP employs 
	 * for menu generation get populated correctly. 
	 * 
	 * The reason why this doesn't lead to menu duplication is that the global $menu
	 * and $submenu arrays are thrown away and replaced with custom-generated ones 
	 * shortly afterwards. The modified menu entry returned by this function becomes 
	 * part of that custom menu.
	 * 
	 * All items added in this way have the same callback function - wsMenuEditorExtras::display_framed_page()
	 * 
	 * @param array $item
	 * @return array
	 */
	function create_framed_menu($item){
		if ( $item['open_in'] == 'iframe' ){
			$slug = 'framed-menu-' . md5($item['file']);//MD5 should be unique enough
			$this->framed_pages[$slug] = $item; //Used by the callback function
			
			//Default to using menu title for page title, if no custom title specified 
			if ( empty($item['page_title']) ) {
				$item['page_title'] = $item['menu_title'];
			}
			
			//Add a virtual menu. The menu record created by add_menu_page will be
			//thrown away; what matters is that this populates other structures
			//like $_registered_pages.
			add_menu_page(
				$item['page_title'],
				$item['menu_title'],
				$item['access_level'],
				$slug,
				array(&$this, 'display_framed_page')
			);
			
			//Change the slug to our newly created page.
			$item['file'] = $slug;
		}
		
		return $item;
	}
	
	/**
	 * Intercept menu items that need to be displayed in an IFrame.
	 * 
	 * @see wsMenuEditorExtras::create_framed_menu()
	 * 
	 * @param array $item
	 * @param string $parent_file
	 * @return array
	 */
	function create_framed_item($item, $parent_file = null){
		if ( ($item['open_in'] == 'iframe') && !empty($parent_file) ){

			$slug = 'framed-menu-item-' . md5($item['file'] . '|' . $parent_file);
			$this->framed_pages[$slug] = $item;
			
			if ( empty($item['page_title']) ) {
				$item['page_title'] = $item['menu_title'];
			}
			add_submenu_page(
				$parent_file,
				$item['page_title'],
				$item['menu_title'],
				$item['access_level'],
				$slug,
				array(&$this, 'display_framed_page')
			);
			
			$item['file'] = $slug;
		}
		
		return $item;
	}
	
	/**
	 * Display a page in an IFrame.
	 * This callback is used by all menu items that are set to open in a frame.
	 * 
	 * @return void
	 */
	function display_framed_page(){
		global $plugin_page;
		
		if ( isset($this->framed_pages[$plugin_page]) ){
			$item = $this->framed_pages[$plugin_page];
		} else {
			return;
		}
		
		if ( !current_user_can($item['access_level']) ){
			echo "You do not have sufficient permissions to view this page.";
			return;
		}
		
		$heading = !empty($item['page_title'])?$item['page_title']:$item['menu_title']; 
		?>
		<div class="wrap">
		<h2><?php echo $heading; ?></h2>
		<!--suppress HtmlUnknownAttribute "frameborder" is in fact allowed here -->
			<iframe
			src="<?php echo esc_attr($item['file']); ?>" 
			style="border: none; width: 100%; min-height:300px;"
			id="ws-framed-page"
			frameborder="0" 
		></iframe>
		</div>
		<script type="text/javascript">
		function wsResizeFrame(){
			var $ = jQuery;
			var footer = $('#footer, #wpfooter');
			var frame = $('#ws-framed-page');
			frame.height( footer.offset().top - frame.offset().top - footer.outerHeight() );
		}
		
		jQuery(function($){
			wsResizeFrame();
			setTimeout(wsResizeFrame, 1000);
		});
		</script>		
		<?php
	}
	
	/**
	 * Output the HTML for import and export dialogs.
	 * Callback for the 'menu_editor_footer' action.
	 * 
	 * @return void
	 */
	function menu_editor_footer(){
		?>
		<div id="export_dialog" title="Export">
	<div class="ws_dialog_panel">
		<div id="export_progress_notice">
			<img src="<?php echo plugins_url('images/spinner.gif', __FILE__); ?>" alt="wait">
			Creating export file...
		</div>
		<div id="export_complete_notice">
			Click the "Download" button below to download the exported admin menu to your computer.
		</div>
	</div>
	<div class="ws_dialog_buttons">
		<a class="button-primary" id="download_menu_button" href="#">Download Export File</a>
		<input type="button" name="cancel" class="button" value="Close" id="ws_cancel_export">
	</div>
</div>

<div id="import_dialog" title="Import">
	<form id="import_menu_form" action="<?php echo esc_attr(admin_url('options-general.php?page=menu_editor&noheader=1')); ?>" method="post">
		<input type="hidden" name="action" value="upload_menu">
		
		<div class="ws_dialog_panel">
			<div id="import_progress_notice">
				<img src="<?php echo plugins_url('images/spinner.gif', __FILE__); ?>" alt="wait">
				Uploading file...
			</div>
			<div id="import_progress_notice2">
				<img src="<?php echo plugins_url('images/spinner.gif', __FILE__); ?>" alt="wait">
				Importing menu...
			</div>
			<div id="import_complete_notice">
				Import Complete!
			</div>
			
			
			<div class="hide-when-uploading">
				Choose an exported menu file (.<?php echo $this->export_settings['file_extension']; ?>) 
				to import: 
				
				<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo intval($this->export_settings['max_file_size']); ?>"> 
				<input type="file" name="menu" id="import_file_selector" size="35">
			</div>
		</div>
		
		<div class="ws_dialog_buttons">
			<input type="submit" name="upload" class="button-primary hide-when-uploading" value="Upload File" id="ws_start_import">
			<input type="button" name="cancel" class="button" value="Close" id="ws_cancel_import">
		</div>
	</form>
</div>

<script type="text/javascript">
/** @namespace wsEditorData */
wsEditorData.wsMenuEditorPro = true;

wsEditorData.exportMenuNonce = "<?php echo esc_js(wp_create_nonce('export_custom_menu'));  ?>";
wsEditorData.menuUploadHandler = "<?php echo ('options-general.php?page=menu_editor&noheader=1'); ?>";
wsEditorData.importMenuNonce = "<?php echo esc_js(wp_create_nonce('import_custom_menu'));  ?>";
</script>
		<?php
	}
	
    /**
     * Prepare a custom menu for export. 
     *
     * Expects menu data to be in $_POST['data'].
     * Outputs a JSON-encoded object with three fields : 
     * 	download_url - the URL that can be used to download the exported menu.
     *	filename - export file name.
     *	filesize - export file size (in bytes).
     *
     * If something goes wrong, the response object will contain an 'error' field with an error message.
     *
     * @return void
     */
	function ajax_export_custom_menu(){
		$wp_menu_editor = $this->wp_menu_editor;
		if (!$wp_menu_editor->current_user_can_edit_menu() || !check_ajax_referer('export_custom_menu', false, false)){
			die( $wp_menu_editor->json_encode( array(
				'error' => __("You're not allowed to do that!", 'admin-menu-editor') 
			)));
		}
		
		//Prepare the export record.
		$export = $this->get_exported_menu();
		$export['total']++;               //Export counter. Could be used to make download URLs unique.
		$export['menu'] = $_POST['data']; //Save the menu structure. Note the lack of validation.
		
		//Include the blog's domain name in the export filename to make it easier to 
		//distinguish between multiple export files.
		$siteurl = get_bloginfo('url');
		$domain = @parse_url($siteurl);
		$domain = isset($domain['host']) ? ($domain['host'] . ' ') : '';
		
		$export['filename'] = sprintf(
			'%sadmin menu (%s).dat',
			$domain,
			date('Y-m-d')
		);
		
		//Store the modified export record. The plugin will need it when the user 
		//actually tries to download the menu. 
		$this->set_exported_menu($export);
		
		$download_url = sprintf(
			'options-general.php?page=menu_editor&noheader=1&action=download_menu&export_num=%d',
			$export['total']
		);
		$download_url = admin_url($download_url);
		
		$result = array(
			'download_url' => $download_url,
			'filename' => $export['filename'],
			'filesize' => strlen($export['menu']),
		);
		
		die($wp_menu_editor->json_encode($result));
	}
	
    /**
     * Get the current exported record
     *
     * @return array
     */
	function get_exported_menu(){
		$user = wp_get_current_user();
		$exports = get_metadata('user', $user->ID, 'custom_menu_export', true);
		
		$defaults = array(
			'total' => 0,
			'menu' => '',
			'filename' => '',
		);
		
		if ( !is_array($exports) ){
			$exports = array();
		}
		
		return array_merge($defaults, $exports);
	}
	
    /**
     * Store the export record.
     *
     * @param array $export
     * @return bool
     */
	function set_exported_menu($export){
		$user = wp_get_current_user();
		return update_metadata('user', $user->ID, 'custom_menu_export', $export);
	}
	
	/**
	 * Handle menu uploads and downloads.
	 * This is a callback for the 'admin_menu_editor_header' action.
	 * 
	 * @param string $action
	 * @return void
	 */
	function menu_editor_header($action = ''){
		$wp_menu_editor = $this->wp_menu_editor;
		
		//Handle menu download requests
		if ( $action == 'download_menu' ){
			$export = $this->get_exported_menu();
			if ( empty($export['menu']) || empty($export['filename']) ){
				die("Exported data not found");
			}
			
			//Force file download
		    header("Content-Description: File Transfer");
		    header('Content-Disposition: attachment; filename="' . $export['filename'] . '"');
		    header("Content-Type: application/force-download");
		    header("Content-Transfer-Encoding: binary");
		    header("Content-Length: " . strlen($export['menu']));
		    
		     /* The three lines below basically make the download non-cacheable */
			header("Cache-control: private");
			header("Pragma: private");
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		    
		    echo $export['menu'];
			
			die();
		
		//Handle menu uploads
		} elseif ( $action == 'upload_menu' ) {	
		    
		    header('Content-Type: text/html');
		    
		    if ( empty($_FILES['menu']) ){
				echo $wp_menu_editor->json_encode(array('error' => "No file specified"));
				die();
			}
			
			$file_data = $_FILES['menu'];
			if ( filesize($file_data['tmp_name']) > $this->export_settings['max_file_size'] ){
				echo '<textarea cols="50" rows="5">', $wp_menu_editor->json_encode(array('error' => "File too big")), '</textarea>';
				die();
			}

			$file_contents = file_get_contents($file_data['tmp_name']);
			
			//Check if this file could plausibly contain an exported menu
			if ( strpos($file_contents, $this->export_settings['old_format_string']) !== false ){

				//This is an exported menu in the old format.
				$data = $wp_menu_editor->json_decode($file_contents, true);
				if ( !(isset($data['menu']) && is_array($data['menu'])) ) {
					echo '<textarea cols="50" rows="5">', $wp_menu_editor->json_encode(array('error' => "Unknown or corrupted file format")), '</textarea>';
					die();
				}

				try {
					$menu = ameMenu::load_array($data['menu']);
				} catch (InvalidMenuException $ex) {
					echo '<textarea cols="50" rows="5">', $wp_menu_editor->json_encode(array('error' => $ex->getMessage())), '</textarea>';
					die();
				}

			} else {
				if (strpos($file_contents, ameMenu::format_name) !== false) {

					//This is an export file in the new format.
					try {
						$menu = ameMenu::load_json($file_contents);
					} catch (InvalidMenuException $ex) {
						echo '<textarea cols="50" rows="5">', $wp_menu_editor->json_encode(array('error' => $ex->getMessage())), '</textarea>';
						die();
					}

				} else {

					//This is an unknown file.
					echo '<textarea cols="50" rows="5">', $wp_menu_editor->json_encode(array('error' => "Unknown file format")), '</textarea>';
					die();

				}
			}

			//Merge the imported menu with the current one.
			$menu['tree'] = $wp_menu_editor->menu_merge($menu['tree']);

			//Everything looks okay, send back the menu data
			die ( '<textarea>' . (ameMenu::to_json($menu)) . '</textarea>' );
		}
	}
	
	/**
	 * Output the "Import" and "Export" buttons.
	 * Callback for the 'admin_menu_editor_sidebar' action.
	 * 
	 * @return void
	 */
	function add_extra_buttons(){
		?>
		<input type="button" id='ws_export_menu' value="Export" class="button ws_main_button" title="Export current menu" />
		<input type="button" id='ws_import_menu' value="Import" class="button ws_main_button" />
		<?php
	}
	
	function hook_user_has_cap($allcaps, $caps, $args){
		//Add "user:user_login" to the user's capabilities. This makes it possible to restrict
		//menu access on a per-user basis.
		
		//The second entry of the $args array should be the user ID
		if ( count($args) < 2 ){
			return $allcaps;
		}
		$user_id = intval($args[1]);
		
		//Get the username & add it as a valid cap
		$username = $this->get_username_by_id($user_id);
		if ( $username !== null ){
			$allcaps['user:' . $username] = true;
		}
				
		return $allcaps;
	}

	private function get_username_by_id($user_id) {
		if ( !array_key_exists($user_id, $this->username_cache) ) {
			$user = get_userdata($user_id);
			if ( $user && isset($user->user_login) && is_string($user->user_login) ){
				$this->username_cache[$user_id] = $user->user_login;
			} else {
				$this->username_cache[$user_id] = null;
			}
		}
		return $this->username_cache[$user_id];
	}

	/**
	 * Apply custom per-role and per-user access settings to a menu item.
	 *
	 * If the user can't access this menu, this method will change the required
	 * capability to "do_not_allow". Otherwise, it will be left unmodified.
	 *
	 * Callback for the 'custom_admin_menu_capability' filter.
	 *
	 * @param string $required_capability
	 * @param array $item
	 * @return string
	 */
	public function apply_custom_access($required_capability, $item) {
		if ( ! $this->current_user_is_granted_access($item) ) {
			$required_capability = 'do_not_allow';
		}
		return $required_capability;
	}

	/**
	 * Check if the current user should be granted access to the specified menu item.
	 *
	 * Applies explicit per-role and per-user settings from $item['grant_access'].
	 * DOES NOT apply the extra capability. That should be done elsewhere.
	 *
	 * If there are no custom permissions set that match the current user, this method
	 * will check if the user would normally be able to access the menu (i.e. without
	 * virtual caps).
	 *
	 * @param array $item Menu item.
	 * @return bool
	 */
	private function current_user_is_granted_access($item) {
		$has_access = null;

		if ( isset($item['grant_access']) && is_array($item['grant_access']) ) {
			$grants = $item['grant_access'];
			$user = wp_get_current_user();

			//If this user is specifically allowed/forbidden, use that setting.
			if ( isset($grants['user:' . $user->get('user_login')]) ) {
				$has_access = $grants['user:' . $user->get('user_login')];
			}

			//Or if they're a super admin, allow *everything* unless explicitly denied.
			$this->disable_virtual_caps = true;
			if ( is_null($has_access) && is_multisite() && is_super_admin($user->ID) ) {
				if ( isset($grants['special:super_admin']) ) {
					$has_access = $grants['special:super_admin'];
				} else {
					$has_access = true;
				}
			}
			$this->disable_virtual_caps = false;


			if ( is_null($has_access) ) {
				//Allow the user if at least one of their roles is allowed,
				//or disallow if all their roles are forbidden.
				foreach ($user->roles as $role_id) {
					if ( isset($grants['role:' . $role_id]) ) {
						$role_has_access = $grants['role:' . $role_id];
						if ( is_null($has_access) ){
							$has_access = $role_has_access;
						} else {
							$has_access = $has_access || $role_has_access; //Allow access if at least one role has access.
						}
					}
				}
			}
		}

		if ( is_null($has_access) ) {
			//There are no custom settings for this user. Check if
			//they would be able to access the menu by default.
			$this->disable_virtual_caps = true;
			$required_capability = $item['access_level'];
			$has_access = current_user_can($required_capability);
			$this->disable_virtual_caps = false;
		}

		return $has_access;
	}

	/**
	 * Grant a user virtual caps they'll need to access certain menu items.
	 *
	 * @param array $capabilities All capabilities belonging to the current user, cap => true/false.
	 * @param array $required_caps The required capabilities.
	 * @param array $args The capability passed to current_user_can, the current user's ID, and other args.
	 * @return array Filtered list of capabilities.
	 */
	function grant_virtual_caps_to_user($capabilities, $required_caps, $args){
		$wp_menu_editor = $this->wp_menu_editor;

		if ( $this->disable_virtual_caps ) {
			return $capabilities;
		}

		$virtual_caps = $wp_menu_editor->get_virtual_caps();

		//The second entry of the $args array should be the user ID
		if ( count($args) < 2 ){
			return $capabilities;
		}
		$user_id = intval($args[1]);

		//We can avoid a potentially costly call chain and object initialization
		//by retrieving the current user directly if the ID matches (as it usually will).
		$current_user = wp_get_current_user();
		if ( $user_id == intval($current_user->ID) ) {
			$user = $current_user;
		} else {
			$user = get_user_by('id', $user_id);
		}

		$grant_keys = array('user:' . $user->user_login);
		foreach($user->roles as $role_id) {
			$grant_keys[] = 'role:' . $role_id;
		}

		//is_super_admin() will call has_cap on single-site installs.
		$this->disable_virtual_caps = true;
		if ( is_multisite() && is_super_admin($user->ID) ) {
			$grant_keys[] = 'special:super_admin';
		}
		$this->disable_virtual_caps = false;

		foreach($grant_keys as $grant) {
			if ( isset($virtual_caps[$grant]) ) {
				$capabilities = array_merge($capabilities, $virtual_caps[$grant]);
			}
		}

		return $capabilities;
	}

	/**
	 * Grant a role virtual caps it'll need to access certain menu items.
	 *
	 * @param array $capabilities Current role capabilities.
	 * @param string $required_cap The required capability.
	 * @param string $role_id Role name/slug.
	 * @return array Filtered capability list.
	 */
	function grant_virtual_caps_to_role($capabilities, $required_cap, $role_id){
		$wp_menu_editor = $this->wp_menu_editor;

		if ( $this->disable_virtual_caps ) {
			return $capabilities;
		}

		$virtual_caps = $wp_menu_editor->get_virtual_caps();
		$grant_key = 'role:' . $role_id;
		if ( isset($virtual_caps[$grant_key]) ) {
			$capabilities = array_merge($capabilities, $virtual_caps[$grant_key]);
		}

		return $capabilities;
	}

	/**
	 * Hook for the internal current_user_can() function used by Admin Menu Editor.
	 * Enables us to use computed capabilities.
	 *
	 * @uses wsMenuEditorExtras::current_user_can_computed()
	 *
	 * @param bool $allow The return value of current_user_can($capablity).
	 * @param string $capability The capability to check for.
	 * @return bool Whether the user has the specified capability.
	 */
	function grant_computed_caps_to_current_user($allow, $capability) {
		return $this->current_user_can_computed($capability, $allow);
	}

	/**
	 * Check if the current user has the specified computed capability. Basically, this method
	 * implements a very limited subset of Boolean logic for use in capability checks.
	 *
	 * Supported operations:
	 *  "capX"      - Normal capability check. Returns true if the user has the capability "capX".
	 *  "not:capX"  - Logical NOT. Returns true if the user *doesn't* have "capX".
	 *  "capX,capY" - Logical OR. Returns true if the user has at least one of "capX" or "capY".
	 *  "capX+capY" - Logical AND. Returns true if the user has all the listed capabilities.
	 *
	 * Operator precedence: NOT, AND, OR.
	 *
	 * @uses current_user_can() Uses the capability checking function from WordPress core.
	 *
	 * @param string $capability
	 * @param bool $default
	 * @return bool
	 */
	private function current_user_can_computed($capability, $default = null) {
		$or_operator = ',';
		if ( strpos($capability, $or_operator) !== false ) {
			$allow = false;
			foreach(explode($or_operator, $capability) as $term) {
				$allow = $allow || $this->current_user_can_computed($term);
			}
			return $allow;
		}

		$and_operator = '+';
		if ( strpos($capability, $and_operator) !== false ) {
			$allow = true;
			foreach(explode($and_operator, $capability) as $term) {
				$allow = $allow && $this->current_user_can_computed($term);
			}
			return $allow;
		}

		$not_operator = 'not:';
		$length = strlen($not_operator);
		if ( substr($capability, 0, $length) == $not_operator ) {
			return ! $this->current_user_can_computed(substr($capability, $length));
		}

		$capability = trim($capability);

		//Special case to handle weird input like "capability+" and " ,capability".
		if ($capability == '') {
			return true;
		}

		return isset($default) ? $default : current_user_can($capability);
	}

	function output_menu_dropzone($type = 'menu') {
		printf(
			'<div id="ws_%s_dropzone" class="ws_dropzone"> </div>',
			($type == 'menu') ? 'top_menu' : 'sub_menu'
		);
	}

	function pro_page_title($default_title){
		return 'Menu Editor Pro';
	}
	
	function pro_menu_title($default_title){
		return 'Menu Editor Pro';
	}

	/**
	 * Add extra verification args to custom update API requests.
	 * 
	 * When checking for updates, send along the blog URL and a hash of that URL + a secret key.
	 * The API endpoint can use this info to verify that the request really came from a valid
	 * installation of the "Pro" version of the plugin. 
	 * 
	 * Admittedly, it would be easy to spoof. Better than nothing nevertheless ;)
	 * 
	 * @param array $options
	 * @param string $api
	 * @param string $resource
	 * @return array
	 */
	function custom_api_options($options, $api = '', $resource = ''){
		if ( isset($options['slug']) && ($options['slug'] == $this->slug) ){
			$extra_args = array(
				'blogurl' => get_bloginfo('url'),
				'secret_hash' => sha1(get_bloginfo('url') . '|' . $this->secret),
			);
			$options['query_args'] = array_merge(
				$options['query_args'],
				$extra_args
			);
		}
		
		return $options;
	}
	
  /**
   * Callback for the 'admin_menu_editor_is_pro' hook. Always returns True to indicate that
   * the Pro version extras are installed.
   *
   * @param bool $value
   * @return bool True
   */
	function is_pro_version($value){
		return true;
	}

	function license_ui_title($title) {
		$title = 'Admin Menu Editor Pro License';
		return $title;
	}

	function license_ui_logo() {
		printf(
			'<p style="text-align: center; margin: 30px 0;"><img src="%s"></p>',
			esc_attr(plugins_url('images/logo-medium.png', __FILE__))
		);
	}

	/**
	 * Format separator items located in sub-menus.
	 * See /css/admin.css for the relevant styles.
	 *
	 * @param array $item Submenu item.
	 * @return array
	 */
	public function create_submenu_separator($item) {
		static $separator_num = 1;
		if ( $item['separator'] ) {
			$item['menu_title'] = '<hr class="ws-submenu-separator">';
			$item['file'] = '#submenu-separator-' . ($separator_num++);
		}
		return $item;
	}
}

if ( isset($wp_menu_editor) && !defined('WP_UNINSTALL_PLUGIN') ) {
	//Initialize extras
	$wsMenuEditorExtras = new wsMenuEditorExtras($wp_menu_editor);
}

if ( !defined('IS_DEMO_MODE') && !defined('IS_MASTER_MODE') ) {

//Load the custom update checker (requires PHP 5)
if ( (version_compare(PHP_VERSION, '5.0.0', '>=')) && (is_admin() || (defined('DOING_CRON') && constant('DOING_CRON'))) && isset($wp_menu_editor) ){
	require 'plugin-updates/plugin-update-checker.php';
	$ameProUpdateChecker = PucFactory::buildUpdateChecker(
		'http://adminmenueditor.com/?get_metadata_for=admin-menu-editor-pro',
		$wp_menu_editor->plugin_file, //Note: This variable is set in the framework constructor
		'admin-menu-editor-pro',
		12,                        //check every 12 hours
		'ame_pro_external_updates' //store book-keeping info in this WP option
	);

	//Hack. See PluginUpdateChecker::installHooks().
	function wsDisableAmeCron(){
		wp_clear_scheduled_hook('check_plugin_updates-admin-menu-editor-pro');
	}
	register_deactivation_hook($wp_menu_editor->plugin_file, 'wsDisableAmeCron');
}

//Load the license manager.
require 'license-manager/LicenseManager.php';
$ameProLicenseManager = new Wslm_LicenseManagerClient(array(
	'api_url' => 'http://adminmenueditor.com/licensing_api/',
	'product_slug' => 'admin-menu-editor-pro',
	'license_scope' => Wslm_LicenseManagerClient::LICENSE_SCOPE_NETWORK,
	'update_checker' => isset($ameProUpdateChecker) ? $ameProUpdateChecker : null,
));
if ( isset($wp_menu_editor) ) {
	$ameLicensingUi = new Wslm_BasicPluginLicensingUI($ameProLicenseManager, $wp_menu_editor->plugin_file);
}

}