<?php
/*
  Plugin Name: Types Embedded
  Plugin URI: http://wordpress.org/extend/plugins/types/
  Description: Define custom post types, custom taxonomies and custom fields.
  Author: OnTheGoSystems
  Author URI: http://www.onthegosystems.com
  Version: 1.8.10
 */
/**
 *
 *
 */

add_action( 'plugins_loaded', 'wpcf_embedded_load_or_deactivate' );

function wpcf_embedded_load_or_deactivate()
{
    if ( function_exists('wpcf_activation_hook') ) {
        add_action( 'admin_init', 'wpcf_embedded_deactivate' );
        add_action( 'admin_notices', 'wpcf_embedded_deactivate_notice' );
    } else {
        require_once 'types.php';
    }
}

/**
 * wpcf_embedded_deactivate
 *
 * Deactivate this plugin
 *
 * @since 1.6.2
 */

function wpcf_embedded_deactivate()
{
    $plugin = plugin_basename( __FILE__ );
    deactivate_plugins( $plugin );
}

/**
 * wpcf_embedded_deactivate_notice
 *
 * Deactivate notice for this plugin
 *
 * @since 1.6.2
 */

function wpcf_embedded_deactivate_notice()
{
?>
    <div class="error">
        <p>
            <?php _e( 'Types Embedded was <strong>deactivated</strong>! You are already running the complete Types plugin, so this one is not needed anymore.', 'wpcf' ); ?>
        </p>
    </div>
<?php
}
