<?php
/**
 *
 *
 */
/**
 * Register data (called automatically).
 *
 * @return type
 */
function wpcf_fields_textarea()
{
    return array(
        'id' => 'wpcf-textarea',
        'title' => __('Multiple lines', 'wpcf'),
        'description' => __('Textarea', 'wpcf'),
        'validate' => array('required'),
    );
}

/**
 * Meta box form.
 *
 * @param type $field
 * @return string
 */
function wpcf_fields_textarea_meta_box_form($field)
{
    $form = array();
    $form['name'] = array(
        '#type' => 'textarea',
        '#name' => 'wpcf[' . $field['slug'] . ']',
    );
    return $form;
}

/**
 * Formats display data.
 */
function wpcf_fields_textarea_view($params)
{
    return wpautop($params['field_value']);
}
