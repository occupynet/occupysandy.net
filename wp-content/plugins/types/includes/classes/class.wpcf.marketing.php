<?php
/**
 *
 * Types Marketing Class
 *
 *
 */

/**
 * Types Marketing Class
 *
 * @since Types 1.6.5
 * @package Types
 * @subpackage Classes
 * @version 0.1
 * @category Help
 * @author marcin <marcin.p@icanlocalize.com>
 */
class WPCF_Types_Marketing
{
    protected $option_name = 'types-site-kind';
    protected $option_disable = 'types-site-kind-disable';
    protected $options;
    protected $adverts;

    public function __construct()
    {
        $this->options = include WPCF_ABSPATH.'/marketing/etc/types-site-kinds.php';
        $this->adverts = include WPCF_ABSPATH.'/marketing/etc/types.php';
        add_filter('admin_body_class', array($this, 'admin_body_class'));
        add_action( 'wpcf_menu_plus', array( $this, 'add_getting_started_to_admin_menu'), PHP_INT_MAX);
        add_filter('editor_addon_dropdown_after_title', array($this, 'add_views_advertising'));
    }

    /**
     * Add Views advertising on modal shortcode window.
     *
     * Add Views advertising on modal shortcode window. Advertisng will be 
     * added only when Views plugin is not active.
     *
     * @since 1.7
     * @param string $content Content of this filter.
     * @return string Content with advert or not.
     */
    public function add_views_advertising($content)
    {
        /**
         * do not load advertising if Views are active
         */
        if ( defined('WPV_VERSION') ) {
            return $content;
        }
        /**
         * Allow to turn off views advert.
         *
         * This filter allow to turn off views advert even Viwes plugin is not 
         * avaialbe.
         *
         * @since 1.7
         *
         * @param boolean $show Show adver?
         */
        if ( !apply_filters('show_views_advertising', true )) {
            return;
        }
        $content .= '<div class="types-marketing types-marketing-views">';
        $content .= sprintf(
            '<h4><span class="icon-toolset-logo ont-color-orange"></span>%s</h4>',
            __('Want to create templates with fields?', 'wpcf')
        );
        $content .= sprintf(
            '<p>%s</p>',
            __('The full Toolset package allows you to design templates for content and insert fields using the WordPress editor.', 'wpcf')
        );
        $content .= sprintf(
            '<p class="buttons"><a href="%s" class="button" target="_blank">%s</a> <a href="%s" class="more" target="_blank">%s</a></p>',
            esc_attr(
                add_query_arg(
                    array(
                        'utm_source' => 'typesplugin',
                        'utm_medium' => 'insert-fields',
                        'utm_campaign' => 'postedit',
                        'utm_term' => 'meet-toolset',
                    ),
                    'http://wp-types.com/'
                )
            ),
            __('Meet Toolset', 'wpcf'),
            esc_attr(
                add_query_arg(
                    array(
                        'utm_source' => 'typesplugin',
                        'utm_medium' => 'insert-fields',
                        'utm_campaign' => 'postedit',
                        'utm_term' => 'creating-content-templates',
                    ),
                    'http://wp-types.com/documentation/user-guides/view-templates/'
                )
            ),
            __('Creating Templates for Content', 'wpcf')
        );
        $content .= '</div>';
        return $content;
    }

    public function admin_body_class($classes)
    {
        $screen = get_current_screen();
        if ( isset($screen->id) && preg_match( '@marketing/getting-started/index$@', $screen->id ) ) {
            if ( !isset($_GET['kind'] )) {
                $classes = 'wpcf-marketing';
            }
            else if ( isset($_POST['marketing'])) {
                $classes = 'wpcf-marketing';
            }
        }

        return $classes;
    }

    protected function get_page_type()
    {
        $screen = get_current_screen();
        switch($screen->id) {
        case 'types_page_wpcf-edit-type':
            return 'cpt';
        case 'types_page_wpcf-edit-tax':
            return 'taxonomy';
        case 'types_page_wpcf-edit':
        case 'types_page_wpcf-edit-usermeta':
            return 'fields';
        }
        return false;
    }

    public function get_options()
    {
        return $this->options;
    }

    public function get_option_name()
    {
        return $this->option_name;
    }

    public function get_default_kind()
    {
        if ( isset($this->options) && is_array($this->options) ) {
            foreach ( $this->options as $kind => $options ) {
                if ( array_key_exists('default', $options ) && $options['default']) {
                    return $kind;
                }
            }
        }
        return false;
    }

    public function get_kind()
    {
        $kind = get_option($this->option_name, false);
        if (
            $kind
            && isset($this->options)
            && is_array($this->options)
            && array_key_exists( $kind, $this->options )
        ) {
            return $kind;
        }
        return false;
    }

    public function get_kind_url($kind = false)
    {
        if ( empty($kind) ) {
            $kind = $this->get_kind();
        }
        if (
            $kind
            && isset($this->options)
            && is_array($this->options)
            && array_key_exists('url', $this->options[$kind] )
        ) {
            return $this->options[$kind]['url'];
        }
        return;
    }

    public function get_option_disiable_value()
    {
        return get_option($this->option_disable, 0);
    }

    public function get_option_disiable_name()
    {
        return $this->option_disable;
    }

    protected function add_ga_campain($url, $utm_medium = 'getting-started')
    {
        return esc_url(
            add_query_arg(
                array(
                    'utm_source' => 'typesplugin',
                    'utm_medium' =>  $utm_medium,
                    'utm_campaign' => sprintf('%s-howto', $this->get_kind() ),
                ),
                $url
            )
        );
    }

    /**
     * add Getting Started to menu
     */
    public function add_getting_started_to_admin_menu()
    {
        $menu = array(
            'page_title' => __( 'What kind of site are you building?', 'wpcf' ),
            'menu_title' => __( 'Getting Started', 'wpcf' ),
            'menu_slug' => basename(dirname(dirname(dirname(__FILE__)))).'/marketing/getting-started/index.php',
            'hook' => 'wpcf_marketing',
            'load_hook' => 'wpcf_marketing_hook',
        );
        wpcf_admin_add_submenu_page($menu);
    }

}
