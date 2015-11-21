<?php
require_once dirname(__FILE__).'/class.field_factory.php';

/**
 * Entry Field.
 *
 * Class for entry type object.
 *
 * @since x.x.x
 *
 */
class WPToolset_Field_Entry extends FieldFactory
{
    /**
     * Setup metaform.
     *
     * Setup metaform for field type "entry".
     *
     * @since x.x.x
     *
     * @return array metaform configuration for field "entry".
     */

    public function metaform()
    {
        /**
         * add special class to hanfle select2
         */
        $attributes = $this->getAttr();
        if ( isset($attributes['class']) ) {
            $attributes['class'] .= ' ';
        } else {
            $attributes['class'] = '';
        }
        $attributes['class'] .= 'js-wpcf-entry-select2';
        /**
         * setup post type
         */
        $attributes['data-post-type'] = 'post';
        $data = $this->getData();
        if ( isset( $data['post_type'])) {
            $attributes['data-post-type'] =  $data['post_type'];
        }
        $attributes['data-nonce'] = wp_create_nonce('wpcf_entry_search');

        $metaform = array();
        $metaform[] = array(
            '#type' => 'textfield',
            '#title' => $this->getTitle(),
            '#description' => $this->getDescription(),
            '#name' => $this->getName(),
            '#value' => $this->getValue(),
            '#validate' => $this->getValidationData(),
            '#repetitive' => $this->isRepetitive(),
            '#attributes' => $attributes,
            'wpml_action' => $this->getWPMLAction(),
        );
        return $metaform;
    }

}
