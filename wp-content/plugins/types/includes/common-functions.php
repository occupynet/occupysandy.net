<?php
/**
 *
 * Custom types form - common functions
 *
 *
 */

function wpcf_admin_metabox_end($table_show = true, $_builtin = false)
{
    $markup = '';
    if ( $table_show ) {
        $markup .= '</td></tr></tbody></table>';
    }
    $markup .= '</div></div>';
    $form = array(
        '#type' => 'markup',
        '#markup' => $markup,
    );
    if ( $_builtin ) {
        $form['_builtin'] = true;
    }
    return $form;
}

function wpcf_admin_metabox_begin($title, $id = false, $table_id = false, $table_show = true, $_builtin = false )
{
    $screen = get_current_screen();
    $markup = sprintf(
        '<div class="postbox %s" %s><div title="%s" class="handlediv"><br></div><h3 class="hndle">%s</h3><div class="inside">',
        postbox_classes($id, $screen->id),
        $id? sprintf('id="%s"', $id):'',
        __('Click to toggle', 'wpcf'),
        $title
    );
    if ( $table_show ) {
        $markup .= sprintf(
            '<table %s class="wpcf-types-form-table widefat"><tbody><tr><td>',
            $table_id? sprintf( 'id="%s"', $table_id):''
        );
    }
    $form = array(
        '#type' => 'markup',
        '#markup' => $markup,
    );
    if ( $_builtin ) {
        $form['_builtin'] = true;
    }
    return $form;
}

function wpcf_admin_common_metabox_save($ct, $button_text, $type = 'custom-post-type' )
{
    $form = array();
    if ( WPCF_Roles::user_can_edit($type, $ct) ) {
        $form['submit-open'] = wpcf_admin_metabox_begin(__( 'Save', 'wpcf' ), 'submitdiv', false, false, '_builtin');
        $form['submit-div-open'] = array(
            '#type' => 'markup',
            '#markup' => '<div class="submitbox" id="submitpost"><div id="major-publishing-actions"><div id="publishing-action"><span class="spinner"></span>',
            '_builtin' => true,
        );
        $form['submit'] = array(
            '#type' => 'submit',
            '#name' => 'submit',
            '#value' => $button_text,
            '#attributes' => array(
                'class' => 'button-primary wpcf-disabled-on-submit',
            ),
            '_builtin' => true,
        );
        /**
        * add data attribute for _builtin post type
        */
        if ( isset($ct['_builtin']) && $ct['_builtin'] ) {
            $form['submit']['#attributes']['data-post_type_is_builtin'] = '_builtin';
        }
        $form['submit-div-close'] = array(
            '#type' => 'markup',
            '#markup' => '</div><div class="clear"></div></div></div>',
            '_builtin' => true,
        );
        $form['submit-close'] = wpcf_admin_metabox_end(true, '_builtin');
    }
    return $form;
}

function wpcf_admin_common_only_show($form)
{
    foreach( $form as $key => $data ) {
        if ( !isset($data['#type'] ) ) {
            continue;
        }
        /**
         * remove draggable elements
         */
        if ( preg_match( '/^draggable/', $key ) ) {
            unset($form[$key]);
            continue;
        }

        switch( $data['#type'] ) {

        case 'select':
            $form[$key]['#markup'] = $form[$key]['#default_value'];
            break;

        case 'radios':
            $form[$key]['#markup'] = '';
            foreach ( $data['#options'] as $radio_key => $radio_value ) {
                if ( $data['#default_value'] == $radio_value ) {
                    $form[$key]['#markup'] = '<span class="dashicons-before dashicons-yes"></span>'.$radio_key;
                }
            }
            break;

        case 'checkbox':
        case 'radio':
            $form[$key]['#markup'] = wpcf_admin_common_only_show_checkbox_helper($data);
            break;

        case 'checkboxes':
            $markup = '';
            if ( isset($data['#options']) && is_array($data['#options']) ) {
                foreach( $data['#options'] as $option_key => $option_value ) {
                    $markup .= wpcf_admin_common_only_show_checkbox_helper($option_value);
                }
            }
            $form[$key]['#markup'] = $markup;
            break;

        case 'textarea':
        case 'textfield':
            $form[$key]['#markup'] = wpautop(empty($form[$key]['#value'])? __('[empty]', 'wpcf'):stripcslashes($form[$key]['#value']));
            break;

            /**
             * do nothing
             */
        case 'markup':
        case 'button':
            break;

        case 'fieldset':
            $fieldset_form = array(
                'type' => array(
                    'value' => $data['type']['#value'],
                    'label' => __('Type', 'wpcf'),
                ),
                'name' => array(
                    'value' => $data['name']['#value'],
                    'label' => __('Name', 'wpcf'),
                ),
                'slug' => array(
                    'value' => $data['slug']['#value'],
                    'label' => __('Slug', 'wpcf'),
                ),
                'description' => array(
                    'value' => $data['description']['#value']? $data['description']['#value']:__('[empty]', 'wpcf'),
                    'label' => __('Description', 'wpcf'),
                ),
                'repetitive' => array(
                    'value' => isset($data['repetitive']) && $data['repetitive']['#default_value']? __('Allow multiple-instances of this field', 'wpcf'):__('This field can have only one value', 'wpcf'),
                    'label' => __('Repetitive', 'wpcf'),
                ),
            );
            foreach ( array_keys($data) as $data_key ) {
                if ( preg_match('/^#/', $data_key ) ) {
                    continue;
                }
                unset($form[$key][$data_key]);
            }
            $form[$key]['#markup'] = '<dl>';
            foreach( $fieldset_form as $fieldset_key => $fieldset_data ) {
                $form[$key]['#markup'] .= sprintf(
                    '<dt>%s</dt><dd>%s</dd>',
                    $fieldset_data['label'],
                    $fieldset_data['value']
                );
            }
            $form[$key]['#markup'] .= '</dl>';
            break;

            /**
             * remove unnesseasry elements
             */
        case 'submit':
        case 'hidden':
            unset($form[$key]);
            break;
        default:
            d($data, $data['#type']);
        }
        $form[$key]['#type'] = 'markup';
    }
    return $form;
}

function wpcf_admin_common_only_show_checkbox_helper($data)
{
    return sprintf(
        '<p><span class="dashicons-before dashicons-%s"></span>%s%s</p>',
        empty($data['#default_value'])? 'no':'yes',
        $data['#title'],
        isset($data['#description']) && !empty($data['#description'])?  sprintf('<br /><span class="description">%s</span>', $data['#description']):''
    );
}
