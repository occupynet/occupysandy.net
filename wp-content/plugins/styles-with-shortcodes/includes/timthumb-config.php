<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/
error_reporting(0);
//----added to support external configuration--------------------------------------------------------
define('SWS_BROWSER_CACHE',1);
require_once("../api.php");

$options = get_option('sws_options');
$options = empty($options)?array():$options;
$allowedSites = isset($options['allowed_ext_thumb_url'])?$options['allowed_ext_thumb_url']:'';
$allowedSites = str_replace("\n","",$allowedSites);
$allowedSites = explode("\r",$allowedSites);
$allowedSites = is_array($allowedSites)?$allowedSites:array();

$allowedSites = array_merge($allowedSites,array (
	'flickr.com',
	'staticflickr.com',
	'picasa.com',
	'img.youtube.com',
	'upload.wikimedia.org',
	'photobucket.com',
	'imgur.com',
	'imageshack.us',
	'tinypic.com',
	'plugins.righthere.com'
));
if(''!=trim($_SERVER['HTTP_HOST']))$allowedSites[]=$_SERVER['HTTP_HOST'];
if(''!=trim($_SERVER['SERVER_NAME']))$allowedSites[]=$_SERVER['SERVER_NAME'];
//-----------------------------------------------------------------------------------------------------

// If ALLOW_EXTERNAL is true and ALLOW_ALL_EXTERNAL_SITES is false, then external images will only be fetched from these domains and their subdomains. 
$ALLOWED_SITES = $allowedSites;
// -------------------------------------------------------------
?>