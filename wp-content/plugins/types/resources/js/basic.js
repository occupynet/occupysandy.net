/** * * Use this file only for scripts needed in full version.
 * Before moving from embedded JS - make sure it's needed only here.
 *
 *
 */
jQuery(document).ready(function($){
    $('input[name=file]').on('change', function() {
        if($(this),$(this).val()) {
            $('input[name=import-file]').removeAttr('disabled');
        }
    });
    $('a.current').each( function() {
        var href = $(this).attr('href');
        if ('undefined' != typeof(href) && href.match(/page=wpcf\-edit(\-(type|usermeta))?/)) {
            $(this).attr('href', window.location.href);
        }
    });
    /**
     * allow to sort CF
     */
    $("#custom_fields ul").sortable();
    /**
     * colorbox for images
     */
    bind_colorbox_to_thumbnail_preview();
});

/**
 * colorbox for images
 */
function bind_colorbox_to_thumbnail_preview() {
    jQuery('.js-wpt-file-preview img').each(function(){
        if ( jQuery(this).data('full-src')) {
            jQuery(this).on('click', function() {
                jQuery.colorbox({
                    href: jQuery(this).data('full-src'),
                    maxWidth: "75%",
                    maxHeight: "75%",
                    close: wpcf_js.close
                });
            });
        }
    });
}

