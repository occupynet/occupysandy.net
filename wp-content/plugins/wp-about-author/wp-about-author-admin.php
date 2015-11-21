<?PHP

//=============================================
// Load admin styles
//=============================================
function add_wp_about_author_admin_styles() {
    global $pagenow;
    if ($pagenow == 'options-general.php' && isset($_GET['page']) && strstr($_GET['page'], "wp-about-author")) {
        wp_enqueue_style('dashboard');
        wp_enqueue_style('global');
        wp_enqueue_style('wp-admin');
        wp_enqueue_style('farbtastic');
        wp_enqueue_style('wp-color-picker');
    }
}

//=============================================
// Load admin scripts
//=============================================
function add_wp_about_author_admin_scripts() {
    global $pagenow;
    if ($pagenow == 'options-general.php' && isset($_GET['page']) && strstr($_GET['page'], "wp-about-author")) {
        wp_enqueue_script('postbox');
        wp_enqueue_script('dashboard');
        wp_enqueue_script('custom-background');
    }
}

//=============================================
// Display plugin Settings Link on plugins page
//=============================================
function wp_about_author_plugin_settings_link($links) {
    $url = admin_url('options-general.php?page=wp-about-author/wp-about-author-admin.php');
    $settings_link = '<a href="'.$url.'">' . __('Settings') . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}

//=============================================
// Display support info
//=============================================
function wp_about_author_show_plugin_support() {
    $content = '<p>Leave a comment on the <a target="_blank" href="http://www.jonbishop.com/downloads/wordpress-plugins/wp-about-author/#comments">WP About Author Plugin Page</a></p>
	<p style="text-align:center;">- or -</p>
	<p>Create a new topic on the <a target="_blank" href="http://wordpress.org/tags/wp-about-author">WordPress Support Forum</a></p>';
    return wp_about_author_postbox('wp-about-author-support', 'Support', $content);
}

//=============================================
// Display support info
//=============================================
function wp_about_author_show_donate() {
    $content = '<p>If you like this plugin please consider donating a few bucks to support its development. If you can\'t spare any change you can also help by giving me a good rating on WordPress.org and tweeting this plugin to your followers.
	<ul>
		<li><a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=jonbish%40gmail%2ecom&lc=US&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted">Donate With PayPal</a></li>
		<li><a target="_blank" href="http://wordpress.org/extend/plugins/wp-about-author/">Give Me A Good Rating</a></li>
		<li><a target="_blank" href="http://twitter.com/?status=WordPress Plugin: Easily Display Customizable Author Bios Below Posts http://bit.ly/a5mDhh (via @jondbishop)">Share On Twitter</a></li>
	</ul></p>';
    return wp_about_author_postbox('wp-about-author-donate', 'Donate & Share', $content);
}

//=============================================
// Display feed
//=============================================
function wp_about_author_show_blogfeed() {
    include_once(ABSPATH . WPINC . '/feed.php');
    $content = "";
    $rss = fetch_feed("http://feeds.feedburner.com/JonBishop");
    if (!is_wp_error($rss)) {
        $maxitems = $rss->get_item_quantity(5);
        $rss_items = $rss->get_items(0, $maxitems);
    }

    if ($maxitems == 0) {
        $content .= "<p>No Posts</p>";
    } else {
        $content .= "<ul>";
        foreach ($rss_items as $item) {
            $content .= "<li><a href='" . $item->get_permalink() . "' title='Posted " . $item->get_date('j F Y | g:i a') . "'>" . $item->get_title() . "</a></li>";
        }
        $content .= "</ul>";
        $content .= "<p><a href='" . $rss->get_permalink() . "'>More Posts &raquo;</a></p>";
    }
    return wp_about_author_postbox('wp-about-author-blog-rss', 'Tips and Tricks', $content);
}

//=============================================
// Contact page options
//=============================================
function wp_about_author_general_settings() {

    // Make sure we have defaults
    add_defaults_wp_about_author();

    $wp_about_author_settings = wp_about_author_process_settings();

    $wrapped_content = "";
    $general_content = "";
    $social_content = "";
    $box_content = "";
    $avatar_content = "";

    if (function_exists('wp_nonce_field')) {
        $general_content .= wp_nonce_field('wp-about-author-update-options', '_wpnonce', true, false);
    }
    $general_content .= '<p><strong>' . __("Display On Front Page") . '</strong><br /> 
				<input type="checkbox" name="wp_author_display_front" ' . checked($wp_about_author_settings['wp_author_display_front'], 'on', false) . ' />
				<small>Display author box on the front page at the top of each entry.</small></p>';
    $general_content .= '<p><strong>' . __("Display In Archives") . '</strong><br /> 
				<input type="checkbox" name="wp_author_display_archives" ' . checked($wp_about_author_settings['wp_author_display_archives'], 'on', false) . ' />
				<small>Display author box on the archive pages at the top of each entry.</small></p>';
    $general_content .= '<p><strong>' . __("Display In Search Results") . '</strong><br /> 
				<input type="checkbox" name="wp_author_display_search" ' . checked($wp_about_author_settings['wp_author_display_search'], 'on', false) . ' />
				<small>Display author box on the search page at the top of each entry.</small></p>';
    $general_content .= '<p><strong>' . __("Display On Individual Posts") . '</strong><br /> 
				<input type="checkbox" name="wp_author_display_posts" ' . checked($wp_about_author_settings['wp_author_display_posts'], 'on', false) . ' />
				<small>Display author box on individual posts at the top of the entry.</small></p>';
    $general_content .= '<p><strong>' . __("Display On Individual Pages") . '</strong><br /> 
				<input type="checkbox" name="wp_author_display_pages" ' . checked($wp_about_author_settings['wp_author_display_pages'], 'on', false) . ' />
				<small>Display author box on individual pages at the top of the entry.</small></p>';
    $general_content .= '<p><strong>' . __("Display In RSS Feeds") . '</strong><br />
				<input type="checkbox" name="wp_author_display_feed" ' . checked($wp_about_author_settings['wp_author_display_feed'], 'on', false) . ' />
				<small>Display author box in feeds at the top of each entry.</small></p>';
    $wrapped_content .= wp_about_author_postbox('wp-about-author-settings-general', 'Display Settings', $general_content);

    $avatar_content .= '<p><strong>' . __("Size") . '</strong><br /> 
                <input type="text" name="wp_author_avatar_size" value="' . $wp_about_author_settings['wp_author_avatar_size'] . '" /><br />
                <small>By default, the size of the image is 100x100.</small></p>';
    $avatar_content .= '<p><strong>' . __("Display as Circle") . '</strong><br />
                <input type="checkbox" name="wp_author_avatar_shape" ' . checked($wp_about_author_settings['wp_author_avatar_shape'], 'on', false) . ' />
                <small>Display circular images instead of square ones.</small></p>';
    $wrapped_content .= wp_about_author_postbox('wp-about-author-settings-avatar', 'Avatar Settings', $avatar_content);

    $social_content .= '<p><strong>' . __("Display Social Media Icons") . '</strong><br />
				<input type="checkbox" name="wp_author_social_images" ' . checked($wp_about_author_settings['wp_author_social_images'], 'on', false) . ' />
				<small>Display buttons instead of text links in the author box.</small></p>';
    $wrapped_content .= wp_about_author_postbox('wp-about-author-settings-general', 'Social Links Display Settings', $social_content);

    $box_content .= '<p><strong>' . __("Box Background Color") . '</strong><br /> 
				<input type="text" name="wp_author_alert_bg" id="background-color" value="' . $wp_about_author_settings['wp_author_alert_bg'] . '" /><br />
				<small>By default, the background color of the box is a yellowish tone.</small></p>';
    $box_content .= '<p><strong>' . __("Box Border") . '</strong><br /> 
                <select name="wp_author_alert_border">
                  <option value="top" ' . selected($wp_about_author_settings['wp_author_alert_border'], 'top', false) . '>Thick Top Border</option>
                  <option value="around" ' . selected($wp_about_author_settings['wp_author_alert_border'], 'around', false) . '>Thin Surrounding Border</option>
                  <option value="none" ' . selected($wp_about_author_settings['wp_author_alert_border'], 'none', false) . '>No Border</option>
                </select><br /><small>By default, a thick black line is displayed above the author bio.</small></p>';
    $wrapped_content .= wp_about_author_postbox('wp-about-author-settings-alert', 'Box Settings', $box_content);

    wp_about_author_admin_wrap('WP About Author Settings', $wrapped_content);
}
// 
function wp_about_author_get_options(){
    $wp_about_author_settings = get_option('wp_about_author_settings');
    // Make sure we have defaults
    add_defaults_wp_about_author($wp_about_author_settings);

    return wp_parse_args( $wp_about_author_settings, $fields );
}


//=============================================
// Process contact page form data
//=============================================
function wp_about_author_process_settings() {
    $fields = wp_about_author_get_options();
    if (!empty($_POST['wp_about_author_option_submitted'])) {
        $wp_about_author_settings = array();

        if (strstr($_GET['page'], "wp-about-author") && check_admin_referer('wp-about-author-update-options')) {
            $color = preg_replace('/[^0-9a-fA-F]/', '', $_POST['wp_author_alert_bg']);
            if ((strlen($color) == 6 || strlen($color) == 3) && isset($_POST['wp_author_alert_bg'])) {
                $wp_about_author_settings['wp_author_alert_bg'] = $_POST['wp_author_alert_bg'];
            }
            foreach ($fields as $field_key=>$field_value) {
                if (isset($_POST[$field_key])) {
                    $wp_about_author_settings[$field_key] = $_POST[$field_key];
                } else {
                    $wp_about_author_settings[$field_key] = "";
                }
            }
            echo "<div id=\"updatemessage\" class=\"updated fade\"><p>WP About Author settings updated.</p></div>\n";
            echo "<script type=\"text/javascript\">setTimeout(function(){jQuery('#updatemessage').hide('slow');}, 3000);</script>";
            update_option('wp_about_author_settings', $wp_about_author_settings);
        }
    }//updated

    $wp_about_author_settings = get_option('wp_about_author_settings');
    $wp_about_author_settings = wp_parse_args( $wp_about_author_settings, $fields );

    return $wp_about_author_settings;
}

//=============================================
// admin options panel
//=============================================
function add_wp_about_author_options_subpanel() {
    if (function_exists('add_options_page')) {
        add_options_page('WP About Author', 'WP About Author', 'manage_options', __FILE__, 'wp_about_author_general_settings');
    }
}

//=============================================
// Create postbox for admin
//=============================================	
function wp_about_author_postbox($id, $title, $content) {
    $postbox_wrap = "";
    $postbox_wrap .= '<div id="' . $id . '" class="postbox">';
    $postbox_wrap .= '<div class="handlediv" title="Click to toggle"><br /></div>';
    $postbox_wrap .= '<h3 class="hndle"><span>' . $title . '</span></h3>';
    $postbox_wrap .= '<div class="inside">' . $content . '</div>';
    $postbox_wrap .= '</div>';
    return $postbox_wrap;
}

//=============================================
// Admin page wrap
//=============================================	
function wp_about_author_admin_wrap($title, $content) {
    ?>
    <div class="wrap">
        <h2><?php echo $title; ?></h2>
        <form method="post" action="">
            <div id="dashboard-widgets-wrap">
                <div id="dashboard-widgets" class="metabox-holder">
                    <div id="postbox-container-1" class="postbox-container" style="width:60%;">
                        <div id="normal-sortables" class="meta-box-sortables ui-sortable">
    <?php
    echo $content;
    ?>
                            <p class="submit">
                                <input type="submit" name="wp_about_author_option_submitted" class="button-primary" value="Save Changes" />
                            </p>
                        </div>
                    </div>
                    <div id="postbox-container-2" class="postbox-container" style="width:30%;">
                        <div id="side-sortables" class="meta-box-sortables ui-sortable">
                            <?php
                            echo wp_about_author_show_donate();
                            echo wp_about_author_show_plugin_support();
                            echo wp_about_author_show_blogfeed();
                            ?>   
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <?php
}
?>