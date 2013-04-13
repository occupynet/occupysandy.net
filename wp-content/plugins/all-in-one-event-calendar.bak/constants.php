<?php

/**
 * ai1ec_initiate_constants function
 *
 * Define required constants, if these have not been defined already.
 *
 * @uses plugin_basename       To determine plug-in folder+file name
 * @uses plugins_url           To determine absolute URI to plug-ins' folder
 * @uses get_option            To fetch 'home' URI value
 *
 * @return void Method does not return
 */
function ai1ec_initiate_constants() {

	// ===============
	// = Plugin Name =
	// ===============
	if ( ! defined( 'AI1EC_PLUGIN_NAME' ) ) {
		define( 'AI1EC_PLUGIN_NAME',        'all-in-one-event-calendar' );
	}

	// ===================
	// = Plugin Basename =
	// ===================
	if ( ! defined( 'AI1EC_PLUGIN_BASENAME' ) ) {
		$plugin = dirname( __FILE__ ) . DIRECTORY_SEPARATOR .
			AI1EC_PLUGIN_NAME . '.php';
		define( 'AI1EC_PLUGIN_BASENAME',    plugin_basename( $plugin ) );
		unset( $plugin );
	}

	// ==================
	// = Plugin Version =
	// ==================
	if ( ! defined( 'AI1EC_VERSION' ) ) {
		define( 'AI1EC_VERSION',            '1.9.6-pro' );
	}

	// ====================
	// = Database Version =
	// ====================
	if ( ! defined( 'AI1EC_DB_VERSION' ) ) {
		define( 'AI1EC_DB_VERSION',         218 );
	}

	// ====================================
	// = Bundled themes version & edition =
	// ====================================
	if ( ! defined( 'AI1EC_THEMES_VERSION' ) ) {
		define( 'AI1EC_THEMES_VERSION',     '25-pro' );
	}

	// ================
	// = Cron Version =
	// ================
	if ( ! defined( 'AI1EC_CRON_VERSION' ) ) {
		define( 'AI1EC_CRON_VERSION',       109 );
	}
	if ( ! defined( 'AI1EC_N_CRON_VERSION' ) ) {
		define( 'AI1EC_N_CRON_VERSION',     107 );
	}
	if ( ! defined( 'AI1EC_N_CRON_FREQ' ) ) {
		define( 'AI1EC_N_CRON_FREQ',        'daily' );
	}
	if ( ! defined( 'AI1EC_U_CRON_VERSION' ) ) {
		define( 'AI1EC_U_CRON_VERSION',     111 );
	}
	if ( ! defined( 'AI1EC_U_CRON_FREQ' ) ) {
		define( 'AI1EC_U_CRON_FREQ',        'hourly' );
	}
	if ( ! defined( 'AI1EC_UPDATES_URL' ) ) {
		define( 'AI1EC_UPDATES_URL',        'http://api.time.ly/plugin/pro/latest' );
	}

	// ===============
	// = Plugin Path =
	// ===============
	if ( ! defined( 'AI1EC_PATH' ) ) {
		define( 'AI1EC_PATH',               dirname( __FILE__ ) );
	}

	// ===================
	// = CSS Folder name =
	// ===================
	if ( ! defined( 'AI1EC_CSS_FOLDER' ) ) {
		define( 'AI1EC_CSS_FOLDER',         'css' );
	}

	// ==================
	// = JS Folder name =
	// ==================
	if ( ! defined( 'AI1EC_JS_FOLDER' ) ) {
		define( 'AI1EC_JS_FOLDER',          'js' );
	}

	// =====================
	// = Image folder name =
	// =====================
	if ( ! defined( 'AI1EC_IMG_FOLDER' ) ) {
		define( 'AI1EC_IMG_FOLDER',         'img' );
	}

	// ============
	// = Lib Path =
	// ============
	if ( ! defined( 'AI1EC_LIB_PATH' ) ) {
		define( 'AI1EC_LIB_PATH',           AI1EC_PATH . DIRECTORY_SEPARATOR . 'lib' );
	}

	// =================
	// = Language Path =
	// =================
	if ( ! defined( 'AI1EC_LANGUAGE_PATH' ) ) {
		define( 'AI1EC_LANGUAGE_PATH',      AI1EC_PLUGIN_NAME . DIRECTORY_SEPARATOR . 'language' );
	}

	// ============
	// = App Path =
	// ============
	if ( ! defined( 'AI1EC_APP_PATH' ) ) {
		define( 'AI1EC_APP_PATH',           AI1EC_PATH . DIRECTORY_SEPARATOR . 'app' );
	}

	// ===================
	// = Controller Path =
	// ===================
	if ( ! defined( 'AI1EC_CONTROLLER_PATH' ) ) {
		define( 'AI1EC_CONTROLLER_PATH',    AI1EC_APP_PATH . DIRECTORY_SEPARATOR . 'controller' );
	}

	// ==================
	// = Factories Path =
	// ==================
	if ( ! defined( 'AI1EC_FACTORY_PATH' ) ) {
		define( 'AI1EC_FACTORY_PATH',       AI1EC_LIB_PATH . DIRECTORY_SEPARATOR . 'factory' );
	}

	// ==============
	// = Model Path =
	// ==============
	if ( ! defined( 'AI1EC_MODEL_PATH' ) ) {
		define( 'AI1EC_MODEL_PATH',         AI1EC_APP_PATH . DIRECTORY_SEPARATOR . 'model' );
	}

	// ==============
	// = Cache Path =
	// ==============
	if ( ! defined( 'AI1EC_CACHE_PATH' ) ) {
		define( 'AI1EC_CACHE_PATH',         AI1EC_PATH . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR );
	}

	// =============
	// = View Path =
	// =============
	if ( ! defined( 'AI1EC_VIEW_PATH' ) ) {
		define( 'AI1EC_VIEW_PATH',          AI1EC_APP_PATH . DIRECTORY_SEPARATOR . 'view' );
	}

	// =================
	// = Adapters Path =
	// =================
	if ( ! defined( 'AI1EC_ADAPTER_PATH' ) ) {
		define( 'AI1EC_ADAPTER_PATH',       AI1EC_LIB_PATH . DIRECTORY_SEPARATOR . 'adapter' );
	}

	// ====================
	// = Admin Theme Path =
	// ====================
	if ( ! defined( 'AI1EC_ADMIN_THEME_PATH' ) ) {
		define( 'AI1EC_ADMIN_THEME_PATH',   AI1EC_VIEW_PATH . DIRECTORY_SEPARATOR . 'admin' );
	}

	// ========================
	// = Admin theme CSS path =
	// ========================
	if ( ! defined( 'AI1EC_ADMIN_THEME_CSS_PATH' ) ) {
		define( 'AI1EC_ADMIN_THEME_CSS_PATH', AI1EC_ADMIN_THEME_PATH . DIRECTORY_SEPARATOR . AI1EC_CSS_FOLDER );
	}

	// =======================
	// = Admin theme JS path =
	// =======================
	if ( ! defined( 'AI1EC_ADMIN_THEME_JS_PATH' ) ) {
		define( 'AI1EC_ADMIN_THEME_JS_PATH', AI1EC_ADMIN_THEME_PATH . DIRECTORY_SEPARATOR . AI1EC_JS_FOLDER );
	}

	// ========================
	// = Admin theme IMG path =
	// ========================
	if ( ! defined( 'AI1EC_ADMIN_THEME_IMG_PATH' ) ) {
		define( 'AI1EC_ADMIN_THEME_IMG_PATH', AI1EC_ADMIN_THEME_PATH . DIRECTORY_SEPARATOR . AI1EC_IMG_FOLDER );
	}

	// ===============
	// = Helper Path =
	// ===============
	if ( ! defined( 'AI1EC_HELPER_PATH' ) ) {
		define( 'AI1EC_HELPER_PATH',          AI1EC_APP_PATH . DIRECTORY_SEPARATOR . 'helper' );
	}

	// ==================
	// = Exception Path =
	// ==================
	if ( ! defined( 'AI1EC_EXCEPTION_PATH' ) ) {
		define( 'AI1EC_EXCEPTION_PATH',       AI1EC_APP_PATH . DIRECTORY_SEPARATOR . 'exception' );
	}

	// ==============
	// = Plugin Url =
	// ==============
	if ( ! defined( 'AI1EC_URL' ) ) {
		define( 'AI1EC_URL',                  plugins_url( '', __FILE__ ) );
	}

	// ==============
	// = Images URL =
	// ==============
	if ( ! defined( 'AI1EC_IMAGE_URL' ) ) {
		define( 'AI1EC_IMAGE_URL',            AI1EC_URL . '/' . AI1EC_IMG_FOLDER );
	}

	// ===========
	// = CSS URL =
	// ===========
	if ( ! defined( 'AI1EC_CSS_URL' ) ) {
		define( 'AI1EC_CSS_URL',              AI1EC_URL . '/' . AI1EC_CSS_FOLDER );
	}

	// ==========
	// = JS URL =
	// ==========
	if ( ! defined( 'AI1EC_JS_URL' ) ) {
		define( 'AI1EC_JS_URL',               AI1EC_URL . '/' . AI1EC_JS_FOLDER );
	}

	// ================
	// = Admin JS URL =
	// ================
	if ( ! defined( 'AI1EC_ADMIN_THEME_JS_URL' ) ) {
		define( 'AI1EC_ADMIN_THEME_JS_URL',   AI1EC_URL . '/app/view/admin/' . AI1EC_JS_FOLDER );
	}

	// =================
	// = Admin CSS URL =
	// =================
	if ( ! defined( 'AI1EC_ADMIN_THEME_CSS_URL' ) ) {
		define( 'AI1EC_ADMIN_THEME_CSS_URL',  AI1EC_URL . '/app/view/admin/' . AI1EC_CSS_FOLDER );
	}

	// =================
	// = Admin IMG URL =
	// =================
	if ( ! defined( 'AI1EC_ADMIN_THEME_IMG_URL' ) ) {
		define( 'AI1EC_ADMIN_THEME_IMG_URL',  AI1EC_URL . '/app/view/admin/' . AI1EC_IMG_FOLDER );
	}

	// =============
	// = POST TYPE =
	// =============
	if ( ! defined( 'AI1EC_POST_TYPE' ) ) {
		define( 'AI1EC_POST_TYPE',           'ai1ec_event' );
	}

	// =========================================
	// = BASE URL FOR ALL CALENDAR ADMIN PAGES =
	// =========================================
	if ( ! defined( 'AI1EC_ADMIN_BASE_URL' ) ) {
		define( 'AI1EC_ADMIN_BASE_URL', 'edit.php?post_type=' . AI1EC_POST_TYPE );
	}

	// =======================================================
	// = THEME SELECTION PAGE BASE URL (wrap in admin_url()) =
	// =======================================================
	if ( ! defined( 'AI1EC_THEME_SELECTION_BASE_URL' ) ) {
		define( 'AI1EC_THEME_SELECTION_BASE_URL', AI1EC_ADMIN_BASE_URL . '&page=' . AI1EC_PLUGIN_NAME . '-themes' );
	}

	// =====================================================
	// = THEME OPTIONS PAGE BASE URL (wrap in admin_url()) =
	// =====================================================
	if ( ! defined( 'AI1EC_THEME_OPTIONS_BASE_URL' ) ) {
		define( 'AI1EC_THEME_OPTIONS_BASE_URL', AI1EC_ADMIN_BASE_URL . '&page=' . AI1EC_PLUGIN_NAME . '-edit-css' );
	}

	// ======================================================
	// = INSTALL THEMES PAGE BASE URL (wrap in admin_url()) =
	// ======================================================
	if ( ! defined( 'AI1EC_INSTALL_THEMES_BASE_URL' ) ) {
		define( 'AI1EC_INSTALL_THEMES_BASE_URL', 'themes.php?page=' .  AI1EC_PLUGIN_NAME . '-install-themes' );
	}

	// =====================================================
	// = UPDATE THEMES PAGE BASE URL (wrap in admin_url()) =
	// =====================================================
	if ( ! defined( 'AI1EC_UPDATE_THEMES_BASE_URL' ) ) {
		define( 'AI1EC_UPDATE_THEMES_BASE_URL', 'index.php?page=' .   AI1EC_PLUGIN_NAME . '-update-themes' );
	}

	// =====================================================
	// = UPDATE PLUGIN BASE URL                            =
	// =====================================================
	if ( ! defined( 'AI1EC_UPGRADE_PLUGIN_BASE_URL' ) ) {
		define( 'AI1EC_UPGRADE_PLUGIN_BASE_URL', 'plugins.php?&amp;page=' . AI1EC_PLUGIN_NAME . '-upgrade' );
	}

	// =====================================================
	// = FEED SETTINGS PAGE BASE URL (wrap in admin_url()) =
	// =====================================================
	if ( ! defined( 'AI1EC_FEED_SETTINGS_BASE_URL' ) ) {
		define( 'AI1EC_FEED_SETTINGS_BASE_URL', AI1EC_ADMIN_BASE_URL . '&page=' . AI1EC_PLUGIN_NAME . '-feeds' );
	}

	// ================================================
	// = SETTINGS PAGE BASE URL (wrap in admin_url()) =
	// ================================================
	if ( ! defined( 'AI1EC_SETTINGS_BASE_URL' ) ) {
		define( 'AI1EC_SETTINGS_BASE_URL',  AI1EC_ADMIN_BASE_URL . '&page=' . AI1EC_PLUGIN_NAME . '-settings' );
	}

	// ======================
	// = Default Theme Name =
	// ======================
	if ( ! defined( 'AI1EC_DEFAULT_THEME_NAME' ) ) {
		define( 'AI1EC_DEFAULT_THEME_NAME', 'vortex' );
	}

	// =============================
	// = Default Theme folder name =
	// =============================
	if ( ! defined( 'AI1EC_THEMES_FOLDER' ) ) {
		define( 'AI1EC_THEMES_FOLDER',      'themes-ai1ec' );
	}

	// ========================
	// = AI1EC Theme location =
	// ========================
	if ( ! defined( 'AI1EC_THEMES_ROOT' ) ) {
		define( 'AI1EC_THEMES_ROOT',        WP_CONTENT_DIR . DIRECTORY_SEPARATOR . AI1EC_THEMES_FOLDER );
	}

	// ===================
	// = AI1EC Theme URL =
	// ===================
	if ( ! defined( 'AI1EC_THEMES_URL' ) ) {
		define( 'AI1EC_THEMES_URL',         WP_CONTENT_URL . '/' . AI1EC_THEMES_FOLDER );
	}

	// ======================
	// = Default theme path =
	// ======================
	if ( ! defined( 'AI1EC_DEFAULT_THEME_PATH' ) ) {
		define( 'AI1EC_DEFAULT_THEME_PATH', AI1EC_THEMES_ROOT . DIRECTORY_SEPARATOR . AI1EC_DEFAULT_THEME_NAME );
	}

	// =====================
	// = Default theme url =
	// =====================
	if ( ! defined( 'AI1EC_DEFAULT_THEME_URL' ) ) {
		define( 'AI1EC_DEFAULT_THEME_URL',  AI1EC_THEMES_URL . '/' . AI1EC_DEFAULT_THEME_NAME );
	}

	// ================
	// = RSS FEED URL =
	// ================
	if ( ! defined( 'AI1EC_RSS_FEED' ) ) {
		define( 'AI1EC_RSS_FEED',           'http://time.ly/feed/' );
	}

	// =======================================
	// = FAKE CATEGORY ID FOR CALENDAR PAGE  =
	// = Numeric-only 1337-speak of          =
	// = AI1EC_CALENDAR - ID must be numeric =
	// =======================================
	if ( ! defined( 'AI1EC_FAKE_CATEGORY_ID' ) ) {
		define( 'AI1EC_FAKE_CATEGORY_ID',   -4113473042 );
	}

	// ================================
	// = EVENT IMPORTERS PLUGINS PATH =
	// ================================
	if ( ! defined( 'AI1EC_IMPORT_PLUGIN_PATH' ) ) {
		define( 'AI1EC_IMPORT_PLUGIN_PATH',     AI1EC_LIB_PATH . DIRECTORY_SEPARATOR . 'plugin' );
	}

	// ========================================
	// = EVENT IMPORTERS PLUGINS INCLUDE PATH =
	// ========================================
	if ( ! defined( 'AI1EC_IMPORT_PLUGIN_INC_PATH' ) ) {
		define( 'AI1EC_IMPORT_PLUGIN_INC_PATH', AI1EC_IMPORT_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'inc');
	}

	// ================================
	// = FACEBOOK PLUGIN INCLUDE PATH =
	// ================================
	if ( ! defined( 'AI1EC_FACEBOOK_PLUGIN_INC_PATH' ) ) {
		define( 'AI1EC_FACEBOOK_PLUGIN_INC_PATH', AI1EC_IMPORT_PLUGIN_INC_PATH . DIRECTORY_SEPARATOR . 'facebook');
	}

	// ==============
	// = SCRIPT URL =
	// ==============
	if ( ! defined( 'AI1EC_SCRIPT_URL' ) ) {
		define( 'AI1EC_SCRIPT_URL',         get_option( 'home' ) . '/?plugin=' . AI1EC_PLUGIN_NAME );
	}

	// ====================================================
	// = Convert http:// to webcal:// in AI1EC_SCRIPT_URL =
	// =  (webcal:// protocol does not support https://)  =
	// ====================================================
	$webcal_url = str_replace( 'http://', 'webcal://', AI1EC_SCRIPT_URL );

	// ==============
	// = EXPORT URL =
	// ==============
	if ( ! defined( 'AI1EC_EXPORT_URL' ) ) {
		define( 'AI1EC_EXPORT_URL',         $webcal_url . '&controller=ai1ec_exporter_controller&action=export_events&cb=' . rand() );
	}

	// =================
	// = LOCATIONS API =
	// =================
	if ( ! defined( 'AI1EC_LOCATIONS_API' ) ) {
		define( 'AI1EC_LOCATIONS_API', 'http://api.time.ly:32000' );
	}

	// =============
	// = STATS API =
	// =============
	if ( ! defined( 'AI1EC_STATS_API' ) ) {
		define( 'AI1EC_STATS_API', 'http://api.time.ly:31000' );
	}

	// The real id is added later, but i need to define this otherwise i have a notice
	if ( ! defined( 'AI1EC_TIMELY_SUBSCRIPTION' ) ) {
		define( 'AI1EC_TIMELY_SUBSCRIPTION', 'I-VVUBFT2VEUEA' );
	}

	// ======================
	// = LICENSE STATUS API =
	// ======================
	if ( ! defined( 'AI1EC_LICENSE_STATUS_JS' ) ) {
		// separate version with product (191-pro)
		$tmp = explode( '-', AI1EC_VERSION );
		// assign version to $tmp (191)
		$tmp = $tmp[0];
		define(
			'AI1EC_LICENSE_STATUS_JS',
			'https://api.time.ly/check_license_status.js?version='. $tmp .
			'&license='
		);
	}

	// ==================
	// = TIMELY ACCOUNT =
	// ==================
	if ( ! defined( 'AI1EC_TIMELY_ACCOUNT_URL' ) ) {
		define( 'AI1EC_TIMELY_ACCOUNT_URL', 'https://my.time.ly/account' );
	}

	// ====================
	// = SPECIAL SETTINGS =
	// ====================

	// Set AI1EC_EVENT_PLATFORM to TRUE to turn WordPress into an events-only
	// platform. For a multi-site install, setting this to TRUE is equivalent to a
	// super-administrator selecting the
	//   "Turn this blog into an events-only platform" checkbox
	// on the Calendar Settings page of every blog on the network.
	// This mode, when enabled on blogs where this plugin is active, hides all
	// administrative functions unrelated to events and the calendar (except to
	// super-administrators), and sets default WordPress settings appropriate for
	// pure event management.
	if ( ! defined( 'AI1EC_EVENT_PLATFORM' ) ) {
		define( 'AI1EC_EVENT_PLATFORM',     FALSE );
	}

	// Enable All-in-One-Event-Calendar to work in debug mode, which means,
	// that cache is ignored, extra output may appear at places, etc.
	// Do not set this to any other value than `false` on production even if
	// you know what you are doing, because you will waste valuable
	// resources - save the Earth, at least.
	if ( ! defined( 'AI1EC_DEBUG' ) ) {
		define( 'AI1EC_DEBUG', FALSE );
	}

}
