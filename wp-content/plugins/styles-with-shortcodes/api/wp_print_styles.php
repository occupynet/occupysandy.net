<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/
require_once("../api.php");

$style = addslashes(stripslashes(trim(isset($_REQUEST['style'])?$_REQUEST['style']:'')));
if(''!=$style){
	wp_print_styles($style);
}
?>