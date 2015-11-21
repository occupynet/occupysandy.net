<?php

/**
 * produce debug information
 *
 *
 */

include_once dirname(__FILE__) . '/functions_debug_information.php';
$debug_information = new ICL_Debug_Information();
$debug_data = $debug_information->get_debug_info();
?>
<div class="wrap">
	<h2><?php _e('Debug information', 'wpv-views');?></h2>
	<div id="poststuff">
		<div id="toolset-debug-info" class="postbox">
			<h3 class="handle"><span><?php _e( 'Debug information', 'wpv-views' ) ?></span></h3>
			<div class="inside">
				<p><?php _e( 'This information allows our support team to see the versions of WordPress, plugins and theme on your site. Provide this information if requested in our support forum. No passwords or other confidential information is included.', 'sitepress', 'wpv-views' ) ?></p><br/>
				<textarea style="font-size:10px;width:100%;height:250px;" rows="26" readonly="readonly"><?php echo esc_html( $debug_information->do_json_encode( $debug_data ) );?></textarea>
			</div>
		</div>
    </div>
</div>
