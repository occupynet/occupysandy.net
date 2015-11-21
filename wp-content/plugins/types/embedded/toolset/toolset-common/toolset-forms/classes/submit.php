<?php

@ini_set('upload_max_size', '64M');
@ini_set('post_max_size', '64M');
@ini_set('max_execution_time', '300');

function _pre($v) {
    echo "<pre>";
    print_r($v);
    echo "</pre>";
}

function get_root_path() {
    $urlp = explode('wp-content', $_SERVER['SCRIPT_FILENAME']);
    return $urlp[0];
}

function get_local($url) {
    $urlParts = parse_url($url);
    return get_root_path() . $urlParts['path'];
}

function clean($string) {
    $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
    return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
}

/**
 * Executing AJAX process.
 *
 * @since 2.1.0
 */
define('WP_USE_THEMES', false);
define('DOING_AJAX', true);
//if (!defined('WP_ADMIN')) {
//    define('WP_ADMIN', true);
//}

require_once( get_root_path() . 'wp-load.php' );
require_once( get_root_path() . 'wp-admin/includes/file.php' );
require_once ( get_root_path() . 'wp-admin/includes/media.php' );
require_once ( get_root_path() . 'wp-admin/includes/image.php' );

/** Allow for cross-domain requests (from the frontend). */
send_origin_headers();

$data = array();

if (isset($_REQUEST['nonce']) && check_ajax_referer('ajax_nonce', 'nonce', false)) {

    if (isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['file'])) {
        $file = $_POST['file'];

        $data = array('result' => true);

        $local_file = get_local($file);

//get all image attachments
        $attachments = get_children(
                array(
                    'post_parent' => $post->ID,
                    //'post_mime_type' => 'image',
                    'post_type' => 'attachment'
                )
        );

//loop through the array
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                $attach_file = strtolower(basename($attachment->guid));
                $my_local_file = strtolower(basename($local_file));
                if ($attach_file == $my_local_file)
                    wp_delete_attachment($attachment->ID);

                // Update the post into the database
//          wp_update_post( array(
//                    'ID' => $attachment->ID,
//                    'post_parent' => 0
//                )
//            );
            }
        }


//        if (file_exists($local_file)) {
//            $res = unlink($local_file);
//        }
        //$data = ($res) ? array('result' => $res) : array('result' => $res, 'error' => 'Error Deleting ' . $file);
    } else {
        if (isset($_GET['id'])) {
            $post_id = intval($_GET['id']);
            $error = false;
            $files = array();

            $upload_overrides = array('test_form' => false);
            if (!empty($_FILES)) {
                foreach ($_FILES as $file) {
//For repetitive
                    foreach ($file as &$f) {
                        if (is_array($f)) {
                            foreach ($f as $p) {
                                $f = $p;
                                break;
                            }
                        }
                    }

                    $res = wp_handle_upload($file, $upload_overrides);

                    if (!isset($res['error'])) {

                        $attachment = array(
                            'post_mime_type' => $res['type'],
                            'post_title' => basename($res['file']),
                            'post_content' => '',
                            'post_status' => 'inherit',
                            'post_parent' => $post_id,
                            'post_type' => 'attachment',
                            'guid' => $res['url'],
                        );
                        $attach_id = wp_insert_attachment($attachment, $res['file']);
                        $attach_data = wp_generate_attachment_metadata($attach_id, $res['file']);
                        wp_update_attachment_metadata($attach_id, $attach_data);

                        $files[] = $res['url'];
                    } else {
                        $error = true;
                    }
                }
                $data = ($error) ? array('error' => 'There was an error uploading your files: ' . $res['error']) : array('files' => $files, 'delete_nonce' => time());
            } else {
                $data = array('error' => 'Error: Files is too big, Max upload size is: ' . ini_get('post_max_size'));
            }
        } else {
            $data = array('result' => false, 'error' => 'Error post id: check _cred_cred_prefix_post_id');
        }
    }
} else {
    $data = array('result' => false, 'error' => 'Upload Error: Invalid NONCE ');
}

echo json_encode($data);
?>