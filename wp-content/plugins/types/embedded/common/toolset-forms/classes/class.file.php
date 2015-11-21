<?php
/**
 *
 *
 */
require_once 'class.textfield.php';

/**
 * Description of class
 *
 * @author Srdjan
 */
class WPToolset_Field_File extends WPToolset_Field_Textfield
{

    protected $_validation = array('required');
    //protected $_defaults = array('filename' => '', 'button_style' => 'btn2');

    public function init()
    {
        WPToolset_Field_File::file_enqueue_scripts();
        $this->set_placeholder_as_attribute();
    }

    public static function file_enqueue_scripts()
    {
        wp_register_script(
            'wptoolset-field-file',
            WPTOOLSET_FORMS_RELPATH . '/js/file-wp35.js',
            array('jquery', 'jquery-masonry'),
            WPTOOLSET_FORMS_VERSION,
            true
        );

        if ( !wp_script_is( 'wptoolset-field-file', 'enqueued' ) ) {
            wp_enqueue_script( 'wptoolset-field-file' );
        }

        if ( is_admin() ) {
            $screen = get_current_screen();
            if (isset($screen->parent_base) && 'users' == $screen->parent_base) {
                wp_enqueue_media();
            }

            if (isset($screen->post_type) && isset($screen->base) && 'post' == $screen->base) {
                global $post;
                if ( is_object($post) ) {
                    wp_enqueue_media(array('post' => $post->ID));
                }
            }
        }

    }

    public function enqueueStyles()
    {
    }

    /**
     *
     * @global object $wpdb
     *
     */
    public function metaform()
    {
        $value = $this->getValue();
        $type = $this->getType();
        $translated_type = '';
        $form = array();
        $preview = '';

        // Get attachment by guid
        if ( !empty( $value ) ) {
            global $wpdb;
            $attachment_id = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment' AND guid=%s",
                    $value
                )
            );
        }

        // Set preview
        if ( !empty( $attachment_id ) ) {
            $attributes = array();
            $full = wp_get_attachment_image_src($attachment_id, 'full');
            if ( !empty($full) ) {
                  $attributes['data-full-src'] = esc_attr($full[0]);
            }
            $preview = wp_get_attachment_image( $attachment_id, 'thumbnail', false, $attributes);
        } else {
            // If external image set preview
            $file_path = parse_url( $value );
            if ( $file_path && isset( $file_path['path'] ) ) {
                $file = pathinfo( $file_path['path'] );
            }
            else {
                $file = pathinfo( $value );
            }
            if (
                isset( $file['extension'] )
                && in_array( strtolower( $file['extension'] ), array('jpg', 'jpeg', 'gif', 'png') )
            ) {
                $preview = '<img alt="" src="' . $value . '" />';
            }
        }

        // Set button
        switch( $type ) {
            case 'audio':
                $translated_type = __( 'audio', 'wpv-views' );
                break;
            case 'image':
                $translated_type = __( 'image', 'wpv-views' );
                break;
            case 'video':
                $translated_type = __( 'video', 'wpv-views' );
                break;
            default:
                $translated_type = __( 'file', 'wpv-views' );
                break;
        }
        $button = sprintf(
            '<a href="#" class="js-wpt-file-upload button button-secondary" data-wpt-type="%s">%s</a>',
            $type,
            sprintf( __( 'Select %s', 'wpv-views' ), $translated_type )
        );

        // Set form
        $form[] = array(
            '#type' => 'textfield',
            '#name' => $this->getName(),
            '#title' => $this->getTitle(),
            '#description' => $this->getDescription(),
            '#value' => $value,
            '#suffix' => '&nbsp;' . $button,
            '#validate' => $this->getValidationData(),
            '#repetitive' => $this->isRepetitive(),
            '#attributes' => $this->getAttr(),
        );

        $form[] = array(
            '#type' => 'markup',
            '#markup' => '<div class="js-wpt-file-preview wpt-file-preview">' . $preview . '</div>',
        );

        return $form;
    }
}
