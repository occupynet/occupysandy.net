<?php
/**
 *
 * Post Types Class
 *
 *
 */

require_once WPCF_EMBEDDED_INC_ABSPATH . '/custom-types.php';

/**
 * Post Types Class
 *
 * @since Types 1.2
 * @package Types
 * @subpackage Classes
 * @version 0.1
 * @category Post Type
 * @author srdjan <srdjan@icanlocalize.com>
 */
class WPCF_Post_Types
{

    var $data;
    var $settings;
    var $messages = null;

    function __construct()
    {
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_head-nav-menus.php', array($this, 'add_filters'));
        add_filter('wp_setup_nav_menu_item',  array( $this, 'setup_archive_item'));
        add_filter('wp_nav_menu_objects', array( $this, 'maybe_make_current'));
    }
    /**
     * Check has some custom fields to display.
     *
     * Check custom post type for custom fields to display on custom post edit 
     * screen.
     *
     * @since 1.7
     *
     * @param array $data CPT data
     * @param string $field name of field to check
     *
     * @return bool It has some fields?
     */
    private function check_has_custom_fields($data, $field = false)
    {
        $value = isset($data['custom_fields']) && is_array($data['custom_fields']) && !empty($data['custom_fields']);
        if ( false == $value ) {
            return $value;
        }
        if ( true == $value && false == $field ) {
            return $value;
        }
        return isset($data['custom_fields'][$field]);
    }

    /**
     * Add sort to admin table list.
     *
     * Add sort by custom field to admin table with list of entries
     *
     * @since 1.7
     *
     * @param object $query QP Query object
     *
     */
    public function pre_get_posts($query)
    {
        /**
         * do not run in admin
         */
        if ( !is_admin() ) {
            return;
        }
        /**
         * check is main query and is set orderby and post_type
         */
        if (
            $query->is_main_query()
            && ( $orderby = $query->get( 'orderby' ) ) 
            && ( $post_type = $query->get( 'post_type' ) ) 
        ) {
            $custom_post_types = wpcf_get_active_custom_types();
            /**
             * this CPT exists as a Types CPT?
             */
            if (!isset($custom_post_types[$post_type])) {
                return;
            }
            /**
             * set up meta_key if this CPT has this field to sort
             */
            if ($this->check_has_custom_fields($custom_post_types[$post_type], $orderby)) {
                $query->set('meta_key',$orderby);
            }
        }
    }

    /**
     * Admin init.
     *
     * Admin init function used to add columns..
     *
     * @since 1.6.6
     */
    public function admin_init()
    {
        add_action('pre_get_posts', array($this, 'pre_get_posts'));
        $custom_post_types = wpcf_get_active_custom_types();
        foreach( $custom_post_types as $post_type => $data ) {
            if ( $this->check_has_custom_fields($data)) {
                $hook = sprintf('manage_edit-%s_columns', $post_type);
                add_filter($hook, array($this, 'manage_posts_columns'));

                $hook = sprintf('manage_edit-%s_sortable_columns', $post_type);
                add_filter($hook, array($this, 'manage_posts_sortable_columns'));

                $hook = sprintf('manage_%s_posts_custom_column', $post_type);
                add_action($hook, array($this, 'manage_custom_columns'), 10, 2);
            }
        }
    }

    /**
     * Add custom fields as a sortable columns.
     *
     * Add custom fields as a sortable columns on custom post admin list
     *
     * @since 1.7
     *
     * @param array $columns Hashtable of columns;
     *
     * @return array Hashtable of columns;
     */
    public function manage_posts_sortable_columns($columns)
    {
        return $this->manage_posts_columns_common($columns, 'sortable');
    }

    /**
     * Add custom fields column helper.
     *
     * Add custom fields as a sortable columns on custom post admin list
     *
     * @since 1.7
     *
     * @param array $columns Hashtable of columns;
     * @param string $mode Work Mode.
     *
     * @return array Hashtable of columns;
     */
    private function manage_posts_columns_common($columns, $mode = 'normal')
    {
        $screen = get_current_screen();
        if ( !isset( $screen->post_type) ) {
            return $columns;
        }
        $custom_post_types = wpcf_get_active_custom_types();
        if(
            !isset($custom_post_types[$screen->post_type])
            || !$this->check_has_custom_fields($custom_post_types[$screen->post_type])
            || !isset($custom_post_types[$screen->post_type]['custom_fields'])
            || empty($custom_post_types[$screen->post_type]['custom_fields'])
        ) {
            return $columns;
        }
        $fields = wpcf_admin_fields_get_fields();

        foreach( array_keys($custom_post_types[$screen->post_type]['custom_fields']) as $full_id) {

            $data = array();
            $key = null;

            foreach( $fields as $field_key => $field_data ) {
                if ( !isset($field_data['meta_key']) ) {
                    continue;
                }
                if ( $full_id != $field_data['meta_key'] ) {
                    continue;
                }
                $key = $field_key;
                $data = $field_data;
            }

            if ( !isset($data['meta_key']) ) {
                continue;
            }

            if ( isset($custom_post_types[$screen->post_type]['custom_fields'][$data['meta_key']]) ) {
                switch($mode) {
                case 'sortable':
                    switch( $data['type'] ) {
                        /**
                         * turn of sorting for complex data
                         */
                    case 'date':
                    case 'skype':
                        $columns[$data['meta_key']] = false;;
                        break;
                    default:
                        $columns[$data['meta_key']] = $data['meta_key'];
                        break;
                    }
                    break;
                case 'normal':
                default:
                    $columns[$data['meta_key']] = $data['name'];
                    break;
                }
            }
        }
        return $columns;
    }

    /**
     * Add custom fields as a columns.
     *
     * Add custom fields as a columns on custom post admin list
     *
     * @since 1.6.6
     *
     * @param array $columns Hashtable of columns;
     *
     * @return array Hashtable of columns;
     */
    public function manage_posts_columns($columns)
    {
        return $this->manage_posts_columns_common($columns, 'normal');
    }

    /**
     * Show value of custom field.
     *
     * Show value of custom field.
     *
     * @since 1.6.6
     *
     * @param string $column Column name,
     * @param int $var Current post ID.
     */
    public function manage_custom_columns($column, $post_id)
    {
        $value = get_post_meta($post_id, $column, true);
        if ( empty($value) ) {
            return;
        }
        $field = wpcf_admin_fields_get_field_by_meta_key($column);
        if ( isset( $field['type'] ) ) {
            switch( $field['type'] ) {
            case 'image':
                $default_width = '100px';
                /**
                 * Width of image.
                 *
                 * Filter allow to change default image size displayed on 
                 * admin etry list for custom field type image. Default is 
                 * 100px - you can change it to any proper CSS width 
                 * definition.
                 *
                 * @since 1.7
                 *
                 * @param string $var Default width "100px".
                 */
                $width = apply_filters('wpcf_field_image_max_width', $default_width);
                if (empty($width)) {
                    $width = $default_width;
                }
                $value = sprintf(
                    '<img src="%s" style="max-width:%s" alt="" />',
                    esc_attr($value),
                    esc_attr($width)
                );
                break;
            case 'skype':
                $value = isset($value['skypename'])? $value['skypename']:'';
                break;
            case 'date':
                require_once WPTOOLSET_FORMS_ABSPATH . '/classes/class.date.php';
                $value = WPToolset_Field_Date::timetodate($value);
                break;
            }
        }
        if ( is_string($value ) ) {
            echo $value;
        }
    }

    /**
     * Assign menu item the appropriate url
     * @param  object $menu_item
     * @return object $menu_item
     */
    public function setup_archive_item( $menu_item ) {
        if ( $menu_item->type !== 'post_type_archive' ) {
            return $menu_item;
        }
        $post_type = $menu_item->object;
        if (post_type_exists( $post_type )) {
            $data = get_post_type_object( $post_type );
            $menu_item->type_label = sprintf( __( 'Archive for %s', 'wpcf' ), $data->labels->name);
            $menu_item->url = get_post_type_archive_link( $post_type );
        }
        return $menu_item;
    }

    public function add_filters()
    {
        $custom_post_types = wpcf_get_active_custom_types();
        if ( empty($custom_post_types) ) {
            return;
        }
        foreach ( $custom_post_types as $slug => $data ) {
            add_filter( 'nav_menu_items_' . $slug, array( $this, 'add_archive_checkbox' ), null, 3 );
        }
    }

    public function add_archive_checkbox( $posts, $args, $post_type )
    {
        global $_nav_menu_placeholder, $wp_rewrite;
        $_nav_menu_placeholder = ( 0 > $_nav_menu_placeholder ) ? intval($_nav_menu_placeholder) - 1 : -1;

        array_unshift( $posts, (object) array(
            'ID' => 0,
            'object_id' => $_nav_menu_placeholder,
            'post_title' => $post_type['args']->labels->all_items,
            'post_type' => 'nav_menu_item',
            'post_excerpt' => '',
            'post_content' => '',
            'type' => 'post_type_archive',
            'object' => $post_type['args']->slug,
        ) );

        return $posts;
    }

    /**
     * Make post type archive link 'current'
     * @uses   Post_Type_Archive_Links :: get_item_ancestors()
     * @param  array $items
     * @return array $items
     */
    public function maybe_make_current( $items ) {
        foreach ( $items as $item ) {
            if ( 'post_type_archive' !== $item->type ) {
                continue;
            }
            $post_type = $item->object;
            if (
                ! is_post_type_archive( $post_type )
                AND ! is_singular( $post_type )
            )
            continue;

            // Make item current
            $item->current = true;
            $item->classes[] = 'current-menu-item';

            // Loop through ancestors and give them 'parent' or 'ancestor' class
            $active_anc_item_ids = $this->get_item_ancestors( $item );
            foreach ( $items as $key => $parent_item ) {
                $classes = (array) $parent_item->classes;

                // If menu item is the parent
                if ( $parent_item->db_id == $item->menu_item_parent ) {
                    $classes[] = 'current-menu-parent';
                    $items[ $key ]->current_item_parent = true;
                }

                // If menu item is an ancestor
                if ( in_array( intval( $parent_item->db_id ), $active_anc_item_ids ) ) {
                    $classes[] = 'current-menu-ancestor';
                    $items[ $key ]->current_item_ancestor = true;
                }

                $items[ $key ]->classes = array_unique( $classes );
            }
        }

        return $items;
    }

    /**
     * Get menu item's ancestors
     * @param  object $item
     * @return array  $active_anc_item_ids
     */
    public function get_item_ancestors( $item ) {
        $anc_id = absint( $item->db_id );

        $active_anc_item_ids = array();
        while (
            $anc_id = get_post_meta( $anc_id, '_menu_item_menu_item_parent', true )
            AND ! in_array( $anc_id, $active_anc_item_ids )
        )
        $active_anc_item_ids[] = $anc_id;

        return $active_anc_item_ids;
    }

    function set($post_type, $settings = null)
    {
        $data = get_post_type_object( $post_type );
        if ( empty( $data ) ) {

        }
        $this->data = $data;
        $this->settings = is_null( $settings ) ? $this->get_settings( $post_type ) : (array) $settings;
    }

    function _get_labels($data)
    {
        $data = (array) $data;
        return isset( $data['labels'] ) ? (object) $data['labels'] : new stdClass();
    }

    function check_singular_plural_match($data = null)
    {
        if ( is_null( $data ) ) {
            $data = $this->data;
        }
        $labels = $this->_get_labels( $data );
        if ( array_key_exists( 'ignore', $labels ) && 'on' == $labels->ignore ) {
            return false;
        }
        return strtolower( $labels->singular_name ) == strtolower( $labels->name );
    }

    function message($message_id)
    {
        $this->_set_messenger();
        return isset( $this->messages[$message_id] ) ? $this->messages[$message_id] : 'Howdy!';
    }

    function _set_messenger()
    {
        if ( is_null( $this->messages ) ) {
            include dirname( __FILE__ ) . '/post-types/messages.php';
            $this->messages = $messages;
        }
    }

    function get_settings($post_type)
    {
        return wpcf_get_custom_post_type_settings( $post_type );
    }

}
