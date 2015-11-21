<?php
/*
 * Plugin contextual help
 *
 *
 */

/**
 * Returns contextual help.
 *
 * @param type $page
 * @param type $contextual_help
 */
function wpcf_admin_help($page, $contextual_help)
{
    $help = '';
    switch ($page) {
        // Custom Fields (list)
        case 'custom_fields':
            $help.= ''
                .__("Types plugin organizes custom fields in groups. Once you create a group, you can add the fields to it and control to what content it belongs.", 'wpcf')
                .PHP_EOL
                .PHP_EOL
                .sprintf(
                    __('You can read more about Custom Fields in this tutorial: %s.', 'wpcf'),
                    '<a href="http://wp-types.com/user-guides/using-custom-fields/" target="_blank">http://wp-types.com/user-guides/using-custom-fields/ &raquo;</a>'
                )
                .PHP_EOL
                .PHP_EOL
                .__("On this page you can see your current custom field groups, as well as information about which post types and taxonomies they are attached to, and whether they are active or not.", 'wpcf')
                .PHP_EOL
                .PHP_EOL
                .sprintf('<h3>%s</h3>', __('You have the following options:', 'wpcf'))
                .'<dl>'
                .'<dt>'.__('Add New', 'wpcf').'</dt>'
                .'<dd>'.__('Use this to add a new custom fields group which can be attached to a post type', 'wpcf').'</dd>'
                .'<dt>'.__('Edit', 'wpcf').'</dt>'
                .'<dd>'.__('Click to edit the custom field group', 'wpcf').'</dd>'
                .'<dt>'.__('Activate', 'wpcf').'</dt>'
                .'<dd>'.__('Click to activate a custom field group', 'wpcf').'</dd>'
                .'<dt>'.__('Deactivate', 'wpcf').'</dt>'
                .'<dd>'.__('Click to deactivate a custom field group (this can be re-activated at a later date)', 'wpcf').'</dd>'
                .'<dt>'.__('Delete', 'wpcf').'</dt>'
                .'<dd>'.__('Click to delete a custom field group.', 'wpcf')
                .' '
                .sprintf('<strong>%s</strong>', __('Warning: This cannot be undone.', 'wpcf'))
                .'</dd>'
                .'</dl>'
                ;
            break;

        case 'need-more-help':
            $help .= sprintf('<h4>%s</h4>', __('Custom fields', 'wpcf'));
            $help .= '<ul>';
            $help .= sprintf(
                '<li><a target="_blank" href="http://wp-types.com/documentation/user-guides/using-custom-fields/#1?utm_source=typesplugin&utm_medium=help&utm_term=adding-fields&utm_campaign=types">%s &raquo;</a></li>',
                __('Adding custom fields to content', 'wpcf')
            );
            $help .= sprintf(
                '<li><a target="_blank" href="http://wp-types.com/documentation/user-guides/displaying-wordpress-custom-fields/?utm_source=typesplugin&utm_medium=help&utm_term=displaying-fields&utm_campaign=types">%s &raquo;</a></li>',
                __('Displaying custom fields on front-end', 'wpcf')
            );
            $help .= '</ul>';

            $help .= sprintf('<h4>%s</h4>', __('User fields', 'wpcf'));
            $help .= '<ul>';
            $help .= sprintf(
                '<li><a target="_blank" href="http://wp-types.com/documentation/user-guides/user-fields/?utm_source=typesplugin&utm_medium=help&utm_term=adding-user-fields&utm_campaign=types">%s &raquo;</a></li>',
                __('Adding user fields to user profiles', 'wpcf')
            );
            $help .= sprintf(
                '<li><a target="_blank" href="http://wp-types.com/documentation/user-guides/displaying-wordpress-user-fields/?utm_source=typesplugin&utm_medium=help&utm_term=displaying-user-fields&utm_campaign=types">%s &raquo;</a></li>',
                __('Displaying user fields on front-end', 'wpcf')
            );
            $help .= '</ul>';

            $help .= sprintf('<h4>%s</h4>', __('Custom post types and taxonomy', 'wpcf'));
            $help .= '<ul>';
            $help .= sprintf(
                '<li><a target="_blank" href="http://wp-types.com/documentation/user-guides/create-a-custom-post-type/?utm_source=typesplugin&utm_medium=help&utm_term=custom-post-types&utm_campaign=types">%s &raquo;</a></li>',
                __('Creating and using custom post types', 'wpcf')
            );
            $help .= sprintf(
                '<li><a target="_blank" href="http://wp-types.com/documentation/user-guides/create-custom-taxonomies/?utm_source=typesplugin&utm_medium=help&utm_term=custom-taxonomy&utm_campaign=types">%s &raquo;</a></li>',
                __('Arranging content with taxonomy', 'wpcf')
            );
            $help .= sprintf(
                '<li><a target="_blank" href="http://wp-types.com/documentation/user-guides/creating-post-type-relationships/?utm_source=typesplugin&utm_medium=help&utm_term=post-relationship&utm_campaign=types">%s &raquo;</a></li>',
                __('Creating parent / child relationships', 'wpcf')
            );
            $help .= '</ul>';

            $help .= sprintf('<h4>%s</h4>', __('Access control', 'wpcf'));
            $help .= '<ul>';
            $help .= sprintf(
                '<li><a target="_blank" href="http://wp-types.com/documentation/user-guides/access-control-for-user-fields/?utm_source=typesplugin&utm_medium=help&utm_term=access-fields&utm_campaign=types">%s &raquo;</a></li>',
                __('Controlling which users can view and edit different fields', 'wpcf')
            );
            $help .= sprintf(
                '<li><a target="_blank" href="http://wp-types.com/documentation/user-guides/setting-access-control/?utm_source=typesplugin&utm_medium=help&utm_term=access-post-types&utm_campaign=types">%s &raquo;</a></li>',
                __('Controlling which users can access different post types', 'wpcf')
            );
            $help .= '</ul>';
            break;

        case 'custom_taxonomies_list':
            $help .= ''
                . __('This is the your Custom Taxonomies list. It provides you with an overview of your data.', 'wpcf')
                .PHP_EOL
                .PHP_EOL
                .sprintf(
                    __('You can read more about Custom Post Types and Taxonomies in this tutorial. %s', 'wpcf'),
                    '<a href="https://wp-types.com/user-guides/create-a-custom-post-type/" target="_blank">https://wp-types.com/user-guides/create-a-custom-post-type/ &raquo;</a>'
                )
                .PHP_EOL
                .PHP_EOL
                .sprintf('<h3>%s</h3>', __('On this page you have the following options:', 'wpcf'))
                .'<dl>'
                .'<dt>'.__('Add New', 'wpcf')
                .'<dd>'.__('Use to create a new Custom Taxonomy', 'wpcf')
                .'<dt>'.__('Edit', 'wpcf')
                .'<dd>'.__('Click to edit the settings of a Custom Taxonomy', 'wpcf').'</dd>'
                .'<dt>'.__('Deactivate', 'wpcf')
                .'<dd>'.__('Click to deactivate a Custom Taxonomy (this can be reactivated at a later date)', 'wpcf').'</dd>'
                .'<dt>'.__('Duplicate', 'wpcf')
                .'<dd>'.__('Click to duplicate a Custom Taxonomy', 'wpcf').'</dd>'
                .'<dt>'.__('Delete', 'wpcf')
                .'<dd>'.__('Click to delete a Custom Taxonomy.', 'wpcf')
                .' '
                .sprintf('<strong>%s</strong>', __('Warning: This cannot be undone.', 'wpcf'))
                .'</dd>'
                .'</dl>';
            break;

        case 'post_types_list':
            $help .= ''
                . __('This is the main admin page for built-in Post Types and your Custom Post Types. It provides you with an overview of your data.', 'wpcf')
               .PHP_EOL
               .PHP_EOL
               .__('Post Types are built-in and user-defined content types.', 'wpcf')
               .PHP_EOL
               .PHP_EOL
               .sprintf(
                    __('You can read more about Custom Post Types and Taxonomies in this tutorial. %s', 'wpcf'),
                    '<a href="https://wp-types.com/user-guides/create-a-custom-post-type/" target="_blank">https://wp-types.com/user-guides/create-a-custom-post-type/ &raquo;</a>'
                )
                .PHP_EOL
                .PHP_EOL
                .sprintf('<h3>%s</h3>', __('On this page you have the following options:', 'wpcf'))
                .'<dl>'
                .'<dt>'.__('Add New', 'wpcf').'</dt>'
                .'<dd>'.__('Use to create a new Custom Post Type', 'wpcf').'</dd>'
                .'<dt>'.__('Edit', 'wpcf').'</dt>'
                .'<dd>'.__('Click to edit the settings of a Post Type', 'wpcf').'</dd>'
                .'<dt>'.__('Deactivate', 'wpcf').'</dt>'
                .'<dd>'.__('Click to deactivate a Custom Post Type (this can be reactivated at a later date)', 'wpcf').'</dd>'
                .'<dt>'.__('Duplicate', 'wpcf')
                .'<dd>'.__('Click to duplicate a Custom Post Type', 'wpcf').'</dd>'
                .'<dt>'.__('Delete', 'wpcf').'</dt>'
                .'<dd>'.__('Click to delete a Custom Post Type.', 'wpcf')
                .' '
                .sprintf('<strong>%s</strong>', __('Warning: This cannot be undone.', 'wpcf'))
                .'</dd>'
                .'</dl>'
                ;
            break;

        // Import/Export page
        case 'import_export':
            $help .= 
                __('Use this page to import and export custom post types, taxonomies and custom fields to and from Types.', 'wpcf')
                    . PHP_EOL
                    . PHP_EOL
                    . __('On this page you have the following options:', 'wpcf')

                    . '<h3>' . __('Import Types data file', 'wpcf') . '</h3>'
                    . '<dl>'
                    .'<dt>' .__('Step 1:', 'wpcf') .'</dt>'
                    .'<dd>' .__('Choose and upload an XML file.', 'wpcf') .'</dd>'
                    .'<dt>' .__('Step 2:', 'wpcf') .'</dt>'
                    .'<dd>' . __('Select which custom post types, taxonomies and custom fields should be imported.', 'wpcf') .'</dd>'
                    .'</dl>'

                    .'<h3>' . __('Import Types data text input', 'wpcf') . '</h3>'
                    . '<dl>'
                    .'<dt>' .__('Step 1:', 'wpcf') .'</dt>'
                    .'<dd>' .__('Paste XML content directly into the text area.', 'wpcf') .'</dd>'
                    .'<dt>' .__('Step 2:', 'wpcf') .'</dt>'
                    .'<dd>' .__('Select which custom post types, taxonomies and custom fields should be imported.', 'wpcf') .'</dd>'
                    .'</dl>'

                    .'<h3>' . __('Export', 'wpcf') . '</h3>'
                    .__('Click Export to export data from Types as an XML file.', 'wpcf')
            ;
            break;

        // Add/Edit group form page
        case 'edit_group':
            $help .= ''
                .__('This is the edit page for your Custom Fields Groups.', 'wpcf')
                . PHP_EOL
                . PHP_EOL
                .sprintf(
                    __('You can read more about creating a Custom Fields Group here: %s', 'wpcf'),
                    '<a href="http://wp-types.com/user-guides/using-custom-fields/" target="_blank">http://wp-types.com/user-guides/using-custom-fields/ &raquo;</a>'
                )
                . PHP_EOL
                . PHP_EOL
                .__('On this page you can create and edit your groups. To create a group, do the following:', 'wpcf')
                .'<ol style="list-style-type:decimal;"><li style="list-style-type:decimal;">'
                .__('Add a Title', 'wpcf')
                .'</li><li style="list-style-type:decimal;">'
                .__('Choose where to display your group. You can attach this to both default WordPress post types and Custom Post Types. (nb: you can also associate taxonomy terms with Custom Field Groups)', 'wpcf')
                .'</li><li style="list-style-type:decimal;">'
                .__('To add a field click on the field you desire under “Available Fields” on the right hand side of your screen.This will be added to your Custom Field Group', 'wpcf')
                .'</li><li style="list-style-type:decimal;">'
                .__('Add information about your Custom Field', 'wpcf')
                .'</li></ol>'
                .'<h3>' .__('Tips', 'wpcf') .'</h3>'
                .'<ul><li>'
                .__('To ensure a user completes a field, check the box for validation required', 'wpcf')
                .'</li><li>'
                .__('Once you have created a field it will be saved for future use under "User created fields"', 'wpcf')
                .'</li><li>'
                .__('You can drag and drop the order of your custom fields using the blue icon', 'wpcf')
                .'</li></ul>';
            break;

            // Add/Edit custom type form page
        case 'edit_type':
            $help .= ''
               .__('Use this page to create a WordPress post type. If you’d like to learn more about Custom Post Types you can read our detailed guide: <a href="https://wp-types.com/user-guides/create-a-custom-post-type/" target="_blank">https://wp-types.com/user-guides/create-a-custom-post-type/</a> or check out our tutorial on creating them with Types: <a href="http://wp-types.com/user-guides/create-a-custom-post-type/" target="_blank">http://wp-types.com/user-guides/create-a-custom-post-type/ &raquo;</a>', 'wpcf')
               .PHP_EOL
               .PHP_EOL
               .'<dt>'.__('Name and Description', 'wpcf').'</dt>'
               .'<dd>'.__('Add a singular and plural name for your post type. You should also add a slug. This will be created from the post type name if none is added.', 'wpcf').'</dd>'
               .'<dt>'.__('Visibility', 'wpcf').'</dt>'
               .'<dd>'.__('Determine whether your post type will be visible on the admin menu to your users.', 'wpcf').'</dd>'
               .'<dd>'.__('You can also adjust the menu position. The default position is 20, which means your post type will appear under “Pages”. You can find more information about menu positioning in the WordPress Codex. <a href="http://codex.wordpress.org/Function_Reference/register_post_type#Parameters" target="_blank">http://codex.wordpress.org/Function_Reference/register_post_type#Parameters</a>', 'wpcf').'</dd>'
               .'<dd>'.__('The default post type icon is the pushpin icon that appears beside WordPress posts. You can change this by adding your own icon of 16px x 16px.', 'wpcf').'</dd>'
               .'<dt>'.__('Select Taxonomies', 'wpcf').'</dt>'
               .'<dd>'.__('Choose which taxonomies are to be associated with this post type.', 'wpcf').'</dd>'
               .'<dt>'.__('Labels', 'wpcf').'</dt>'
               .'<dd>'.__('Labels are the text that is attached to your custom post type name. Examples of them in use are “Add New Post” (where “Add New” is the label”) and “Edit Post” (where “Edit” is the label). In normal circumstances the defaults will suffice.', 'wpcf').'</dd>'
               .'<dt>'.__('Custom Post Properites', 'wpcf').'</dt>'
               .'<dd>'.__('Choose which sections to display on your “Add New” page.', 'wpcf').'</dd>'
               .'<dt>'.__('Advanced Settings', 'wpcf').'</dt>'
               .'<dd>'.__('Advanced settings give you even more control over your custom post type. You can read in detail what all of these settings do on our tutorial.', 'wpcf').'</dd>'
                .'</dl>'
                ;
            break;

        // Add/Edit custom taxonomy form page
        case 'edit_tax':
            $help .= ''
                .__('You can use Custom Taxonomies to categorize your content. Read more about what they are on our website: <a href="https://wp-types.com/user-guides/create-a-custom-post-type/" target="_blank">https://wp-types.com/user-guides/create-a-custom-post-type/ &raquo;</a> or you can read our guide about how to set them up: <a href="http://wp-types.com/user-guides/create-custom-taxonomies/" target="_blank">http://wp-types.com/user-guides/create-custom-taxonomies/</a>', 'wpcf')
                .'<dl>'
                .'<dt>'.__('Name and Description', 'wpcf') .'</dt>'
                .'<dd>'.__('Add a singular and plural name for your taxonomy. You should also add a slug. This will be created from the taxonomy name if none is added.', 'wpcf').'</dd>'
                .'<dt>'.__('Visibility', 'wpcf') .'</dt>'
                .'<dd>'.__('Determine whether your taxonomy will be visible on the admin menu to your users.', 'wpcf').'</dd>'
                .'<dt>'.__('Select Post Types', 'wpcf') .'</dt>'
                .'<dd>'.__('Choose which post types this taxonomy should be associated with.', 'wpcf').'</dd>'
                .'<dt>'.__('Labels', 'wpcf') .'</dt>'
                .'<dd>'.__('Labels are the text that is attached to your custom taxonomy name. Examples of them in use are “Add New Taxonomy” (where “Add New” is the label”) and “Edit Taxonomy” (where “Edit” is the label). In normal circumstances the defaults will suffice.', 'wpcf').'</dd>'
                .'<dt>'.__('Options', 'wpcf') .'</dt>'
                .'<dd>'.__('Advanced settings give you even more control over your custom taxonomy. You can read in detail what all of these settings do on our tutorial.', 'wpcf').'</dd>'
                .'</dl>'
                ;
            break;

        case 'user_fields_list':
            $help .= ''
                .__("Types plugin organizes User Fields in groups. Once you create a group, you can add the fields to it and control to what content it belongs.", 'wpcf')
                .PHP_EOL
                .PHP_EOL
                .__("On this page you can see your current User Fields groups, as well as information about which user role they are attached to, and whether they are active or not.", 'wpcf')
                . sprintf('<h3>%s</h3>', __('You have the following options:', 'wpcf'))
                .'<dl>'
                .'<dt>'.__('Add New', 'wpcf').'</dt>'
                .'<dd>'.__('Use this to add a new User Fields Group', 'wpcf').'</dd>'
                .'<dt>'.__('Edit', 'wpcf').'</dt>'
                .'<dd>'.__('Click to edit the User Fields Group', 'wpcf').'</dd>'
                .'<dt>'.__('Activate', 'wpcf').'</dt>'
                .'<dd>'.__('Click to activate a User Fields Group', 'wpcf').'</dd>'
                .'<dt>'.__('Deactivate', 'wpcf').'</dt>'
                .'<dd>'.__('Click to deactivate a User Fields Group (this can be re-activated at a later date)', 'wpcf').'</dd>'
                .'<dt>'.__('Delete', 'wpcf').'</dt>'
                .'<dd>'.__('Click to delete a User Fields Group.', 'wpcf')
                .' '
                .sprintf('<strong>%s</strong>', __('Warning: This cannot be undone.', 'wpcf'))
                .'</dd>'
                .'</dl>'
                ;
            break;

        case 'user_fields_edit':
            $help .= ''
                .__('This is the edit page for your User Fields Group.', 'wpcf')
                .PHP_EOL
                .PHP_EOL
                . __('On this page you can create and edit your group. To create a group, do the following:', 'wpcf')
                .'<ol><li>'
                . __('Add a Title', 'wpcf')
                .'</li><li>'
                . __('Choose where to display your group. You can attach this to both default WordPress post types and User Post Types. (nb: you can also associate taxonomy terms with User Field Groups)', 'wpcf')
                .'</li><li>'
                . __('To add a field click on the field you desire under “Available Fields” on the right hand side of your screen. This will be added to your User Field Group', 'wpcf')
                .'</li><li>'
                . __('Add information about your User Field', 'wpcf')
                .'</li></ol>'
                .'<h3>' . __('Tips', 'wpcf') .'</h3>'
                .'<ul><li>'
                . __('To ensure a user completes a field, check the box for validation required', 'wpcf')
                .'</li><li>'
                . __('Once you have created a field it will be saved for future use under "User created fields"', 'wpcf')
                .'</li><li>'
                . __('You can drag and drop the order of your custom fields using the blue icon', 'wpcf')
                .'</li></ul>';
            break;

    }

    return wpautop($help);
}

function wpcf_admin_help_add_tabs($call, $hook, $contextual_help = '')
{

    set_current_screen( $hook );
    $screen = get_current_screen();
    if ( is_null( $screen ) ) {
        return;
    }

    $title =  __( 'Types', 'wpcf' );

    switch($call) {

    case 'edit_type':
        $title =  __( 'Post Type', 'wpcf' );
        break;

    case 'post_types_list':
            $title =  __( 'Post Types', 'wpcf' );
            break;

    case 'custom_taxonomies_list':
        $title =  __( 'Custom Taxonomies', 'wpcf' );
        break;

    case 'edit_tax':
        $title =  __( 'Taxonomy', 'wpcf' );
        break;

    case 'custom_fields':
        $title = __('Custom Fields', 'wpcf');
        break;

    case 'edit_group':
        $title = __('Custom Fields Group', 'wpcf');
        break;

    case 'user_fields_list':
        $title = __('User Fields Groups', 'wpcf');
        break;

    case 'user_fields_edit':
        $title = __('User Fields Group', 'wpcf');
        break;

    case 'import_export':
        $title = __('Import/Export', 'wpcf');
        break;

    }

    $args = array(
        'title' => $title,
        'id' => 'wpcf',
        'content' => wpcf_admin_help( $call, $contextual_help),
        'callback' => false,
    );
    $screen->add_help_tab( $args );

    /**
     * Need Help section for a bit advertising
     */
    $args = array(
        'title' => __( 'Need More Help?', 'wpcf' ),
        'id' => 'custom_fields_group-need-help',
        'content' => wpcf_admin_help( 'need-more-help', $contextual_help ),
        'callback' => false,
    );
    $screen->add_help_tab( $args );

}
