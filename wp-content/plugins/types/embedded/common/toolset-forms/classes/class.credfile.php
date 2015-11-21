<?php

/**
 *
 *
 */
require_once 'class.textfield.php';

/**
 * Description of class
 *
 * @author Francesco / Srdjan
 */
class WPToolset_Field_Credfile extends WPToolset_Field_Textfield {

    public $enable_progress_bar;
    
    public function init() {
        wp_register_script('wpt-field-credfile', WPTOOLSET_FORMS_RELPATH . '/js/credfile.js', array('wptoolset-forms'), WPTOOLSET_FORMS_VERSION, true);
        wp_enqueue_script('wpt-field-credfile');

        $this->enable_progress_bar = true;
        if ($this->enable_progress_bar) {
            //Add settings switch in order to use AJAX JQUERY UPLOAD or not
            $scriptpath = WPTOOLSET_FORMS_RELPATH . '/js/jquery_upload/';
            $stylepath = WPTOOLSET_FORMS_RELPATH . '/js/jquery_upload/';

            wp_enqueue_style('progress_bar-style', $stylepath . 'progress_bar.css');

            if (!wp_script_is('jquery')) {
                wp_enqueue_script('jquery', $scriptpath . 'jquery.min.js', array(), '', false);
            }
            wp_enqueue_script('jquery-ui-script', $scriptpath . 'jquery-ui.min.js', array('jquery'), '', true);
            wp_enqueue_script('jquery-ui-widget-script', $scriptpath . 'jquery.ui.widget.js', array('jquery'), '', true);
            //wp_enqueue_script('jtmpl-script', $scriptpath . 'tmpl.min.js', array('jquery'), '', true);
            wp_enqueue_script('load-image-all-script', $scriptpath . 'load-image.all.min.js', array('jquery'), '', true);
            //wp_enqueue_script('canvas-to-blob-script', $scriptpath . 'canvas-to-blob.min.js', array('jquery'), '', true);
            //wp_enqueue_script('jquery-blueimp-gallery-script', $scriptpath . 'jquery.blueimp-gallery.min.js', array('jquery'), '', true);
            wp_enqueue_script('jquery-iframe-transport-script', $scriptpath . 'jquery.iframe-transport.js', array('jquery'), '', true);
            wp_enqueue_script('jquery-fileupload-script', $scriptpath . 'jquery.fileupload.js', array('jquery'), '', true);
            wp_enqueue_script('jquery-fileupload-process-script', $scriptpath . 'jquery.fileupload-process.js', array('jquery'), '', true);
            wp_enqueue_script('jquery-fileupload-image-script', $scriptpath . 'jquery.fileupload-image.js', array('jquery'), '', true);
            wp_enqueue_script('jquery-fileupload-audio-script', $scriptpath . 'jquery.fileupload-audio.js', array('jquery'), '', true);
            wp_enqueue_script('jquery-fileupload-video-script', $scriptpath . 'jquery.fileupload-video.js', array('jquery'), '', true);
            wp_enqueue_script('jquery-fileupload-validate-script', $scriptpath . 'jquery.fileupload-validate.js', array('jquery'), '', true);
            wp_enqueue_script('jquery-fileupload-ui-script', $scriptpath . 'jquery.fileupload-ui.js', array('jquery'), '', true);
            wp_enqueue_script('jquery-fileupload-jquery-ui-script', $scriptpath . 'jquery.fileupload-jquery-ui.js', array('jquery'), '', true);
            wp_enqueue_script('my_ajax_file_uploader', $scriptpath . 'file_upload.js', array('jquery'));

            //wp_localize_script('my_ajax_file_uploader_thing', 'settings', array('ajaxurl' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('uploader_nonce')));
            wp_localize_script('my_ajax_file_uploader', 'settings', array('ajaxurl' => plugins_url("submit.php", __FILE__), 'nonce' => wp_create_nonce('ajax_nonce')));
        }
    }

    public static function registerScripts() {
        
    }

    public static function registerStyles() {
        
    }

    public function enqueueScripts() {
        
    }

    public function enqueueStyles() {
        
    }

    public function metaform() {
        $value = $this->getValue();
        $name = $this->getName();
        if (isset($this->_data['title'])) {
            $title = $this->_data['title'];
        } else {
            $title = $name;
        }

        $id = str_replace(array("[", "]"), "", $name);
        $delete_input_showhide = '';
        $button_extra_classnames = '';

        $has_image = false;
        $is_empty = false;

        if (empty($value)) {
            $value = ''; // NOTE we need to set it to an empty string because sometimes it is NULL on repeating fields
            $is_empty = true;
            $delete_input_showhide = ' style="display:none"';
        }

        if ($name == '_featured_image') {
            $title = __('Featured Image', 'wpv-views');
            if (!$is_empty) {
                if (preg_match('/src="([\w\d\:\/\._-]*)"/', $value, $_v)) {
                    $value = $_v[1];
                }
            }
        }

        if (!$is_empty) {
            $pathinfo = pathinfo($value);
            // TODO we should check against the allowed mime types, not file extensions
            if (isset($pathinfo['extension']) && in_array(strtolower($pathinfo['extension']), array('png', 'gif', 'jpg', 'jpeg', 'bmp', 'tif'))) {
                $has_image = true;
            }
        }

        if (array_key_exists('use_bootstrap', $this->_data) && $this->_data['use_bootstrap']) {
            $button_extra_classnames = ' btn btn-default btn-sm';
        }

        $preview_file = ''; //WPTOOLSET_FORMS_RELPATH . '/images/icon-attachment32.png';
        $attr_hidden = array(
            'id' => $id . "_hidden",
            'class' => 'js-wpv-credfile-hidden',
            'data-wpt-type' => 'file'
        );
        $attr_file = array(
            'id' => $id . "_file",
            'class' => 'js-wpt-credfile-upload-file wpt-credfile-upload-file',
            'alt' => $value,
        );

        if (!$is_empty) {
            $preview_file = $value;
            // Set attributes
            $attr_file['disabled'] = 'disabled';
            $attr_file['style'] = 'display:none';
        } else {
            $attr_hidden['disabled'] = 'disabled';
        }

        $form = array();

        $form[] = array(
            '#type' => 'markup',
            '#markup' => '<input type="button" style="display:none" data-action="undo" class="js-wpt-credfile-undo wpt-credfile-undo' . $button_extra_classnames . '" value="' . esc_attr(__('Restore original', 'wpv-views')) . '" />',
        );
        $form[] = array(
            '#type' => 'markup',
            '#markup' => '<input type="button"' . $delete_input_showhide . ' data-action="delete" class="js-wpt-credfile-delete wpt-credfile-delete' . $button_extra_classnames . '" value="' . esc_attr(__('Clear', 'wpv-views')) . '" />',
        );
        $form[] = array(
            '#type' => 'hidden',
            '#name' => $name,
            '#value' => $value,
            '#attributes' => $attr_hidden,
        );
        $form[] = array(
            '#type' => 'file',
            '#name' => $name,
            '#value' => $value,
            '#title' => $title,
            '#before' => '',
            '#after' => '',
            '#attributes' => $attr_file,
            '#validate' => $this->getValidationData(),
            '#repetitive' => $this->isRepetitive(),
        );
        
        if ($this->enable_progress_bar) {
            //Progress Bar
            $form[] = array(
                '#type' => 'markup',
                '#markup' => '<div id="progress_' . $id . '" class="meter" style="display:none;"><span class = "progress-bar" style="width:0;"></span></div>',
            );
        }
        
        if ($has_image) {
            $form[] = array(
                '#type' => 'markup',
                '#markup' => '<span class="js-wpt-credfile-preview  wpt-credfile-preview"><img id="' . $id . '_image" src="' . $preview_file . '" title="' . $preview_file . '" alt="' . $preview_file . '" class="js-wpt-credfile-preview-item wpt-credfile-preview-item" /></span>',
            );
        } else {
            //if ( !$is_empty )
            $form[] = array(
                '#type' => 'markup',
                '#markup' => '<span class="js-wpt-credfile-preview wpt-credfile-preview">' . $preview_file . '</span>',
            );
        }
        return $form;
    }

}
