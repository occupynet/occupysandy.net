<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/
require_once("../api.php");

function send_error_die($msg){
	die(json_encode(array('R'=>'ERR','MSG'=>$msg)));
}
//-----------
if(!current_user_can('manage_options')&&!current_user_can('manage_sws')){
	send_error_die('No access');
}
//-------------

$bundle_name = $_REQUEST['bundle'];
if(trim($bundle_name)==''){
	send_error_die('Settings error, missing parameter.');
}

$import_export = new ImportExport();
$res = $import_export->restore_bundle_from_name($bundle_name,$error);
if(false===$res){
	$error = trim($error)==''?'Error restoring bundle(Undefined import/export error)':$error;
	send_error_die($error);
}

$ret = array(
	'R'=>'OK',
	'MSG'=>''
);
die(json_encode($ret));
?>