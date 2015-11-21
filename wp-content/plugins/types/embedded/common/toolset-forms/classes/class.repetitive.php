<?php
/*
 * Repetitive controller
 *
 *
 * If field is repetitive
 * - queues repetitive CSS and JS
 * - renders JS templates in admin footer
 */
class WPToolset_Forms_Repetitive
{
    private $__templates = array();

    function __construct(){
        // Register
        wp_register_script( 'wptoolset-forms-repetitive',
                WPTOOLSET_FORMS_RELPATH . '/js/repetitive.js',
                array('jquery', 'jquery-ui-sortable', 'underscore'), WPTOOLSET_FORMS_VERSION,
                true );
//        wp_register_style( 'wptoolset-forms-repetitive', '' );
        // Render settings
        add_action( 'admin_footer', array($this, 'renderTemplates') );
        add_action( 'wp_footer', array($this, 'renderTemplates') );

        wp_enqueue_script( 'wptoolset-forms-repetitive' );
		
	}

    function add( $config, $html ) {
        if ( !empty( $config['repetitive'] ) ) {
            $this->__templates[$config['id']] = $html;
        }
    }

    function renderTemplates() {
        foreach ( $this->__templates as $id => $template ) {
            echo '<script type="text/html" id="tpl-wpt-field-' . $id . '">'
            . $template . '</script>';
        }
    }
}
