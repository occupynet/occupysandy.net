<?php
/**
 *
 *
 */
require_once 'class.textfield.php';

class WPToolset_Field_Skype extends WPToolset_Field_Textfield
{
    protected $_defaults = array(
        'skypename' => '',
        'action' => 'chat',
        'color' => 'blue',
        'size' => 32,
    );

    public function init()
    {
        add_action( 'admin_footer', array($this, 'editButtonTemplate') );
        add_action( 'wp_footer', array($this, 'editButtonTemplate') );

        wp_register_script(
            'skype-uri-buttom',
            '//www.skypeassets.com/i/scom/js/skype-uri.js'
        );

        wp_register_script(
            'wptoolset-field-skype',
            WPTOOLSET_FORMS_RELPATH . '/js/skype.js',
            array('jquery', 'skype-uri-buttom'),
            WPTOOLSET_FORMS_VERSION,
            true
        );
        wp_enqueue_script( 'wptoolset-field-skype' );
        add_thickbox();
        $translation = array('title' => esc_js( __( 'Edit Skype button', 'wpv-views' ) ) );
        wp_localize_script( 'wptoolset-field-skype', 'wptSkypeData', $translation );
        $this->set_placeholder_as_attribute();
    }

    public function enqueueStyles() {

    }

    public function metaform() {
        $value = wp_parse_args( $this->getValue(), $this->_defaults );
        $attributes = $this->getAttr();
        if ( isset($attributes['class'] ) ) {
            $attributes['class'] .= ' ';
        } else {
            $attributes['class'] = '';
        }
        $attributes['class'] = 'js-wpt-skypename js-wpt-cond-trigger regular-text';// What is this js-wpt-cond-trigger classname for?
        $form = array();
        $form[] = array(
            '#type' => 'textfield',
            '#title' => $this->getTitle(),
            '#description' => $this->getDescription(),
            '#name' => $this->getName() . "[skypename]",
            '#attributes' => array(),
            '#value' => $value['skypename'],
            '#validate' => $this->getValidationData(),
            '#attributes' => $attributes,
            '#repetitive' => $this->isRepetitive(),
        );

        /**
         * action
         */
        $form[] = array(
            '#type' => 'hidden',
            '#value' => $value['action'],
            '#name' => $this->getName() . '[action]',
            '#attributes' => array('class' => 'js-wpt-skype-action'),
        );

        /**
         * color
         */
        $form[] = array(
            '#type' => 'hidden',
            '#value' => $value['color'],
            '#name' => $this->getName() . '[color]',
            '#attributes' => array('class' => 'js-wpt-skype-color'),
        );

        /**
         * size
         */
        $form[] = array(
            '#type' => 'hidden',
            '#value' => $value['size'],
            '#name' => $this->getName() . '[size]',
            '#attributes' => array('class' => 'js-wpt-skype-size'),
        );

        if (!is_admin()) {
            return $form;
        }
        $button_element = array(
            '#name' => '',
            '#type' => 'button',
            '#value' => esc_attr( __( 'Edit Skype', 'wpv-views' ) ),
            '#attributes' => array(
                'class' => 'js-wpt-skype-edit-button button button-small button-secondary',
            ),
        );
        foreach( $value as $key => $val ) {
            $button_element['#attributes']['data-'.esc_attr($key)] = $val;
        }
        $form[] = $button_element;
        return $form;
    }

    public function editButtonTemplate()
    {

        static $edit_button_template_template_already_loaded;

        if ( $edit_button_template_template_already_loaded ) {
            return;
        }

        $edit_button_template_template_already_loaded = true;

        $form = array();
        $form['full-open'] = array(
            '#type' => 'markup',
            '#markup' => '<div id="tpl-wpt-skype-edit-button" style="display:none;"><div id="wpt-skype-edit-button-popup">',
        );
        $form['preview'] = array(
            '#type' => 'markup',
            '#markup' => sprintf(
                '<div id="wpt-skype-edit-button-popup-preview"><p class="bold">%s</p><div id="wpt-skype-edit-button-popup-preview-button"><div id="wpt-skype-preview"></div><small style="display:none">%s</small></div><p class="description"><strong>%s</strong>: %s</p></div>',
                __('Preview of your Skype button', 'wpv-views'),
                __('*Hover over to see the menu', 'wpv-views'),
                __('Note', 'wpv-views'),
                __('Skype button background is transparent and will work on any colour backgrounds.', 'wpv-views')
            ),
        );
        $form['options-open'] = array(
            '#type' => 'markup',
            '#markup' => '<div class="main">',
        );
        $form['skypename'] = array(
            '#type' => 'textfield',
            '#name' => 'skype[name]',
            '#attributes' => array(
                'class' => 'js-wpt-skypename-popup js-wpt-skype',
                'data-skype-field-name' => 'skypename',
            ),
            '#before' => sprintf('<h3>%s</h2>', __( 'Enter your Skype Name', 'wpv-views' )),
        );
        $form['skype-action'] = array(
            '#type' => 'checkboxes',
            '#name' => 'skype[action]',
            '#options' => array(
                'call' => array(
                    '#name' => 'skype[action][call]',
                    '#value' => 'call',
                    '#title' => __('Call', 'wpv-views'),
                    '#description' => __('Start a call with just a click.', 'wpv-views'),
                    '#default_value' => 'call',
                    '#attributes' => array(
                        'class' => 'js-wpt-skype js-wpt-skype-action js-wpt-skype-action-call',
                        'data-skype-field-name' => 'action',
                    ),
                ),
                'chat' => array(
                    '#name' => 'skype[action][chat]',
                    '#title' => __('Chat', 'wpv-views'),
                    '#value' => 'chat',
                    '#description' => __('Start the conversation with an instant message.', 'wpv-views'),
                    '#attributes' => array(
                        'class' => 'js-wpt-skype js-wpt-skype-action js-wpt-skype-action-chat',
                        'data-skype-field-name' => 'action',
                    ),
                ),
            ),
            '#before' =>  sprintf('<h3>%s</h3>', __( "Choose what you'd like your button to do", 'wpv-views' )),
        );

        $form['skype-color-header'] = array(
            '#type' => 'markup',
            '#markup' =>  sprintf('<h3>%s</h3>', __( 'Choose how you want your button to look', 'wpv-views' )),
        );

        $form['skype-color'] = array(
            '#type' => 'select',
            '#name' => 'skype[color]',
            '#options' => array(
                array(
                    '#value' => 'blue',
                    '#title' => __('Blue', 'wpv-views'),
                    '#attributes' => array(
                        'data-skype-field-name' => 'color',
                        'class' => 'js-wpt-skype',
                    ),
                ),
                array(
                    '#value' => 'white',
                    '#title' => __('White', 'wpv-views'),
                    '#attributes' => array(
                        'data-skype-field-name' => 'color',
                        'class' => 'js-wpt-skype',
                    ),
                ),
            ),
            '#default_value' => 'blue',
            '#attributes' => array(
                'class' => 'js-wpt-skype js-wpt-skype-color'
            ),
            '#inline' => true,
        );
        $form['skype-size'] = array(
            '#type' => 'select',
            '#name' => 'skype[size]',
            '#options' => array(),
            '#default_value' => 32,
            '#attributes' => array(
                'class' => 'js-wpt-skype js-wpt-skype-size'
            ),
            '#inline' => true,
        );
        foreach( array(10,12,14,16,24,32) as $size ) {
            $form['skype-size']['#options'][] = array(
                '#value' => $size,
                '#title' => sprintf('%dpx', $size),
                '#attributes' => array(
                    'data-skype-field-name' => 'size',
                    'class' => 'js-wpt-skype',
                ),
            );
        }
        $form['options-close'] = array(
            '#type' => 'markup',
            '#markup' => '</div>',
        );

        $form['submit'] = array(
            '#type' => 'button',
            '#name' => 'skype[submit]',
            '#attributes' => array(
                'class' => 'button-secondary js-wpt-close-thickbox',
            ),
            '#value' => __( 'Save', 'wpv-views' ),
        );

        $form['full-close'] = array(
            '#type' => 'markup',
            '#markup' => '</div></div>',
        );

        $theForm = new Enlimbo_Forms( __FUNCTION__ );
        $theForm->autoHandle( __FUNCTION__, $form);
        echo $theForm->renderElements($form);
    }

    public function editform( $config = null ) {

    }

    public function mediaEditor(){
        return array();
    }

}
