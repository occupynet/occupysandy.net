/**
 *
 *
 */
var wptFile = (function($, w) {
    var frame = [];
    var $item, $parent, $preview;

    function init() {
        // Fetch available headers and apply jQuery.masonry
        // once the images have loaded.
        var $headers = $('.available-headers');

        $headers.imagesLoaded( function() {
            $headers.masonry({
                itemSelector: '.default-header',
                isRTL: !! ( 'undefined' != typeof isRtl && isRtl )
            });
        });
        /*
        $('.js-wpt-field').on('click', 'a.js-wpt-file-upload', function() {
            if ( $(this).data('attched-thickbox') ) {
                return;
            }
            return wptFile.open(this, true);
        });
        */
        // Build the choose from library frame.
        $('.js-wpt-field').on('click', 'a.js-wpt-file-upload', function( event ) {
            wptFile.bindOpen($(this), event);
        });
    }

    function bindOpen($el, event)
    {
            var $type = $el.data('wpt-type');
            var $id = $el.parent().attr('id');

            if ( event ) {
                event.preventDefault();
            }

            // If the media frame already exists, reopen it.
            if ( frame[$id] ) {
                frame[$id].open();
                return;
            }

            // Create the media frame.
            frame[$id] = wp.media.frames.customHeader = wp.media({
                // Set the title of the modal.
                title: $el.html(),

                // Tell the modal to show only images.
                library: {
                    type: 'file' == $type? null:$type
                },

                // Customize the submit button.
                button: {
                    // Set the text of the button.
                    text: $el.data('update'),
                    // Tell the button not to close the modal, since we're
                    // going to refresh the page when the image is selected.
                    close: false
                }
            });

            // When an image is selected, run a callback.
            frame[$id].on( 'select', function() {
                // Grab the selected attachment.
                var attachment = frame[$id].state().get('selection').first();
                var $parent = $el.parent();
                switch( $type ) {
                    case 'image':
                        /**
                         * value
                         */
                        var has_size_full = false;
                        if (
                            'undefined' != typeof attachment.attributes.sizes
                            && 'undefined' != typeof attachment.attributes.sizes.full
                            && 'undefined' != typeof attachment.attributes.sizes.full.url
                           ) {
                               has_size_full = true;
                               $('.textfield', $parent).val(attachment.attributes.sizes.full.url);
                           }
                        else if ( 'undefined' != typeof(attachment.attributes.url) ) {
                            $('.textfield', $parent).val(attachment.attributes.url);
                        }

                        /**
                         * preview
                         */
                        if ( 0 == $('.wpt-file-preview img', $parent.parent()).length) {
                            $('.wpt-file-preview', $parent.parent()).append('<img src="">');
                        }
                        if (
                            'undefined' != typeof attachment.attributes.sizes
                            && 'undefined' != typeof attachment.attributes.sizes.thumbnail
                            && 'undefined' != typeof attachment.attributes.sizes.thumbnail.url
                           ) {
                               $('.wpt-file-preview img', $parent.parent()).attr('src', attachment.attributes.sizes.thumbnail.url);
                           }
                        else if ( has_size_full ) {
                            $('.wpt-file-preview img', $parent.parent()).attr('src', attachment.attributes.sizes.full.url);
                        }
                        else if ( 'undefined' != typeof(attachment.attributes.url) ) {
                            $('.wpt-file-preview img', $parent.parent()).attr('src', attachment.attributes.url);
                        }
                        /**
                         * add full
                         */
                        if ( has_size_full ) {
                            $('.wpt-file-preview img', $parent.parent()).data('full-src', attachment.attributes.sizes.full.url);
                        } else if ( 'undefined' != typeof(attachment.attributes.url) ) {
                            $('.wpt-file-preview img', $parent.parent()).data('full-src', attachment.attributes.url);
                        }
                        /**
                         * bind preview
                         */
                        if ( 'function' == typeof bind_colorbox_to_thumbnail_preview) {
                            bind_colorbox_to_thumbnail_preview();
                        }
                        break;
                    default:
                        $('.textfield', $parent).val(attachment.attributes.url);
                        break;
                }
                frame[$id].close();
            });

            frame[$id].open();
    }

    return {
        init: init,
        bindOpen: bindOpen,
    };
})(jQuery);

jQuery(document).ready(wptFile.init);

