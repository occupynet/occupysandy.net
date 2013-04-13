<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/
require_once("../api.php");

global $wpdb;

if(!current_user_can('manage_options')){
	die();
}

$sql = "SELECT P.ID, P.post_title";
$sql.= ", COALESCE((SELECT M.meta_value FROM `{$wpdb->postmeta}` M WHERE M.post_id=P.ID AND M.meta_key=\"sc_shortcode\" LIMIT 1),'') as sc_shortcode";
$sql.= ", COALESCE((SELECT M.meta_value FROM `{$wpdb->postmeta}` M WHERE M.post_id=P.ID AND M.meta_key=\"sc_shortcodes\" LIMIT 1),'') as sc_shortcodes";
$sql.= ", COALESCE((SELECT M.meta_value FROM `{$wpdb->postmeta}` M WHERE M.post_id=P.ID AND M.meta_key=\"sc_priority_shortcode\" LIMIT 1),'') as sc_priority_shortcode";
$sql.= " FROM `{$wpdb->posts}` P";
$sql.= " WHERE post_type='csshortcode' ORDER BY menu_order ASC";
if($wpdb->query($sql)&&$wpdb->num_rows>0){

}
echo intval($wpdb->num_rows);
echo $wpdb->last_error."<br />";


echo "postmeta: `{$wpdb->postmeta}`<br />";
echo "posts: `{$wpdb->posts}`<br />";

$tables = $wpdb->get_col("show tables",0);
echo "<PRE>";
print_r($tables);
echo "</PRE>";
?>