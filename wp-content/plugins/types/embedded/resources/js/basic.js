/**
 *
 * Embedded JS.
 * For now full and embedded version use this script.
 * Before moving full-version-only code - make sure it's not needed here.
 *
 *
 */

var wpcfFormGroupsSupportPostTypeState = new Array();
var wpcfFormGroupsSupportTaxState = new Array();
var wpcfFormGroupsSupportTemplatesState = new Array();

// TODO document this
var wpcfFieldsEditorCallback_redirect = null;

jQuery(document).ready(function(){

    /**
     * modal advertising
     */
    if(jQuery.isFunction(jQuery.fn.types_modal_box)) {
        jQuery('.js-types-show-modal').types_modal_box();
    }
    jQuery('.wpcf-notif-description a').on('click', function() {
        jQuery(this).attr('target', '_blank');
    });
    //user suggestion
    if(jQuery.isFunction(jQuery.suggest)) {
        jQuery('.input').suggest("admin-ajax.php?action=wpcf_types_suggest_user&tax=post_tag", {
            multiple:false,
            multipleSep: ","
        });
    }
    // Only for adding group
    jQuery('.wpcf-fields-add-ajax-link').click(function(){
        jQuery.ajax({
            url: jQuery(this).attr('href'),
            cache: false,
            beforeSend: function() {
                jQuery('#wpcf-fields-under-title').hide();
                jQuery('#wpcf-ajax-response').addClass('wpcf-ajax-loading');
            },
            success: function(data) {
                jQuery('#wpcf-ajax-response').removeClass('wpcf-ajax-loading');
                jQuery('#wpcf-fields-sortable').append(data);
                jQuery('#wpcf-fields-sortable .ui-draggable:last').find('input:first').focus().select();
                var scrollToHeight = jQuery('#wpcf-fields-sortable .ui-draggable:last').offset();
                window.scrollTo(0, scrollToHeight.top);
                /**
                 * bind logic button if it is possible
                 */
                if ('function' == typeof(wpcfConditionalLogiButtonsBindClick)) {
                    wpcfConditionalLogiButtonsBindClick();
                }
            }
        });
        return false;
    });
    /*
     * Moved to fields-form.js
     */
    //    // Sort and Drag
    //    jQuery('#wpcf-fields-sortable').sortable({
    //        revert: true,
    //        handle: 'img.wpcf-fields-form-move-field',
    //        containment: 'parent'
    //    });
    //    jQuery('.wpcf-fields-radio-sortable').sortable({
    //        revert: true,
    //        handle: 'img.wpcf-fields-form-radio-move-field',
    //        containment: 'parent'
    //    });
    //    jQuery('.wpcf-fields-checkboxes-sortable').sortable({
    //        revert: true,
    //        handle: 'img.wpcf-fields-form-checkboxes-move-field',
    //        containment: 'parent'
    //    });
    //    jQuery('.wpcf-fields-select-sortable').sortable({
    //        revert: true,
    //        handle: 'img.wpcf-fields-form-select-move-field',
    //        containment: 'parent'
    //    });

    jQuery(".wpcf-form-fieldset legend").live('click', function() {
        jQuery(this).parent().children(".collapsible").slideToggle("fast", function() {
            var toggle = '';
            if (jQuery(this).is(":visible")) {
                jQuery(this).parent().children("legend").removeClass("legend-collapsed").addClass("legend-expanded");
                toggle = 'open';
            } else {
                jQuery(this).parent().children("legend").removeClass("legend-expanded").addClass("legend-collapsed");
                toggle = 'close';
            }
            // Save collapsed state
            // Get fieldset id
            var collapsed = jQuery(this).parent().attr('id');

            // For group form save fieldset toggle per group
            if (jQuery(this).parents('form').hasClass('wpcf-fields-form')) {
                // Get group id
                var group_id = false;
                if (jQuery('input[name="group_id"]').length > 0) {
                    group_id = jQuery('input[name="group_id"]').val();
                } else {
                    group_id = -1;
                }
                jQuery.ajax({
                    url: ajaxurl,
                    cache: false,
                    type: 'get',
                    data: 'action=wpcf_ajax&wpcf_action=group_form_collapsed&id='+collapsed+'&toggle='+toggle+'&group_id='+group_id+'&_wpnonce='+wpcf_nonce_toggle_group
                });
            } else {
                jQuery.ajax({
                    url: ajaxurl,
                    cache: false,
                    type: 'get',
                    data: 'action=wpcf_ajax&wpcf_action=form_fieldset_toggle&id='+collapsed+'&toggle='+toggle+'&_wpnonce'+wpcf_nonce_toggle_fieldset
                });
            }
        });
    });
    jQuery('.wpcf-forms-set-legend').live('keyup', function(){
        var val = jQuery(this).val();
        if ( val ) {
            val = val.replace(/</, '&lt;');
            val = val.replace(/>/, '&gt;');
            val = val.replace(/'/, '&#39;');
            val = val.replace(/"/, '&quot;');
        }
        jQuery(this).parents('fieldset').find('.wpcf-legend-update').html(val);
    });
    jQuery('.wpcf-form-groups-radio-update-title-display-value').live('keyup', function(){
        jQuery('#'+jQuery(this).attr('id')+'-display-value').prev('label').html(jQuery(this).val());
    });
    jQuery('.form-error').parents('.collapsed').slideDown();
    jQuery('.wpcf-form input').live('focus', function(){
        jQuery(this).parents('.collapsed').slideDown();
    });

    // Delete AJAX added element
    jQuery('.wpcf-form-fields-delete').live('click', function(){
        if (jQuery(this).attr('href') == 'javascript:void(0);') {
            jQuery(this).parent().fadeOut(function(){
                jQuery(this).remove();
            });
        }
    });

    // Check radio and select if same values
    // Check checkbox has a value to store
    jQuery('.wpcf-fields-form').submit(function(){
        wpcfLoadingButton();
        var passed = true;
        var checkedArr = new Array();
        jQuery('.wpcf-compare-unique-value-wrapper').each(function(index){
            var childID = jQuery(this).attr('id');
            checkedArr[childID] = new Array();
            jQuery(this).find('.wpcf-compare-unique-value').each(function(index, value){
                var parentID = jQuery(this).parents('.wpcf-compare-unique-value-wrapper').first().attr('id');
                var currentValue = jQuery(this).val();
                if (currentValue != ''
                    && jQuery.inArray(currentValue, checkedArr[parentID]) > -1) {

                    passed = false;
                    jQuery('#'+parentID).children('.wpcf-form-error-unique-value').remove();
                    jQuery('#'+parentID).append('<div class="wpcf-form-error-unique-value wpcf-form-error">'+wpcfFormUniqueValuesCheckText+'</div>');
                    jQuery(this).parents('fieldset').children('.fieldset-wrapper').slideDown();
                    jQuery(this).focus();
                }

                checkedArr[parentID].push(currentValue);
            });
        });
        if (passed == false) {
            // Bind message fade out
            jQuery('.wpcf-compare-unique-value').live('keyup', function(){
                jQuery(this).parents('.wpcf-compare-unique-value-wrapper').find('.wpcf-form-error-unique-value').fadeOut(function(){
                    jQuery(this).remove();
                });
            });
            wpcfLoadingButtonStop();
            return false;
        }
        // Check field names unique
        passed = true;
        checkedArr = new Array();
        jQuery('.wpcf-forms-field-name').each(function(index){
            var currentValue = jQuery(this).val().toLowerCase();
            if (currentValue != ''
                && jQuery.inArray(currentValue, checkedArr) > -1) {
                passed = false;
                if (!jQuery(this).hasClass('wpcf-name-checked-error')) {
                    jQuery(this).before('<div class="wpcf-form-error-unique-value wpcf-form-error">'+wpcfFormUniqueNamesCheckText+'</div>').addClass('wpcf-name-checked-error');
                }
                jQuery(this).parents('fieldset').children('.fieldset-wrapper').slideDown();
                jQuery(this).focus();

            }
            checkedArr.push(currentValue);
        });
        if (passed == false) {
            // Bind message fade out
            jQuery('.wpcf-forms-field-name').live('keyup', function(){
                jQuery(this).removeClass('wpcf-name-checked-error').prev('.wpcf-form-error-unique-value').fadeOut(function(){
                    jQuery(this).remove();
                });
            });
            wpcfLoadingButtonStop();
            return false;
        }

        // Check field slugs unique
        passed = true;
        checkedArr = new Array();
        /**
         * first fill array with defined, but unused fields
         */
        jQuery('#wpcf-form-groups-user-fields .wpcf-fields-add-ajax-link:visible').each(function(){
            checkedArr.push(jQuery(this).data('slug'));
        });
        jQuery('.wpcf-forms-field-slug').each(function(index){
            var currentValue = jQuery(this).val().toLowerCase();
            if (currentValue != ''
                && jQuery.inArray(currentValue, checkedArr) > -1) {
                passed = false;
                if (!jQuery(this).hasClass('wpcf-slug-checked-error')) {
                    jQuery(this).before('<div class="wpcf-form-error-unique-value wpcf-form-error">'+wpcfFormUniqueSlugsCheckText+'</div>').addClass('wpcf-slug-checked-error');
                }
                jQuery(this).parents('fieldset').children('.fieldset-wrapper').slideDown();
                jQuery(this).focus();

            }
            checkedArr.push(currentValue);
        });

        // Conditional check
        if (wpcfConditionalFormDateCheck() == false) {
            wpcfLoadingButtonStop();
            return false;
        }

        // check to make sure checkboxes have a value to save.
        jQuery('[data-wpcf-type=checkbox],[data-wpcf-type=checkboxes]').each(function () {
            if (wpcf_checkbox_value_zero(this)) {
                passed = false;
            }
        });

        if (passed == false) {
            // Bind message fade out
            jQuery('.wpcf-forms-field-slug').live('keyup', function(){
                jQuery(this).removeClass('wpcf-slug-checked-error').prev('.wpcf-form-error-unique-value').fadeOut(function(){
                    jQuery(this).remove();
                });
            });
            wpcfLoadingButtonStop();
            return false;
        }
    });

    /*
     * Generic AJAX call (link). Parameters can be used.
     */
    jQuery('.wpcf-ajax-link').live('click', function(){
        var callback = wpcfGetParameterByName('wpcf_ajax_callback', jQuery(this).attr('href'));
        var update = wpcfGetParameterByName('wpcf_ajax_update', jQuery(this).attr('href'));
        var updateAdd = wpcfGetParameterByName('wpcf_ajax_update_add', jQuery(this).attr('href'));
        var warning = wpcfGetParameterByName('wpcf_warning', jQuery(this).attr('href'));
        var thisObject = jQuery(this);
        var thisObjectTR = jQuery(this).closest('tr');
        if (warning != false) {
            var answer = confirm(warning);
            if (answer == false) {
                return false;
            }
        }
        jQuery.ajax({
            url: jQuery(this).attr('href'),
            type: 'get',
            dataType: 'json',
            cache: false,
            beforeSend: function() {
                if (update != false) {
                    jQuery('#'+update).html('').show().addClass('wpcf-ajax-loading-small');
                }
            },
            success: function(data) {
                if (data != null) {
                    if (typeof data.output != 'undefined') {
                        if (update != false) {
                            jQuery('#'+update).removeClass('wpcf-ajax-loading-small').html(data.output);
                        }
                        if (updateAdd != false) {
                            if (data.output.length < 1) {
                                jQuery('#'+updateAdd).fadeOut();
                            }
                            jQuery('#'+updateAdd).append(data.output);
                        }
                    }
                    if (typeof data.status != 'undefined' ) {
                        if ( 'inactive' == data.status ) {
                            thisObjectTR.addClass('status-inactive');
                        } else {
                            thisObjectTR.removeClass('status-inactive');
                        }
                    }
                    if (typeof data.status_label != 'undefined' ) {
                        jQuery('td.status', thisObjectTR).html(data.status_label);
                    }
                    if (
                        typeof data.execute != 'undefined'
                        && (
                            typeof data.wpcf_nonce_ajax_callback != 'undefined'
                            && data.wpcf_nonce_ajax_callback == wpcf_nonce_ajax_callback
                        )
                    ) {
                        switch(data.execute) {
                            case 'reload':
                                location.reload();
                                break;
                            case 'append':
                                if (
                                    typeof data.append_target != 'undefined'
                                    && typeof data.append_value != 'undefined'
                                ) {
                                    jQuery(data.append_target).append(data.append_value);
                                }
                                break;
                        }
                    }
                }
                if (callback != false) {
                    eval(callback+'(data, thisObject)');
                }
            }
        });
        wpcfLoadingButtonStop();
        return false;
    });

    jQuery('.wpcf-form-groups-support-post-type').each(function(){
        if (jQuery(this).is(':checked')) {
            window.wpcfFormGroupsSupportPostTypeState.push(jQuery(this).attr('id'));
        }
    });

    jQuery('.wpcf-form-groups-support-tax').each(function(){
        if (jQuery(this).is(':checked')) {
            window.wpcfFormGroupsSupportTaxState.push(jQuery(this).attr('id'));
        }
    });

    jQuery('.wpcf-form-groups-support-templates input').each(function(){
        if (jQuery(this).is(':checked')) {
            window.wpcfFormGroupsSupportTemplatesState.push(jQuery(this).attr('id'));
        }
    });

    // Add scroll to user created fieldset if necessary
    if (jQuery('#wpcf-form-groups-user-fields').length > 0) {
        var wpcfFormGroupsUserCreatedFieldsHeight = Math.round(jQuery('#wpcf-form-groups-user-fields').height());
        var wpcfScreenHeight = Math.round(jQuery(window).height());
        var wpcfFormGroupsUserCreatedFieldsOffset = jQuery('#wpcf-form-groups-user-fields').offset();
        /**
         * use jScrollPane only when have enough space
         */
        if ( wpcfScreenHeight -wpcfFormGroupsUserCreatedFieldsOffset.top > 100 ) {
            if (wpcfFormGroupsUserCreatedFieldsHeight+wpcfFormGroupsUserCreatedFieldsOffset.top > wpcfScreenHeight) {
                var wpcfFormGroupsUserCreatedFieldsHeightResize = Math.round(wpcfScreenHeight-wpcfFormGroupsUserCreatedFieldsOffset.top-40);
                jQuery('#wpcf-form-groups-user-fields').height(wpcfFormGroupsUserCreatedFieldsHeightResize);
                jQuery('#wpcf-form-groups-user-fields .fieldset-wrapper').height(wpcfFormGroupsUserCreatedFieldsHeightResize-15);
                jQuery('#wpcf-form-groups-user-fields .fieldset-wrapper').jScrollPane();
            }
            jQuery('.wpcf-form-fields-align-right').css('position', 'fixed');
        } else {
            jQuery('#wpcf-form-groups-user-fields').closest('.wpcf-form-fields-align-right').css('position', 'absolute' );
        }
    }

    // Types form
    jQuery('input[name="ct[public]"]').change(function(){
        if (jQuery(this).val() == 'public') {
            jQuery('#wpcf-types-form-visiblity-toggle').slideDown();
        } else {
            jQuery('#wpcf-types-form-visiblity-toggle').slideUp();
        }
    });
    jQuery('input[name="ct[rewrite][custom]"]').change(function(){
        if (jQuery(this).val() == 'custom') {
            jQuery('#wpcf-types-form-rewrite-toggle').slideDown();
        } else {
            jQuery('#wpcf-types-form-rewrite-toggle').slideUp();
        }
    });
    jQuery('.wpcf-tax-form input[name="ct[rewrite][enabled]"]').change(function(){
        if (jQuery(this).is(':checked')) {
            jQuery('#wpcf-types-form-rewrite-toggle').slideDown();
        } else {
            jQuery('#wpcf-types-form-rewrite-toggle').slideUp();
        }
    });
    /**
     * meta_box_cb
     */
    jQuery('.wpcf-tax-form input[name="ct[meta_box_cb][disabled]"]').change(function(){
        if (jQuery(this).is(':checked')) {
            jQuery('#wpcf-types-form-meta_box_cb-toggle').slideUp();
        } else {
            jQuery('#wpcf-types-form-meta_box_cb-toggle').slideDown();
        }
    });
    jQuery('input[name="ct[show_in_menu]"]').change(function(){
        if (jQuery(this).is(':checked')) {
            jQuery('#wpcf-types-form-showinmenu-toggle').slideDown();
        } else {
            jQuery('#wpcf-types-form-showinmenu-toggle').slideUp();
        }
    });
    jQuery('input[name="ct[has_archive]"]').change(function(){
        if (jQuery(this).is(':checked')) {
            jQuery('#wpcf-types-form-has_archive-toggle').slideDown();
        } else {
            jQuery('#wpcf-types-form-has_archive-toggle').slideUp();
        }
    });
    jQuery('input[name="ct[query_var_enabled]"]').change(function(){
        if (jQuery(this).is(':checked')) {
            jQuery('#wpcf-types-form-queryvar-toggle').slideDown();
        } else {
            jQuery('#wpcf-types-form-queryvar-toggle').slideUp();
        }
    });

    jQuery('.wpcf-groups-form-ajax-update-custom_taxonomies-ok, .wpcf-groups-form-ajax-update-custom_post_types-ok, .wpcf-groups-form-ajax-update-templates-ok').click(function(){
        var count = 0;
        if (jQuery('.wpcf-groups-form-ajax-update-custom_taxonomies-ok').parent().find("input:checked").length > 0) {
            count += 1;
        }
        if (jQuery('.wpcf-groups-form-ajax-update-custom_post_types-ok').parent().find("input:checked").length > 0) {
            count += 1;
        }
        if (jQuery('.wpcf-groups-form-ajax-update-templates-ok').parent().find("input:checked").length > 0) {
            count += 1;
        }
        if (count > 1) {
            jQuery('#wpcf-fields-form-filters-association-form').show();
        } else {
            jQuery('#wpcf-fields-form-filters-association-form').hide();
        }
        wpcfFieldsFormFiltersSummary();
    });

    // Loading submit button
    jQuery('.wpcf-tax-form, .wpcf-types-form').submit(function(){
        wpcfLoadingButton();
    });

    /**
     * auto create slugs on all fields wich needs this:
     *
     * - custom post slug
     * - custom taxonmy slug
     * - custom field slug
     * - user meta fields
     */
    jQuery('.js-wpcf-slugize').live('blur focus click', function() {
        var slug = jQuery(this).val();
        if ( '' == slug ){
            slug = jQuery('.js-wpcf-slugize-source', jQuery(this).closest('.js-wpcf-slugize-container')).val();
        }
        if ( '' != slug ){
            val = wpcf_slugize(slug);
            jQuery(this).val(val);
        }
    });
});

/**
 * Searches for parameter inside string ('arg', 'edit.php?arg=first&arg2=sec')
 */
function wpcfGetParameterByName(name, string){
    name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
    var regexS = "[\\?&]"+name+"=([^&#]*)";
    var regex = new RegExp( regexS );
    var results = regex.exec(string);
    if (results == null) {
        return false;
    } else {
        return decodeURIComponent(results[1].replace(/\+/g, " "));
    }
}

/**
 * AJAX delete elements from group form callback.
 */
function wpcfFieldsFormDeleteElement(data, element) {
    element.parent().fadeOut(function(){
        element.parent().remove();
    });
}

/**
 * Set count for options
 */
function wpcfFieldsFormCountOptions(obj) {
    var count = wpcfGetParameterByName('count', obj.attr('href'));
    count++;
    obj.attr('href',  obj.attr('href').replace(/count=.*/, 'count='+count));
}

// Migrate checkboxes
function wpcfCbSaveEmptyMigrate(object, field_slug, total, wpnonce, action, metaType) {
    jQuery.ajax({
        url: ajaxurl+'?action=wpcf_ajax&wpcf_action=cb_save_empty_migrate&field='+field_slug+'&subaction='+action+'&total='+total+'&_wpnonce='+wpnonce+'&meta_type='+metaType,
        type: 'get',
        dataType: 'json',
        //            data: ,
        cache: false,
        beforeSend: function() {
            object.parent().parent().find('.wpcf-cb-save-empty-migrate-response').html('').show().addClass('wpcf-ajax-loading-small');
        },
        success: function(data) {
            if (data != null) {
                if (typeof data.output != 'undefined') {
                    object.parent().parent().find('.wpcf-cb-save-empty-migrate-response').removeClass('wpcf-ajax-loading-small').html(data.output);
                }
            }
        }
    });
}

function wpcfCbMigrateStep(total, offset, field_slug, wpnonce, metaType) {
    jQuery.ajax({
        url: ajaxurl+'?action=wpcf_ajax&wpcf_action=cb_save_empty_migrate&field='+field_slug+'&subaction=save&total='+total+'&offset='+offset+'&_wpnonce='+wpnonce+'&meta_type='+metaType,
        type: 'get',
        dataType: 'json',
        //            data: ,
        cache: false,
        beforeSend: function() {
        //            jQuery('#wpcf-cb-save-empty-migrate-response-'+field_slug).html(total+'/'+offset);
        },
        success: function(data) {
            if (data != null) {
                if (typeof data.output != 'undefined') {
                    jQuery('#wpcf-cb-save-empty-migrate-response-'+field_slug).html(data.output);
                }
            }
        }
    });
}

function wpcfCdCheckDateCustomized(object) {
    var show = false;
    object.parents('.fieldset-wrapper').find('.wpcf-cd-field option:selected').each(function(){
        if (jQuery(this).hasClass('wpcf-conditional-select-date')) {
            show = true;
        }
    });
    if (show) {
        object.parent().find('.wpcf-cd-notice-date').show();
    } else {
        object.parent().find('.wpcf-cd-notice-date').show();
    }
}

/**
 * Adds spinner graphics and disable button.
 */
function wpcfLoadingButton() {
    jQuery('.wpcf-disabled-on-submit').attr('disabled', 'disabled').each(function(){
        if ( 'undefined' == typeof(types_modal) ) {
            jQuery(this).after('<div id="'+jQuery(this).attr('id')+'-loading" class="wpcf-loading">&nbsp;</div>');
        }
    });
}
/**
 * Counter loading.
 */
function wpcfLoadingButtonStop() {
    jQuery('.wpcf-disabled-on-submit').removeAttr('disabled');
    jQuery('.wpcf-loading').fadeOut();
    //Fix https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/194177056/comments
    //type modal didnt disappeared
    jQuery('.types_modal_box').remove();
    jQuery('.types_block_page').remove();
}

/**
 * Editor callback func.
 */
function wpcfFieldsEditorCallback(fieldID , metaType, postID) {

    var colorboxWidth = 750 + 'px';

    if ( !( jQuery.browser.msie && parseInt(jQuery.browser.version) < 9 ) ) {
        var documentWidth = jQuery(document).width();
        if ( documentWidth < 750 ) {
            colorboxWidth = 600 + 'px';
        }
    }

    var url = ajaxurl+'?action=wpcf_ajax&wpcf_action=editor_callback&_typesnonce='+types.wpnonce+'&field_id='+fieldID+'&field_type='+metaType+'&post_id='+postID;

    // Check if shortcode passed
    if ( typeof arguments[3] === 'string' ) {
        // urlencode() PHP
        url += '&shortcode='+arguments[3];
    }

    jQuery.colorbox({
        href: url,
        iframe: true,
        inline : false,
        width: colorboxWidth,
        opacity: 0.7,
        closeButton: false
    });
}

/**
 * TODO Document this!
 * 1.1.5
 */
function wpcfFieldsEditorCallback_set_redirect(function_name, params) {
    wpcfFieldsEditorCallback_redirect = {
        'function' : function_name,
        'params' : params
    };
}
//Usermeta shortocde addon
function wpcf_showmore(show){
    if (show){
        jQuery('#specific_user_div').css('display','block');
        jQuery('#display_username_for_author').removeAttr('checked');
    }
    else{
        jQuery('#specific_user_div').css('display','none');
        jQuery('#display_username_for_suser').removeAttr('checked');
    }
}
//Usermeta shortocde addon
function hideControls(control_id1,control_id2){
    control_id1 = '#'+control_id1;
    control_id2 = '#'+control_id2;
    jQuery(control_id1).css('display','none');
    jQuery(control_id2).css('display','inline');
    jQuery(control_id2).focus();
}

/**
 * slugize
 */
function wpcf_slugize(val)
{
    /**
     * not a string or empty - thank you
     */
    if ( 'string' != typeof val || '' == val ) {
        return;
    }
    val = removeDiacritics(val.toLowerCase());
    val = val.replace(/[^a-z0-9A-Z_]+/g, '-');
    val = val.replace(/\-+/g, '-');
    val = val.replace(/^\-/g, '');
    val = val.replace(/\-$/g, '');
    return val;
};

/**
 * removeDiacritics
 */

var defaultDiacriticsRemovalMap = [
    {'base':'A', 'letters':/[\u0041\u24B6\uFF21\u00C0\u00C1\u00C2\u1EA6\u1EA4\u1EAA\u1EA8\u00C3\u0100\u0102\u1EB0\u1EAE\u1EB4\u1EB2\u0226\u01E0\u00C4\u01DE\u1EA2\u00C5\u01FA\u01CD\u0200\u0202\u1EA0\u1EAC\u1EB6\u1E00\u0104\u023A\u2C6F]/g},
    {'base':'AA','letters':/[\uA732]/g},
    {'base':'AE','letters':/[\u00C6\u01FC\u01E2]/g},
    {'base':'AO','letters':/[\uA734]/g},
    {'base':'AU','letters':/[\uA736]/g},
    {'base':'AV','letters':/[\uA738\uA73A]/g},
    {'base':'AY','letters':/[\uA73C]/g},
    {'base':'B', 'letters':/[\u0042\u24B7\uFF22\u1E02\u1E04\u1E06\u0243\u0182\u0181]/g},
    {'base':'C', 'letters':/[\u0043\u24B8\uFF23\u0106\u0108\u010A\u010C\u00C7\u1E08\u0187\u023B\uA73E]/g},
    {'base':'D', 'letters':/[\u0044\u24B9\uFF24\u1E0A\u010E\u1E0C\u1E10\u1E12\u1E0E\u0110\u018B\u018A\u0189\uA779]/g},
    {'base':'DZ','letters':/[\u01F1\u01C4]/g},
    {'base':'Dz','letters':/[\u01F2\u01C5]/g},
    {'base':'E', 'letters':/[\u0045\u24BA\uFF25\u00C8\u00C9\u00CA\u1EC0\u1EBE\u1EC4\u1EC2\u1EBC\u0112\u1E14\u1E16\u0114\u0116\u00CB\u1EBA\u011A\u0204\u0206\u1EB8\u1EC6\u0228\u1E1C\u0118\u1E18\u1E1A\u0190\u018E]/g},
    {'base':'F', 'letters':/[\u0046\u24BB\uFF26\u1E1E\u0191\uA77B]/g},
    {'base':'G', 'letters':/[\u0047\u24BC\uFF27\u01F4\u011C\u1E20\u011E\u0120\u01E6\u0122\u01E4\u0193\uA7A0\uA77D\uA77E]/g},
    {'base':'H', 'letters':/[\u0048\u24BD\uFF28\u0124\u1E22\u1E26\u021E\u1E24\u1E28\u1E2A\u0126\u2C67\u2C75\uA78D]/g},
    {'base':'I', 'letters':/[\u0049\u24BE\uFF29\u00CC\u00CD\u00CE\u0128\u012A\u012C\u0130\u00CF\u1E2E\u1EC8\u01CF\u0208\u020A\u1ECA\u012E\u1E2C\u0197]/g},
    {'base':'J', 'letters':/[\u004A\u24BF\uFF2A\u0134\u0248]/g},
    {'base':'K', 'letters':/[\u004B\u24C0\uFF2B\u1E30\u01E8\u1E32\u0136\u1E34\u0198\u2C69\uA740\uA742\uA744\uA7A2]/g},
    {'base':'L', 'letters':/[\u004C\u24C1\uFF2C\u013F\u0139\u013D\u1E36\u1E38\u013B\u1E3C\u1E3A\u0141\u023D\u2C62\u2C60\uA748\uA746\uA780]/g},
    {'base':'LJ','letters':/[\u01C7]/g},
    {'base':'Lj','letters':/[\u01C8]/g},
    {'base':'M', 'letters':/[\u004D\u24C2\uFF2D\u1E3E\u1E40\u1E42\u2C6E\u019C]/g},
    {'base':'N', 'letters':/[\u004E\u24C3\uFF2E\u01F8\u0143\u00D1\u1E44\u0147\u1E46\u0145\u1E4A\u1E48\u0220\u019D\uA790\uA7A4]/g},
    {'base':'NJ','letters':/[\u01CA]/g},
    {'base':'Nj','letters':/[\u01CB]/g},
    {'base':'O', 'letters':/[\u004F\u24C4\uFF2F\u00D2\u00D3\u00D4\u1ED2\u1ED0\u1ED6\u1ED4\u00D5\u1E4C\u022C\u1E4E\u014C\u1E50\u1E52\u014E\u022E\u0230\u00D6\u022A\u1ECE\u0150\u01D1\u020C\u020E\u01A0\u1EDC\u1EDA\u1EE0\u1EDE\u1EE2\u1ECC\u1ED8\u01EA\u01EC\u00D8\u01FE\u0186\u019F\uA74A\uA74C]/g},
    {'base':'OI','letters':/[\u01A2]/g},
    {'base':'OO','letters':/[\uA74E]/g},
    {'base':'OU','letters':/[\u0222]/g},
    {'base':'P', 'letters':/[\u0050\u24C5\uFF30\u1E54\u1E56\u01A4\u2C63\uA750\uA752\uA754]/g},
    {'base':'Q', 'letters':/[\u0051\u24C6\uFF31\uA756\uA758\u024A]/g},
    {'base':'R', 'letters':/[\u0052\u24C7\uFF32\u0154\u1E58\u0158\u0210\u0212\u1E5A\u1E5C\u0156\u1E5E\u024C\u2C64\uA75A\uA7A6\uA782]/g},
    {'base':'S', 'letters':/[\u0053\u24C8\uFF33\u1E9E\u015A\u1E64\u015C\u1E60\u0160\u1E66\u1E62\u1E68\u0218\u015E\u2C7E\uA7A8\uA784]/g},
    {'base':'T', 'letters':/[\u0054\u24C9\uFF34\u1E6A\u0164\u1E6C\u021A\u0162\u1E70\u1E6E\u0166\u01AC\u01AE\u023E\uA786]/g},
    {'base':'TZ','letters':/[\uA728]/g},
    {'base':'U', 'letters':/[\u0055\u24CA\uFF35\u00D9\u00DA\u00DB\u0168\u1E78\u016A\u1E7A\u016C\u00DC\u01DB\u01D7\u01D5\u01D9\u1EE6\u016E\u0170\u01D3\u0214\u0216\u01AF\u1EEA\u1EE8\u1EEE\u1EEC\u1EF0\u1EE4\u1E72\u0172\u1E76\u1E74\u0244]/g},
    {'base':'V', 'letters':/[\u0056\u24CB\uFF36\u1E7C\u1E7E\u01B2\uA75E\u0245]/g},
    {'base':'VY','letters':/[\uA760]/g},
    {'base':'W', 'letters':/[\u0057\u24CC\uFF37\u1E80\u1E82\u0174\u1E86\u1E84\u1E88\u2C72]/g},
    {'base':'X', 'letters':/[\u0058\u24CD\uFF38\u1E8A\u1E8C]/g},
    {'base':'Y', 'letters':/[\u0059\u24CE\uFF39\u1EF2\u00DD\u0176\u1EF8\u0232\u1E8E\u0178\u1EF6\u1EF4\u01B3\u024E\u1EFE]/g},
    {'base':'Z', 'letters':/[\u005A\u24CF\uFF3A\u0179\u1E90\u017B\u017D\u1E92\u1E94\u01B5\u0224\u2C7F\u2C6B\uA762]/g},
    {'base':'a', 'letters':/[\u0061\u24D0\uFF41\u1E9A\u00E0\u00E1\u00E2\u1EA7\u1EA5\u1EAB\u1EA9\u00E3\u0101\u0103\u1EB1\u1EAF\u1EB5\u1EB3\u0227\u01E1\u00E4\u01DF\u1EA3\u00E5\u01FB\u01CE\u0201\u0203\u1EA1\u1EAD\u1EB7\u1E01\u0105\u2C65\u0250]/g},
    {'base':'aa','letters':/[\uA733]/g},
    {'base':'ae','letters':/[\u00E6\u01FD\u01E3]/g},
    {'base':'ao','letters':/[\uA735]/g},
    {'base':'au','letters':/[\uA737]/g},
    {'base':'av','letters':/[\uA739\uA73B]/g},
    {'base':'ay','letters':/[\uA73D]/g},
    {'base':'b', 'letters':/[\u0062\u24D1\uFF42\u1E03\u1E05\u1E07\u0180\u0183\u0253]/g},
    {'base':'c', 'letters':/[\u0063\u24D2\uFF43\u0107\u0109\u010B\u010D\u00E7\u1E09\u0188\u023C\uA73F\u2184]/g},
    {'base':'d', 'letters':/[\u0064\u24D3\uFF44\u1E0B\u010F\u1E0D\u1E11\u1E13\u1E0F\u0111\u018C\u0256\u0257\uA77A]/g},
    {'base':'dz','letters':/[\u01F3\u01C6]/g},
    {'base':'e', 'letters':/[\u0065\u24D4\uFF45\u00E8\u00E9\u00EA\u1EC1\u1EBF\u1EC5\u1EC3\u1EBD\u0113\u1E15\u1E17\u0115\u0117\u00EB\u1EBB\u011B\u0205\u0207\u1EB9\u1EC7\u0229\u1E1D\u0119\u1E19\u1E1B\u0247\u025B\u01DD]/g},
    {'base':'f', 'letters':/[\u0066\u24D5\uFF46\u1E1F\u0192\uA77C]/g},
    {'base':'g', 'letters':/[\u0067\u24D6\uFF47\u01F5\u011D\u1E21\u011F\u0121\u01E7\u0123\u01E5\u0260\uA7A1\u1D79\uA77F]/g},
    {'base':'h', 'letters':/[\u0068\u24D7\uFF48\u0125\u1E23\u1E27\u021F\u1E25\u1E29\u1E2B\u1E96\u0127\u2C68\u2C76\u0265]/g},
    {'base':'hv','letters':/[\u0195]/g},
    {'base':'i', 'letters':/[\u0069\u24D8\uFF49\u00EC\u00ED\u00EE\u0129\u012B\u012D\u00EF\u1E2F\u1EC9\u01D0\u0209\u020B\u1ECB\u012F\u1E2D\u0268\u0131]/g},
    {'base':'j', 'letters':/[\u006A\u24D9\uFF4A\u0135\u01F0\u0249]/g},
    {'base':'k', 'letters':/[\u006B\u24DA\uFF4B\u1E31\u01E9\u1E33\u0137\u1E35\u0199\u2C6A\uA741\uA743\uA745\uA7A3]/g},
    {'base':'l', 'letters':/[\u006C\u24DB\uFF4C\u0140\u013A\u013E\u1E37\u1E39\u013C\u1E3D\u1E3B\u017F\u0142\u019A\u026B\u2C61\uA749\uA781\uA747]/g},
    {'base':'lj','letters':/[\u01C9]/g},
    {'base':'m', 'letters':/[\u006D\u24DC\uFF4D\u1E3F\u1E41\u1E43\u0271\u026F]/g},
    {'base':'n', 'letters':/[\u006E\u24DD\uFF4E\u01F9\u0144\u00F1\u1E45\u0148\u1E47\u0146\u1E4B\u1E49\u019E\u0272\u0149\uA791\uA7A5]/g},
    {'base':'nj','letters':/[\u01CC]/g},
    {'base':'o', 'letters':/[\u006F\u24DE\uFF4F\u00F2\u00F3\u00F4\u1ED3\u1ED1\u1ED7\u1ED5\u00F5\u1E4D\u022D\u1E4F\u014D\u1E51\u1E53\u014F\u022F\u0231\u00F6\u022B\u1ECF\u0151\u01D2\u020D\u020F\u01A1\u1EDD\u1EDB\u1EE1\u1EDF\u1EE3\u1ECD\u1ED9\u01EB\u01ED\u00F8\u01FF\u0254\uA74B\uA74D\u0275]/g},
    {'base':'oi','letters':/[\u01A3]/g},
    {'base':'ou','letters':/[\u0223]/g},
    {'base':'oo','letters':/[\uA74F]/g},
    {'base':'p','letters':/[\u0070\u24DF\uFF50\u1E55\u1E57\u01A5\u1D7D\uA751\uA753\uA755]/g},
    {'base':'q','letters':/[\u0071\u24E0\uFF51\u024B\uA757\uA759]/g},
    {'base':'r','letters':/[\u0072\u24E1\uFF52\u0155\u1E59\u0159\u0211\u0213\u1E5B\u1E5D\u0157\u1E5F\u024D\u027D\uA75B\uA7A7\uA783]/g},
    {'base':'s','letters':/[\u0073\u24E2\uFF53\u00DF\u015B\u1E65\u015D\u1E61\u0161\u1E67\u1E63\u1E69\u0219\u015F\u023F\uA7A9\uA785\u1E9B]/g},
    {'base':'t','letters':/[\u0074\u24E3\uFF54\u1E6B\u1E97\u0165\u1E6D\u021B\u0163\u1E71\u1E6F\u0167\u01AD\u0288\u2C66\uA787]/g},
    {'base':'tz','letters':/[\uA729]/g},
    {'base':'u','letters':/[\u0075\u24E4\uFF55\u00F9\u00FA\u00FB\u0169\u1E79\u016B\u1E7B\u016D\u00FC\u01DC\u01D8\u01D6\u01DA\u1EE7\u016F\u0171\u01D4\u0215\u0217\u01B0\u1EEB\u1EE9\u1EEF\u1EED\u1EF1\u1EE5\u1E73\u0173\u1E77\u1E75\u0289]/g},
    {'base':'v','letters':/[\u0076\u24E5\uFF56\u1E7D\u1E7F\u028B\uA75F\u028C]/g},
    {'base':'vy','letters':/[\uA761]/g},
    {'base':'w','letters':/[\u0077\u24E6\uFF57\u1E81\u1E83\u0175\u1E87\u1E85\u1E98\u1E89\u2C73]/g},
    {'base':'x','letters':/[\u0078\u24E7\uFF58\u1E8B\u1E8D]/g},
    {'base':'y','letters':/[\u0079\u24E8\uFF59\u1EF3\u00FD\u0177\u1EF9\u0233\u1E8F\u00FF\u1EF7\u1E99\u1EF5\u01B4\u024F\u1EFF]/g},
    {'base':'z','letters':/[\u007A\u24E9\uFF5A\u017A\u1E91\u017C\u017E\u1E93\u1E95\u01B6\u0225\u0240\u2C6C\uA763]/g}
];
var changes;
function removeDiacritics (str) {
    if(!changes) {
        changes = defaultDiacriticsRemovalMap;
    }
    for(var i=0; i<changes.length; i++) {
        str = str.replace(changes[i].letters, changes[i].base);
    }
    return str;
}

