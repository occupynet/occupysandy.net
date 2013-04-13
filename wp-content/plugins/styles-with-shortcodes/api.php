<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/
ob_start();
if(!defined('SWS_BROWSER_CACHE')){
	Header('Cache-Control: no-cache');
	Header('Pragma: no-cache');
}
require_once('../../../../wp-load.php');
$content = ob_get_contents();
ob_end_clean();
?>