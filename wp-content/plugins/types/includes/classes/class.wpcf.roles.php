<?php

define('WPCF_CUSTOM_POST_TYPE_VIEW',        'wpcf_custom_post_type_view');
define('WPCF_CUSTOM_POST_TYPE_EDIT',        'wpcf_custom_post_type_edit');
define('WPCF_CUSTOM_POST_TYPE_EDIT_OTHERS', 'wpcf_custom_post_type_edit_others');

define('WPCF_CUSTOM_TAXONOMY_VIEW',         'wpcf_custom_taxonomy_view');
define('WPCF_CUSTOM_TAXONOMY_EDIT',         'wpcf_custom_taxonomy_edit');
define('WPCF_CUSTOM_TAXONOMY_EDIT_OTHERS',  'wpcf_custom_taxonomy_edit_others');

define('WPCF_CUSTOM_FIELD_VIEW',            'wpcf_custom_field_view');
define('WPCF_CUSTOM_FIELD_EDIT',            'wpcf_custom_field_edit');
define('WPCF_CUSTOM_FIELD_EDIT_OTHERS',     'wpcf_custom_field_edit_others');

define('WPCF_USER_META_FIELD_VIEW',         'wpcf_user_meta_field_view');
define('WPCF_USER_META_FIELD_EDIT',         'wpcf_user_meta_field_edit');
define('WPCF_USER_META_FIELD_EDIT_OTHERS',  'wpcf_user_meta_field_edit_others');

define('WPCF_EDIT',                         'manage_options');

/**
 * Class to Rule for Access
 *
 * @since 1.8
 *
 */
class WPCF_Roles
{
    private static $instance = null;
    private $users_settings;
    private static $users_settings_name = 'wpcf_users_options';

    protected static $perms_to_pages = array();

    private function __construct()
    {
        $this->users_settings = get_option(self::$users_settings_name, false);

        add_action('init', array($this, 'add_caps'), 99 );
        add_action('edit_user_profile', array($this, 'edit_user_profile'));
        add_filter('wpcf_access_custom_capabilities', array($this, 'wpcf_access_custom_capabilities'), 50);
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new WPCF_Roles();
        }
        return self::$instance;
    }

    public static function edit_user_profile()
    {
        update_option(self::$users_settings_name, false);
    }

    public static function wpcf_access_custom_capabilities($data)
    {
        $wp_roles['label'] = __('Types capabilities', 'wpcf');
        $wp_roles['capabilities'] = self::wpcf_get_capabilities();
        $data[] = $wp_roles;
        return $data;
    }

    public static final function wpcf_get_capabilities()
    {
        return array(

            WPCF_CUSTOM_POST_TYPE_VIEW        => __('View Custom Post Types', 'wpcf'),
            WPCF_CUSTOM_POST_TYPE_EDIT        => __('Create and edit my Custom Post Types', 'wpcf'),
            WPCF_CUSTOM_POST_TYPE_EDIT_OTHERS => __('Edit others Custom Post Types', 'wpcf'),

            WPCF_CUSTOM_TAXONOMY_VIEW         => __('View Custom Taxonomies', 'wpcf'),
            WPCF_CUSTOM_TAXONOMY_EDIT         => __('Create and edit my Custom Taxonomies', 'wpcf'),
            WPCF_CUSTOM_TAXONOMY_EDIT_OTHERS  => __('Edit others Custom Taxonomies', 'wpcf'),

            WPCF_CUSTOM_FIELD_VIEW            => __('View Custom Fields', 'wpcf'),
            WPCF_CUSTOM_FIELD_EDIT            => __('Create and edit my Custom Fields', 'wpcf'),
            WPCF_CUSTOM_FIELD_EDIT_OTHERS     => __('Edit others Custom Fields', 'wpcf'),

            WPCF_USER_META_FIELD_VIEW         => __('View User Meta Fields', 'wpcf'),
            WPCF_USER_META_FIELD_EDIT         => __('Create and edit my User Meta Fields', 'wpcf'),
            WPCF_USER_META_FIELD_EDIT_OTHERS  => __('Edit others User Meta Fields', 'wpcf'),

        );
    }

    public static function get_cap_for_page($page)
    {
        return self::$perms_to_pages[$page] ? self::$perms_to_pages[$page] : WPCF_EDIT;
    }

    public function add_caps()
    {
        if( $this->users_settings ){
            return;
        }

        global $wp_roles;

        if ( ! isset( $wp_roles ) || ! is_object( $wp_roles ) ) {
            $wp_roles = new WP_Roles();
        }

        $wpcf_capabilities = array_keys( self::wpcf_get_capabilities() );

        $roles = $wp_roles->get_names();
        foreach ( $roles as $current_role => $role_name ) {
            $capability_can = apply_filters( 'wpcf_capability_can', 'manage_options' );
            if ( isset( $wp_roles->roles[ $current_role ][ 'capabilities' ][ $capability_can ] ) ) {
                $role = get_role( $current_role );
                if ( isset( $role ) && is_object( $role ) ) {
                    for ( $i = 0, $caps_limit = count( $wpcf_capabilities ); $i < $caps_limit; $i ++ ) {
                        if ( ! isset( $wp_roles->roles[ $current_role ][ 'capabilities' ][ $wpcf_capabilities[ $i ] ] ) ) {
                            $role->add_cap( $wpcf_capabilities[ $i ] );
                        }
                    }
                }
            }
        }

        //Set new caps for all Super Admins
        $super_admins = get_super_admins();
        foreach ( $super_admins as $admin ) {
            $updated_current_user = new WP_User( $admin );
            for ( $i = 0, $caps_limit = count( $wpcf_capabilities ); $i < $caps_limit; $i ++ ) {
                $updated_current_user->add_cap( $wpcf_capabilities[ $i ] );
            }
        }

        // We need to refresh $current_user caps to display the entire NNN menu

        // If $current_user has not been updated yet with the new capabilities,
        global $current_user;
        if ( isset( $current_user ) && isset( $current_user->ID ) ) {

            // Insert the capabilities for the current execution
            $updated_current_user = new WP_User( $current_user->ID );

            for ( $i = 0, $caps_limit = count( $wpcf_capabilities ); $i < $caps_limit; $i ++ ) {
                if ( $updated_current_user->has_cap($wpcf_capabilities[$i]) ) {
                    $current_user->add_cap($wpcf_capabilities[$i]);
                }
            }

            // Refresh $current_user->allcaps
            $current_user->get_role_caps();
        }

        $this->users_settings = true;
        update_option(self::$users_settings_name, $this->users_settings);
    }

    public function disable_all_caps()
    {
        global $wp_roles;

        if ( ! isset( $wp_roles ) || ! is_object( $wp_roles ) ) {
            $wp_roles = new WP_Roles();
        }

        $wpcf_capabilities = array_keys( self::wpcf_get_capabilities() );

        foreach ( $wpcf_capabilities as $cap ) {
            foreach (array_keys($wp_roles->roles) as $role) {
                $wp_roles->remove_cap($role, $cap);
            }
        }

        //Remove caps for all Super Admins
        $super_admins = get_super_admins();
        foreach ( $super_admins as $admin ) {
            $user = new WP_User( $admin );
            for ( $i = 0, $caps_limit = count( $wpcf_capabilities ); $i < $caps_limit; $i ++ ) {
                $user->remove_cap( $wpcf_capabilities[ $i ] );
            }
        }

    }

    public static function user_can_create($type = 'custom-post-type')
    {
        switch( $type ) {
        case 'custom-post-type':
            return current_user_can( WPCF_CUSTOM_POST_TYPE_EDIT );
        case 'custom-taxonomy':
            return current_user_can( WPCF_CUSTOM_TAXONOMY_EDIT );
        case 'custom-field':
            return current_user_can( WPCF_CUSTOM_FIELD_EDIT );
        case 'user-meta-field':
            return current_user_can( WPCF_USER_META_FIELD_EDIT );
        }
        return false;
    }

    public static function user_can_edit_other($type = 'custom-post-type')
    {
        switch( $type ) {
        case 'custom-post-type':
            return current_user_can( WPCF_CUSTOM_POST_TYPE_EDIT_OTHERS );
        case 'custom-taxonomy':
            return current_user_can( WPCF_CUSTOM_TAXONOMY_EDIT_OTHERS );
        case 'custom-field':
            return current_user_can( WPCF_CUSTOM_FIELD_EDIT_OTHERS );
        case 'user-meta-field':
            return current_user_can( WPCF_USER_META_FIELD_EDIT_OTHERS );
        }
        return false;
    }

    public static function user_can_edit($type = 'custom-post-type', $item)
    {
        /**
         * check only for proper data
         */
        if ( !is_array($item) ) {
            return false;
        }
        /**
         * add new
         */
        switch( $type) {
        case 'custom-post-type':
        case 'custom-taxonomy':
            if ( !isset($item['slug'] ) || empty($item['slug']) ) {
                return self::user_can_create($type);
            }
            break;
        case 'custom-field':
        case 'user-meta-field':
            if ( !isset($item['id'] ) || empty($item['id']) ) {
                return self::user_can_create($type);
            }
            break;
        }
        /**
         * if can edit other, then can edit always
         */
        if ( self::user_can_edit_other($type) ) {
            return true;
        }
        /**
         * if item has no autor or empty athor, then:
         * no! you can not edit
         */
        if ( !isset($item[WPCF_AUTHOR]) || empty($item[WPCF_AUTHOR]) ) {
            return false;
        }
        /**
         * no user - no edit
         */
        $user_id = get_current_user_id();
        if (empty($user_id) ) {
            return false;
        }
        /**
         * if author match, check can edit
         */
        return ( $item[WPCF_AUTHOR] == $user_id ) && self::user_can_create( $type );
    }

    public static function user_can_view()
    {
        switch( $type ) {
        case 'custom-post-type':
            return current_user_can( WPCF_CUSTOM_POST_TYPE_VIEW);
        case 'custom-taxonomy':
            return current_user_can( WPCF_CUSTOM_TAXONOMY_VIEW );
        case 'custom-field':
            return current_user_can( WPCF_CUSTOM_FIELD_VIEW );
        case 'user-meta-field':
            return current_user_can( WPCF_USER_META_FIELD_VIEW );
        }
        return false;
    }

    public static function user_can_edit_custom_post_by_slug($slug)
    {
        $entries = get_option(WPCF_OPTION_NAME_CUSTOM_TYPES, array());
        if (isset($entries[$slug])) {
            return self::user_can_edit('custom-post-type', $entries[$slug]);
        }
        return false;
    }

    public static function user_can_edit_custom_taxonomy_by_slug($slug)
    {
        $entries = get_option(WPCF_OPTION_NAME_CUSTOM_TAXONOMIES, array());
        if (isset($entries[$slug])) {
            return self::user_can_edit('custom-taxonomy', $entries[$slug]);
        }
        return false;
    }

    public static function user_can_edit_custom_field_group_by_id( $id )
    {
        $item = self::get_entry($id, 'wp-types-group');
        return self::user_can_edit('custom-field', $item);
    }

    public static function user_can_edit_usermeta_field_group_by_id( $id )
    {
        $item = self::get_entry($id, 'wp-types-user-group');
        return self::user_can_edit('user-meta-field', $item);
    }

    private static function get_entry($id, $post_type)
    {
        $args = array(
            'post__in' => array($id),
            'post_type' => $post_type,
        );
        $query = new WP_Query($args);
        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $data = array(
                    'id' => get_the_ID(),
                    WPCF_AUTHOR => get_the_author_meta('ID'),
                );
                wp_reset_postdata();
                return $data;
            }
        }
        return $id;
    }
}
