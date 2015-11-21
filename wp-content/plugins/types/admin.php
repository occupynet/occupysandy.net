<?php
/**
 *
 * Admin functions
 *
 *
 */
require_once WPCF_ABSPATH.'/marketing.php';
require_once WPCF_ABSPATH.'/includes/classes/class.wpcf.roles.php';
WPCF_Roles::getInstance();
/*
 * This needs to be called after main 'init' hook.
 * Main init hook calls required Types code for frontend.
 * Admin init hook only in admin area.
 *
 * TODO Revise it to change to 'admin_init'
 */
add_action( 'admin_init', 'wpcf_admin_init_hook', 11 );
add_action( 'admin_menu', 'wpcf_admin_menu_hook' );
add_action( 'wpcf_admin_page_init', 'wpcf_enqueue_scripts' );
add_action( 'admin_enqueue_scripts', 'wpcf_admin_enqueue_scripts' );

wpcf_admin_load_teasers( array('types-access.php') );
if ( defined( 'DOING_AJAX' ) ) {
    require_once WPCF_INC_ABSPATH . '/ajax.php';
}
include_once WPCF_ABSPATH.'/includes/classes/class.wpcf.marketing.messages.php';
new WPCF_Types_Marketing_Messages();

/**
 * last edit flag
 */
if ( !defined('TOOLSET_EDIT_LAST' )){
    define( 'TOOLSET_EDIT_LAST', '_toolset_edit_last');
}

/**
 * last author
 */
if ( !defined('WPCF_AUTHOR' )){
    define( 'WPCF_AUTHOR', '_wpcf_author_id');
}

/**
 * admin_init hook.
 */
function wpcf_admin_init_hook()
{
    wp_register_style('wpcf-css-embedded', WPCF_EMBEDDED_RES_RELPATH . '/css/basic.css', array(), WPCF_VERSION );

    wp_enqueue_style( 'wpcf-promo-tabs', WPCF_EMBEDDED_RES_RELPATH . '/css/tabs.css', array(), WPCF_VERSION );
    wp_enqueue_style('toolset-dashicons');
}

/**
 * admin_menu hook.
 */
function wpcf_admin_menu_hook()
{
    $wpcf_capability = apply_filters( 'wpcf_capability', WPCF_CUSTOM_POST_TYPE_VIEW);

    add_menu_page(
        __( 'Types', 'wpcf' ),
        __( 'Types', 'wpcf' ),
        $wpcf_capability,
        'wpcf',
        'wpcf_admin_menu_summary',
        'none'
    );

    $subpages = array();

    // Custom Post Types
    $subpages['wpcf-cpt'] = array(
        'menu_title' => __( 'Post Types', 'wpcf' ),
        'function'   => 'wpcf_admin_menu_summary_cpt',
        'capability_filter' => 'wpcf_cpt_view',
        'capability' => WPCF_CUSTOM_POST_TYPE_VIEW,
    );

    // Custom Taxonomies
    $subpages['wpcf-ctt'] = array(
        'menu_title' => __( 'Custom Taxonomies', 'wpcf' ),
        'function'   => 'wpcf_admin_menu_summary_ctt',
        'capability_filter' => 'wpcf_ctt_view',
        'capability' => WPCF_CUSTOM_TAXONOMY_VIEW,
    );

    // Custom fields
    $subpages['wpcf-cf'] = array(
        'menu_title' => __( 'Custom Fields', 'wpcf' ),
        'function'   => 'wpcf_admin_menu_summary',
        'capability_filter' => 'wpcf_cf_view',
        'capability' => WPCF_CUSTOM_FIELD_VIEW,
    );

    // User Meta
    $subpages['wpcf-um'] = array(
        'menu_title' => __( 'User Fields', 'wpcf' ),
        'function'   => 'wpcf_usermeta_summary',
        'capability_filter' => 'wpcf_uf_view',
        'capability' => WPCF_USER_META_FIELD_VIEW,
    );

    // Settings
    $subpages['wpcf-custom-settings'] = array(
        'menu_title' => __( 'Settings', 'wpcf' ),
        'function'   => 'wpcf_admin_menu_settings',
    );

    foreach( $subpages as $menu_slug => $menu ) {
        wpcf_admin_add_submenu_page($menu, $menu_slug);
    }

    if ( isset( $_GET['page'] ) ) {
        $current_page = $_GET['page'];
        switch ( $current_page ) {
    /**
     * User Fields Control
     */
        case 'wpcf-user-fields-control':
            wpcf_admin_add_submenu_page(
                array(
                    'menu_title' => __( 'User Fields Control', 'wpcf' ),
                    'function'   => 'wpcf_admin_menu_user_fields_control',
                    'capability_filter' => 'wpcf_ufc_view',
                ),
                'wpcf-user-fields-control'
            );
            break;

    /**
     *  Custom Fields Control
     */
        case 'wpcf-custom-fields-control':
            wpcf_admin_add_submenu_page(
                array(
                    'menu_title' => __( 'Custom Fields Control', 'wpcf' ),
                    'function'   => 'wpcf_admin_menu_custom_fields_control',
                    'capability_filter' => 'wpcf_cfc_view',
                ),
                'wpcf-custom-fields-control'
            );
            break;
    /**
     * Import/Export
     */
        case 'wpcf-import-export':
            wpcf_admin_add_submenu_page(
                array(
                    'menu_title' => __( 'Import/Export', 'wpcf' ),
                    'function'   => 'wpcf_admin_menu_import_export',
                ),
                'wpcf-import-export'
            );
            break;

            /**
             * debug
             */
        case 'wpcf-debug-information':
            wpcf_admin_add_submenu_page(
                array(
                    'menu_title' => __( 'Debug Information', 'wpcf' ),
                    'function' => 'wpcf_admin_menu_debug_information',
                ),
                'wpcf-debug-information'
            );
            break;
            /**
             * custom field grup
             */
        case 'wpcf-edit':
            $title = isset( $_GET['group_id'] ) ? __( 'Edit Group', 'wpcf' ) : __( 'Add New Custom Fields Group', 'wpcf' );
            $hook = wpcf_admin_add_submenu_page(
                array(
                    'menu_title' => $title,
                    'function' => 'wpcf_admin_menu_edit_fields',
                    'capability' => WPCF_CUSTOM_FIELD_VIEW
                ),
                $current_page
            );
            add_action( 'load-' . $hook, 'wpcf_admin_menu_edit_fields_hook' );
            wpcf_admin_plugin_help( $hook, 'wpcf-edit' );
            break;

        case 'wpcf-view-custom-field':
            $hook = wpcf_admin_add_submenu_page(
                array(
                    'menu_title' => __('View Custom Fields Group', 'wpcf'),
                    'function' => 'wpcf_admin_menu_edit_fields',
                    'capability' => WPCF_CUSTOM_FIELD_VIEW
                ),
                $current_page
            );
            wpcf_admin_plugin_help( $hook, 'wpcf-edit' );
            break;
            /**
             * custom post
             */
        case 'wpcf-edit-type':
            $title = __( 'Add New Custom Post Type', 'wpcf' );
            if ( isset( $_GET['wpcf-post-type'] ) ) {
                $title = __( 'Edit Custom Post Type', 'wpcf' );
                if ( wpcf_is_builtin_post_types($_GET['wpcf-post-type']) ) {
                    $title = __( 'Edit Post Type', 'wpcf' );
                }
            }
            $hook = wpcf_admin_add_submenu_page(
                array(
                    'menu_title' => $title,
                    'function' => 'wpcf_admin_menu_edit_type',
                    'capability' => WPCF_CUSTOM_FIELD_EDIT
                ),
                $current_page
            );
            add_action( 'load-' . $hook, 'wpcf_admin_menu_edit_type_hook' );
            wpcf_admin_plugin_help( $hook, 'wpcf-edit-type' );
            break;

        case 'wpcf-view-type':
            $hook = wpcf_admin_add_submenu_page(
                array(
                    'menu_title' => __('View Custom Post Type', 'wpcf'),
                    'function' => 'wpcf_admin_menu_edit_type',
                    'capability' => WPCF_CUSTOM_FIELD_VIEW
                ),
                $current_page
            );
            add_action( 'load-' . $hook, 'wpcf_admin_menu_edit_type_hook' );
            wpcf_admin_plugin_help( $hook, 'wpcf-edit-type' );
            break;

        case 'wpcf-edit-tax':
            $title = isset( $_GET['wpcf-tax'] ) ? __( 'Edit Custom Taxonomy', 'wpcf' ) : __( 'Add New Custom Taxonomy', 'wpcf' );
            $hook = wpcf_admin_add_submenu_page(
                array(
                    'menu_title' => $title,
                    'function' => 'wpcf_admin_menu_edit_tax',
                    'capability' => WPCF_CUSTOM_TAXONOMY_EDIT
                ),
                $current_page
            );
            add_action( 'load-' . $hook, 'wpcf_admin_menu_edit_tax_hook' );
            wpcf_admin_plugin_help( $hook, 'wpcf-edit-tax' );
            break;

        case 'wpcf-view-tax':
            $hook = wpcf_admin_add_submenu_page(
                array(
                    'menu_title' => __('View Custom Taxonomy', 'wpcf'),
                    'function' => 'wpcf_admin_menu_edit_tax',
                    'capability' => WPCF_CUSTOM_TAXONOMY_VIEW
                ),
                $current_page
            );
            add_action( 'load-' . $hook, 'wpcf_admin_menu_edit_tax_hook' );
            wpcf_admin_plugin_help( $hook, 'wpcf-edit-tax' );
            break;

            /**
             * user meta fields
             */
        case 'wpcf-edit-usermeta':
            $title = isset( $_GET['group_id'] ) ? __( 'Edit User Fields Group', 'wpcf' ) : __( 'Add New User Fields Group', 'wpcf' );
            $hook = wpcf_admin_add_submenu_page(
                array(
                    'menu_title' => $title,
                    'function' => 'wpcf_admin_menu_edit_user_fields',
                    'capability' => WPCF_USER_META_FIELD_EDIT,
                ),
                $current_page
            );
            wpcf_admin_plugin_help( $hook, 'wpcf-edit-usermeta' );
            break;

        case 'wpcf-view-usermeta':
            $hook = wpcf_admin_add_submenu_page(
                array(
                    'menu_title' => __('View User Fields Group', 'wpcf'),
                    'function' => 'wpcf_admin_menu_edit_user_fields',
                    'capability' => WPCF_USER_META_FIELD_VIEW,
                ),
                $current_page
            );
            wpcf_admin_plugin_help( $hook, 'wpcf-edit-usermeta' );
            break;
        }
    }

    // Check if migration from other plugin is needed
    if (
        (class_exists( 'Acf') && !class_exists('acf_pro'))
        || defined( 'CPT_VERSION' ) 
    ) {
        $hook = add_submenu_page( 'wpcf', __( 'Migration', 'wpcf' ),
            __( 'Migration', 'wpcf' ), 'manage_options', 'wpcf-migration',
            'wpcf_admin_menu_migration' );
        add_action( 'load-' . $hook, 'wpcf_admin_menu_migration_hook' );
        wpcf_admin_plugin_help( $hook, 'wpcf-migration' );
    }

    do_action( 'wpcf_menu_plus' );

    // remove the repeating Types submenu
    remove_submenu_page( 'wpcf', 'wpcf' );
}

/**
 * Menu page hook.
 */
function wpcf_admin_menu_debug_information()
{
    require_once WPCF_EMBEDDED_ABSPATH.'/common/debug/debug-information.php';
}

/**
 * Menu page hook.
 */
function wpcf_usermeta_summary_hook()
{
    do_action( 'wpcf_admin_page_init' );
    wpcf_admin_load_collapsible();
    wpcf_admin_page_add_options('uf',  __( 'User Fields', 'wpcf' ));
}

/**
 * Menu page hook.
 */
function wpcf_admin_menu_summary_hook()
{
    do_action( 'wpcf_admin_page_init' );
    wpcf_admin_load_collapsible();
    wpcf_admin_page_add_options('cf',  __( 'Custom Fields', 'wpcf' ));
}

/**
 * Menu page display.
 */
function wpcf_admin_menu_summary()
{
    wpcf_add_admin_header( __( 'Custom Fields Groups', 'wpcf' ), array('page'=>'wpcf-edit'));
    require_once WPCF_INC_ABSPATH . '/fields.php';
    require_once WPCF_INC_ABSPATH . '/fields-list.php';
    $to_display = wpcf_admin_fields_get_fields();
    if ( !empty( $to_display ) ) {
        add_action( 'wpcf_groups_list_table_after', 'wpcf_admin_promotional_text' );
    }
    wpcf_admin_fields_list();
    wpcf_add_admin_footer();
}

/**
 * Menu page hook.
 */
function wpcf_admin_menu_edit_fields_hook()
{
    do_action( 'wpcf_admin_page_init' );

    /*
     * Enqueue scripts
     */
    // Group filter
    wp_enqueue_script( 'wpcf-filter-js',
            WPCF_EMBEDDED_RES_RELPATH
            . '/js/custom-fields-form-filter.js', array('jquery'), WPCF_VERSION );
    // Form
    wp_enqueue_script( 'wpcf-form-validation',
            WPCF_EMBEDDED_RES_RELPATH . '/js/'
            . 'jquery-form-validation/jquery.validate.min.js', array('jquery'),
            WPCF_VERSION );
    wp_enqueue_script( 'wpcf-form-validation-additional',
            WPCF_EMBEDDED_RES_RELPATH . '/js/'
            . 'jquery-form-validation/additional-methods.min.js',
            array('jquery'), WPCF_VERSION );
    // Scroll
    wp_enqueue_script( 'wpcf-scrollbar',
            WPCF_EMBEDDED_RELPATH . '/common/visual-editor/res/js/scrollbar.js',
            array('jquery') );
    wp_enqueue_script( 'wpcf-mousewheel',
            WPCF_EMBEDDED_RELPATH . '/common/visual-editor/res/js/mousewheel.js',
            array('wpcf-scrollbar') );
    // MAIN
    wp_enqueue_script( 'wpcf-fields-form',
            WPCF_EMBEDDED_RES_RELPATH
            . '/js/fields-form.js', array('wpcf-js') );

    /*
     * Enqueue styles
     */
    wp_enqueue_style( 'wpcf-scroll',
            WPCF_EMBEDDED_RELPATH . '/common/visual-editor/res/css/scroll.css' );

    //Css editor
    wp_enqueue_script( 'wpcf-form-codemirror' ,
        WPCF_RELPATH . '/resources/js/codemirror234/lib/codemirror.js', array('wpcf-js'));
    wp_enqueue_script( 'wpcf-form-codemirror-css-editor' ,
        WPCF_RELPATH . '/resources/js/codemirror234/mode/css/css.js', array('wpcf-js'));
    wp_enqueue_script( 'wpcf-form-codemirror-html-editor' ,
        WPCF_RELPATH . '/resources/js/codemirror234/mode/xml/xml.js', array('wpcf-js'));
    wp_enqueue_script( 'wpcf-form-codemirror-html-editor2' ,
        WPCF_RELPATH . '/resources/js/codemirror234/mode/htmlmixed/htmlmixed.js', array('wpcf-js'));
    wp_enqueue_script( 'wpcf-form-codemirror-editor-resize' ,
        WPCF_RELPATH . '/resources/js/jquery_ui/jquery.ui.resizable.min.js', array('wpcf-js'));

    wp_enqueue_style( 'wpcf-css-editor',
            WPCF_RELPATH . '/resources/js/codemirror234/lib/codemirror.css' );
    wp_enqueue_style( 'wpcf-css-editor-resize',
            WPCF_RELPATH . '/resources/js/jquery_ui/jquery.ui.theme.min.css' );
    wp_enqueue_style( 'wpcf-usermeta',
                WPCF_EMBEDDED_RES_RELPATH . '/css/usermeta.css' );

    add_action( 'admin_footer', 'wpcf_admin_fields_form_js_validation' );
    require_once WPCF_INC_ABSPATH . '/fields.php';
    require_once WPCF_INC_ABSPATH . '/fields-form.php';
    $form = wpcf_admin_fields_form();
    wpcf_form( 'wpcf_form_fields', $form );
}

/**
 * Menu page display.
 */
function wpcf_admin_menu_edit_fields()
{
    $title = __('View Custom Fields Group', 'wpcf');
    if ( isset( $_GET['group_id'] ) ) {
        if ( WPCF_Roles::user_can_edit('custom-field', $_GET['group_id']) ) {
            $title = __( 'Edit Custom Fields Group', 'wpcf' );
        }
    } else if ( WPCF_Roles::user_can_create('custom-field')) {
        $title = __( 'Add New Custom Fields Group', 'wpcf' );
    }
    wpcf_add_admin_header( $title );
    wpcf_wpml_warning();
    $form = wpcf_form( 'wpcf_form_fields' );
    echo '<form method="post" action="" class="wpcf-fields-form wpcf-form-validate js-types-show-modal">';
    echo $form->renderForm();
    echo '</form>';
    wpcf_add_admin_footer();
}

function wpcf_admin_page_add_options( $name, $label)
{
    $option = 'per_page';
    $args = array(
        'label' => $label,
        'default' => 10,
        'option' => sprintf('wpcf_%s_%s', $name, $option),
    );
    add_screen_option( $option, $args );
}

function wpcf_admin_menu_summary_cpt_ctt_hook()
{
    do_action( 'wpcf_admin_page_init' );
    wp_enqueue_style( 'wpcf-promo-tabs', WPCF_RES_RELPATH . '/css/tabs.css', array(), WPCF_VERSION );
    wpcf_admin_load_collapsible();
    require_once WPCF_INC_ABSPATH . '/custom-types.php';
    require_once WPCF_INC_ABSPATH . '/custom-taxonomies.php';
    require_once WPCF_INC_ABSPATH . '/custom-types-taxonomies-list.php';
}

/**
 * Menu page hook.
 */
function wpcf_admin_menu_summary_cpt_hook()
{
    wpcf_admin_menu_summary_cpt_ctt_hook();
    wpcf_admin_page_add_options('cpt',  __( 'Post Types', 'wpcf' ));
}

/**
 * Menu page display.
 */
function wpcf_admin_menu_summary_cpt()
{
    wpcf_add_admin_header(
        __( 'Post Types', 'wpcf' ),
        array('page'=>'wpcf-edit-type'),
        __('Add New Custom Post Type', 'wpcf')
    );
    $to_display_posts = get_option( WPCF_OPTION_NAME_CUSTOM_TYPES, array() );
    $to_display_tax = get_option( WPCF_OPTION_NAME_CUSTOM_TAXONOMIES, array() );
    if ( !empty( $to_display_posts ) || !empty( $to_display_tax ) ) {
        add_action( 'wpcf_types_tax_list_table_after', 'wpcf_admin_promotional_text' );
    }
    wpcf_admin_custom_post_types_list();
    wpcf_add_admin_footer();
}

/**
 * Menu page hook.
 */
function wpcf_admin_menu_summary_ctt_hook()
{
    wpcf_admin_menu_summary_cpt_ctt_hook();
    wpcf_admin_page_add_options('ctt',  __( 'Custom Taxonomies', 'wpcf' ));
}

/**
 * Menu page display.
 */
function wpcf_admin_menu_summary_ctt()
{
    wpcf_add_admin_header( __( 'Custom Taxonomies', 'wpcf' ), array('page' => 'wpcf-edit-tax') );
    wpcf_admin_custom_taxonomies_list();
    do_action('wpcf_types_tax_list_table_after');
    wpcf_add_admin_footer();
}

/**
 * Menu page hook.
 */
function wpcf_admin_menu_edit_type_hook()
{
    do_action( 'wpcf_admin_page_init' );
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/custom-types.php';
    require_once WPCF_INC_ABSPATH . '/custom-types-form.php';
    require_once WPCF_INC_ABSPATH . '/post-relationship.php';
    wp_enqueue_script( 'wpcf-custom-types-form',
            WPCF_RES_RELPATH . '/js/'
            . 'custom-types-form.js', array('jquery'), WPCF_VERSION );
    wp_enqueue_script( 'wpcf-form-validation',
            WPCF_RES_RELPATH . '/js/'
            . 'jquery-form-validation/jquery.validate.min.js', array('jquery'),
            WPCF_VERSION );
    wp_enqueue_script( 'wpcf-form-validation-additional',
            WPCF_RES_RELPATH . '/js/'
            . 'jquery-form-validation/additional-methods.min.js',
            array('jquery'), WPCF_VERSION );
    add_action( 'admin_footer', 'wpcf_admin_types_form_js_validation' );
    wpcf_post_relationship_init();
    $form = wpcf_admin_custom_types_form();
    wpcf_form( 'wpcf_form_types', $form );
}

/**
 * Menu page display.
 */
function wpcf_admin_menu_edit_type()
{
    $title = __('View Custom Post Type', 'wpcf');
    if ( WPCF_Roles::user_can_edit('custom-post-type', array()) ) {
        if ( isset( $_GET['wpcf-post-type'] ) ) {
            $title = __( 'Edit Custom Post Type', 'wpcf' );
            if ( wpcf_is_builtin_post_types($_GET['wpcf-post-type']) ) {
                $title = __( 'Edit Post Type', 'wpcf' );
            }
            /**
             * add new CPT link
             */
            $title .= sprintf(
                '<a href="%s" class="add-new-h2">%s</a>',
                esc_url(add_query_arg( 'page', 'wpcf-edit-type', admin_url('admin.php'))),
                __('Add New', 'wpcf')
            );
        } else {
            $title = __( 'Add New Custom Post Type', 'wpcf' );
        }
    }
    wpcf_add_admin_header( $title );
    wpcf_wpml_warning();
    $form = wpcf_form( 'wpcf_form_types' );
    echo '<form method="post" action="" class="wpcf-types-form wpcf-form-validate js-types-do-not-show-modal">';
    echo $form->renderForm();
    echo '</form>';
    wpcf_add_admin_footer();
}

/**
 * Menu page hook.
 */
function wpcf_admin_menu_edit_tax_hook()
{
    do_action( 'wpcf_admin_page_init' );
    wp_enqueue_script( 'wpcf-form-validation',
            WPCF_RES_RELPATH . '/js/'
            . 'jquery-form-validation/jquery.validate.min.js', array('jquery'),
            WPCF_VERSION );
    wp_enqueue_script( 'wpcf-form-validation-additional',
            WPCF_RES_RELPATH . '/js/'
            . 'jquery-form-validation/additional-methods.min.js',
            array('jquery'), WPCF_VERSION );
    add_action( 'admin_footer', 'wpcf_admin_tax_form_js_validation' );
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/custom-taxonomies.php';
    require_once WPCF_INC_ABSPATH . '/custom-taxonomies-form.php';
    $form = wpcf_admin_custom_taxonomies_form();
    wpcf_form( 'wpcf_form_tax', $form );
}

/**
 * Menu page display.
 */
function wpcf_admin_menu_edit_tax()
{
    $title = __( 'View Custom Taxonomy', 'wpcf' );
    if ( WPCF_Roles::user_can_create('custom-taxonomy') ) {
        $title = __( 'Add New Custom Taxonomy', 'wpcf' );
        if ( isset( $_GET['wpcf-tax'] ) ) {
            $title = __( 'Edit Custom Taxonomy', 'wpcf' );
        }
    }
    wpcf_add_admin_header( $title, array('page' => 'wpcf-edit-tax' ));
    wpcf_wpml_warning();
    $form = wpcf_form( 'wpcf_form_tax' );
    echo '<form method="post" action="" class="wpcf-tax-form wpcf-form-validate js-types-show-modal">';
    echo $form->renderForm();
    echo '</form>';
    wpcf_add_admin_footer();
}

/**
 * Menu page hook.
 */
function wpcf_admin_menu_import_export_hook()
{
    do_action( 'wpcf_admin_page_init' );
    require_once WPCF_INC_ABSPATH . '/fields.php';
    require_once WPCF_INC_ABSPATH . '/import-export.php';
    if ( extension_loaded( 'simplexml' ) && isset( $_POST['export'] )
            && wp_verify_nonce( $_POST['_wpnonce'], 'wpcf_import' ) ) {
        wpcf_admin_export_data();
        die();
    }
}

/**
 * Menu page display.
 */
function wpcf_admin_menu_import_export()
{
    wpcf_add_admin_header( __( 'Import/Export', 'wpcf' ) );
    echo '<form method="post" action="" class="wpcf-import-export-form '
    . 'wpcf-form-validate" enctype="multipart/form-data">';
    echo wpcf_form_simple( wpcf_admin_import_export_form() );
    echo '</form>';
    wpcf_add_admin_footer();
}

/**
 * Menu page hook.
 */
function wpcf_admin_menu_custom_fields_control_hook()
{
    do_action( 'wpcf_admin_page_init' );
    add_action( 'admin_head', 'wpcf_admin_custom_fields_control_js' );
    add_thickbox();
    require_once WPCF_INC_ABSPATH . '/fields.php';
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
    require_once WPCF_INC_ABSPATH . '/fields-control.php';

    if ( isset( $_REQUEST['_wpnonce'] )
            && wp_verify_nonce( $_REQUEST['_wpnonce'],
                    'custom_fields_control_bulk' )
            && (isset( $_POST['action'] ) || isset( $_POST['action2'] )) && !empty( $_POST['fields'] ) ) {
        $action = ( $_POST['action'] == '-1' ) ? sanitize_text_field( $_POST['action2'] ) : sanitize_text_field( $_POST['action'] );
        wpcf_admin_custom_fields_control_bulk_actions( $action );
    }

    global $wpcf_control_table;
    $wpcf_control_table = new WPCF_Custom_Fields_Control_Table( array(
                'ajax' => true,
                'singular' => __( 'Custom Field', 'wpcf' ),
                'plural' => __( 'Custom Fields', 'wpcf' ),
                    ) );
    $wpcf_control_table->prepare_items();
}

/**
 * Menu page display.
 */
function wpcf_admin_menu_custom_fields_control()
{
    global $wpcf_control_table;
    wpcf_add_admin_header( __( 'Custom Fields Control', 'wpcf' ) );
    echo '<form method="post" action="" id="wpcf-custom-fields-control-form" class="wpcf-custom-fields-control-form wpcf-form-validate" enctype="multipart/form-data">';
    echo wpcf_admin_custom_fields_control_form( $wpcf_control_table );
    wp_nonce_field( 'custom_fields_control_bulk' );
    echo '</form>';
    wpcf_add_admin_footer();
}

/**
 * Menu page hook.
 */
function wpcf_admin_menu_migration_hook()
{
    do_action( 'wpcf_admin_page_init' );
    require_once WPCF_INC_ABSPATH . '/fields.php';
    require_once WPCF_INC_ABSPATH . '/custom-types.php';
    require_once WPCF_INC_ABSPATH . '/custom-taxonomies.php';
    require_once WPCF_INC_ABSPATH . '/migration.php';
    $form = wpcf_admin_migration_form();
    wpcf_form( 'wpcf_form_migration', $form );
}

/**
 * Menu page display.
 */
function wpcf_admin_menu_migration()
{
    wpcf_add_admin_header( __( 'Migration', 'wpcf' ) );
    echo '<form method="post" action="" id="wpcf-migration-form" class="wpcf-migration-form '
    . 'wpcf-form-validate" enctype="multipart/form-data">';
    $form = wpcf_form( 'wpcf_form_migration' );
    echo $form->renderForm();
    echo '</form>';
    wpcf_add_admin_footer();
}

/**
 * Menu page hook.
 */
function wpcf_admin_menu_settings_hook()
{
    do_action( 'wpcf_admin_page_init' );
    require_once WPCF_INC_ABSPATH . '/settings.php';
    $form = wpcf_admin_general_settings_form();
    wpcf_form( 'wpcf_form_general_settings', $form );
}

/**
 * Menu page display.
 */
function wpcf_admin_menu_settings()
{
    ob_start();
    wpcf_add_admin_header( __( 'Settings', 'wpcf' ) );

    ?>
<form method="post" action="" id="wpcf-general-settings-form" class="wpcf-settings-form wpcf-form-validate">
                    <?php

                    $form = wpcf_form( 'wpcf_form_general_settings' );
                    echo $form->renderForm();

                    ?>
    </form>
    <table class="widefat" id="types-tools">
        <thead>
            <tr>
                <th><?php _e( 'Types tools', 'wpcf' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
<?php

                    $pages = array(
                        'wpcf-custom-fields-control' => array(
                            'page' => 'wpcf-custom-fields-control',
                            'name' => __('Custom Fields Control', 'wpcf'),
                            'description' => __('Allow to control custom fields.', 'wpcf'),
                        ),
                        'wpcf-user-fields-control' => array(
                            'page' => 'wpcf-user-fields-control',
                            'name' => __('User Fields Control', 'wpcf'),
                            'description' => __('Allow to control user meta fields.', 'wpcf'),
                        ),
                        'wpcf-import-export' => array(
                            'page' => 'wpcf-import-export',
                            'name' => __('Import/Export', 'wpcf'),
                            'description' => __('For import or export data from Types.', 'wpcf'),
                        ),
                        'wpcf-access' => array(
                            'page' => 'wpcf-access',
                            'name' => __('Access', 'wpcf'),
                            'description' => __('Access lets you control what content types different users can read, edit and publish on your site and create custom roles.', 'wpcf'),
                        ),
                        'installer' => array(
                            'page' => 'installer',
                            'name' => __('Installer', 'wpcf'),
                            'description' => __('This page lets you install plugins and update existing plugins.', 'wpcf'),
                        ),
                        'wpcf-debug-information' => array(
                            'page' => 'wpcf-debug-information',
                            'name' => __('Debug Information', 'wpcf'),
                            'description' => __( 'For retrieving debug information if asked by a support person.', 'wpcf'),
                        ),
                    );

                    /**
                     * remove Access page if is a full version of Access 
                     * installer and running
                     */
                    if ( defined( 'WPCF_ACCESS_VERSION' ) ) {
                        unset($pages['wpcf-access']);
                    }

                    echo '<ul>';
                    foreach( $pages as $data ) {
                        echo '<li>';
                        printf(
                            '<strong><a href="%s">%s</a></strong>',
                            esc_url( admin_url(sprintf('admin.php?page=%s', $data['page']))),
                            $data['name']
                        );
                        if ( isset($data['description']) && !empty($data['description'])) {
                            echo ' - ';
                            echo $data['description'];
                        }
                        echo '<li>';
                    }
                    echo '</ul>';
?>
                </td>
            </tr>
        </tbody>
    </table>
    <?php
    wpcf_add_admin_footer();

    echo ob_get_clean();
}

/**
 * Adds typical header on admin pages.
 *
 * @param string $title
 * @param string $icon_id Custom icon
 * @return string
 */
function wpcf_add_admin_header($title, $add_new = false, $add_new_title = false)
{
    echo '<div class="wrap">';
    echo '<h2>', $title;
    if ( !$add_new_title ) {
        $add_new_title = __('Add New', 'wpcf');
    }
    if ( is_array($add_new) && isset($add_new['page']) ) {
        $add_button = false;
        /**
         * check user can?
         */
        switch($add_new['page']) {
        case 'wpcf-edit-type':
            $add_button = WPCF_Roles::user_can_create('custom-post-type');
            break;
        case 'wpcf-edit-tax':
            $add_button = WPCF_Roles::user_can_create('custom-taxonomy');
            break;
        case 'wpcf-edit':
            $add_button = WPCF_Roles::user_can_create('custom-field');
            break;
        case 'wpcf-edit-usermeta':
            $add_button = WPCF_Roles::user_can_create('user-meta-field');
            break;
        }
        if ( $add_button ) {
            printf(
                ' <a href="%s" class="add-new-h2">%s</a>',
                esc_url(add_query_arg( $add_new, admin_url('admin.php'))),
                $add_new_title
            );
        }
    }
    echo '</h2>';
    $current_page = sanitize_text_field( $_GET['page'] );
    do_action( 'wpcf_admin_header' );
    do_action( 'wpcf_admin_header_' . $current_page );
}

/**
 * Adds footer on admin pages.
 *
 * <b>Strongly recomended</b> if wpcf_add_admin_header() is called before.
 * Otherwise invalid HTML formatting will occur.
 */
function wpcf_add_admin_footer()
{
    $current_page = sanitize_text_field( $_GET['page'] );
	do_action( 'wpcf_admin_footer_' . $current_page );
    do_action( 'wpcf_admin_footer' );
    echo '</div>';
}

/**
 * Returns HTML formatted 'widefat' table.
 *
 * @param type $ID
 * @param type $header
 * @param type $rows
 * @param type $empty_message
 */
function wpcf_admin_widefat_table( $ID, $header, $rows = array(), $empty_message = 'No results' )
{
    if ( 'No results' == $empty_message ) {
        $empty_message = __('No results', 'wpcf');
    }
    $head = '';
    $footer = '';
    foreach ( $header as $key => $value ) {
        $head .= '<th id="wpcf-table-' . $key . '">' . $value . '</th>' . "\r\n";
        $footer .= '<th>' . $value . '</th>' . "\r\n";
    }
    echo '<table id="' . $ID . '" class="widefat" cellspacing="0">
            <thead>
                <tr>
                  ' . $head . '
                </tr>
            </thead>
            <tfoot>
                <tr>
                  ' . $footer . '
                </tr>
            </tfoot>
            <tbody>
              ';
    $row = '';
    if ( empty( $rows ) ) {
        echo '<tr><td colspan="' . count( $header ) . '">' . $empty_message
        . '</td></tr>';
    } else {
        $i = 0;
        foreach ( $rows as $row ) {
            $classes = array();
            if ( $i++%2 ) {
                $classes[] =  'alternate';
            }
            if ( isset($row['status']) && 'inactive' == $row['status'] ) {
                $classes[] = sprintf('status-%s', $row['status']);
            };
            printf('<tr class="%s">', implode(' ', $classes ));
            foreach ( $row as $column_name => $column_value ) {
                if ( preg_match( '/^(status|raw_name)$/', $column_name )) {
                    continue;
                }
                echo '<td class="wpcf-table-column-' . $column_name . '">';
                echo $column_value;
                echo '</td>' . "\r\n";
            }
            echo '</tr>' . "\r\n";
        }
    }
    echo '
            </tbody>
          </table>' . "\r\n";
}

/**
 * Admin tabs.
 *
 * @param type $tabs
 * @param type $page
 * @param type $default
 * @param type $current
 * @return string
 */
function wpcf_admin_tabs($tabs, $page, $default = '', $current = '')
{
    if ( empty( $current ) && isset( $_GET['tab'] ) ) {
        $current = sanitize_text_field( $_GET['tab'] );
    } else {
        $current = $default;
    }
    $output = '<h2 class="nav-tab-wrapper">';
    foreach ( $tabs as $tab => $name ) {
        $class = ( $tab == $current ) ? ' nav-tab-active' : '';
        $output .= "<a class='nav-tab$class' href='?page=$page&tab=$tab'>$name</a>";
    }
    $output .= '</h2>';
    return $output;
}

/**
 * Saves open fieldsets.
 *
 * @param type $action
 * @param type $fieldset
 */
function wpcf_admin_form_fieldset_save_toggle($action, $fieldset)
{
    $data = get_user_meta( get_current_user_id(), 'wpcf-form-fieldsets-toggle',
            true );
    if ( $action == 'open' ) {
        $data[$fieldset] = 1;
    } elseif ( $action == 'close' ) {
        unset( $data[$fieldset] );
    }
    update_user_meta( get_current_user_id(), 'wpcf-form-fieldsets-toggle', $data );
}

/**
 * Check if fieldset is saved as open.
 *
 * @param type $fieldset
 */
function wpcf_admin_form_fieldset_is_collapsed($fieldset)
{
    $data = get_user_meta( get_current_user_id(), 'wpcf-form-fieldsets-toggle',
            true );
    if ( empty( $data ) ) {
        return true;
    }
    return array_key_exists( $fieldset, $data ) ? false : true;
}

/**
 * Adds help on admin pages.
 *
 * @param type $contextual_help
 * @param type $screen_id
 * @param type $screen
 * @return type
 */
function wpcf_admin_plugin_help($hook, $page)
{
    global $wp_version;
    $call = false;
    $contextual_help = '';
    $page = $page;
    if ( isset( $page ) && isset( $_GET['page'] ) && $_GET['page'] == $page ) {
        switch ( $page ) {
            case 'wpcf-cf':
                $call = 'custom_fields';
                break;

            case 'wpcf-cpt':
                $call = 'post_types_list';
                break;

            case 'wpcf-ctt':
                $call = 'custom_taxonomies_list';
                break;

            case 'wpcf-import-export':
                $call = 'import_export';
                break;

            case 'wpcf-edit':
                $call = 'edit_group';
                break;

            case 'wpcf-edit-type':
                $call = 'edit_type';
                break;

            case 'wpcf-edit-tax':
                $call = 'edit_tax';
                break;

            case 'wpcf':
                $call = 'wpcf';
                break;

            case 'wpcf-um':
                $call = 'user_fields_list';
                break;

            case 'wpcf-edit-usermeta':
                $call = 'user_fields_edit';
                break;
        }
    }
    if ( $call ) {
        require_once WPCF_ABSPATH . '/help.php';
        // WP 3.3 changes
        if ( version_compare( $wp_version, '3.2.1', '>' ) ) {
            wpcf_admin_help_add_tabs($call, $hook, $contextual_help);
        } else {
            $contextual_help = wpcf_admin_help( $call, $contextual_help );
            add_contextual_help( $hook, $contextual_help );
        }
    }
}

/**
 * Promo texts
 *
 * @todo Move!
 */
function wpcf_admin_promotional_text()
{
    $promo_tabs = get_option( '_wpcf_promo_tabs', false );
    // random selection every one hour
    if ( $promo_tabs ) {
        $time = time();
        $time_check = intval( $promo_tabs['time'] ) + 60 * 60;
        if ( $time > $time_check ) {
            $selected = mt_rand( 0, 3 );
            $promo_tabs['selected'] = $selected;
            $promo_tabs['time'] = $time;
            update_option( '_wpcf_promo_tabs', $promo_tabs );
        } else {
            $selected = $promo_tabs['selected'];
        }
    } else {
        $promo_tabs = array();
        $selected = mt_rand( 0, 3 );
        $promo_tabs['selected'] = $selected;
        $promo_tabs['time'] = time();
        update_option( '_wpcf_promo_tabs', $promo_tabs );
    }
}

/**
 * Collapsible scripts.
 */
function wpcf_admin_load_collapsible()
{
    wp_enqueue_script( 'wpcf-collapsible',
            WPCF_RES_RELPATH . '/js/collapsible.js', array('jquery'),
            WPCF_VERSION );
    wp_enqueue_style( 'wpcf-collapsible',
            WPCF_RES_RELPATH . '/css/collapsible.css', array(), WPCF_VERSION );
    $option = get_option( 'wpcf_toggle', array() );
    if ( !empty( $option ) ) {
        $setting = 'new Array("' . implode( '", "', array_keys( $option ) ) . '")';
        wpcf_admin_add_js_settings( 'wpcf_collapsed', $setting );
    }
}

/**
 * Various delete/deactivate content actions.
 *
 * @param type $type
 * @param type $arg
 * @param type $action
 */
function wpcf_admin_deactivate_content($type, $arg, $action = 'delete')
{
    switch ( $type ) {
        case 'post_type':
            // Clean tax relations
            if ( $action == 'delete' ) {
                $custom = get_option( WPCF_OPTION_NAME_CUSTOM_TAXONOMIES, array() );
                foreach ( $custom as $post_type => $data ) {
                    if ( empty( $data['supports'] ) ) {
                        continue;
                    }
                    if ( array_key_exists( $arg, $data['supports'] ) ) {
                        unset( $custom[$post_type]['supports'][$arg] );
                        $custom[$post_type][TOOLSET_EDIT_LAST] = time();
                    }
                }
                update_option( WPCF_OPTION_NAME_CUSTOM_TAXONOMIES, $custom );
            }
            break;

        case 'taxonomy':
            // Clean post relations
            if ( $action == 'delete' ) {
                $custom = get_option( WPCF_OPTION_NAME_CUSTOM_TYPES, array() );
                foreach ( $custom as $post_type => $data ) {
                    if ( empty( $data['taxonomies'] ) ) {
                        continue;
                    }
                    if ( array_key_exists( $arg, $data['taxonomies'] ) ) {
                        unset( $custom[$post_type]['taxonomies'][$arg] );
                        $custom[$post_type][TOOLSET_EDIT_LAST] = time();
                    }
                }
                update_option( WPCF_OPTION_NAME_CUSTOM_TYPES, $custom );
            }
            break;

        default:
            break;
    }
}

/**
 * Loads teasers.
 *
 * @param type $teasers
 */
function wpcf_admin_load_teasers($teasers)
{
    foreach ( $teasers as $teaser ) {
        $file = WPCF_ABSPATH . '/plus/' . $teaser;
        if ( file_exists( $file ) ) {
            require_once $file;
        }
    }
}

/**
 * Get temporary directory
 *
 * @return
 */

function wpcf_get_temporary_directory()
{
    $dir = sys_get_temp_dir();
    if ( !empty( $dir ) && is_dir( $dir ) && is_writable( $dir ) ) {
        return $dir;
    }
    $dir = wp_upload_dir();
    $dir = $dir['basedir'];
    return $dir;
}

/**
 *
 */

function wpcf_admin_enqueue_scripts($hook)
{
    wp_register_script(
        'marketing-getting-started',
        plugin_dir_url( __FILE__ ).'/marketing/getting-started/assets/scripts/getting-started.js',
        array('jquery'),
        WPCF_VERSION,
        true
    );
    if ( preg_match( '@/marketing/getting-started/[^/]+.php$@', $hook ) ) {
        $marketing = new WPCF_Types_Marketing_Messages();
        wp_localize_script(
            'marketing-getting-started',
            'marketing_getting_started',
            array( 'id' => $marketing->get_option_name() )
        );
        wp_enqueue_script('marketing-getting-started');
        wp_enqueue_style(
            'marketing-getting-started',
            plugin_dir_url( __FILE__ ).'/marketing/getting-started/assets/css/getting-started.css',
            array(),
            WPCF_VERSION,
            'all'
        );
    }
}


/**
 * add types configuration to debug
 */

function wpcf_get_extra_debug_info($extra_debug)
{
    $extra_debug['types'] = wpcf_get_settings();
    return $extra_debug;
}

add_filter( 'icl_get_extra_debug_info', 'wpcf_get_extra_debug_info' );

function wpcf_admin_add_submenu_page($menu, $menu_slug = null, $menu_parent = 'wpcf')
{
    if ( !is_admin() ) {
        return;
    }
    $menu_slug = array_key_exists('menu_slug', $menu)? $menu['menu_slug']:$menu_slug;

    $capability = array_key_exists('capability', $menu)? $menu['capability']:'manage_options';;
    $wpcf_capability = apply_filters( 'wpcf_capability', $capability, $menu, $menu_slug );
    $wpcf_capability = apply_filters( 'wpcf_capability'.$menu_slug, $capability, $menu, $menu_slug );

    /**
     * allow change capability  by filter
     * full list https://goo.gl/OJYTvl
     */
    if ( isset($menu['capability_filter'] ) ) {
        $wpcf_capability = apply_filters( $menu['capability_filter'], $wpcf_capability, $menu, $menu_slug );
    }

    /**
     * add submenu
     */
    $hook = add_submenu_page(
        $menu_parent,
        isset($menu['page_title'])? $menu['page_title']:$menu['menu_title'],
        $menu['menu_title'],
        $wpcf_capability,
        $menu_slug,
        array_key_exists('function', $menu)? $menu['function']:null
    );
    if ( !empty($menu_slug) ) {
        wpcf_admin_plugin_help( $hook, $menu_slug );
    }
    /**
     * add action
     */
    if ( !array_key_exists('load_hook', $menu) && array_key_exists('function', $menu) ) {
        $menu['load_hook'] = sprintf( '%s_hook', $menu['function'] );
    }
    if ( !empty($menu['load_hook']) && function_exists( $menu['load_hook'] ) ) {
        $action = sprintf(
            'load-%s',
            array_key_exists('hook', $menu)? $menu['hook']:$hook
        );
        add_action( $action, $menu['load_hook'] );
    }
    /**
     * add submenu to submenu
     */
    if ( array_key_exists('submenu', $menu) ) {
        foreach( $menu['submenu'] as $submenu_slug => $submenu ) {
            wpcf_admin_add_submenu_page($submenu, $submenu_slug, $hook);
        }
    }
    return $hook;
}

/**
 * sort helper for tables
 */
function wpcf_usort_reorder($a,$b)
{
    $orderby = (!empty($_REQUEST['orderby'])) ? sanitize_text_field( $_REQUEST['orderby'] ) : 'title'; //If no sort, default to title
    $order = (!empty($_REQUEST['order'])) ? sanitize_text_field( $_REQUEST['order'] ) : 'asc'; //If no order, default to asc
    if ( ! in_array( $order, array( 'asc', 'desc' ) ) ) {
        $order = 'asc';
    }
    if ('title' == $orderby || !isset($a[$orderby])) {
        $orderby = 'slug';
    }
    $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
    return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
}

add_filter('set-screen-option', 'wpcf_table_set_option', 10, 3);
function wpcf_table_set_option($status, $option, $value)
{
      return $value;
}

