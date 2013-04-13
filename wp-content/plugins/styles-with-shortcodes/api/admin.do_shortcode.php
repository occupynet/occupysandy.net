<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/
require_once("../api.php");
//-----------
if ( !current_user_can( 'edit_post', $post_id ) ){
	die('No access');
} 
//-------------
$content = $_REQUEST['content'];
$content = do_shortcode($content);

$ret = array(
	'R'=>'OK',
	'MSG'=>'',
	'DATA'=> $content
);
die(json_encode($ret));
?>