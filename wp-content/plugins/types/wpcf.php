<?php
/*
  Plugin Name: Types - Complete Solution for Custom Fields and Types
  Plugin URI: http://wordpress.org/extend/plugins/types/
  Description: Define custom post types, custom taxonomy and custom fields.
  Author: ICanLocalize
  Author URI: http://wp-types.com
  Version: 1.1.3
 */
// Added check because of activation hook and theme embedded code
if (!defined('WPCF_VERSION')) {
    define('WPCF_VERSION', '1.1.3');
}

define('WPCF_REPOSITORY','http://api.wp-types.com/');

define('WPCF_ABSPATH', dirname(__FILE__));
define('WPCF_RELPATH', plugins_url() . '/' . basename(WPCF_ABSPATH));
define('WPCF_INC_ABSPATH', WPCF_ABSPATH . '/includes');
define('WPCF_INC_RELPATH', WPCF_RELPATH . '/includes');
define('WPCF_RES_ABSPATH', WPCF_ABSPATH . '/resources');
define('WPCF_RES_RELPATH', WPCF_RELPATH . '/resources');
require_once WPCF_INC_ABSPATH . '/constants.php';

if (!defined('EDITOR_ADDON_RELPATH')) {
    define('EDITOR_ADDON_RELPATH',
            WPCF_RELPATH . '/embedded/common/visual-editor');
}


add_action('plugins_loaded', 'wpcf_init');
add_action('after_setup_theme', 'wpcf_init_embedded_code', 999);
register_activation_hook(__FILE__, 'wpcf_upgrade_init');
register_deactivation_hook(__FILE__, 'wpcf_deactivate_init');

add_filter('plugin_action_links', 'wpcf_types_plugin_action_links', 10, 2);

/**
 * Main init hook.
 */
function wpcf_init() {
    if (is_admin()) {
        require_once WPCF_ABSPATH . '/admin.php';
    }
}

/**
 * Include embedded code if not used in theme.
 */
function wpcf_init_embedded_code() {
    if (!defined('WPCF_EMBEDDED_ABSPATH')) {
        require_once WPCF_ABSPATH . '/embedded/types.php';
        wpcf_embedded_init();
    } else {// Added because if plugin is active - theme embedded code won't fire
        require_once WPCF_EMBEDDED_ABSPATH . '/types.php';
        wpcf_embedded_init();
    }
}

/**
 * Upgrade hook.
 */
function wpcf_upgrade_init() {
    require_once WPCF_ABSPATH . '/upgrade.php';
    wpcf_upgrade();
    wpcf_types_plugin_activate();
}

// Local debug
if (($_SERVER['SERVER_NAME'] == '192.168.1.2' || $_SERVER['SERVER_NAME'] == 'localhost') && !function_exists('debug')) {

    function debug($data, $die = true) {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        if ($die)
            die();
    }

}

function wpcf_types_plugin_activate() {
    add_option('wpcf_types_plugin_do_activation_redirect', true);
}

function wpcf_deactivate_init() {
    delete_option('wpcf_types_plugin_do_activation_redirect', true);
}

function wpcf_types_plugin_redirect() {
    if (get_option('wpcf_types_plugin_do_activation_redirect', false)) {
        delete_option('wpcf_types_plugin_do_activation_redirect');
        wp_redirect(admin_url() . 'admin.php?page=wpcf-help');
        exit;
    }
}

function wpcf_types_plugin_action_links($links, $file) {
    $this_plugin = basename(WPCF_ABSPATH) . '/wpcf.php';
    if ($file == $this_plugin) {
        $links[] = '<a href="admin.php?page=wpcf-help">' . __('Getting started',
                        'wpcf') . '</a>';
    }
    return $links;
}

/**
 * Checks if name is reserved.
 * 
 * @param type $name
 * @return type 
 */
function wpcf_is_reserved_name($name, $check_pages = true) {
    if ($check_pages) {
        global $wpdb;
        $page = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type='page'",
                        sanitize_title($name)));
        if ($page) {
            return true;
        }
    }
    $reserved = wpcf_reserved_names();
    $name = str_replace('-', '_', sanitize_title($name));
    return in_array($name, $reserved);
}

/**
 * Reserved names.
 * 
 * @return type 
 */
function wpcf_reserved_names() {
    $reserved = array(
        'attachment',
        'attachment_id',
        'author',
        'author_name',
        'calendar',
        'cat',
        'category',
        'category__and',
        'category__in',
        'category__not_in',
        'category_name',
        'comments_per_page',
        'comments_popup',
        'cpage',
        'day',
        'debug',
        'error',
        'exact',
        'feed',
        'hour',
        'link_category',
        'm',
        'minute',
        'monthnum',
        'more',
        'name',
        'nav_menu',
        'nopaging',
        'offset',
        'order',
        'orderby',
        'p',
        'page',
        'page_id',
        'paged',
        'pagename',
        'pb',
        'perm',
        'post',
        'post__in',
        'post__not_in',
        'post_format',
        'post_mime_type',
        'post_status',
        'post_tag',
        'post_type',
        'posts',
        'posts_per_archive_page',
        'posts_per_page',
        'preview',
        'robots',
        's',
        'search',
        'second',
        'sentence',
        'showposts',
        'static',
        'subpost',
        'subpost_id',
        'tag',
        'tag__and',
        'tag__in',
        'tag__not_in',
        'tag_id',
        'tag_slug__and',
        'tag_slug__in',
        'taxonomy',
        'tb',
        'term',
        'type',
        'w',
        'withcomments',
        'withoutcomments',
        'year',
    );

    return apply_filters('wpcf_reserved_names', $reserved);
}

/**
 * Returns unique ID.
 * 
 * @staticvar array $cache
 * @param type $cache_key
 * @return type 
 */
function wpcf_unique_id($cache_key) {
    $cache_key = md5(strval($cache_key) . strval(time()));
    static $cache = array();
    if (!isset($cache[$cache_key])) {
        $cache[$cache_key] = 1;
    } else {
        $cache[$cache_key] += 1;
    }
    return $cache_key . '-' . $cache[$cache_key];
}

/**
 * i18n friendly version of basename(), copy from wp-includes/formatting.php to solve bug with windows
 *
 * @since 3.1.0
 *
 * @param string $path A path.
 * @param string $suffix If the filename ends in suffix this will also be cut off.
 * @return string
 */
function wpcf_basename( $path, $suffix = '' ) {
    return urldecode( basename( str_replace( array( '%2F', '%5C' ), '/', urlencode( $path ) ), $suffix ) ); 
}

/**
 * Copy from wp-includes/media.php
 * Scale down an image to fit a particular size and save a new copy of the image.
 *
 * The PNG transparency will be preserved using the function, as well as the
 * image type. If the file going in is PNG, then the resized image is going to
 * be PNG. The only supported image types are PNG, GIF, and JPEG.
 *
 * Some functionality requires API to exist, so some PHP version may lose out
 * support. This is not the fault of WordPress (where functionality is
 * downgraded, not actual defects), but of your PHP version.
 *
 * @since 2.5.0
 *
 * @param string $file Image file path.
 * @param int $max_w Maximum width to resize to.
 * @param int $max_h Maximum height to resize to.
 * @param bool $crop Optional. Whether to crop image or resize.
 * @param string $suffix Optional. File suffix.
 * @param string $dest_path Optional. New image file path.
 * @param int $jpeg_quality Optional, default is 90. Image quality percentage.
 * @return mixed WP_Error on failure. String with new destination path.
 */
function wpcf_image_resize( $file, $max_w, $max_h, $crop = false, $suffix = null, $dest_path = null, $jpeg_quality = 90 ) {

	$image = wp_load_image( $file );
	if ( !is_resource( $image ) )
		return new WP_Error( 'error_loading_image', $image, $file );

	$size = @getimagesize( $file );
	if ( !$size )
		return new WP_Error('invalid_image', __('Could not read image size'), $file);
	list($orig_w, $orig_h, $orig_type) = $size;

	$dims = image_resize_dimensions($orig_w, $orig_h, $max_w, $max_h, $crop);
	if ( !$dims )
		return new WP_Error( 'error_getting_dimensions', __('Could not calculate resized image dimensions') );
	list($dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h) = $dims;

	$newimage = wp_imagecreatetruecolor( $dst_w, $dst_h );

	imagecopyresampled( $newimage, $image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);

	// convert from full colors to index colors, like original PNG.
	if ( IMAGETYPE_PNG == $orig_type && function_exists('imageistruecolor') && !imageistruecolor( $image ) )
		imagetruecolortopalette( $newimage, false, imagecolorstotal( $image ) );

	// we don't need the original in memory anymore
	imagedestroy( $image );

	// $suffix will be appended to the destination filename, just before the extension
	if ( !$suffix )
		$suffix = "{$dst_w}x{$dst_h}";

	$info = pathinfo($file);
	$dir = $info['dirname'];
	$ext = $info['extension'];
	$name = wpcf_basename($file, ".$ext"); // use fix here for windows

	if ( !is_null($dest_path) and $_dest_path = realpath($dest_path) )
		$dir = $_dest_path;
	$destfilename = "{$dir}/{$name}-{$suffix}.{$ext}";

	if ( IMAGETYPE_GIF == $orig_type ) {
		if ( !imagegif( $newimage, $destfilename ) )
			return new WP_Error('resize_path_invalid', __( 'Resize path invalid' ));
	} elseif ( IMAGETYPE_PNG == $orig_type ) {
		if ( !imagepng( $newimage, $destfilename ) )
			return new WP_Error('resize_path_invalid', __( 'Resize path invalid' ));
	} else {
		// all other formats are converted to jpg
		if ( 'jpg' != $ext && 'jpeg' != $ext )
			$destfilename = "{$dir}/{$name}-{$suffix}.jpg";
		if ( !imagejpeg( $newimage, $destfilename, apply_filters( 'jpeg_quality', $jpeg_quality, 'image_resize' ) ) )
			return new WP_Error('resize_path_invalid', __( 'Resize path invalid' ));
	}

	imagedestroy( $newimage );

	// Set correct file permissions
	$stat = stat( dirname( $destfilename ));
	$perms = $stat['mode'] & 0000666; //same permissions as parent folder, strip off the executable bits
	@ chmod( $destfilename, $perms );

	return $destfilename;
}
